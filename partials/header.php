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
</head>
<body id="page-body" class="<?= $isHighContrast ? 'high-contrast' : '' ?> bg-light d-flex flex-column min-vh-100">
    <?php require_once __DIR__ . '/nav.php'; ?>
    <main class="flex-shrink-0 my-4" role="main">
