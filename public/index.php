<?php
declare(strict_types=1);

// public/index.php

// 1. This Helps The Built in Error Log Where Errors are Handled Properly and Securely. 
// See true/false setting in config.local.php to turn on development mode error reporting or see admin dash.
error_reporting(E_ALL);
ini_set('display_errors', '0');
ini_set('display_startup_errors', '0');
ini_set('log_errors', '1');


// 2. REGISTER THE CENTRALIZED ERROR HANDLER FIRST
require_once __DIR__ . '/../includes/error_handler.php';
register_global_error_handlers(__DIR__ . '/../logs');

// 3. VENDOR AUTOLOAD
require_once __DIR__ . '/../vendor/autoload.php';

// 4. APPLICATION INITIALIZATION (Loads BASE_PATH, sessions, functions, auth helpers, security, and $pdo)
require_once __DIR__ . '/../includes/init.php';

//4a Dismiss Notices Move from partials/header.php

// public/index.php — after require init.php, before FastRoute
if (isset($_GET['dismiss_notice'])) {
    $dismissId = (int) $_GET['dismiss_notice'];
    if ($dismissId > 0) {
        if (!isset($_SESSION['dismissed_notices']) || !is_array($_SESSION['dismissed_notices'])) {
            $_SESSION['dismissed_notices'] = [];
        }
        $_SESSION['dismissed_notices'][$dismissId] = true;
    }
    $uri = isset($_SERVER['REQUEST_URI']) && is_string($_SERVER['REQUEST_URI'])
        ? $_SERVER['REQUEST_URI'] : '/';
    $clean = strtok($uri, '?') ?: '/';
    header('Location: ' . $clean);
    exit;
}

// 5. NORMALIZE URI FOR GLOBAL GATEKEEPING
$server = $_SERVER;
$httpMethod = isset($server['REQUEST_METHOD']) && is_string($server['REQUEST_METHOD']) ? $server['REQUEST_METHOD'] : 'GET';
$uri = isset($server['REQUEST_URI']) && is_string($server['REQUEST_URI']) ? $server['REQUEST_URI'] : '/';

$pos = strpos($uri, '?');
if (false !== $pos) {
    $uri = substr($uri, 0, $pos);
}
$uri = rawurldecode($uri);

// 6. Safe subfolder prefix stripping using $baseDir provided by init.php
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


// 7. SCHEMA STATUS (soft — do not lock the site for pending migrations)
// Pending migrations are applied by an admin via Settings / update-database after backup.
// A hard site-wide redirect here caused lockouts on routine migrations and exposed
// the update gateway to anonymous users.
$schemaBehind = false;
if (is_file(__DIR__ . '/../db/migrate_runner.php') && isset($pdo) && $pdo instanceof PDO) {
    require_once __DIR__ . '/../db/migrate_runner.php';
    try {
        if (function_exists('get_schema_version')) {
            $currentSchema = get_schema_version($pdo);
            $latestSchema = $currentSchema;
            $migrationsDir = __DIR__ . '/../db/migrations';
            if (is_dir($migrationsDir)) {
                $globFiles = glob($migrationsDir . '/*.php');
                if ($globFiles !== false) {
                    foreach ($globFiles as $migFile) {
                        $m = [];
                        if (preg_match('/(\d+)_/', basename($migFile), $m)) {
                            $latestSchema = max($latestSchema, (int) $m[1]);
                        }
                    }
                }
            }
            $schemaBehind = ($currentSchema < $latestSchema);
            // Available later for an admin banner if you want:
            // $GLOBALS['schema_behind'] = $schemaBehind;
        }
    } catch (\Throwable $e) {
        // DB not ready / serious failure — do not redirect the public to the updater.
        // Fresh installs are handled by db/db.php → /install/.
        // Admins can still open /update-database when authenticated.
    }
}

// 8. FASTROUTE DISPATCHING
/** @var callable(FastRoute\RouteCollector): void $routeDefinition */
$routeDefinition = require __DIR__ . '/../routes/web.php';
$dispatcher = FastRoute\simpleDispatcher($routeDefinition);

$routeInfo = $dispatcher->dispatch($httpMethod, $uri);

if (!is_array($routeInfo)) {
    throw new RuntimeException('Internal Server Error: Malformed dispatch response');
}

$status = $routeInfo[0] ?? null;

// 9. Helper closure to render the shared error view cleanly for 404/405
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
