<?php
/**
 * MIGRATED FILE MAPPING
 * ---------------------
 * Original Old File: user/suggest_edit.php/user/actions/save_suggest_edit.php
 * Migrated Date: 2026-08-05 05:26:30
 */
declare(strict_types=1);

namespace App\Controllers;

use PDO;

class UserSuggestEditController
{
    private PDO $pdo;

    public function __construct(PDO $pdo)
    {
        $this->pdo = $pdo;
    }

    public function show(): void
    {
        // Ensure the moderation module is enabled; otherwise block access to suggestions
        if (!is_module_enabled($this->pdo, 'moderation')) {
            http_response_code(403);
            exit('403 Forbidden: The Moderation Workflow module is currently disabled.');
        }

        // Enforce permission-based access control
                /** @var array{id: int|string, username?: string}|null $currentUser */
        $currentUser = require_suggest_edit_access($this->pdo);

        $basePath = defined('BASE_PATH') ? rtrim(BASE_PATH, '/') : '';
        $queryGet = $_GET;
        $recordId = isset($queryGet['record_id']) ? (string)$queryGet['record_id'] : null;
        $serverRef = isset($_SERVER['HTTP_REFERER']) && is_string($_SERVER['HTTP_REFERER']) ? $_SERVER['HTTP_REFERER'] : $basePath . '/index.php';
        $returnUrl = isset($queryGet['return']) && is_string($queryGet['return']) ? $queryGet['return'] : $serverRef;

        if ($recordId === null || $recordId === '') {
            exit(__('record_history.exit_no_record'));
        }

        // Fetch ALL columns belonging to this record's table, including data type and formatting details
        $stmt = $this->pdo->prepare("
            SELECT 
                r.id AS record_id,
                r.table_id,
                tc.id AS column_id,
                tc.column_name,
                tc.data_type,
                tc.boolean_display_format,
                COALESCE(rv.value_content, '') AS value_content
            FROM records r
            JOIN table_columns tc ON tc.table_id = r.table_id
            LEFT JOIN record_values rv ON rv.record_id = r.id AND rv.column_id = tc.id
            WHERE r.id = ?
            ORDER BY tc.id ASC
        ");
        $stmt->execute([$recordId]);
        /** @var array<int, array<string, mixed>> $recordData */
        $recordData = $stmt->fetchAll(PDO::FETCH_ASSOC);

        if (empty($recordData)) {
            exit(__('record_history.exit_not_found'));
        }

        $tableId = isset($recordData[0]['table_id']) ? (int) $recordData[0]['table_id'] : 0;
        if ($tableId < 1 || !user_can_view_table($this->pdo, $tableId, $currentUser)) {
            http_response_code(403);
            exit('403 Forbidden: You cannot suggest edits for this record.');
        }

        $message = $_SESSION['message'] ?? '';
        $error = $_SESSION['error'] ?? '';
        unset($_SESSION['message'], $_SESSION['error']);

        require_once __DIR__ . '/../Views/user/suggest_edit.php';
    }
}
