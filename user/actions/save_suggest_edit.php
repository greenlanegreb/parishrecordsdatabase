<?php
// user/actions/save_suggest_edit.php - Handles edit suggestion submissions
require_once '../../db/db.php';
session_start();

$record_id = $_POST['record_id'] ?? null;

if (!$record_id) {
    http_response_code(403);
    exit("No record specified.");
}

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $column_id = $_POST['column_id'] ?? '';
    $proposed_value = trim($_POST['proposed_value'] ?? '');
    $user_id = $_SESSION['user_id'] ?? null; 

    if (empty($proposed_value) || empty($column_id)) {
        $_SESSION['error'] = "Proposed value cannot be empty.";
    } else {
        $col_stmt = $pdo->prepare("SELECT column_name FROM table_columns WHERE id = ?");
        $col_stmt->execute([$column_id]);
        $col_info = $col_stmt->fetch();

        if ($col_info) {
            $ins = $pdo->prepare("INSERT INTO edit_suggestions (record_id, suggested_by, column_name, proposed_value, status) VALUES (?, ?, ?, ?, 'pending')");
            
            if ($ins->execute([$record_id, $user_id, $col_info['column_name'], $proposed_value])) {
                $_SESSION['message'] = "Your edit suggestion has been successfully submitted to the admin queue for review.";
                
                $audit = $pdo->prepare("INSERT INTO audit_logs (user_id, action, record_id, details, ip_address) VALUES (?, 'EDIT_SUGGESTION', ?, ?, ?)");
                $audit->execute([$user_id, $record_id, "Suggested edit for column: {$col_info['column_name']}", $_SERVER['REMOTE_ADDR']]);
            } else {
                $_SESSION['error'] = "Failed to submit suggestion.";
            }
        }
    }
}

header("Location: ../suggest_edit.php?record_id=" . urlencode($record_id));
exit;
