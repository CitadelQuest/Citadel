<?php

namespace App\Service;

use App\Entity\DiaryEntry;
use App\Service\UserDatabaseManager;
use Symfony\Component\Security\Core\User\UserInterface;

class DiaryService
{
    public function __construct(
        private readonly UserDatabaseManager $userDatabaseManager
    ) {
    }

    public function createEntry(
        UserInterface $user,
        string $title,
        string $content,
        ?string $mood = null,
        array|string|null $tags = null,
        ?bool $isEncrypted = null,
        ?string $contentFormatted = null
    ): DiaryEntry {
        $entry = new DiaryEntry();
        $entry
            ->setTitle($title)
            ->setContent($content)
            ->setMood($mood)
            ->setTags($tags)
            ->setIsEncrypted($isEncrypted ?? false)
            ->setContentFormatted($contentFormatted);

        // Store in user's database
        $userDb = $this->userDatabaseManager->getDatabaseConnection($user);
        $userDb->executeStatement(
            'INSERT INTO diary_entries (id, title, content, created_at, updated_at, is_encrypted, is_favorite, tags, mood, content_formatted) 
             VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?)',
            [
                $entry->getId(),
                $entry->getTitle(),
                $entry->getContent(),
                $entry->getCreatedAt()->format('Y-m-d H:i:s'),
                $entry->getUpdatedAt()->format('Y-m-d H:i:s'),
                $entry->isEncrypted() ? 1 : 0,
                $entry->isFavorite() ? 1 : 0,
                $entry->getTags() ? implode(',', $entry->getTags()) : null,
                $entry->getMood(),
                $entry->getContentFormatted()
            ]
        );

        return $entry;
    }

    /**
     * @return DiaryEntry[]
     */
    public function findLatestEntries(UserInterface $user, int $limit = 10): array
    {
        $userDb = $this->userDatabaseManager->getDatabaseConnection($user);
        $stmt = $userDb->executeQuery(
            'SELECT * FROM diary_entries ORDER BY created_at DESC LIMIT ?',
            [$limit]
        );

        return array_map(
            [$this, 'createEntryFromRow'],
            $stmt->fetchAllAssociative()
        );
    }

    /**
     * @return DiaryEntry[]
     */
    public function findFavorites(UserInterface $user): array
    {
        $userDb = $this->userDatabaseManager->getDatabaseConnection($user);
        $stmt = $userDb->executeQuery(
            'SELECT * FROM diary_entries WHERE is_favorite = 1 ORDER BY created_at DESC'
        );

        return array_map(
            [$this, 'createEntryFromRow'],
            $stmt->fetchAllAssociative()
        );
    }

    /**
     * @return DiaryEntry[]
     */
    public function findByTag(UserInterface $user, string $tag): array
    {
        $userDb = $this->userDatabaseManager->getDatabaseConnection($user);
        $stmt = $userDb->executeQuery(
            'SELECT * FROM diary_entries WHERE tags LIKE ? ORDER BY created_at DESC',
            ['%' . $tag . '%']
        );

        return array_map(
            [$this, 'createEntryFromRow'],
            $stmt->fetchAllAssociative()
        );
    }

    private function createEntryFromRow(array $row): DiaryEntry
    {
        $entry = new DiaryEntry();
        return $entry
            ->setId($row['id'])
            ->setTitle($row['title'])
            ->setContent($row['content'])
            ->setContentFormatted($row['content_formatted'])
            ->setMood($row['mood'])
            ->setTags($row['tags'] ? explode(',', $row['tags']) : null)
            ->setIsEncrypted((bool)$row['is_encrypted'])
            ->setIsFavorite((bool)$row['is_favorite'])
            ->setCreatedAt(new \DateTimeImmutable($row['created_at']))
            ->setUpdatedAt(new \DateTimeImmutable($row['updated_at']))
        ;
    }

    public function toggleFavorite(UserInterface $user, string $id): DiaryEntry
    {
        $userDb = $this->userDatabaseManager->getDatabaseConnection($user);
        
        // First get the current entry
        $stmt = $userDb->executeQuery('SELECT * FROM diary_entries WHERE id = ?', [$id]);
        $row = $stmt->fetchAssociative();
        
        if (!$row) {
            throw new \RuntimeException('Entry not found');
        }
        
        $entry = $this->createEntryFromRow($row);
        $newFavoriteState = !$entry->isFavorite();
        
        // Update the favorite status
        $userDb->executeStatement(
            'UPDATE diary_entries SET is_favorite = ? WHERE id = ?',
            [$newFavoriteState ? 1 : 0, $id]
        );
        
        $entry->setIsFavorite($newFavoriteState);
        return $entry;
    }

    public function updateEntry(UserInterface $user, string $id, array $data): DiaryEntry
    {
        $userDb = $this->userDatabaseManager->getDatabaseConnection($user);
        
        // First get the current entry
        $stmt = $userDb->executeQuery('SELECT * FROM diary_entries WHERE id = ?', [$id]);
        $row = $stmt->fetchAssociative();
        
        if (!$row) {
            throw new \RuntimeException('Entry not found');
        }
        
        $entry = $this->createEntryFromRow($row);
        
        // Update only provided fields
        if (isset($data['title'])) {
            $entry->setTitle($data['title']);
        }
        if (isset($data['content'])) {
            $entry->setContent($data['content']);
        }
        if (isset($data['contentFormatted'])) {
            $entry->setContentFormatted($data['contentFormatted']);
        }
        if (array_key_exists('mood', $data)) {
            $entry->setMood($data['mood'] ?: null);
        }
        if (isset($data['tags'])) {
            $entry->setTags($data['tags']);
        }
        
        // Update in database
        $userDb->executeStatement(
            'UPDATE diary_entries 
             SET title = ?, content = ?, content_formatted = ?, mood = ?, tags = ?, updated_at = CURRENT_TIMESTAMP 
             WHERE id = ?',
            [
                $entry->getTitle(),
                $entry->getContent(),
                $entry->getContentFormatted(),
                $entry->getMood(),
                $entry->getTags() ? implode(',', $entry->getTags()) : null,
                $id
            ]
        );
        
        return $entry;
    }

    public function deleteEntry(UserInterface $user, string $id): void
    {
        $userDb = $this->userDatabaseManager->getDatabaseConnection($user);
        $userDb->executeStatement('DELETE FROM diary_entries WHERE id = ?', [$id]);
    }

    public function findById(UserInterface $user, string $id): ?DiaryEntry
    {
        $userDb = $this->userDatabaseManager->getDatabaseConnection($user);
        $stmt = $userDb->executeQuery('SELECT * FROM diary_entries WHERE id = ?', [$id]);
        $row = $stmt->fetchAssociative();

        return $row ? $this->createEntryFromRow($row) : null;
    }
}
