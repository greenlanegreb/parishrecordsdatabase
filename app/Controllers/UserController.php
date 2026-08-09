<?php
/**
 * MIGRATED FILE MAPPING
 * ---------------------
 * Original Old File: cat create_user.php/save_user.php
 * Migrated Date: 2026-08-04 09:24:11
 */
declare(strict_types=1);

namespace App\Controllers;

use PDO;

class UserController
{
    private PDO $pdo;

    public function __construct(PDO $pdo)
    {
        $this->pdo = $pdo;
    }

    public function create(): void
    {
        // 1. Module check
        $moduleCheck = $this->pdo->prepare("SELECT is_enabled FROM modules WHERE module_name = ?");
        $moduleCheck->execute(['users']);
        $moduleEnabled = $moduleCheck->fetchColumn();

        if (!$moduleEnabled) {
            http_response_code(403);
            exit('403 Forbidden: The User Management module is currently disabled.');
        }

        // Assume auth helper verifies and returns current admin user array
        /** @var array{id: int, username: string} $currentUser */
        $currentUser = require_admin_page($this->pdo, 'invite_users', 'Create and invite new users');

        $message = $_SESSION['message'] ?? '';
        $error = $_SESSION['error'] ?? '';
        unset($_SESSION['message'], $_SESSION['error']);

        // Prefill variables securely
        $get = $_GET;
        $prefillEmail = isset($get['email']) && is_string($get['email']) ? trim($get['email']) : '';
        $prefillFirst = isset($get['first_name']) && is_string($get['first_name']) ? trim($get['first_name']) : '';
        $prefillSurname = isset($get['surname']) && is_string($get['surname']) ? trim($get['surname']) : '';
        $volunteerId = isset($get['volunteer_id']) ? (int)$get['volunteer_id'] : 0;

        // Fetch roles dynamically
        $stmt = $this->pdo->query("SELECT id, role_name FROM roles ORDER BY id ASC");
        /** @var array<int, array{id: int, role_name: string}> $rolesList */
        $rolesList = $stmt !== false ? $stmt->fetchAll(PDO::FETCH_ASSOC) : [];

        // Render View
        require_once __DIR__ . '/../Views/admin/create_user.php';
    }

