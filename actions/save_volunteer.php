<?php
// actions/save_volunteer.php - Handles volunteer submission processing with honeypot anti-spam and CSRF protection
require_once '../db/db.php';
require_once '../db/auth_helpers.php';
session_start();

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    http_response_code(405);
    exit('Method Not Allowed');
}

// Verify the CSRF token and enforce permission check
verify_csrf_token();
$current_user = require_permission($pdo, 'submit_volunteer', 'Allows submitting volunteer interest and transcription applications');

$bot_trap = trim($_POST['website_url'] ?? '');

if (!empty($bot_trap)) {
    // Silently fake success for bots
    $_SESSION['message'] = "Thank you! Your volunteer interest has been recorded.";
} else {
    $name = trim($_POST['name'] ?? '');
    $email = trim($_POST['email'] ?? '');
    $experience = trim($_POST['experience'] ?? '');

    if (empty($name) || empty($email) || empty($experience)) {
        $_SESSION['error'] = "All fields are required.";
    } elseif (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
        $_SESSION['error'] = "Please provide a valid email address.";
    } else {
        $stmt = $pdo->prepare("INSERT INTO volunteers (name, email, experience) VALUES (?, ?, ?)");
        if ($stmt->execute([$name, $email, $experience])) {
            $_SESSION['message'] = "Thank you! Your interest in volunteering for data entry has been successfully recorded.";
            
            // Log action if user is logged in
            if ($current_user && isset($current_user['id'])) {
                $audit = $pdo->prepare("INSERT INTO audit_logs (user_id, action, details, ip_address) VALUES (?, 'SUBMIT_VOLUNTEER', ?, ?)");
                $audit->execute([$current_user['id'], "Submitted volunteer application from: {$email}", $_SERVER['REMOTE_ADDR']]);
            }
        } else {
            $_SESSION['error'] = "An error occurred while saving your submission. Please try again.";
        }
    }
}

header('Location: ../volunteer.php');
exit;
