<?php
declare(strict_types=1);

ini_set('display_errors', '1');
ini_set('display_startup_errors', '1');
error_reporting(E_ALL);

// public/index.php

// 1. REGISTER THE CENTRALIZED ERROR HANDLER FIRST
require_once __DIR__ . '/../includes/error_handler.php';
register_global_error_handlers(__DIR__ . '/../logs');

// 2. VENDOR AUTOLOAD
require_once __DIR__ . '/../vendor/autoload.php';

// 3. APPLICATION INITIALIZATION (Loads BASE_PATH, sessions, functions, auth helpers, security, and $pdo)
require_once __DIR__ . '/../includes/init.php';

// 4. NORMALIZE URI FOR GLOBAL GATEKEEPING
$server = $_SERVER;
$httpMethod = isset($server['REQUEST_METHOD']) && is_string($server['REQUEST_METHOD']) ? $server['REQUEST_METHOD'] : 'GET';
$uri = isset($server['REQUEST_URI']) && is_string($server['REQUEST_URI']) ? $server['REQUEST_URI'] : '/';

$pos = strpos($uri, '?');
if (false !== $pos) {
    $uri = substr($uri, 0, $pos);
}
$uri = rawurldecode($uri);

// Safe subfolder prefix stripping using $baseDir provided by init.php
if ($baseDir !== '') {
    if ($uri === $baseDir) {
        $uri = '/';
    } elseif (str_starts_with($uri, $baseDir . '/')) {
        $uri = substr($uri, strlen($baseDir));
    }
}
if ($uri === '' || $uri === false) {
    $uri = '/';
}

// 5. GLOBAL SCHEMA / MIGRATION SAFETY-VALVE INTERCEPT
if ($uri !== '/update-database' && is_file(__DIR__ . '/../db/migrate_runner.php')) {
    require_once __DIR__ . '/../db/migrate_runner.php';
    
    try {
        $currentSchema = function_exists('get_schema_version') && isset($pdo) && $pdo instanceof PDO ? get_schema_version($pdo) : 0;
        $latestSchema = $currentSchema;
        $migrationsDir = __DIR__ . '/../db/migrations';
        
        if (is_dir($migrationsDir)) {
            $globFiles = glob($migrationsDir . '/*.php');
            if ($globFiles !== false) {
                foreach ($globFiles as $migFile) {
                    $m = [];
                    if (preg_match('/(\d+)_/', basename($migFile), $m)) {
                        $latestSchema = max($latestSchema, (int)$m[1]);
                    }
                }
            }
        }
        
        if ($currentSchema < $latestSchema) {
            $redirectTarget = ($baseDir !== '' ? $baseDir : '') . '/update-database';
            if (!headers_sent()) {
                header('Location: ' . $redirectTarget);
                exit;
            }
        }
    } catch (\Throwable $e) {
        // Fail gracefully if database or tables aren't set up yet, forcing the update gateway
        $redirectTarget = ($baseDir !== '' ? $baseDir : '') . '/update-database';
        if (!headers_sent()) {
            header('Location: ' . $redirectTarget);
            exit;
        }
    }
}

// 6. FASTROUTE DISPATCHING
/** @var callable(FastRoute\RouteCollector): void $routeDefinition */
$routeDefinition = require __DIR__ . '/../routes/web.php';
$dispatcher = FastRoute\simpleDispatcher($routeDefinition);

$routeInfo = $dispatcher->dispatch($httpMethod, $uri);

if (!is_array($routeInfo)) {
    throw new RuntimeException('Internal Server Error: Malformed dispatch response');
}

$status = $routeInfo[0] ?? null;

// Helper closure to render the shared error view cleanly for 404/405
$renderErrorView = function(int $code, string $title, string $message): void {
    if (!headers_sent()) {
        http_response_code($code);
    }
    $errorCode = $code;
    $errorTitle = $title;
    $errorMessage = $message;
    $basePath = defined('BASE_PATH') ? BASE_PATH : '';
    $isLocal = false;

    $templatePath = dirname(__DIR__) . '/app/Views/errors/error_template.php';
    if (is_file($templatePath)) {
        require $templatePath;
    } else {
        echo "<h1>{$errorCode} — {$errorTitle}</h1><p>{$errorMessage}</p>";
    }
    exit;
};

switch ($status) {
    case FastRoute\Dispatcher::NOT_FOUND:
        $renderErrorView(404, 'Page Not Found', 'The page, resource, or record you are looking for could not be found or has been removed.');
        break;

    case FastRoute\Dispatcher::METHOD_NOT_ALLOWED:
        $allowedMethods = implode(', ', $routeInfo[1]);
        $renderErrorView(405, 'Method Not Allowed', "The {$httpMethod} method is not supported for this route. Allowed methods: {$allowedMethods}");
        break;

    case FastRoute\Dispatcher::FOUND:
        $handler = $routeInfo[1] ?? null;
        $vars = $routeInfo[2] ?? [];

        if (is_array($handler) && count($handler) === 2) {
            $class = $handler[0];
            $method = $handler[1];

            if (is_string($class) && is_string($method) && class_exists($class) && method_exists($class, $method)) {
                $parameters = is_array($vars) ? array_values($vars) : [];

                // Instantiate the controller object dynamically and pass $pdo (already loaded by init.php)
                $controller = new $class($pdo ?? null);

                if (method_exists($controller, $method)) {
                    $controller->$method(...$parameters);
                } else {
                    throw new RuntimeException("Internal Server Error: Method {$method} execution failed");
                }
            } else {
                throw new RuntimeException('Internal Server Error: Invalid Handler configuration');
            }
        } else {
            throw new RuntimeException('Internal Server Error: Malformed Route definition');
        }
        break;

    default:
        throw new RuntimeException('Internal Server Error: Unhandled dispatch state');
        break;
}
