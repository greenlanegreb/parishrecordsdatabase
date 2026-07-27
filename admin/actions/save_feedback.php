<?php
// admin/actions/save_feedback.php - Handles feedback status updates and deletions
require_once '../../db/db.php';
require_once '../../db/auth_helpers.php';
require_once '../../includes/functions.php';
session_start();

// Ensure the feedback module is enabled; otherwise block action execution
if (!is_module_enabled($pdo, 'feedback')) {
    http_response_code(403);
    exit('403 Forbidden: The Feedback Submissions module is currently disabled.');
}

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    http_response_code(405);
    exit('Method Not Allowed');
}

// Verify CSRF token and enforce permission check
verify_csrf_token();
$current_user = require_permission($pdo, 'manage_feedback', 'Manage and moderate public feedback and submissions');

$action = $_POST['action'] ?? '';

if ($action === 'update_feedback') {
    $feedback_id = intval($_POST['feedback_id'] ?? 0);
    $status = trim($_POST['status'] ?? 'Pending');
    $admin_notes = trim($_POST['admin_notes'] ?? '');

    $allowed_statuses = ['Pending', 'Completed', 'Revised from Proposal', 'Rejected'];
    if (!in_array($status, $allowed_statuses)) {
        $status = 'Pending';
    }

    if ($feedback_id > 0) {
        $update_stmt = $pdo->prepare("UPDATE feedback SET status = ?, admin_notes = ? WHERE id = ?");
        if ($update_stmt->execute([$status, $admin_notes, $feedback_id])) {
            $_SESSION['message'] = "Feedback entry #{$feedback_id} has been successfully updated.";
            
            $audit = $pdo->prepare("INSERT INTO audit_logs (user_id, action, details, ip_address) VALUES (?, 'UPDATE_FEEDBACK', ?, ?)");
            $audit->execute([$current_user['id'], "Updated feedback entry #{$feedback_id} status to {$status}", $_SERVER['REMOTE_ADDR']]);
        } else {
            $_SESSION['error'] = "Failed to update feedback entry.";
        }
    }
} elseif ($action === 'delete_feedback') {
    $feedback_id = intval($_POST['feedback_id'] ?? 0);
    if ($feedback_id > 0) {
        $del_stmt = $pdo->prepare("DELETE FROM feedback WHERE id = ?");
        if ($del_stmt->execute([$feedback_id])) {
            $_SESSION['message'] = "Feedback entry #{$feedback_id} has been successfully deleted.";
            
            $audit = $pdo->prepare("INSERT INTO audit_logs (user_id, action, details, ip_address) VALUES (?, 'DELETE_FEEDBACK', ?, ?)");
            $audit->execute([$current_user['id'], "Deleted feedback entry #{$feedback_id}", $_SERVER['REMOTE_ADDR']]);
        } else {
            $_SESSION['error'] = "Failed to delete feedback entry.";
        }
    }
}

header('Location: ../feedback_dashboard.php');
exit;
