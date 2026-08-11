<?php
/**
 * MIGRATED FILE MAPPING
 * ---------------------
 * Original Old File: api/export_json.php
 * Migrated Date: 2026-08-05 06:01:20
 */
declare(strict_types=1);

namespace App\Controllers;

use PDO;

class ApiExportJsonController
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

        $queryGet = $_GET;
        $tableId = isset($queryGet['table_id']) ? (int) $queryGet['table_id'] : 1;

        if (!user_can_view_table($this->pdo, $tableId, $currentUser)) {
            http_response_code(403);
            header('Content-Type: application/json; charset=utf-8');
            echo json_encode(['error' => 'Unauthorized: You do not have permission to view or export this table.']);
            exit;
        }

        $remoteAddr = isset($_SERVER['REMOTE_ADDR']) && is_string($_SERVER['REMOTE_ADDR'])
            ? $_SERVER['REMOTE_ADDR'] : '127.0.0.1';
        $audit = $this->pdo->prepare(
            'INSERT INTO audit_logs (user_id, action, details, ip_address) VALUES (?, ?, ?, ?)'
        );
        $audit->execute([
            $userId,
            'JSON_EXPORT',
            "Generated JSON database export for table ID {$tableId}",
            $remoteAddr,
        ]);

        $colsStmt = $this->pdo->prepare(
            'SELECT id, column_name, data_type FROM table_columns WHERE table_id = ? ORDER BY sort_order ASC, id ASC'
        );
        $colsStmt->execute([$tableId]);
        /** @var array<int, array<string, mixed>> $columns */
        $columns = $colsStmt->fetchAll(PDO::FETCH_ASSOC);

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

        /** @var array<int, array<string, mixed>> $exportData */
        $exportData = [];
        foreach ($records as $rec) {
            $recId = isset($rec['id']) ? (int) $rec['id'] : 0;
            if (record_matches_filters($recId, $recordValues, $searchFilters, $dateFilters)) {
                $row = ['record_id' => $recId];
                foreach ($columns as $col) {
                    $cId = isset($col['id']) ? $col['id'] : 0;
                    $colName = isset($col['column_name']) && is_string($col['column_name']) ? $col['column_name'] : '';
                    $row[$colName] = $recordValues[$recId][$cId] ?? null;
                }

                $createdById = isset($rec['created_by']) ? (int) $rec['created_by'] : 0;
                if ($createdById > 0 && function_exists('format_user_display_name_by_id')) {
                    $row['created_by'] = format_user_display_name_by_id($this->pdo, $createdById, $currentUser);
                } else {
                    $row['created_by'] = 'User_Anon';
                }
                $row['date_added'] = isset($rec['created_at']) && is_string($rec['created_at'])
                    ? $rec['created_at'] : '';
                $exportData[] = $row;
            }
        }

        header('Content-Type: application/json; charset=utf-8');
        header('Content-Disposition: attachment; filename="prd-table-' . $tableId . '-export-' . date('Y-m-d') . '.json"');
        header('Cache-Control: no-store, no-cache, must-revalidate');

        echo json_encode([
            'system' => 'Parish Records Database (PRD)',
            'table_id' => $tableId,
            'export_date' => date('Y-m-d H:i:s'),
            'total_records' => count($exportData),
            'data' => $exportData,
        ], JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE);
        exit;
    }
}
