<?php
/**
 * MIGRATED FILE MAPPING
 * ---------------------
 * Original Old File: admin/actions/download_database_backup.php
 * Migrated Date: 2026-08-05 04:28:31
 */
declare(strict_types=1);

namespace App\Controllers;

use Exception;
use PDO;

class AdminBackupController
{
    private PDO $pdo;

    public function __construct(PDO $pdo)
    {
        $this->pdo = $pdo;
    }

    public function downloadBackup(): void
    {
        $serverMethod = isset($_SERVER['REQUEST_METHOD']) && is_string($_SERVER['REQUEST_METHOD']) ? $_SERVER['REQUEST_METHOD'] : 'GET';
        if ($serverMethod !== 'POST') {
            http_response_code(405);
            exit('Method Not Allowed');
        }

        verify_csrf_token();
        /** @var array{id: int, username: string} $currentUser */
        $currentUser = require_permission($this->pdo, 'manage_settings', 'Manage global site settings, mail drivers, and maintenance mode');

        $filename = 'prd-backup-' . date('Y-m-d-His') . '.sql';

        header('Content-Type: application/sql; charset=utf-8');
        header('Content-Disposition: attachment; filename="' . $filename . '"');
        header('Cache-Control: no-store, no-cache, must-revalidate');

        $out = fopen('php://output', 'w');
        if ($out === false) {
            http_response_code(500);
            exit('Failed to open output stream.');
        }

        $schemaVersion = function_exists('get_schema_version') ? get_schema_version($this->pdo) : 0;

        fwrite($out, "-- pRD database backup\n");
        fwrite($out, "-- Generated: " . gmdate('Y-m-d H:i:s') . " UTC\n");
        fwrite($out, "-- Schema version: " . $schemaVersion . "\n\n");
        fwrite($out, "SET NAMES utf8mb4;\nSET FOREIGN_KEY_CHECKS=0;\n\n");

        $tablesStmt = $this->pdo->query("SHOW TABLES");
        /** @var array<int, string> $tables */
        $tables = $tablesStmt !== false ? $tablesStmt->fetchAll(PDO::FETCH_COLUMN) : [];

        foreach ($tables as $table) {
            $escapedTable = str_replace('`', '``', $table);

            $createStmt = $this->pdo->query("SHOW CREATE TABLE `{$escapedTable}`");
            /** @var array<int, mixed>|false $create */
            $create = $createStmt !== false ? $createStmt->fetch(PDO::FETCH_NUM) : false;

            if ($create !== false && isset($create[1]) && is_string($create[1])) {
                fwrite($out, "DROP TABLE IF EXISTS `{$escapedTable}`;\n");
                fwrite($out, $create[1] . ";\n\n");
            }

            $rowsStmt = $this->pdo->query("SELECT * FROM `{$escapedTable}`");
            if ($rowsStmt !== false) {
                while ($row = $rowsStmt->fetch(PDO::FETCH_ASSOC)) {
                    $cols = [];
                    $vals = [];
                    foreach ($row as $col => $val) {
                        $cols[] = '`' . str_replace('`', '``', (string)$col) . '`';
                        if ($val === null) {
                            $vals[] = 'NULL';
                        } else {
                            $quoted = $this->pdo->quote((string)$val);
                            $vals[] = $quoted !== false ? $quoted : 'NULL';
                        }
                    }
                    fwrite(
                        $out,
                        "INSERT INTO `{$escapedTable}` (" . implode(', ', $cols) . ") VALUES (" . implode(', ', $vals) . ");\n"
                    );
                }
            }
            fwrite($out, "\n");
        }

        fwrite($out, "SET FOREIGN_KEY_CHECKS=1;\n");
        fclose($out);

        // Audit log the successful backup generation
        $remoteAddr = isset($_SERVER['REMOTE_ADDR']) && is_string($_SERVER['REMOTE_ADDR']) ? $_SERVER['REMOTE_ADDR'] : '127.0.0.1';
        try {
            $audit = $this->pdo->prepare("INSERT INTO audit_logs (`user_id`, `action`, `details`, `ip_address`, `created_at`) VALUES (?, 'DOWNLOAD_DATABASE_BACKUP', ?, ?, NOW())");
            $audit->execute([$currentUser['id'], "Downloaded full SQL database backup ({$filename})", $remoteAddr]);
        } catch (Exception $e) {
            // Suppress audit logging failure if stream is already closed/sent
        }

        exit;
    }
}
