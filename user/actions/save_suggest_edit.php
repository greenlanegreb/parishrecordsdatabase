<?php
// user/actions/save_suggest_edit.php - Handles edit suggestion submissions securely without upfront points
require_once '../../db/db.php';
require_once '../../db/auth_helpers.php';
require_once '../../includes/functions.php';

// Ensure the moderation module is enabled; otherwise block action execution
if (!is_module_enabled($pdo, 'moderation')) {
    http_response_code(403);
    exit('403 Forbidden: The Moderation Workflow module is currently disabled.');
}

if (session_status() === PHP_SESSION_NONE) {
    session_start();
}
if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    http_response_code(405);
    exit('Method Not Allowed');
}
verify_csrf_token();
$current_user = require_permission($pdo, 'access_suggest_edit', 'Allows submitting edit suggestions for records');
$user_id = $current_user['id'];

$record_id = $_POST['record_id'] ?? null;
$return_url = $_POST['return_url'] ?? '../index.php';

if (!$record_id) {
    http_response_code(403);
    exit("No record specified.");
}

$column_id = $_POST['column_id'] ?? '';
$proposed_value = trim($_POST['proposed_value'] ?? '');
$reasoning = trim($_POST['reasoning'] ?? '');

// Check if proposed value is empty (allowing string '0' for boolean false)
if (($proposed_value === '' && $proposed_value !== '0') || empty($column_id)) {
    $_SESSION['error'] = "Proposed value cannot be empty.";
} else {
    $col_stmt = $pdo->prepare("SELECT column_name, data_type, boolean_display_format FROM table_columns WHERE id = ?");
    $col_stmt->execute([$column_id]);
    $col_info = $col_stmt->fetch();

    if ($col_info) {
        $display_val = $proposed_value;
        if ($col_info['data_type'] === 'BOOLEAN') {
            $fmt = $col_info['boolean_display_format'] ?? 'yes_no';
            $display_val = format_boolean_value($proposed_value, $fmt);
        }

        // Insert suggestion as pending with points_awarded = 0 (no points in limbo)
        $ins = $pdo->prepare("
            INSERT INTO edit_suggestions (record_id, suggested_by, column_name, proposed_value, reasoning, status, points_awarded) 
            VALUES (?, ?, ?, ?, ?, 'pending', 0)
        ");
        
        if ($ins->execute([$record_id, $user_id, $col_info['column_name'], $proposed_value, $reasoning])) {
            $_SESSION['message'] = "Your edit suggestion has been successfully submitted to the admin queue for review.";
            
            $audit_details = "Suggested edit for column: {$col_info['column_name']} (Proposed: {$display_val}).";
            if ($reasoning !== '') {
                $audit_details .= " Reasoning/Evidence: " . $reasoning;
            }

            $audit = $pdo->prepare("INSERT INTO audit_logs (user_id, action, record_id, details, ip_address) VALUES (?, ?, ?, ?, ?)");
            $audit->execute([$user_id, 'EDIT_SUGGESTION', $record_id, $audit_details, $_SERVER['REMOTE_ADDR']]);
        } else {
            $_SESSION['error'] = "Failed to submit suggestion.";
        }
    }
}

header("Location: ../suggest_edit.php?record_id=" . urlencode($record_id) . "&return=" . urlencode($return_url));
exit;
