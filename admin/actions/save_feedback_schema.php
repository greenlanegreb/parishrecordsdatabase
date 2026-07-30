<?php
// admin/actions/save_feedback_schema.php - Handles ticket field schema updates and reordering
require_once '../../db/db.php';
require_once '../../db/auth_helpers.php';
require_once '../../includes/functions.php';

if (!is_module_enabled($pdo, 'feedback')) {
    http_response_code(403);
    exit('403 Forbidden');
}
if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    http_response_code(405);
    exit('Method Not Allowed');
}

verify_csrf_token();
$current_user = require_permission($pdo, 'manage_feedback', 'Manage feedback schema definitions');
$action = $_POST['action'] ?? '';

if ($action === 'create' || $action === 'update') {
    $column_name = trim($_POST['column_name'] ?? '');
    $data_type = trim($_POST['data_type'] ?? 'VARCHAR');
    $field_subtype = trim($_POST['field_subtype'] ?? '');
    $field_options = trim($_POST['field_options'] ?? '');
    $allow_multiple = isset($_POST['allow_multiple']) ? 1 : 0;
    $max_length = !empty($_POST['max_length']) ? intval($_POST['max_length']) : null;
    $is_required = isset($_POST['is_required']) ? 1 : 0;
    $boolean_format = ($data_type === 'BOOLEAN') ? trim($_POST['boolean_display_format'] ?? 'yes_no') : null;

    if (!empty($column_name)) {
        if ($action === 'create') {
            $ord = $pdo->query("SELECT COALESCE(MAX(sort_order), 0) + 1 FROM feedback_columns")->fetchColumn();
            
            $stmt = $pdo->prepare("INSERT INTO feedback_columns (column_name, data_type, field_subtype, field_options, allow_multiple, max_length, boolean_display_format, sort_order, is_required, created_by) VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?)");
            $stmt->execute([$column_name, $data_type, $field_subtype, $field_options, $allow_multiple, $max_length, $boolean_format, $ord, $is_required, $current_user['id']]);
            
            $_SESSION['message'] = "Ticket field '{$column_name}' created successfully.";
        } else {
            $col_id = intval($_POST['column_id'] ?? 0);
            if ($col_id > 0) {
                $stmt = $pdo->prepare("UPDATE feedback_columns SET column_name = ?, data_type = ?, field_subtype = ?, field_options = ?, allow_multiple = ?, max_length = ?, boolean_display_format = ?, is_required = ? WHERE id = ?");
                $stmt->execute([$column_name, $data_type, $field_subtype, $field_options, $allow_multiple, $max_length, $boolean_format, $is_required, $col_id]);
                
                $_SESSION['message'] = "Ticket field updated successfully.";
            }
        }
    }
} elseif ($action === 'delete') {
    $col_id = intval($_POST['column_id'] ?? 0);
    if ($col_id > 0) {
        $pdo->prepare("DELETE FROM feedback_ticket_values WHERE column_id = ?")->execute([$col_id]);
        $pdo->prepare("DELETE FROM feedback_columns WHERE id = ?")->execute([$col_id]);
        $_SESSION['message'] = "Ticket field deleted.";
    }
} elseif ($action === 'update_order_batch') {
    $sort_orders = $_POST['sort_orders'] ?? [];
    $stmt = $pdo->prepare("UPDATE feedback_columns SET sort_order = ? WHERE id = ?");
    $pdo->beginTransaction();
    foreach ($sort_orders as $id => $order) {
        $stmt->execute([intval($order), intval($id)]);
    }
    $pdo->commit();
    exit;
}

header('Location: ../manage_feedback_schema.php');
exit;
