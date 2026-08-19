<?php
/**
 * MIGRATED FILE MAPPING
 * ---------------------
 * Original Old File: admin/moderate.php/admin/actions/save_moderation.php
 * Migrated Date: 2026-08-05 03:36:59
 */
declare(strict_types=1);

namespace App\Controllers;

use App\Services\ModerationService;
use PDO;
use Exception;

class ModerationController
{
    private PDO $pdo;

    public function __construct(PDO $pdo)
    {
        $this->pdo = $pdo;
    }

    public function index(): void
    {
        if (!\is_module_enabled($this->pdo, 'moderation')) {
            http_response_code(403);
            exit('403 Forbidden: The Moderation Workflow module is currently disabled.');
        }

        /** @var array{id: int, username: string, date_format?: string}|false $currentUser */
        $currentUser = \get_current_user_data($this->pdo);
        if (!$currentUser) {
            $basePath = defined('BASE_PATH') ? BASE_PATH : '';
            header('Location: ' . $basePath . '/login');
            exit;
        }

        // Authorization checks
        $isAdmin = \is_admin($this->pdo);
        $hasAnyModPerm = $isAdmin;

        if (!$hasAnyModPerm) {
            $tablesChk = $this->pdo->query("SELECT id FROM dynamic_tables");
            /** @var array<int, int> $tableIds */
            $tableIds = $tablesChk !== false ? $tablesChk->fetchAll(PDO::FETCH_COLUMN) : [];
            foreach ($tableIds as $tId) {
                if (\has_permission($this->pdo, 'moderate_table_' . $tId)) {
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
        $timePrefs = \get_user_time_prefs($currentUser);
        $userTimezone = $timePrefs[0];
        $fullFormatStr = $timePrefs[1];

        $pendingStmt = $this->pdo->query("
            SELECT es.*, r.table_id, dt.table_name, 
                   u.id as suggestor_id, u.username as suggestor_name, u.first_name as suggestor_first, u.surname as suggestor_surname, u.attribution_display_mode as suggestor_mode,
                   rv.value_content as current_live_value, tc.is_required, tc.data_type, tc.boolean_display_format, tc.date_search_behavior, tc.field_options, tc.allow_multiple, tc.min_value, tc.max_value
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
            
            if ($isAdmin || ($tId === 1 && \has_permission($this->pdo, 'moderate_table_1')) || \has_permission($this->pdo, $modPermKey)) {
                $pendingSuggestions[] = $s;
            }
        }

        $message = $_SESSION['message'] ?? '';
        $error = $_SESSION['error'] ?? '';
        unset($_SESSION['message'], $_SESSION['error']);

        $dupTab = isset($_GET['tab']) && (string) $_GET['tab'] === 'similar';
        $dupQueue = [];
        $dupTables = [];
        try {
            $reviewService = new \App\Services\DuplicateReviewService($this->pdo);
            $allQueue = $reviewService->pendingQueue();
            foreach ($allQueue as $row) {
                $tId = isset($row['table_id']) ? (int) $row['table_id'] : 0;
                if ($isAdmin || \has_permission($this->pdo, 'moderate_table_' . $tId)) {
                    $dupQueue[] = $row;
                }
            }
            $tStmt = $this->pdo->query('SELECT id, table_name FROM dynamic_tables ORDER BY table_name ASC');
            $allTables = $tStmt !== false ? $tStmt->fetchAll(PDO::FETCH_ASSOC) : [];
            foreach ($allTables as $t) {
                $tId = isset($t['id']) ? (int) $t['id'] : 0;
                if ($isAdmin || \has_permission($this->pdo, 'moderate_table_' . $tId)) {
                    $dupTables[] = $t;
                }
            }
        } catch (Exception $e) {
            $dupQueue = [];
            $dupTables = [];
        }

        require_once __DIR__ . '/../Views/admin/moderate.php';
    }

    public function store(): void
    {
        if (!\is_module_enabled($this->pdo, 'moderation')) {
            http_response_code(403);
            exit('403 Forbidden: The Moderation Workflow module is currently disabled.');
        }

        $serverMethod = isset($_SERVER['REQUEST_METHOD']) && is_string($_SERVER['REQUEST_METHOD']) ? $_SERVER['REQUEST_METHOD'] : 'GET';
        if ($serverMethod !== 'POST') {
            http_response_code(405);
            exit('Method Not Allowed');
        }
        \verify_csrf_token();

        /** @var array{id: int, username: string, date_format?: string}|false $currentUser */
        $currentUser = \get_current_user_data($this->pdo);
        if (!$currentUser) {
            $basePath = defined('BASE_PATH') ? BASE_PATH : '';
            header('Location: ' . $basePath . '/login');
            exit;
        }

        $post = $_POST;
        $suggestionId = isset($post['suggestion_id']) ? (int)$post['suggestion_id'] : null;
        $action = isset($post['action']) && is_string($post['action']) ? $post['action'] : '';

        if (!function_exists('flatten_posted_column_value')) {
            require_once dirname(__DIR__, 2) . '/includes/column_options.php';
        }
        $rawFinal = isset($post['final_value']) ? $post['final_value'] : '';
        $flatFinal = flatten_posted_column_value($rawFinal);
        $finalValue = ($flatFinal === '0') ? '0' : \sanitize_incoming_text($flatFinal);

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

            // Centralized robust date normalization replacing the old inline manual regex
            if ($colDataType === 'DATE' && function_exists('normalize_incoming_date')) {
                $finalValue = normalize_incoming_date($finalValue);
            }
        }

        $remoteAddr = isset($_SERVER['REMOTE_ADDR']) && is_string($_SERVER['REMOTE_ADDR']) ? $_SERVER['REMOTE_ADDR'] : '127.0.0.1';

        if ($suggestionId !== null && in_array($action, ['approve', 'reject'], true)) {
            try {
                $moderationService = new ModerationService($this->pdo);
                $moderationService->handleSuggestion($suggestionId, $action, $finalValue, $currentUser, $remoteAddr);
            } catch (Exception $e) {
                $_SESSION['error'] = $e->getMessage();
            }
        }

        $basePath = defined('BASE_PATH') ? rtrim(BASE_PATH, '/') : '';
        header('Location: ' . $basePath . '/admin/moderation');
        exit;
    }
}
