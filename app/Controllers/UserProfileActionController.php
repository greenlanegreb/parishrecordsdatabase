<?php
/**
 * MIGRATED FILE MAPPING
 * ---------------------
 * Original Old File: user/profile.php/user/actions/save_profile.php
 * Migrated Date: 2026-08-05 05:10:52
 */
declare(strict_types=1);

namespace App\Controllers;

use Exception;
use PDO;

class UserProfileActionController
{
    private PDO $pdo;

    public function __construct(PDO $pdo)
    {
        $this->pdo = $pdo;
    }

    public function handle(): void
    {
        $serverMethod = isset($_SERVER['REQUEST_METHOD']) && is_string($_SERVER['REQUEST_METHOD']) ? $_SERVER['REQUEST_METHOD'] : 'GET';
        if ($serverMethod !== 'POST') {
            http_response_code(405);
            exit('Method Not Allowed');
        }

        verify_csrf_token();
        /** @var array{id: int|string, username: string, email: string, two_fa_enabled?: int|string} $currentUser */
        $currentUser = require_permission($this->pdo, 'access_profile', 'Allows viewing and managing personal user profile and security settings');
        $userId = $currentUser['id'];

        $basePath = defined('BASE_PATH') ? rtrim(BASE_PATH, '/') : '';
        $post = $_POST;
        $action = isset($post['action']) && is_string($post['action']) ? $post['action'] : '';
        $remoteAddr = isset($_SERVER['REMOTE_ADDR']) && is_string($_SERVER['REMOTE_ADDR']) ? $_SERVER['REMOTE_ADDR'] : '127.0.0.1';

        // 0. Handle Personal Details Update Request
        if ($action === 'update_personal_details') {
            $firstName = isset($post['first_name']) && is_string($post['first_name']) ? trim($post['first_name']) : '';
            $surname = isset($post['surname']) && is_string($post['surname']) ? trim($post['surname']) : '';
            $displayMode = isset($post['attribution_display_mode']) && is_string($post['attribution_display_mode']) ? trim($post['attribution_display_mode']) : 'initials_random';
            $timezone = isset($post['timezone']) && is_string($post['timezone']) ? trim($post['timezone']) : 'UTC';
            $dateFormat = isset($post['date_format']) && is_string($post['date_format']) ? trim($post['date_format']) : 'd/m/Y';
            $timeFormat = isset($post['time_format']) && is_string($post['time_format']) ? trim($post['time_format']) : '24';
            
            $rawLang = isset($post['language']) && is_string($post['language']) ? strtolower(trim($post['language'])) : '';
            $language = preg_replace('/[^a-z_]/', '', $rawLang) ?? '';

            $allowedModes = ['full_name', 'volunteers_only', 'initials_random'];
            if (!in_array($displayMode, $allowedModes, true)) {
                $displayMode = 'initials_random';
            }

            $validTimezones = timezone_identifiers_list();
            if (!in_array($timezone, $validTimezones, true)) {
                $timezone = 'UTC';
            }

            $allowedDateFormats = ['d/m/Y', 'd/m/y', 'd.m.Y', 'm/d/Y', 'l j F Y'];
            if (!in_array($dateFormat, $allowedDateFormats, true)) {
                $dateFormat = 'd/m/Y';
            }

            $allowedTimeFormats = ['12', '24', 'none'];
            if (!in_array($timeFormat, $allowedTimeFormats, true)) {
                $timeFormat = '24';
            }

            if ($language !== '') {
                $langFile = __DIR__ . '/../../lang/' . $language . '.php';
                if (!is_file($langFile)) {
                    $language = '';
                }
            }
            $languageDb = ($language === '') ? null : $language;

            $updName = $this->pdo->prepare("UPDATE users SET first_name = ?, surname = ?, attribution_display_mode = ?, timezone = ?, date_format = ?, time_format = ?, language = ? WHERE id = ?");
            if ($updName->execute([$firstName, $surname, $displayMode, $timezone, $dateFormat, $timeFormat, $languageDb, $userId])) {
                if ($languageDb !== null && function_exists('set_language')) {
                    set_language($languageDb);
                }
                $_SESSION['message'] = "Personal details, timezone, and format settings updated successfully!";
            } else {
                http_response_code(403);
                error_log("Database error during personal details update for user ID: {$userId} from IP: " . $remoteAddr);
                $_SESSION['error'] = "Failed to update personal details.";
            }
        }
        // 1. Handle Email Update Request
        elseif ($action === 'update_email') {
            $newEmail = isset($post['email']) && is_string($post['email']) ? trim($post['email']) : '';
            if ($newEmail === '' || !filter_var($newEmail, FILTER_VALIDATE_EMAIL)) {
                http_response_code(403);
                error_log("Failed email update attempt (invalid format) for user ID: {$userId} from IP: " . $remoteAddr);
                $_SESSION['error'] = "Please provide a valid email address.";
            } elseif ($newEmail === $currentUser['email']) {
                $_SESSION['error'] = "The new email address matches your current email.";
            } else {
                $chk = $this->pdo->prepare("SELECT id FROM users WHERE email = ? AND id != ?");
                $chk->execute([$newEmail, $userId]);
                if ($chk->fetch()) {
                    http_response_code(403);
                    error_log("Failed email update attempt (email already registered) for user ID: {$userId} from IP: " . $remoteAddr);
                    $_SESSION['error'] = "That email address is already registered to another account.";
                } else {
                    $token = bin2hex(random_bytes(32));
                    $expires = date('Y-m-d H:i:s', strtotime('+24 hours'));
                    $upd = $this->pdo->prepare("UPDATE users SET email = ?, email_verified = 0, invite_token = ?, invite_expires_at = ? WHERE id = ?");
                    if ($upd->execute([$newEmail, $token, $expires, $userId])) {
                        require_once __DIR__ . '/../../db/mail_helper.php';
                        if (send_user_invitation($this->pdo, $newEmail, $token)) {
                            $_SESSION['message'] = "Email updated successfully! A verification link has been sent to your new address.";
                        } else {
                            http_response_code(403);
                            error_log("Database email update succeeded but mail dispatch failed for user ID: {$userId} from IP: " . $remoteAddr);
                            $_SESSION['error'] = "Email updated in database, but failed to dispatch the verification message.";
                        }
                    } else {
                        http_response_code(403);
                        error_log("Database error during email update for user ID: {$userId} from IP: " . $remoteAddr);
                        $_SESSION['error'] = "Failed to update email address.";
                    }
                }
            }
        }
        // 2. Handle Password Update Request
        elseif ($action === 'update_password') {
            $currentPassword = isset($post['current_password']) && is_string($post['current_password']) ? $post['current_password'] : '';
            $newPassword = isset($post['new_password']) && is_string($post['new_password']) ? $post['new_password'] : '';
            $confirmPassword = isset($post['confirm_password']) && is_string($post['confirm_password']) ? $post['confirm_password'] : '';

            $hashStmt = $this->pdo->prepare("SELECT password_hash FROM users WHERE id = ?");
            $hashStmt->execute([$userId]);
            $passwordHash = $hashStmt->fetchColumn();

            if ($currentPassword === '' || $newPassword === '' || $confirmPassword === '') {
                $_SESSION['error'] = "All password fields are required.";
            } elseif ($passwordHash === false || $passwordHash === null || !password_verify($currentPassword, (string)$passwordHash)) {
                http_response_code(403);
                error_log("Failed password change attempt (incorrect current password) for user ID: {$userId} from IP: " . $remoteAddr);
                $_SESSION['error'] = "Your current password was incorrect.";
            } elseif ($newPassword !== $confirmPassword) {
                $_SESSION['error'] = "The new passwords do not match.";
            } elseif (strlen($newPassword) < 8) {
                $_SESSION['error'] = "New password must be at least 8 characters long.";
            } else {
                $newHash = password_hash($newPassword, PASSWORD_DEFAULT);
                $updPwd = $this->pdo->prepare("UPDATE users SET password_hash = ? WHERE id = ?");
                if ($updPwd->execute([$newHash, $userId])) {
                    $_SESSION['message'] = "Password updated successfully!";
                } else {
                    http_response_code(403);
                    error_log("Database error during password update for user ID: {$userId} from IP: " . $remoteAddr);
                    $_SESSION['error'] = "Failed to update password.";
                }
            }
        }
        // 3. Handle Generate New Backup Codes Request
        elseif ($action === 'generate_backup_codes') {
            if (empty($currentUser['two_fa_enabled'])) {
                $_SESSION['error'] = "You must enable 2FA before generating backup codes.";
            } else {
                /** @array<int, string> $rawCodes */
                $rawCodes = [];
                /** @array<int, string> $hashedCodes */
                $hashedCodes = [];
                for ($i = 0; $i < 5; $i++) {
                    $code = strtoupper(bin2hex(random_bytes(3)));
                    $formattedCode = substr($code, 0, 3) . '-' . substr($code, 3, 3);
                    $rawCodes[] = $formattedCode;
                    $hashedCodes[] = password_hash($formattedCode, PASSWORD_DEFAULT);
                }

                $hashedCodesJson = json_encode($hashedCodes);
                $updCodes = $this->pdo->prepare("UPDATE users SET backup_codes = ? WHERE id = ?");
                if ($updCodes->execute([$hashedCodesJson, $userId])) {
                    $_SESSION['new_raw_backup_codes'] = $rawCodes;
                    $_SESSION['message'] = "New backup codes generated successfully! Please download and save them immediately.";
                } else {
                    http_response_code(403);
                    error_log("Database error generating backup codes for user ID: {$userId} from IP: " . $remoteAddr);
                    $_SESSION['error'] = "Failed to generate new backup codes.";
                }
            }
        }
        // 4. Handle 2FA Setup Initiation
        elseif ($action === 'setup_2fa') {
            header('Location: ' . $basePath . '/setup-2fa');
            exit;
        }

        header('Location: ' . $basePath . '/profile');
        exit;
    }
}
