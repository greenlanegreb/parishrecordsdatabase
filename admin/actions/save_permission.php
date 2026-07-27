<?php
// admin/actions/save_permission.php - Handles the creation of new custom system permissions
require_once '../../db/db.php';
require_once '../../db/auth_helpers.php';
session_start();

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    http_response_code(405);
    exit('Method Not Allowed');
}
verify_csrf_token();
$current_user = require_permission($pdo, 'manage_settings', 'Manage global site settings, mail drivers, and maintenance mode');

$permission_key = strtolower(trim($_POST['permission_key'] ?? ''));
$description = trim($_POST['description'] ?? '');

// Format and sanitize permission key (alphanumeric and underscores only)
$permission_key = preg_replace('/[^a-z0-9_]/', '_', $permission_key);

if (empty($permission_key)) {
    $_SESSION['error'] = "Permission key cannot be empty.";
} else {
    try {
        $chk = $pdo->prepare("SELECT id FROM permissions WHERE permission_key = ?");
        $chk->execute([$permission_key]);
        if ($chk->fetch()) {
            $_SESSION['error'] = "A permission with that key already exists.";
        } else {
            $ins = $pdo->prepare("INSERT INTO permissions (permission_key, description) VALUES (?, ?)");
            if ($ins->execute([$permission_key, $description])) {
                $_SESSION['message'] = "Custom permission '{$permission_key}' successfully created and added to the matrix!";
                
                $audit = $pdo->prepare("INSERT INTO audit_logs (user_id, action, details, ip_address) VALUES (?, 'CREATE_PERMISSION', ?, ?)");
                $audit->execute([$current_user['id'], "Created new permission: {$permission_key}", $_SERVER['REMOTE_ADDR']]);
            } else {
                $_SESSION['error'] = "Failed to create permission.";
            }
        }
    } catch (Exception $e) {
        $_SESSION['error'] = "Database error: " . $e->getMessage();
    }
}

header('Location: ../settings.php#tab-permissions');
exit;
