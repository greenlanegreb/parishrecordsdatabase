<?php
/**
 * MIGRATED FILE MAPPING
 * ---------------------
 * Original Old File: admin/actions/save_user.php
 * Migrated Date: 2026-08-05 04:45:53
 */
declare(strict_types=1);

namespace App\Controllers;

use Exception;
use PDO;

class AdminUserActionController
{
    private PDO $pdo;

    public function __construct(PDO $pdo)
    {
        $this->pdo = $pdo;
    }

    public function create(): void
    {
        if (!is_module_enabled($this->pdo, 'users')) {
            http_response_code(403);
            exit('403 Forbidden: The User Management module is currently disabled.');
        }

        $serverMethod = isset($_SERVER['REQUEST_METHOD']) && is_string($_SERVER['REQUEST_METHOD'])
            ? $_SERVER['REQUEST_METHOD'] : 'GET';
        if ($serverMethod !== 'POST') {
            http_response_code(405);
            exit('Method Not Allowed');
        }

        verify_csrf_token();

        /** @var array{id: int, username: string} $currentUser */
        $currentUser = require_permission($this->pdo, 'manage_users', 'Manage user accounts, roles, and status');

        $post = $_POST;
        $firstName = isset($post['first_name']) && is_string($post['first_name']) ? trim($post['first_name']) : '';
        $surname = isset($post['surname']) && is_string($post['surname']) ? trim($post['surname']) : '';
        $requestedUsername = isset($post['username']) && is_string($post['username']) ? trim($post['username']) : '';
        $email = isset($post['email']) && is_string($post['email']) ? trim($post['email']) : '';
        $requestedRoleId = isset($post['role_id']) ? (int) $post['role_id'] : 0;
        $volunteerId = isset($post['volunteer_id']) ? (int) $post['volunteer_id'] : 0;
        $remoteAddr = isset($_SERVER['REMOTE_ADDR']) && is_string($_SERVER['REMOTE_ADDR'])
            ? $_SERVER['REMOTE_ADDR'] : '127.0.0.1';

        $roleCheck = $this->pdo->prepare('SELECT id, role_name FROM roles WHERE id = ?');
        $roleCheck->execute([$requestedRoleId]);
        /** @var array{id: int|string, role_name: string}|false $validRole */
        $validRole = $roleCheck->fetch(PDO::FETCH_ASSOC);

        $roleId = ($validRole !== false) ? (int) $validRole['id'] : 2;
        $roleName = ($validRole !== false && isset($validRole['role_name'])) ? $validRole['role_name'] : 'user';

        if ($email === '') {
            $_SESSION['error'] = 'Email address is a required field.';
            header('Location: ' . BASE_PATH . '/admin/users');
            exit;
        }

        if (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
            $_SESSION['error'] = 'Invalid email format.';
            header('Location: ' . BASE_PATH . '/admin/users');
            exit;
        }

        $chkEmail = $this->pdo->prepare('SELECT id FROM users WHERE email = ?');
        $chkEmail->execute([$email]);
        if ($chkEmail->fetch()) {
            $_SESSION['error'] = 'A user account with that email address already exists.';
            header('Location: ' . BASE_PATH . '/admin/users');
            exit;
        }

        // Admin path: uniqueness only — no public IP username-check rate limit
        // Live users + retired_usernames (never reuse deleted logins)
        $unameHelper = dirname(__DIR__, 2) . '/includes/username_check_helpers.php';
        if (is_file($unameHelper)) {
            require_once $unameHelper;
        }

        $username = '';
        if ($requestedUsername !== '') {
            $sanitized = preg_replace('/[^a-zA-Z0-9_\-]/', '', $requestedUsername) ?? '';
            if ($sanitized !== '') {
                if (function_exists('is_username_available') && is_username_available($this->pdo, $sanitized)) {
                    $username = $sanitized;
                } else {
                    $_SESSION['error'] = function_exists('__') && __('admin_users.err_username_taken') !== 'admin_users.err_username_taken'
                        ? __('admin_users.err_username_taken')
                        : "The username '{$sanitized}' is not available (in use or previously used). A unique username has been allocated instead.";
                }
            }
        }

        if ($username === '') {
            $username = function_exists('allocate_unique_username')
                ? allocate_unique_username($this->pdo, $firstName, $surname)
                : ('user' . bin2hex(random_bytes(3)));

            if ($requestedUsername !== '' && empty($_SESSION['error'])) {
                $_SESSION['error'] = function_exists('__') && __('admin_users.err_username_auto') !== 'admin_users.err_username_auto'
                    ? str_replace(':username', $username, __('admin_users.err_username_auto'))
                    : "The username you requested was unavailable. Username '{$username}' was automatically allocated.";
            }
        }

        $token = bin2hex(random_bytes(32));
        $expires = date('Y-m-d H:i:s', strtotime('+24 hours'));

        try {
            require_once __DIR__ . '/../../db/mail_helper.php';

            $ins = $this->pdo->prepare(
                'INSERT INTO users (username, first_name, surname, email, password_hash, role_id, invite_token, invite_expires_at)
                 VALUES (?, ?, ?, ?, ?, ?, ?, ?)'
            );
            if ($ins->execute([$username, $firstName, $surname, $email, '', $roleId, $token, $expires])) {
                $userDetails = [
                    'first_name' => $firstName,
                    'surname'    => $surname,
                    'username'   => $username,
                    'role_name'  => ucwords((string) $roleName),
                ];

                if (send_user_invitation($this->pdo, $email, $token, $userDetails)) {
                    if ($volunteerId > 0) {
                        $upVol = $this->pdo->prepare("UPDATE volunteer_submissions SET status = 'Accepted' WHERE id = ?");
                        $upVol->execute([$volunteerId]);
                    }
                    if (empty($_SESSION['message'])) {
                        $_SESSION['message'] = "User '{$username}' created successfully and invitation email sent!";
                    }

                    $audit = $this->pdo->prepare(
                        'INSERT INTO audit_logs (user_id, action, details, ip_address) VALUES (?, \'CREATE_USER\', ?, ?)'
                    );
                    $audit->execute([
                        $currentUser['id'],
                        "Created user account ({$roleName}) with username: {$username}",
                        $remoteAddr,
                    ]);
                } else {
                    $_SESSION['error'] = 'User account created in database, but failed to dispatch the invitation email.';
                }
            } else {
                $_SESSION['error'] = 'Database insertion failed.';
            }
        } catch (Exception $e) {
            $_SESSION['error'] = 'Database error: ' . $e->getMessage();
        }

        header('Location: ' . BASE_PATH . '/admin/users');
        exit;
    }
}
