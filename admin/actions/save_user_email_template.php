<?php
// admin/actions/save_user_email_template.php - Action handler to persist template changes
require_once '../../db/db.php';
require_once '../../db/auth_helpers.php';
require_once '../../includes/functions.php';
session_start();

if (!is_module_enabled($pdo, 'users')) {
    http_response_code(403);
    exit('403 Forbidden: The User Management module is currently disabled.');
}

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    http_response_code(405);
    exit('Method Not Allowed');
}
verify_csrf_token();
$current_user = require_permission($pdo, 'manage_users', 'Manage user email templates');

$subject = trim($_POST['subject'] ?? '');
$body = trim($_POST['body'] ?? '');

if (empty($subject) || empty($body)) {
    $_SESSION['error'] = "Subject and body fields cannot be blank.";
    header('Location: ../manage_user_emails.php');
    exit;
}

$stmt = $pdo->prepare("UPDATE user_email_templates SET subject = ?, body = ? WHERE trigger_event = 'user_invitation'");
if ($stmt->execute([$subject, $body])) {
    $_SESSION['message'] = "User invitation email template updated successfully.";
    $audit = $pdo->prepare("INSERT INTO audit_logs (user_id, action, details, ip_address) VALUES (?, 'UPDATE_USER_TEMPLATE', 'Updated user invitation email template layout', ?)");
    $audit->execute([$current_user['id'], $_SERVER['REMOTE_ADDR']]);
} else {
    $_SESSION['error'] = "Failed to update the template database record.";
}

header('Location: ../manage_user_emails.php');
exit;
