<?php
declare(strict_types=1);

require_once __DIR__ . '/../vendor/autoload.php';

if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

/** @var callable(FastRoute\RouteCollector): void $routeDefinition */
$routeDefinition = require __DIR__ . '/../routes/web.php';
$dispatcher = FastRoute\simpleDispatcher($routeDefinition);

$server = $_SERVER;
$httpMethod = isset($server['REQUEST_METHOD']) && is_string($server['REQUEST_METHOD']) ? $server['REQUEST_METHOD'] : 'GET';
$uri = isset($server['REQUEST_URI']) && is_string($server['REQUEST_URI']) ? $server['REQUEST_URI'] : '/';

$pos = strpos($uri, '?');
if (false !== $pos) {
    $uri = substr($uri, 0, $pos);
}
$uri = rawurldecode($uri);

$scriptName = isset($server['SCRIPT_NAME']) && is_string($server['SCRIPT_NAME']) ? $server['SCRIPT_NAME'] : '';
$baseDir = dirname($scriptName);
if ($baseDir !== '/' && str_starts_with($uri, $baseDir)) {
    $uri = substr($uri, strlen($baseDir));
}
if ($uri === '') {
    $uri = '/';
}

$routeInfo = $dispatcher->dispatch($httpMethod, $uri);

if (!is_array($routeInfo)) {
    http_response_code(500);
    exit('Internal Server Error');
}

$status = $routeInfo[0] ?? null;

switch ($status) {
    case FastRoute\Dispatcher::NOT_FOUND:
        http_response_code(404);
        echo "404 Not Found";
        break;

    case FastRoute\Dispatcher::METHOD_NOT_ALLOWED:
        http_response_code(405);
        echo "405 Method Not Allowed";
        break;

    case FastRoute\Dispatcher::FOUND:
        $handler = $routeInfo[1] ?? null;
        $vars = $routeInfo[2] ?? [];

        if (is_array($handler) && count($handler) === 2) {
            $class = $handler[0];
            $method = $handler[1];

            if (is_string($class) && is_string($method) && class_exists($class) && method_exists($class, $method)) {
                $parameters = is_array($vars) ? array_values($vars) : [];
                /** @var callable $callable */
                $callable = [$class, $method];
                $callable(...$parameters);
            } else {
                http_response_code(500);
                echo "Internal Server Error: Invalid Handler";
            }
        } else {
            http_response_code(500);
            echo "Internal Server Error: Malformed Route";
        }
        break;

    default:
        http_response_code(500);
        echo "Internal Server Error";
        break;
}
