<?php
// actions/save_volunteer.php - Handles volunteer submission processing with honeypot anti-spam and CSRF protection
require_once '../db/db.php';
require_once '../db/auth_helpers.php';
require_once '../includes/functions.php';
session_start();

// Ensure the volunteers module is enabled; otherwise block action execution
if (!is_module_enabled($pdo, 'volunteers')) {
    http_response_code(403);
    exit('403 Forbidden: The Volunteer Portal module is currently disabled.');
}

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    http_response_code(405);
    exit('Method Not Allowed');
}

verify_csrf_token();

// ------------------------------------------------------------------
// Guest-safe permission: logged-in user OR guest role has submit_volunteer
// ------------------------------------------------------------------
$current_user = function_exists('get_current_user_data') ? get_current_user_data($pdo) : null;
$has_guest_permission = guest_has_permission($pdo, 'submit_volunteer');

if (!$current_user && !$has_guest_permission) {
    // Fall back to normal require_permission (forces login)
    $current_user = require_permission($pdo, 'submit_volunteer', 'Allows submitting volunteer interest and transcription applications');
}

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
