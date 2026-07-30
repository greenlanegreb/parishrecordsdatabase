<?php
// admin/actions/save_ticket_reply.php - Handles replies, emails, and ticket statuses
require_once '../../db/db.php';
require_once '../../db/auth_helpers.php';
require_once '../../includes/functions.php';
session_start();

if (!is_module_enabled($pdo, 'feedback')) {
    http_response_code(403);
    exit('403 Forbidden');
}
if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    http_response_code(405);
    exit('Method Not Allowed');
}

verify_csrf_token();
$current_user = require_permission($pdo, 'manage_feedback', 'Manage feedback replies');
$action = $_POST['action'] ?? '';
$ticket_id = intval($_POST['ticket_id'] ?? 0);

if ($ticket_id <= 0) {
    header('Location: ../feedback_dashboard.php');
    exit;
}

$t_stmt = $pdo->prepare("SELECT * FROM feedback_tickets WHERE id = ?");
$t_stmt->execute([$ticket_id]);
$ticket = $t_stmt->fetch(PDO::FETCH_ASSOC);

if (!$ticket) {
    header('Location: ../feedback_dashboard.php');
    exit;
}

$system_name = get_system_name($pdo);

if ($action === 'update_status') {
    $status = trim($_POST['status'] ?? 'Pending');
    $pdo->prepare("UPDATE feedback_tickets SET status = ? WHERE id = ?")->execute([$status, $ticket_id]);
    $_SESSION['message'] = "Ticket status updated to {$status}.";
} elseif ($action === 'post_reply') {
    $message = trim($_POST['reply_message'] ?? '');
    if (!empty($message)) {
        $stmt = $pdo->prepare("INSERT INTO feedback_ticket_replies (ticket_id, user_id, message, is_admin_reply) VALUES (?, ?, ?, 1)");
        $stmt->execute([$ticket_id, $current_user['id'], $message]);

        // Send email notification to submitter
        $to = $ticket['email'];
        $subject = "[{$system_name}] Update on Ticket #{$ticket_id}";
        $body = "Hello {$ticket['name']},\n\nAn administrator has posted a reply to your support ticket (#{$ticket_id}):\n\n{$message}\n\nBest regards,\n{$system_name}";
        $headers = "From: no-reply@" . parse_url($_SERVER['HTTP_HOST'], PHP_URL_HOST) . "\r\n" .
                   "Reply-To: no-reply@" . parse_url($_SERVER['HTTP_HOST'], PHP_URL_HOST) . "\r\n" .
                   "X-Mailer: PHP/" . phpversion();
        
        @mail($to, $subject, $body, $headers);
        $_SESSION['message'] = "Reply sent and email notification dispatched successfully.";
    }
}

header('Location: ../view_ticket.php?id=' . $ticket_id);
exit;
