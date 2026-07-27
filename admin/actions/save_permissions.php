<?php
// admin/actions/save_permissions.php - Processes role and permission matrix updates
require_once '../../db/db.php';
require_once '../../db/auth_helpers.php';
session_start();

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    http_response_code(405);
    exit('Method Not Allowed');
}

// Verify CSRF token and enforce dynamic permission check
verify_csrf_token();
$current_user = require_permission($pdo, 'manage_settings', 'Manage global site settings, mail drivers, and maintenance mode');

$submitted_matrix = $_POST['permissions'] ?? []; // Format: role_permissions[role_id][permission_id] = 1

try {
    $pdo->beginTransaction();

    // Clear existing mappings to rebuild cleanly based on checkbox state
    $pdo->exec("DELETE FROM role_permissions");

    if (!empty($submitted_matrix) && is_array($submitted_matrix)) {
        $insert_stmt = $pdo->prepare("INSERT INTO role_permissions (role_id, permission_id) VALUES (?, ?)");
        
        foreach ($submitted_matrix as $role_id => $permission_ids) {
            foreach ($permission_ids as $permission_id => $val) {
                if ($val == '1') {
                    $insert_stmt->execute([intval($role_id), intval($permission_id)]);
                }
            }
        }
    }

    $pdo->commit();
    $_SESSION['message'] = "Role permissions matrix successfully updated!";
    
    $audit = $pdo->prepare("INSERT INTO audit_logs (user_id, action, details, ip_address) VALUES (?, 'UPDATE_PERMISSIONS', ?, ?)");
    $audit->execute([$current_user['id'], "Updated dynamic role permission matrix", $_SERVER['REMOTE_ADDR']]);

} catch (Exception $e) {
    if ($pdo->inTransaction()) {
        $pdo->rollBack();
    }
    $_SESSION['error'] = "Failed to update permissions: " . $e->getMessage();
}

header('Location: ../settings.php#tab-permissions');
exit;
