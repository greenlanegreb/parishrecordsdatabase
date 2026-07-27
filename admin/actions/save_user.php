<?php
// admin/actions/save_user.php - Handles user creation logic and invitation dispatch
require_once '../../db/db.php';
require_once '../../db/auth_helpers.php';
require_once '../../db/mail_helper.php';
session_start();

// Enforce permission-based access control and validate POST request method via central helper
if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    http_response_code(405);
    exit('Method Not Allowed');
}
verify_csrf_token();
$current_user = require_permission($pdo, 'manage_users', 'Manage user accounts, roles, and status');

$username = trim($_POST['username'] ?? '');
$email = trim($_POST['email'] ?? '');
$requested_role_id = intval($_POST['role_id'] ?? 0);

// Validate that the requested role_id exists in the database dynamically
$role_check = $pdo->prepare("SELECT id, role_name FROM roles WHERE id = ?");
$role_check->execute([$requested_role_id]);
$valid_role = $role_check->fetch(PDO::FETCH_ASSOC);

// Fallback to default 'user' role if invalid role selected
if (!$valid_role) {
    $default_role = $pdo->query("SELECT id, role_name FROM roles WHERE role_name = 'user' LIMIT 1")->fetch(PDO::FETCH_ASSOC);
    $role_id = $default_role ? $default_role['id'] : 2;
    $role_name = $default_role ? $default_role['role_name'] : 'user';
} else {
    $role_id = $valid_role['id'];
    $role_name = $valid_role['role_name'];
}

if (empty($username) || empty($email)) {
    $_SESSION['error'] = "Username and email are required fields.";
} elseif (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
    $_SESSION['error'] = "Invalid email format.";
} else {
    $chk = $pdo->prepare("SELECT id FROM users WHERE username = ? OR email = ?");
    $chk->execute([$username, $email]);
    if ($chk->fetch()) {
        $_SESSION['error'] = "A user with that username or email already exists.";
    } else {
        $token = bin2hex(random_bytes(32));
        $expires = date('Y-m-d H:i:s', strtotime('+24 hours'));

        $ins = $pdo->prepare("INSERT INTO users (username, email, password_hash, role_id, reset_token, reset_expires) VALUES (?, ?, ?, ?, ?, ?)");
        
        if ($ins->execute([$username, $email, '', $role_id, $token, $expires])) {
            if (send_user_invitation($pdo, $email, $token)) {
                $_SESSION['message'] = "User '{$username}' created successfully with role '{$role_name}' and invitation email sent!";
                
                $audit = $pdo->prepare("INSERT INTO audit_logs (user_id, action, details, ip_address) VALUES (?, 'CREATE_USER', ?, ?)");
                $audit->execute([$current_user['id'], "Created user account ({$role_name}) and sent invite: {$username}", $_SERVER['REMOTE_ADDR']]);
            } else {
                $_SESSION['error'] = "User account created in database, but failed to dispatch the invitation email.";
            }
        } else {
            $_SESSION['error'] = "Database insertion failed.";
        }
    }
}

header('Location: ../create_user.php');
exit;
