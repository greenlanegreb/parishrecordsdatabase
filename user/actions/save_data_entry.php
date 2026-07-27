<?php
// user/actions/save_data_entry.php - Handles record insertions and duplicate confirmations with validation & text sanitization
require_once '../../db/db.php';
require_once '../../db/auth_helpers.php';
require_once '../../includes/functions.php';

// Enforce dynamic permission check replacing hardcoded roles (automatically registers 'access_data_entry' if new)
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}
if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    http_response_code(405);
    exit('Method Not Allowed');
}
verify_csrf_token();
$current_user = require_permission($pdo, 'access_data_entry', 'Allows accessing the core data entry workstation and creating records');

if (isset($_POST['action']) && $_POST['action'] === 'insert_record') {
    $input_filters = $_POST['filters'] ?? [];
    $confirmed_duplicate = isset($_POST['confirm_duplicate']) && $_POST['confirm_duplicate'] === '1';

    // Fetch column metadata for required field rules
    $cols_map = [];
    $stmt_cols = $pdo->query("SELECT id, column_name, is_required FROM table_columns");
    while ($col = $stmt_cols->fetch()) {
        $cols_map[$col['id']] = $col;
    }

    // Server-side required field check & text sanitization prep
    $sanitized_inputs = [];
    foreach ($input_filters as $cid => $val) {
        $clean_val = sanitize_incoming_text($val);
        $sanitized_inputs[$cid] = $clean_val;
        if (isset($cols_map[$cid]) && !empty($cols_map[$cid]['is_required'])) {
            if ($clean_val === '') {
                $_SESSION['error'] = "The required field '{$cols_map[$cid]['column_name']}' cannot be left blank.";
                header('Location: ../data_entry.php');
                exit;
            }
        }
    }

    $has_content = false;
    foreach ($sanitized_inputs as $val) {
        if (!empty($val)) { $has_content = true; break; }
    }

    if ($has_content) {
        $first_col_val = '';
        $first_col_id = 0;
        foreach ($sanitized_inputs as $cid => $cval) {
            if (!empty($cval)) {
                $first_col_id = $cid;
                $first_col_val = $cval;
                break;
            }
        }

        try {
            // Begin a database transaction to lock the sequence and prevent race conditions
            $pdo->beginTransaction();

            // Check for duplicates if not already confirmed, using a locking read
            if (!$confirmed_duplicate && !empty($first_col_val)) {
                $check_stmt = $pdo->prepare("
                    SELECT r.id, rv.value_content, u.username 
                    FROM record_values rv
                    JOIN records r ON rv.record_id = r.id
                    JOIN users u ON r.created_by = u.id
                    WHERE rv.column_id = ? AND rv.value_content = ?
                    FOR UPDATE
                ");
                $check_stmt->execute([$first_col_id, $first_col_val]);
                $existing_matches = $check_stmt->fetchAll();

                if (count($existing_matches) > 0) {
                    // Rollback transaction lock before redirecting to the modal view
                    $pdo->rollBack();
                    $_SESSION['duplicate_warning'] = true;
                    $_SESSION['duplicate_matches'] = $existing_matches;
                    $_SESSION['submitted_filters'] = $sanitized_inputs;
                    header('Location: ../data_entry.php');
                    exit;
                }
            }

            // Proceed with insertion
            $rec_stmt = $pdo->prepare("INSERT INTO records (created_by) VALUES (?)");
            $rec_stmt->execute([$current_user['id']]);
            $record_id = $pdo->lastInsertId();

            foreach ($sanitized_inputs as $column_id => $value_content) {
                if (!empty($value_content)) {
                    $val_stmt = $pdo->prepare("INSERT INTO record_values (record_id, column_id, value_content) VALUES (?, ?, ?)");
                    $val_stmt->execute([$record_id, $column_id, $value_content]);
                }
            }

            // Increment points
            $points_stmt = $pdo->prepare("UPDATE users SET points = points + 1 WHERE id = ?");
            $points_stmt->execute([$current_user['id']]);

            // Audit log
            $audit_stmt = $pdo->prepare("INSERT INTO audit_logs (user_id, action, record_id, details, ip_address) VALUES (?, 'INSERT', ?, ?, ?)");
            $audit_stmt->execute([$current_user['id'], $record_id, "Added record entry via data entry with sanitization", $_SERVER['REMOTE_ADDR']]);

            // Commit the transaction successfully
            $pdo->commit();
            $_SESSION['message'] = "Record successfully added!";
        } catch (Exception $e) {
            // If any error or collision occurs, safely roll back changes
            if ($pdo->inTransaction()) {
                $pdo->rollBack();
            }
            throw $e;
        }
    }
}

// Clear duplicate states if completed or cancelled
unset($_SESSION['duplicate_warning'], $_SESSION['duplicate_matches'], $_SESSION['submitted_filters']);
header('Location: ../data_entry.php');
exit;
