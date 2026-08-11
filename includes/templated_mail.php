<?php
declare(strict_types=1);

/**
 * Shared templated email helpers for volunteer / feedback (and similar).
 * Domain engines build tags; this file replaces tokens and dispatches mail.
 */

require_once __DIR__ . '/../db/mail_helper.php';

/**
 * Replace {tags} in subject/body and send via send_user_invitation().
 *
 * @param array<string, string> $tags Keys like '{first_name}' => 'Jane'
 */
function send_templated_mail(
    PDO $pdo,
    string $toEmail,
    string $subjectTemplate,
    string $bodyTemplate,
    array $tags,
    string $triggerEvent = 'notification'
): bool {
    if ($toEmail === '' || $subjectTemplate === '' || $bodyTemplate === '') {
        return false;
    }

    $subject = str_replace(array_keys($tags), array_values($tags), $subjectTemplate);
    $body    = str_replace(array_keys($tags), array_values($tags), $bodyTemplate);

    return send_user_invitation(
        $pdo,
        $toEmail,
        '',
        [
            'first_name' => $tags['{first_name}'] ?? '',
            'surname'    => $tags['{surname}'] ?? '',
            'username'   => $tags['{username}'] ?? '',
            'role_name'  => $tags['{role_name}'] ?? '',
        ],
        $triggerEvent,
        $subject,
        $body
    );
}

/**
 * Build {column_name} tags from a dynamic values table.
 * Table/column names must be fixed strings from our code — never user input.
 *
 * @return array<string, string>
 */
function dynamic_field_tags(
    PDO $pdo,
    string $valuesTable,
    string $columnsTable,
    string $fkColumn,
    int $fkId
): array {
    $allowed = [
        'volunteer_submission_values' => true,
        'volunteer_columns'           => true,
        'feedback_ticket_values'      => true,
        'feedback_columns'            => true,
        'submission_id'               => true,
        'ticket_id'                   => true,
    ];

    if (
        !isset($allowed[$valuesTable], $allowed[$columnsTable], $allowed[$fkColumn])
    ) {
        return [];
    }

    $sql = "SELECT v.value_content, c.column_name
            FROM {$valuesTable} v
            JOIN {$columnsTable} c ON v.column_id = c.id
            WHERE v.{$fkColumn} = ?";

    $stmt = $pdo->prepare($sql);
    $stmt->execute([$fkId]);

    $tags = [];
    while ($row = $stmt->fetch(PDO::FETCH_ASSOC)) {
        $col = isset($row['column_name']) && is_string($row['column_name']) ? $row['column_name'] : '';
        $val = isset($row['value_content']) && is_string($row['value_content']) ? $row['value_content'] : '';
        $clean = preg_replace('/[^a-zA-Z0-9_]/', '_', trim($col));
        if ($clean === null || $clean === '') {
            $clean = 'field';
        }
        $tags['{' . strtolower($clean) . '}'] = $val;
    }

    return $tags;
}
