<?php
// user/actions/save_public_volunteer.php - Handles public volunteer form submission
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}
require_once '../../db/db.php';
require_once '../../db/auth_helpers.php';
require_once '../../includes/functions.php';

if (!is_module_enabled($pdo, 'volunteers')) {
    http_response_code(403);
    exit('403 Forbidden');
}

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    http_response_code(405);
    exit('Method Not Allowed');
}

verify_csrf_token();

// Check honeypot
if (!empty($_POST['website_url'])) {
    $_SESSION['error'] = 'Spam detection triggered.';
    header('Location: ../../volunteer.php');
    exit;
}

$fields = $_POST['fields'] ?? [];
$_SESSION['submitted_volunteer_fields'] = $fields;

// Validate required fields
$cols_stmt = $pdo->query("SELECT * FROM volunteer_columns");
$columns = $cols_stmt->fetchAll(PDO::FETCH_ASSOC);

foreach ($columns as $col) {
    if (!empty($col['is_required'])) {
        $val = $fields[$col['id']] ?? '';
        if ($val === '' || (is_array($val) && empty($val))) {
            $_SESSION['error'] = "The field '" . $col['column_name'] . "' is required.";
            header('Location: ../../volunteer.php');
            exit;
        }
    }
}

$current_user = function_exists('get_current_user_data') ? get_current_user_data($pdo) : null;
$user_id = $current_user ? $current_user['id'] : null;

$pdo->beginTransaction();
try {
    $stmt = $pdo->prepare("INSERT INTO volunteer_submissions (created_by) VALUES (?)");
    $stmt->execute([$user_id]);
    $submission_id = $pdo->lastInsertId();

    $val_stmt = $pdo->prepare("INSERT INTO volunteer_submission_values (submission_id, column_id, value_content) VALUES (?, ?, ?)");

    foreach ($fields as $col_id => $val) {
        if ($val !== '' && $val !== []) {
            $val_content = is_array($val) ? sanitize_incoming_text(implode(', ', $val)) : sanitize_incoming_text($val);
            $val_stmt->execute([$submission_id, intval($col_id), $val_content]);
        }
    }

    $pdo->commit();
    unset($_SESSION['submitted_volunteer_fields']);
    $_SESSION['message'] = "Thank you! Your volunteer interest has been successfully submitted.";
} catch (Exception $e) {
    $pdo->rollBack();
    $_SESSION['error'] = "An error occurred while saving your submission. Please try again.";
}

header('Location: ../../volunteer.php');
exit;
