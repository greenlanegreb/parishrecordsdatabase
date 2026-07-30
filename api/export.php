<?php
// api/export.php - Handles CSV Export generation based on active search filters with citation header
require_once '../db/db.php';
require_once '../db/auth_helpers.php';
require_once '../includes/functions.php';
session_start();

$current_user = require_permission($pdo, 'export_data', 'Export database records and search result sets to CSV');

$audit = $pdo->prepare("INSERT INTO audit_logs (user_id, action, details, ip_address) VALUES (?, 'CSV_EXPORT', ?, ?)");
$audit->execute([$current_user['id'], "Generated CSV database export with citation header", $_SERVER['REMOTE_ADDR']]);

$system_name = get_system_name($pdo);

// Fetch columns and records
$cols_stmt = $pdo->query("SELECT * FROM table_columns ORDER BY id ASC");
$columns = $cols_stmt->fetchAll();
$search_filters = $_GET['filters'] ?? [];
$date_filters = $_GET['date_filters'] ?? [];

$records_stmt = $pdo->query("SELECT r.id, r.created_at, u.username FROM records r LEFT JOIN users u ON r.created_by = u.id ORDER BY r.id DESC");
$records = $records_stmt->fetchAll();

$values_stmt = $pdo->query("SELECT record_id, column_id, value_content FROM record_values");
$record_values = [];
foreach ($values_stmt->fetchAll() as $val) {
    $record_values[$val['record_id']][$val['column_id']] = $val['value_content'];
}

header('Content-Type: text/csv; charset=utf-8');
header('Content-Disposition: attachment; filename="prd-export-' . date('Y-m-d') . '.csv"');
$output = fopen('php://output', 'w');

// --- Citation & Metadata Header Rows ---
fputcsv($output, ["# Source System: " . $system_name]);
fputcsv($output, ["# Export Generated On: " . date('Y-m-d H:i:s')]);
fputcsv($output, ["# Exported By: " . ($current_user['username'] ?? 'User')]);
fputcsv($output, ["# --------------------------------------------------"]);
fputcsv($output, []); // Blank spacer row

// Standard Column Headers
$header_row = ['Record ID'];
foreach ($columns as $col) {
    $header_row[] = $col['column_name'];
}
$header_row[] = 'Created By';
$header_row[] = 'Date Added';
fputcsv($output, $header_row);

// Data Rows (using your existing filter checks...)
foreach ($records as $rec) {
    if (record_matches_filters($rec['id'], $record_values, $search_filters, $date_filters)) {
        $row = ['#' . $rec['id']];
        foreach ($columns as $col) {
            $raw_val = $record_values[$rec['id']][$col['id']] ?? '';
            if (($col['data_type'] ?? '') === 'BOOLEAN') {
                $row[] = format_boolean_value($raw_val, $col['boolean_display_format'] ?? 'yes_no');
            } elseif (($col['data_type'] ?? '') === 'DATE') {
                $row[] = format_display_date($raw_val, $user_date_format ?? 'd/m/Y');
            } else {
                $row[] = $raw_val;
            }
        }
        $row[] = $rec['username'] ?? 'User_Anon';
        $row[] = $rec['created_at'];
        fputcsv($output, $row);
    }
}
fclose($output);
exit;
