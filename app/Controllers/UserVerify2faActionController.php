<?php
/**
 * MIGRATED FILE MAPPING
 * ---------------------
 * Original Old File: user/verify_2fa.php/user/actions/save_verify_2fa.php
 * Migrated Date: 2026-08-05 05:31:06
 */
declare(strict_types=1);

namespace App\Controllers;

use PDO;

class UserVerify2faActionController
{
    private PDO $pdo;

    public function __construct(PDO $pdo)
    {
        $this->pdo = $pdo;
    }

    public function handle(): void
    {
        $remoteAddr = isset($_SERVER['REMOTE_ADDR']) && is_string($_SERVER['REMOTE_ADDR']) ? $_SERVER['REMOTE_ADDR'] : '127.0.0.1';
        $basePath = defined('BASE_PATH') ? rtrim(BASE_PATH, '/') : '';

        if (!isset($_SESSION['pending_2fa_user_id'])) {
            http_response_code(403);
            error_log("Unauthorized direct access attempt to save_verify_2fa.php from IP: " . $remoteAddr);
            header('Location: ' . $basePath . '/login');
            exit;
        }

        $userId = $_SESSION['pending_2fa_user_id'];

        $serverMethod = isset($_SERVER['REQUEST_METHOD']) && is_string($_SERVER['REQUEST_METHOD']) ? $_SERVER['REQUEST_METHOD'] : 'GET';
        if ($serverMethod !== 'POST') {
            http_response_code(405);
            exit('Method Not Allowed');
        }

        $post = $_POST;
        $inputCode = isset($post['code']) && is_string($post['code']) ? trim($post['code']) : '';
        
        // Fetch user data securely using the dynamic relational roles join + user preferences
        $stmt = $this->pdo->prepare("
            SELECT u.id, u.username, r.role_name AS role, u.google_2fa_secret, u.backup_codes, 
                   u.is_new_user, u.language, u.timezone, u.date_format, u.time_format 
            FROM users u 
            LEFT JOIN roles r ON u.role_id = r.id 
            WHERE u.id = ?
        ");
        $stmt->execute([$userId]);
        /** @var array{id: int|string, username: string, role?: string, google_2fa_secret: string, backup_codes?: string, is_new_user?: int|string, language?: string, timezone?: string, date_format?: string, time_format?: string}|false $user */
        $user = $stmt->fetch(PDO::FETCH_ASSOC);

        if ($user === false) {
            http_response_code(403);
            error_log("Invalid pending 2FA user ID: {$userId} from IP: " . $remoteAddr);
            header('Location: ' . $basePath . '/login');
            exit;
        }

        require_once __DIR__ . '/../../db/2fa_helpers.php';

        $authenticated = false;

        // 1. Check TOTP app code
        if (verify_google_2fa($user['google_2fa_secret'], $inputCode)) {
            $authenticated = true;
        } 
        // 2. Check backup recovery codes
        elseif (!empty($user['backup_codes']) && is_string($user['backup_codes'])) {
            /** @var mixed $storedHashedCodes */
            $storedHashedCodes = json_decode($user['backup_codes'], true);
            if (is_array($storedHashedCodes)) {
                foreach ($storedHashedCodes as $index => $hashedCode) {
                    if (is_string($hashedCode) && password_verify($inputCode, $hashedCode)) {
                        $authenticated = true;
                        
                        unset($storedHashedCodes[$index]);
                        $updatedCodesJson = json_encode(array_values($storedHashedCodes));
                        
                        $upd = $this->pdo->prepare("UPDATE users SET backup_codes = ? WHERE id = ?");
                        $upd->execute([$updatedCodesJson, $userId]);
                        
                        error_log("Backup recovery code used and burned for user ID: {$userId} from IP: " . $remoteAddr);
                        break;
                    }
                }
            }
        }

        if ($authenticated) {
            unset($_SESSION['pending_2fa_user_id']);
            $_SESSION['user_id'] = $user['id'];
            $_SESSION['username'] = $user['username'];
            $_SESSION['role'] = $user['role'] ?? 'user';
            
            // Optionally store language/preferences in session if your app uses them globally
            if (!empty($user['language'])) {
                $_SESSION['language'] = $user['language'];
            }

            $audit = $this->pdo->prepare("INSERT INTO audit_logs (user_id, action, details, ip_address) VALUES (?, 'LOGIN_SUCCESS_2FA', 'Completed 2FA login challenge', ?)");
            $audit->execute([$user['id'], $remoteAddr]);

            // Forward new users to onboarding wizard, otherwise go to data_entry
            if (!empty($user['is_new_user'])) {
                header('Location: ' . $basePath . '/user/onboarding');
            } else {
                $dest = function_exists('post_login_destination_path')
                    ? post_login_destination_path($this->pdo)
                    : ($basePath . '/data-entry');
                header('Location: ' . $dest);
            }
            exit;
        } else {
            http_response_code(403);
            error_log("Failed 2FA verification attempt for user ID: {$userId} from IP: " . $remoteAddr);
            $_SESSION['error'] = "Invalid verification code or backup code. Please try again.";
        }

        header('Location: ' . $basePath . '/user/verify-2fa');
        exit;
    }
}
