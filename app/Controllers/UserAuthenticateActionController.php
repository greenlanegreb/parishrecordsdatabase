<?php
/**
 * MIGRATED FILE MAPPING
 * ---------------------
 * Original Old File: user/login.php/user/actions/authenticate.php
 * Migrated Date: 2026-08-05 04:59:43
 *
 * Patch 1.1: onboarding → /user/onboarding, 2FA → /user/verify-2fa (match routes/web.php)
 */
declare(strict_types=1);

namespace App\Controllers;

use PDO;
use PDOException;

require_once dirname(__DIR__, 2) . '/includes/form_fields.php';

class UserAuthenticateActionController
{
    private PDO $pdo;

    public function __construct(PDO $pdo)
    {
        $this->pdo = $pdo;
    }

    public function authenticate(): void
    {
        $serverMethod = isset($_SERVER['REQUEST_METHOD']) && is_string($_SERVER['REQUEST_METHOD'])
            ? $_SERVER['REQUEST_METHOD'] : 'GET';
        if ($serverMethod !== 'POST') {
            http_response_code(405);
            exit('Method Not Allowed');
        }

        \verify_csrf_token();

        $post = $_POST;
        $username = isset($post['username']) && is_string($post['username']) ? trim($post['username']) : '';
        $password = isset($post['password']) && is_string($post['password']) ? $post['password'] : '';
        $_SESSION['old_username'] = $username;
        if ($username === '') {
            remember_field_error('username', function_exists('__') ? __('login.err_username_required') : 'Please enter your username.');
        }
        if ($password === '') {
            remember_field_error('password', function_exists('__') ? __('login.err_password_required') : 'Please enter your password.');
        }
        if ($username === '' || $password === '') {
            header('Location: ' . (defined('BASE_PATH') ? rtrim(BASE_PATH, '/') : '') . '/login');
            exit;
        }
        $remoteAddr = isset($_SERVER['REMOTE_ADDR']) && is_string($_SERVER['REMOTE_ADDR'])
            ? $_SERVER['REMOTE_ADDR'] : '127.0.0.1';

        $basePath = defined('BASE_PATH') ? rtrim(BASE_PATH, '/') : '';

        try {
            $stmt = $this->pdo->prepare(
                'SELECT id, username, password_hash, two_fa_enabled, google_2fa_secret, is_active, is_new_user
                 FROM users WHERE username = ? OR email = ?'
            );
            $stmt->execute([$username, $username]);
            /** @var array{id: int|string, username: string, password_hash: string, two_fa_enabled: int|string, google_2fa_secret?: string, is_active: int|string, is_new_user?: int|string}|false $user */
            $user = $stmt->fetch(PDO::FETCH_ASSOC);
        } catch (PDOException $e) {
            $errorCode = (string) $e->getCode();
            $errorMsg = $e->getMessage();
            if (
                in_array($errorCode, ['42S22', '42S02'], true)
                || str_contains($errorMsg, 'Unknown column')
                || str_contains($errorMsg, 'Base table or view not found')
            ) {
                $schemaCurrent = function_exists('get_schema_version') ? get_schema_version($this->pdo) : 0;
                $schemaLatest = $schemaCurrent;
                $migrationsDir = __DIR__ . '/../../db/migrations';

                if (is_dir($migrationsDir)) {
                    $migFiles = glob($migrationsDir . '/*.php');
                    if ($migFiles !== false) {
                        foreach ($migFiles as $migFile) {
                            $matches = [];
                            if (preg_match('/(\d+)_/', basename($migFile), $matches)) {
                                $schemaLatest = max($schemaLatest, (int) $matches[1]);
                            }
                        }
                    }
                }

                if ($schemaCurrent < $schemaLatest) {
                    header('Location: ' . $basePath . '/update-database');
                    exit;
                }
            }

            throw $e;
        }

        $isActive = $user !== false && !empty($user['is_active']);
        $passwordValid = $user !== false && password_verify($password, $user['password_hash']);

        if ($user !== false && $isActive && $passwordValid) {
            if (!empty($user['two_fa_enabled'])) {
                $_SESSION['pending_2fa_user_id'] = $user['id'];
                header('Location: ' . $basePath . '/user/verify-2fa');
                exit;
            }

            $_SESSION['user_id'] = $user['id'];
            $_SESSION['username'] = $user['username'];

            // Founding admin and invited users with is_new_user=1 go through onboarding
            if (!empty($user['is_new_user'])) {
                header('Location: ' . $basePath . '/user/onboarding');
            } else {
                $dest = function_exists('post_login_destination_path')
                    ? post_login_destination_path($this->pdo)
                    : ($basePath . '/data-entry');
                header('Location: ' . $dest);
            }
            exit;
        }

        error_log("Failed login attempt for user: '{$username}' from IP: " . $remoteAddr);
        http_response_code(403);
        $_SESSION['error'] = __('authenticate.err_invalid_credentials');
        remember_field_error('username', (string) $_SESSION['error']);
        remember_field_error('password', (string) $_SESSION['error']);
        header('Location: ' . $basePath . '/login');
        exit;
    }
}
