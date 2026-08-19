<?php
/**
 * MIGRATED FILE MAPPING
 * ---------------------
 * Original Old File: user/actions/save_suggestion.php
 * Migrated Date: 2026-08-05 05:48:21
 */
declare(strict_types=1);

namespace App\Controllers;

use App\Services\DuplicateCheckService;
use Exception;
use PDO;

class UserSaveSuggestionActionController
{
    private PDO $pdo;

    public function __construct(PDO $pdo)
    {
        $this->pdo = $pdo;
    }

    public function handle(): void
    {
        $serverMethod = isset($_SERVER['REQUEST_METHOD']) && is_string($_SERVER['REQUEST_METHOD']) ? $_SERVER['REQUEST_METHOD'] : 'GET';
        if ($serverMethod !== 'POST') {
            http_response_code(405);
            exit('Method Not Allowed');
        }

        $basePath = defined('BASE_PATH') ? rtrim(BASE_PATH, '/') : '';
        $post = $_POST;

        // Where to send the user back
        $rawReturnUrl = isset($post['return_url']) && is_string($post['return_url']) ? trim($post['return_url']) : '';
        $returnUrl = $rawReturnUrl;
        if ($returnUrl === '' || preg_match('#^https?://#i', $returnUrl)) {
            // Disallow open redirects; only local relative paths
            $returnUrl = '/';
        }
        
        // Ensure local relative return URL honors BASE_PATH if present
        if ($basePath !== '' && str_starts_with($returnUrl, '/') && !str_starts_with($returnUrl, $basePath)) {
            $returnUrl = $basePath . $returnUrl;
        }

        $tableIdReturn = isset($post['table_id']) ? (int)$post['table_id'] : 0;

        $suggestionRedirect = function(string $url): void {
            header('Location: ' . $url);
            exit;
        };

        // Moderation module must be on
        if (!is_module_enabled($this->pdo, 'moderation')) {
            $_SESSION['error'] = 'Suggestions are currently disabled.';
            $suggestionRedirect($returnUrl);
        }

        // CSRF (session token works for guests too when form includes csrf_field)
        verify_csrf_token();

        require_once __DIR__ . '/../../includes/security_engine.php';

        // Firewall (rate limit, UA, excessive links)
        $firewallResult = run_form_firewall_check($this->pdo);
        if ($firewallResult !== true) {
            $_SESSION['error'] = is_string($firewallResult) ? $firewallResult : 'Firewall block triggered.';
            $suggestionRedirect($returnUrl);
        }

        // CAPTCHA when enabled in Settings
        $captchaResult = verify_form_captcha($this->pdo);
        if ($captchaResult !== true) {
            $_SESSION['error'] = is_string($captchaResult) ? $captchaResult : 'CAPTCHA verification failed.';
            $suggestionRedirect($returnUrl);
        }

        // Honeypot (accept either legacy field name)
        $hp1 = isset($post['website_hp']) && is_string($post['website_hp']) ? trim($post['website_hp']) : '';
        $hp2 = isset($post['website_url']) && is_string($post['website_url']) ? trim($post['website_url']) : '';
        if ($hp1 !== '' || $hp2 !== '') {
            $_SESSION['error'] = 'Spam detection triggered.';
            $suggestionRedirect($returnUrl);
        }

        $recordId = isset($post['record_id']) ? (int)$post['record_id'] : 0;
        $columnId = isset($post['column_id']) ? (int)$post['column_id'] : 0;
        $columnName = isset($post['column_name']) && is_string($post['column_name']) ? trim($post['column_name']) : '';
        
        if (!function_exists('flatten_posted_column_value')) {
            require_once dirname(__DIR__, 2) . '/includes/column_options.php';
        }
        $rawPropVal = isset($post['proposed_value']) ? $post['proposed_value'] : '';
        $proposedValue = sanitize_incoming_text(flatten_posted_column_value($rawPropVal));
        
        $rawReasoning = isset($post['reasoning']) ? $post['reasoning'] : '';
        $reasoning = sanitize_incoming_text(is_string($rawReasoning) ? $rawReasoning : '');
        $notifyOutcome = isset($post['notify_outcome']) ? 1 : 0;
        $notifyEmail = '';

        if ($recordId < 1) {
            $_SESSION['error'] = 'Invalid record.';
            $suggestionRedirect($returnUrl);
        }

        // Load record + table
        $recStmt = $this->pdo->prepare("SELECT id, table_id FROM records WHERE id = ?");
        $recStmt->execute([$recordId]);
        /** @var array{id: int|string, table_id: int|string}|false $record */
        $record = $recStmt->fetch(PDO::FETCH_ASSOC);
        if ($record === false) {
            $_SESSION['error'] = 'Record not found.';
            $suggestionRedirect($returnUrl);
        }

        $tableId = (int)$record['table_id'];
        $currentUser = function_exists('get_current_user_data') ? get_current_user_data($this->pdo) : null;

        // Table visibility is the gate (guest or logged-in)
        if (!user_can_view_table($this->pdo, $tableId, $currentUser)) {
            $_SESSION['error'] = 'You are not allowed to suggest edits for this record.';
            $suggestionRedirect($returnUrl);
        }

        if ($notifyOutcome === 1) {
            $rawNotify = isset($post['notify_email']) && is_string($post['notify_email']) ? trim($post['notify_email']) : '';
            $notifyEmail = filter_var($rawNotify, FILTER_VALIDATE_EMAIL) ? $rawNotify : '';
            if ($notifyEmail === '' && $currentUser !== null && isset($currentUser['email']) && is_string($currentUser['email'])) {
                $notifyEmail = filter_var(trim($currentUser['email']), FILTER_VALIDATE_EMAIL) ?: '';
            }
            if ($notifyEmail === '') {
                $_SESSION['error'] = __('suggest_edit.err_notify_email') !== 'suggest_edit.err_notify_email'
                    ? __('suggest_edit.err_notify_email')
                    : 'Please enter an email address if you would like to hear the outcome.';
                $suggestionRedirect($returnUrl);
            }
        }

        // Resolve column (prefer id; else name scoped to this table)
        /** @var array{id: int|string, column_name: string, data_type: string, is_required?: int|string, boolean_display_format?: string}|false $col */
        $col = false;
        if ($columnId > 0) {
            $c = $this->pdo->prepare("SELECT id, column_name, data_type, is_required, boolean_display_format, field_options, allow_multiple, min_value, max_value FROM table_columns WHERE id = ? AND table_id = ?");
            $c->execute([$columnId, $tableId]);
            $col = $c->fetch(PDO::FETCH_ASSOC);
        } elseif ($columnName !== '') {
            $c = $this->pdo->prepare("SELECT id, column_name, data_type, is_required, boolean_display_format, field_options, allow_multiple, min_value, max_value FROM table_columns WHERE column_name = ? AND table_id = ?");
            $c->execute([$columnName, $tableId]);
            $col = $c->fetch(PDO::FETCH_ASSOC);
        }

        if ($col === false) {
            $_SESSION['error'] = 'Invalid column.';
            $suggestionRedirect($returnUrl);
        }

        // Normalize date inputs automatically if the column is a DATE type
        if (isset($col['data_type']) && $col['data_type'] === 'DATE' && function_exists('normalize_incoming_date')) {
            $proposedValue = normalize_incoming_date($proposedValue);
        }

        $dataType = isset($col['data_type']) && is_string($col['data_type']) ? $col['data_type'] : '';
        if ($dataType === 'SELECT') {
            $opts = parse_column_options($col['field_options'] ?? '');
            $multi = !empty($col['allow_multiple']);
            if ($proposedValue !== '' && !column_values_are_allowed($proposedValue, $opts, $multi)) {
                $_SESSION['error'] = __('save_data_entry.err_invalid_choice') !== 'save_data_entry.err_invalid_choice'
                    ? sprintf(__('save_data_entry.err_invalid_choice'), (string) ($col['column_name'] ?? ''))
                    : 'Please choose a listed option.';
                $suggestionRedirect($returnUrl);
            }
        } elseif ($dataType === 'INT' && $proposedValue !== '') {
            if (!preg_match('/^-?\d+$/', $proposedValue)) {
                $_SESSION['error'] = __('save_data_entry.err_not_number') !== 'save_data_entry.err_not_number'
                    ? sprintf(__('save_data_entry.err_not_number'), (string) ($col['column_name'] ?? ''))
                    : 'That field must be a whole number.';
                $suggestionRedirect($returnUrl);
            } else {
                $n = (int) $proposedValue;
                if (isset($col['min_value']) && $col['min_value'] !== null && $col['min_value'] !== '' && $n < (int)$col['min_value']) {
                    $_SESSION['error'] = __('save_data_entry.err_min') !== 'save_data_entry.err_min'
                        ? sprintf(__('save_data_entry.err_min'), (string) ($col['column_name'] ?? ''))
                        : 'That value is below the minimum.';
                    $suggestionRedirect($returnUrl);
                }
                if (isset($col['max_value']) && $col['max_value'] !== null && $col['max_value'] !== '' && $n > (int)$col['max_value']) {
                    $_SESSION['error'] = __('save_data_entry.err_max') !== 'save_data_entry.err_max'
                        ? sprintf(__('save_data_entry.err_max'), (string) ($col['column_name'] ?? ''))
                        : 'That value is above the maximum.';
                    $suggestionRedirect($returnUrl);
                }
            }
        }

        $isRequired = !empty($col['is_required']);
        if ($isRequired && $proposedValue === '') {
            $_SESSION['error'] = 'That field is required.';
            $suggestionRedirect($returnUrl);
        }

        // Allow "0" as a real boolean/text value; block only truly empty when required already handled
        if ($proposedValue === '' && !$isRequired) {
            // Optional empty still not useful as a suggestion
            $_SESSION['error'] = 'Please enter a proposed value.';
            $suggestionRedirect($returnUrl);
        }

        $confirmedDuplicate = isset($post['confirm_duplicate']) && $post['confirm_duplicate'] === '1';
        $reportDuplicate = isset($post['report_duplicate']) && $post['report_duplicate'] === '1';
        $duplicateOf = isset($post['duplicate_of']) ? (int) $post['duplicate_of'] : 0;
        if ($reportDuplicate) {
            $note = $duplicateOf > 0
                ? sprintf('This looks like a duplicate of record #%d.', $duplicateOf)
                : 'This looks like a duplicate of another record.';
            $reasoning = trim($reasoning === '' ? $note : $reasoning . "\n" . $note);
        }

        $dupMode = function_exists('get_setting') ? get_setting($this->pdo, 'duplicate_mode', 'warn') : 'warn';
        $dupPicky = function_exists('get_setting') ? get_setting($this->pdo, 'duplicate_picky', 'similar') : 'similar';
        if (!in_array($dupMode, ['off', 'warn', 'block', 'flag'], true)) {
            $dupMode = 'warn';
        }

        if ($dupMode !== 'off' && !($confirmedDuplicate && $dupMode !== 'block')) {
            $colsStmt = $this->pdo->prepare('SELECT * FROM table_columns WHERE table_id = ?');
            $colsStmt->execute([$tableId]);
            $colsMap = [];
            $valuesByCol = [];
            foreach ($colsStmt->fetchAll(PDO::FETCH_ASSOC) as $cRow) {
                $cid = isset($cRow['id']) ? (int) $cRow['id'] : 0;
                if ($cid < 1) {
                    continue;
                }
                $colsMap[$cid] = $cRow;
                $valuesByCol[$cid] = '';
            }
            $rvStmt = $this->pdo->prepare('SELECT column_id, value_content FROM record_values WHERE record_id = ?');
            $rvStmt->execute([$recordId]);
            while ($rv = $rvStmt->fetch(PDO::FETCH_ASSOC)) {
                $cid = isset($rv['column_id']) ? (int) $rv['column_id'] : 0;
                $valuesByCol[$cid] = isset($rv['value_content']) && is_string($rv['value_content']) ? $rv['value_content'] : '';
            }
            $valuesByCol[(int) $col['id']] = $proposedValue;

            $dupes = (new DuplicateCheckService($this->pdo))->findMatches(
                $tableId,
                $valuesByCol,
                $colsMap,
                $dupPicky === 'exact' ? 'exact' : 'similar',
                $recordId
            );
            if ($dupes !== []) {
                $_SESSION['suggest_dup_warning'] = true;
                $_SESSION['suggest_dup_matches'] = $dupes;
                $_SESSION['suggest_dup_mode'] = $dupMode;
                $_SESSION['error'] = $dupMode === 'block'
                    ? (__('data_entry.dup_blocked') !== 'data_entry.dup_blocked' ? __('data_entry.dup_blocked') : 'This record is too similar to one already saved, so it cannot be added.')
                    : (__('suggest_edit.dup_please_check') !== 'suggest_edit.dup_please_check' ? __('suggest_edit.dup_please_check') : 'This change would look very similar to another record. Please check below.');
                $suggestionRedirect($returnUrl);
            }
        }

        $suggestedBy = ($currentUser !== false && $currentUser !== null && isset($currentUser['id'])) ? $currentUser['id'] : null;

        // Prefer PHP UTC timestamp so a skewed MySQL NOW() does not own the clock
        $createdAt = gmdate('Y-m-d H:i:s');

        try {
            $ins = $this->pdo->prepare("
                INSERT INTO edit_suggestions
                    (record_id, suggested_by, column_name, proposed_value, reasoning, notify_outcome, notify_email, status, points_awarded, created_at)
                VALUES
                    (?, ?, ?, ?, ?, ?, ?, 'pending', 0, ?)
            ");
            $ins->execute([
                $recordId,
                $suggestedBy,
                $col['column_name'],
                $proposedValue,
                $reasoning !== '' ? $reasoning : null,
                $notifyOutcome,
                $notifyEmail !== '' ? $notifyEmail : null,
                $createdAt,
            ]);
            $_SESSION['message'] = 'Your suggestion was submitted for review.';
            unset($_SESSION['suggest_dup_warning'], $_SESSION['suggest_dup_matches'], $_SESSION['suggest_dup_mode']);
        } catch (Exception $e) {
            // Fallback if reasoning/points columns missing on very old DB
            try {
                $ins = $this->pdo->prepare("
                    INSERT INTO edit_suggestions
                        (record_id, suggested_by, column_name, proposed_value, status, created_at)
                    VALUES
                        (?, ?, ?, ?, 'pending', ?)
                ");
                $ins->execute([
                    $recordId,
                    $suggestedBy,
                    $col['column_name'],
                    $proposedValue,
                    $createdAt,
                ]);
                $_SESSION['message'] = 'Your suggestion was submitted for review.';
            unset($_SESSION['suggest_dup_warning'], $_SESSION['suggest_dup_matches'], $_SESSION['suggest_dup_mode']);
            } catch (Exception $e2) {
                error_log('save_suggestion failed: ' . $e2->getMessage());
                $_SESSION['error'] = 'Could not save your suggestion.';
            }
        }

        $suggestionRedirect($returnUrl);
    }
}
