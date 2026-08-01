<?php
// admin/actions/save_user_management.php - Handles user moderation, score overrides, role changes, email updates, invitation/password resets, and deletions
require_once '../../db/db.php';
require_once '../../db/auth_helpers.php';
require_once '../../db/mail_helper.php';
require_once '../../includes/functions.php';
session_start();

// Ensure the users module is enabled; otherwise block action execution
if (!is_module_enabled($pdo, 'users')) {
    http_response_code(403);
    exit('403 Forbidden: The User Management module is currently disabled.');
}

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

// Determine the first admin user ID dynamically to protect them server-side
$first_admin_id = 1;
try {
    $fa_stmt = $pdo->query("
        SELECT u.id FROM users u
        JOIN roles r ON u.role_id = r.id
        WHERE LOWER(r.role_name) = 'admin'
        ORDER BY u.created_at ASC, u.id ASC
        LIMIT 1
    ");
    $fa_id = $fa_stmt->fetchColumn();
    if ($fa_id) {
        $first_admin_id = intval($fa_id);
    }
} catch (Exception $e) {
    // Fallback safely to ID 1
}

// Prevent modifying or deleting the protected primary admin account server-side
$is_target_first_admin = ($target_user_id === $first_admin_id);
if ($is_target_first_admin && in_array($action, ['change_role', 'suspend', 'delete'])) {
    http_response_code(403);
    $_SESSION['error'] = "Security Error: The primary system administrator account cannot be modified or deleted.";
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

        case 'update_email':
            $new_email = trim($_POST['new_email'] ?? '');
            if (empty($new_email) || !filter_var($new_email, FILTER_VALIDATE_EMAIL)) {
                $_SESSION['error'] = "Invalid or empty email address format provided.";
                break;
            }

            // Check collision
            $chk = $pdo->prepare("SELECT id FROM users WHERE email = ? AND id != ?");
            $chk->execute([$new_email, $target_user_id]);
            if ($chk->fetch()) {
                $_SESSION['error'] = "That email address is already registered to another user account.";
                break;
            }

            $upd = $pdo->prepare("UPDATE users SET email = ? WHERE id = ?");
            $upd->execute([$new_email, $target_user_id]);

            $_SESSION['message'] = "Email address for '{$target_user['username']}' updated to {$new_email}.";

            $audit = $pdo->prepare("INSERT INTO audit_logs (user_id, action, details, ip_address) VALUES (?, 'UPDATE_USER_EMAIL', ?, ?)");
            $audit->execute([$current_user['id'], "Changed email address for {$target_user['username']} to {$new_email}", $_SERVER['REMOTE_ADDR']]);
            break;

        case 'send_password_reset':
            $token = bin2hex(random_bytes(32));
            $expires = date('Y-m-d H:i:s', strtotime('+24 hours'));
            
            // Uses reset_token column for password reset requests
            $upd = $pdo->prepare("UPDATE users SET reset_token = ?, reset_expires_at = ? WHERE id = ?");
            $upd->execute([$token, $expires, $target_user_id]);

            // Fetch target user email, username, and role details
            $u_det = $pdo->prepare("SELECT u.email, u.username, r.role_name FROM users u LEFT JOIN roles r ON u.role_id = r.id WHERE u.id = ?");
            $u_det->execute([$target_user_id]);
            $u_data = $u_det->fetch(PDO::FETCH_ASSOC);

            if ($u_data && !empty($u_data['email'])) {
                send_user_invitation($pdo, $u_data['email'], $token, [
                    'first_name' => $u_data['username'],
                    'surname'    => '',
                    'username'   => $u_data['username'],
                    'role_name'  => $u_data['role_name'] ?? 'User'
                ], 'password_reset');

                $_SESSION['message'] = "Password reset link successfully dispatched to '{$u_data['username']}'.";

                $audit = $pdo->prepare("INSERT INTO audit_logs (user_id, action, details, ip_address) VALUES (?, 'SEND_PASSWORD_RESET', ?, ?)");
                $audit->execute([$current_user['id'], "Dispatched password reset link to user: {$u_data['username']}", $_SERVER['REMOTE_ADDR']]);
            } else {
                $_SESSION['error'] = "Could not find a valid email address for this user.";
            }
            break;

        case 'resend_invite':
            $token = bin2hex(random_bytes(32));
            $expires = date('Y-m-d H:i:s', strtotime('+24 hours'));
            
            // Uses invite_token column for invitations
            $upd = $pdo->prepare("UPDATE users SET invite_token = ?, invite_expires_at = ? WHERE id = ?");
            $upd->execute([$token, $expires, $target_user_id]);

            $u_det = $pdo->prepare("SELECT u.email, u.username, r.role_name FROM users u LEFT JOIN roles r ON u.role_id = r.id WHERE u.id = ?");
            $u_det->execute([$target_user_id]);
            $u_data = $u_det->fetch(PDO::FETCH_ASSOC);

            if ($u_data && !empty($u_data['email'])) {
                send_user_invitation($pdo, $u_data['email'], $token, [
                    'first_name' => $u_data['username'],
                    'surname'    => '',
                    'username'   => $u_data['username'],
                    'role_name'  => $u_data['role_name'] ?? 'User'
                ]);

                $_SESSION['message'] = "Invitation email successfully dispatched to '{$u_data['username']}'.";

                $audit = $pdo->prepare("INSERT INTO audit_logs (user_id, action, details, ip_address) VALUES (?, 'RESEND_INVITE', ?, ?)");
                $audit->execute([$current_user['id'], "Dispatched invitation email to user: {$u_data['username']}", $_SERVER['REMOTE_ADDR']]);
            } else {
                $_SESSION['error'] = "Could not find a valid email address for this user.";
            }
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

        case 'delete':
            if ($target_user_id === intval($current_user['id'])) {
                $_SESSION['error'] = "You cannot delete your own active administrative account.";
                break;
            }

            $del = $pdo->prepare("DELETE FROM users WHERE id = ?");
            $del->execute([$target_user_id]);

            $_SESSION['message'] = "User '{$target_user['username']}' has been permanently deleted.";

            $audit = $pdo->prepare("INSERT INTO audit_logs (user_id, action, details, ip_address) VALUES (?, 'DELETE_USER', ?, ?)");
            $audit->execute([$current_user['id'], "Permanently deleted user account: {$target_user['username']}", $_SERVER['REMOTE_ADDR']]);
            break;

        case 'reset_2fa':
            $upd = $pdo->prepare("UPDATE users SET two_fa_enabled = 0, google_2fa_secret = NULL WHERE id = ?");
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
