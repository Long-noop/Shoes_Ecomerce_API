<?php
// Enable error reporting for development
error_reporting(E_ALL);
ini_set('display_errors', 1);

// Set timezone
date_default_timezone_set('Asia/Ho_Chi_Minh');

// Define paths
define('ROOT_PATH', dirname(__DIR__));
define('PUBLIC_PATH', __DIR__);
define('UPLOAD_PATH', PUBLIC_PATH . '/uploads');

// Load configuration
require_once ROOT_PATH . '/configs/config.php';
require_once ROOT_PATH . '/configs/database.php';
require_once ROOT_PATH . '/configs/cors.php';

// Load core classes
require_once ROOT_PATH . '/core/Database.php';
require_once ROOT_PATH . '/core/Model.php';
require_once ROOT_PATH . '/core/Controller.php';
require_once ROOT_PATH . '/core/Router.php';
require_once ROOT_PATH . '/core/Request.php';
require_once ROOT_PATH . '/core/Response.php';
require_once ROOT_PATH . '/core/Validator.php';
require_once ROOT_PATH . '/core/Auth.php';
require_once ROOT_PATH . '/core/JWT.php';

// Load helpers
// require_once ROOT_PATH . '/helpers/functions.php';
// require_once ROOT_PATH . '/helpers/sanitize.php';

// Handle CORS
handleCORS();

// Initialize router
$router = new Router();

// Load routes
require_once ROOT_PATH . '/routes/api.php';

// Run router
$router->run();
?>