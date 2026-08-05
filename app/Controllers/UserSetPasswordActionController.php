<?php
/**
 * MIGRATED FILE MAPPING
 * ---------------------
 * Original Old File: user/set_password.php/user/actions/save_password.php
 * Migrated Date: 2026-08-05 05:18:19
 */declare(strict_types=1);


namespace App\Controllers;

use PDO;

class UserSetPasswordActionController
{
    private PDO $pdo;

    public function __construct(PDO $pdo)
    {
        $this->pdo = $pdo;
    }

    public function save(): void
    {
        if (session_status() === PHP_SESSION_NONE) {
            session_start();
        }

        $serverMethod = isset($_SERVER['REQUEST_METHOD']) && is_string($_SERVER['REQUEST_METHOD']) ? $_SERVER['REQUEST_METHOD'] : 'GET';
        if ($serverMethod !== 'POST') {
            http_response_code(405);
            exit('Method Not Allowed');
        }

        verify_csrf_token();

        $post = $_POST;
        $token = isset($post['token']) && is_string($post['token']) ? trim($post['token']) : '';
        $password = isset($post['password']) && is_string($post['password']) ? $post['password'] : '';
        $confirmPassword = isset($post['confirm_password']) && is_string($post['confirm_password']) ? $post['confirm_password'] : '';

        if ($token === '') {
            http_response_code(403);
            exit("Invalid or missing setup token.");
        }

        // 1. Validate inputs FIRST before checking database state or modifying tokens
        if ($password === '' || strlen($password) < 8) {
            $_SESSION['error'] = "Password must be at least 8 characters long.";
            header("Location: /user/set_password.php?token=" . urlencode($token));
            exit;
        }

        if ($password !== $confirmPassword) {
            $_SESSION['error'] = "Passwords do not match.";
            header("Location: /user/set_password.php?token=" . urlencode($token));
            exit;
        }

        // 2. Verify token validity and expiration against the database using positional parameters
        $stmt = $this->pdo->prepare("
            SELECT id, username FROM users 
            WHERE (invite_token = ? AND invite_expires_at > NOW()) 
               OR (reset_token = ? AND reset_expires_at > NOW())
            LIMIT 1
        ");
        $stmt->execute([$token, $token]);
        /** @var array{id: int|string, username: string}|false $user */
        $user = $stmt->fetch(PDO::FETCH_ASSOC);

        if ($user === false) {
            http_response_code(403);
            exit("This password setup link is invalid or has expired.");
        }

        // 3. Process success: Hash password, clear both invite and reset tokens atomically, and activate account
        $hash = password_hash($password, PASSWORD_DEFAULT);

        $update = $this->pdo->prepare("
            UPDATE users 
            SET password_hash = ?, 
                invite_token = NULL, 
                invite_expires_at = NULL, 
                reset_token = NULL, 
                reset_expires_at = NULL, 
                is_new_user = 0, 
                is_active = 1 
            WHERE id = ?
        ");

        if ($update->execute([$hash, $user['id']])) {
            // Set a distinct success flag or message
            $_SESSION['message'] = "Password successfully configured! You can now log in.";
            header("Location: /user/set_password.php?token=" . urlencode($token));
            exit;
        } else {
            $_SESSION['error'] = "Failed to update password due to a database error.";
            header("Location: /user/set_password.php?token=" . urlencode($token));
            exit;
        }
    }
}
