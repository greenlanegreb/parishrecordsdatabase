<?php
declare(strict_types=1);
/**
 * MIGRATED FILE MAPPING
 * ---------------------
 * Original Old File: db/mail_helper.php
 * Migrated Date: 2026-08-05 12:10:00
 */

// db/mail_helper.php - Hybrid mailer supporting local Postfix or PHPMailer SMTP with database email template parsing

use PHPMailer\PHPMailer\PHPMailer;
use PHPMailer\PHPMailer\Exception;

/**
 * Send an email (such as an invitation or notification) using DB templates and selected mail driver.
 *
 * @param \PDO $pdo
 * @param string $toEmail
 * @param string $resetToken
 * @param array<string, mixed> $userDetails
 * @param string $triggerEvent
 * @param string|null $customSubject
 * @param string|null $customBody
 * @return bool
 */
function send_user_invitation(
    \PDO $pdo, 
    string $toEmail, 
    string $resetToken, 
    array $userDetails = [], 
    string $triggerEvent = 'user_invitation', 
    ?string $customSubject = null, 
    ?string $customBody = null
): bool {
    $systemName = get_system_name($pdo);
    
    // 1. Fetch template from database if custom ones aren't explicitly passed
    $subjectTmpl = $customSubject;
    $bodyTmpl = $customBody;

    if ($subjectTmpl === null || $bodyTmpl === null || $subjectTmpl === '' || $bodyTmpl === '') {
        try {
            $stmt = $pdo->prepare("SELECT subject, body FROM user_email_templates WHERE trigger_event = ?");
            $stmt->execute([$triggerEvent]);
            /** @array{subject: string, body: string}|false $tmpl */
            $tmpl = $stmt->fetch(\PDO::FETCH_ASSOC);
            if (is_array($tmpl)) {
                $subjectTmpl = ($subjectTmpl !== null && $subjectTmpl !== '') ? $subjectTmpl : (isset($tmpl['subject']) && is_string($tmpl['subject']) ? $tmpl['subject'] : null);
                $bodyTmpl = ($bodyTmpl !== null && $bodyTmpl !== '') ? $bodyTmpl : (isset($tmpl['body']) && is_string($tmpl['body']) ? $tmpl['body'] : null);
            }
        } catch (\Exception $e) {
            // Fallback if table doesn't exist yet
        }
    }

    // Fallbacks if database row is completely missing
    $subjectTmpl = ($subjectTmpl !== null && $subjectTmpl !== '') ? $subjectTmpl : ($systemName . " - Account Access");
    $bodyTmpl = ($bodyTmpl !== null && $bodyTmpl !== '') ? $bodyTmpl : "Hello {first_name},\n\nPlease use this link to access your account: {invite_link}";

    // 2. Link URL configuration using dynamic BASE_PATH
    $httpHost = isset($_SERVER['HTTP_HOST']) && is_string($_SERVER['HTTP_HOST']) ? $_SERVER['HTTP_HOST'] : 'localhost';
    $isHttps = isset($_SERVER['HTTPS']) && is_string($_SERVER['HTTPS']) && $_SERVER['HTTPS'] !== 'off';
    $protocol = $isHttps ? "https://" : "https://";
    $basePath = defined('BASE_PATH') && is_string(BASE_PATH) ? BASE_PATH : '';
    
    $setupLink = $protocol . $httpHost . $basePath . "/user/set-password?token=" . $resetToken;

    $fName = isset($userDetails['first_name']) && is_string($userDetails['first_name']) ? $userDetails['first_name'] : (isset($userDetails['username']) && is_string($userDetails['username']) ? $userDetails['username'] : 'User');
    $sName = isset($userDetails['surname']) && is_string($userDetails['surname']) ? $userDetails['surname'] : '';
    $uName = isset($userDetails['username']) && is_string($userDetails['username']) ? $userDetails['username'] : 'User';
    $rName = isset($userDetails['role_name']) && is_string($userDetails['role_name']) ? $userDetails['role_name'] : 'user';
    $rl = dirname(__DIR__) . '/includes/role_labels.php';
    if (is_file($rl)) {
        require_once $rl;
    }
    if (function_exists('role_display_label')) {
        $rName = role_display_label($rName);
    }

    // 3. Define placeholder replacements
    $replacements = [
        '{system_name}' => $systemName,
        '{invite_link}' => $setupLink,
        '{first_name}'  => $fName,
        '{surname}'     => $sName,
        '{username}'    => $uName,
        '{email}'       => $toEmail,
        '{role_name}'   => $rName
    ];

    $subject = str_replace(array_keys($replacements), array_values($replacements), $subjectTmpl);
    $messageBody = str_replace(array_keys($replacements), array_values($replacements), $bodyTmpl);

    // 4. Fetch configured mail settings from site_settings
    $mailDriver = 'mail'; // Default to native mail (Postfix)
    $mailDomain = '';
    $mailFrom   = '';
    $smtpHost = '';
    $smtpPort = 587;
    $smtpUser = '';
    $smtpPass = '';
    $smtpEncryption = 'tls';

    try {
        $stmt = $pdo->query("SELECT setting_key, setting_value FROM site_settings WHERE setting_key IN ('mail_driver', 'mail_domain', 'mail_from', 'smtp_host', 'smtp_port', 'smtp_user', 'smtp_pass', 'smtp_encryption')");
        if ($stmt !== false) {
            while ($row = $stmt->fetch(\PDO::FETCH_ASSOC)) {
                $sKey = isset($row['setting_key']) && is_string($row['setting_key']) ? $row['setting_key'] : '';
                $sVal = isset($row['setting_value']) && is_string($row['setting_value']) ? $row['setting_value'] : '';
                switch ($sKey) {
                    case 'mail_driver': $mailDriver = trim($sVal); break;
                    case 'mail_domain': $mailDomain = trim($sVal); break;
                    case 'mail_from': $mailFrom = trim($sVal); break;
                    case 'smtp_host': $smtpHost = trim($sVal); break;
                    case 'smtp_port': $smtpPort = (int)$sVal; break;
                    case 'smtp_user': $smtpUser = trim($sVal); break;
                    case 'smtp_pass': $smtpPass = $sVal; break;
                    case 'smtp_encryption': $smtpEncryption = trim($sVal); break;
                }
            }
        }
    } catch (\Exception $e) {}

    // 5. Strict From Address resolution
    $fromEmail = $mailFrom !== '' ? $mailFrom : ($mailDomain !== '' ? ("no-reply@" . $mailDomain) : '');

    if ($fromEmail === '') {
        error_log("Mail Error: No 'From' email or mail domain configured in site settings.");
        return false;
    }

    // 6. Handle PHPMailer SMTP Client if selected by admin
    if ($mailDriver === 'smtp' && $smtpHost !== '') {
        try {
            $mail = new PHPMailer(true);
            $mail->isSMTP();
            $mail->Host       = $smtpHost;
            $mail->SMTPAuth   = true;
            $mail->Username   = $smtpUser;
            $mail->Password   = $smtpPass;
            
            if ($smtpEncryption === 'ssl') {
                $mail->SMTPSecure = PHPMailer::ENCRYPTION_SMTPS;
            } else {
                $mail->SMTPSecure = PHPMailer::ENCRYPTION_STARTTLS;
            }
            
            $mail->Port       = $smtpPort;

            // Headers & Recipients
            $mail->setFrom($fromEmail, $systemName);
            $mail->addAddress($toEmail);
            $mail->addReplyTo($fromEmail, $systemName);
            
            $mail->Subject = $subject;
            $mail->Body    = $messageBody;

            return $mail->send();
        } catch (Exception $e) {
            $errorInfo = isset($mail) && property_exists($mail, 'ErrorInfo') ? $mail->ErrorInfo : $e->getMessage();
            error_log("PHPMailer Error: {$errorInfo}. Falling back to Postfix mail().");
        }
    }

    // 7. Default Native Mail / Postfix Relay approach
    $headers = "From: " . $fromEmail . "\r\n" .
               "Reply-To: " . $fromEmail . "\r\n" .
               "X-Mailer: PHP/" . phpversion();

    $envelopeSender = "-f" . $fromEmail;

    return mail($toEmail, $subject, $messageBody, $headers, $envelopeSender);
}
