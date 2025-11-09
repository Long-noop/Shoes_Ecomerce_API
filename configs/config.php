<?php
// Application configuration
define('APP_NAME', 'KICKS E-commerce');
define('APP_VERSION', '1.0.0');
define('APP_ENV', getenv('APP_ENV') ?: 'development');
define('APP_DEBUG', getenv('APP_DEBUG') ?: true);

// API Configuration
define('API_VERSION', 'v1');
define('API_PREFIX', '/api/' . API_VERSION);

// JWT Configuration
define('JWT_SECRET', getenv('JWT_SECRET') ?: 'longtomoshimasu');
define('JWT_EXPIRATION', 86400); // 24 hours

// Upload Configuration
define('MAX_FILE_SIZE', 5 * 1024 * 1024); // 5MB
define('ALLOWED_IMAGE_TYPES', ['jpg', 'jpeg', 'png', 'gif', 'webp']);

// Pagination
define('DEFAULT_PAGE_SIZE', 20);
define('MAX_PAGE_SIZE', 100);

// URLs
define('BASE_URL', getenv('BASE_URL') ?: 'http://localhost:8000');
define('FRONTEND_URL', getenv('FRONTEND_URL') ?: 'http://localhost:3000');
?>