    public function store(): void
    {
        $moduleCheck = $this->pdo->prepare("SELECT is_enabled FROM modules WHERE module_name = ?");
        $moduleCheck->execute(['users']);
        if (!$moduleCheck->fetchColumn()) {
            http_response_code(403);
            exit('403 Forbidden: The User Management module is currently disabled.');
        }

        if (($_SERVER['REQUEST_METHOD'] ?? 'GET') !== 'POST') {
            http_response_code(405);
            exit('Method Not Allowed');
        }

        verify_csrf_token();
        /** @var array{id: int, username: string} $currentUser */
        $currentUser = require_permission($this->pdo, 'manage_users', 'Manage user accounts, roles, and status');

        $basePath = defined('BASE_PATH') ? rtrim(BASE_PATH, '/') : '';
        $post = $_POST;
        $firstName = isset($post['first_name']) && is_string($post['first_name']) ? trim($post['first_name']) : '';
        $surname = isset($post['surname']) && is_string($post['surname']) ? trim($post['surname']) : '';
        $requestedUsername = isset($post['username']) && is_string($post['username']) ? trim($post['username']) : '';
        $email = isset($post['email']) && is_string($post['email']) ? trim($post['email']) : '';
        $requestedRoleId = isset($post['role_id']) ? (int)$post['role_id'] : 0;
        $volunteerId = isset($post['volunteer_id']) ? (int)$post['volunteer_id'] : 0;

        $roleCheck = $this->pdo->prepare("SELECT id, role_name FROM roles WHERE id = ?");
        $roleCheck->execute([$requestedRoleId]);
        /** @var array{id: int, role_name: string}|false $validRole */
        $validRole = $roleCheck->fetch(PDO::FETCH_ASSOC);

        $roleId = $validRole ? $validRole['id'] : 2;
        $roleName = $validRole ? $validRole['role_name'] : 'user';

        if ($email === '') {
            $_SESSION['error'] = "Email address is a required field.";
            header('Location: ' . $basePath . '/admin/users');
            exit;
        }
        if (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
            $_SESSION['error'] = "Invalid email format.";
            header('Location: ' . $basePath . '/admin/users');
            exit;
        }

        $chkEmail = $this->pdo->prepare("SELECT id FROM users WHERE email = ?");
        $chkEmail->execute([$email]);
        if ($chkEmail->fetch()) {
            $_SESSION['error'] = "A user account with that email address already exists.";
            header('Location: ' . $basePath . '/admin/users');
            exit;
        }

        // Username verification & auto-allocation logic
        $username = '';
        if ($requestedUsername !== '') {
            log_username_check_attempt($this->pdo);

            if (has_exceeded_username_check_limit($this->pdo)) {
                $_SESSION['error'] = "Username availability check limit reached (max 3 per 24 hours). A unique username has been automatically allocated instead.";
            } else {
                $sanitized = preg_replace('/[^a-zA-Z0-9_\-]/', '', $requestedUsername);
                $sanitizedStr = is_string($sanitized) ? $sanitized : '';

                $chkUser = $this->pdo->prepare("SELECT id FROM users WHERE username = ?");
                $chkUser->execute([$sanitizedStr]);
                if ($chkUser->fetch()) {
                    $_SESSION['error'] = "The username '{$sanitizedStr}' is already taken. A unique username has been automatically allocated instead.";
                } else {
                    $username = $sanitizedStr;
                }
            }
        }

        if ($username === '') {
            $fnFiltered = preg_replace('/[^a-zA-Z]/', '', $firstName);
            $snFiltered = preg_replace('/[^a-zA-Z]/', '', $surname);
            $base = strtolower(substr(is_string($fnFiltered) ? $fnFiltered : '', 0, 1) . (is_string($snFiltered) ? $snFiltered : ''));
            if ($base === '') {
                $base = 'user';
            }

            $username = $base;
            $counter = 1;
            while (true) {
                $chkUser = $this->pdo->prepare("SELECT id FROM users WHERE username = ?");
                $chkUser->execute([$username]);
                if (!$chkUser->fetch()) {
                    break;
                }
                $username = $base . $counter;
                $counter++;
            }

            if ($requestedUsername !== '' && empty($_SESSION['error'])) {
                $_SESSION['error'] = "The username you requested was unavailable. Username '{$username}' was automatically allocated.";
            }
        }

        $token = bin2hex(random_bytes(32));
        $expires = date('Y-m-d H:i:s', strtotime('+24 hours'));

        $ins = $this->pdo->prepare("INSERT INTO users (username, first_name, surname, email, password_hash, role_id, invite_token, invite_expires_at) VALUES (?, ?, ?, ?, ?, ?, ?, ?)");

        if ($ins->execute([$username, $firstName, $surname, $email, '', $roleId, $token, $expires])) {
            $userDetails = [
                'first_name' => $firstName,
                'surname' => $surname,
                'username' => $username,
                'role_name' => ucwords($roleName)
            ];

            if (send_user_invitation($this->pdo, $email, $token, $userDetails)) {
                if ($volunteerId > 0) {
                    $upVol = $this->pdo->prepare("UPDATE volunteer_submissions SET status = 'Accepted' WHERE id = ?");
                    $upVol->execute([$volunteerId]);
                }

                if (empty($_SESSION['message'])) {
                    $_SESSION['message'] = "User '{$username}' created successfully and invitation email sent!";
                }

                $remoteAddr = isset($_SERVER['REMOTE_ADDR']) && is_string($_SERVER['REMOTE_ADDR']) ? $_SERVER['REMOTE_ADDR'] : '127.0.0.1';
                $audit = $this->pdo->prepare("INSERT INTO audit_logs (user_id, action, details, ip_address) VALUES (?, 'CREATE_USER', ?, ?)");
                $audit->execute([$currentUser['id'], "Created user account ({$roleName}) with username: {$username}", $remoteAddr]);
            } else {
                $_SESSION['error'] = "User account created in database, but failed to dispatch the invitation email.";
            }
        } else {
            $_SESSION['error'] = "Database insertion failed.";
        }

        header('Location: ' . $basePath . '/admin/users');
        exit;
    }
}
