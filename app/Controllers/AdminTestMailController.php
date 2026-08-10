<?php
/**
 * MIGRATED FILE MAPPING
 * ---------------------
 * Original Old File: admin/actions/test_mail.php
 * Migrated Date: 2026-08-05 04:43:53
 */
declare(strict_types=1);

namespace App\Controllers;

use Exception;
use PDO;

class AdminTestMailController
{
    private PDO $pdo;

    public function __construct(PDO $pdo)
    {
        $this->pdo = $pdo;
    }

    public function send(): void
    {
        $serverMethod = isset($_SERVER['REQUEST_METHOD']) && is_string($_SERVER['REQUEST_METHOD']) ? $_SERVER['REQUEST_METHOD'] : 'GET';
        if ($serverMethod !== 'POST') {
            http_response_code(405);
            exit('Method Not Allowed');
        }

        // Verify CSRF token and enforce dynamic permission check
        verify_csrf_token();
        /** @var array{id: int, username: string} $currentUser */
        $currentUser = require_permission($this->pdo, 'manage_settings', 'Manage global site settings, mail drivers, and maintenance mode');

        $post = $_POST;
        $testEmail = isset($post['test_email']) && is_string($post['test_email']) ? trim($post['test_email']) : '';
        $remoteAddr = isset($_SERVER['REMOTE_ADDR']) && is_string($_SERVER['REMOTE_ADDR']) ? $_SERVER['REMOTE_ADDR'] : '127.0.0.1';

        if (!filter_var($testEmail, FILTER_VALIDATE_EMAIL)) {
            $_SESSION['error'] = "Please provide a valid recipient email address for testing.";
            header('Location: ' . BASE_PATH . '/admin/settings#test-mail-section');
            exit;
        }

        $systemName = get_system_name($this->pdo);
        $subject = $systemName . " - Configuration Test Email";

        // Fetch mail settings cleanly from site_settings
        $mailDomain = '';
        $mailFrom = '';
        try {
            $stmt = $this->pdo->query("SELECT setting_key, setting_value FROM site_settings WHERE setting_key IN ('mail_domain', 'mail_from')");
            if ($stmt !== false) {
                while ($row = $stmt->fetch(PDO::FETCH_ASSOC)) {
                    $sKey = isset($row['setting_key']) ? (string)$row['setting_key'] : '';
                    $sVal = isset($row['setting_value']) && is_string($row['setting_value']) ? trim($row['setting_value']) : '';
                    if ($sKey === 'mail_domain') { $mailDomain = $sVal; }
                    if ($sKey === 'mail_from') { $mailFrom = $sVal; }
                }
            }
        } catch (Exception $e) {
            // Fallback gracefully
        }

        // Use configured mail_from, falling back to mail_domain if available
        $fromEmail = ($mailFrom !== '') ? $mailFrom : (($mailDomain !== '') ? ("no-reply@" . $mailDomain) : 'webmaster@localhost');

        $messageBody = "Hello,\n\n" .
                       "This is a diagnostic test email sent from the " . $systemName . " settings panel.\n" .
                       "If you are reading this, your mail configuration is working successfully!\n\n" .
                       "Timestamp: " . date('Y-m-d H:i:s') . "\n";

        $success = false;

        try {
            // Check which driver is active
            $mailDriver = 'mail';
            $stmt = $this->pdo->prepare("SELECT setting_value FROM site_settings WHERE setting_key = 'mail_driver'");
            $stmt->execute();
            $driverVal = $stmt->fetchColumn();
            if ($driverVal !== false && $driverVal !== null && is_string($driverVal)) {
                $mailDriver = trim($driverVal);
            }

            if ($mailDriver === 'smtp') {
                // Test via PHPMailer SMTP

                $mail = new PHPMailer\PHPMailer\PHPMailer(true);
                $mail->isSMTP();
                
                // Fetch SMTP configs cleanly
                /** @var array<string, string> $smtp */
                $smtp = [];
                $smtpStmt = $this->pdo->query("SELECT setting_key, setting_value FROM site_settings WHERE setting_key LIKE 'smtp_%'");
                if ($smtpStmt !== false) {
                    while ($row = $smtpStmt->fetch(PDO::FETCH_ASSOC)) {
                        $sKey = isset($row['setting_key']) ? (string)$row['setting_key'] : '';
                        $sVal = isset($row['setting_value']) && is_string($row['setting_value']) ? $row['setting_value'] : '';
                        $smtp[$sKey] = $sVal;
                    }
                }

                $mail->Host       = $smtp['smtp_host'] ?? '';
                $mail->SMTPAuth   = true;
                $mail->Username   = $smtp['smtp_user'] ?? '';
                $mail->Password   = $smtp['smtp_pass'] ?? '';
                $mail->SMTPSecure = (($smtp['smtp_encryption'] ?? 'tls') === 'ssl') ? PHPMailer\PHPMailer\PHPMailer::ENCRYPTION_SMTPS : PHPMailer\PHPMailer\PHPMailer::ENCRYPTION_STARTTLS;
                $mail->Port       = isset($smtp['smtp_port']) ? (int)$smtp['smtp_port'] : 587;

                $mail->setFrom($fromEmail, $systemName);
                $mail->addAddress($testEmail);
                $mail->addReplyTo($fromEmail, $systemName);
                
                $mail->Subject = $subject;
                $mail->Body    = $messageBody;

                $success = $mail->send();
            } else {
                // Test via Native Postfix mail()
                $headers = "From: " . $fromEmail . "\r\n" .
                           "Reply-To: " . $fromEmail . "\r\n" .
                           "X-Mailer: PHP/" . phpversion();
                $envelopeSender = "-f" . $fromEmail;
                $success = mail($testEmail, $subject, $messageBody, $headers, $envelopeSender);
            }
        } catch (Exception $e) {
            $_SESSION['error'] = "Test mail failed: " . $e->getMessage();
            header('Location: ' . BASE_PATH . '/admin/settings#test-mail-section');
            exit;
        }

        if ($success) {
            $_SESSION['message'] = "Test email successfully dispatched to " . htmlspecialchars($testEmail, ENT_QUOTES, 'UTF-8') . "!";
            
            $audit = $this->pdo->prepare("INSERT INTO audit_logs (user_id, action, details, ip_address) VALUES (?, 'TEST_MAIL', ?, ?)");
            $audit->execute([$currentUser['id'], "Dispatched test email successfully to: {$testEmail}", $remoteAddr]);
        } else {
            $_SESSION['error'] = "Failed to dispatch test email. Check your server logs or SMTP settings.";
        }

        header('Location: ' . BASE_PATH . '/admin/settings#test-mail-section');
        exit;
    }
}
