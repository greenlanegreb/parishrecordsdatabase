<?php
/**
 * MIGRATED FILE MAPPING
 * ---------------------
 * Original Old File: user/verify_email.php
 * Migrated Date: 2026-08-05 05:34:13
 */declare(strict_types=1);


namespace App\Controllers;

use PDO;

class UserVerifyEmailController
{
    private PDO $pdo;

    public function __construct(PDO $pdo)
    {
        $this->pdo = $pdo;
    }

    public function verify(): void
    {
        if (session_status() === PHP_SESSION_NONE) {
            session_start();
        }

        $message = '';
        $error = '';

        $queryGet = $_GET;
        $token = isset($queryGet['token']) && is_string($queryGet['token']) ? trim($queryGet['token']) : '';

        if ($token === '') {
            $error = __('verify_email.err_no_token');
        } else {
            $stmt = $this->pdo->prepare("SELECT id, email_verified, invite_expires_at FROM users WHERE invite_token = ?");
            $stmt->execute([$token]);
            /** @var array{id: int|string, email_verified: int|string, invite_expires_at: string}|false $user */
            $user = $stmt->fetch(PDO::FETCH_ASSOC);

            if ($user === false) {
                $error = __('verify_email.err_invalid_token');
            } elseif (!empty($user['email_verified'])) {
                $message = __('verify_email.msg_already_verified');
            } else {
                $currentTime = date('Y-m-d H:i:s');
                
                if ($currentTime > $user['invite_expires_at']) {
                    $error = __('verify_email.err_expired_token');
                } else {
                    $update = $this->pdo->prepare("UPDATE users SET email_verified = 1, invite_token = NULL, invite_expires_at = NULL WHERE id = ?");
                    
                    if ($update->execute([$user['id']])) {
                        $message = __('verify_email.msg_success');
                    } else {
                        $error = __('verify_email.err_update_failed');
                    }
                }
            }
        }

        require_once __DIR__ . '/../Views/user/verify_email.php';
    }
}
