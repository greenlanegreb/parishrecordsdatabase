<?php
// admin/actions/save_user_management.php - Handles user moderation, score overrides, and role changes
require_once '../../db/db.php';
require_once '../../db/auth_helpers.php';
session_start();

// Enforce permission-based access control and validate POST request method
if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    http_response_code(405);
    exit('Method Not Allowed');
}
verify_csrf_token();
$current_user = require_permission($pdo, 'manage_users', 'Manage user accounts, roles, and status');

$action = $_POST['action'] ?? '';
$target_user_id = intval($_POST['target_user_id'] ?? 0);

if ($target_user_id <= 0) {
    $_SESSION['error'] = "Invalid target user specified.";
    header('Location: ../users.php');
    exit;
}

// Fetch target user details for logging
$target_stmt = $pdo->prepare("SELECT id, username FROM users WHERE id = ?");
$target_stmt->execute([$target_user_id]);
$target_user = $target_stmt->fetch(PDO::FETCH_ASSOC);

if (!$target_user) {
    $_SESSION['error'] = "User not found.";
    header('Location: ../users.php');
    exit;
}

try {
    switch ($action) {
        case 'change_role':
            $new_role_id = intval($_POST['new_role_id'] ?? 0);
            
            // Verify role exists
            $r_chk = $pdo->prepare("SELECT role_name FROM roles WHERE id = ?");
            $r_chk->execute([$new_role_id]);
            $role_data = $r_chk->fetch(PDO::FETCH_ASSOC);
            
            if (!$role_data) {
                $_SESSION['error'] = "Selected role does not exist.";
                break;
            }
            
            $upd = $pdo->prepare("UPDATE users SET role_id = ? WHERE id = ?");
            $upd->execute([$new_role_id, $target_user_id]);
            
            $_SESSION['message'] = "Role for '{$target_user['username']}' successfully updated to '{$role_data['role_name']}'.";
            
            $audit = $pdo->prepare("INSERT INTO audit_logs (user_id, action, details, ip_address) VALUES (?, 'CHANGE_USER_ROLE', ?, ?)");
            $audit->execute([$current_user['id'], "Changed role of user {$target_user['username']} to {$role_data['role_name']}", $_SERVER['REMOTE_ADDR']]);
            break;

        case 'override_points':
            $new_points = intval($_POST['new_points'] ?? 0);
            
            $upd = $pdo->prepare("UPDATE users SET points = ? WHERE id = ?");
            $upd->execute([$new_points, $target_user_id]);
            
            $_SESSION['message'] = "Score for '{$target_user['username']}' updated to {$new_points} points.";
            
            $audit = $pdo->prepare("INSERT INTO audit_logs (user_id, action, details, ip_address) VALUES (?, 'OVERRIDE_POINTS', ?, ?)");
            $audit->execute([$current_user['id'], "Overrode score for {$target_user['username']} to {$new_points}", $_SERVER['REMOTE_ADDR']]);
            break;

        case 'suspend':
            if ($target_user_id === intval($current_user['id'])) {
                $_SESSION['error'] = "You cannot suspend your own administrative account.";
                break;
            }
            
            $upd = $pdo->prepare("UPDATE users SET is_active = 0 WHERE id = ?");
            $upd->execute([$target_user_id]);
            
            $_SESSION['message'] = "User '{$target_user['username']}' has been suspended.";
            
            $audit = $pdo->prepare("INSERT INTO audit_logs (user_id, action, details, ip_address) VALUES (?, 'SUSPEND_USER', ?, ?)");
            $audit->execute([$current_user['id'], "Suspended user account: {$target_user['username']}", $_SERVER['REMOTE_ADDR']]);
            break;

        case 'unsuspend':
            $upd = $pdo->prepare("UPDATE users SET is_active = 1 WHERE id = ?");
            $upd->execute([$target_user_id]);
            
            $_SESSION['message'] = "User '{$target_user['username']}' has been reactivated.";
            
            $audit = $pdo->prepare("INSERT INTO audit_logs (user_id, action, details, ip_address) VALUES (?, 'REACTIVATE_USER', ?, ?)");
            $audit->execute([$current_user['id'], "Reactivated user account: {$target_user['username']}", $_SERVER['REMOTE_ADDR']]);
            break;

        case 'reset_2fa':
            $upd = $pdo->prepare("UPDATE users SET two_fa_enabled = 0, two_fa_secret = NULL WHERE id = ?");
            $upd->execute([$target_user_id]);
            
            $_SESSION['message'] = "Two-factor authentication has been reset for '{$target_user['username']}'.";
            
            $audit = $pdo->prepare("INSERT INTO audit_logs (user_id, action, details, ip_address) VALUES (?, 'RESET_2FA', ?, ?)");
            $audit->execute([$current_user['id'], "Reset 2FA for user account: {$target_user['username']}", $_SERVER['REMOTE_ADDR']]);
            break;

        default:
            $_SESSION['error'] = "Unknown management action requested.";
            break;
    }
} catch (Exception $e) {
    $_SESSION['error'] = "Database error: " . $e->getMessage();
}

header('Location: ../users.php');
exit;
