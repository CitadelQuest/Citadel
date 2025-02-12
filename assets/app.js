/*
 * CitadelQuest Main Application Entry Point
 */

// Import styles
import './styles/app.scss';
import '@mdi/font/css/materialdesignicons.min.css';

// Import Bootstrap's JavaScript
import 'bootstrap';

// Import HTMX setup
import './js/htmx-setup';

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
