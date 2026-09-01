<?php
/**
 * MIGRATED FILE MAPPING
 * ---------------------
 * Original Old File: user/set_password.php/user/actions/save_password.php
 * Migrated Date: 2026-08-05 05:18:19
 */
declare(strict_types=1);

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
        $serverMethod = isset($_SERVER['REQUEST_METHOD']) && is_string($_SERVER['REQUEST_METHOD']) ? $_SERVER['REQUEST_METHOD'] : 'GET';
        if ($serverMethod !== 'POST') {
            http_response_code(405);
            exit('Method Not Allowed');
        }

        verify_csrf_token();

        $basePath = defined('BASE_PATH') ? rtrim(BASE_PATH, '/') : '';
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
            header("Location: " . $basePath . "/user/set-password?token=" . urlencode($token));
            exit;
        }

        if ($password !== $confirmPassword) {
            $_SESSION['error'] = "Passwords do not match.";
            header("Location: " . $basePath . "/user/set-password?token=" . urlencode($token));
            exit;
        }

        // 2. Verify token validity and expiration against the database using positional parameters
        // Prefer invite match so we know whether this is first-time setup
        $stmt = $this->pdo->prepare("
            SELECT id, username,
                   CASE
                     WHEN invite_token = ? AND invite_expires_at > NOW() THEN 1
                     ELSE 0
                   END AS is_invite_setup
            FROM users
            WHERE (invite_token = ? AND invite_expires_at > NOW())
               OR (reset_token = ? AND reset_expires_at > NOW())
            LIMIT 1
        ");
        $stmt->execute([$token, $token, $token]);
        /** @var array{id: int|string, username: string, is_invite_setup?: int|string}|false $user */
        $user = $stmt->fetch(PDO::FETCH_ASSOC);

        if ($user === false) {
            http_response_code(403);
            exit("This password setup link is invalid or has expired.");
        }

        // 3. Process success: Hash password, clear both invite and reset tokens atomically, and activate account
        $hash = password_hash($password, PASSWORD_DEFAULT);

        // Invite first-time setup: keep is_new_user = 1 so login still runs onboarding.
        // Password reset for an existing account: leave is_new_user unchanged (usually 0).
        $isInvite = !empty($user['is_invite_setup']);
        if ($isInvite) {
            $update = $this->pdo->prepare("
                UPDATE users
                SET password_hash = ?,
                    invite_token = NULL,
                    invite_expires_at = NULL,
                    reset_token = NULL,
                    reset_expires_at = NULL,
                    is_new_user = 1,
                    is_active = 1,
                    email_verified = 1
                WHERE id = ?
            ");
        } else {
            $update = $this->pdo->prepare("
                UPDATE users
                SET password_hash = ?,
                    invite_token = NULL,
                    invite_expires_at = NULL,
                    reset_token = NULL,
                    reset_expires_at = NULL,
                    is_active = 1
                WHERE id = ?
            ");
        }

        if ($update->execute([$hash, $user['id']])) {
            $_SESSION['message'] = $isInvite
                ? (function_exists('__') && __('set_password.msg_ready_onboarding') !== 'set_password.msg_ready_onboarding'
                    ? __('set_password.msg_ready_onboarding')
                    : 'Password saved. Please log in — you will be guided through a short setup.')
                : (function_exists('__') && __('set_password.msg_ready') !== 'set_password.msg_ready'
                    ? __('set_password.msg_ready')
                    : 'Password successfully configured! You can now log in.');
            header('Location: ' . $basePath . '/login');
            exit;
        }

        $_SESSION['error'] = function_exists('__') && __('set_password.err_db') !== 'set_password.err_db'
            ? __('set_password.err_db')
            : 'Failed to update password due to a database error.';
        header('Location: ' . $basePath . '/user/set-password?token=' . urlencode($token));
        exit;
    }
}
