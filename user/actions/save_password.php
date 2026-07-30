<?php
// user/actions/save_password.php - Handles password setup via secure token
require_once '../../db/db.php';
require_once '../../includes/functions.php';
session_start();

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    http_response_code(405);
    exit('Method Not Allowed');
}

verify_csrf_token();

$token = $_POST['token'] ?? '';

if (empty($token)) {
    http_response_code(403);
    exit("Invalid or missing setup token.");
}

// Verify token validity and expiration using correct schema columns
$stmt = $pdo->prepare("SELECT id, username FROM users WHERE verification_token = ? AND token_expires_at > NOW()");
$stmt->execute([$token]);
$user = $stmt->fetch();

if (!$user) {
    http_response_code(403);
    exit("This password setup link is invalid or has expired.");
}

$password = $_POST['password'] ?? '';
$confirm_password = $_POST['confirm_password'] ?? '';

if (empty($password) || strlen($password) < 8) {
    $_SESSION['error'] = "Password must be at least 8 characters long.";
} elseif ($password !== $confirm_password) {
    $_SESSION['error'] = "Passwords do not match.";
} else {
    $hash = password_hash($password, PASSWORD_DEFAULT);

    $update = $pdo->prepare("UPDATE users SET password_hash = ?, verification_token = NULL, token_expires_at = NULL, is_new_user = 0, is_active = 1 WHERE id = ?");
    if ($update->execute([$hash, $user['id']])) {
        $_SESSION['message'] = "Password successfully configured! You can now log in.";
        header("Location: ../set_password.php?token=" . urlencode($token));
        exit;
    } else {
        $_SESSION['error'] = "Failed to update password.";
    }
}

header("Location: ../set_password.php?token=" . urlencode($token));
exit;
