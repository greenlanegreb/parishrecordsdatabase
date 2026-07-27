<?php
// api/search.php - Handles AJAX search, sorting, and pagination filtered by active table ID
require_once '../db/db.php';
require_once '../db/auth_helpers.php';
require_once '../includes/functions.php';
session_start();

require_permission($pdo, 'view_public', 'View and search records');

$table_id = intval($_GET['table_id'] ?? 1);

// Enforce permission check for specific table
$perm_key = 'view_table_' . $table_id;
if ($table_id !== 1 && !has_permission($pdo, $perm_key)) {
    http_response_code(403);
    exit('Unauthorized table access.');
}

$user_date_format = 'd/m/Y';
if (isset($_SESSION['user_id'])) {
    $current_user = get_current_user_data($pdo);
    if ($current_user) {
        $user_date_format = $current_user['date_format'] ?? 'd/m/Y';
    }
}

// Fetch columns for this specific table
$cols_stmt = $pdo->prepare("SELECT * FROM table_columns WHERE table_id = ? ORDER BY sort_order ASC, column_name ASC");
$cols_stmt->execute([$table_id]);
$columns = $cols_stmt->fetchAll();

$search_filters = $_GET['filters'] ?? [];
$date_filters = $_GET['date_filters'] ?? [];
$sort_col = $_GET['sort'] ?? 'id';
$sort_dir = (isset($_GET['dir']) && strtoupper($_GET['dir']) === 'ASC') ? 'ASC' : 'DESC';
$page = max(1, intval($_GET['page'] ?? 1));
$per_page = 10;
$offset = ($page - 1) * $per_page;

$order_clause = "ORDER BY r.id {$sort_dir}";
if ($sort_col === 'date') {
    $order_clause = "ORDER BY r.created_at {$sort_dir}";
}

// Fetch records restricted to the active table ID
$records_stmt = $pdo->prepare("SELECT r.id, r.created_at, u.username FROM records r LEFT JOIN users u ON r.created_by = u.id WHERE r.table_id = ? {$order_clause}");
$records_stmt->execute([$table_id]);
$records = $records_stmt->fetchAll();

$values_stmt = $pdo->query("SELECT record_id, column_id, value_content FROM record_values");
$raw_values = $values_stmt->fetchAll();
$record_values = [];
foreach ($raw_values as $val) {
    $record_values[$val['record_id']][$val['column_id']] = $val['value_content'];
}

$matched_records = [];
foreach ($records as $rec) {
    if (record_matches_filters($rec['id'], $record_values, $search_filters, $date_filters)) {
        $matched_records[] = $rec;
    }
}

$total_matched = count($matched_records);
$total_pages = ceil($total_matched / $per_page);
$paginated_records = array_slice($matched_records, $offset, $per_page);

if (empty($paginated_records)) {
    echo '<tr><td colspan="' . (count($columns) + 4) . '">No records found in this table.</td></tr>';
} else {
    foreach ($paginated_records as $rec) {
        echo '<tr>';
        echo '<td>#' . $rec['id'] . '</td>';
        foreach ($columns as $col) {
            $raw_val = $record_values[$rec['id']][$col['id']] ?? '';
            if (($col['data_type'] ?? '') === 'BOOLEAN') {
                $display_val = format_boolean_value($raw_val, $col['boolean_display_format'] ?? 'yes_no');
            } elseif (($col['data_type'] ?? '') === 'DATE') {
                $display_val = format_display_date($raw_val, $user_date_format);
            } else {
                $display_val = $raw_val;
            }
            echo '<td>' . htmlspecialchars($display_val) . '</td>';
        }
        echo '<td>' . htmlspecialchars(obscure_name_ajax($rec['username'])) . '</td>';
        echo '<td>' . date('Y-m-d H:i', strtotime($rec['created_at'])) . '</td>';
        echo '<td><button type="button" class="btn btn-secondary" style="padding: 0.2rem 0.4rem; font-size: 0.8rem;" onclick="openSuggestModal(' . $rec['id'] . ')">Suggest Edit</button></td>';
        echo '</tr>';
    }
}
echo '|||TOTAL_PAGES:' . $total_pages . '|||CURRENT_PAGE:' . $page;
