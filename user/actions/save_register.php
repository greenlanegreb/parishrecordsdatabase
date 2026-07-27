<?php
// user/actions/save_register.php - Handles user onboarding and email verification token generation
require_once '../../db/db.php';
require_once '../../db/mail_helper.php';
session_start();

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $username = trim($_POST['username'] ?? '');
    $email = trim($_POST['email'] ?? '');
    $password = $_POST['password'] ?? '';

    if (empty($username) || empty($email) || empty($password)) {
        $_SESSION['error'] = "All fields are required.";
    } elseif (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
        $_SESSION['error'] = "Invalid email format.";
    } else {
        $stmt = $pdo->prepare("SELECT id FROM users WHERE username = ? OR email = ?");
        $stmt->execute([$username, $email]);
        
        if ($stmt->rowCount() > 0) {
            $_SESSION['error'] = "Username or email is already registered.";
        } else {
            $password_hash = password_hash($password, PASSWORD_DEFAULT);
            $token = bin2hex(random_bytes(32));
            $expires_at = date('Y-m-d H:i:s', strtotime('+24 hours'));

            $insert = $pdo->prepare("INSERT INTO users (username, email, password_hash, verification_token, token_expires_at, is_active) VALUES (?, ?, ?, ?, ?, 1)");
            
            if ($insert->execute([$username, $email, $password_hash, $token, $expires_at])) {
                // Dispatch using mail_helper with $pdo as the first argument
                if (send_user_invitation($pdo, $email, $token)) {
                    $_SESSION['message'] = "Registration successful! Please check your email to verify your account.";
                } else {
                    $_SESSION['message'] = "Registration successful, but failed to send verification email.";
                }
                error_log("New user registered: {$username}. Token generated.");
            } else {
                $_SESSION['error'] = "An error occurred during registration. Please try again.";
            }
        }
    }
}

header('Location: ../register.php');
exit;
