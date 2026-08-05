<?php
/**
 * MIGRATED FILE MAPPING
 * ---------------------
 * Original Old File: api/search.php
 * Migrated Date: 2026-08-05 06:03:09
 */declare(strict_types=1);


namespace App\Controllers;

use PDO;

class ApiSearchController
{
    private PDO $pdo;

    public function __construct(PDO $pdo)
    {
        $this->pdo = $pdo;
    }

    public function search(): void
    {
        if (session_status() === PHP_SESSION_NONE) {
            session_start();
        }

        header('Content-Type: application/json; charset=utf-8');
        header('Cache-Control: no-store, no-cache, must-revalidate');

        // ------------------------------------------------------------------
        // Access: guest needs view_as_guest; everyone needs view_table_{id}
        // (No special case for table id 1; guests are checked too.)
        // ------------------------------------------------------------------
        /** @var array{id: int|string, date_format?: string}|null $currentUser */
        $currentUser = function_exists('get_current_user_data') ? get_current_user_data($this->pdo) : null;

        if ($currentUser === null && !guest_has_permission($this->pdo, 'view_as_guest')) {
            http_response_code(403);
            echo json_encode(['error' => __('api_search.error_public_forbidden')]);
            exit;
        }

        $queryGet = $_GET;
        $tableId = isset($queryGet['table_id']) ? (int)$queryGet['table_id'] : 1;
        if (!user_can_view_table($this->pdo, $tableId, $currentUser)) {
            http_response_code(403);
            echo json_encode(['error' => __('api_search.error_unauthorized_table')]);
            exit;
        }

        // ------------------------------------------------------------------
        // Rest of the original logic
        // ------------------------------------------------------------------
        $userDateFormat = 'd/m/Y';
        if ($currentUser !== null && isset($currentUser['date_format']) && is_string($currentUser['date_format'])) {
            $userDateFormat = $currentUser['date_format'];
        }

        $colsStmt = $this->pdo->prepare("SELECT * FROM table_columns WHERE table_id = ? ORDER BY sort_order ASC, column_name ASC");
        $colsStmt->execute([$tableId]);
        /** @var array<int, array<string, mixed>> $columns */
        $columns = $colsStmt->fetchAll(PDO::FETCH_ASSOC);

        /** @var array<mixed, mixed> $searchFilters */
        $searchFilters = isset($queryGet['filters']) && is_array($queryGet['filters']) ? $queryGet['filters'] : [];
        /** @var array<mixed, mixed> $dateFilters */
        $dateFilters = isset($queryGet['date_filters']) && is_array($queryGet['date_filters']) ? $queryGet['date_filters'] : [];
        
        $sortCol = isset($queryGet['sort']) && is_string($queryGet['sort']) ? $queryGet['sort'] : 'id';
        $sortDir = (isset($queryGet['dir']) && is_string($queryGet['dir']) && strtoupper($queryGet['dir']) === 'ASC') ? 'ASC' : 'DESC';
        $page = max(1, isset($queryGet['page']) ? (int)$queryGet['page'] : 1);
        $perPage = 10;
        $offset = ($page - 1) * $perPage;

        $orderClause = "ORDER BY r.id {$sortDir}";
        if ($sortCol === 'date') {
            $orderClause = "ORDER BY r.created_at {$sortDir}";
        }

        $recordsStmt = $this->pdo->prepare(
            "SELECT r.id, r.created_at, u.username
             FROM records r
             LEFT JOIN users u ON r.created_by = u.id
             WHERE r.table_id = ? {$orderClause}"
        );
        $recordsStmt->execute([$tableId]);
        /** @var array<int, array<string, mixed>> $records */
        $records = $recordsStmt->fetchAll(PDO::FETCH_ASSOC);

        $valuesStmt = $this->pdo->query("SELECT record_id, column_id, value_content FROM record_values");
        /** @var array<int, array<string, mixed>> $rawValues */
        $rawValues = $valuesStmt !== false ? $valuesStmt->fetchAll(PDO::FETCH_ASSOC) : [];
        
        /** @var array<int|string, array<int|string, string>> $recordValues */
        $recordValues = [];
        foreach ($rawValues as $val) {
            $recId = isset($val['record_id']) ? $val['record_id'] : 0;
            $colId = isset($val['column_id']) ? $val['column_id'] : 0;
            $valCont = isset($val['value_content']) && is_string($val['value_content']) ? $val['value_content'] : '';
            $recordValues[$recId][$colId] = $valCont;
        }

        /** @var array<int, array<string, mixed>> $matchedRecords */
        $matchedRecords = [];
        foreach ($records as $rec) {
            $recId = isset($rec['id']) ? (int)$rec['id'] : 0;
            if (record_matches_filters($recId, $recordValues, $searchFilters, $dateFilters)) {
                $matchedRecords[] = $rec;
            }
        }

        $totalMatched = count($matchedRecords);
        $totalPages = (int)ceil($totalMatched / $perPage);
        if ($totalPages < 1) {
            $totalPages = 1;
        }
        $paginatedRecords = array_slice($matchedRecords, $offset, $perPage);

        $isModerationEnabled = is_module_enabled($this->pdo, 'moderation');

        // Build HTML rows
        ob_start();
        if (empty($paginatedRecords)) {
            echo '<tr><td colspan="' . (count($columns) + 4) . '">' . htmlspecialchars(__('api_search.no_records'), ENT_QUOTES, 'UTF-8') . '</td></tr>';
        } else {
            foreach ($paginatedRecords as $rec) {
                $recId = isset($rec['id']) ? (int)$rec['id'] : 0;
                $recUsername = isset($rec['username']) && is_string($rec['username']) ? $rec['username'] : '';
                $recCreatedAt = isset($rec['created_at']) && is_string($rec['created_at']) ? $rec['created_at'] : '';

                echo '<tr>';
                echo '<td>#' . $recId . '</td>';
                foreach ($columns as $col) {
                    $cId = isset($col['id']) ? $col['id'] : 0;
                    $rawVal = $recordValues[$recId][$cId] ?? '';
                    $dataType = isset($col['data_type']) && is_string($col['data_type']) ? $col['data_type'] : '';
                    $boolFormat = isset($col['boolean_display_format']) && is_string($col['boolean_display_format']) ? $col['boolean_display_format'] : 'yes_no';

                    if ($dataType === 'BOOLEAN') {
                        $displayVal = format_boolean_value($rawVal, $boolFormat);
                    } elseif ($dataType === 'DATE') {
                        $displayVal = format_display_date($rawVal, $userDateFormat);
                    } else {
                        $displayVal = $rawVal;
                    }
                    echo '<td>' . htmlspecialchars($displayVal, ENT_QUOTES, 'UTF-8') . '</td>';
                }
                echo '<td>' . htmlspecialchars(obscure_name_ajax($recUsername), ENT_QUOTES, 'UTF-8') . '</td>';
                echo '<td>' . date('Y-m-d H:i', strtotime($recCreatedAt)) . '</td>';

                // Actions Column: History button + Suggest Edit button (if module enabled)
                echo '<td>';
                echo '<a href="/record_history.php?record_id=' . $recId . '" class="btn btn-secondary" style="padding:0.2rem 0.4rem;font-size:0.8rem;text-decoration:none;margin-right:4px;">' . htmlspecialchars(__('api_search.history_btn'), ENT_QUOTES, 'UTF-8') . '</a>';
                if ($isModerationEnabled) {
                    echo '<button type="button" class="btn" style="padding:0.2rem 0.4rem;font-size:0.8rem;" onclick="openSuggestModal(' . $recId . ')">' . htmlspecialchars(__('api_search.suggest_edit_btn'), ENT_QUOTES, 'UTF-8') . '</button>';
                }
                echo '</td>';

                echo '</tr>';
            }
        }
        $html = ob_get_clean();

        echo json_encode([
            'html'         => $html !== false ? $html : '',
            'total_pages'  => $totalPages,
            'current_page' => $page,
        ]);
        exit;
    }
}
