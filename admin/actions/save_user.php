<?php
// admin/actions/save_user.php - Handles user creation with rate-limited custom username validation & auto-allocation
require_once '../../db/db.php';
require_once '../../db/auth_helpers.php';
require_once '../../db/mail_helper.php';
require_once '../../includes/functions.php';
session_start();

if (!is_module_enabled($pdo, 'users')) {
    http_response_code(403);
    exit('403 Forbidden: The User Management module is currently disabled.');
}

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    http_response_code(405);
    exit('Method Not Allowed');
}
verify_csrf_token();
$current_user = require_permission($pdo, 'manage_users', 'Manage user accounts, roles, and status');

$first_name = trim($_POST['first_name'] ?? '');
$surname    = trim($_POST['surname'] ?? '');
$requested_username = trim($_POST['username'] ?? '');
$email = trim($_POST['email'] ?? '');
$requested_role_id = intval($_POST['role_id'] ?? 0);
$volunteer_id = intval($_POST['volunteer_id'] ?? 0);

$role_check = $pdo->prepare("SELECT id, role_name FROM roles WHERE id = ?");
$role_check->execute([$requested_role_id]);
$valid_role = $role_check->fetch(PDO::FETCH_ASSOC);

$role_id = $valid_role ? $valid_role['id'] : 2;
$role_name = $valid_role ? $valid_role['role_name'] : 'user';

if (empty($email)) {
    $_SESSION['error'] = "Email address is a required field.";
    header('Location: ../create_user.php');
    exit;
}
if (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
    $_SESSION['error'] = "Invalid email format.";
    header('Location: ../create_user.php');
    exit;
}

$chk_email = $pdo->prepare("SELECT id FROM users WHERE email = ?");
$chk_email->execute([$email]);
if ($chk_email->fetch()) {
    $_SESSION['error'] = "A user account with that email address already exists.";
    header('Location: ../create_user.php');
    exit;
}

// Secure Username Determination with Rate-Limiting Protection
$username = '';
if (!empty($requested_username)) {
    // 1. Log this check attempt immediately to the database audit trail for rate limiting
    log_username_check_attempt($pdo);

    // 2. Check if this IP has exceeded the 24-hour rate limit (3 attempts max)
    if (has_exceeded_username_check_limit($pdo)) {
        $_SESSION['error'] = "Username availability check limit reached (max 3 per 24 hours). A unique username has been automatically allocated instead.";
    } else {
        $sanitized = preg_replace('/[^a-zA-Z0-9_\-]/', '', $requested_username);
        
        $chk_user = $pdo->prepare("SELECT id FROM users WHERE username = ?");
        $chk_user->execute([$sanitized]);
        if ($chk_user->fetch()) {
            $_SESSION['error'] = "The username '{$sanitized}' is already taken. A unique username has been automatically allocated instead.";
        } else {
            $username = $sanitized; // Custom username is valid and available!
        }
    }
}

// Fallback: Auto-generate unique username if none provided or if custom check failed/rate-limited
if (empty($username)) {
    $base = strtolower(substr(preg_replace('/[^a-zA-Z]/', '', $first_name), 0, 1) . preg_replace('/[^a-zA-Z]/', '', $surname));
    if (empty($base)) {
        $base = 'user';
    }

    $username = $base;
    $counter = 1;
    while (true) {
        $chk_user = $pdo->prepare("SELECT id FROM users WHERE username = ?");
        $chk_user->execute([$username]);
        if (!$chk_user->fetch()) {
            break;
        }
        $username = $base . $counter;
        $counter++;
    }
    
    // Append auto-allocated notice if they originally tried a taken/limited name
    if (!empty($requested_username) && empty($_SESSION['error'])) {
        $_SESSION['error'] = "The username you requested was unavailable. Username '{$username}' was automatically allocated.";
    }
}

$token = bin2hex(random_bytes(32));
$expires = date('Y-m-d H:i:s', strtotime('+24 hours'));

$ins = $pdo->prepare("INSERT INTO users (username, first_name, surname, email, password_hash, role_id, invite_token, invite_expires_at) VALUES (?, ?, ?, ?, ?, ?, ?, ?)");

if ($ins->execute([$username, $first_name, $surname, $email, '', $role_id, $token, $expires])) {
    $user_details = [
        'first_name' => $first_name,
        'surname'    => $surname,
        'username'   => $username,
        'role_name'  => ucwords($role_name)
    ];

    if (send_user_invitation($pdo, $email, $token, $user_details)) {
        if ($volunteer_id > 0) {
            $up_vol = $pdo->prepare("UPDATE volunteer_submissions SET status = 'Accepted' WHERE id = ?");
            $up_vol->execute([$volunteer_id]);
        }

        if (empty($_SESSION['message'])) {
            $_SESSION['message'] = "User '{$username}' created successfully and invitation email sent!";
        }
        
        $audit = $pdo->prepare("INSERT INTO audit_logs (user_id, action, details, ip_address) VALUES (?, 'CREATE_USER', ?, ?)");
        $audit->execute([$current_user['id'], "Created user account ({$role_name}) with username: {$username}", $_SERVER['REMOTE_ADDR']]);
    } else {
        $_SESSION['error'] = "User account created in database, but failed to dispatch the invitation email.";
    }
} else {
    $_SESSION['error'] = "Database insertion failed.";
}

header('Location: ../create_user.php');
exit;
