<?php
// admin/moderate.php - Moderator and Admin moderation queue view
require_once '../db/db.php';
require_once '../db/auth_helpers.php';
require_once '../includes/functions.php';
session_start();

// Enforce moderator or admin privileges via central helper
require_role($pdo, ['admin', 'moderator']);
$current_user = get_current_user_data($pdo);

// Determine user timezone and dynamically compile the date/time format string using the central helper
$user_timezone = $current_user['timezone'] ?? 'UTC';
$full_format_str = get_user_datetime_format($current_user);

$message = $_SESSION['message'] ?? '';
$error = $_SESSION['error'] ?? '';
unset($_SESSION['message'], $_SESSION['error']);

// Fetch pending suggestions along with the current live value from record_values and required column status, data type, and display format
$pending_stmt = $pdo->query("
    SELECT es.*, u.username as suggestor_name, rv.value_content as current_live_value, tc.is_required, tc.data_type, tc.boolean_display_format
    FROM edit_suggestions es
    LEFT JOIN users u ON es.suggested_by = u.id
    LEFT JOIN table_columns tc ON es.column_name = tc.column_name
    LEFT JOIN record_values rv ON es.record_id = rv.record_id AND tc.id = rv.column_id
    WHERE es.status = 'pending'
    ORDER BY es.created_at ASC
");
$pending_suggestions = $pending_stmt->fetchAll();
?>

    <?php require_once '../partials/header.php'; ?>

    <?php if (!empty($error)): ?>
        <p class="alert-danger"><strong><?php echo htmlspecialchars($error); ?></strong></p>
    <?php endif; ?>
    <?php if (!empty($message)): ?>
        <p class="alert-success"><strong><?php echo htmlspecialchars($message); ?></strong></p>
    <?php endif; ?>

    <h3>Pending Suggestions Review</h3>
    <p>Compare user-proposed changes against live records. You can accept the proposal as-is, edit/override the value(s) before approval, or decline the suggestion entirely.</p>
    
    <!-- Prominent Shortcut Hint Box -->
    <div style="background: rgba(0, 123, 255, 0.05); border-left: 4px solid var(--primary-color, #007bff); padding: 0.75rem 1rem; margin-bottom: 1.5rem; border-radius: 4px;">
        <p style="margin: 0; font-size: 0.9rem; color: #333;">
            ⚡ <strong>Keyboard Shortcut Tip:</strong> When reviewing any row, you can press <strong>Ctrl + Enter</strong> to quickly approve the changes, or press <strong>Esc</strong> while focused on the override box to clear it instantly!
        </p>
    </div>

    <table class="data-table" role="table" style="width: 100%; border-collapse: collapse;">
        <thead>
            <tr>
                <th scope="col">ID / Date</th>
                <th scope="col">Record & Column</th>
                <th scope="col">Comparison (Live vs Proposed)</th>
                <th scope="col">Moderator Actions</th>
            </tr>
        </thead>
        <tbody>
            <?php if (empty($pending_suggestions)): ?>
                <tr><td colspan="4">No pending suggestions to review.</td></tr>
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
                            <strong>Record ID:</strong> #<?php echo $s['record_id']; ?><br>
                            <strong>Column:</strong> <?php echo htmlspecialchars($s['column_name']); ?>
                            <?php if (!empty($s['is_required'])): ?>
                                <br><span style="color: var(--danger-color); font-size: 0.8rem; font-weight: bold;">(Required Field)</span>
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
                                <input type="hidden" name="column_id" value="<?php echo $s['column_name']; ?>">
                                
                                <label for="final_value_<?php echo $s['id']; ?>" style="font-size: 0.85rem; font-weight: bold;">Override Value (if needed):</label><br>
                                
                                <?php if (($s['data_type'] ?? '') === 'BOOLEAN'): ?>
                                    <?php 
                                        $display_format = $s['boolean_display_format'] ?? 'yes_no';
                                        $opt1_text = 'Yes / True';
                                        $opt2_text = 'No / False';
                                        if ($display_format === 'male_female') {
                                            $opt1_text = 'Male';
                                            $opt2_text = 'Female';
                                        } elseif ($display_format === 'true_false') {
                                            $opt1_text = 'True';
                                            $opt2_text = 'False';
                                        } elseif ($display_format === 'tick_cross') {
                                            $opt1_text = '✔ (Tick)';
                                            $opt2_text = '✘ (Cross)';
                                        }
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
    // Keyboard shortcuts for the moderation queue
    document.addEventListener('DOMContentLoaded', () => {
        document.querySelectorAll('.moderation-form').forEach(form => {
            form.addEventListener('keydown', (e) => {
                // Ctrl + Enter triggers approval
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
                
                // Escape key clears text inputs
                if (e.key === 'Escape' && e.target.tagName === 'INPUT' && e.target.type === 'text') {
                    e.preventDefault();
                    e.target.value = '';
                }
            });
        });
    });
    </script>

    <?php require_once '../partials/footer.php'; ?>
