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
if (isset($queryGet['zoom']) && is_string($queryGet['zoom'])) {
    $steps = [90, 100, 115, 130, 150];
    $cur = isset($_SESSION['prd_zoom']) ? (int) $_SESSION['prd_zoom'] : 100;
    if (!in_array($cur, $steps, true)) {
        $cur = 100;
    }
    $idx = (int) array_search($cur, $steps, true);
    if ($queryGet['zoom'] === 'up' && $idx < count($steps) - 1) {
        $cur = $steps[$idx + 1];
    } elseif ($queryGet['zoom'] === 'down' && $idx > 0) {
        $cur = $steps[$idx - 1];
    } elseif ($queryGet['zoom'] === 'reset') {
        $cur = 100;
    }
    $_SESSION['prd_zoom'] = $cur;
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
$prdZoom = isset($_SESSION['prd_zoom']) ? (int) $_SESSION['prd_zoom'] : 100;
if (!in_array($prdZoom, [90, 100, 115, 130, 150], true)) {
    $prdZoom = 100;
}
$langCode = function_exists('get_active_language') ? get_active_language() : 'en';
?>
<!DOCTYPE html>
<?php
$prdRtl = in_array(strtolower((string) $langCode), ['ar', 'fa', 'he', 'ur', 'ps', 'prs', 'yi', 'ckb', 'dv'], true);
?>
<html lang="<?= htmlspecialchars($langCode, ENT_QUOTES, 'UTF-8') ?>" dir="<?= $prdRtl ? 'rtl' : 'ltr' ?>" class="prd-zoom-<?= (int) $prdZoom ?>">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?= htmlspecialchars($pageTitle, ENT_QUOTES, 'UTF-8') ?></title>
    <!-- Bootstrap 5 CSS CDN -->
    <?php if (!empty($prdRtl)): ?>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.rtl.min.css" rel="stylesheet" crossorigin="anonymous">
    <?php else: ?>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet" integrity="sha384-QWTKZyjpPEjISv5WaRU9OFeRpok6YctnYmDr5pNlyT2bRjXh0JMhjY6hW+ALEwIH" crossorigin="anonymous">
    <?php endif; ?>
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
    <?php
    if (isset($pdo) && $pdo instanceof PDO) {
        try {
            $prdAppear = new \App\Services\AppearanceService($pdo);
            $prdAppearCss = $prdAppear->cssVariables();
        } catch (\Throwable $e) {
            $prdAppearCss = '';
        }
        if ($prdAppearCss !== '') {
            echo '<style id="prd-appearance">body:not(.high-contrast){'
                . $prdAppearCss . ';'
                . 'background-color:var(--prd-page-bg);'
                . 'font-family:var(--prd-font);font-size:calc(var(--prd-font-size,1rem) * var(--prd-zoom,1));letter-spacing:var(--prd-letter-spacing);}'
                . 'body:not(.high-contrast) #main-content{color:var(--prd-text);}'
                . 'body:not(.high-contrast) #main-content a{color:var(--prd-link);}'
                . 'body:not(.high-contrast) #main-content .dropdown-menu a.dropdown-item{color:#343a40!important;text-decoration:none;}'
                . 'body:not(.high-contrast) #main-content .dropdown-menu .dropdown-item.text-danger{color:#b02a37!important;}'
                . 'body:not(.high-contrast) .navbar.navbar-light{background-color:var(--prd-header-bg);box-shadow:none;border-bottom:1px solid var(--prd-border,#dee2e6);}'
                . 'body:not(.high-contrast) .navbar .nav-link{color:var(--prd-header-text);font-size:.95rem;padding:.45rem .7rem;}'
                . 'body:not(.high-contrast) .navbar-brand{font-weight:600;}'
                . 'body:not(.high-contrast) .btn-primary{background-color:var(--prd-button);border-color:var(--prd-button);color:var(--prd-button-text);}'
                . 'body:not(.high-contrast) footer,.site-footer,.prd-site-footer{background-color:var(--prd-footer-bg)!important;color:var(--prd-footer-text);}'
                . 'body:not(.high-contrast) footer a,.site-footer a,.prd-site-footer a{color:var(--prd-footer-link);}'
                . 'body:not(.high-contrast) .site-footer .text-muted{color:var(--prd-footer-text)!important;}'
                . '</style>';
        }
    }
    ?>
</head>
<body id="page-body" class="<?= $isHighContrast ? 'high-contrast' : '' ?> bg-light d-flex flex-column min-vh-100">
    <a class="visually-hidden-focusable btn btn-sm btn-dark position-absolute top-0 start-0 m-2 z-3" href="#main-content"><?= htmlspecialchars(__('header.skip_to_content') !== 'header.skip_to_content' ? __('header.skip_to_content') : 'Skip to main content', ENT_QUOTES, 'UTF-8') ?></a>
    <?php require_once __DIR__ . '/nav.php'; ?>
    <main id="main-content" class="flex-shrink-0 my-4" role="main" tabindex="-1">
