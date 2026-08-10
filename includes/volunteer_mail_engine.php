<?php
declare(strict_types=1);
/**
 * MIGRATED FILE MAPPING
 * ---------------------
 * Original Old File: includes/volunteer_mail_engine.php
 * Migrated Date: 2026-08-04 18:30:00
 */

require_once __DIR__ . '/../db/mail_helper.php';

/**
 * Sends a templated volunteer submission email using dynamic token replacement.
 *
 * @param PDO $pdo Database connection
 * @param int $submissionId Volunteer submission ID
 * @param string $triggerEvent Event trigger identifier (e.g., 'submission_received')
 * @param string $recipientEmail Optional target email address (defaults to submitter email)
 * @return bool True on successful dispatch, false otherwise
 */
function send_volunteer_templated_email(PDO $pdo, int $submissionId, string $triggerEvent, string $recipientEmail = ''): bool
{
    // 1. Fetch the email template for this trigger event
    $stmt = $pdo->prepare("SELECT * FROM volunteer_email_templates WHERE trigger_event = ?");
    $stmt->execute([$triggerEvent]);
    /** @var array{id: int|string, subject: string, body: string}|false $template */
    $template = $stmt->fetch(PDO::FETCH_ASSOC);

    if ($template === false) {
        return false;
    }

    // 2. Fetch the volunteer submission details
    $subStmt = $pdo->prepare("SELECT * FROM volunteer_submissions WHERE id = ?");
    $subStmt->execute([$submissionId]);
    /** @var array{id: int|string, first_name?: string, surname?: string, email?: string, status?: string}|false $submission */
    $submission = $subStmt->fetch(PDO::FETCH_ASSOC);

    if ($submission === false) {
        return false;
    }

    $subEmail = isset($submission['email']) && is_string($submission['email']) ? $submission['email'] : '';
    $toEmail = ($recipientEmail !== '') ? $recipientEmail : $subEmail;
    if ($toEmail === '') {
        return false;
    }

    $systemName = get_system_name($pdo);

    $firstName = isset($submission['first_name']) && is_string($submission['first_name']) ? $submission['first_name'] : '';
    $surname = isset($submission['surname']) && is_string($submission['surname']) ? $submission['surname'] : '';
    $status = isset($submission['status']) && is_string($submission['status']) ? $submission['status'] : 'Pending Review';

    // 3. Build Fixed Core Tags
    /** @var array<string, string> $tags */
    $tags = [
        '{first_name}'    => $firstName,
        '{surname}'       => $surname,
        '{email}'         => $subEmail,
        '{submission_id}' => (string)$submission['id'],
        '{system_name}'   => $systemName,
        '{status}'        => $status,
    ];

    // 4. Build Custom Schema Tags dynamically from response values
    $valQuery = $pdo->prepare("
        SELECT v.value_content, c.column_name 
        FROM volunteer_submission_values v 
        JOIN volunteer_columns c ON v.column_id = c.id 
        WHERE v.submission_id = ?
    ");
    $valQuery->execute([$submissionId]);
    /** @var array<int, array<string, mixed>> $customValues */
    $customValues = $valQuery->fetchAll(PDO::FETCH_ASSOC);

    foreach ($customValues as $cv) {
        $colName = isset($cv['column_name']) && is_string($cv['column_name']) ? $cv['column_name'] : '';
        $valCont = isset($cv['value_content']) && is_string($cv['value_content']) ? $cv['value_content'] : '';

        $cleanedColName = preg_replace('/[^a-zA-Z0-9_]/', '_', trim($colName));
        $safeTag = '{' . strtolower($cleanedColName !== null ? $cleanedColName : 'field') . '}';
        $tags[$safeTag] = $valCont;
    }

    // 5. Parse Subject and Body templates using token replacement
    /** @var array<int, string> $tagKeys */
    $tagKeys = array_keys($tags);
    /** @var array<int, string> $tagVals */
    $tagVals = array_values($tags);

    $subject = str_replace($tagKeys, $tagVals, $template['subject']);
    $body = str_replace($tagKeys, $tagVals, $template['body']);

        // 6. Dispatch using your hybrid mail helper
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
