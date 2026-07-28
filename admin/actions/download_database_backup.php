<?php
// admin/actions/download_database_backup.php - Download full SQL backup (admin only)
require_once '../../db/db.php';
require_once '../../db/auth_helpers.php';
require_once '../../includes/functions.php';

if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    http_response_code(405);
    exit('Method Not Allowed');
}

verify_csrf_token();
require_permission($pdo, 'manage_settings', 'Manage global site settings, mail drivers, and maintenance mode');

$filename = 'prd-backup-' . date('Y-m-d-His') . '.sql';

header('Content-Type: application/sql; charset=utf-8');
header('Content-Disposition: attachment; filename="' . $filename . '"');
header('Cache-Control: no-store, no-cache, must-revalidate');

$out = fopen('php://output', 'w');

fwrite($out, "-- PRD database backup\n");
fwrite($out, "-- Generated: " . gmdate('Y-m-d H:i:s') . " UTC\n");
fwrite($out, "-- Schema version: " . get_schema_version($pdo) . "\n\n");
fwrite($out, "SET NAMES utf8mb4;\nSET FOREIGN_KEY_CHECKS=0;\n\n");

$tables = $pdo->query("SHOW TABLES")->fetchAll(PDO::FETCH_COLUMN);

foreach ($tables as $table) {
    $table = str_replace('`', '``', $table);

    $create = $pdo->query("SHOW CREATE TABLE `{$table}`")->fetch(PDO::FETCH_NUM);
    if (!empty($create[1])) {
        fwrite($out, "DROP TABLE IF EXISTS `{$table}`;\n");
        fwrite($out, $create[1] . ";\n\n");
    }

    $rows = $pdo->query("SELECT * FROM `{$table}`");
    while ($row = $rows->fetch(PDO::FETCH_ASSOC)) {
        $cols = [];
        $vals = [];
        foreach ($row as $col => $val) {
            $cols[] = '`' . str_replace('`', '``', $col) . '`';
            if ($val === null) {
                $vals[] = 'NULL';
            } else {
                $vals[] = $pdo->quote($val);
            }
        }
        fwrite(
            $out,
            "INSERT INTO `{$table}` (" . implode(', ', $cols) . ") VALUES (" . implode(', ', $vals) . ");\n"
        );
    }
    fwrite($out, "\n");
}

fwrite($out, "SET FOREIGN_KEY_CHECKS=1;\n");
fclose($out);
exit;
