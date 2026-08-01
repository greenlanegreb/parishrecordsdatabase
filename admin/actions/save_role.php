<?php
// admin/actions/save_role.php - Handles the creation, updating, and deletion of custom system roles
require_once '../../db/db.php';
require_once '../../db/auth_helpers.php';

session_start();
if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    http_response_code(405);
    exit('Method Not Allowed');
}
verify_csrf_token();
$current_user = require_permission($pdo, 'manage_settings', 'Manage global site settings, mail drivers, and maintenance mode');

$action = $_POST['action'] ?? 'create_role';

// HANDLE ROLE DELETION
if (isset($_POST['delete_role_id'])) {
    $role_id_to_delete = intval($_POST['delete_role_id']);
    
    // Fetch role info
    $r_chk = $pdo->prepare("SELECT role_name FROM roles WHERE id = ?");
    $r_chk->execute([$role_id_to_delete]);
    $role_to_delete = $r_chk->fetch(PDO::FETCH_ASSOC);
    
    if (!$role_to_delete) {
        $_SESSION['error'] = "Role not found.";
    } elseif (in_array($role_to_delete['role_name'], ['admin', 'moderator', 'user', 'guest'])) {
        $_SESSION['error'] = "Core system roles cannot be deleted.";
    } else {
        // Find default 'user' role ID to reassign orphaned users safely
        $default_r = $pdo->query("SELECT id FROM roles WHERE role_name = 'user' LIMIT 1")->fetch(PDO::FETCH_COLUMN);
        $fallback_role_id = $default_r ? intval($default_r) : 2; // Default fallback ID 2
        
        $pdo->beginTransaction();
        try {
            // Reassign any users currently assigned to this deleted role over to the default user role
            $reassign = $pdo->prepare("UPDATE users SET role_id = ? WHERE role_id = ?");
            $reassign->execute([$fallback_role_id, $role_id_to_delete]);
            
            // Delete role permission mappings
            $del_perms = $pdo->prepare("DELETE FROM role_permissions WHERE role_id = ?");
            $del_perms->execute([$role_id_to_delete]);
            
            // Delete the role itself
            $del_role = $pdo->prepare("DELETE FROM roles WHERE id = ?");
            $del_role->execute([$role_id_to_delete]);
            
            $pdo->commit();
            $_SESSION['message'] = "Role '{$role_to_delete['role_name']}' successfully deleted and associated users safely reassigned.";
            
            $audit = $pdo->prepare("INSERT INTO audit_logs (user_id, action, details, ip_address) VALUES (?, 'DELETE_ROLE', ?, ?)");
            $audit->execute([$current_user['id'], "Deleted custom role: {$role_to_delete['role_name']}", $_SERVER['REMOTE_ADDR']]);
        } catch (Exception $e) {
            $pdo->rollBack();
            $_SESSION['error'] = "Failed to delete role: " . $e->getMessage();
        }
    }
    header('Location: ../settings.php#tab-permissions');
    exit;
}

// HANDLE ROLE CREATION OR UPDATING
$role_name = strtolower(trim($_POST['role_name'] ?? ''));
$description = trim($_POST['description'] ?? '');
$role_name = preg_replace('/[^a-z0-9_]/', '_', $role_name);

if (empty($role_name)) {
    $_SESSION['error'] = "Role name cannot be empty.";
    header('Location: ../settings.php#tab-permissions');
    exit;
}

try {
    if ($action === 'update_role') {
        $role_id = intval($_POST['role_id'] ?? 0);
        
        // Check name collision
        $chk = $pdo->prepare("SELECT id FROM roles WHERE role_name = ? AND id != ?");
        $chk->execute([$role_name, $role_id]);
        if ($chk->fetch()) {
            $_SESSION['error'] = "Another role with that name already exists.";
        } else {
            $upd = $pdo->prepare("UPDATE roles SET role_name = ?, description = ? WHERE id = ?");
            if ($upd->execute([$role_name, $description, $role_id])) {
                $_SESSION['message'] = "Role '{$role_name}' successfully updated!";
                
                $audit = $pdo->prepare("INSERT INTO audit_logs (user_id, action, details, ip_address) VALUES (?, 'UPDATE_ROLE', ?, ?)");
                $audit->execute([$current_user['id'], "Updated role ID {$role_id} to name: {$role_name}", $_SERVER['REMOTE_ADDR']]);
            } else {
                $_SESSION['error'] = "Failed to update role.";
            }
        }
    } else {
        // Create new role
        $chk = $pdo->prepare("SELECT id FROM roles WHERE role_name = ?");
        $chk->execute([$role_name]);
        if ($chk->fetch()) {
            $_SESSION['error'] = "A role with that name already exists.";
        } else {
            $ins = $pdo->prepare("INSERT INTO roles (role_name, description) VALUES (?, ?)");
            if ($ins->execute([$role_name, $description])) {
                $new_role_id = $pdo->lastInsertId();
                
                // Automatically assign basic 'view_public' permission by default
                $perm_stmt = $pdo->prepare("SELECT id FROM permissions WHERE permission_key = 'view_public'");
                $perm_stmt->execute();
                $view_perm_id = $perm_stmt->fetchColumn();
                
                if ($view_perm_id) {
                    $map_stmt = $pdo->prepare("INSERT INTO role_permissions (role_id, permission_id) VALUES (?, ?)");
                    $map_stmt->execute([$new_role_id, $view_perm_id]);
                }
                $_SESSION['message'] = "Custom role '{$role_name}' successfully created!";
                
                $audit = $pdo->prepare("INSERT INTO audit_logs (user_id, action, details, ip_address) VALUES (?, 'CREATE_ROLE', ?, ?)");
                $audit->execute([$current_user['id'], "Created new role: {$role_name}", $_SERVER['REMOTE_ADDR']]);
            } else {
                $_SESSION['error'] = "Failed to create role.";
            }
        }
    }
} catch (Exception $e) {
    $_SESSION['error'] = "Database error: " . $e->getMessage();
}

header('Location: ../settings.php#tab-permissions');
exit;
