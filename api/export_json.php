<?php
// api/export_json.php - Handles JSON Export generation based on active search filters
require_once '../db/db.php';
require_once '../db/auth_helpers.php';
require_once '../includes/functions.php';
session_start();

$current_user = require_permission($pdo, 'export_data', 'Export database records to JSON');

// Log export activity
$audit = $pdo->prepare("INSERT INTO audit_logs (user_id, action, details, ip_address) VALUES (?, 'JSON_EXPORT', ?, ?)");
$audit->execute([$current_user['id'], "Generated JSON database export", $_SERVER['REMOTE_ADDR']]);

$cols_stmt = $pdo->query("SELECT id, column_name, data_type FROM table_columns ORDER BY id ASC");
$columns = $cols_stmt->fetchAll(PDO::FETCH_ASSOC);

$search_filters = $_GET['filters'] ?? [];
$date_filters = $_GET['date_filters'] ?? [];

$records_stmt = $pdo->query("SELECT r.id, r.created_at, u.username FROM records r LEFT JOIN users u ON r.created_by = u.id ORDER BY r.id DESC");
$records = $records_stmt->fetchAll(PDO::FETCH_ASSOC);

$values_stmt = $pdo->query("SELECT record_id, column_id, value_content FROM record_values");
$record_values = [];
foreach ($values_stmt->fetchAll(PDO::FETCH_ASSOC) as $val) {
    $record_values[$val['record_id']][$val['column_id']] = $val['value_content'];
}

$export_data = [];
foreach ($records as $rec) {
    if (record_matches_filters($rec['id'], $record_values, $search_filters, $date_filters)) {
        $row = ['record_id' => (int)$rec['id']];
        foreach ($columns as $col) {
            $row[$col['column_name']] = $record_values[$rec['id']][$col['id']] ?? null;
        }
        $row['created_by'] = $rec['username'] ?? 'User_Anon';
        $row['date_added'] = $rec['created_at'];
        $export_data[] = $row;
    }
}

header('Content-Type: application/json; charset=utf-8');
header('Content-Disposition: attachment; filename="prd-export-' . date('Y-m-d') . '.json"');
echo json_encode([
    'system' => 'Parish Records Database (PRD)',
    'export_date' => date('Y-m-d H:i:s'),
    'total_records' => count($export_data),
    'data' => $export_data
], JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE);
exit;
