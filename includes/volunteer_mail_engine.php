<?php
// includes/volunteer_mail_engine.php - Token replacement and email dispatcher for volunteers
require_once __DIR__ . '/../db/mail_helper.php';

function send_volunteer_templated_email(PDO $pdo, int $submission_id, string $trigger_event, string $recipient_email = '') {
    // 1. Fetch the email template for this trigger event
    $stmt = $pdo->prepare("SELECT * FROM volunteer_email_templates WHERE trigger_event = ?");
    $stmt->execute([$trigger_event]);
    $template = $stmt->fetch(PDO::FETCH_ASSOC);

    if (!$template) {
        return false;
    }

    // 2. Fetch the volunteer submission details
    $sub_stmt = $pdo->prepare("SELECT * FROM volunteer_submissions WHERE id = ?");
    $sub_stmt->execute([$submission_id]);
    $submission = $sub_stmt->fetch(PDO::FETCH_ASSOC);

    if (!$submission) {
        return false;
    }

    $to_email = !empty($recipient_email) ? $recipient_email : ($submission['email'] ?? '');
    if (empty($to_email)) {
        return false;
    }

    $system_name = get_system_name($pdo);

    // 3. Build Fixed Core Tags
    $tags = [
        '{first_name}'    => $submission['first_name'] ?? '',
        '{surname}'       => $submission['surname'] ?? '',
        '{email}'         => $submission['email'] ?? '',
        '{submission_id}' => $submission['id'] ?? '',
        '{system_name}'   => $system_name,
        '{status}'        => $submission['status'] ?? 'Pending Review',
    ];

    // 4. Build Custom Schema Tags dynamically from response values
    $val_query = $pdo->prepare("
        SELECT v.value_content, c.column_name 
        FROM volunteer_submission_values v 
        JOIN volunteer_columns c ON v.column_id = c.id 
        WHERE v.submission_id = ?
    ");
    $val_query->execute([$submission_id]);
    $custom_values = $val_query->fetchAll(PDO::FETCH_ASSOC);

    foreach ($custom_values as $cv) {
        $safe_tag = '{' . strtolower(preg_replace('/[^a-zA-Z0-9_]/', '_', trim($cv['column_name']))) . '}';
        $tags[$safe_tag] = $cv['value_content'] ?? '';
    }

    // 5. Parse Subject and Body templates using token replacement
    $tag_keys = array_keys($tags);
    $tag_vals = array_values($tags);

    $subject = str_replace($tag_keys, $tag_vals, $template['subject']);
    $body    = str_replace($tag_keys, $tag_vals, $template['body']);

    // 6. Dispatch using your hybrid mail helper
    return send_user_invitation($pdo, $to_email, '', $subject, $body);
}
