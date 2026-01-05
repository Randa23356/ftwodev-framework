<?php

namespace Engine;

class Boot
{
    const VERSION = '1.5.0';
    
    private static $config = [];

    public static function run()
    {
        // 1. Load Environment Variables
        Env::load();

        // 2. Load Config
        self::loadConfig();

        // 3. Start Session
        Session::start();

        // 4. Error Handling
        if (config('app.debug', true)) {
            ini_set('display_errors', 1);
            ini_set('display_startup_errors', 1);
            error_reporting(E_ALL);
        } else {
            ini_set('display_errors', 0);
            error_reporting(0);
        }

        // 5. Dispatch Router
        try {
            $router = new Router();
            require_once __DIR__ . '/../config/routes.php';
            $router->dispatch();
        } catch (\Exception $e) {
            self::handleException($e);
        }
    }

    private static function loadConfig()
    {
        $configPath = __DIR__ . '/../config/';
        if (file_exists($configPath . 'app.php')) {
            self::$config['app'] = require $configPath . 'app.php';
        }
        if (file_exists($configPath . 'database.php')) {
            self::$config['database'] = require $configPath . 'database.php';
        }
    }

    public static function config($key, $default = null)
    {
        $keys = explode('.', $key);
        $value = self::$config;

        foreach ($keys as $k) {
            if (isset($value[$k])) {
                $value = $value[$k];
            } else {
                return $default;
            }
        }
        return $value;
    }

    public static function env($key, $default = null)
    {
        return Env::get($key, $default);
    }

    private static function handleException($e)
    {
        echo "<h1>FTwoDev Framework Error</h1>";
        echo "<p>" . $e->getMessage() . "</p>";
        if (config('app.debug', true)) {
             echo "<pre>" . $e->getTraceAsString() . "</pre>";
        }
    }
}
