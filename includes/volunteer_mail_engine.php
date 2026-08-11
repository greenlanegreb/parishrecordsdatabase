<?php
declare(strict_types=1);
/**
 * MIGRATED FILE MAPPING
 * ---------------------
 * Original Old File: includes/volunteer_mail_engine.php
 * Migrated Date: 2026-08-04 18:30:00
 */

require_once __DIR__ . '/templated_mail.php';

/**
 * Sends a templated volunteer submission email using dynamic token replacement.
 */
function send_volunteer_templated_email(
    PDO $pdo,
    int $submissionId,
    string $triggerEvent,
    string $recipientEmail = ''
): bool {
    $stmt = $pdo->prepare('SELECT * FROM volunteer_email_templates WHERE trigger_event = ?');
    $stmt->execute([$triggerEvent]);
    /** @var array{id: int|string, subject: string, body: string}|false $template */
    $template = $stmt->fetch(PDO::FETCH_ASSOC);
    if ($template === false) {
        return false;
    }

    $subStmt = $pdo->prepare('SELECT * FROM volunteer_submissions WHERE id = ?');
    $subStmt->execute([$submissionId]);
    /** @var array{id: int|string, first_name?: string, surname?: string, email?: string, status?: string, preferred_username?: string}|false $submission */
    $submission = $subStmt->fetch(PDO::FETCH_ASSOC);
    if ($submission === false) {
        return false;
    }

    $subEmail = isset($submission['email']) && is_string($submission['email']) ? $submission['email'] : '';
    $toEmail = ($recipientEmail !== '') ? $recipientEmail : $subEmail;
    if ($toEmail === '') {
        return false;
    }

    $firstName = isset($submission['first_name']) && is_string($submission['first_name']) ? $submission['first_name'] : '';
    $surname = isset($submission['surname']) && is_string($submission['surname']) ? $submission['surname'] : '';
    $status = isset($submission['status']) && is_string($submission['status']) ? $submission['status'] : 'Pending Review';
    $preferred = isset($submission['preferred_username']) && is_string($submission['preferred_username'])
        ? $submission['preferred_username'] : '';

    $tags = [
        '{first_name}'          => $firstName,
        '{surname}'             => $surname,
        '{email}'               => $subEmail,
        '{submission_id}'       => (string) $submission['id'],
        '{system_name}'         => get_system_name($pdo),
        '{status}'              => $status,
        '{preferred_username}'  => $preferred,
        '{username}'            => $preferred,
    ];

    $tags = array_merge(
        $tags,
        dynamic_field_tags(
            $pdo,
            'volunteer_submission_values',
            'volunteer_columns',
            'submission_id',
            $submissionId
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
