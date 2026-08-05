<?php
/**
 * MIGRATED FILE MAPPING
 * ---------------------
 * Original Old File: user/actions/save_suggestion.php
 * Migrated Date: 2026-08-05 05:48:21
 */declare(strict_types=1);


namespace App\Controllers;

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
        if (session_status() === PHP_SESSION_NONE) {
            session_start();
        }

        $serverMethod = isset($_SERVER['REQUEST_METHOD']) && is_string($_SERVER['REQUEST_METHOD']) ? $_SERVER['REQUEST_METHOD'] : 'GET';
        if ($serverMethod !== 'POST') {
            http_response_code(405);
            exit('Method Not Allowed');
        }

        $post = $_POST;

        // Where to send the user back
        $rawReturnUrl = isset($post['return_url']) && is_string($post['return_url']) ? trim($post['return_url']) : '';
        $returnUrl = $rawReturnUrl;
        if ($returnUrl === '' || preg_match('#^https?://#i', $returnUrl)) {
            // Disallow open redirects; only local relative paths
            $returnUrl = '/index.php';
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
        
        $rawPropVal = isset($post['proposed_value']) ? $post['proposed_value'] : '';
        $proposedValue = sanitize_incoming_text(is_string($rawPropVal) ? $rawPropVal : '');
        
        $rawReasoning = isset($post['reasoning']) ? $post['reasoning'] : '';
        $reasoning = sanitize_incoming_text(is_string($rawReasoning) ? $rawReasoning : '');

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

        // Resolve column (prefer id; else name scoped to this table)
        /** @var array{id: int|string, column_name: string, data_type: string, is_required?: int|string, boolean_display_format?: string}|false $col */
        $col = false;
        if ($columnId > 0) {
            $c = $this->pdo->prepare("SELECT id, column_name, data_type, is_required, boolean_display_format FROM table_columns WHERE id = ? AND table_id = ?");
            $c->execute([$columnId, $tableId]);
            $col = $c->fetch(PDO::FETCH_ASSOC);
        } elseif ($columnName !== '') {
            $c = $this->pdo->prepare("SELECT id, column_name, data_type, is_required, boolean_display_format FROM table_columns WHERE column_name = ? AND table_id = ?");
            $c->execute([$columnName, $tableId]);
            $col = $c->fetch(PDO::FETCH_ASSOC);
        }

        if ($col === false) {
            $_SESSION['error'] = 'Invalid column.';
            $suggestionRedirect($returnUrl);
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

        $suggestedBy = ($currentUser !== false && $currentUser !== null && isset($currentUser['id'])) ? $currentUser['id'] : null;

        // Prefer PHP UTC timestamp so a skewed MySQL NOW() does not own the clock
        $createdAt = gmdate('Y-m-d H:i:s');

        try {
            $ins = $this->pdo->prepare("
                INSERT INTO edit_suggestions
                    (record_id, suggested_by, column_name, proposed_value, reasoning, status, points_awarded, created_at)
                VALUES
                    (?, ?, ?, ?, ?, 'pending', 0, ?)
            ");
            $ins->execute([
                $recordId,
                $suggestedBy,
                $col['column_name'],
                $proposedValue,
                $reasoning !== '' ? $reasoning : null,
                $createdAt,
            ]);
            $_SESSION['message'] = 'Your suggestion was submitted for review.';
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
            } catch (Exception $e2) {
                error_log('save_suggestion failed: ' . $e2->getMessage());
                $_SESSION['error'] = 'Could not save your suggestion.';
            }
        }

        $suggestionRedirect($returnUrl);
    }
}
