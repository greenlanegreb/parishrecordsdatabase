<?php
// actions/save_feedback.php - Handles feedback form submission with honeypot anti-spam and CSRF protection
require_once '../db/db.php';
require_once '../db/auth_helpers.php';
require_once '../includes/functions.php';
session_start();

// Ensure the feedback module is enabled; otherwise block action execution
if (!is_module_enabled($pdo, 'feedback')) {
    http_response_code(403);
    exit('403 Forbidden: The Feedback Submissions module is currently disabled.');
}

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    http_response_code(405);
    exit('Method Not Allowed');
}

// Verify the CSRF token and enforce submission permission
verify_csrf_token();
$current_user = require_permission($pdo, 'submit_feedback', 'Allows submitting public feedback and inquiries');

$bot_trap = trim($_POST['website_url'] ?? '');

if (!empty($bot_trap)) {
    // Silently fake success for bots
    $_SESSION['message'] = "Thank you! Your feedback has been successfully submitted.";
} else {
    $name = trim($_POST['name'] ?? '');
    $email = trim($_POST['email'] ?? '');
    $feedback_text = trim($_POST['message'] ?? '');

    if (empty($name) || empty($email) || empty($feedback_text)) {
        $_SESSION['error'] = "All fields are required.";
    } elseif (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
        $_SESSION['error'] = "Please provide a valid email address.";
    } else {
        $stmt = $pdo->prepare("INSERT INTO feedback (name, email, message) VALUES (?, ?, ?)");
        if ($stmt->execute([$name, $email, $feedback_text])) {
            $_SESSION['message'] = "Thank you! Your feedback has been successfully submitted.";
            
            // Log action if user is logged in
            if ($current_user && isset($current_user['id'])) {
                $audit = $pdo->prepare("INSERT INTO audit_logs (user_id, action, details, ip_address) VALUES (?, 'SUBMIT_FEEDBACK', ?, ?)");
                $audit->execute([$current_user['id'], "Submitted feedback from: {$email}", $_SERVER['REMOTE_ADDR']]);
            }
        } else {
            $_SESSION['error'] = "An error occurred while saving your feedback. Please try again.";
        }
    }
}

header('Location: ../feedback.php');
exit;
