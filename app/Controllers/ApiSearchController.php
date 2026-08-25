<?php
/**
 * MIGRATED FILE MAPPING
 * ---------------------
 * Original Old File: api/search.php
 * Migrated Date: 2026-08-05 06:03:09
 */
declare(strict_types=1);

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
        header('Content-Type: application/json; charset=utf-8');
        header('Cache-Control: no-store, no-cache, must-revalidate');

        /** @var array{id: int|string, date_format?: string, timezone?: string}|null $currentUser */
        $currentUser = function_exists('get_current_user_data') ? get_current_user_data($this->pdo) : null;
        if ($currentUser === null && !guest_has_permission($this->pdo, 'view_as_guest')) {
            http_response_code(403);
            echo json_encode(['error' => __('api_search.error_public_forbidden')]);
            exit;
        }

        $queryGet = $_GET;
        $tableId = isset($queryGet['table_id']) ? (int) $queryGet['table_id'] : 1;
        if (!user_can_view_table($this->pdo, $tableId, $currentUser)) {
            http_response_code(403);
            echo json_encode(['error' => __('api_search.error_unauthorized_table')]);
            exit;
        }

        $siteDateFormat = function_exists('get_setting')
            ? get_setting($this->pdo, 'default_date_format', 'd/m/Y')
            : 'd/m/Y';
        if ($siteDateFormat === '') {
            $siteDateFormat = 'd/m/Y';
        }

        if ($currentUser !== null) {
            $userDateFormat = (
                isset($currentUser['date_format']) && is_string($currentUser['date_format']) && $currentUser['date_format'] !== ''
            ) ? $currentUser['date_format'] : $siteDateFormat;
            $userTimezone = (
                isset($currentUser['timezone']) && is_string($currentUser['timezone']) && $currentUser['timezone'] !== ''
            ) ? $currentUser['timezone'] : 'UTC';
            $fullFormatStr = function_exists('get_user_datetime_format')
                ? get_user_datetime_format($currentUser)
                : ($userDateFormat . ' H:i');
        } else {
            $userDateFormat = $siteDateFormat;
            $userTimezone = 'UTC';
            $fullFormatStr = $userDateFormat . ' H:i';
        }

        $colsStmt = $this->pdo->prepare(
            'SELECT * FROM table_columns WHERE table_id = ? ORDER BY sort_order ASC, column_name ASC'
        );
        $colsStmt->execute([$tableId]);
        /** @var array<int, array<string, mixed>> $columns */
        $columns = $colsStmt->fetchAll(PDO::FETCH_ASSOC);

        $visHelper = dirname(__DIR__, 2) . '/includes/column_visibility.php';
        if (is_file($visHelper)) {
            require_once $visHelper;
        }
        if (function_exists('resolve_visible_columns')) {
            $columns = resolve_visible_columns($columns, $tableId);
        }

        /** @var array<mixed, mixed> $searchFilters */
        $searchFilters = isset($queryGet['filters']) && is_array($queryGet['filters']) ? $queryGet['filters'] : [];
        /** @var array<mixed, mixed> $dateFilters */
        $dateFilters = isset($queryGet['date_filters']) && is_array($queryGet['date_filters']) ? $queryGet['date_filters'] : [];
        $sortCol = isset($queryGet['sort']) && is_string($queryGet['sort']) ? $queryGet['sort'] : 'id';
        $sortDir = (isset($queryGet['dir']) && is_string($queryGet['dir']) && strtoupper($queryGet['dir']) === 'ASC')
            ? 'ASC' : 'DESC';
        $page = max(1, isset($queryGet['page']) ? (int) $queryGet['page'] : 1);
        $perPage = 10;
        $offset = ($page - 1) * $perPage;

        $orderClause = "ORDER BY r.id {$sortDir}";
        if ($sortCol === 'date') {
            $orderClause = "ORDER BY r.created_at {$sortDir}";
        }

        $recordsStmt = $this->pdo->prepare(
            "SELECT r.id, r.created_at, r.created_by,
                    u.username, u.first_name, u.surname, u.attribution_display_mode
             FROM records r
             LEFT JOIN users u ON r.created_by = u.id
             WHERE r.table_id = ? {$orderClause}"
        );
        $recordsStmt->execute([$tableId]);
        /** @var array<int, array<string, mixed>> $records */
        $records = $recordsStmt->fetchAll(PDO::FETCH_ASSOC);

        $valuesStmt = $this->pdo->prepare("
            SELECT rv.record_id, rv.column_id, rv.value_content
            FROM record_values rv
            JOIN records r ON rv.record_id = r.id
            WHERE r.table_id = ?
        ");
        $valuesStmt->execute([$tableId]);
        /** @var array<int, array<string, mixed>> $rawValues */
        $rawValues = $valuesStmt->fetchAll(PDO::FETCH_ASSOC);

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
            $recId = isset($rec['id']) ? (int) $rec['id'] : 0;
            if (record_matches_filters($recId, $recordValues, $searchFilters, $dateFilters)) {
                $matchedRecords[] = $rec;
            }
        }

        $totalMatched = count($matchedRecords);
        $totalPages = (int) ceil($totalMatched / $perPage);
        if ($totalPages < 1) {
            $totalPages = 1;
        }
        $paginatedRecords = array_slice($matchedRecords, $offset, $perPage);

        $isModerationEnabled = is_module_enabled($this->pdo, 'moderation');
        $canSuggestEdit = function_exists('can_suggest_edit')
            ? can_suggest_edit($this->pdo)
            : $isModerationEnabled;
        $canDeleteRecords = function_exists('has_permission')
            && has_permission($this->pdo, 'delete_records');
        $canEditRecords = function_exists('has_permission')
            && has_permission($this->pdo, 'edit_records');

        $basePath = defined('BASE_PATH') ? rtrim((string) BASE_PATH, '/') : '';
        $csrfToken = function_exists('generate_csrf_token') ? generate_csrf_token() : '';

        ob_start();
        if (empty($paginatedRecords)) {
            echo '<tr><td colspan="' . (count($columns) + 3) . '" class="text-center py-4 text-muted">'
                . htmlspecialchars(__('api_search.no_records'), ENT_QUOTES, 'UTF-8')
                . '</td></tr>';
        } else {
            foreach ($paginatedRecords as $rec) {
                $recId = isset($rec['id']) ? (int) $rec['id'] : 0;
                $recCreatedAt = isset($rec['created_at']) && is_string($rec['created_at']) ? $rec['created_at'] : '';
                echo '<tr>';
                foreach ($columns as $col) {
                    $cId = isset($col['id']) ? $col['id'] : 0;
                    $rawVal = $recordValues[$recId][$cId] ?? '';
                    $dataType = isset($col['data_type']) && is_string($col['data_type']) ? $col['data_type'] : '';
                    $boolFormat = isset($col['boolean_display_format']) && is_string($col['boolean_display_format'])
                        ? $col['boolean_display_format'] : 'yes_no';
                    if ($dataType === 'BOOLEAN') {
                        $displayVal = format_boolean_value($rawVal, $boolFormat);
                    } elseif ($dataType === 'DATE') {
                        $displayVal = format_display_date($rawVal, $userDateFormat);
                    } else {
                        $displayVal = $rawVal;
                    }
                    echo '<td>' . htmlspecialchars((string) $displayVal, ENT_QUOTES, 'UTF-8') . '</td>';
                }

                $creatorId = isset($rec['created_by']) ? (int) $rec['created_by'] : 0;
                if ($creatorId > 0) {
                    $creator = [
                        'id' => $creatorId,
                        'username' => isset($rec['username']) && is_string($rec['username']) ? $rec['username'] : '',
                        'first_name' => isset($rec['first_name']) && is_string($rec['first_name']) ? $rec['first_name'] : '',
                        'surname' => isset($rec['surname']) && is_string($rec['surname']) ? $rec['surname'] : '',
                        'attribution_display_mode' => isset($rec['attribution_display_mode']) && is_string($rec['attribution_display_mode'])
                            ? $rec['attribution_display_mode']
                            : 'initials_random',
                    ];
                    $createdByLabel = function_exists('format_user_display_name')
                        ? format_user_display_name($this->pdo, $creator, $currentUser)
                        : 'Contributor';
                } else {
                    $createdByLabel = 'System';
                }

                if (!function_exists('show_created_by_column') || show_created_by_column($tableId)) {
                    echo '<td>' . htmlspecialchars($createdByLabel, ENT_QUOTES, 'UTF-8') . '</td>';
                }
                if (!function_exists('show_created_at_column') || show_created_at_column($tableId)) {
                    echo '<td>' . htmlspecialchars(format_user_time($recCreatedAt, $userTimezone, $fullFormatStr), ENT_QUOTES, 'UTF-8') . '</td>';
                }

                echo '<td class="text-end pe-3 text-nowrap">';

                // History
                echo '<a href="record_history.php?record_id=' . $recId
                    . '" class="btn btn-sm btn-outline-secondary py-0 px-2 text-decoration-none me-1" style="font-size: 0.75rem;">'
                    . htmlspecialchars(__('api_search.history_btn'), ENT_QUOTES, 'UTF-8') . '</a>';

                // Suggest edit (moderation module / can_suggest_edit)
                if ($canSuggestEdit) {
                    echo '<button type="button" class="btn btn-sm btn-outline-primary py-0 px-2 suggest-edit-btn me-1" data-record-id="'
                        . $recId . '" style="font-size: 0.75rem;">'
                        . htmlspecialchars(__('api_search.suggest_edit_btn'), ENT_QUOTES, 'UTF-8') . '</button>';
                }

                // Direct edit (permission: edit_records) — independent of moderation module
                if ($canEditRecords) {
                    $editLabel = (__('btn.edit') !== 'btn.edit') ? __('btn.edit') : 'Edit';
                    $returnUrl = $basePath . '/';
                    $editUrl = $basePath . '/records/' . $recId . '/edit?return=' . rawurlencode($returnUrl);
                    echo '<a href="' . htmlspecialchars($editUrl, ENT_QUOTES, 'UTF-8')
                        . '" class="btn btn-sm btn-outline-success py-0 px-2 me-1 text-decoration-none" style="font-size: 0.75rem;">'
                        . htmlspecialchars($editLabel, ENT_QUOTES, 'UTF-8') . '</a>';
                }

                // Delete (permission: delete_records)
                if ($canDeleteRecords) {
                    $delLabel = (__('data_entry.delete_record_btn') !== 'data_entry.delete_record_btn')
                        ? __('data_entry.delete_record_btn') : 'Delete';
                    $delConfirm = (__('data_entry.delete_record_confirm') !== 'data_entry.delete_record_confirm')
                        ? __('data_entry.delete_record_confirm')
                        : 'Delete this record permanently? Values, map pins and related suggestions for it will be removed. This cannot be undone.';
                    $returnUrl = $basePath . '/data-entry';
                    // Confirm on the button (more reliable than form onsubmit when HTML is injected via AJAX)
                    $confirmJs = 'return confirm(' . json_encode($delConfirm, JSON_HEX_TAG | JSON_HEX_APOS | JSON_HEX_QUOT | JSON_HEX_AMP | JSON_UNESCAPED_UNICODE) . ');';
                    echo '<form method="POST" action="' . htmlspecialchars($basePath . '/records/delete', ENT_QUOTES, 'UTF-8')
                        . '" class="d-inline-block ms-1">';
                    echo '<input type="hidden" name="csrf_token" value="' . htmlspecialchars((string) $csrfToken, ENT_QUOTES, 'UTF-8') . '">';
                    echo '<input type="hidden" name="record_id" value="' . $recId . '">';
                    echo '<input type="hidden" name="return_url" value="' . htmlspecialchars($returnUrl, ENT_QUOTES, 'UTF-8') . '">';
                    echo '<button type="submit" class="btn btn-sm btn-outline-danger py-0 px-2" style="font-size: 0.75rem;" onclick="'
                        . htmlspecialchars($confirmJs, ENT_QUOTES, 'UTF-8') . '">'
                        . htmlspecialchars($delLabel, ENT_QUOTES, 'UTF-8') . '</button>';
                    echo '</form>';
                }

                echo '</td>';
                echo '</tr>';
            }
        }
        $html = ob_get_clean();

        echo json_encode([
            'html' => $html !== false ? $html : '',
            'total_pages' => $totalPages,
            'current_page' => $page,
        ]);
        exit;
    }
}
