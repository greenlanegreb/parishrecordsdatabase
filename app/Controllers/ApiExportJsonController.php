<?php
/**
 * MIGRATED FILE MAPPING
 * ---------------------
 * Original Old File: api/export_json.php
 * Migrated Date: 2026-08-05 06:01:20
 */declare(strict_types=1);


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
        if (session_status() === PHP_SESSION_NONE) {
            session_start();
        }

        /** @var array{id: int|string, username?: string} $currentUser */
        $currentUser = require_permission($this->pdo, 'export_data', 'Export database records to JSON');
        $userId = $currentUser['id'];

        $remoteAddr = isset($_SERVER['REMOTE_ADDR']) && is_string($_SERVER['REMOTE_ADDR']) ? $_SERVER['REMOTE_ADDR'] : '127.0.0.1';

        // Log export activity
        $audit = $this->pdo->prepare("INSERT INTO audit_logs (user_id, action, details, ip_address) VALUES (?, 'JSON_EXPORT', ?, ?)");
        $audit->execute([$userId, "Generated JSON database export", $remoteAddr]);

        $colsStmt = $this->pdo->query("SELECT id, column_name, data_type FROM table_columns ORDER BY id ASC");
        /** @var array<int, array<string, mixed>> $columns */
        $columns = $colsStmt !== false ? $colsStmt->fetchAll(PDO::FETCH_ASSOC) : [];

        $queryGet = $_GET;
        /** @var array<mixed, mixed> $searchFilters */
        $searchFilters = isset($queryGet['filters']) && is_array($queryGet['filters']) ? $queryGet['filters'] : [];
        /** @var array<mixed, mixed> $dateFilters */
        $dateFilters = isset($queryGet['date_filters']) && is_array($queryGet['date_filters']) ? $queryGet['date_filters'] : [];

        $recordsStmt = $this->pdo->query("SELECT r.id, r.created_at, u.username FROM records r LEFT JOIN users u ON r.created_by = u.id ORDER BY r.id DESC");
        /** @var array<int, array<string, mixed>> $records */
        $records = $recordsStmt !== false ? $recordsStmt->fetchAll(PDO::FETCH_ASSOC) : [];

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

        /** @var array<int, array<string, mixed>> $exportData */
        $exportData = [];
        foreach ($records as $rec) {
            $recId = isset($rec['id']) ? (int)$rec['id'] : 0;
            if (record_matches_filters($recId, $recordValues, $searchFilters, $dateFilters)) {
                $row = ['record_id' => $recId];
                foreach ($columns as $col) {
                    $cId = isset($col['id']) ? $col['id'] : 0;
                    $colName = isset($col['column_name']) && is_string($col['column_name']) ? $col['column_name'] : '';
                    $row[$colName] = $recordValues[$recId][$cId] ?? null;
                }
                $recUsername = isset($rec['username']) && is_string($rec['username']) ? $rec['username'] : 'User_Anon';
                $recCreatedAt = isset($rec['created_at']) && is_string($rec['created_at']) ? $rec['created_at'] : '';

                $row['created_by'] = $recUsername;
                $row['date_added'] = $recCreatedAt;
                $exportData[] = $row;
            }
        }

        header('Content-Type: application/json; charset=utf-8');
        header('Content-Disposition: attachment; filename="prd-export-' . date('Y-m-d') . '.json"');
        header('Cache-Control: no-store, no-cache, must-revalidate');

        echo json_encode([
            'system' => 'Parish Records Database (PRD)',
            'export_date' => date('Y-m-d H:i:s'),
            'total_records' => count($exportData),
            'data' => $exportData
        ], JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE);
        exit;
    }
}
