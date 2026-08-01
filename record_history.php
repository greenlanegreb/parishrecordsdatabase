<?php
// record_history.php - Chronological history and audit trail for a single record
require_once 'db/db.php';
require_once 'db/auth_helpers.php';
require_once 'includes/functions.php';

if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

$record_id = intval($_GET['record_id'] ?? 0);
if (!$record_id) {
    exit(__('record_history.exit_no_record'));
}

// Ensure record exists and find its table parent
$rec_stmt = $pdo->prepare("SELECT r.*, dt.table_name FROM records r JOIN dynamic_tables dt ON r.table_id = dt.id WHERE r.id = ?");
$rec_stmt->execute([$record_id]);
$record = $rec_stmt->fetch();

if (!$record) {
    exit(__('record_history.exit_not_found'));
}

$table_id = intval($record['table_id']);

// Access control: Ensure user/guest has permission to view this table
$current_user = function_exists('get_current_user_data') ? get_current_user_data($pdo) : null;
$has_public_permission = guest_has_permission($pdo, 'view_public');
$perm_key = 'view_table_' . $table_id;

if ($table_id !== 1 && !$current_user && !$has_public_permission) {
    header('Location: user/login.php');
    exit;
}
if ($table_id !== 1 && $current_user && !has_permission($pdo, $perm_key)) {
    require_once __DIR__ . '/403.php';
    exit;
}

// Check if current user has permission to purge individual audit log entries (self-discovering)
$can_purge_audit = $current_user && has_permission($pdo, 'purge_audit_entry', 'Allows purging individual audit log entries from record history');

// User timezone + datetime format preferences
$user_timezone = $current_user['timezone'] ?? 'UTC';
$full_format_str = ($current_user['date_format'] ?? 'd-m-Y') . ' ' . (($current_user['time_format'] ?? '24') === '12' ? 'g:i A' : 'H:i');

// Flash messages
$message = $_SESSION['message'] ?? '';
$error = $_SESSION['error'] ?? '';
unset($_SESSION['message'], $_SESSION['error']);

