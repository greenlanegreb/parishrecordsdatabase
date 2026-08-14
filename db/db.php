<?php
declare(strict_types=1);
/**
 * db/db.php — committed loader (no secrets).
 * Secrets live in config.local.php (written by the installer, not in git).
 */
$projectRoot = dirname(__DIR__);
$configLocal = $projectRoot . '/config.local.php';
if (is_file($configLocal)) {
    require $configLocal;
    return;
}

$script = isset($_SERVER['SCRIPT_NAME']) && is_string($_SERVER['SCRIPT_NAME'])
    ? str_replace('\\', '/', $_SERVER['SCRIPT_NAME'])
    : '';

if (strpos($script, '/install/') !== false || str_ends_with($script, '/install')) {
    return;
}

$base = str_replace('\\', '/', dirname($script));
// Front controller is under /public; installer is at project-root /install/
if (str_ends_with($base, '/public')) {
    $base = substr($base, 0, -strlen('/public'));
}
if (preg_match('#/(user|admin|api|actions|db|partials|includes)(/|$)#', $base)) {
    $base = dirname($base);
}
$base = rtrim($base, '/');
header('Location: ' . $base . '/install/');
exit;
