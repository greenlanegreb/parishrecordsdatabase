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

        $dtHelp = dirname(__DIR__, 2) . '/includes/datetime_helpers.php';
        if (is_file($dtHelp)) {
            require_once $dtHelp;
        }
        $siteTz = 'UTC';
        $siteDateFormat = 'd/m/Y';
        $siteTime = '24';
        if (function_exists('get_site_datetime_defaults')) {
            [$siteTz, $siteDateFormat, $siteTime] = get_site_datetime_defaults($this->pdo);
        }
        $viewer = is_array($currentUser) ? $currentUser : [];
        $userDateFormat = (
            isset($viewer['date_format']) && is_string($viewer['date_format']) && $viewer['date_format'] !== ''
        ) ? $viewer['date_format'] : $siteDateFormat;
        $userTimePref = (
            isset($viewer['time_format']) && is_string($viewer['time_format']) && $viewer['time_format'] !== ''
        ) ? $viewer['time_format'] : $siteTime;
        $userTimezone = (
            isset($viewer['timezone']) && is_string($viewer['timezone']) && $viewer['timezone'] !== ''
        ) ? $viewer['timezone'] : $siteTz;
        if (function_exists('get_user_time_prefs')) {
            [$userTimezone, ] = get_user_time_prefs($viewer, $this->pdo);
        }
        $timeBit = ($userTimePref === '12') ? ' h:i A' : ' H:i';
        $fullFormatStr = $userDateFormat . $timeBit;

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
                    $typeKey = strtoupper(trim((string) $dataType));
                    $rawStr = is_string($rawVal) ? $rawVal : (is_scalar($rawVal) ? (string) $rawVal : '');
                    $looksIsoDate = (bool) preg_match('/^\d{4}-\d{2}-\d{2}/', trim($rawStr));
                    if ($typeKey === 'BOOLEAN') {
                        $displayVal = function_exists('format_boolean_value')
                            ? format_boolean_value($rawVal, $boolFormat)
                            : $rawVal;
                    } elseif ($typeKey === 'LOCATION' && class_exists(\App\Services\LocationValueService::class)) {
                        $displayVal = \App\Services\LocationValueService::formatDisplay($rawStr);
                    } elseif ($typeKey === 'TIME') {
                        $displayVal = function_exists('format_display_time')
                            ? format_display_time($rawStr, $userTimePref)
                            : $rawStr;
                    } elseif ($typeKey === 'DATE' || $typeKey === 'DATETIME' || $looksIsoDate) {
                        $displayVal = function_exists('format_display_date')
                            ? format_display_date($rawStr, $userDateFormat)
                            : $rawStr;
                    } else {
                        $displayVal = $rawVal;
                    }
                    echo '<td data-col-id="' . (int) $cId . '">' . htmlspecialchars((string) $displayVal, ENT_QUOTES, 'UTF-8') . '</td>';
                }

                echo '<td class="text-end pe-2" data-col-id="actions">';
                $actionsLabel = (__('index.th_actions') !== 'index.th_actions') ? __('index.th_actions') : 'Actions';
                echo '<div class="dropdown d-inline-block">';
                echo '<button class="btn btn-sm btn-outline-dark dropdown-toggle" type="button" data-bs-toggle="dropdown" aria-expanded="false" aria-haspopup="true">'
                    . htmlspecialchars($actionsLabel, ENT_QUOTES, 'UTF-8') . '</button>';
                echo '<ul class="dropdown-menu dropdown-menu-end shadow-sm border-0 py-2">';
                $viewLabel = (__('record.view') !== 'record.view') ? __('record.view') : 'View record';
                echo '<li><a class="dropdown-item" href="' . htmlspecialchars($basePath . '/records/' . $recId, ENT_QUOTES, 'UTF-8') . '">'
                    . htmlspecialchars($viewLabel, ENT_QUOTES, 'UTF-8') . '</a></li>';
                echo '<li><a class="dropdown-item" href="record_history.php?record_id=' . $recId . '">'
                    . htmlspecialchars(__('api_search.history_btn'), ENT_QUOTES, 'UTF-8') . '</a></li>';

                if ($canSuggestEdit) {
                    echo '<li><button type="button" class="dropdown-item suggest-edit-btn" data-record-id="'
                        . $recId . '">'
                        . htmlspecialchars(__('api_search.suggest_edit_btn'), ENT_QUOTES, 'UTF-8') . '</button></li>';
                }

                if ($canEditRecords) {
                    $editLabel = (__('btn.edit') !== 'btn.edit') ? __('btn.edit') : 'Edit';
                    $returnUrl = $basePath . '/?table_id=' . (int) $tableId;
                    $editUrl = $basePath . '/records/' . $recId . '/edit?return=' . rawurlencode($returnUrl);
                    echo '<li><a class="dropdown-item" href="' . htmlspecialchars($editUrl, ENT_QUOTES, 'UTF-8') . '">'
                        . htmlspecialchars($editLabel, ENT_QUOTES, 'UTF-8') . '</a></li>';
                }

                if ($canDeleteRecords) {
                    $delLabel = (__('data_entry.delete_record_btn') !== 'data_entry.delete_record_btn')
                        ? __('data_entry.delete_record_btn') : 'Delete';
                    $delConfirm = (__('data_entry.delete_record_confirm') !== 'data_entry.delete_record_confirm')
                        ? __('data_entry.delete_record_confirm')
                        : 'Delete this record permanently? Values, map pins and related suggestions for it will be removed. This cannot be undone.';
                    $ref = isset($_SERVER['HTTP_REFERER']) && is_string($_SERVER['HTTP_REFERER']) ? $_SERVER['HTTP_REFERER'] : '';
                    $returnUrl = (str_contains($ref, '/data-entry'))
                        ? ($basePath . '/data-entry?table_id=' . (int) $tableId)
                        : ($basePath . '/?table_id=' . (int) $tableId);
                    $confirmJs = 'return confirm(' . json_encode($delConfirm, JSON_HEX_TAG | JSON_HEX_APOS | JSON_HEX_QUOT | JSON_HEX_AMP | JSON_UNESCAPED_UNICODE) . ');';
                    echo '<li><hr class="dropdown-divider"></li><li>';
                    echo '<form method="POST" action="' . htmlspecialchars($basePath . '/records/delete', ENT_QUOTES, 'UTF-8') . '">';
                    echo '<input type="hidden" name="csrf_token" value="' . htmlspecialchars((string) $csrfToken, ENT_QUOTES, 'UTF-8') . '">';
                    echo '<input type="hidden" name="record_id" value="' . $recId . '">';
                    echo '<input type="hidden" name="return_url" value="' . htmlspecialchars($returnUrl, ENT_QUOTES, 'UTF-8') . '">';
                    echo '<button type="submit" class="dropdown-item text-danger" onclick="'
                        . htmlspecialchars($confirmJs, ENT_QUOTES, 'UTF-8') . '">'
                        . htmlspecialchars($delLabel, ENT_QUOTES, 'UTF-8') . '</button>';
                    echo '</form></li>';
                }

                echo '</ul></div></td>';
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
