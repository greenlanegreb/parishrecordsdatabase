<?php
/**
 * MIGRATED FILE MAPPING
 * ---------------------
 * Original Old File: user/register.php/user/actions/save_register.php
 * Migrated Date: 2026-08-05 05:14:47
 */
declare(strict_types=1);

namespace App\Controllers;

use PDO;

require_once dirname(__DIR__, 2) . '/includes/form_fields.php';


class UserRegisterActionController
{
    private PDO $pdo;

    public function __construct(PDO $pdo)
    {
        $this->pdo = $pdo;
    }

    public function register(): void
    {
        // Ensure the users module is enabled; otherwise block action execution
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

        $basePath = defined('BASE_PATH') ? rtrim(BASE_PATH, '/') : '';
        $post = $_POST;
        $username = isset($post['username']) && is_string($post['username']) ? trim($post['username']) : '';
        $email = isset($post['email']) && is_string($post['email']) ? trim($post['email']) : '';
        $password = isset($post['password']) && is_string($post['password']) ? $post['password'] : '';

        $_SESSION['old_username'] = $username;
        $_SESSION['old_email'] = $email;
        if ($username === '' || $email === '' || $password === '') {
            $_SESSION['error'] = "All fields are required.";
            if ($username === '') { remember_field_error('username', $_SESSION['error']); }
            if ($email === '') { remember_field_error('email', $_SESSION['error']); }
            if ($password === '') { remember_field_error('password', $_SESSION['error']); }
        } elseif (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
            $_SESSION['error'] = "Invalid email format.";
            remember_field_error('email', $_SESSION['error']);
        } else {
            $stmt = $this->pdo->prepare("SELECT id FROM users WHERE username = ? OR email = ?");
            $stmt->execute([$username, $email]);

            $usernameBlocked = false;
            $helper = dirname(__DIR__, 2) . '/includes/username_check_helpers.php';
            if (is_file($helper)) {
                require_once $helper;
            }
            if (function_exists('is_username_available') && !is_username_available($this->pdo, $username)) {
                $usernameBlocked = true;
            }

            if ($stmt->rowCount() > 0 || $usernameBlocked) {
                $_SESSION['error'] = $usernameBlocked && $stmt->rowCount() === 0
                    ? (function_exists('__') && __('register.err_username_retired') !== 'register.err_username_retired'
                        ? __('register.err_username_retired')
                        : 'That username is not available. Please choose another.')
                    : "Username or email is already registered.";
                remember_field_error('username', $_SESSION['error']);
                if ($stmt->rowCount() > 0) {
                    remember_field_error('email', $_SESSION['error']);
                }
            } else {
                $passwordHash = password_hash($password, PASSWORD_DEFAULT);
                $token = bin2hex(random_bytes(32));
                
                // PHP-generated timestamp to match application timezone consistency
                $expiresAt = date('Y-m-d H:i:s', strtotime('+24 hours'));

                $insert = $this->pdo->prepare("INSERT INTO users (username, email, password_hash, invite_token, invite_expires_at, is_active) VALUES (?, ?, ?, ?, ?, 1)");
                
                if ($insert->execute([$username, $email, $passwordHash, $token, $expiresAt])) {
                    require_once __DIR__ . '/../../db/mail_helper.php';
                    // Dispatch using mail_helper with $pdo as the first argument
                    if (send_user_invitation($this->pdo, $email, $token)) {
                        $_SESSION['message'] = "Registration successful! Please check your email to verify your account.";
                    } else {
                        $_SESSION['message'] = "Registration successful, but failed to send verification email.";
                    }
                    error_log("New user registered: {$username}. Token generated.");
                } else {
                    $_SESSION['error'] = "An error occurred during registration. Please try again.";
                }
            }
        }

        header('Location: ' . $basePath . '/register');
        exit;
    }
}
