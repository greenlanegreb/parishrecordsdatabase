<?php
/**
 * MIGRATED FILE MAPPING
 * ---------------------
 * Original Old File: admin/actions/save_user.php
 * Migrated Date: 2026-08-05 04:45:53
 */declare(strict_types=1);


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
        if (session_status() === PHP_SESSION_NONE) {
            session_start();
        }

        if (!is_module_enabled($this->pdo, 'users')) {
            http_response_code(403);
            exit('403 Forbidden: The User Management module is currently disabled.');
        }

        $serverMethod = isset($_SERVER['REQUEST_METHOD']) && is_string($_SERVER['REQUEST_METHOD']) ? $_SERVER['REQUEST_METHOD'] : 'GET';
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
        $requestedRoleId = isset($post['role_id']) ? (int)$post['role_id'] : 0;
        $volunteerId = isset($post['volunteer_id']) ? (int)$post['volunteer_id'] : 0;
        $remoteAddr = isset($_SERVER['REMOTE_ADDR']) && is_string($_SERVER['REMOTE_ADDR']) ? $_SERVER['REMOTE_ADDR'] : '127.0.0.1';

        $roleCheck = $this->pdo->prepare("SELECT id, role_name FROM roles WHERE id = ?");
        $roleCheck->execute([$requestedRoleId]);
        /** @var array{id: int|string, role_name: string}|false $validRole */
        $validRole = $roleCheck->fetch(PDO::FETCH_ASSOC);

        $roleId = ($validRole !== false) ? (int)$validRole['id'] : 2;
        $roleName = ($validRole !== false && isset($validRole['role_name'])) ? $validRole['role_name'] : 'user';

        if ($email === '') {
            $_SESSION['error'] = "Email address is a required field.";
            header('Location: /admin/users');
            exit;
        }
        if (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
            $_SESSION['error'] = "Invalid email format.";
            header('Location: /admin/users');
            exit;
        }

        $chkEmail = $this->pdo->prepare("SELECT id FROM users WHERE email = ?");
        $chkEmail->execute([$email]);
        if ($chkEmail->fetch()) {
            $_SESSION['error'] = "A user account with that email address already exists.";
            header('Location: /admin/users');
            exit;
        }

        // Secure Username Determination with Rate-Limiting Protection
        $username = '';
        if ($requestedUsername !== '') {
            // 1. Log this check attempt immediately to the database audit trail for rate limiting
            log_username_check_attempt($this->pdo);

            // 2. Check if this IP has exceeded the 24-hour rate limit (3 attempts max)
            if (has_exceeded_username_check_limit($this->pdo)) {
                $_SESSION['error'] = "Username availability check limit reached (max 3 per 24 hours). A unique username has been automatically allocated instead.";
            } else {
                $sanitized = preg_replace('/[^a-zA-Z0-9_\-]/', '', $requestedUsername) ?? '';
                
                $chkUser = $this->pdo->prepare("SELECT id FROM users WHERE username = ?");
                $chkUser->execute([$sanitized]);
                if ($chkUser->fetch()) {
                    $_SESSION['error'] = "The username '{$sanitized}' is already taken. A unique username has been automatically allocated instead.";
                } else {
                    $username = $sanitized; // Custom username is valid and available!
                }
            }
        }

        // Fallback: Auto-generate unique username if none provided or if custom check failed/rate-limited
        if ($username === '') {
            $cleanedFirst = preg_replace('/[^a-zA-Z]/', '', $firstName) ?? '';
            $cleanedSurname = preg_replace('/[^a-zA-Z]/', '', $surname) ?? '';
            $base = strtolower(substr($cleanedFirst, 0, 1) . $cleanedSurname);
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
            
            // Append auto-allocated notice if they originally tried a taken/limited name
            if ($requestedUsername !== '' && empty($_SESSION['error'])) {
                $_SESSION['error'] = "The username you requested was unavailable. Username '{$username}' was automatically allocated.";
            }
        }

        $token = bin2hex(random_bytes(32));
        $expires = date('Y-m-d H:i:s', strtotime('+24 hours'));

        try {
            require_once __DIR__ . '/../../db/mail_helper.php';

            $ins = $this->pdo->prepare("INSERT INTO users (username, first_name, surname, email, password_hash, role_id, invite_token, invite_expires_at) VALUES (?, ?, ?, ?, ?, ?, ?, ?)");

            if ($ins->execute([$username, $firstName, $surname, $email, '', $roleId, $token, $expires])) {
                $userDetails = [
                    'first_name' => $firstName,
                    'surname'    => $surname,
                    'username'   => $username,
                    'role_name'  => ucwords($roleName)
                ];

                if (send_user_invitation($this->pdo, $email, $token, $userDetails)) {
                    if ($volunteerId > 0) {
                        $upVol = $this->pdo->prepare("UPDATE volunteer_submissions SET status = 'Accepted' WHERE id = ?");
                        $upVol->execute([$volunteerId]);
                    }

                    if (empty($_SESSION['message'])) {
                        $_SESSION['message'] = "User '{$username}' created successfully and invitation email sent!";
                    }
                    
                    $audit = $this->pdo->prepare("INSERT INTO audit_logs (user_id, action, details, ip_address) VALUES (?, 'CREATE_USER', ?, ?)");
                    $audit->execute([$currentUser['id'], "Created user account ({$roleName}) with username: {$username}", $remoteAddr]);
                } else {
                    $_SESSION['error'] = "User account created in database, but failed to dispatch the invitation email.";
                }
            } else {
                $_SESSION['error'] = "Database insertion failed.";
            }
        } catch (Exception $e) {
            $_SESSION['error'] = "Database error: " . $e->getMessage();
        }

        header('Location: /admin/users');
        exit;
    }
}
