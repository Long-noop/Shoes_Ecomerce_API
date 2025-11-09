<?php
class Router {
    private $routes = [];

    public function get($path, $handler) {
        $this->addRoute('GET', $path, $handler);
    }

    public function post($path, $handler) {
        $this->addRoute('POST', $path, $handler);
    }

    public function put($path, $handler) {
        $this->addRoute('PUT', $path, $handler);
    }

    public function delete($path, $handler) {
        $this->addRoute('DELETE', $path, $handler);
    }

    private function addRoute($method, $path, $handler) {
        $pattern = preg_replace('/\{([a-zA-Z0-9_]+)\}/', '(?P<$1>[^/]+)', $path);
        $pattern = '#^' . $pattern . '$#';
        
        $this->routes[] = [
            'method' => $method,
            'pattern' => $pattern,
            'handler' => $handler,
            'path' => $path
        ];
    }

    public function run() {
        $method = $_SERVER['REQUEST_METHOD'];
        $uri = parse_url($_SERVER['REQUEST_URI'], PHP_URL_PATH);

        foreach ($this->routes as $route) {
            if ($route['method'] === $method && preg_match($route['pattern'], $uri, $matches)) {
                $params = array_filter($matches, 'is_string', ARRAY_FILTER_USE_KEY);
                
                if (is_callable($route['handler'])) {
                    call_user_func_array($route['handler'], $params);
                } else if (is_array($route['handler'])) {
                    [$controller, $method] = $route['handler'];
                    $controllerPath = ROOT_PATH . "/controllers/$controller.php";
                    
                    if (file_exists($controllerPath)) {
                        require_once $controllerPath;
                        $controllerInstance = new $controller();
                        call_user_func_array([$controllerInstance, $method], $params);
                    } else {
                        http_response_code(500);
                        echo json_encode(['error' => 'Controller not found']);
                    }
                }
                return;
            }
        }

        // 404 Not Found
        http_response_code(404);
        echo json_encode(['error' => 'Route not found']);
    }
}
?>