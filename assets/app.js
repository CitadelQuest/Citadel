/*
 * CitadelQuest Main Application Entry Point
 */

// Import styles
import './styles/app.scss';
import '@mdi/font/css/materialdesignicons.min.css';

// Import icons and images
import './images/android-chrome-192x192.png';
import './images/android-chrome-512x512.png';
import './images/apple-touch-icon.png';
import './images/favicon-16x16.png';
import './images/favicon-32x32.png';
import './images/favicon.ico';
import './images/logo-sm.png';
import './images/citadel_quest_bg.jpg';
import './images/bg-pattern.svg';

// Import Bootstrap's JavaScript
import 'bootstrap';

// Import language switcher
import { initLanguageSwitcher } from './js/language';

// Import notifications
import './js/notifications';


// Initialize Bootstrap components
document.addEventListener('DOMContentLoaded', () => {
    // Initialize language switcher
    initLanguageSwitcher();
    // Enable tooltips everywhere
    const tooltipTriggerList = [].slice.call(document.querySelectorAll('[data-bs-toggle="tooltip"]'));
    tooltipTriggerList.map(function (tooltipTriggerEl) {
        return new bootstrap.Tooltip(tooltipTriggerEl);
    });

    // Enable popovers everywhere
    const popoverTriggerList = [].slice.call(document.querySelectorAll('[data-bs-toggle="popover"]'));
    popoverTriggerList.map(function (popoverTriggerEl) {
        return new bootstrap.Popover(popoverTriggerEl);
    });

});
