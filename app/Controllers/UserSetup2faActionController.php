<?php
/**
 * MIGRATED FILE MAPPING
 * ---------------------
 * Original Old File: user/setup_2fa.php/user/actions/save_setup_2fa.php
 * Migrated Date: 2026-08-05 05:23:06
 */declare(strict_types=1);


namespace App\Controllers;

use PDO;

class UserSetup2faActionController
{
    private PDO $pdo;

    public function __construct(PDO $pdo)
    {
        $this->pdo = $pdo;
    }

    public function handle(): void
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
        /** @var array{id: int|string, username: string} $currentUser */
        $currentUser = require_permission($this->pdo, 'setup_2fa', 'Allows setting up and configuring Google Authenticator 2FA');
        $userId = $currentUser['id'];

        $secret = isset($_SESSION['temp_2fa_secret']) && is_string($_SESSION['temp_2fa_secret']) ? $_SESSION['temp_2fa_secret'] : '';
        $post = $_POST;
        $enteredCode = isset($post['code']) && is_string($post['code']) ? trim($post['code']) : '';
        $remoteAddr = isset($_SERVER['REMOTE_ADDR']) && is_string($_SERVER['REMOTE_ADDR']) ? $_SERVER['REMOTE_ADDR'] : '127.0.0.1';

        require_once __DIR__ . '/../../db/2fa_helpers.php';

        if ($secret !== '' && verify_google_2fa($secret, $enteredCode)) {
            $hashedCodesJson = isset($_SESSION['temp_hashed_backup_codes']) && is_string($_SESSION['temp_hashed_backup_codes']) ? $_SESSION['temp_hashed_backup_codes'] : '[]';
            $upd = $this->pdo->prepare("UPDATE users SET google_2fa_secret = ?, backup_codes = ?, two_fa_enabled = 1 WHERE id = ?");
            if ($upd->execute([$secret, $hashedCodesJson, $userId])) {
                unset($_SESSION['temp_2fa_secret'], $_SESSION['temp_raw_backup_codes'], $_SESSION['temp_hashed_backup_codes']);
                $_SESSION['message'] = "Two-Factor Authentication successfully enabled!";
                header('Location: /user/profile.php');
                exit;
            } else {
                http_response_code(403);
                error_log("Database error saving 2FA setup for user ID: {$userId} from IP: " . $remoteAddr);
                $_SESSION['error'] = "Failed to activate 2FA in the database.";
            }
        } else {
            http_response_code(403);
            error_log("Failed 2FA activation confirmation attempt for user ID: {$userId} from IP: " . $remoteAddr);
            $_SESSION['error'] = "Invalid 2FA code. Please ensure your authenticator app is synced and try again.";
        }

        header('Location: /user/setup_2fa.php');
        exit;
    }
}
