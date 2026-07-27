<?php
// admin/actions/save_moderation.php - Handles suggestion approvals, overrides, and rejections by admins/moderators
require_once '../../db/db.php';
require_once '../../db/auth_helpers.php';
require_once '../../includes/functions.php';

// Enforce moderator or admin privileges and validate POST request method
$current_user = initialize_action($pdo, ['admin', 'moderator'], 'POST');

$suggestion_id = $_POST['suggestion_id'] ?? null;
$action = $_POST['action'] ?? '';
$final_value = sanitize_incoming_text($_POST['final_value'] ?? '');

if ($suggestion_id && in_array($action, ['approve', 'reject'])) {
    $s_stmt = $pdo->prepare("SELECT * FROM edit_suggestions WHERE id = ?");
    $s_stmt->execute([$suggestion_id]);
    $suggestion = $s_stmt->fetch();

    if ($suggestion) {
        if ($action === 'approve') {
            // Find column metadata including requirement rules and data types
            $c_stmt = $pdo->prepare("SELECT id, is_required, data_type FROM table_columns WHERE column_name = ?");
            $c_stmt->execute([$suggestion['column_name']]);
            $col = $c_stmt->fetch();

            if ($col) {
                // Enforce required column rule if a moderator cleared the field
                if (!empty($col['is_required']) && $final_value === '') {
                    $_SESSION['error'] = "Cannot approve: This column is marked as required and cannot be left blank.";
                    header('Location: ../moderate.php');
                    exit;
                }

                // Check if a record value already exists, insert if missing, update if present
                $check_val = $pdo->prepare("SELECT id FROM record_values WHERE record_id = ? AND column_id = ?");
                $check_val->execute([$suggestion['record_id'], $col['id']]);
                
                if ($check_val->fetch()) {
                    $up_stmt = $pdo->prepare("UPDATE record_values SET value_content = ? WHERE record_id = ? AND column_id = ?");
                    $up_stmt->execute([$final_value, $suggestion['record_id'], $col['id']]);
                } else {
                    $ins_stmt = $pdo->prepare("INSERT INTO record_values (record_id, column_id, value_content) VALUES (?, ?, ?)");
                    $ins_stmt->execute([$suggestion['record_id'], $col['id'], $final_value]);
                }
            }

            $status_stmt = $pdo->prepare("UPDATE edit_suggestions SET status = 'approved' WHERE id = ?");
            $status_stmt->execute([$suggestion_id]);
            $_SESSION['message'] = "Suggestion #{$suggestion_id} approved and applied.";
        } else {
            $status_stmt = $pdo->prepare("UPDATE edit_suggestions SET status = 'rejected' WHERE id = ?");
            $status_stmt->execute([$suggestion_id]);
            $_SESSION['message'] = "Suggestion #{$suggestion_id} has been rejected.";
        }

        $audit = $pdo->prepare("INSERT INTO audit_logs (user_id, action, record_id, details, ip_address) VALUES (?, ?, ?, ?, ?)");
        $audit->execute([$current_user['id'], strtoupper($action) . '_SUGGESTION', $suggestion['record_id'], "Handled suggestion ID: {$suggestion_id} with final value: '{$final_value}'", $_SERVER['REMOTE_ADDR']]);
    }
}

header('Location: ../moderate.php');
exit;
