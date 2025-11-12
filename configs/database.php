<?php
if (file_exists(ROOT_PATH . '/.env')) {
    $env = parse_ini_file(ROOT_PATH . '/.env');
    foreach ($env as $key => $value) {
        putenv("$key=$value");
    }
}

define('DB_HOST', getenv('DB_HOST') ?: 'localhost');
define('DB_NAME', getenv('DB_NAME') ?: 'shoes_ecommerce');
define('DB_USER', getenv('DB_USER') ?: 'root');
define('DB_PASS', getenv('DB_PASS') ?: '');
define('DB_CHARSET', 'utf8mb4');
?>
