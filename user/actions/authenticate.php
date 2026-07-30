<?php
// user/actions/authenticate.php - Handles user credential verification and 2FA routing
require_once '../../db/db.php';
session_start();

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $username = trim($_POST['username'] ?? '');
    $password = $_POST['password'] ?? '';
    
    // Added is_new_user to the select query
    $stmt = $pdo->prepare("SELECT id, username, password_hash, two_fa_enabled, google_2fa_secret, is_active, is_new_user FROM users WHERE username = ? OR email = ?");
    $stmt->execute([$username, $username]);
    $user = $stmt->fetch();

    if ($user && $user['is_active'] && password_verify($password, $user['password_hash'])) {
        if ($user['two_fa_enabled']) {
            $_SESSION['pending_2fa_user_id'] = $user['id'];
            header('Location: ../verify_2fa.php');
            exit;
        } else {
            $_SESSION['user_id'] = $user['id'];
            $_SESSION['username'] = $user['username'];

            // Forward new users to onboarding wizard, otherwise go to data_entry
            if (!empty($user['is_new_user'])) {
                header('Location: ../onboarding.php');
            } else {
                header('Location: ../data_entry.php');
            }
            exit;
        }
    } else {
        error_log("Failed login attempt for user: '{$username}' from IP: " . $_SERVER['REMOTE_ADDR']);
        http_response_code(403);
        $_SESSION['error'] = "Invalid credentials or account access restricted.";
        header('Location: ../login.php');
        exit;
    }
}

header('Location: ../login.php');
exit;