// Fetch audit logs tied to this record ID, joining edit_suggestions if applicable
$history_stmt = $pdo->prepare("
    SELECT al.*, u.username, es.column_name as sug_column, es.proposed_value as sug_value, es.reasoning as sug_reasoning, es.status as sug_status
    FROM audit_logs al
    LEFT JOIN users u ON al.user_id = u.id
    LEFT JOIN edit_suggestions es ON al.record_id = es.record_id AND (al.details LIKE CONCAT('%suggestion ID: ', es.id, '%') OR al.details LIKE CONCAT('%column: ', es.column_name, '%'))
    WHERE al.record_id = ?
    ORDER BY al.created_at DESC
");
$history_stmt->execute([$record_id]);
$history_logs = $history_stmt->fetchAll(PDO::FETCH_ASSOC);

// Fetch current live values for this record to show context
$vals_stmt = $pdo->prepare("
    SELECT tc.column_name, rv.value_content 
    FROM table_columns tc
    LEFT JOIN record_values rv ON rv.column_id = tc.id AND rv.record_id = ?
    WHERE tc.table_id = ?
    ORDER BY tc.sort_order ASC
");
$vals_stmt->execute([$record_id, $table_id]);
$current_values = $vals_stmt->fetchAll(PDO::FETCH_ASSOC);

$return_url = $_SERVER['HTTP_REFERER'] ?? 'index.php?table_id=' . $table_id;
?>
<?php require_once 'partials/header.php'; ?>

<div class="search-box-container" style="max-width: 900px; margin: 2rem auto;">
    <?php if (!empty($message)): ?>
        <p class="alert-success"><strong><?php echo htmlspecialchars($message); ?></strong></p>
    <?php endif; ?>
    <?php if (!empty($error)): ?>
        <p class="alert-danger"><strong><?php echo htmlspecialchars($error); ?></strong></p>
    <?php endif; ?>

    <div style="display: flex; justify-content: space-between; align-items: center; margin-bottom: 1rem;">
        <h3 style="margin: 0;"><?php echo htmlspecialchars(__('record_history.heading_prefix')); ?> #<?php echo $record_id; ?></h3>
        <a href="<?php echo htmlspecialchars($return_url); ?>" class="btn btn-secondary" style="font-size: 0.9rem; text-decoration: none; padding: 0.4rem 0.8rem;">← <?php echo htmlspecialchars(__('record_history.return_btn')); ?></a>
    </div>
    
    <p style="color: #666; font-size: 0.95rem;">
        <?php echo htmlspecialchars(__('record_history.directory_table_label')); ?> <strong><?php echo htmlspecialchars($record['table_name']); ?></strong><br>
        <?php echo htmlspecialchars(__('record_history.subheading_lifecycle')); ?>
    </p>

    <!-- Current State Snapshot Preview -->
    <div style="background: rgba(0,0,0,0.02); border: 1px solid var(--border-color); padding: 1rem; border-radius: 6px; margin-bottom: 1.5rem;">
        <h4 style="margin-top: 0; font-size: 1rem; border-bottom: 1px solid var(--border-color); padding-bottom: 0.4rem;"><?php echo htmlspecialchars(__('record_history.snapshot_heading')); ?></h4>
        <div style="display: grid; grid-template-columns: repeat(auto-fit, minmax(200px, 1fr)); gap: 0.75rem; margin-top: 0.5rem;">
            <?php foreach ($current_values as $cv): ?>
                <div>
                    <span style="font-size: 0.75rem; text-transform: uppercase; color: #666; font-weight: bold;"><?php echo htmlspecialchars($cv['column_name']); ?>:</span>
                    <div style="color: #333; word-break: break-word;"><?php echo htmlspecialchars($cv['value_content'] !== '' ? $cv['value_content'] : __('record_history.empty_value')); ?></div>
                </div>
            <?php endforeach; ?>
        </div>
    </div>

    <h4><?php echo htmlspecialchars(__('record_history.timeline_heading')); ?></h4>
    <?php if (empty($history_logs)): ?>
        <p style="font-style: italic; color: #777;"><?php echo htmlspecialchars(__('record_history.no_history')); ?></p>
    <?php else: ?>
        <div style="display: flex; flex-direction: column; gap: 0.75rem;">
            <?php foreach ($history_logs as $log): ?>
                <div style="background: #fff; border: 1px solid var(--border-color); border-left: 4px solid var(--primary-color, #007bff); padding: 0.75rem 1rem; border-radius: 4px; box-shadow: 0 1px 3px rgba(0,0,0,0.05); position: relative;">
                    
                    <!-- Purge Individual Audit Log Entry Button (Gated via self-discovery permission) -->
                    <?php if ($can_purge_audit): ?>
                        <form action="user/actions/purge_audit_entry.php" method="POST" onsubmit="return confirm('<?php echo htmlspecialchars(__('record_history.purge_confirm')); ?>');" style="position: absolute; top: 0.75rem; right: 1rem; margin: 0;">
                            <?php echo csrf_field(); ?>
                            <input type="hidden" name="audit_id" value="<?php echo $log['id']; ?>">
                            <input type="hidden" name="record_id" value="<?php echo $record_id; ?>">
                            <button type="submit" class="btn btn-danger" style="padding: 0.15rem 0.4rem; font-size: 0.7rem;"><?php echo htmlspecialchars(__('record_history.purge_btn')); ?></button>
                        </form>
                    <?php endif; ?>

                    <div style="display: flex; justify-content: space-between; align-items: center; margin-bottom: 0.3rem; padding-right: 70px;">
                        <span style="background: #e9ecef; padding: 0.1rem 0.4rem; border-radius: 3px; font-size: 0.75rem; font-weight: bold; color: #333;">
                            <?php echo htmlspecialchars($log['action']); ?>
                        </span>
                        <small style="color: #666;"><?php echo format_user_time($log['created_at'], $user_timezone, $full_format_str); ?></small>
                    </div>
                    <div style="font-size: 0.9rem; color: #444; margin-bottom: 0.3rem;">
                        <strong><?php echo htmlspecialchars(__('record_history.actor_label')); ?></strong> <?php echo htmlspecialchars($log['username'] ?? __('record_history.system_guest')); ?>
                    </div>
                    <div style="font-size: 0.9rem; color: #222; background: rgba(0,0,0,0.01); padding: 0.4rem; border-radius: 3px; word-break: break-word; margin-bottom: 0.4rem;">
                        <?php echo nl2br(htmlspecialchars($log['details'])); ?>
                    </div>

                    <?php if (!empty($log['sug_column']) || !empty($log['sug_value'])): ?>
                        <div style="background: rgba(0,123,255,0.04); border: 1px dashed rgba(0,123,255,0.2); padding: 0.5rem; border-radius: 4px; font-size: 0.85rem;">
                            <div><strong><?php echo htmlspecialchars(__('record_history.target_column')); ?></strong> <?php echo htmlspecialchars($log['sug_column']); ?></div>
                            <div><strong><?php echo htmlspecialchars(__('record_history.proposed_value')); ?></strong> <span style="color: #0056b3; font-weight: bold;"><?php echo htmlspecialchars($log['sug_value']); ?></span></div>
                            <?php if (!empty($log['sug_reasoning'])): ?>
                                <div><strong><?php echo htmlspecialchars(__('record_history.reasoning_evidence')); ?></strong> <?php echo htmlspecialchars($log['sug_reasoning']); ?></div>
                            <?php endif; ?>
                        </div>
                    <?php endif; ?>
                </div>
            <?php endforeach; ?>
        </div>
    <?php endif; ?>
</div>

<?php require_once 'partials/footer.php'; ?>
