<?php
// admin/actions/test_mail.php - Sends a test email to verify configuration
require_once '../../db/db.php';
require_once '../../db/auth_helpers.php';
require_once '../../includes/functions.php';
require_once '../../db/mail_helper.php';
session_start();

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    http_response_code(405);
    exit('Method Not Allowed');
}

// Verify CSRF token and enforce dynamic permission check
verify_csrf_token();
$current_user = require_permission($pdo, 'manage_settings', 'Manage global site settings, mail drivers, and maintenance mode');

$test_email = trim($_POST['test_email'] ?? '');

if (!filter_var($test_email, FILTER_VALIDATE_EMAIL)) {
    $_SESSION['error'] = "Please provide a valid recipient email address for testing.";
    header('Location: ../settings.php');
    exit;
}

$system_name = get_system_name($pdo);
$subject = $system_name . " - Configuration Test Email";

// Fetch mail domain for building the payload
$mail_domain = 'deballiolsociety.org.uk';
try {
    $stmt = $pdo->prepare("SELECT setting_value FROM site_settings WHERE setting_key = 'mail_domain'");
    $stmt->execute();
    $val = $stmt->fetchColumn();
    if ($val) { $mail_domain = trim($val); }
} catch (Exception $e) {}

$from_email = "no-reply@" . $mail_domain;
$message_body = "Hello,\n\n" .
                "This is a diagnostic test email sent from the " . $system_name . " settings panel.\n" .
                "If you are reading this, your mail configuration (" . strtoupper(get_current_mail_driver($pdo)) . ") is working successfully!\n\n" .
                "Timestamp: " . date('Y-m-d H:i:s') . "\n";

// Execute send using a test wrapper or direct function logic
$success = false;

// We can leverage a dedicated test sender or reuse mail helper logic
// To keep it clean and robust, we will run the underlying mail dispatch logic directly:
try {
    // Check which driver is active
    $mail_driver = 'mail';
    $stmt = $pdo->prepare("SELECT setting_value FROM site_settings WHERE setting_key = 'mail_driver'");
    $stmt->execute();
    $driver_val = $stmt->fetchColumn();
    if ($driver_val) { $mail_driver = trim($driver_val); }

    if ($mail_driver === 'smtp') {
        // Test via PHPMailer SMTP
        require_once __DIR__ . '/../../includes/phpmailer/Exception.php';
        require_once __DIR__ . '/../../includes/phpmailer/PHPMailer.php';
        require_once __DIR__ . '/../../includes/phpmailer/SMTP.php';

        $mail = new PHPMailer\PHPMailer\PHPMailer(true);
        $mail->isSMTP();
        
        // Fetch SMTP configs
        $smtp = [];
        $stmt = $pdo->query("SELECT setting_key, setting_value FROM site_settings WHERE setting_key LIKE 'smtp_%'");
        while ($row = $stmt->fetch(PDO::FETCH_ASSOC)) {
            $smtp[$row['setting_key']] = $row['setting_value'];
        }

        $mail->Host       = $smtp['smtp_host'] ?? '';
        $mail->SMTPAuth   = true;
        $mail->Username   = $smtp['smtp_user'] ?? '';
        $mail->Password   = $smtp['smtp_pass'] ?? '';
        $mail->SMTPSecure = (($smtp['smtp_encryption'] ?? 'tls') === 'ssl') ? PHPMailer\PHPMailer\PHPMailer::ENCRYPTION_SMTPS : PHPMailer\PHPMailer\PHPMailer::ENCRYPTION_STARTTLS;
        $mail->Port       = intval($smtp['smtp_port'] ?? 587);

        $mail->setFrom($from_email, $system_name);
        $mail->addAddress($test_email);
        $mail->addReplyTo($from_email, $system_name);
        
        $mail->Subject = $subject;
        $mail->Body    = $message_body;

        $success = $mail->send();
    } else {
        // Test via Native Postfix mail()
        $headers = "From: " . $from_email . "\r\n" .
                   "Reply-To: " . $from_email . "\r\n" .
                   "X-Mailer: PHP/" . phpversion();
        $envelope_sender = "-f" . $from_email;
        $success = mail($test_email, $subject, $message_body, $headers, $envelope_sender);
    }
} catch (Exception $e) {
    $_SESSION['error'] = "Test mail failed: " . $e->getMessage();
    header('Location: ../settings.php');
    exit;
}

if ($success) {
    $_SESSION['message'] = "Test email successfully dispatched to " . htmlspecialchars($test_email) . "!";
    
    $audit = $pdo->prepare("INSERT INTO audit_logs (user_id, action, details, ip_address) VALUES (?, 'TEST_MAIL', ?, ?)");
    $audit->execute([$current_user['id'], "Dispatched test email successfully to: {$test_email}", $_SERVER['REMOTE_ADDR']]);
} else {
    $_SESSION['error'] = "Failed to dispatch test email. Check your server logs or SMTP settings.";
}

header('Location: ../settings.php');
exit;
