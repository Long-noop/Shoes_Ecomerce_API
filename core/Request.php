<?php
class Request{
    private $data;

    public function __construct()
    {
        $this->data = $this->parseInput();
    }

    public function parseInput() {
        $method = $_SERVER['REQUEST_METHOD'];
        if($method === 'GET'){
            return $_GET;
        } else if ($method === 'POST') {
            $contentType = $_SERVER['CONTENT_TYPE'] ?? '';
            
            if (strpos($contentType, 'application/json') !== false) {
                return json_decode(file_get_contents('php://input'), true) ?? [];
            }
            return $_POST;
        } else {
            return json_decode(file_get_contents('php://input'), true) ?? [];
        }
    }

    public function get($key, $default = null) {
        return $this->data[$key] ?? $default;
    }

    public function all() {
        return $this->data;
    }

    public function has($key) {
        return isset($this->data[$key]);
    }

    public function method() {
        return $_SERVER['REQUEST_METHOD'];
    }
    public function getBearerToken() {
        $headers = $this->getHeaders();
        
        if (isset($headers['Authorization'])) {
            if (preg_match('/Bearer\s+(.*)$/i', $headers['Authorization'], $matches)) {
                return $matches[1];
            }
        }
        return null;
    }

    private function getHeaders() {
        $headers = [];
        foreach ($_SERVER as $key => $value) {
            if (strpos($key, 'HTTP_') === 0) {
                $header = str_replace(' ', '-', ucwords(strtolower(str_replace('_', ' ', substr($key, 5)))));
                $headers[$header] = $value;
            }
        }
        
        if (isset($_SERVER['CONTENT_TYPE'])) {
            $headers['Content-Type'] = $_SERVER['CONTENT_TYPE'];
        }
        
        return $headers;
    }

    public function file($key) {
        return $_FILES[$key] ?? null;
    }
}
?>