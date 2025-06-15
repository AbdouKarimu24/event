<?php
// Global configuration for EventZon XAMPP version

// Database configuration
define('DB_HOST', 'localhost');
define('DB_USER', 'root');
define('DB_PASS', '');
define('DB_NAME', 'eventzon');

// Application settings
define('APP_NAME', 'EventZon');
define('APP_URL', 'http://localhost/eventzon');
define('APP_TIMEZONE', 'Africa/Douala');

// Security settings
define('SESSION_LIFETIME', 86400); // 24 hours
define('PASSWORD_MIN_LENGTH', 6);
define('MAX_LOGIN_ATTEMPTS', 5);

// Upload settings
define('UPLOAD_MAX_SIZE', 5242880); // 5MB
define('UPLOAD_PATH', 'uploads/');
define('ALLOWED_IMAGE_TYPES', ['jpg', 'jpeg', 'png', 'gif']);

// Email settings (for future implementation)
define('SMTP_HOST', 'localhost');
define('SMTP_PORT', 587);
define('SMTP_USERNAME', '');
define('SMTP_PASSWORD', '');
define('FROM_EMAIL', 'noreply@eventzon.cm');
define('FROM_NAME', 'EventZon');

// Pagination settings
define('EVENTS_PER_PAGE', 12);
define('BOOKINGS_PER_PAGE', 10);

// Currency settings
define('CURRENCY_CODE', 'XAF');
define('CURRENCY_SYMBOL', 'XAF');

// Set timezone
date_default_timezone_set(APP_TIMEZONE);

// Session configuration
ini_set('session.cookie_lifetime', SESSION_LIFETIME);
ini_set('session.gc_maxlifetime', SESSION_LIFETIME);
?>