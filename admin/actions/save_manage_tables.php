<?php
// admin/actions/save_manage_tables.php - Handles custom table creation, updates, deletion, and column schema operations
require_once '../../db/db.php';
require_once '../../db/auth_helpers.php';
session_start();
if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    http_response_code(405);
    exit('Method Not Allowed');
}
// Verify CSRF and enforce dynamic multi-table management permission check
verify_csrf_token();
$current_user = require_permission($pdo, 'manage_tables', 'Manage dynamic database tables and column schema definitions');
$action = $_POST['action'] ?? 'create';
$table_id = intval($_POST['table_id'] ?? 1);
// 1. HANDLE TABLE CREATION
if ($action === 'create_table') {
    $table_name = trim($_POST['table_name'] ?? '');
    $description = trim($_POST['description'] ?? '');
    if (empty($table_name)) {
        $_SESSION['error'] = "Table name cannot be empty.";
    } else {
        $stmt = $pdo->prepare("INSERT INTO dynamic_tables (table_name, description, created_by) VALUES (?, ?, ?)");
        if ($stmt->execute([$table_name, $description, $current_user['id']])) {
            $new_table_id = $pdo->lastInsertId();
            
            // Automatically register granular viewing and moderation permissions for this new table
            $view_perm_key = 'view_table_' . $new_table_id;
            $view_perm_desc = 'Allows viewing and searching records in table: ' . $table_name;
            
            $mod_perm_key = 'moderate_table_' . $new_table_id;
            $mod_perm_desc = 'Allows reviewing and moderating suggestions in table: ' . $table_name;
            
            try {
                // Register view permission
                $p_stmt = $pdo->prepare("INSERT INTO permissions (permission_key, description) VALUES (?, ?) ON DUPLICATE KEY UPDATE description = COALESCE(VALUES(description), description)");
                $p_stmt->execute([$view_perm_key, $view_perm_desc]);
                
                // Register moderation permission
                $p_stmt->execute([$mod_perm_key, $mod_perm_desc]);
                
                // Fetch permission IDs
                $get_p = $pdo->prepare("SELECT id FROM permissions WHERE permission_key IN (?, ?)");
                $get_p->execute([$view_perm_key, $mod_perm_key]);
                $perms = $get_p->fetchAll(PDO::FETCH_ASSOC);
                
                // Assign view to admin and user roles by default; assign moderation to admin and moderator roles by default
                foreach ($perms as $p) {
                    $p_id = $p['id'];
                    $p_key = $p['permission_key'];
                    
                    if (strpos($p_key, 'view_table_') === 0) {
                        $r_stmt = $pdo->prepare("SELECT id FROM roles WHERE role_name IN ('admin', 'user')");
                    } else {
                        $r_stmt = $pdo->prepare("SELECT id FROM roles WHERE role_name IN ('admin', 'moderator')");
                    }
                    $r_stmt->execute();
                    $roles = $r_stmt->fetchAll(PDO::FETCH_COLUMN);
                    
                    $map_stmt = $pdo->prepare("INSERT IGNORE INTO role_permissions (role_id, permission_id) VALUES (?, ?)");
                    foreach ($roles as $r_id) {
                        $map_stmt->execute([$r_id, $p_id]);
                    }
                }
            } catch (Exception $e) {
                // Non-blocking auto-registration fallback
            }
            $_SESSION['message'] = "Custom table '{$table_name}' successfully created with table-scoped view and moderation permissions!";
            
            $audit = $pdo->prepare("INSERT INTO audit_logs (user_id, action, details, ip_address) VALUES (?, 'CREATE_TABLE', ?, ?)");
            $audit->execute([$current_user['id'], "Created table: {$table_name}", $_SERVER['REMOTE_ADDR']]);
            
            header('Location: ../manage_tables.php?table_id=' . $new_table_id);
            exit;
        } else {
            $_SESSION['error'] = "Failed to create table. Table name might already exist.";
        }
    }
}
// 1b. HANDLE TABLE UPDATE (EDITING TABLE METADATA)
elseif ($action === 'update_table') {
    $table_name = trim($_POST['table_name'] ?? '');
    $description = trim($_POST['description'] ?? '');
    
    if ($table_id <= 0) {
        $_SESSION['error'] = "Invalid table selected for editing.";
    } elseif (empty($table_name)) {
        $_SESSION['error'] = "Table name cannot be empty.";
    } else {
        $stmt = $pdo->prepare("UPDATE dynamic_tables SET table_name = ?, description = ? WHERE id = ?");
        if ($stmt->execute([$table_name, $description, $table_id])) {
            $_SESSION['message'] = "Table metadata successfully updated!";
            
            $audit = $pdo->prepare("INSERT INTO audit_logs (user_id, action, details, ip_address) VALUES (?, 'UPDATE_TABLE', ?, ?)");
            $audit->execute([$current_user['id'], "Updated table ID {$table_id} metadata to: {$table_name}", $_SERVER['REMOTE_ADDR']]);
        } else {
            $_SESSION['error'] = "Failed to update table metadata.";
        }
    }
    header('Location: ../manage_tables.php?table_id=' . $table_id);
    exit;
}
// 2. HANDLE TABLE DELETION
elseif ($action === 'delete_table') {
    if ($table_id <= 1) {
        $_SESSION['error'] = "The default root table cannot be deleted.";
    } else {
        $t_stmt = $pdo->prepare("SELECT table_name FROM dynamic_tables WHERE id = ?");
        $t_stmt->execute([$table_id]);
        $table_info = $t_stmt->fetch();
        if ($table_info) {
            $pdo->beginTransaction();
            try {
                // Delete associated record values first
                $del_vals = $pdo->prepare("DELETE rv FROM record_values rv JOIN table_columns tc ON rv.column_id = tc.id WHERE tc.table_id = ?");
                $del_vals->execute([$table_id]);
                // Delete associated records
                $del_recs = $pdo->prepare("DELETE FROM records WHERE table_id = ?");
                $del_recs->execute([$table_id]);
                // Delete associated columns
                $del_cols = $pdo->prepare("DELETE FROM table_columns WHERE table_id = ?");
                $del_cols->execute([$table_id]);
                // Delete the table itself
                $del_tbl = $pdo->prepare("DELETE FROM dynamic_tables WHERE id = ?");
                $del_tbl->execute([$table_id]);
                $pdo->commit();
                $_SESSION['message'] = "Table '{$table_info['table_name']}' and all its data were successfully deleted.";
                $audit = $pdo->prepare("INSERT INTO audit_logs (user_id, action, details, ip_address) VALUES (?, 'DELETE_TABLE', ?, ?)");
                $audit->execute([$current_user['id'], "Deleted table ID {$table_id}: {$table_info['table_name']}", $_SERVER['REMOTE_ADDR']]);
            } catch (Exception $e) {
                $pdo->rollBack();
                $_SESSION['error'] = "Failed to delete table safely.";
            }
        }
    }
    header('Location: ../manage_tables.php');
    exit;
}
// 3. HANDLE COLUMN CREATION / UPDATING
elseif ($action === 'create' || $action === 'update') {
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
            $stmt = $pdo->prepare("INSERT INTO table_columns (table_id, column_name, data_type, max_length, boolean_display_format, date_search_behavior, sort_order, is_required, exclude_from_public_search, created_by) VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?)");
            if ($stmt->execute([$table_id, $column_name, $data_type, $max_length, $boolean_display_format, $date_search_behavior, $sort_order, $is_required, $exclude_from_public_search, $current_user['id']])) {
                $_SESSION['message'] = "Dynamic column '{$column_name}' successfully created!";
                
                $audit = $pdo->prepare("INSERT INTO audit_logs (user_id, action, details, ip_address) VALUES (?, 'CREATE_COLUMN', ?, ?)");
                $audit->execute([$current_user['id'], "Created column '{$column_name}' in table ID {$table_id}", $_SERVER['REMOTE_ADDR']]);
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
} 
// 4. HANDLE BATCH SORT ORDER UPDATES
elseif ($action === 'update_order_batch') {
    $sort_orders = $_POST['sort_orders'] ?? [];
    if (!empty($sort_orders) && is_array($sort_orders)) {
        $stmt = $pdo->prepare("UPDATE table_columns SET sort_order = ? WHERE id = ?");
        $pdo->beginTransaction();
        try {
            foreach ($sort_orders as $col_id => $order_val) {
                $stmt->execute([intval($order_val), intval($col_id)]);
            }
            $pdo->commit();
            $_SESSION['message'] = "All column sort orders successfully updated!";
        } catch (Exception $e) {
            $pdo->rollBack();
            $_SESSION['error'] = "Failed to update column sort orders.";
        }
    } else {
        $_SESSION['error'] = "No sort order data received.";
    }
} 
// 5. HANDLE COLUMN DELETION
elseif ($action === 'delete') {
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
// Redirect back to manage_tables.php maintaining the active table view
header('Location: ../manage_tables.php?table_id=' . $table_id);
exit;
