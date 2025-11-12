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
        
        // ✅ FIX: Remove base path (project folder path)
        $scriptName = dirname($_SERVER['SCRIPT_NAME']);
        if ($scriptName !== '/') {
            $uri = substr($uri, strlen($scriptName));
        }
        
        // Ensure URI starts with /
        if (empty($uri) || $uri[0] !== '/') {
            $uri = '/' . $uri;
        }
        
        // Debug mode
        if (isset($_GET['debug'])) {
            header('Content-Type: application/json');
            echo json_encode([
                'original_uri' => $_SERVER['REQUEST_URI'],
                'parsed_uri' => $uri,
                'script_name' => $_SERVER['SCRIPT_NAME'],
                'base_path' => $scriptName,
                'method' => $method,
                'registered_routes' => array_map(function($route) {
                    return [
                        'method' => $route['method'],
                        'path' => $route['path'],
                        'pattern' => $route['pattern']
                    ];
                }, $this->routes)
            ], JSON_PRETTY_PRINT);
            exit;
        }
        
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
                        echo json_encode([
                            'error' => 'Controller not found',
                            'controller' => $controller,
                            'path' => $controllerPath
                        ]);
                    }
                }
                return;
            }
        }
        
        // 404 Not Found
        http_response_code(404);
        echo json_encode([
            'error' => 'Route not found',
            'uri' => $uri,
            'method' => $method,
            'available_routes' => array_map(function($r) {
                return $r['method'] . ' ' . $r['path'];
            }, $this->routes)
        ]);
    }
}
?>