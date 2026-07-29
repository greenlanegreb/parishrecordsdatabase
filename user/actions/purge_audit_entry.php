<?php
// user/actions/purge_audit_entry.php - Handles secure deletion of a single audit log entry
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

// Self-discovers and enforces the permission key in the database matrix
$current_user = require_permission($pdo, 'purge_audit_entry', 'Allows purging individual audit log entries from record history');

$audit_id = intval($_POST['audit_id'] ?? 0);
$record_id = intval($_POST['record_id'] ?? 0);

if (!$audit_id || !$record_id) {
    $_SESSION['error'] = "Invalid audit or record specified.";
    header('Location: ../../index.php');
    exit;
}

try {
    $stmt = $pdo->prepare("DELETE FROM audit_logs WHERE id = ?");
    $stmt->execute([$audit_id]);

    $_SESSION['message'] = "Audit log entry successfully purged.";
} catch (Exception $e) {
    $_SESSION['error'] = "Failed to purge audit entry: " . $e->getMessage();
}

header('Location: ../../record_history.php?record_id=' . $record_id);
exit;
