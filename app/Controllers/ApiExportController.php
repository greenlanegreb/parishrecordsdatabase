<?php
/**
 * MIGRATED FILE MAPPING
 * ---------------------
 * Original Old File: api/export.php
 * Migrated Date: 2026-08-05 05:59:46
 */
declare(strict_types=1);

namespace App\Controllers;

use PDO;

class ApiExportController
{
    private PDO $pdo;

    public function __construct(PDO $pdo)
    {
        $this->pdo = $pdo;
    }

    public function export(): void
    {
        /** @var array{id: int|string, username?: string, date_format?: string}|null $currentUser */
        $currentUser = require_export_access($this->pdo);
        $userId = ($currentUser !== null && isset($currentUser['id'])) ? $currentUser['id'] : null;

        $exportedBy = 'Guest';
        if ($currentUser !== null && function_exists('format_user_display_name')) {
            $exportedBy = format_user_display_name($this->pdo, $currentUser, $currentUser);
        } elseif ($currentUser !== null && isset($currentUser['username']) && is_string($currentUser['username'])) {
            $exportedBy = $currentUser['username'];
        }

        $queryGet = $_GET;

        $tableId = isset($queryGet['table_id']) ? (int) $queryGet['table_id'] : 1;
        if (!user_can_view_table($this->pdo, $tableId, $currentUser)) {
            http_response_code(403);
            exit('Unauthorized: You do not have permission to view or export this table.');
        }

        $remoteAddr = isset($_SERVER['REMOTE_ADDR']) && is_string($_SERVER['REMOTE_ADDR'])
            ? $_SERVER['REMOTE_ADDR'] : '127.0.0.1';
        $audit = $this->pdo->prepare(
            'INSERT INTO audit_logs (user_id, action, details, ip_address) VALUES (?, ?, ?, ?)'
        );
        $audit->execute([
            $userId,
            'CSV_EXPORT',
            "Generated CSV database export for table ID {$tableId}",
            $remoteAddr,
        ]);

        $systemName = get_system_name($this->pdo);

        $colsStmt = $this->pdo->prepare(
            'SELECT * FROM table_columns WHERE table_id = ? ORDER BY sort_order ASC, id ASC'
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

        $recordsStmt = $this->pdo->prepare(
            'SELECT r.id, r.created_at, r.created_by FROM records r WHERE r.table_id = ? ORDER BY r.id DESC'
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

        header('Content-Type: text/csv; charset=utf-8');
        header('Content-Disposition: attachment; filename="prd-table-' . $tableId . '-export-' . date('Y-m-d') . '.csv"');
        header('Cache-Control: no-store, no-cache, must-revalidate');

        $output = fopen('php://output', 'w');
        if ($output === false) {
            http_response_code(500);
            exit('Failed to open output stream.');
        }

        fputcsv($output, ['# Source System: ' . $systemName], ',', '"', '\\');
        fputcsv($output, ['# Export Generated On: ' . date('Y-m-d H:i:s')], ',', '"', '\\');
        fputcsv($output, ['# Exported By: ' . $exportedBy], ',', '"', '\\');
        fputcsv($output, ['# --------------------------------------------------'], ',', '"', '\\');
        fputcsv($output, [], ',', '"', '\\');

        /** @var array<int, string> $headerRow */
        $headerRow = ['Record ID'];
        foreach ($columns as $col) {
            $colName = isset($col['column_name']) && is_string($col['column_name']) ? $col['column_name'] : '';
            $headerRow[] = $colName;
        }
        if (!function_exists('show_created_by_column') || show_created_by_column($tableId)) {
            $headerRow[] = 'Created By';
        }
        if (!function_exists('show_created_at_column') || show_created_at_column($tableId)) {
            $headerRow[] = 'Date Added';
        }
        fputcsv($output, $headerRow, ',', '"', '\\');

        $userDateFormat = isset($currentUser['date_format']) && is_string($currentUser['date_format'])
            ? $currentUser['date_format'] : 'd/m/Y';

        foreach ($records as $rec) {
            $recId = isset($rec['id']) ? (int) $rec['id'] : 0;
            if (record_matches_filters($recId, $recordValues, $searchFilters, $dateFilters)) {
                $row = ['#' . $recId];
                foreach ($columns as $col) {
                    $cId = isset($col['id']) ? $col['id'] : 0;
                    $rawVal = $recordValues[$recId][$cId] ?? '';
                    $dataType = isset($col['data_type']) && is_string($col['data_type']) ? $col['data_type'] : '';
                    $boolFormat = isset($col['boolean_display_format']) && is_string($col['boolean_display_format'])
                        ? $col['boolean_display_format'] : 'yes_no';

                    if ($dataType === 'BOOLEAN') {
                        $row[] = format_boolean_value($rawVal, $boolFormat);
                    } elseif ($dataType === 'DATE') {
                        $row[] = format_display_date($rawVal, $userDateFormat);
                    } else {
                        $row[] = $rawVal;
                    }
                }

                $createdById = isset($rec['created_by']) ? (int) $rec['created_by'] : 0;
                if ($createdById > 0 && function_exists('format_user_display_name_by_id')) {
                    $createdByLabel = format_user_display_name_by_id($this->pdo, $createdById, $currentUser);
                } else {
                    $createdByLabel = 'User_Anon';
                }
                $recCreatedAt = isset($rec['created_at']) && is_string($rec['created_at']) ? $rec['created_at'] : '';

                if (!function_exists('show_created_by_column') || show_created_by_column($tableId)) {
                    $row[] = $createdByLabel;
                }
                if (!function_exists('show_created_at_column') || show_created_at_column($tableId)) {
                    $row[] = $recCreatedAt;
                }
                fputcsv($output, $row, ',', '"', '\\');
            }
        }
        fclose($output);
        exit;
    }
}
