<?php
declare(strict_types=1);
/**
 * MIGRATED FILE MAPPING
 * ---------------------
 * Original Old File: partials/header.php
 * Migrated Date: 2026-08-05 06:20:00
 */

// Ensure central initialization is loaded (includes BASE_PATH, sessions, functions, auth helpers, and db.php)
$initPath = dirname(__DIR__) . '/includes/init.php';
if (file_exists($initPath)) {
    require_once $initPath;
}

if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

$queryGet = $_GET;
if (isset($queryGet['contrast']) && $queryGet['contrast'] === 'toggle') {
    $_SESSION['high_contrast'] = empty($_SESSION['high_contrast']);
    // Rebuild path without query string so we never loop on ?contrast=toggle
    $uri = isset($_SERVER['REQUEST_URI']) && is_string($_SERVER['REQUEST_URI']) ? $_SERVER['REQUEST_URI'] : '/';
    $path = parse_url($uri, PHP_URL_PATH);
    if (!is_string($path) || $path === '') {
        $path = '/';
    }
    header('Location: ' . $path);
    exit;
}
if (isset($queryGet['lang']) && is_string($queryGet['lang']) && function_exists('set_language')) {
    $requested = preg_replace('/[^a-zA-Z_]/', '', $queryGet['lang']) ?? '';
    $langFile = dirname(__DIR__) . '/lang/' . $requested . '.php';
    if ($requested !== '' && is_file($langFile)) {
        set_language($requested);
    }
    $redirectUrl = strtok($_SERVER['REQUEST_URI'] ?? '/', '?') ?: '/';
    header('Location: ' . $redirectUrl);
    exit;
}

// $pdo is already globally available via init.php
$pdo = $pdo ?? ($GLOBALS['pdo'] ?? null);

$pageTitle = function_exists('get_system_name') && $pdo instanceof PDO ? get_system_name($pdo) : __('header.default_title');

// Use dynamic BASE_PATH constant defined centrally
$baseUrl = defined('BASE_PATH') && is_string(BASE_PATH) ? rtrim(BASE_PATH, '/') : '';
$isHighContrast = isset($_SESSION['high_contrast']) && $_SESSION['high_contrast'];
$langCode = function_exists('get_active_language') ? get_active_language() : 'en';
?>
<!DOCTYPE html>
<html lang="<?= htmlspecialchars($langCode, ENT_QUOTES, 'UTF-8') ?>">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?= htmlspecialchars($pageTitle, ENT_QUOTES, 'UTF-8') ?></title>
    <!-- Bootstrap 5 CSS CDN -->
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet" integrity="sha384-QWTKZyjpPEjISv5WaRU9OFeRpok6YctnYmDr5pNlyT2bRjXh0JMhjY6hW+ALEwIH" crossorigin="anonymous">
    <?php
        // Prefer /public/css when the app is fronted from project root (rewrite to public/index.php)
        $prdCssHref = $baseUrl . '/css/prd.css';
        if (defined('ROOT_PATH') && is_file(ROOT_PATH . '/public/css/prd.css')) {
            $prdCssHref = $baseUrl . '/public/css/prd.css';
        } elseif (is_file(dirname(__DIR__) . '/public/css/prd.css')) {
            $prdCssHref = $baseUrl . '/public/css/prd.css';
        }
    ?>
    <link href="<?= htmlspecialchars($prdCssHref, ENT_QUOTES, 'UTF-8') ?>" rel="stylesheet">
</head>
<body id="page-body" class="<?= $isHighContrast ? 'high-contrast' : '' ?> bg-light d-flex flex-column min-vh-100">
    <a class="visually-hidden-focusable btn btn-sm btn-dark position-absolute top-0 start-0 m-2 z-3" href="#main-content"><?= htmlspecialchars(__('header.skip_to_content') !== 'header.skip_to_content' ? __('header.skip_to_content') : 'Skip to main content', ENT_QUOTES, 'UTF-8') ?></a>
    <?php require_once __DIR__ . '/nav.php'; ?>
    <main id="main-content" class="flex-shrink-0 my-4" role="main" tabindex="-1">
