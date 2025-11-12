<?php
class Database {
    private static $instance = null;
    private $connection;

    private function __construct()
    {
        try {
            $dsn = "mysql:host=" . DB_HOST . ";dbname=" . DB_NAME . ";charset=" . DB_CHARSET;
            $options = [
                PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION,
                PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
                PDO::ATTR_EMULATE_PREPARES => false,
            ];

            $this->connection = new PDO($dsn, DB_USER, DB_PASS, $options);
        } catch (PDOException $e) {
            // Log the error message for server logs
            error_log("Database Connection Error: " . $e->getMessage());

            // Ensure we return a JSON error response with HTTP 500 when possible
            if (!headers_sent()) {
                http_response_code(500);
                header('Content-Type: application/json');
            }

            echo json_encode(['error' => 'Database Connection Failed']);
            exit;
        }
    }

    public static function getInstance(){
        if(self::$instance == null) {
            self::$instance = new self();
        }
        return self::$instance;
    }

    public function getConnection(){
        return $this->connection;
    }

    private function __clone() {}

    public function __wakeup() {
        throw new Exception("Cannot unserialize singleton");
    }
}
?>