<?php
// user/actions/save_public_volunteer.php - Handles public volunteer submission with security checks and automated email
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}
require_once '../../db/db.php';
require_once '../../db/auth_helpers.php';
require_once '../../includes/functions.php';
require_once '../../includes/security_engine.php';

if (!is_module_enabled($pdo, 'volunteers')) {
    http_response_code(403);
    exit('403 Forbidden');
}

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    http_response_code(405);
    exit('Method Not Allowed');
}

verify_csrf_token();

// 1. Run Threat Defense Firewall Check
$firewall_result = run_form_firewall_check($pdo);
if ($firewall_result !== true) {
    $_SESSION['error'] = $firewall_result;
    header('Location: ../../volunteer.php');
    exit;
}

// 2. Run CAPTCHA Verification Check
$captcha_result = verify_form_captcha($pdo);
if ($captcha_result !== true) {
    $_SESSION['error'] = $captcha_result;
    header('Location: ../../volunteer.php');
    exit;
}

if (!empty($_POST['website_url'])) {
    $_SESSION['error'] = 'Spam detection triggered.';
    header('Location: ../../volunteer.php');
    exit;
}

$first_name = trim($_POST['volunteer_first_name'] ?? '');
$surname = trim($_POST['volunteer_surname'] ?? '');
$email = trim($_POST['volunteer_email'] ?? '');
$fields = $_POST['fields'] ?? [];

$_SESSION['submitted_volunteer_first'] = $first_name;
$_SESSION['submitted_volunteer_surname'] = $surname;
$_SESSION['submitted_volunteer_email'] = $email;
$_SESSION['submitted_volunteer_fields'] = $fields;

if (empty($first_name) || empty($surname) || empty($email)) {
    $_SESSION['error'] = "First name, surname, and email address are required fields.";
    header('Location: ../../volunteer.php');
    exit;
}
if (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
    $_SESSION['error'] = "Please provide a valid email address.";
    header('Location: ../../volunteer.php');
    exit;
}

$current_user = function_exists('get_current_user_data') ? get_current_user_data($pdo) : null;
$user_id = $current_user ? $current_user['id'] : null;

$pdo->beginTransaction();
try {
    $stmt = $pdo->prepare("INSERT INTO volunteer_submissions (first_name, surname, email, created_by) VALUES (?, ?, ?, ?)");
    $stmt->execute([$first_name, $surname, $email, $user_id]);
    $submission_id = $pdo->lastInsertId();

    if (!empty($fields)) {
        $val_stmt = $pdo->prepare("INSERT INTO volunteer_submission_values (submission_id, column_id, value_content) VALUES (?, ?, ?)");

        foreach ($fields as $col_id => $val) {
            if ($val !== '' && $val !== [] && !is_null($val)) {
                if (is_array($val)) {
                    $val = array_filter($val, function($v) { return is_string($v) && trim($v) !== ''; });
                }
                if (!empty($val)) {
                    $val_content = is_array($val) ? sanitize_incoming_text(implode(', ', $val)) : sanitize_incoming_text($val);
                    $val_stmt->execute([$submission_id, intval($col_id), $val_content]);
                }
            }
        }
    }

    $pdo->commit();
    
    // Trigger automated email template
    require_once '../../includes/volunteer_mail_engine.php';
    send_volunteer_templated_email($pdo, $submission_id, 'submission_received');

    unset($_SESSION['submitted_volunteer_first'], $_SESSION['submitted_volunteer_surname'], $_SESSION['submitted_volunteer_email'], $_SESSION['submitted_volunteer_fields']);
    $_SESSION['message'] = "Thank you! Your volunteer interest has been successfully submitted.";
} catch (Exception $e) {
    $pdo->rollBack();
    $_SESSION['error'] = "An error occurred while saving your submission. Please try again.";
}

header('Location: ../../volunteer.php');
exit;
