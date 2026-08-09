<?php
// public/403.php
declare(strict_types=1);

// 1. Bootstrap the centralized environment (automatically defines BASE_PATH)
require_once __DIR__ . '/../includes/init.php';

// 2. Set variables for the pure view template
$errorCode = 403;
$errorTitle = 'Access Forbidden';
$errorMessage = 'You do not have the necessary permissions or administrative privileges to view this resource.';
$basePath = BASE_PATH;
$isLocal = false; // Static error pages default to production-safe view

// 3. Set HTTP response code
if (!headers_sent()) {
    http_response_code($errorCode);
}

// 4. Locate and load the pure view template portably
$templatePath = dirname(__DIR__) . '/app/Views/errors/error_template.php';

if (is_file($templatePath)) {
    require $templatePath;
} else {
    echo "<h1>{$errorCode} — {$errorTitle}</h1><p>{$errorMessage}</p>";
}
exit;
