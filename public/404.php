<?php
// public/404.php (Used by Apache/Nginx server errors)
declare(strict_types=1);

// 1. Calculate BASE_PATH dynamically for subfolder portability
if (!defined('BASE_PATH')) {
    $docRoot = isset($_SERVER['DOCUMENT_ROOT']) && is_string($_SERVER['DOCUMENT_ROOT']) ? rtrim(str_replace('\\', '/', $_SERVER['DOCUMENT_ROOT']), '/') : '';
    $projectRoot = rtrim(str_replace('\\', '/', dirname(__DIR__)), '/');
    $basePath = $docRoot !== '' ? str_replace($docRoot, '', $projectRoot) : '';
    define('BASE_PATH', $basePath);
}

$errorCode = 404;
$errorTitle = 'Page Not Found';
$errorMessage = 'The page, resource, or record you are looking for could not be found or has been removed.';
$basePath = BASE_PATH; // Now correctly dynamic!
$isLocal = false;

if (!headers_sent()) {
    http_response_code($errorCode);
}

$templatePath = dirname(__DIR__) . '/app/Views/errors/error_template.php';
if (is_file($templatePath)) {
    require $templatePath;
} else {
    echo "<h1>404 — Page Not Found</h1>";
}
exit;
