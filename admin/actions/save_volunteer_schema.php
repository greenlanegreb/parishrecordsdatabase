<?php
// admin/actions/save_volunteer_schema.php - Handles volunteer form schema updates, reordering, and settings
require_once '../../db/db.php';
require_once '../../db/auth_helpers.php';
require_once '../../includes/functions.php';

if (!is_module_enabled($pdo, 'volunteers')) {
    http_response_code(403);
    exit('403 Forbidden');
}
if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    http_response_code(405);
    exit('Method Not Allowed');
}

verify_csrf_token();
$current_user = require_permission($pdo, 'manage_volunteers', 'Manage volunteer schema definitions');
$action = $_POST['action'] ?? '';

if ($action === 'create' || $action === 'update') {
    $column_name = trim($_POST['column_name'] ?? '');
    $data_type = trim($_POST['data_type'] ?? 'VARCHAR');
    $field_subtype = trim($_POST['field_subtype'] ?? '');
    $field_options = trim($_POST['field_options'] ?? '');
    $field_options = implode(', ', array_filter(array_map('trim', preg_split('/[\r\n]+|,/', $field_options))));
    $allow_multiple = isset($_POST['allow_multiple']) ? 1 : 0;
    $max_length = !empty($_POST['max_length']) ? intval($_POST['max_length']) : null;
    $is_required = isset($_POST['is_required']) ? 1 : 0;
    $boolean_format = ($data_type === 'BOOLEAN') ? trim($_POST['boolean_display_format'] ?? 'yes_no') : null;

    if (!empty($column_name)) {
        if ($action === 'create') {
            $ord = $pdo->query("SELECT COALESCE(MAX(sort_order), 0) + 1 FROM volunteer_columns")->fetchColumn();
            
            $stmt = $pdo->prepare("INSERT INTO volunteer_columns (column_name, data_type, field_subtype, field_options, allow_multiple, max_length, boolean_display_format, sort_order, is_required, created_by) VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?)");
            $stmt->execute([$column_name, $data_type, $field_subtype, $field_options, $allow_multiple, $max_length, $boolean_format, $ord, $is_required, $current_user['id']]);
            
            $_SESSION['message'] = "Volunteer field '{$column_name}' created successfully.";
        } else {
            $col_id = intval($_POST['column_id'] ?? 0);
            if ($col_id > 0) {
                $stmt = $pdo->prepare("UPDATE volunteer_columns SET column_name = ?, data_type = ?, field_subtype = ?, field_options = ?, allow_multiple = ?, max_length = ?, boolean_display_format = ?, is_required = ? WHERE id = ?");
                $stmt->execute([$column_name, $data_type, $field_subtype, $field_options, $allow_multiple, $max_length, $boolean_format, $is_required, $col_id]);
                
                $_SESSION['message'] = "Volunteer field updated successfully.";
            }
        }
    }
} elseif ($action === 'delete') {
    $col_id = intval($_POST['column_id'] ?? 0);
    if ($col_id > 0) {
        $pdo->prepare("DELETE FROM volunteer_submission_values WHERE column_id = ?")->execute([$col_id]);
        $pdo->prepare("DELETE FROM volunteer_columns WHERE id = ?")->execute([$col_id]);
        $_SESSION['message'] = "Volunteer field deleted.";
    }
} elseif ($action === 'update_order_batch') {
    $sort_orders = $_POST['sort_orders'] ?? [];
    $stmt = $pdo->prepare("UPDATE volunteer_columns SET sort_order = ? WHERE id = ?");
    $pdo->beginTransaction();
    foreach ($sort_orders as $id => $order) {
        $stmt->execute([intval($order), intval($id)]);
    }
    $pdo->commit();
    exit;
} elseif ($action === 'update_settings') {
    $form_title = trim($_POST['form_title'] ?? 'Volunteer for Data Entry');
    $form_intro = trim($_POST['form_intro'] ?? '');

    $stmt = $pdo->prepare("INSERT INTO volunteer_form_settings (setting_key, setting_value) VALUES ('form_title', ?), ('form_intro', ?) ON DUPLICATE KEY UPDATE setting_value = VALUES(setting_value)");
    $stmt->execute([$form_title, $form_intro]);

    $_SESSION['message'] = "Form presentation settings updated successfully.";
}

header('Location: ../manage_volunteer_schema.php');
exit;
