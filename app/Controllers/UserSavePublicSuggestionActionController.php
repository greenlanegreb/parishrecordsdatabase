<?php
/**
 * MIGRATED FILE MAPPING
 * ---------------------
 * Original Old File: save_public_suggestion.php
 * Migrated Date: 2026-08-05 05:41:34
 */
declare(strict_types=1);

namespace App\Controllers;

use PDO;

class UserSavePublicSuggestionActionController
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

        // 1. Honeypot Anti-Spam Check: If the hidden trap field is filled out, it's a bot. Silently drop or reject.
        $honeypot = isset($post['website_hp']) && is_string($post['website_hp']) ? trim($post['website_hp']) : '';
        if ($honeypot !== '') {
            $_SESSION['error'] = __('save_public_suggestion.err_spam_detected');
            header('Location: ' . $basePath . '/');
            exit;
        }

        $recordId = isset($post['record_id']) ? (int)$post['record_id'] : 0;
        $columnName = isset($post['column_name']) && is_string($post['column_name']) ? trim($post['column_name']) : '';
        $rawProposedVal = isset($post['proposed_value']) ? $post['proposed_value'] : '';
        $proposedValue = sanitize_incoming_text(is_string($rawProposedVal) ? $rawProposedVal : '');
        
        // Determine user ID if logged in, otherwise null for public guest
        $suggestedBy = isset($_SESSION['user_id']) ? (int)$_SESSION['user_id'] : null;

        if ($recordId > 0 && $columnName !== '') {
            // Verify the column actually exists to prevent fake column injection and check data type
            $colStmt = $this->pdo->prepare("SELECT id, is_required, data_type FROM table_columns WHERE column_name = ?");
            $colStmt->execute([$columnName]);
            /** @var array{id: int|string, is_required: int|string, data_type?: string}|false $col */
            $col = $colStmt->fetch(PDO::FETCH_ASSOC);

            if ($col !== false) {
                // Enforce required field rules on suggestions if left blank
                if (!empty($col['is_required']) && $proposedValue === '') {
                    $_SESSION['error'] = __('save_public_suggestion.err_field_required');
                    header('Location: ' . $basePath . '/');
                    exit;
                }

                // Normalize date inputs automatically if the column is a DATE type
                if (isset($col['data_type']) && (string)$col['data_type'] === 'DATE' && $proposedValue !== '') {
                    $proposedValue = normalize_incoming_date($proposedValue);
                }

                // Insert into edit_suggestions table (which feeds admin/moderate.php)
                $stmt = $this->pdo->prepare("
                    INSERT INTO edit_suggestions (record_id, column_name, proposed_value, suggested_by, status, created_at) 
                    VALUES (?, ?, ?, ?, 'pending', NOW())
                ");
                if ($stmt->execute([$recordId, $columnName, $proposedValue, $suggestedBy])) {
                    $_SESSION['message'] = __('save_public_suggestion.msg_success');
                } else {
                    $_SESSION['error'] = __('save_public_suggestion.err_failed_submit');
                }
            } else {
                $_SESSION['error'] = __('save_public_suggestion.err_invalid_column');
            }
        } else {
            $_SESSION['error'] = __('save_public_suggestion.err_invalid_params');
        }

        header('Location: ' . $basePath . '/');
        exit;
    }
}
