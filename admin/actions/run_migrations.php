<?php
// admin/actions/run_migrations.php - Apply pending schema migrations (admin UI)
require_once '../../db/db.php';
require_once '../../db/auth_helpers.php';
require_once '../../includes/functions.php';
require_once '../../db/migrate_runner.php';

if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    http_response_code(405);
    exit('Method Not Allowed');
}

verify_csrf_token();
require_permission($pdo, 'manage_settings', 'Manage global site settings, mail drivers, and maintenance mode');

try {
    $before = get_schema_version($pdo);
    $result = run_pending_migrations($pdo);

    if (empty($result['applied'])) {
        $_SESSION['message'] = "Database is already up to date (schema version {$result['current']}).";
    } else {
        $lines = implode(' ', $result['applied']);
        $_SESSION['message'] = "Database updated from version {$before} to {$result['current']}. Applied: {$lines}";
    }
} catch (Throwable $e) {
    error_log('Migration failed: ' . $e->getMessage());
    $_SESSION['error'] = "Database update failed: " . $e->getMessage();
}

header('Location: ../settings.php');
exit;
