<?php
// actions/save_volunteer.php - Handles volunteer submission processing with honeypot anti-spam
require_once '../db/db.php';
session_start();

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
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
            } else {
                $_SESSION['error'] = "An error occurred while saving your submission. Please try again.";
            }
        }
    }
}

header('Location: ../volunteer.php');
exit;
