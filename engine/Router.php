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
            
            // Controller doesn't exist - provide helpful message
            if (!class_exists($controllerName)) {
                $this->showSetupHelp($parts[0]);
            }
        }
        
        throw new \Exception("Action not found: " . json_encode($action));
    }
    
    private function showSetupHelp($controllerName)
    {
        $controllerFile = __DIR__ . '/../projects/Controllers/' . $controllerName . '.php';
        
        echo "<div style='font-family: system-ui; max-width: 800px; margin: 2rem auto; padding: 2rem; background: #f8fafc; border-radius: 12px; border-left: 4px solid #ef4444;'>";
        echo "<h1 style='color: #dc2626; margin-bottom: 1rem;'>⚠️ Controller Not Found</h1>";
        echo "<p style='color: #64748b; margin-bottom: 1.5rem;'><strong>Controller '<code>$controllerName</code>' does not exist.</strong></p>";
        
        echo "<div style='background: #1e293b; color: #94a3b8; padding: 1rem; border-radius: 8px; margin-bottom: 1.5rem;'>";
        echo "<p style='margin: 0; font-family: monospace; font-size: 0.9rem;'>Expected file: <code style='color: #f8fafc;'>projects/Controllers/$controllerName.php</code></p>";
        echo "</div>";
        
        echo "<h3 style='color: #374151; margin-bottom: 1rem;'>🔧 Quick Fix:</h3>";
        echo "<ol style='color: #64748b; line-height: 1.6;'>";
        echo "<li>Run setup command to create basic controllers:</li>";
        echo "<div style='background: #020617; color: #94a3b8; padding: 0.75rem; border-radius: 6px; margin: 0.5rem 0; font-family: monospace;'>";
        echo "<span style='color: #10b981;'>➜</span> <span style='color: #f8fafc;'>php ftwo ignite:setup</span>";
        echo "</div>";
        echo "<li>Or create the controller manually:</li>";
        echo "<div style='background: #020617; color: #94a3b8; padding: 0.75rem; border-radius: 6px; margin: 0.5rem 0; font-family: monospace;'>";
        echo "<span style='color: #10b981;'>➜</span> <span style='color: #f8fafc;'>php ftwo craft:controller $controllerName</span>";
        echo "</div>";
        echo "</ol>";
        
        echo "<h3 style='color: #374151; margin-bottom: 1rem;'>📋 Available Commands:</h3>";
        echo "<ul style='color: #64748b; line-height: 1.6;'>";
        echo "<li><code style='background: #e5e7eb; padding: 0.25rem 0.5rem; border-radius: 4px;'>php ftwo ignite:setup</code> - Create basic structure</li>";
        echo "<li><code style='background: #e5e7eb; padding: 0.25rem 0.5rem; border-radius: 4px;'>php ftwo craft:controller Name</code> - Create controller</li>";
        echo "<li><code style='background: #e5e7eb; padding: 0.25rem 0.5rem; border-radius: 4px;'>php ftwo craft:model Name</code> - Create model</li>";
        echo "</ul>";
        
        echo "<div style='margin-top: 2rem; padding-top: 1.5rem; border-top: 1px solid #e5e7eb;'>";
        echo "<p style='color: #6b7280; font-size: 0.9rem; margin: 0;'>💡 <strong>Tip:</strong> Run <code style='background: #e5e7eb; padding: 0.25rem 0.5rem; border-radius: 4px;'>php ftwo ignite:setup</code> to create the basic framework structure including WelcomeController and HomeController.</p>";
        echo "</div>";
        
        echo "</div>";
        exit;
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
