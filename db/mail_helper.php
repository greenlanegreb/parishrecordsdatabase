<?php
// db/mail_helper.php - Hybrid mailer supporting local Postfix or PHPMailer SMTP with database email template parsing

// Require the lightweight core PHPMailer files directly
require_once __DIR__ . '/../includes/phpmailer/Exception.php';
require_once __DIR__ . '/../includes/phpmailer/PHPMailer.php';
require_once __DIR__ . '/../includes/phpmailer/SMTP.php';

use PHPMailer\PHPMailer\PHPMailer;
use PHPMailer\PHPMailer\SMTP;
use PHPMailer\PHPMailer\Exception;

function send_user_invitation($pdo, $to_email, $reset_token, array $user_details = [], string $trigger_event = 'user_invitation', $custom_subject = null, $custom_body = null) {
    $system_name = get_system_name($pdo);
    
    // 1. Fetch template from database if custom ones aren't explicitly passed
    $subject_tmpl = $custom_subject;
    $body_tmpl = $custom_body;

    if (empty($subject_tmpl) || empty($body_tmpl)) {
        try {
            $stmt = $pdo->prepare("SELECT subject, body FROM user_email_templates WHERE trigger_event = ?");
            $stmt->execute([$trigger_event]);
            $tmpl = $stmt->fetch(PDO::FETCH_ASSOC);
            if ($tmpl) {
                $subject_tmpl = $subject_tmpl ?: $tmpl['subject'];
                $body_tmpl = $body_tmpl ?: $tmpl['body'];
            }
        } catch (Exception $e) {
            // Fallback if table doesn't exist yet
        }
    }

    // Fallbacks if database row is completely missing
    $subject_tmpl = $subject_tmpl ?? ($system_name . " - Account Access");
    $body_tmpl = $body_tmpl ?? "Hello {first_name},\n\nPlease use this link to access your account: {invite_link}";

    // 2. Link URL configuration using dynamic BASE_PATH
    $host = $_SERVER['HTTP_HOST'] ?? 'localhost';
    $protocol = (!empty($_SERVER['HTTPS']) && $_SERVER['HTTPS'] !== 'off') ? "https://" : "https://";
    $base_path = defined('BASE_PATH') ? BASE_PATH : '';
    
    $setup_link = $protocol . $host . $base_path . "/user/set_password.php?token=" . $reset_token;

    // 3. Define placeholder replacements
    $replacements = [
        '{system_name}' => $system_name,
        '{invite_link}' => $setup_link,
        '{first_name}'  => $user_details['first_name'] ?? ($user_details['username'] ?? 'User'),
        '{surname}'     => $user_details['surname'] ?? '',
        '{username}'    => $user_details['username'] ?? 'User',
        '{email}'       => $to_email,
        '{role_name}'   => $user_details['role_name'] ?? 'User'
    ];

    $subject = str_replace(array_keys($replacements), array_values($replacements), $subject_tmpl);
    $message_body = str_replace(array_keys($replacements), array_values($replacements), $body_tmpl);

    // 4. Fetch configured mail settings from site_settings
    $mail_driver = 'mail'; // Default to native mail (Postfix)
    $mail_domain = '';
    $mail_from   = '';
    $smtp_host = '';
    $smtp_port = 587;
    $smtp_user = '';
    $smtp_pass = '';
    $smtp_encryption = 'tls';

    try {
        $stmt = $pdo->query("SELECT setting_key, setting_value FROM site_settings WHERE setting_key IN ('mail_driver', 'mail_domain', 'mail_from', 'smtp_host', 'smtp_port', 'smtp_user', 'smtp_pass', 'smtp_encryption')");
        while ($row = $stmt->fetch(PDO::FETCH_ASSOC)) {
            switch ($row['setting_key']) {
                case 'mail_driver': $mail_driver = trim($row['setting_value']); break;
                case 'mail_domain': $mail_domain = trim($row['setting_value']); break;
                case 'mail_from': $mail_from = trim($row['setting_value']); break;
                case 'smtp_host': $smtp_host = trim($row['setting_value']); break;
                case 'smtp_port': $smtp_port = intval($row['setting_value']); break;
                case 'smtp_user': $smtp_user = trim($row['setting_value']); break;
                case 'smtp_pass': $smtp_pass = $row['setting_value']; break;
                case 'smtp_encryption': $smtp_encryption = trim($row['setting_value']); break;
            }
        }
    } catch (Exception $e) {}

    // 5. Strict From Address resolution
    $from_email = !empty($mail_from) ? $mail_from : (!empty($mail_domain) ? ("no-reply@" . $mail_domain) : '');

    if (empty($from_email)) {
        error_log("Mail Error: No 'From' email or mail domain configured in site settings.");
        return false;
    }

    // 6. Handle PHPMailer SMTP Client if selected by admin
    if ($mail_driver === 'smtp' && !empty($smtp_host)) {
        try {
            $mail = new PHPMailer(true);
            $mail->isSMTP();
            $mail->Host       = $smtp_host;
            $mail->SMTPAuth   = true;
            $mail->Username   = $smtp_user;
            $mail->Password   = $smtp_pass;
            
            if ($smtp_encryption === 'ssl') {
                $mail->SMTPSecure = PHPMailer::ENCRYPTION_SMTPS;
            } else {
                $mail->SMTPSecure = PHPMailer::ENCRYPTION_STARTTLS;
            }
            
            $mail->Port       = $smtp_port;

            // Headers & Recipients
            $mail->setFrom($from_email, $system_name);
            $mail->addAddress($to_email);
            $mail->addReplyTo($from_email, $system_name);
            
            $mail->Subject = $subject;
            $mail->Body    = $message_body;

            return $mail->send();
        } catch (Exception $e) {
            error_log("PHPMailer Error: {$mail->ErrorInfo}. Falling back to Postfix mail().");
        }
    }

    // 7. Default Native Mail / Postfix Relay approach
    $headers = "From: " . $from_email . "\r\n" .
               "Reply-To: " . $from_email . "\r\n" .
               "X-Mailer: PHP/" . phpversion();

    $envelope_sender = "-f" . $from_email;

    return mail($to_email, $subject, $message_body, $headers, $envelope_sender);
}
