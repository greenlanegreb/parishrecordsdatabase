<?php
// user/actions/save_public_suggestion.php - Securely handles public and user edit suggestions with honeypot & sanitization
require_once '../../db/db.php';
require_once '../../db/auth_helpers.php';
require_once '../../includes/functions.php';
session_start();

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    // 1. Honeypot Anti-Spam Check: If the hidden trap field is filled out, it's a bot. Silently drop or reject.
    if (!empty($_POST['website_hp'])) {
        $_SESSION['error'] = "Spam detection triggered. Submission rejected.";
        header('Location: ../../index.php');
        exit;
    }

    $record_id = intval($_POST['record_id'] ?? 0);
    $column_name = trim($_POST['column_name'] ?? '');
    $proposed_value = sanitize_incoming_text($_POST['proposed_value'] ?? '');
    
    // Determine user ID if logged in, otherwise null for public guest
    $suggested_by = isset($_SESSION['user_id']) ? intval($_SESSION['user_id']) : null;

    if ($record_id > 0 && !empty($column_name)) {
        // Verify the column actually exists to prevent fake column injection
        $col_stmt = $pdo->prepare("SELECT id, is_required FROM table_columns WHERE column_name = ?");
        $col_stmt->execute([$column_name]);
        $col = $col_stmt->fetch();

        if ($col) {
            // Enforce required field rules on suggestions if left blank
            if (!empty($col['is_required']) && $proposed_value === '') {
                $_SESSION['error'] = "This field is required and cannot be submitted blank.";
                header('Location: ../../index.php');
                exit;
            }

            // Insert into edit_suggestions table (which feeds admin/moderate.php)
            $stmt = $pdo->prepare("
                INSERT INTO edit_suggestions (record_id, column_name, proposed_value, suggested_by, status, created_at) 
                VALUES (?, ?, ?, ?, 'pending', NOW())
            ");
            if ($stmt->execute([$record_id, $column_name, $proposed_value, $suggested_by])) {
                $_SESSION['message'] = "Your edit suggestion has been successfully submitted and sent to the moderation queue for review. Thank you!";
            } else {
                $_SESSION['error'] = "Failed to submit edit suggestion. Please try again.";
            }
        } else {
            $_SESSION['error'] = "Invalid column specified.";
        }
    } else {
        $_SESSION['error'] = "Invalid record submission parameters.";
    }
}

header('Location: ../../index.php');
exit;
