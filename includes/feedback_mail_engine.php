<?php
declare(strict_types=1);
/**
 * MIGRATED FILE MAPPING
 * ---------------------
 * Original Old File: includes/feedback_mail_engine.php
 * Migrated Date: 2026-08-04 17:00:00
 */

require_once __DIR__ . '/../db/mail_helper.php';

/**
 * Sends a templated feedback ticket email using dynamic token replacement.
 *
 * @param PDO $pdo Database connection
 * @param int $ticketId Feedback ticket ID
 * @param string $triggerEvent Event trigger identifier (e.g., 'ticket_received')
 * @param string $recipientEmail Optional target email address (defaults to ticket owner email)
 * @return bool True on successful dispatch, false otherwise
 */
function send_feedback_templated_email(PDO $pdo, int $ticketId, string $triggerEvent, string $recipientEmail = ''): bool
{
    $stmt = $pdo->prepare("SELECT * FROM feedback_email_templates WHERE trigger_event = ?");
    $stmt->execute([$triggerEvent]);
    /** @var array{id: int|string, subject: string, body: string}|false $template */
    $template = $stmt->fetch(PDO::FETCH_ASSOC);

    if ($template === false) {
        return false;
    }

    $tStmt = $pdo->prepare("SELECT * FROM feedback_tickets WHERE id = ?");
    $tStmt->execute([$ticketId]);
    /** @var array{id: int|string, first_name?: string, surname?: string, email?: string, subject?: string, status?: string}|false $ticket */
    $ticket = $tStmt->fetch(PDO::FETCH_ASSOC);

    if ($ticket === false) {
        return false;
    }

    $ticketEmail = isset($ticket['email']) && is_string($ticket['email']) ? $ticket['email'] : '';
    $toEmail = ($recipientEmail !== '') ? $recipientEmail : $ticketEmail;
    if ($toEmail === '') {
        return false;
    }

    $systemName = get_system_name($pdo);

    $firstName = isset($ticket['first_name']) && is_string($ticket['first_name']) ? $ticket['first_name'] : '';
    $surname = isset($ticket['surname']) && is_string($ticket['surname']) ? $ticket['surname'] : '';
    $ticketSubj = isset($ticket['subject']) && is_string($ticket['subject']) ? $ticket['subject'] : '';
    $ticketStatus = isset($ticket['status']) && is_string($ticket['status']) ? $ticket['status'] : 'Pending';

    /** @var array<string, string> $tags */
    $tags = [
        '{first_name}'  => $firstName,
        '{surname}'     => $surname,
        '{email}'       => $ticketEmail,
        '{ticket_id}'   => (string)$ticket['id'],
        '{subject}'     => $ticketSubj,
        '{status}'      => $ticketStatus,
        '{system_name}' => $systemName,
    ];

    $valQuery = $pdo->prepare("
        SELECT v.value_content, c.column_name 
        FROM feedback_ticket_values v 
        JOIN feedback_columns c ON v.column_id = c.id 
        WHERE v.ticket_id = ?
    ");
    $valQuery->execute([$ticketId]);
    /** @var array<int, array<string, mixed>> $customValues */
    $customValues = $valQuery->fetchAll(PDO::FETCH_ASSOC);

    foreach ($customValues as $cv) {
        $colName = isset($cv['column_name']) && is_string($cv['column_name']) ? $cv['column_name'] : '';
        $valCont = isset($cv['value_content']) && is_string($cv['value_content']) ? $cv['value_content'] : '';

        $cleanedColName = preg_replace('/[^a-zA-Z0-9_]/', '_', trim($colName));
        $safeTag = '{' . strtolower($cleanedColName !== null ? $cleanedColName : 'field') . '}';
        $tags[$safeTag] = $valCont;
    }

    /** @var array<int, string> $tagKeys */
    $tagKeys = array_keys($tags);
    /** @var array<int, string> $tagVals */
    $tagVals = array_values($tags);

    $subject = str_replace($tagKeys, $tagVals, $template['subject']);
    $body = str_replace($tagKeys, $tagVals, $template['body']);

    return send_user_invitation(
        $pdo,
        $toEmail,
        '',
        [
            'first_name' => $firstName,
            'surname'    => $surname,
            'username'   => '',
            'role_name'  => '',
        ],
        $triggerEvent,
        $subject,
        $body
    );
}
