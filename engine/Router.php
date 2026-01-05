<?php

namespace Engine;

class Router
{
    private static $routes = [];
    private static $lastRoute = null;

    public static function get($uri, $action)
    {
        $method = 'GET';
        self::$routes[$method][$uri] = [
            'action' => $action,
            'middleware' => []
        ];
        self::$lastRoute = ['method' => $method, 'uri' => $uri];
        return new self(); // Return instance for chaining
    }

    public static function post($uri, $action)
    {
        $method = 'POST';
        self::$routes[$method][$uri] = [
            'action' => $action,
            'middleware' => []
        ];
        self::$lastRoute = ['method' => $method, 'uri' => $uri];
        return new self();
    }

    public function middleware($names)
    {
        if (self::$lastRoute) {
            if (is_string($names)) $names = [$names];
            $method = self::$lastRoute['method'];
            $uri = self::$lastRoute['uri'];
            self::$routes[$method][$uri]['middleware'] = array_merge(
                self::$routes[$method][$uri]['middleware'],
                $names
            );
        }
        return $this;
    }

    public function dispatch()
    {
        $uri = parse_url($_SERVER['REQUEST_URI'], PHP_URL_PATH);
        $method = $_SERVER['REQUEST_METHOD'];

        if ($uri !== '/' && substr($uri, -1) === '/') {
            $uri = rtrim($uri, '/');
        }

        if (self::isPublicAsset($uri)) {
             return false;
        }
        
        if (isset(self::$routes[$method][$uri])) {
            $route = self::$routes[$method][$uri];
            $this->runPipeline($route['middleware'], function() use ($route) {
                return $this->executeAction($route['action']);
            });
        } else {
            if ($this->attemptAutoRoute($uri)) {
                return;
            }
            $this->abort(404);
        }
    }

    private function runPipeline($middlewareNames, $finalAction)
    {
        $config = require __DIR__ . '/../config/middleware.php';
        $middlewares = $config['global'];

        foreach ($middlewareNames as $name) {
            if (isset($config['named'][$name])) {
                $middlewares[] = $config['named'][$name];
            }
        }

        $pipeline = array_reverse($middlewares);
        $next = $finalAction;

        foreach ($pipeline as $middlewareClass) {
            $next = function() use ($middlewareClass, $next) {
                $middleware = new $middlewareClass();
                return $middleware->handle($next);
            };
        }

        echo $next();
    }

    private function attemptAutoRoute($uri)
    {
        $parts = array_values(array_filter(explode('/', $uri)));
        if (empty($parts)) return false;

        $controllerName = ucfirst($parts[0]) . 'Controller';
        $methodName = $parts[1] ?? 'index';
        $fullController = "Projects\\Controllers\\$controllerName";

        if (class_exists($fullController)) {
            $controller = new $fullController();
            if (method_exists($controller, $methodName)) {
                $params = array_slice($parts, 2);
                // For now, magic routes don't have middleware support 
                // unless we implement something like controller-based middleware
                echo call_user_func_array([$controller, $methodName], $params);
                return true;
            }
        }
        return false;
    }

    private function executeAction($action)
    {
        if (is_callable($action)) {
            return call_user_func($action);
        }

        if (is_string($action)) {
            $parts = explode('@', $action);
            $controllerName = "Projects\\Controllers\\" . $parts[0];
            $method = $parts[1];

            if (class_exists($controllerName)) {
                $controller = new $controllerName();
                if (method_exists($controller, $method)) {
                    return $controller->$method();
                }
            }
        }
        throw new \Exception("Action not found: " . json_encode($action));
    }

    private function abort($code)
    {
        http_response_code($code);
        echo "<h1>{$code} Not Found</h1>";
        exit;
    }

    private static function isPublicAsset($uri) {
        $ext = pathinfo($uri, PATHINFO_EXTENSION);
        return !empty($ext);
    }
}
