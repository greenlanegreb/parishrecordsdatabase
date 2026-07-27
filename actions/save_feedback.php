<?php
// actions/save_feedback.php - Handles feedback form submission with honeypot anti-spam and CSRF protection
require_once '../db/db.php';
require_once '../db/auth_helpers.php';
session_start();

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    // Verify the CSRF token
    verify_csrf_token();

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
            } else {
                $_SESSION['error'] = "An error occurred while saving your feedback. Please try again.";
            }
        }
    }
}

header('Location: ../feedback.php');
exit;
