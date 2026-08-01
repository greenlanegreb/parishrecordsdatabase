<?php
// user/actions/save_password.php - Handles password setup via secure token
require_once '../../db/db.php';
require_once '../../db/auth_helpers.php';
require_once '../../includes/functions.php';
session_start();

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    http_response_code(405);
    exit('Method Not Allowed');
}

verify_csrf_token();

$token = $_POST['token'] ?? '';
$password = $_POST['password'] ?? '';
$confirm_password = $_POST['confirm_password'] ?? '';

if (empty($token)) {
    http_response_code(403);
    exit("Invalid or missing setup token.");
}

// 1. Validate inputs FIRST before checking database state or modifying tokens
if (empty($password) || strlen($password) < 8) {
    $_SESSION['error'] = "Password must be at least 8 characters long.";
    header("Location: ../set_password.php?token=" . urlencode($token));
    exit;
}

if ($password !== $confirm_password) {
    $_SESSION['error'] = "Passwords do not match.";
    header("Location: ../set_password.php?token=" . urlencode($token));
    exit;
}

// 2. Verify token validity and expiration against the database
$stmt = $pdo->prepare("SELECT id, username FROM users WHERE verification_token = ? AND token_expires_at > NOW()");
$stmt->execute([$token]);
$user = $stmt->fetch();

if (!$user) {
    http_response_code(403);
    exit("This password setup link is invalid or has expired.");
}

// 3. Process success: Hash password, clear token atomically, and activate account
$hash = password_hash($password, PASSWORD_DEFAULT);

$update = $pdo->prepare("
    UPDATE users 
    SET password_hash = ?, 
        verification_token = NULL, 
        token_expires_at = NULL, 
        is_new_user = 0, 
        is_active = 1 
    WHERE id = ?
");

if ($update->execute([$hash, $user['id']])) {
    // Set a distinct success flag or message
    $_SESSION['message'] = "Password successfully configured! You can now log in.";
    header("Location: ../set_password.php?token=" . urlencode($token));
    exit;
} else {
    $_SESSION['error'] = "Failed to update password due to a database error.";
    header("Location: ../set_password.php?token=" . urlencode($token));
    exit;
}
