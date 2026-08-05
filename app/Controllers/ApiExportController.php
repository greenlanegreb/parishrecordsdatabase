<?php
/**
 * MIGRATED FILE MAPPING
 * ---------------------
 * Original Old File: api/export.php
 * Migrated Date: 2026-08-05 05:59:46
 */declare(strict_types=1);


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
        if (session_status() === PHP_SESSION_NONE) {
            session_start();
        }

        /** @var array{id: int|string, username?: string} $currentUser */
        $currentUser = require_permission($this->pdo, 'export_data', 'Export database records and search result sets to CSV');
        $userId = $currentUser['id'];
        $currentUsername = isset($currentUser['username']) && is_string($currentUser['username']) ? $currentUser['username'] : 'User';

        $remoteAddr = isset($_SERVER['REMOTE_ADDR']) && is_string($_SERVER['REMOTE_ADDR']) ? $_SERVER['REMOTE_ADDR'] : '127.0.0.1';
        $audit = $this->pdo->prepare("INSERT INTO audit_logs (user_id, action, details, ip_address) VALUES (?, 'CSV_EXPORT', ?, ?)");
        $audit->execute([$userId, "Generated CSV database export with citation header", $remoteAddr]);

        $systemName = get_system_name($this->pdo);

        // Fetch columns and records
        $colsStmt = $this->pdo->query("SELECT * FROM table_columns ORDER BY id ASC");
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

        header('Content-Type: text/csv; charset=utf-8');
        header('Content-Disposition: attachment; filename="prd-export-' . date('Y-m-d') . '.csv"');
        header('Cache-Control: no-store, no-cache, must-revalidate');
        
        $output = fopen('php://output', 'w');
        if ($output === false) {
            http_response_code(500);
            exit('Failed to open output stream.');
        }

        // --- Citation & Metadata Header Rows ---
        fputcsv($output, ["# Source System: " . $systemName]);
        fputcsv($output, ["# Export Generated On: " . date('Y-m-d H:i:s')]);
        fputcsv($output, ["# Exported By: " . $currentUsername]);
        fputcsv($output, ["# --------------------------------------------------"]);
        fputcsv($output, []); // Blank spacer row

        // Standard Column Headers
        /** @var array<int, string> $headerRow */
        $headerRow = ['Record ID'];
        foreach ($columns as $col) {
            $colName = isset($col['column_name']) && is_string($col['column_name']) ? $col['column_name'] : '';
            $headerRow[] = $colName;
        }
        $headerRow[] = 'Created By';
        $headerRow[] = 'Date Added';
        fputcsv($output, $headerRow);

        $userDateFormat = isset($_SESSION['date_format']) && is_string($_SESSION['date_format']) ? $_SESSION['date_format'] : 'd/m/Y';

        // Data Rows
        foreach ($records as $rec) {
            $recId = isset($rec['id']) ? (int)$rec['id'] : 0;
            if (record_matches_filters($recId, $recordValues, $searchFilters, $dateFilters)) {
                $row = ['#' . $recId];
                foreach ($columns as $col) {
                    $cId = isset($col['id']) ? $col['id'] : 0;
                    $rawVal = $recordValues[$recId][$cId] ?? '';
                    $dataType = isset($col['data_type']) && is_string($col['data_type']) ? $col['data_type'] : '';
                    $boolFormat = isset($col['boolean_display_format']) && is_string($col['boolean_display_format']) ? $col['boolean_display_format'] : 'yes_no';

                    if ($dataType === 'BOOLEAN') {
                        $row[] = format_boolean_value($rawVal, $boolFormat);
                    } elseif ($dataType === 'DATE') {
                        $row[] = format_display_date($rawVal, $userDateFormat);
                    } else {
                        $row[] = $rawVal;
                    }
                }
                $recUsername = isset($rec['username']) && is_string($rec['username']) ? $rec['username'] : 'User_Anon';
                $recCreatedAt = isset($rec['created_at']) && is_string($rec['created_at']) ? $rec['created_at'] : '';
                
                $row[] = $recUsername;
                $row[] = $recCreatedAt;
                fputcsv($output, $row);
            }
        }
        fclose($output);
        exit;
    }
}
