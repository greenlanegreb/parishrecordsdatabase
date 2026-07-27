<?php
// api/export.php - Handles CSV Export generation based on active search filters
require_once '../db/db.php';
require_once '../includes/functions.php';
session_start();

$cols_stmt = $pdo->query("SELECT * FROM table_columns ORDER BY id ASC");
$columns = $cols_stmt->fetchAll();

$search_filters = $_GET['filters'] ?? [];

$records_stmt = $pdo->query("SELECT r.id, r.created_at, u.username FROM records r LEFT JOIN users u ON r.created_by = u.id ORDER BY r.id DESC");
$records = $records_stmt->fetchAll();

$values_stmt = $pdo->query("SELECT record_id, column_id, value_content FROM record_values");
$raw_values = $values_stmt->fetchAll();
$record_values = [];
foreach ($raw_values as $val) {
    $record_values[$val['record_id']][$val['column_id']] = $val['value_content'];
}

header('Content-Type: text/csv; charset=utf-8');
header('Content-Disposition: attachment; filename="psd-export.csv"');

$output = fopen('php://output', 'w');

$header_row = ['Record ID'];
foreach ($columns as $col) {
    $header_row[] = $col['column_name'];
}
$header_row[] = 'Created By';
$header_row[] = 'Date Added';
fputcsv($output, $header_row);

foreach ($records as $rec) {
    $match = true;
    if (!empty($search_filters)) {
        foreach ($search_filters as $col_id => $search_term) {
            if (!empty(trim($search_term))) {
                $cell_val = $record_values[$rec['id']][$col_id] ?? '';
                if (stripos($cell_val, trim($search_term)) === false) {
                    $match = false;
                    break;
                }
            }
        }
    }

    if ($match) {
        $row = ['#' . $rec['id']];
        foreach ($columns as $col) {
            $raw_val = $record_values[$rec['id']][$col['id']] ?? '';
            if (($col['data_type'] ?? '') === 'BOOLEAN') {
                $row[] = format_boolean_value($raw_val, $col['boolean_display_format'] ?? 'yes_no');
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
