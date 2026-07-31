<?php
// admin/actions/save_feedback_email_template.php - Handles saving feedback email templates
require_once '../../db/db.php';
require_once '../../db/auth_helpers.php';
require_once '../../includes/functions.php';

if (!is_module_enabled($pdo, 'feedback')) {
    http_response_code(403);
    exit('403 Forbidden');
}
if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    http_response_code(405);
    exit('Method Not Allowed');
}

verify_csrf_token();
$current_user = require_permission($pdo, 'manage_feedback', 'Manage feedback email templates');

$template_id = intval($_POST['template_id'] ?? 0);
$subject = trim($_POST['subject'] ?? '');
$body = trim($_POST['body'] ?? '');

if ($template_id > 0 && !empty($subject) && !empty($body)) {
    $stmt = $pdo->prepare("UPDATE feedback_email_templates SET subject = ?, body = ? WHERE id = ?");
    $stmt->execute([$subject, $body, $template_id]);
    
    $_SESSION['message'] = "Feedback email template updated successfully.";
} else {
    $_SESSION['error'] = "Subject and body fields cannot be empty.";
}

header('Location: ../manage_feedback_emails.php');
exit;
