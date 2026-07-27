<?php
// admin/actions/save_column.php - Handles column creation, updating, reordering, and deletion logic
require_once '../../db/db.php';
require_once '../../db/auth_helpers.php';
session_start();

// Enforce strict administrator privileges
require_role($pdo, 'admin');
$current_user = get_current_user_data($pdo);

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $action = $_POST['action'] ?? 'create';

    if ($action === 'create' || $action === 'update') {
        $column_name = trim($_POST['column_name'] ?? '');
        $data_type = trim($_POST['data_type'] ?? 'VARCHAR');
        $max_length = !empty($_POST['max_length']) ? intval($_POST['max_length']) : null;
        $sort_order = isset($_POST['sort_order']) ? intval($_POST['sort_order']) : 0;
        $is_required = isset($_POST['is_required']) ? 1 : 0;
        $exclude_from_public_search = isset($_POST['exclude_from_public_search']) ? 1 : 0;
        
        $boolean_display_format = ($data_type === 'BOOLEAN') ? trim($_POST['boolean_display_format'] ?? 'yes_no') : null;
        $date_search_behavior = ($data_type === 'DATE') ? trim($_POST['date_search_behavior'] ?? 'manual_only') : null;

        if (empty($column_name)) {
            $_SESSION['error'] = "Column name cannot be empty.";
        } else {
            if ($action === 'create') {
                $stmt = $pdo->prepare("INSERT INTO table_columns (column_name, data_type, max_length, boolean_display_format, date_search_behavior, sort_order, is_required, exclude_from_public_search, created_by) VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?)");
                if ($stmt->execute([$column_name, $data_type, $max_length, $boolean_display_format, $date_search_behavior, $sort_order, $is_required, $exclude_from_public_search, $current_user['id']])) {
                    $_SESSION['message'] = "Dynamic column '{$column_name}' successfully created!";
                    
                    $audit = $pdo->prepare("INSERT INTO audit_logs (user_id, action, details, ip_address) VALUES (?, 'CREATE_COLUMN', ?, ?)");
                    $audit->execute([$current_user['id'], "Created column: {$column_name}", $_SERVER['REMOTE_ADDR']]);
                } else {
                    $_SESSION['error'] = "Failed to create column.";
                }
            } elseif ($action === 'update') {
                $column_id = intval($_POST['column_id'] ?? 0);
                if ($column_id > 0) {
                    $stmt = $pdo->prepare("UPDATE table_columns SET column_name = ?, data_type = ?, max_length = ?, boolean_display_format = ?, date_search_behavior = ?, sort_order = ?, is_required = ?, exclude_from_public_search = ? WHERE id = ?");
                    if ($stmt->execute([$column_name, $data_type, $max_length, $boolean_display_format, $date_search_behavior, $sort_order, $is_required, $exclude_from_public_search, $column_id])) {
                        $_SESSION['message'] = "Dynamic column '{$column_name}' successfully updated!";
                        
                        $audit = $pdo->prepare("INSERT INTO audit_logs (user_id, action, details, ip_address) VALUES (?, 'UPDATE_COLUMN', ?, ?)");
                        $audit->execute([$current_user['id'], "Updated column ID {$column_id}: {$column_name}", $_SERVER['REMOTE_ADDR']]);
                    } else {
                        $_SESSION['error'] = "Failed to update column.";
                    }
                }
            }
        }
    } elseif ($action === 'update_order') {
        $column_id = intval($_POST['column_id'] ?? 0);
        $sort_order = intval($_POST['sort_order'] ?? 0);

        if ($column_id > 0) {
            $stmt = $pdo->prepare("UPDATE table_columns SET sort_order = ? WHERE id = ?");
            if ($stmt->execute([$sort_order, $column_id])) {
                $_SESSION['message'] = "Column sort order successfully updated!";
            } else {
                $_SESSION['error'] = "Failed to update sort order.";
            }
        }
    } elseif ($action === 'delete') {
        $column_id = intval($_POST['column_id'] ?? 0);

        if ($column_id > 0) {
            $c_stmt = $pdo->prepare("SELECT column_name FROM table_columns WHERE id = ?");
            $c_stmt->execute([$column_id]);
            $col_info = $c_stmt->fetch();

            if ($col_info) {
                $del_vals = $pdo->prepare("DELETE FROM record_values WHERE column_id = ?");
                $del_vals->execute([$column_id]);

                $del_col = $pdo->prepare("DELETE FROM table_columns WHERE id = ?");
                $del_col->execute([$column_id]);

                $_SESSION['message'] = "Column '{$col_info['column_name']}' and its associated data entries were successfully deleted.";

                $audit = $pdo->prepare("INSERT INTO audit_logs (user_id, action, details, ip_address) VALUES (?, 'DELETE_COLUMN', ?, ?)");
                $audit->execute([$current_user['id'], "Deleted column ID {$column_id}: {$col_info['column_name']}", $_SERVER['REMOTE_ADDR']]);
            }
        }
    }
}

header('Location: ../columns.php');
exit;
