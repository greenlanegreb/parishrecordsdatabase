<?php
// admin/actions/save_volunteer.php - Handles volunteer deletions, workflow updates, and trigger emails
require_once '../../db/db.php';
require_once '../../db/auth_helpers.php';
require_once '../../includes/functions.php';
session_start();

if (!is_module_enabled($pdo, 'volunteers')) {
    http_response_code(403);
    exit('403 Forbidden: The Volunteer Portal module is currently disabled.');
}

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    http_response_code(405);
    exit('Method Not Allowed');
}

verify_csrf_token();
$current_user = require_permission($pdo, 'manage_volunteers', 'Manage and review volunteer applications and submissions');

$action = $_POST['action'] ?? '';

if ($action === 'delete_volunteer') {
    $volunteer_id = intval($_POST['volunteer_id'] ?? 0);
    if ($volunteer_id > 0) {
        $del_stmt = $pdo->prepare("DELETE FROM volunteer_submissions WHERE id = ?");
        if ($del_stmt->execute([$volunteer_id])) {
            $_SESSION['message'] = "Volunteer entry #{$volunteer_id} has been successfully deleted.";
            
            $audit = $pdo->prepare("INSERT INTO audit_logs (user_id, action, details, ip_address) VALUES (?, 'DELETE_VOLUNTEER', ?, ?)");
            $audit->execute([$current_user['id'], "Deleted volunteer entry ID #{$volunteer_id}", $_SERVER['REMOTE_ADDR']]);
        } else {
            $_SESSION['error'] = "Failed to delete volunteer entry.";
        }
    }
} elseif ($action === 'update_interview') {
    $volunteer_id = intval($_POST['volunteer_id'] ?? 0);
    $status = trim($_POST['status'] ?? 'Pending Review');
    $interview_date = !empty($_POST['interview_date']) ? $_POST['interview_date'] : null;
    $interview_notes = trim($_POST['interview_notes'] ?? '');

    if ($volunteer_id > 0) {
        $stmt = $pdo->prepare("UPDATE volunteer_submissions SET status = ?, interview_date = ?, interview_notes = ? WHERE id = ?");
        if ($stmt->execute([$status, $interview_date, $interview_notes, $volunteer_id])) {
            $_SESSION['message'] = "Interview details and status updated successfully for submission #{$volunteer_id}.";
            
            // Trigger workflow email templates based on status change
            require_once '../../includes/volunteer_mail_engine.php';
            if ($status === 'Chat Scheduled') {
                send_volunteer_templated_email($pdo, $volunteer_id, 'chat_scheduled');
            } elseif ($status === 'Accepted') {
                send_volunteer_templated_email($pdo, $volunteer_id, 'application_accepted');
            }
        } else {
            $_SESSION['error'] = "Failed to update interview details.";
        }
    }
}

header('Location: ../volunteer_dashboard.php');
exit;
