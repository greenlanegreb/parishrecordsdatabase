<?php
// admin/actions/save_role.php - Handles the creation of new custom system roles
require_once '../../db/db.php';
require_once '../../db/auth_helpers.php';
session_start();

// Enforce admin permission check and validate POST request method
if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    http_response_code(405);
    exit('Method Not Allowed');
}
verify_csrf_token();
$current_user = require_permission($pdo, 'manage_settings', 'Manage global site settings, mail drivers, and maintenance mode');

$role_name = strtolower(trim($_POST['role_name'] ?? ''));
$description = trim($_POST['description'] ?? '');

// Format and sanitize role name (alphanumeric and underscores only)
$role_name = preg_replace('/[^a-z0-9_]/', '_', $role_name);

if (empty($role_name)) {
    $_SESSION['error'] = "Role name cannot be empty.";
} else {
    try {
        // Check if role already exists
        $chk = $pdo->prepare("SELECT id FROM roles WHERE role_name = ?");
        $chk->execute([$role_name]);
        if ($chk->fetch()) {
            $_SESSION['error'] = "A role with that name already exists.";
        } else {
            // Insert new role
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
    } catch (Exception $e) {
        $_SESSION['error'] = "Database error: " . $e->getMessage();
    }
}

header('Location: ../settings.php#tab-permissions');
exit;
