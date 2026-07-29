<?php
// admin/actions/purge_audit_logs.php - Handles audit log clearing operations
require_once '../../db/db.php';
require_once '../../db/auth_helpers.php';
require_once '../../includes/functions.php';

if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    http_response_code(405);
    exit('Method Not Allowed');
}

verify_csrf_token();
require_admin_page($pdo, 'manage_settings', 'Manage global site settings');

$purge_type = $_POST['purge_type'] ?? '';

try {
    if ($purge_type === 'all') {
        $pdo->exec("DELETE FROM audit_logs");
        $_SESSION['message'] = "The entire audit log has been successfully cleared.";
    } elseif ($purge_type === 'records_only') {
        // Targets actions typically associated with core records like INSERT, PURGE_RECORD, etc.
        $stmt = $pdo->prepare("DELETE FROM audit_logs WHERE action IN ('INSERT', 'PURGE_RECORD', 'EDIT_SUGGESTION', 'APPROVE_SUGGESTION', 'REJECT_SUGGESTION')");
        $stmt->execute();
        $_SESSION['message'] = "All records-related audit logs have been successfully cleared.";
    } elseif (!empty($purge_type)) {
        // Purge by a specific action type (e.g. LOGIN, UPDATE, etc.)
        $stmt = $pdo->prepare("DELETE FROM audit_logs WHERE action = ?");
        $stmt->execute([$purge_type]);
        $_SESSION['message'] = "Audit logs for action type '{$purge_type}' have been successfully cleared.";
    } else {
        $_SESSION['error'] = "Invalid purge action specified.";
    }
} catch (Exception $e) {
    $_SESSION['error'] = "Failed to clear audit logs: " . $e->getMessage();
}

header('Location: ../settings.php#tab-audit');
exit;
