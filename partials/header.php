<?php
// partials/header.php - Global Document Head & Header Setup
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

$page_title = function_exists('get_system_name') && isset($pdo) ? get_system_name($pdo) : 'Parish Records Database';

// Use dynamic BASE_PATH constant defined in db.php (defaulting to empty string if root-hosted)
$base_url = defined('BASE_PATH') ? BASE_PATH : '';
$is_high_contrast = $_SESSION['high_contrast'] ?? false;
$stylesheet_file = $is_high_contrast ? 'high-contrast.css' : 'style.css';
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?php echo htmlspecialchars($page_title); ?></title>
    <link rel="stylesheet" href="<?php echo $base_url; ?>/assets/css/<?php echo $stylesheet_file; ?>">
</head>
<body id="page-body" class="<?php echo $is_high_contrast ? 'high-contrast' : ''; ?>">
    <?php require_once __DIR__ . '/nav.php'; ?>
