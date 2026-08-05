<?php
/**
 * MIGRATED FILE MAPPING
 * ---------------------
 * Original Old File: admin/moderate.php/admin/actions/save_moderation.php
 * Migrated Date: 2026-08-05 03:36:59
 */declare(strict_types=1);


namespace App\Controllers;

use PDO;

class ModerationController
{
    private PDO $pdo;

    public function __construct(PDO $pdo)
    {
        $this->pdo = $pdo;
    }

    public function index(): void
    {
        if (session_status() === PHP_SESSION_NONE) {
            session_start();
        }

        $moduleCheck = $this->pdo->prepare("SELECT is_enabled FROM modules WHERE module_name = ?");
        $moduleCheck->execute(['moderation']);
        if (!$moduleCheck->fetchColumn()) {
            http_response_code(403);
            exit('403 Forbidden: The Moderation Workflow module is currently disabled.');
        }

        /** @var array{id: int, username: string, date_format?: string}|false $currentUser */
        $currentUser = get_current_user_data($this->pdo);
        if (!$currentUser) {
            $basePath = defined('BASE_PATH') ? BASE_PATH : '';
            header('Location: ' . $basePath . '/user/login.php');
            exit;
        }

        // Authorization checks
        $isAdmin = is_admin($this->pdo);
        $hasAnyModPerm = $isAdmin;

        if (!$hasAnyModPerm) {
            $tablesChk = $this->pdo->query("SELECT id FROM dynamic_tables");
            /** @var array<int, int> $tableIds */
            $tableIds = $tablesChk !== false ? $tablesChk->fetchAll(PDO::FETCH_COLUMN) : [];
            foreach ($tableIds as $tId) {
                if (has_permission($this->pdo, 'moderate_table_' . $tId)) {
                    $hasAnyModPerm = true;
                    break;
                }
            }
        }

        if (!$hasAnyModPerm) {
            require_once __DIR__ . '/../403.php';
            exit;
        }

        /** @var array{0: string, 1: string} $timePrefs */
        $timePrefs = get_user_time_prefs($currentUser);
        $userTimezone = $timePrefs[0];
        $fullFormatStr = $timePrefs[1];

        $pendingStmt = $this->pdo->query("
            SELECT es.*, r.table_id, dt.table_name, 
                   u.id as suggestor_id, u.username as suggestor_name, u.first_name as suggestor_first, u.surname as suggestor_surname, u.attribution_display_mode as suggestor_mode,
                   rv.value_content as current_live_value, tc.is_required, tc.data_type, tc.boolean_display_format, tc.date_search_behavior
            FROM edit_suggestions es
            JOIN records r ON es.record_id = r.id
            JOIN dynamic_tables dt ON r.table_id = dt.id
            LEFT JOIN users u ON es.suggested_by = u.id
            LEFT JOIN table_columns tc ON es.column_name = tc.column_name AND tc.table_id = r.table_id
            LEFT JOIN record_values rv ON es.record_id = rv.record_id AND tc.id = rv.column_id
            WHERE es.status = 'pending'
            ORDER BY es.created_at ASC
        ");
        /** @var array<int, array<string, mixed>> $allPending */
        $allPending = $pendingStmt !== false ? $pendingStmt->fetchAll(PDO::FETCH_ASSOC) : [];

        /** @var array<int, array<string, mixed>> $pendingSuggestions */
        $pendingSuggestions = [];
        foreach ($allPending as $s) {
            $tId = isset($s['table_id']) ? (int)$s['table_id'] : 0;
            $modPermKey = 'moderate_table_' . $tId;
          
            if ($isAdmin || ($tId === 1 && has_permission($this->pdo, 'moderate_table_1')) || has_permission($this->pdo, $modPermKey)) {
                $pendingSuggestions[] = $s;
            }
        }

        $message = $_SESSION['message'] ?? '';
        $error = $_SESSION['error'] ?? '';
        unset($_SESSION['message'], $_SESSION['error']);

        require_once __DIR__ . '/../Views/admin/moderate.php';
    }

    public function store(): void
    {
        if (session_status() === PHP_SESSION_NONE) {
            session_start();
        }

        $moduleCheck = $this->pdo->prepare("SELECT is_enabled FROM modules WHERE module_name = ?");
        $moduleCheck->execute(['moderation']);
        if (!$moduleCheck->fetchColumn()) {
            http_response_code(403);
            exit('403 Forbidden: The Moderation Workflow module is currently disabled.');
        }

        $serverMethod = isset($_SERVER['REQUEST_METHOD']) && is_string($_SERVER['REQUEST_METHOD']) ? $_SERVER['REQUEST_METHOD'] : 'GET';
        if ($serverMethod !== 'POST') {
            http_response_code(405);
            exit('Method Not Allowed');
        }
        verify_csrf_token();

        $post = $_POST;
        $suggestionId = isset($post['suggestion_id']) ? $post['suggestion_id'] : null;
        $action = isset($post['action']) && is_string($post['action']) ? $post['action'] : '';

        $rawFinal = isset($post['final_value']) ? $post['final_value'] : '';
        $finalValue = ($rawFinal === '0' || $rawFinal === 0) ? '0' : sanitize_incoming_text(is_string($rawFinal) ? $rawFinal : '');

        if ($suggestionId !== null && $finalValue !== '') {
            $typeChk = $this->pdo->prepare("
                SELECT tc.data_type 
                FROM edit_suggestions es
                JOIN records r ON es.record_id = r.id
                JOIN table_columns tc ON es.column_name = tc.column_name AND tc.table_id = r.table_id
                WHERE es.id = ?
            ");
            $typeChk->execute([$suggestionId]);
            $colDataType = $typeChk->fetchColumn();

            if ($colDataType === 'DATE') {
                $normalized = $finalValue;
                /** @var array{id: int, username: string, date_format?: string}|false $currentUser */
                $currentUser = get_current_user_data($this->pdo);
                $dateFormat = $currentUser && isset($currentUser['date_format']) ? $currentUser['date_format'] : '';
                
                $m = [];
                if (preg_match('/^(\d{1,2})\/(\d{1,2})\/(\d{2,4})$/', $finalValue, $m)) {
                    $year = strlen($m[3]) === 2 ? ((int)$m[3] > 50 ? '19' . $m[3] : '20' . $m[3]) : $m[3];
                    $normalized = sprintf('%04d-%02d-%02d', (int)$year, (int)$m[2], (int)$m[1]);
                } elseif (preg_match('/^(\d{1,2})\.(\d{1,2})\.(\d{2,4})$/', $finalValue, $m)) {
                    $year = strlen($m[3]) === 2 ? ((int)$m[3] > 50 ? '19' . $m[3] : '20' . $m[3]) : $m[3];
                    $normalized = sprintf('%04d-%02d-%02d', (int)$year, (int)$m[2], (int)$m[1]);
                } elseif (preg_match('/^(\d{1,2})\/(\d{1,2})\/(\d{4})$/', $finalValue, $m) && $dateFormat === 'm/d/Y') {
                    $normalized = sprintf('%04d-%02d-%02d', (int)$m[3], (int)$m[1], (int)$m[2]);
                }
                
                $finalValue = $normalized;
            }
        }

        if ($suggestionId !== null && in_array($action, ['approve', 'reject'], true)) {
            $sStmt = $this->pdo->prepare("
                SELECT es.*, r.table_id 
                FROM edit_suggestions es
                JOIN records r ON es.record_id = r.id
                WHERE es.id = ?
            ");
            $sStmt->execute([$suggestionId]);
            /** @var array<string, mixed>|false $suggestion */
            $suggestion = $sStmt->fetch(PDO::FETCH_ASSOC);
            
            if ($suggestion !== false) {
                $tableId = isset($suggestion['table_id']) ? (int)$suggestion['table_id'] : 0;
                $modPermKey = 'moderate_table_' . $tableId;
                
                /** @var array{id: int, username: string} $currentUser */
                $currentUser = get_current_user_data($this->pdo);
                if (!is_admin($this->pdo) && !has_permission($this->pdo, $modPermKey)) {
                    http_response_code(403);
                    exit('Unauthorized: You do not have moderation permission for this specific table.');
                }

                $suggestorId = isset($suggestion['suggested_by']) ? $suggestion['suggested_by'] : null;
                $alreadyProcessed = isset($suggestion['points_awarded']) && (int)$suggestion['points_awarded'] === 1;

                if ($action === 'approve') {
                    $cStmt = $this->pdo->prepare("SELECT id, is_required, data_type FROM table_columns WHERE column_name = ? AND table_id = ?");
                    $cStmt->execute([$suggestion['column_name'], $tableId]);
                    /** @var array<string, mixed>|false $col */
                    $col = $cStmt->fetch(PDO::FETCH_ASSOC);
                    
                    $originalCreatorId = null;
                    if ($col !== false) {
                        if (!empty($col['is_required']) && $finalValue === '') {
                            $_SESSION['error'] = "Cannot approve: This column is marked as required and cannot be left blank.";
                            header('Location: /admin/moderate');
                            exit;
                        }

                        $creatorStmt = $this->pdo->prepare("SELECT created_by FROM records WHERE id = ?");
                        $creatorStmt->execute([$suggestion['record_id']]);
                        $originalCreatorId = $creatorStmt->fetchColumn();

                        $checkVal = $this->pdo->prepare("SELECT id FROM record_values WHERE record_id = ? AND column_id = ?");
                        $checkVal->execute([$suggestion['record_id'], $col['id']]);
                        
                        if ($checkVal->fetch()) {
                            $upStmt = $this->pdo->prepare("UPDATE record_values SET value_content = ? WHERE record_id = ? AND column_id = ?");
                            $upStmt->execute([$finalValue, $suggestion['record_id'], $col['id']]);
                        } else {
                            $insStmt = $this->pdo->prepare("INSERT INTO record_values (record_id, column_id, value_content) VALUES (?, ?, ?)");
                            $insStmt->execute([$suggestion['record_id'], $col['id'], $finalValue]);
                        }
                    }

                    $statusStmt = $this->pdo->prepare("UPDATE edit_suggestions SET status = 'approved', points_awarded = 1 WHERE id = ?");
                    $statusStmt->execute([$suggestionId]);

                    if (!$alreadyProcessed) {
                        adjust_user_points($this->pdo, $currentUser['id'], 1);

                        if ($suggestorId !== null) {
                            adjust_user_points($this->pdo, (int)$suggestorId, 1);
                        }

                        if ($originalCreatorId !== false && $originalCreatorId !== null) {
                            adjust_user_points($this->pdo, (int)$originalCreatorId, -1);
                        }
                    }

                    $_SESSION['message'] = "Suggestion #{$suggestionId} approved and applied.";
                } else {
                    $statusStmt = $this->pdo->prepare("UPDATE edit_suggestions SET status = 'rejected', points_awarded = 1 WHERE id = ?");
                    $statusStmt->execute([$suggestionId]);

                    if (!$alreadyProcessed && $suggestorId !== null) {
                        adjust_user_points($this->pdo, (int)$suggestorId, -1);
                    }

                    $_SESSION['message'] = "Suggestion #{$suggestionId} has been rejected.";
                }
                
                $remoteAddr = isset($_SERVER['REMOTE_ADDR']) && is_string($_SERVER['REMOTE_ADDR']) ? $_SERVER['REMOTE_ADDR'] : '127.0.0.1';
                $audit = $this->pdo->prepare("INSERT INTO audit_logs (user_id, action, record_id, details, ip_address) VALUES (?, ?, ?, ?, ?)");
                $audit->execute([$currentUser['id'], strtoupper($action) . '_SUGGESTION', $suggestion['record_id'], "Handled suggestion ID: {$suggestionId} in table ID {$tableId}", $remoteAddr]);
            }
        }

        header('Location: /admin/moderate');
        exit;
    }
}
