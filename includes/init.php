<?php
// includes/init.php
declare(strict_types=1);

/**
 * APPLICATION INITIALIZATION
 * --------------------------
 * Handles global constants (BASE_PATH), secure session management,
 * and core helper file loading across the entire application lifecycle.
 */

// 1. Calculate and define BASE_PATH globally if not already set
if (!defined('BASE_PATH')) {
    $doc_root = rtrim(str_replace('\\', '/', $_SERVER['DOCUMENT_ROOT'] ?? ''), '/');
    $project_root = rtrim(str_replace('\\', '/', dirname(__DIR__)), '/');
    $base_path = $doc_root !== '' ? str_replace($doc_root, '', $project_root) : '';
    define('BASE_PATH', $base_path);
}

$baseDir = BASE_PATH;

// 2. Setup secure sessions centrally with proper cookie paths
if (session_status() === PHP_SESSION_NONE) {
    session_set_cookie_params([
        'lifetime' => 0,
        'path' => $baseDir !== '' ? $baseDir . '/' : '/',
        'secure' => isset($_SERVER['HTTPS']),
        'httponly' => true,
        'samesite' => 'Lax'
    ]);
    session_start();
}

// 3. Load core utility and formatting functions
$functionsPath = __DIR__ . '/functions.php';
if (is_file($functionsPath)) {
    require_once $functionsPath;
}

// 4. Load database and authorization helpers
$authHelpersPath = __DIR__ . '/../db/auth_helpers.php';
if (is_file($authHelpersPath)) {
    require_once $authHelpersPath;
}

// 4b. Load controller helpers (DRY wrappers)
$controllerHelpersPath = __DIR__ . '/controller_helpers.php';
if (is_file($controllerHelpersPath)) {
    require_once $controllerHelpersPath;
}

// 5. Load security engine / threat defense firewall
$securityEnginePath = __DIR__ . '/security_engine.php';
if (is_file($securityEnginePath)) {
    require_once $securityEnginePath;
}

// 6. Load global database connection ($pdo)
$dbPath = __DIR__ . '/../db/db.php';
if (is_file($dbPath)) {
    require_once $dbPath;
}

// 7. Define universal root server path for clean file includes
if (!defined('ROOT_PATH')) {
    define('ROOT_PATH', dirname(__DIR__));
}
