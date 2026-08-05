<?php
declare(strict_types=1);
/**
 * MIGRATED FILE MAPPING
 * ---------------------
 * Original Old File: partials/header.php
 * Migrated Date: 2026-08-05 06:20:00
 */

if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

// Ensure database connection and auth helpers are available for dynamic title and BASE_PATH
if (!isset($pdo)) {
    @include_once __DIR__ . '/../db/db.php';
}
if (!function_exists('get_system_name') && file_exists(__DIR__ . '/../db/auth_helpers.php')) {
    include_once __DIR__ . '/../db/auth_helpers.php';
}

$pageTitle = function_exists('get_system_name') && isset($pdo) && $pdo instanceof PDO ? get_system_name($pdo) : __('header.default_title');

// Use dynamic BASE_PATH constant defined in db.php (defaulting to empty string if root-hosted)
$baseUrl = defined('BASE_PATH') && is_string(BASE_PATH) ? rtrim(BASE_PATH, '/') : '';
$isHighContrast = isset($_SESSION['high_contrast']) && $_SESSION['high_contrast'];
$stylesheetFile = $isHighContrast ? 'high-contrast.css' : 'style.css';
?>
<!DOCTYPE html>
<html lang="<?= htmlspecialchars(get_active_language(), ENT_QUOTES, 'UTF-8') ?>">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?= htmlspecialchars($pageTitle, ENT_QUOTES, 'UTF-8') ?></title>
    <!-- Bootstrap 5 CSS CDN -->
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet" integrity="sha384-QWTKZyjpPEjISv5WaRU9OFeRpok6YctnYmDr5pNlyT2bRjXh0JMhjY6hW+ALEwIH" crossorigin="anonymous">
    <!-- Custom Application Stylesheet -->
    <link rel="stylesheet" href="<?= htmlspecialchars($baseUrl, ENT_QUOTES, 'UTF-8') ?>/assets/css/<?= htmlspecialchars($stylesheetFile, ENT_QUOTES, 'UTF-8') ?>">
</head>
<body id="page-body" class="<?= $isHighContrast ? 'high-contrast' : '' ?> bg-light d-flex flex-column min-vh-100">
    <?php require_once __DIR__ . '/nav.php'; ?>
    <main class="flex-shrink-0 my-4" role="main">
