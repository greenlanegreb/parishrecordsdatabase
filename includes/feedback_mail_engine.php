<?php
// includes/feedback_mail_engine.php - Token replacement and email dispatcher for feedback tickets
require_once __DIR__ . '/../db/mail_helper.php';

function send_feedback_templated_email(PDO $pdo, int $ticket_id, string $trigger_event, string $recipient_email = '') {
    $stmt = $pdo->prepare("SELECT * FROM feedback_email_templates WHERE trigger_event = ?");
    $stmt->execute([$trigger_event]);
    $template = $stmt->fetch(PDO::FETCH_ASSOC);

    if (!$template) {
        return false;
    }

    $t_stmt = $pdo->prepare("SELECT * FROM feedback_tickets WHERE id = ?");
    $t_stmt->execute([$ticket_id]);
    $ticket = $t_stmt->fetch(PDO::FETCH_ASSOC);

    if (!$ticket) {
        return false;
    }

    $to_email = !empty($recipient_email) ? $recipient_email : ($ticket['email'] ?? '');
    if (empty($to_email)) {
        return false;
    }

    $system_name = get_system_name($pdo);

    $tags = [
        '{first_name}'  => $ticket['first_name'] ?? '',
        '{surname}'     => $ticket['surname'] ?? '',
        '{email}'       => $ticket['email'] ?? '',
        '{ticket_id}'   => $ticket['id'] ?? '',
        '{subject}'     => $ticket['subject'] ?? '',
        '{status}'      => $ticket['status'] ?? 'Pending',
        '{system_name}' => $system_name,
    ];

    $val_query = $pdo->prepare("
        SELECT v.value_content, c.column_name 
        FROM feedback_ticket_values v 
        JOIN feedback_columns c ON v.column_id = c.id 
        WHERE v.ticket_id = ?
    ");
    $val_query->execute([$ticket_id]);
    $custom_values = $val_query->fetchAll(PDO::FETCH_ASSOC);

    foreach ($custom_values as $cv) {
        $safe_tag = '{' . strtolower(preg_replace('/[^a-zA-Z0-9_]/', '_', trim($cv['column_name']))) . '}';
        $tags[$safe_tag] = $cv['value_content'] ?? '';
    }

    $tag_keys = array_keys($tags);
    $tag_vals = array_values($tags);

    $subject = str_replace($tag_keys, $tag_vals, $template['subject']);
    $body    = str_replace($tag_keys, $tag_vals, $template['body']);

    return send_user_invitation($pdo, $to_email, '', $subject, $body);
}
