<?php
/**
 * MIGRATED FILE MAPPING
 * ---------------------
 * Original Old File: user/register.php/user/actions/save_register.php
 * Migrated Date: 2026-08-05 05:14:47
 */declare(strict_types=1);


namespace App\Controllers;

use PDO;

class UserRegisterActionController
{
    private PDO $pdo;

    public function __construct(PDO $pdo)
    {
        $this->pdo = $pdo;
    }

    public function register(): void
    {
        if (session_status() === PHP_SESSION_NONE) {
            session_start();
        }

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

        $post = $_POST;
        $username = isset($post['username']) && is_string($post['username']) ? trim($post['username']) : '';
        $email = isset($post['email']) && is_string($post['email']) ? trim($post['email']) : '';
        $password = isset($post['password']) && is_string($post['password']) ? $post['password'] : '';

        if ($username === '' || $email === '' || $password === '') {
            $_SESSION['error'] = "All fields are required.";
        } elseif (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
            $_SESSION['error'] = "Invalid email format.";
        } else {
            $stmt = $this->pdo->prepare("SELECT id FROM users WHERE username = ? OR email = ?");
            $stmt->execute([$username, $email]);
            
            if ($stmt->rowCount() > 0) {
                $_SESSION['error'] = "Username or email is already registered.";
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

        header('Location: /user/register.php');
        exit;
    }
}
