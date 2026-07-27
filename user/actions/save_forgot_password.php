<?php
// user/actions/save_forgot_password.php - Generates password reset tokens and dispatches emails for guests
require_once '../../db/db.php';
require_once '../../db/auth_helpers.php';
require_once '../../db/mail_helper.php';
session_start();

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    verify_csrf_token();
    
    $email = trim($_POST['email'] ?? '');

    if (empty($email) || !filter_var($email, FILTER_VALIDATE_EMAIL)) {
        $_SESSION['error'] = "Please provide a valid email address.";
    } else {
        $stmt = $pdo->prepare("SELECT id, username, is_active FROM users WHERE email = ?");
        $stmt->execute([$email]);
        $user = $stmt->fetch();

        // To prevent user enumeration attacks, we show a generic success message even if the email isn't found,
        // but only dispatch the email if the user actually exists and is active.
        if ($user && $user['is_active']) {
            $token = bin2hex(random_bytes(32));
            $expires = date('Y-m-d H:i:s', strtotime('+24 hours'));

            $upd = $pdo->prepare("UPDATE users SET reset_token = ?, reset_expires = ? WHERE id = ?");
            if ($upd->execute([$token, $expires, $user['id']])) {
                // Send custom reset email
                $system_name = get_system_name($pdo);
                $subject = $system_name . " - Password Recovery Request";
                
                $host = $_SERVER['HTTP_HOST'] ?? 'localhost';
                $protocol = (!empty($_SERVER['HTTPS']) && $_SERVER['HTTPS'] !== 'off') ? "https://" : "https://";
                $base_path = defined('BASE_PATH') ? BASE_PATH : '';
                $reset_link = $protocol . $host . $base_path . "/user/set_password.php?token=" . $token;

                $message_body = "Hello " . $user['username'] . ",\n\n" .
                                "We received a request to reset your password for " . $system_name . ".\n" .
                                "Click the link below to choose a new password:\n\n" .
                                $reset_link . "\n\n" .
                                "This link is valid for 24 hours. If you did not request a password reset, please ignore this email.\n";

                send_user_invitation($pdo, $email, $token, $subject, $message_body);
            }
        }

        $_SESSION['message'] = "If an account matches that email address, a password reset link has been dispatched.";
    }
}

header('Location: ../forgot_password.php');
exit;
