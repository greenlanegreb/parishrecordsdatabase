<?php
declare(strict_types=1);

/**
 * Send the suggest-edit outcome mail if the submitter asked for it.
 *
 * @param array<string, mixed> $suggestion
 */
function send_suggestion_outcome_mail(PDO $pdo, array $suggestion, string $decision): bool
{
    $notifyRaw = $suggestion['notify_outcome'] ?? 0;
    $notify = ($notifyRaw === 1 || $notifyRaw === '1' || $notifyRaw === true);
    if (!$notify) {
        return false;
    }

    $to = '';
    if (isset($suggestion['notify_email']) && is_string($suggestion['notify_email'])) {
        $to = trim($suggestion['notify_email']);
    }
    $first = '';
    $userId = isset($suggestion['suggested_by']) ? (int) $suggestion['suggested_by'] : 0;
    if ($userId > 0) {
        $u = $pdo->prepare('SELECT email, first_name, username FROM users WHERE id = ?');
        $u->execute([$userId]);
        $row = $u->fetch(PDO::FETCH_ASSOC);
        if (is_array($row)) {
            if ($to === '' && isset($row['email']) && is_string($row['email'])) {
                $to = trim($row['email']);
            }
            $first = isset($row['first_name']) && is_string($row['first_name']) && $row['first_name'] !== ''
                ? $row['first_name']
                : (isset($row['username']) && is_string($row['username']) ? $row['username'] : '');
        }
    }
    if ($to === '' || !filter_var($to, FILTER_VALIDATE_EMAIL)) {
        return false;
    }

    $helper = __DIR__ . '/templated_mail.php';
    if (is_file($helper)) {
        require_once $helper;
    }

    $tpl = false;
    try {
        $tplStmt = $pdo->prepare('SELECT subject, body FROM user_email_templates WHERE trigger_event = ?');
        $tplStmt->execute(['suggestion_outcome']);
        $tpl = $tplStmt->fetch(PDO::FETCH_ASSOC);
    } catch (\Throwable $e) {
        $tpl = false;
    }
    if (!is_array($tpl) || trim((string) ($tpl['subject'] ?? '')) === '' || trim((string) ($tpl['body'] ?? '')) === '') {
        $tpl = [
            'subject' => 'Update on your suggested change - {system_name}',
            'body' => "Hello {first_name},\n\nA moderator has reviewed the change you suggested on {system_name}.\n\nDecision: {decision}\nField: {column_name}\nYour suggestion: {proposed_value}\n\nTheir note:\n{moderator_rationale}\n\nIf you would like to discuss this further, you can open a support ticket here:\n{feedback_link}\n\nThank you for helping to keep the records accurate.\n\n{system_name}",
        ];
    }

    $systemName = function_exists('get_system_name') ? get_system_name($pdo) : 'pRD';
    $basePath = defined('BASE_PATH') ? rtrim((string) BASE_PATH, '/') : '';
    $httpHost = isset($_SERVER['HTTP_HOST']) && is_string($_SERVER['HTTP_HOST']) ? $_SERVER['HTTP_HOST'] : '';
    $https = !empty($_SERVER['HTTPS']) && $_SERVER['HTTPS'] !== 'off';
    $origin = $httpHost !== '' ? (($https ? 'https://' : 'http://') . $httpHost) : '';
    $feedbackLink = $origin . $basePath . '/feedback';
    $d = strtolower(trim($decision));
    $accepted = in_array($d, ['approve', 'approved', 'accept', 'accepted'], true);
    $decisionLabel = $accepted
        ? (__('suggest_edit.decision_accepted') !== 'suggest_edit.decision_accepted' ? __('suggest_edit.decision_accepted') : 'Accepted')
        : (__('suggest_edit.decision_not_accepted') !== 'suggest_edit.decision_not_accepted' ? __('suggest_edit.decision_not_accepted') : 'Not accepted');

    $rationale = isset($suggestion['moderator_rationale']) && is_string($suggestion['moderator_rationale'])
        ? trim($suggestion['moderator_rationale'])
        : '';
    if ($rationale === '') {
        $rationale = __('suggest_edit.no_rationale') !== 'suggest_edit.no_rationale'
            ? __('suggest_edit.no_rationale')
            : 'No extra note was added.';
    }

    $tags = [
        '{system_name}' => $systemName,
        '{first_name}' => $first !== '' ? $first : 'there',
        '{surname}' => '',
        '{username}' => '',
        '{role_name}' => '',
        '{decision}' => $decisionLabel,
        '{column_name}' => isset($suggestion['column_name']) ? (string) $suggestion['column_name'] : '',
        '{proposed_value}' => isset($suggestion['proposed_value']) ? (string) $suggestion['proposed_value'] : '',
        '{moderator_rationale}' => $rationale,
        '{feedback_link}' => $feedbackLink,
    ];

    if (!function_exists('send_templated_mail')) {
        return false;
    }

    return send_templated_mail(
        $pdo,
        $to,
        (string) ($tpl['subject'] ?? ''),
        (string) ($tpl['body'] ?? ''),
        $tags,
        'suggestion_outcome'
    );
}
