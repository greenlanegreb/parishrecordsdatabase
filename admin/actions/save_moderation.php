<?php
// admin/actions/save_moderation.php - Handles suggestion approvals and granular table-scoped security validation
require_once '../../db/db.php';
require_once '../../db/auth_helpers.php';
require_once '../../includes/functions.php';
session_start();

// Ensure the moderation module is enabled; otherwise block action execution
if (!is_module_enabled($pdo, 'moderation')) {
    http_response_code(403);
    exit('403 Forbidden: The Moderation Workflow module is currently disabled.');
}

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    http_response_code(405);
    exit('Method Not Allowed');
}
verify_csrf_token();

$suggestion_id = $_POST['suggestion_id'] ?? null;
$action = $_POST['action'] ?? '';
$final_value = sanitize_incoming_text($_POST['final_value'] ?? '');

if ($suggestion_id && in_array($action, ['approve', 'reject'])) {
    // Fetch suggestion joined with record table info
    $s_stmt = $pdo->prepare("
        SELECT es.*, r.table_id 
        FROM edit_suggestions es
        JOIN records r ON es.record_id = r.id
        WHERE es.id = ?
    ");
    $s_stmt->execute([$suggestion_id]);
    $suggestion = $s_stmt->fetch();
    
    if ($suggestion) {
        $table_id = intval($suggestion['table_id']);
        $mod_perm_key = 'moderate_table_' . $table_id;
        
        // Enforce granular table-scoped moderation permissions
        $current_user = get_current_user_data($pdo);
        if (!is_admin($pdo) && !has_permission($pdo, $mod_perm_key)) {
            http_response_code(403);
            exit('Unauthorized: You do not have moderation permission for this specific table.');
        }

        if ($action === 'approve') {
            // Find column metadata including requirement rules for this specific table
            $c_stmt = $pdo->prepare("SELECT id, is_required, data_type FROM table_columns WHERE column_name = ? AND table_id = ?");
            $c_stmt->execute([$suggestion['column_name'], $table_id]);
            $col = $c_stmt->fetch();
            if ($col) {
                if (!empty($col['is_required']) && $final_value === '') {
                    $_SESSION['error'] = "Cannot approve: This column is marked as required and cannot be left blank.";
                    header('Location: ../moderate.php');
                    exit;
                }
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
        $audit->execute([$current_user['id'], strtoupper($action) . '_SUGGESTION', $suggestion['record_id'], "Handled suggestion ID: {$suggestion_id} in table ID {$table_id}", $_SERVER['REMOTE_ADDR']]);
    }
}
header('Location: ../moderate.php');
exit;
