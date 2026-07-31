<?php
// admin/actions/save_settings.php - Processes global site settings updates
require_once '../../db/db.php';
require_once '../../db/auth_helpers.php';
require_once '../../includes/functions.php';
session_start();

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    http_response_code(405);
    exit('Method Not Allowed');
}

verify_csrf_token();
$current_user = require_permission($pdo, 'manage_settings', 'Manage global site settings, mail drivers, and maintenance mode');

$system_name        = trim($_POST['system_name'] ?? '');
$default_language   = preg_replace('/[^a-z_]/', '', strtolower(trim($_POST['default_language'] ?? 'en'))) ?: 'en';
$mail_domain        = trim($_POST['mail_domain'] ?? '');
$mail_from          = trim($_POST['mail_from'] ?? '');
$mail_driver        = trim($_POST['mail_driver'] ?? 'mail');
$smtp_host          = trim($_POST['smtp_host'] ?? '');
$smtp_port          = intval($_POST['smtp_port'] ?? 587);
$smtp_user          = trim($_POST['smtp_user'] ?? '');
$smtp_pass          = $_POST['smtp_pass'] ?? '';
$smtp_encryption    = trim($_POST['smtp_encryption'] ?? 'tls');

// CAPTCHA parameters
$captcha_provider   = trim($_POST['captcha_provider'] ?? 'none');
$turnstile_site     = trim($_POST['turnstile_site_key'] ?? '');
$turnstile_secret   = trim($_POST['turnstile_secret_key'] ?? '');
$recaptcha_site     = trim($_POST['recaptcha_site_key'] ?? '');
$recaptcha_secret   = trim($_POST['recaptcha_secret_key'] ?? '');
$hcaptcha_site      = trim($_POST['hcaptcha_site_key'] ?? '');
$hcaptcha_secret    = trim($_POST['hcaptcha_secret_key'] ?? '');

// Only allow languages that have a file under lang/
$lang_file = __DIR__ . '/../../lang/' . $default_language . '.php';
if (!is_file($lang_file)) {
    $default_language = 'en';
}

if (!empty($system_name)) {
    $stmt = $pdo->prepare("INSERT INTO site_settings (setting_key, setting_value) VALUES (?, ?) ON DUPLICATE KEY UPDATE setting_value = ?");

    $stmt->execute(['system_name', $system_name, $system_name]);
    $stmt->execute(['default_language', $default_language, $default_language]);

    // CAPTCHA persistence (only update secrets if provided or handle blanks)
    $stmt->execute(['captcha_provider', $captcha_provider, $captcha_provider]);
    $stmt->execute(['turnstile_site_key', $turnstile_site, $turnstile_site]);
    if (!empty($turnstile_secret)) {
        $stmt->execute(['turnstile_secret_key', $turnstile_secret, $turnstile_secret]);
    }
    
    $stmt->execute(['recaptcha_site_key', $recaptcha_site, $recaptcha_site]);
    if (!empty($recaptcha_secret)) {
        $stmt->execute(['recaptcha_secret_key', $recaptcha_secret, $recaptcha_secret]);
    }

    $stmt->execute(['hcaptcha_site_key', $hcaptcha_site, $hcaptcha_site]);
    if (!empty($hcaptcha_secret)) {
        $stmt->execute(['hcaptcha_secret_key', $hcaptcha_secret, $hcaptcha_secret]);
    }

    $stmt->execute(['mail_domain', $mail_domain, $mail_domain]);
    $stmt->execute(['mail_from', $mail_from, $mail_from]);

    $stmt->execute(['mail_driver', $mail_driver, $mail_driver]);
    $stmt->execute(['smtp_host', $smtp_host, $smtp_host]);
    $stmt->execute(['smtp_port', $smtp_port, $smtp_port]);
    $stmt->execute(['smtp_user', $smtp_user, $smtp_user]);

    if (!empty($smtp_pass)) {
        $stmt->execute(['smtp_pass', $smtp_pass, $smtp_pass]);
    }

    $stmt->execute(['smtp_encryption', $smtp_encryption, $smtp_encryption]);

    $_SESSION['message'] = "Global site settings, mail configurations, and security parameters updated successfully.";
    $audit = $pdo->prepare("INSERT INTO audit_logs (user_id, action, details, ip_address) VALUES (?, 'UPDATE_SETTINGS', ?, ?)");
    $audit->execute([$current_user['id'], "Updated global site settings, mail drivers, and CAPTCHA configurations", $_SERVER['REMOTE_ADDR']]);
} else {
    $_SESSION['error'] = "System name cannot be empty.";
}

header('Location: ../settings.php');
exit;
