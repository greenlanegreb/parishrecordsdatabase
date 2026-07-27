<?php
// admin/actions/save_volunteer.php - Handles volunteer submission deletions
require_once '../../db/db.php';
require_once '../../db/auth_helpers.php';
session_start();

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    http_response_code(405);
    exit('Method Not Allowed');
}

// Verify CSRF token and enforce dynamic permission check
verify_csrf_token();
$current_user = require_permission($pdo, 'manage_volunteers', 'Manage and review volunteer applications and submissions');

if (isset($_POST['action']) && $_POST['action'] === 'delete_volunteer') {
    $volunteer_id = intval($_POST['volunteer_id'] ?? 0);
    if ($volunteer_id > 0) {
        $del_stmt = $pdo->prepare("DELETE FROM volunteers WHERE id = ?");
        if ($del_stmt->execute([$volunteer_id])) {
            $_SESSION['message'] = "Volunteer entry #{$volunteer_id} has been successfully deleted.";
            
            $audit = $pdo->prepare("INSERT INTO audit_logs (user_id, action, details, ip_address) VALUES (?, 'DELETE_VOLUNTEER', ?, ?)");
            $audit->execute([$current_user['id'], "Deleted volunteer entry ID #{$volunteer_id}", $_SERVER['REMOTE_ADDR']]);
        } else {
            $_SESSION['error'] = "Failed to delete volunteer entry.";
        }
    }
}

header('Location: ../volunteer_dashboard.php');
exit;
