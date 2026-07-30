<?php
// user/actions/save_data_entry.php - Handles record insertions and duplicate confirmations with multi-table support
require_once '../../db/db.php';
require_once '../../db/auth_helpers.php';
require_once '../../includes/functions.php';

if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    http_response_code(405);
    exit('Method Not Allowed');
}

verify_csrf_token();
$current_user = require_permission($pdo, 'access_data_entry', 'Allows accessing the core data entry workstation and creating records');

$table_id = intval($_POST['table_id'] ?? 1);

// Enforce table-specific permission check
$perm_key = 'view_table_' . $table_id;
if ($table_id !== 1 && !has_permission($pdo, $perm_key)) {
    http_response_code(403);
    exit('Unauthorized table access.');
}

if (isset($_POST['action']) && $_POST['action'] === 'insert_record') {
    $input_filters = $_POST['filters'] ?? [];
    $confirmed_duplicate = isset($_POST['confirm_duplicate']) && $_POST['confirm_duplicate'] === '1';

    // Fetch column metadata specifically for this table
    $cols_map = [];
    $stmt_cols = $pdo->prepare("SELECT id, column_name, is_required, data_type FROM table_columns WHERE table_id = ?");
    $stmt_cols->execute([$table_id]);
    while ($col = $stmt_cols->fetch()) {
        $cols_map[$col['id']] = $col;
    }

    // Save submitted filters to session so form fields persist if validation fails or duplicate warning triggers
    $_SESSION['submitted_filters'] = $input_filters;

    // Server-side required field check & text sanitization prep
    $sanitized_inputs = [];
    foreach ($input_filters as $cid => $val) {
        $is_bool = (isset($cols_map[$cid]) && ($cols_map[$cid]['data_type'] ?? '') === 'BOOLEAN');
        
        // Handle boolean "0" properly without treating it as empty
        if ($is_bool) {
            $clean_val = ($val !== '' && $val !== null) ? trim((string)$val) : '';
        } else {
            $clean_val = sanitize_incoming_text($val);
        }

        $sanitized_inputs[$cid] = $clean_val;

        if (isset($cols_map[$cid]) && !empty($cols_map[$cid]['is_required'])) {
            if ($clean_val === '') {
                $_SESSION['error'] = "The required field '{$cols_map[$cid]['column_name']}' cannot be left blank.";
                header('Location: ../data_entry.php?table_id=' . $table_id);
                exit;
            }
        }
    }

    $has_content = false;
    foreach ($sanitized_inputs as $val) {
        if ($val !== '') { $has_content = true; break; }
    }

    if ($has_content) {
        $first_col_val = '';
        $first_col_id = 0;
        foreach ($sanitized_inputs as $cid => $cval) {
            if ($cval !== '') {
                $first_col_id = $cid;
                $first_col_val = $cval;
                break;
            }
        }

        try {
            $pdo->beginTransaction();

            // Check for duplicates within the same table if not confirmed
            if (!$confirmed_duplicate && $first_col_val !== '') {
                $check_stmt = $pdo->prepare("
                    SELECT r.id, rv.value_content, u.username 
                    FROM record_values rv
                    JOIN records r ON rv.record_id = r.id
                    LEFT JOIN users u ON r.created_by = u.id
                    WHERE r.table_id = ? AND rv.column_id = ? AND rv.value_content = ?
                    FOR UPDATE
                ");
                $check_stmt->execute([$table_id, $first_col_id, $first_col_val]);
                $existing_matches = $check_stmt->fetchAll();

                if (count($existing_matches) > 0) {
                    $pdo->rollBack();
                    $_SESSION['duplicate_warning'] = true;
                    $_SESSION['duplicate_matches'] = $existing_matches;
                    header('Location: ../data_entry.php?table_id=' . $table_id);
                    exit;
                }
            }

            // Proceed with insertion bound to the active table ID
            $rec_stmt = $pdo->prepare("INSERT INTO records (table_id, created_by) VALUES (?, ?)");
            $rec_stmt->execute([$table_id, $current_user['id']]);
            $record_id = $pdo->lastInsertId();

            $audit_details_parts = ["Created record entry in table ID {$table_id}."];

            foreach ($sanitized_inputs as $column_id => $value_content) {
                if ($value_content !== '') {
                    $val_stmt = $pdo->prepare("INSERT INTO record_values (record_id, column_id, value_content) VALUES (?, ?, ?)");
                    $val_stmt->execute([$record_id, $column_id, $value_content]);

                    // Build readable summary for audit details
                    if (isset($cols_map[$column_id])) {
                        $col_name = $cols_map[$column_id]['column_name'];
                        $audit_details_parts[] = "{$col_name}: {$value_content}";
                    }
                }
            }

            // Increment points securely via helper function
            adjust_user_points($pdo, intval($current_user['id']), 1);

            // Enhanced Audit log with initial field values
            $audit_details = implode(' | ', $audit_details_parts);
            $audit_stmt = $pdo->prepare("INSERT INTO audit_logs (user_id, action, record_id, details, ip_address) VALUES (?, ?, ?, ?, ?)");
            $audit_stmt->execute([$current_user['id'], 'INSERT', $record_id, $audit_details, $_SERVER['REMOTE_ADDR']]);

            $pdo->commit();
            // Clear submitted filters on successful save so the form resets for the next entry
            unset($_SESSION['submitted_filters']);
            $_SESSION['message'] = "Record successfully added!";
        } catch (Exception $e) {
            if ($pdo->inTransaction()) {
                $pdo->rollBack();
            }
            throw $e;
        }
    }
}

unset($_SESSION['duplicate_warning'], $_SESSION['duplicate_matches']);
header('Location: ../data_entry.php?table_id=' . $table_id);
exit;
