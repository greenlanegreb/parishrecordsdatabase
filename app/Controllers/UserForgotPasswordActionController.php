<?php
/**
 * MIGRATED FILE MAPPING
 * ---------------------
 * Original Old File: user/forgot_password.php/user/actions/save_forgot_password.php
 * Migrated Date: 2026-08-05 04:55:09
 */
declare(strict_types=1);

namespace App\Controllers;

use Exception;
use PDO;

class UserForgotPasswordActionController
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
        
        $basePath = defined('BASE_PATH') ? rtrim(BASE_PATH, '/') : '';
        $post = $_POST;
        $email = isset($post['email']) && is_string($post['email']) ? trim($post['email']) : '';

        if ($email === '' || !filter_var($email, FILTER_VALIDATE_EMAIL)) {
            $_SESSION['error'] = "Please provide a valid email address.";
        } else {
            $stmt = $this->pdo->prepare("SELECT id, username, is_active FROM users WHERE email = ?");
            $stmt->execute([$email]);
            /** @var array{id: int|string, username: string, is_active: int|string}|false $user */
            $user = $stmt->fetch(PDO::FETCH_ASSOC);

            // To prevent user enumeration attacks, we show a generic success message even if the email isn't found,
            // but only dispatch the email if the user actually exists and is active.
            if ($user !== false && !empty($user['is_active'])) {
                $token = bin2hex(random_bytes(32));
                $expires = date('Y-m-d H:i:s', strtotime('+24 hours'));

                $upd = $this->pdo->prepare("UPDATE users SET reset_token = ?, reset_expires = ? WHERE id = ?");
                if ($upd->execute([$token, $expires, $user['id']])) {
                    // Send custom reset email
                    $systemName = get_system_name($this->pdo);
                    $subject = $systemName . " - Password Recovery Request";
                    
                    $serverHost = isset($_SERVER['HTTP_HOST']) && is_string($_SERVER['HTTP_HOST']) ? $_SERVER['HTTP_HOST'] : 'localhost';
                    $httpsOn = !empty($_SERVER['HTTPS']) && $_SERVER['HTTPS'] !== 'off';
                    $protocol = $httpsOn ? "https://" : "https://"; // Maintains original fallback pattern
                    $resetLink = $protocol . $serverHost . $basePath . "/user/set-password?token=" . $token;

                    $username = isset($user['username']) && is_string($user['username']) ? $user['username'] : 'User';
                    $messageBody = "Hello " . $username . ",\n\n" .
                                   "We received a request to reset your password for " . $systemName . ".\n" .
                                   "Click the link below to choose a new password:\n\n" .
                                   $resetLink . "\n\n" .
                                   "This link is valid for 24 hours. If you did not request a password reset, please ignore this email.\n";

                    require_once __DIR__ . '/../../db/mail_helper.php';
                    send_user_invitation(
                         $this->pdo,
                         $email,
                         $token,
                         [
                             'username'   => $username,
                             'first_name' => $username,
                             'surname'    => '',
                             'role_name'  => '',
                         ],
                         'password_reset',
                         $subject,
                         $messageBody
                   );
                }
            }

            $_SESSION['message'] = "If an account matches that email address, a password reset link has been dispatched.";
        }

        header('Location: ' . $basePath . '/forgot-password');
        exit;
    }
}
