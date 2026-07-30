<?php
// user/actions/save_public_ticket.php - Processes schema-driven ticket submissions with data retention
require_once '../../db/db.php';
require_once '../../db/auth_helpers.php';
require_once '../../includes/functions.php';
session_start();

if (!is_module_enabled($pdo, 'feedback')) {
    http_response_code(403);
    exit('403 Forbidden');
}
if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    http_response_code(405);
    exit('Method Not Allowed');
}

if (!empty($_POST['website_hp'])) {
    header('Location: ../../feedback.php');
    exit;
}

verify_csrf_token();

$fields = $_POST['fields'] ?? [];
$user_id = isset($_SESSION['user_id']) ? intval($_SESSION['user_id']) : null;
$_SESSION['submitted_feedback_fields'] = $fields;

$cols_stmt = $pdo->query("SELECT * FROM feedback_columns");
$columns_map = [];
while ($col = $cols_stmt->fetch(PDO::FETCH_ASSOC)) {
    $columns_map[$col['id']] = $col;
}

// Validate required fields and max length constraints
foreach ($columns_map as $col_id => $col_meta) {
    $val = trim($fields[$col_id] ?? '');
    
    if (!empty($col_meta['is_required']) && $val === '') {
        $_SESSION['error'] = "The field '{$col_meta['column_name']}' is mandatory.";
        header('Location: ../../feedback.php');
        exit;
    }
    
    if (!empty($col_meta['max_length']) && mb_strlen($val) > intval($col_meta['max_length'])) {
        $_SESSION['error'] = "The field '{$col_meta['column_name']}' exceeds the maximum allowed length of {$col_meta['max_length']} characters.";
        header('Location: ../../feedback.php');
        exit;
    }
}

// Extract standard ticket metadata dynamically
$name = 'Anonymous Submitter';
$email = 'no-email@provided.com';
$subject = 'Support Inquiry';

foreach ($fields as $col_id => $val) {
    if (isset($columns_map[$col_id])) {
        $cname = strtolower($columns_map[$col_id]['column_name']);
        $cleaned = trim($val);
        if (str_contains($cname, 'name') && !str_contains($cname, 'filename')) {
            $name = $cleaned;
        } elseif (str_contains($cname, 'email')) {
            $email = $cleaned;
        } elseif (str_contains($cname, 'subject') || str_contains($cname, 'title')) {
            $subject = $cleaned;
        }
    }
}

try {
    $pdo->beginTransaction();

    $stmt = $pdo->prepare("INSERT INTO feedback_tickets (user_id, name, email, subject, status) VALUES (?, ?, ?, ?, 'Pending')");
    $stmt->execute([$user_id, $name, $email, $subject]);
    $ticket_id = $pdo->lastInsertId();

    if (!empty($fields)) {
        $val_stmt = $pdo->prepare("INSERT INTO feedback_ticket_values (ticket_id, column_id, value_content) VALUES (?, ?, ?)");
        foreach ($fields as $col_id => $val) {
            if ($val !== '') {
                $val_stmt->execute([$ticket_id, intval($col_id), sanitize_incoming_text($val)]);
            }
        }
    }

    $pdo->commit();
    unset($_SESSION['submitted_feedback_fields']);
    $_SESSION['message'] = "Your support ticket (#{$ticket_id}) has been successfully submitted!";
} catch (Exception $e) {
    if ($pdo->inTransaction()) {
        $pdo->rollBack();
    }
    $_SESSION['error'] = "An error occurred while saving your ticket. Please try again.";
}

header('Location: ../../feedback.php');
exit;
