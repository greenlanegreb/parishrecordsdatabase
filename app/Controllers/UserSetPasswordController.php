<?php
/**
 * MIGRATED FILE MAPPING
 * ---------------------
 * Original Old File: user/set_password.php/user/actions/save_password.php
 * Migrated Date: 2026-08-05 05:17:35
 */declare(strict_types=1);


namespace App\Controllers;

use PDO;

class UserSetPasswordController
{
    private PDO $pdo;

    public function __construct(PDO $pdo)
    {
        $this->pdo = $pdo;
    }

    public function show(): void
    {
        if (session_status() === PHP_SESSION_NONE) {
            session_start();
        }

        $queryGet = $_GET;
        $token = isset($queryGet['token']) && is_string($queryGet['token']) ? trim($queryGet['token']) : '';

        if ($token === '') {
            exit(__('set_password.exit_invalid_token'));
        }

        $message = $_SESSION['message'] ?? '';
        $error = $_SESSION['error'] ?? '';

        /** @var array{id?: int|string, username: string}|false $user */
        $user = false;

        // If a success message is already set, the token was just consumed by save_password.php.
        // Skip the database token validation so we don't trigger a false expiration error.
        if (!empty($message)) {
            $user = ['username' => 'User']; // Fallback placeholder for greeting
        } else {
            $stmt = $this->pdo->prepare("SELECT id, username FROM users WHERE (invite_token = ? AND invite_expires_at > NOW()) OR (reset_token = ? AND reset_expires_at > NOW())");
            $stmt->execute([$token, $token]);
            $user = $stmt->fetch(PDO::FETCH_ASSOC);

            if ($user === false) {
                exit(__('set_password.exit_expired_token'));
            }
        }

        unset($_SESSION['message'], $_SESSION['error']);

        require_once __DIR__ . '/../Views/user/set_password.php';
    }
}
