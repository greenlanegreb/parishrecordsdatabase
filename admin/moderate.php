<?php
// admin/moderate.php - Moderator and Admin moderation queue view (Table-Scoped Granular Moderation)
require_once '../db/db.php';
require_once '../db/auth_helpers.php';
require_once '../includes/functions.php';
session_start();

$current_user = get_current_user_data($pdo);
$user_timezone = $current_user['timezone'] ?? 'UTC';
$full_format_str = get_user_datetime_format($current_user);
$message = $_SESSION['message'] ?? '';
$error = $_SESSION['error'] ?? '';
unset($_SESSION['message'], $_SESSION['error']);

// Fetch pending suggestions and join with records to discover their parent table_id
$pending_stmt = $pdo->query("
    SELECT es.*, r.table_id, dt.table_name, u.username as suggestor_name, rv.value_content as current_live_value, tc.is_required, tc.data_type, tc.boolean_display_format
    FROM edit_suggestions es
    JOIN records r ON es.record_id = r.id
    JOIN dynamic_tables dt ON r.table_id = dt.id
    LEFT JOIN users u ON es.suggested_by = u.id
    LEFT JOIN table_columns tc ON es.column_name = tc.column_name AND tc.table_id = r.table_id
    LEFT JOIN record_values rv ON es.record_id = rv.record_id AND tc.id = rv.column_id
    WHERE es.status = 'pending'
    ORDER BY es.created_at ASC
");
$all_pending = $pending_stmt->fetchAll();

// Filter suggestions based on table-specific moderation permissions (e.g., moderate_table_X)
$pending_suggestions = [];
foreach ($all_pending as $s) {
    $t_id = intval($s['table_id']);
    $mod_perm_key = 'moderate_table_' . $t_id;
    
    // Fallback support for Table 1 or general admin/moderator capability checks
    if (is_admin($pdo) || ($t_id === 1 && has_permission($pdo, 'moderate_table_1', 'Allows moderating Table 1 records')) || has_permission($pdo, $mod_perm_key, 'Allows moderating records in table: ' . $s['table_name'])) {
        $pending_suggestions[] = $s;
    }
}

// If the user has zero moderation permissions across any table, block access with 403
$has_any_mod_perm = is_admin($pdo);
if (!$has_any_mod_perm) {
    $tables_chk = $pdo->query("SELECT id FROM dynamic_tables")->fetchAll(PDO::FETCH_COLUMN);
    foreach ($tables_chk as $t_id) {
        if (has_permission($pdo, 'moderate_table_' . $t_id)) {
            $has_any_mod_perm = true;
            break;
        }
    }
}
if (!$has_any_mod_perm) {
    require_once __DIR__ . '/../403.php';
    exit;
}
?>
    <?php require_once '../partials/header.php'; ?>
    <?php if (!empty($error)): ?>
        <p class="alert-danger" role="alert"><strong><?php echo htmlspecialchars($error); ?></strong></p>
    <?php endif; ?>
    <?php if (!empty($message)): ?>
        <p class="alert-success" role="status"><strong><?php echo htmlspecialchars($message); ?></strong></p>
    <?php endif; ?>
    
    <h3>Pending Suggestions Review</h3>
    <p>Compare user-proposed changes against live records across your permitted tables. Approve proposals, override values, or decline suggestions.</p>
    
    <div style="background: rgba(0, 123, 255, 0.05); border-left: 4px solid var(--primary-color, #007bff); padding: 0.75rem 1rem; margin-bottom: 1.5rem; border-radius: 4px;">
        <p style="margin: 0; font-size: 0.9rem; color: #333;">
            ⚡ <strong>Keyboard Shortcut Tip:</strong> Press <strong>Ctrl + Enter</strong> to quickly approve, or <strong>Esc</strong> to clear the override box!
        </p>
    </div>
    <table class="data-table" role="table" style="width: 100%; border-collapse: collapse;">
        <thead>
            <tr>
                <th scope="col">ID / Date</th>
                <th scope="col">Table, Record & Column</th>
                <th scope="col">Comparison (Live vs Proposed)</th>
                <th scope="col">Moderator Actions</th>
            </tr>
        </thead>
        <tbody>
            <?php if (empty($pending_suggestions)): ?>
                <tr><td colspan="4">No pending suggestions found for your permitted moderation tables.</td></tr>
            <?php else: ?>
                <?php foreach ($pending_suggestions as $s): ?>
                    <?php 
                        $live_display = $s['current_live_value'] ?? '';
                        $prop_display = $s['proposed_value'] ?? '';
                        
                        if (($s['data_type'] ?? '') === 'BOOLEAN') {
                            $fmt = $s['boolean_display_format'] ?? 'yes_no';
                            $live_display = format_boolean_value($s['current_live_value'], $fmt);
                            $prop_display = format_boolean_value($s['proposed_value'], $fmt);
                        } elseif (($s['data_type'] ?? '') === 'DATE') {
                            $live_display = format_display_date($s['current_live_value'], $current_user['date_format'] ?? 'd/m/Y');
                            $prop_display = format_display_date($s['proposed_value'], $current_user['date_format'] ?? 'd/m/Y');
                        }
                    ?>
                    <tr>
                        <td>
                            <strong>#<?php echo $s['id']; ?></strong><br>
                            <small><?php echo format_user_time($s['created_at'], $user_timezone, $full_format_str); ?></small><br>
                            <small style="color: #666;">By: <?php echo htmlspecialchars($s['suggestor_name'] ?? 'Viewer/Guest'); ?></small>
                        </td>
                        <td>
                            <span style="background: #e9ecef; padding: 0.1rem 0.4rem; border-radius: 3px; font-size: 0.8rem; font-weight: bold;"><?php echo htmlspecialchars($s['table_name']); ?></span><br>
                            <strong>Record ID:</strong> #<?php echo $s['record_id']; ?><br>
                            <strong>Column:</strong> <?php echo htmlspecialchars($s['column_name']); ?>
                            <?php if (!empty($s['is_required'])): ?>
                                <br><span style="color: var(--danger-color); font-size: 0.8rem; font-weight: bold;">(Required)</span>
                            <?php endif; ?>
                        </td>
                        <td>
                            <div style="display: flex; gap: 1rem; background: rgba(0,0,0,0.02); padding: 0.5rem; border-radius: 4px; border: 1px solid var(--border-color);">
                                <div style="flex: 1; border-right: 1px solid var(--border-color); padding-right: 0.5rem;">
                                    <span style="font-size: 0.75rem; text-transform: uppercase; color: #666; font-weight: bold;">Current Live Value:</span>
                                    <div style="word-break: break-word; color: #444;"><?php echo htmlspecialchars($live_display !== '' ? $live_display : '[Empty]'); ?></div>
                                </div>
                                <div style="flex: 1;">
                                    <span style="font-size: 0.75rem; text-transform: uppercase; color: green; font-weight: bold;">Proposed Change:</span>
                                    <div style="word-break: break-word; color: green; font-weight: 500;"><?php echo htmlspecialchars($prop_display); ?></div>
                                </div>
                            </div>
                        </td>
                        <td>
                            <form method="POST" action="actions/save_moderation.php" class="moderation-form">
                                <?php echo csrf_field(); ?>
                                <input type="hidden" name="suggestion_id" value="<?php echo $s['id']; ?>">
                                
                                <label for="final_value_<?php echo $s['id']; ?>" style="font-size: 0.85rem; font-weight: bold;">Override Value:</label><br>
                                
                                <?php if (($s['data_type'] ?? '') === 'BOOLEAN'): ?>
                                    <?php 
                                        $display_format = $s['boolean_display_format'] ?? 'yes_no';
                                        $opt1_text = 'Yes / True'; $opt2_text = 'No / False';
                                        if ($display_format === 'male_female') { $opt1_text = 'Male'; $opt2_text = 'Female'; }
                                        elseif ($display_format === 'true_false') { $opt1_text = 'True'; $opt2_text = 'False'; }
                                        elseif ($display_format === 'tick_cross') { $opt1_text = '✔ (Tick)'; $opt2_text = '✘ (Cross)'; }
                                    ?>
                                    <select id="final_value_<?php echo $s['id']; ?>" name="final_value" style="width: 100%; padding: 0.3rem; margin-bottom: 0.5rem;" <?php echo (!empty($s['is_required'])) ? 'required' : ''; ?>>
                                        <option value="">-- Select --</option>
                                        <option value="1" <?php echo ($s['proposed_value'] === '1') ? 'selected' : ''; ?>><?php echo $opt1_text; ?></option>
                                        <option value="0" <?php echo ($s['proposed_value'] === '0') ? 'selected' : ''; ?>><?php echo $opt2_text; ?></option>
                                    </select><br>
                                <?php else: ?>
                                    <input type="text" id="final_value_<?php echo $s['id']; ?>" name="final_value" value="<?php echo htmlspecialchars($s['proposed_value']); ?>" <?php echo (!empty($s['is_required'])) ? 'required' : ''; ?> style="width: 100%; padding: 0.3rem; margin-bottom: 0.5rem;"><br>
                                <?php endif; ?>
                                
                                <div style="display: flex; gap: 0.5rem;">
                                    <button type="submit" name="action" value="approve" class="btn btn-success approve-btn" style="padding: 0.25rem 0.5rem; font-size: 0.85rem;" onclick="return confirm('Approve and apply this value?');">Approve</button>
                                    <button type="submit" name="action" value="reject" class="btn btn-danger" style="padding: 0.25rem 0.5rem; font-size: 0.85rem;" onclick="return confirm('Decline and reject this suggestion?');">Decline</button>
                                </div>
                            </form>
                        </td>
                    </tr>
                <?php endforeach; ?>
            <?php endif; ?>
        </tbody>
    </table>
    <script>
    document.addEventListener('DOMContentLoaded', () => {
        document.querySelectorAll('.moderation-form').forEach(form => {
            form.addEventListener('keydown', (e) => {
                if ((e.ctrlKey || e.metaKey) && e.key === 'Enter') {
                    e.preventDefault();
                    const approveBtn = form.querySelector('.approve-btn');
                    if (approveBtn && confirm('Approve and apply this value?')) {
                        const actionInput = document.createElement('input');
                        actionInput.type = 'hidden';
                        actionInput.name = 'action';
                        actionInput.value = 'approve';
                        form.appendChild(actionInput);
                        form.submit();
                    }
                }
                if (e.key === 'Escape' && e.target.tagName === 'INPUT' && e.target.type === 'text') {
                    e.preventDefault();
                    e.target.value = '';
                }
            });
        });
    });
    </script>
    <?php require_once '../partials/footer.php'; ?>
