<?php
/**
 * MIGRATED FILE MAPPING
 * ---------------------
 * Original Old File: user/suggest_edit.php/user/actions/save_suggest_edit.php
 * Migrated Date: 2026-08-05 05:27:15
 */
declare(strict_types=1);

namespace App\Controllers;

use PDO;

class UserSuggestEditActionController
{
    private PDO $pdo;

    public function __construct(PDO $pdo)
    {
        $this->pdo = $pdo;
    }

    public function save(): void
    {
        // Ensure the moderation module is enabled; otherwise block action execution
        if (!is_module_enabled($this->pdo, 'moderation')) {
            http_response_code(403);
            exit('403 Forbidden: The Moderation Workflow module is currently disabled.');
        }

        $serverMethod = isset($_SERVER['REQUEST_METHOD']) && is_string($_SERVER['REQUEST_METHOD']) ? $_SERVER['REQUEST_METHOD'] : 'GET';
        if ($serverMethod !== 'POST') {
            http_response_code(405);
            exit('Method Not Allowed');
        }

        verify_csrf_token();
        /** @var array{id: int|string, username: string} $currentUser */
        $currentUser = require_permission($this->pdo, 'access_suggest_edit', 'Allows submitting edit suggestions for records');
        $userId = $currentUser['id'];

        $basePath = defined('BASE_PATH') ? rtrim(BASE_PATH, '/') : '';
        $post = $_POST;
        $recordId = isset($post['record_id']) ? (string)$post['record_id'] : null;
        $returnUrl = isset($post['return_url']) && is_string($post['return_url']) ? $post['return_url'] : 'index.php';
        $remoteAddr = isset($_SERVER['REMOTE_ADDR']) && is_string($_SERVER['REMOTE_ADDR']) ? $_SERVER['REMOTE_ADDR'] : '127.0.0.1';

        if ($recordId === null || $recordId === '') {
            http_response_code(403);
            exit("No record specified.");
        }

        // Check honeypot anti-spam traps
        if (!empty($post['website_hp']) || !empty($post['website_url'])) {
            $_SESSION['error'] = "Spam detected.";
            header("Location: " . $basePath . "/user/suggest-edit?record_id=" . urlencode($recordId) . "&return=" . urlencode($returnUrl));
            exit;
        }

        // Validate Captcha if function exists
        if (function_exists('verify_form_captcha')) {
            if (!verify_form_captcha($this->pdo, $post)) {
                $_SESSION['error'] = "Invalid captcha code. Please try again.";
                header("Location: " . $basePath . "/user/suggest-edit?record_id=" . urlencode($recordId) . "&return=" . urlencode($returnUrl));
                exit;
            }
        }

        $columnId = isset($post['column_id']) ? (string)$post['column_id'] : '';
        $proposedValue = isset($post['proposed_value']) && is_string($post['proposed_value']) ? trim($post['proposed_value']) : '';
        $reasoning = isset($post['reasoning']) && is_string($post['reasoning']) ? trim($post['reasoning']) : '';

        // Check if proposed value is empty (allowing string '0' for boolean false)
        if (($proposedValue === '' && $proposedValue !== '0') || $columnId === '') {
            $_SESSION['error'] = "Proposed value cannot be empty.";
        } else {
            $colStmt = $this->pdo->prepare("SELECT column_name, data_type, boolean_display_format FROM table_columns WHERE id = ?");
            $colStmt->execute([$columnId]);
            /** @var array{column_name: string, data_type: string, boolean_display_format?: string}|false $colInfo */
            $colInfo = $colStmt->fetch(PDO::FETCH_ASSOC);

            if ($colInfo !== false) {
                // Normalize date inputs automatically if the column is a DATE type
                if ($colInfo['data_type'] === 'DATE') {
                    $proposedValue = normalize_incoming_date($proposedValue);
                }

                $displayVal = $proposedValue;
                if ($colInfo['data_type'] === 'BOOLEAN') {
                    $fmt = isset($colInfo['boolean_display_format']) && is_string($colInfo['boolean_display_format']) ? $colInfo['boolean_display_format'] : 'yes_no';
                    $displayVal = format_boolean_value($proposedValue, $fmt);
                }

                // Insert suggestion as pending with points_awarded = 0 (no points in limbo)
                $ins = $this->pdo->prepare("
                    INSERT INTO edit_suggestions (record_id, suggested_by, column_name, proposed_value, reasoning, status, points_awarded) 
                    VALUES (?, ?, ?, ?, ?, 'pending', 0)
                ");
                
                if ($ins->execute([$recordId, $userId, $colInfo['column_name'], $proposedValue, $reasoning])) {
                    $_SESSION['message'] = "Your edit suggestion has been successfully submitted to the admin queue for review.";
                    
                    $auditDetails = "Suggested edit for column: {$colInfo['column_name']} (Proposed: {$displayVal}).";
                    if ($reasoning !== '') {
                        $auditDetails .= " Reasoning/Evidence: " . $reasoning;
                    }

                    $audit = $this->pdo->prepare("INSERT INTO audit_logs (user_id, action, record_id, details, ip_address) VALUES (?, ?, ?, ?, ?)");
                    $audit->execute([$userId, 'EDIT_SUGGESTION', $recordId, $auditDetails, $remoteAddr]);
                } else {
                    $_SESSION['error'] = "Failed to submit suggestion.";
                }
            }
        }

        header("Location: " . $basePath . "/user/suggest-edit?record_id=" . urlencode($recordId) . "&return=" . urlencode($returnUrl));
        exit;
    }
}
