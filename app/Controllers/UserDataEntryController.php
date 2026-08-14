<?php
/**
 * MIGRATED FILE MAPPING
 * ---------------------
 * Original Old File: user/data_entry.php/user/actions/save_data_entry.php
 * Migrated Date: 2026-08-05 04:49:46
 */
declare(strict_types=1);

namespace App\Controllers;

use PDO;

class UserDataEntryController
{
    private PDO $pdo;

    public function __construct(PDO $pdo)
    {
        $this->pdo = $pdo;
    }

    public function index(): void
    {
        \require_permission($this->pdo, 'access_data_entry', 'Allows accessing the core data entry workstation and creating records');
        /** @var array{id: int|string, username: string, first_name?: string, surname?: string, attribution_display_mode?: string, date_format?: string, timezone?: string} $currentUser */
        $currentUser = \get_current_user_data($this->pdo);

        // Check existence of tables and columns
        $tablesCountStmt = $this->pdo->query("SELECT COUNT(*) FROM dynamic_tables");
        $totalTablesCount = $tablesCountStmt !== false ? (int)$tablesCountStmt->fetchColumn() : 0;

        $totalColumnsCount = 0;
        if ($totalTablesCount > 0) {
            $colsCountStmt = $this->pdo->query("SELECT COUNT(*) FROM table_columns");
            $totalColumnsCount = $colsCountStmt !== false ? (int)$colsCountStmt->fetchColumn() : 0;
        }

        $tablesStmt = $this->pdo->query("SELECT id, table_name FROM dynamic_tables ORDER BY id ASC");
        /** @var array<int, array<string, mixed>> $allTables */
        $allTables = $tablesStmt !== false ? $tablesStmt->fetchAll(PDO::FETCH_ASSOC) : [];

        /** @var array<int, array<string, mixed>> $availableTables */
        $availableTables = [];
        foreach ($allTables as $t) {
            $tId = isset($t['id']) ? (int)$t['id'] : 0;
            if (user_can_view_table($this->pdo, $tId, $currentUser)) {
                $availableTables[] = $t;
            }
        }

        $queryGet = $_GET;
        $activeTableId = isset($queryGet['table_id'])
            ? (int)$queryGet['table_id']
            : (!empty($availableTables) ? (int)$availableTables[0]['id'] : 0);


        // No tables yet → fall through; the view shows the "create a table" empty state.
        // Only 403 when tables exist but this user cannot view the requested one.
        if ($totalTablesCount > 0 && ($activeTableId < 1 || !\user_can_view_table($this->pdo, $activeTableId, $currentUser))) {
            http_response_code(403);
            $basePath = defined('BASE_PATH') ? rtrim((string) BASE_PATH, '/') : '';
            echo '<!DOCTYPE html><html lang="en"><head><meta charset="UTF-8"><title>403</title></head><body>';
            echo '<p>403 Forbidden — you cannot view this table.</p>';
            echo '<p><a href="' . htmlspecialchars($basePath, ENT_QUOTES, 'UTF-8') . '/">Home</a></p>';
            echo '</body></html>';
            exit;
        }

        $userDateFormat = isset($currentUser['date_format']) && is_string($currentUser['date_format']) ? $currentUser['date_format'] : 'd/m/Y';
        $userTimezone = isset($currentUser['timezone']) && is_string($currentUser['timezone']) ? $currentUser['timezone'] : 'UTC';
        $fullFormatStr = \get_user_datetime_format($currentUser);

        $datePlaceholder = __('data_entry.date_placeholder_ymd');
        if ($userDateFormat === 'd/m/Y') {
            $datePlaceholder = __('data_entry.date_placeholder_dmy');
        } elseif ($userDateFormat === 'm/d/Y') {
            $datePlaceholder = __('data_entry.date_placeholder_mdy');
        }

        $colsStmt = $this->pdo->prepare("SELECT * FROM table_columns WHERE table_id = ? ORDER BY sort_order ASC, column_name ASC");
        $colsStmt->execute([$activeTableId]);
        /** @var array<int, array<string, mixed>> $columns */
        $columns = $colsStmt->fetchAll(PDO::FETCH_ASSOC);

        if (isset($queryGet['export_csv']) && $queryGet['export_csv'] === '1') {
            generate_csv_export($this->pdo, 'data-entry-records-export');
        }

        $message = $_SESSION['message'] ?? '';
        $error = $_SESSION['error'] ?? '';
        $duplicateWarning = $_SESSION['duplicate_warning'] ?? false;
        /** @var array<int, array<string, mixed>> $matches */
        $matches = $_SESSION['duplicate_matches'] ?? [];
        /** @var array<int, string> $submittedData */
        $submittedData = $_SESSION['submitted_filters'] ?? [];
        unset($_SESSION['message'], $_SESSION['error']);

        $page = max(1, isset($queryGet['page']) ? (int)$queryGet['page'] : 1);
        $perPage = 10;
        $offset = ($page - 1) * $perPage;
        
        /** @var array<int, string> $searchFilters */
        $searchFilters = isset($queryGet['filters']) && is_array($queryGet['filters']) ? $queryGet['filters'] : [];
        /** @var array<int, array{from?: string, to?: string}> $dateFilters */
        $dateFilters = isset($queryGet['date_filters']) && is_array($queryGet['date_filters']) ? $queryGet['date_filters'] : [];

        $hasActiveSearch = false;
        foreach ($searchFilters as $val) {
            if ($val !== '' && $val !== null) { $hasActiveSearch = true; break; }
        }
        if (!$hasActiveSearch) {
            foreach ($dateFilters as $df) {
                if (!empty($df['from']) || !empty($df['to'])) { $hasActiveSearch = true; break; }
            }
        }

        /** @var array<int, array<string, mixed>> $paginatedRecords */
        $paginatedRecords = [];
        $totalRecords = 0;
        $totalPages = 1;

        if ($totalTablesCount > 0 && $totalColumnsCount > 0) {
            $recordsStmt = $this->pdo->prepare("
                SELECT r.id, r.created_at, u.id as user_id, u.username, u.first_name, u.surname, u.attribution_display_mode 
                FROM records r 
                LEFT JOIN users u ON r.created_by = u.id 
                WHERE r.table_id = ? 
                ORDER BY r.id DESC
            ");
            $recordsStmt->execute([$activeTableId]);
            /** @var array<int, array<string, mixed>> $allRecords */
            $allRecords = $recordsStmt->fetchAll(PDO::FETCH_ASSOC);

            $valuesStmt = $this->pdo->query("SELECT record_id, column_id, value_content FROM record_values");
            /** @var array<int, array<string, mixed>> $rawValues */
            $rawValues = $valuesStmt !== false ? $valuesStmt->fetchAll(PDO::FETCH_ASSOC) : [];
            
            /** @var array<int, array<int, string>> $recordValues */
            $recordValues = [];
            foreach ($rawValues as $val) {
                $rId = isset($val['record_id']) ? (int)$val['record_id'] : 0;
                $cId = isset($val['column_id']) ? (int)$val['column_id'] : 0;
                $vCont = isset($val['value_content']) && is_string($val['value_content']) ? $val['value_content'] : '';
                $recordValues[$rId][$cId] = $vCont;
            }

            /** @var array<int, array<string, mixed>> $filteredRecords */
            $filteredRecords = [];
            foreach ($allRecords as $rec) {
                $recId = isset($rec['id']) ? (int)$rec['id'] : 0;
                if (record_matches_filters($recId, $recordValues, $searchFilters, $dateFilters)) {
                    $filteredRecords[] = $rec;
                }
            }
            $totalRecords = count($filtered_records = $filteredRecords);
            $totalPages = (int)ceil($totalRecords / $perPage);
            if ($totalPages < 1) {
                $totalPages = 1;
            }
            $paginatedRecords = array_slice($filtered_records, $offset, $perPage);
        }

        $isAdmin = function_exists('is_admin') && is_admin($this->pdo);
        $isModerationEnabled = is_module_enabled($this->pdo, 'moderation');

        require_once __DIR__ . '/../Views/user/data_entry.php';
    }
}
