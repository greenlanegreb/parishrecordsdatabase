<?php
/**
 * MIGRATED FILE MAPPING
 * ---------------------
 * Original Old File: user/profile.php/user/actions/save_profile.php
 * Migrated Date: 2026-08-05 05:10:52
 *
 * Personal details via includes/user_preferences.php (shared with onboarding).
 * Route is /profile (not /user/profile).
 */
declare(strict_types=1);

namespace App\Controllers;

use PDO;

require_once dirname(__DIR__, 2) . '/includes/form_fields.php';


class UserProfileActionController
{
    private PDO $pdo;

    public function __construct(PDO $pdo)
    {
        $this->pdo = $pdo;
    }

    public function handle(): void
    {
        $serverMethod = isset($_SERVER['REQUEST_METHOD']) && is_string($_SERVER['REQUEST_METHOD'])
            ? $_SERVER['REQUEST_METHOD'] : 'GET';
        if ($serverMethod !== 'POST') {
            http_response_code(405);
            exit('Method Not Allowed');
        }

        \verify_csrf_token();
        /** @var array{id: int|string, username: string, email: string, two_fa_enabled?: int|string} $currentUser */
        $currentUser = \require_permission(
            $this->pdo,
            'access_profile',
            'Allows viewing and managing personal user profile and security settings'
        );
        $userId = $currentUser['id'];

        $basePath = defined('BASE_PATH') ? rtrim((string) BASE_PATH, '/') : '';
        $post = $_POST;
        $action = isset($post['action']) && is_string($post['action']) ? $post['action'] : '';
        $remoteAddr = isset($_SERVER['REMOTE_ADDR']) && is_string($_SERVER['REMOTE_ADDR'])
            ? $_SERVER['REMOTE_ADDR'] : '127.0.0.1';

        // Shared personal-details helpers (function_exists guards — safe if also loaded elsewhere)
        require_once dirname(__DIR__, 2) . '/includes/user_preferences.php';

        // 0. Personal details (incl. language-only apply without losing draft)
        if ($action === 'update_personal_details') {
            $normalized = \user_normalize_personal_details($post);
            $applyLangOnly = isset($post['apply_language']) && (string) $post['apply_language'] === '1';

            if ($applyLangOnly) {
                \user_store_personal_draft('profile_personal_draft', $normalized);
                \user_apply_ui_language($normalized['language'], $this->pdo);
                header('Location: ' . $basePath . '/profile');
                exit;
            }

            $err = \user_validate_personal_details_required($normalized);
            if ($err !== '') {
                \user_store_personal_draft('profile_personal_draft', $normalized);
                $_SESSION['error'] = $err;
                $fn = trim((string)($normalized['first_name'] ?? ''));
                $sn = trim((string)($normalized['surname'] ?? ''));
                if ($fn === '') { remember_field_error('first_name', $err); }
                if ($sn === '') { remember_field_error('surname', $err); }
                header('Location: ' . $basePath . '/profile');
                exit;
            }

            if (\user_save_personal_details($this->pdo, $userId, $normalized, false)) {
                unset($_SESSION['profile_personal_draft']);
                \user_apply_ui_language($normalized['language'], $this->pdo);
                $_SESSION['message'] = \function_exists('__')
                    ? __('profile.msg_personal_updated')
                    : 'Personal details, timezone, and format settings updated successfully!';
            } else {
                http_response_code(403);
                error_log('Database error during personal details update for user ID: ' . $userId . ' from IP: ' . $remoteAddr);
                $_SESSION['error'] = \function_exists('__')
                    ? __('profile.err_personal_update')
                    : 'Failed to update personal details.';
            }
            header('Location: ' . $basePath . '/profile');
            exit;
        }

        // 1. Email update
        if ($action === 'update_email') {
            $newEmail = isset($post['email']) && is_string($post['email']) ? trim($post['email']) : '';
            if ($newEmail === '' || !filter_var($newEmail, FILTER_VALIDATE_EMAIL)) {
                http_response_code(403);
                error_log("Failed email update attempt (invalid format) for user ID: {$userId} from IP: " . $remoteAddr);
                $_SESSION['error'] = 'Please provide a valid email address.';
                remember_field_error('email', $_SESSION['error']);
            } elseif ($newEmail === $currentUser['email']) {
                $_SESSION['error'] = 'The new email address matches your current email.';
                remember_field_error('email', $_SESSION['error']);
            } else {
                $chk = $this->pdo->prepare('SELECT id FROM users WHERE email = ? AND id != ?');
                $chk->execute([$newEmail, $userId]);
                if ($chk->fetch()) {
                    http_response_code(403);
                    error_log("Failed email update attempt (email already registered) for user ID: {$userId} from IP: " . $remoteAddr);
                    $_SESSION['error'] = 'That email address is already registered to another account.';
                remember_field_error('email', $_SESSION['error']);
                } else {
                    $token = bin2hex(random_bytes(32));
                    $expires = date('Y-m-d H:i:s', strtotime('+24 hours'));
                    $upd = $this->pdo->prepare(
                        'UPDATE users SET email = ?, email_verified = 0, invite_token = ?, invite_expires_at = ? WHERE id = ?'
                    );
                    if ($upd->execute([$newEmail, $token, $expires, $userId])) {
                        require_once __DIR__ . '/../../db/mail_helper.php';
                        if (send_user_invitation($this->pdo, $newEmail, $token)) {
                            $_SESSION['message'] = 'Email updated successfully! A verification link has been sent to your new address.';
                        } else {
                            http_response_code(403);
                            error_log("Database email update succeeded but mail dispatch failed for user ID: {$userId} from IP: " . $remoteAddr);
                            $_SESSION['error'] = 'Email updated in database, but failed to dispatch the verification message.';
                        }
                    } else {
                        http_response_code(403);
                        error_log("Database error during email update for user ID: {$userId} from IP: " . $remoteAddr);
                        $_SESSION['error'] = 'Failed to update email address.';
                    }
                }
            }
            header('Location: ' . $basePath . '/profile');
            exit;
        }

        // 2. Password update
        if ($action === 'update_password') {
            $currentPassword = isset($post['current_password']) && is_string($post['current_password']) ? $post['current_password'] : '';
            $newPassword = isset($post['new_password']) && is_string($post['new_password']) ? $post['new_password'] : '';
            $confirmPassword = isset($post['confirm_password']) && is_string($post['confirm_password']) ? $post['confirm_password'] : '';

            $hashStmt = $this->pdo->prepare('SELECT password_hash FROM users WHERE id = ?');
            $hashStmt->execute([$userId]);
            $passwordHash = $hashStmt->fetchColumn();

            if ($currentPassword === '' || $newPassword === '' || $confirmPassword === '') {
                $_SESSION['error'] = 'All password fields are required.';
                if ($currentPassword === '') { remember_field_error('current_password', $_SESSION['error']); }
                if ($newPassword === '') { remember_field_error('new_password', $_SESSION['error']); }
                if ($confirmPassword === '') { remember_field_error('confirm_password', $_SESSION['error']); }
            } elseif ($passwordHash === false || $passwordHash === null || !password_verify($currentPassword, (string) $passwordHash)) {
                http_response_code(403);
                error_log("Failed password change attempt (incorrect current password) for user ID: {$userId} from IP: " . $remoteAddr);
                $_SESSION['error'] = 'Your current password was incorrect.';
                remember_field_error('current_password', $_SESSION['error']);
            } elseif ($newPassword !== $confirmPassword) {
                $_SESSION['error'] = 'The new passwords do not match.';
                remember_field_error('new_password', $_SESSION['error']);
                remember_field_error('confirm_password', $_SESSION['error']);
            } elseif (strlen($newPassword) < 8) {
                $_SESSION['error'] = 'New password must be at least 8 characters long.';
                remember_field_error('new_password', $_SESSION['error']);
            } else {
                $newHash = password_hash($newPassword, PASSWORD_DEFAULT);
                $updPwd = $this->pdo->prepare('UPDATE users SET password_hash = ? WHERE id = ?');
                if ($updPwd->execute([$newHash, $userId])) {
                    $_SESSION['message'] = 'Password updated successfully!';
                } else {
                    http_response_code(403);
                    error_log("Database error during password update for user ID: {$userId} from IP: " . $remoteAddr);
                    $_SESSION['error'] = 'Failed to update password.';
                }
            }
            header('Location: ' . $basePath . '/profile');
            exit;
        }

        // 3. Generate backup codes
        if ($action === 'generate_backup_codes') {
            if (empty($currentUser['two_fa_enabled'])) {
                $_SESSION['error'] = 'You must enable 2FA before generating backup codes.';
            } else {
                $rawCodes = [];
                $hashedCodes = [];
                for ($i = 0; $i < 5; $i++) {
                    $code = strtoupper(bin2hex(random_bytes(3)));
                    $formattedCode = substr($code, 0, 3) . '-' . substr($code, 3, 3);
                    $rawCodes[] = $formattedCode;
                    $hashedCodes[] = password_hash($formattedCode, PASSWORD_DEFAULT);
                }
                $hashedCodesJson = json_encode($hashedCodes);
                $updCodes = $this->pdo->prepare('UPDATE users SET backup_codes = ? WHERE id = ?');
                if ($updCodes->execute([$hashedCodesJson, $userId])) {
                    $_SESSION['new_raw_backup_codes'] = $rawCodes;
                    $_SESSION['message'] = 'New backup codes generated successfully! Please download and save them immediately.';
                } else {
                    http_response_code(403);
                    error_log("Database error generating backup codes for user ID: {$userId} from IP: " . $remoteAddr);
                    $_SESSION['error'] = 'Failed to generate new backup codes.';
                }
            }
            header('Location: ' . $basePath . '/profile');
            exit;
        }

        // 4. 2FA setup
        if ($action === 'setup_2fa') {
            header('Location: ' . $basePath . '/setup-2fa');
            exit;
        }

        header('Location: ' . $basePath . '/profile');
        exit;
    }
}
