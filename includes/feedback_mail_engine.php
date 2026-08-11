<?php
declare(strict_types=1);
/**
 * MIGRATED FILE MAPPING
 * ---------------------
 * Original Old File: includes/feedback_mail_engine.php
 * Migrated Date: 2026-08-04 17:00:00
 */

require_once __DIR__ . '/templated_mail.php';

/**
 * Sends a templated feedback ticket email using dynamic token replacement.
 */
function send_feedback_templated_email(
    PDO $pdo,
    int $ticketId,
    string $triggerEvent,
    string $recipientEmail = ''
): bool {
    $stmt = $pdo->prepare('SELECT * FROM feedback_email_templates WHERE trigger_event = ?');
    $stmt->execute([$triggerEvent]);
    /** @var array{id: int|string, subject: string, body: string}|false $template */
    $template = $stmt->fetch(PDO::FETCH_ASSOC);
    if ($template === false) {
        return false;
    }

    $tStmt = $pdo->prepare('SELECT * FROM feedback_tickets WHERE id = ?');
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

    $firstName = isset($ticket['first_name']) && is_string($ticket['first_name']) ? $ticket['first_name'] : '';
    $surname = isset($ticket['surname']) && is_string($ticket['surname']) ? $ticket['surname'] : '';
    $ticketSubj = isset($ticket['subject']) && is_string($ticket['subject']) ? $ticket['subject'] : '';
    $ticketStatus = isset($ticket['status']) && is_string($ticket['status']) ? $ticket['status'] : 'Pending';

    $tags = [
        '{first_name}'  => $firstName,
        '{surname}'     => $surname,
        '{email}'       => $ticketEmail,
        '{ticket_id}'   => (string) $ticket['id'],
        '{subject}'     => $ticketSubj,
        '{status}'      => $ticketStatus,
        '{system_name}' => get_system_name($pdo),
    ];

    $tags = array_merge(
        $tags,
        dynamic_field_tags(
            $pdo,
            'feedback_ticket_values',
            'feedback_columns',
            'ticket_id',
            $ticketId
        )
    );

    return send_templated_mail(
        $pdo,
        $toEmail,
        (string) $template['subject'],
        (string) $template['body'],
        $tags,
        $triggerEvent
    );
}
