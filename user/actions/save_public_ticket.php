<?php
// user/actions/save_public_ticket.php - Processes schema-driven ticket submissions with security checks & automated email
require_once '../../db/db.php';
require_once '../../db/auth_helpers.php';
require_once '../../includes/functions.php';
require_once '../../includes/security_engine.php';
session_start();

if (!is_module_enabled($pdo, 'feedback')) {
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
    header('Location: ../../feedback.php');
    exit;
}

// 2. Run CAPTCHA Verification Check
$captcha_result = verify_form_captcha($pdo);
if ($captcha_result !== true) {
    $_SESSION['error'] = $captcha_result;
    header('Location: ../../feedback.php');
    exit;
}

if (!empty($_POST['website_hp'])) {
    header('Location: ../../feedback.php');
    exit;
}

$first_name = trim($_POST['feedback_first_name'] ?? '');
$surname = trim($_POST['feedback_surname'] ?? '');
$email = trim($_POST['feedback_email'] ?? '');
$subject = trim($_POST['feedback_subject'] ?? 'Support Inquiry');
$fields = $_POST['fields'] ?? [];
$user_id = isset($_SESSION['user_id']) ? intval($_SESSION['user_id']) : null;

$_SESSION['submitted_feedback_first'] = $first_name;
$_SESSION['submitted_feedback_surname'] = $surname;
$_SESSION['submitted_feedback_email'] = $email;
$_SESSION['submitted_feedback_subject'] = $subject;
$_SESSION['submitted_feedback_fields'] = $fields;

if (empty($first_name) || empty($surname) || empty($email) || empty($subject)) {
    $_SESSION['error'] = "First name, surname, email address, and subject are mandatory fields.";
    header('Location: ../../feedback.php');
    exit;
}
if (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
    $_SESSION['error'] = "Invalid email format.";
    header('Location: ../../feedback.php');
    exit;
}

$cols_stmt = $pdo->query("SELECT * FROM feedback_columns");
$columns_map = [];
while ($col = $cols_stmt->fetch(PDO::FETCH_ASSOC)) {
    $columns_map[$col['id']] = $col;
}

// Validate custom required fields safely (handling strings and arrays)
foreach ($columns_map as $col_id => $col_meta) {
    if (!empty($col_meta['is_required'])) {
        $val = $fields[$col_id] ?? null;
        $is_empty = false;
        if (is_null($val)) {
            $is_empty = true;
        } elseif (is_array($val)) {
            $filtered = array_filter($val, function($v) { return is_string($v) && trim($v) !== ''; });
            if (empty($filtered)) $is_empty = true;
        } else {
            if (!is_string($val) || trim($val) === '') $is_empty = true;
        }

        if ($is_empty) {
            $_SESSION['error'] = "The field '{$col_meta['column_name']}' is mandatory.";
            header('Location: ../../feedback.php');
            exit;
        }
    }
}

try {
    $pdo->beginTransaction();

    $stmt = $pdo->prepare("INSERT INTO feedback_tickets (user_id, first_name, surname, email, subject, status) VALUES (?, ?, ?, ?, ?, 'Pending')");
    $stmt->execute([$user_id, $first_name, $surname, $email, $subject]);
    $ticket_id = $pdo->lastInsertId();

    if (!empty($fields)) {
        $val_stmt = $pdo->prepare("INSERT INTO feedback_ticket_values (ticket_id, column_id, value_content) VALUES (?, ?, ?)");
        foreach ($fields as $col_id => $val) {
            if ($val !== '' && $val !== [] && !is_null($val)) {
                if (is_array($val)) {
                    $val = array_filter($val, function($v) { return is_string($v) && trim($v) !== ''; });
                }
                if (!empty($val)) {
                    $val_content = is_array($val) ? sanitize_incoming_text(implode(', ', $val)) : sanitize_incoming_text($val);
                    $val_stmt->execute([$ticket_id, intval($col_id), $val_content]);
                }
            }
        }
    }

    $pdo->commit();

    // Trigger automated support ticket email template
    require_once '../../includes/feedback_mail_engine.php';
    send_feedback_templated_email($pdo, $ticket_id, 'ticket_received');

    unset($_SESSION['submitted_feedback_first'], $_SESSION['submitted_feedback_surname'], $_SESSION['submitted_feedback_email'], $_SESSION['submitted_feedback_subject'], $_SESSION['submitted_feedback_fields']);
    $_SESSION['message'] = "Your support ticket (#{$ticket_id}) has been successfully submitted!";
} catch (Exception $e) {
    if ($pdo->inTransaction()) {
        $pdo->rollBack();
    }
    $_SESSION['error'] = "An error occurred while saving your ticket. Please try again.";
}

header('Location: ../../feedback.php');
exit;
