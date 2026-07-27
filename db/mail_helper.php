<?php
// db/mail_helper.php - Hybrid mailer supporting local Postfix or PHPMailer SMTP

// Require the lightweight core PHPMailer files directly
require_once __DIR__ . '/../includes/phpmailer/Exception.php';
require_once __DIR__ . '/../includes/phpmailer/PHPMailer.php';
require_once __DIR__ . '/../includes/phpmailer/SMTP.php';

use PHPMailer\PHPMailer\PHPMailer;
use PHPMailer\PHPMailer\SMTP;
use PHPMailer\PHPMailer\Exception;

function send_user_invitation($pdo, $to_email, $reset_token, $custom_subject = null, $custom_body = null) {
    $system_name = get_system_name($pdo);
    
    // 1. Fetch configured mail settings from site_settings with safe fallbacks
    $mail_driver = 'mail'; // Default to native mail (Postfix)
    $mail_domain = 'deballiolsociety.org.uk';
    $smtp_host = '';
    $smtp_port = 587;
    $smtp_user = '';
    $smtp_pass = '';
    $smtp_encryption = 'tls';

    try {
        $stmt = $pdo->query("SELECT setting_key, setting_value FROM site_settings WHERE setting_key IN ('mail_driver', 'mail_domain', 'smtp_host', 'smtp_port', 'smtp_user', 'smtp_pass', 'smtp_encryption')");
        while ($row = $stmt->fetch(PDO::FETCH_ASSOC)) {
            switch ($row['setting_key']) {
                case 'mail_driver': $mail_driver = trim($row['setting_value']); break;
                case 'mail_domain': $mail_domain = trim($row['setting_value']); break;
                case 'smtp_host': $smtp_host = trim($row['setting_value']); break;
                case 'smtp_port': $smtp_port = intval($row['setting_value']); break;
                case 'smtp_user': $smtp_user = trim($row['setting_value']); break;
                case 'smtp_pass': $smtp_pass = $row['setting_value']; break;
                case 'smtp_encryption': $smtp_encryption = trim($row['setting_value']); break;
            }
        }
    } catch (Exception $e) {
        // Fallbacks apply if table columns are missing
    }
    
    // 2. Link URL configuration using dynamic BASE_PATH
    $host = $_SERVER['HTTP_HOST'] ?? $mail_domain;
    $protocol = (!empty($_SERVER['HTTPS']) && $_SERVER['HTTPS'] !== 'off') ? "https://" : "https://";
    $base_path = defined('BASE_PATH') ? BASE_PATH : '';
    
    $setup_link = $protocol . $host . $base_path . "/user/set_password.php?token=" . $reset_token;
    
    // 3. Determine subject and body (use defaults if custom ones aren't provided)
    $subject = $custom_subject ?? ($system_name . " - Account Invitation");
    
    if ($custom_body !== null) {
        $message_body = $custom_body;
    } else {
        $message_body = "Hello,\n\n" .
                        "An account has been created for you on the " . $system_name . ".\n" .
                        "Please click the link below to set your password and activate your account:\n\n" .
                        $setup_link . "\n\n" .
                        "This link is valid for 24 hours.\n\n" .
                        "If you did not expect this invitation, please contact the site administrator.\n";
    }

    $from_email = "no-reply@" . $mail_domain;

    // 4. Handle PHPMailer SMTP Client if selected by admin
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
            // Fall back gracefully to native mail if SMTP connection fails
            error_log("PHPMailer Error: {$mail->ErrorInfo}. Falling back to Postfix mail().");
        }
    }

    // 5. Default Native Mail / Postfix Relay approach (Zero-config fallback)
    $headers = "From: " . $from_email . "\r\n" .
               "Reply-To: " . $from_email . "\r\n" .
               "X-Mailer: PHP/" . phpversion();

    $envelope_sender = "-f" . $from_email;

    return mail($to_email, $subject, $message_body, $headers, $envelope_sender);
}
?>
