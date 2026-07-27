<?php
// admin/actions/save_user.php - Handles user creation logic and invitation dispatch
require_once '../../db/db.php';
require_once '../../db/auth_helpers.php';
require_once '../../db/mail_helper.php';

// Enforce strict administrator privileges and validate POST request method via central helper
$current_user = initialize_action($pdo, 'admin', 'POST');

$username = trim($_POST['username'] ?? '');
$email = trim($_POST['email'] ?? '');
$allowed_roles = ['user', 'moderator', 'admin'];
$role = in_array($_POST['role'] ?? '', $allowed_roles) ? $_POST['role'] : 'user';

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

        $ins = $pdo->prepare("INSERT INTO users (username, email, password_hash, role, reset_token, reset_expires) VALUES (?, ?, ?, ?, ?, ?)");
        
        if ($ins->execute([$username, $email, '', $role, $token, $expires])) {
            // FIXED: Added $pdo as the first argument
            if (send_user_invitation($pdo, $email, $token)) {
                $_SESSION['message'] = "User '{$username}' created successfully with role '{$role}' and invitation email sent!";
                
                $audit = $pdo->prepare("INSERT INTO audit_logs (user_id, action, details, ip_address) VALUES (?, 'CREATE_USER', ?, ?)");
                $audit->execute([$current_user['id'], "Created user account ({$role}) and sent invite: {$username}", $_SERVER['REMOTE_ADDR']]);
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
