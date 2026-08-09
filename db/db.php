<?php
// db/db.php - Loader for config.local.php
$projectRoot = dirname(__DIR__);
$configLocal = $projectRoot . '/config.local.php';

if (is_file($configLocal)) {
    require $configLocal;
    return;
}

$script = str_replace('\\', '/', $_SERVER['SCRIPT_NAME'] ?? '');
if (strpos($script, '/install/') !== false || str_ends_with($script, '/install')) {
    return;
}

$base = str_replace('\\', '/', dirname($script));
if (preg_match('#/(user|admin|api|actions|db|partials|includes)(/|$)#', $base)) {
    $base = dirname($base);
}
$base = rtrim($base, '/');
header('Location: ' . $base . '/install/');
exit;
