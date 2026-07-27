<?php
// mail_helper.php - Lightweight native mail wrapper using server Postfix relay

function send_user_invitation($pdo, $to_email, $reset_token) {
    $system_name = get_system_name($pdo);
    $subject = $system_name . " - Account Invitation";
    
    // Fallback-safe host resolution
    $host = $_SERVER['HTTP_HOST'] ?? 'deballiolsociety.org.uk';
    
    // Construct a clean, absolute URL path to the setup page regardless of calling depth
    $protocol = (!empty($_SERVER['HTTPS']) && $_SERVER['HTTPS'] !== 'off') ? "https://" : "https://";
    
    $setup_link = $protocol . $host . "/projects/cakebread-database/user/set_password.php?token=" . $reset_token;
    
    $message = "Hello,\n\n" .
               "An account has been created for you on the " . $system_name . ".\n" .
               "Please click the link below to set your password and activate your account:\n\n" .
               $setup_link . "\n\n" .
               "This link is valid for 24 hours.\n\n" .
               "If you did not expect this invitation, please contact the site administrator.\n";

    // Proper headers to satisfy basic mail clients
    $headers = "From: no-reply@" . $host . "\r\n" .
               "Reply-To: no-reply@" . $host . "\r\n" .
               "X-Mailer: PHP/" . phpversion();

    // The fifth parameter (-f) sets the envelope sender for ISPConfig's Postfix
    $envelope_sender = "-fno-reply@" . $host;

    return mail($to_email, $subject, $message, $headers, $envelope_sender);
}
?>
