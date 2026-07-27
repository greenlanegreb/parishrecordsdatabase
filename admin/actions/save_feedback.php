<?php
// admin/actions/save_feedback.php - Handles feedback status updates and deletions
require_once '../../db/db.php';
require_once '../../db/auth_helpers.php';
session_start();

// Enforce strict administrator privileges
require_role($pdo, 'admin');

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
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
            } else {
                $_SESSION['error'] = "Failed to delete feedback entry.";
            }
        }
    }
}

header('Location: ../feedback_dashboard.php');
exit;
