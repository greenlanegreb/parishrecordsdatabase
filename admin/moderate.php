<?php
// admin/moderate.php - Moderator and Admin moderation queue view (Table-Scoped Granular Moderation)
require_once '../db/db.php';
require_once '../db/auth_helpers.php';
require_once '../includes/functions.php';

if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

if (!is_module_enabled($pdo, 'moderation')) {
    http_response_code(403);
    exit('403 Forbidden: The Moderation Workflow module is currently disabled.');
}

$current_user = get_current_user_data($pdo);
if (!$current_user) {
    header('Location: ' . (defined('BASE_PATH') ? BASE_PATH : '') . '/user/login.php');
    exit;
}

$message = $_SESSION['message'] ?? '';
$error   = $_SESSION['error']   ?? '';
unset($_SESSION['message'], $_SESSION['error']);

[$user_timezone, $full_format_str] = get_user_time_prefs($current_user);

$pending_stmt = $pdo->query("
    SELECT es.*, r.table_id, dt.table_name, 
           u.id as suggestor_id, u.username as suggestor_name, u.first_name as suggestor_first, u.surname as suggestor_surname, u.attribution_display_mode as suggestor_mode,
           rv.value_content as current_live_value, tc.is_required, tc.data_type, tc.boolean_display_format, tc.date_search_behavior
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

$pending_suggestions = [];
foreach ($all_pending as $s) {
    $t_id = intval($s['table_id']);
    $mod_perm_key = 'moderate_table_' . $t_id;
  
    if (is_admin($pdo) || ($t_id === 1 && has_permission($pdo, 'moderate_table_1')) || has_permission($pdo, $mod_perm_key)) {
        $pending_suggestions[] = $s;
    }
}

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

<h3><?php echo htmlspecialchars(__('moderate.heading')); ?></h3>
<p><?php echo htmlspecialchars(__('moderate.subheading')); ?></p>

<div style="background: rgba(0, 123, 255, 0.05); border-left: 4px solid var(--primary-color, #007bff); padding: 0.75rem 1rem; margin-bottom: 1.5rem; border-radius: 4px;">
    <p style="margin: 0; font-size: 0.9rem; color: #333;">
        ⚡ <strong><?php echo htmlspecialchars(__('moderate.shortcut_label')); ?></strong> <?php echo htmlspecialchars(__('moderate.shortcut_desc')); ?>
    </p>
</div>

<table class="data-table" role="table" style="width: 100%; border-collapse: collapse;">
    <thead>
        <tr>
            <th scope="col"><?php echo htmlspecialchars(__('moderate.th_id_date')); ?></th>
            <th scope="col"><?php echo htmlspecialchars(__('moderate.th_table_record')); ?></th>
            <th scope="col"><?php echo htmlspecialchars(__('moderate.th_comparison')); ?></th>
            <th scope="col"><?php echo htmlspecialchars(__('moderate.th_actions')); ?></th>
        </tr>
    </thead>
    <tbody>
        <?php if (empty($pending_suggestions)): ?>
            <tr><td colspan="4"><?php echo htmlspecialchars(__('moderate.no_suggestions')); ?></td></tr>
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
                        <small style="color: #666;"><?php echo htmlspecialchars(__('moderate.by_label')); ?> <?php echo htmlspecialchars(format_user_display_name($pdo, [
                            'id' => $s['suggestor_id'],
                            'username' => $s['suggestor_name'] ?? __('moderate.guest_user'),
                            'first_name' => $s['suggestor_first'] ?? '',
                            'surname' => $s['suggestor_surname'] ?? '',
                            'attribution_display_mode' => $s['suggestor_mode'] ?? 'initials_random'
                        ], $current_user)); ?></small>
                    </td>
                    <td>
                        <span style="background: #e9ecef; padding: 0.1rem 0.4rem; border-radius: 3px; font-size: 0.8rem; font-weight: bold;"><?php echo htmlspecialchars($s['table_name']); ?></span><br>
                        <strong><?php echo htmlspecialchars(__('moderate.record_id_label')); ?></strong> #<?php echo $s['record_id']; ?><br>
                        <strong><?php echo htmlspecialchars(__('moderate.column_label')); ?></strong> <?php echo htmlspecialchars($s['column_name']); ?>
                        <?php if (!empty($s['is_required'])): ?>
                            <br><span style="color: var(--danger-color); font-size: 0.8rem; font-weight: bold;">(<?php echo htmlspecialchars(__('moderate.required_badge')); ?>)</span>
                        <?php endif; ?>
                    </td>
                    <td>
                        <div style="display: flex; gap: 1rem; background: rgba(0,0,0,0.02); padding: 0.5rem; border-radius: 4px; border: 1px solid var(--border-color); margin-bottom: 0.5rem;">
                            <div style="flex: 1; border-right: 1px solid var(--border-color); padding-right: 0.5rem;">
                                <span style="font-size: 0.75rem; text-transform: uppercase; color: #666; font-weight: bold;"><?php echo htmlspecialchars(__('moderate.live_value_label')); ?></span>
                                <div style="word-break: break-word; color: #444;"><?php echo htmlspecialchars($live_display !== '' ? $live_display : __('moderate.empty_placeholder')); ?></div>
                            </div>
                            <div style="flex: 1;">
                                <span style="font-size: 0.75rem; text-transform: uppercase; color: green; font-weight: bold;"><?php echo htmlspecialchars(__('moderate.proposed_value_label')); ?></span>
                                <div style="word-break: break-word; color: green; font-weight: 500;"><?php echo htmlspecialchars($prop_display); ?></div>
                            </div>
                        </div>
                        <?php if (!empty($s['reasoning'])): ?>
                            <div style="background: #fff3cd; border: 1px solid #ffeeba; padding: 0.4rem 0.6rem; border-radius: 4px; font-size: 0.9rem; color: #856404;">
                                <strong><?php echo htmlspecialchars(__('moderate.evidence_label')); ?></strong><br>
                                <div style="word-break: break-word; margin-top: 0.2rem;"><?php echo nl2br(htmlspecialchars($s['reasoning'])); ?></div>
                            </div>
                        <?php else: ?>
                            <small style="color: #888; font-style: italic;"><?php echo htmlspecialchars(__('moderate.no_evidence')); ?></small>
                        <?php endif; ?>
                    </td>
                    <td>
                        <form method="POST" action="actions/save_moderation.php" class="moderation-form">
                            <?php echo csrf_field(); ?>
                            <input type="hidden" name="suggestion_id" value="<?php echo $s['id']; ?>">
                          
                            <label for="final_value_<?php echo $s['id']; ?>" style="font-size: 0.85rem; font-weight: bold;"><?php echo htmlspecialchars(__('moderate.override_label')); ?></label><br>
                          
                            <?php if (($s['data_type'] ?? '') === 'BOOLEAN'): ?>
                                <?php
                                    $display_format = $s['boolean_display_format'] ?? 'yes_no';
                                    $opt1_text = __('index.opt_yes_true'); $opt2_text = __('index.opt_no_false');
                                    if ($display_format === 'male_female') { $opt1_text = __('index.opt_male'); $opt2_text = __('index.opt_female'); }
                                    elseif ($display_format === 'true_false') { $opt1_text = __('index.opt_true'); $opt2_text = __('index.opt_false'); }
                                    elseif ($display_format === 'tick_cross') { $opt1_text = __('index.opt_tick'); $opt2_text = __('index.opt_cross'); }
                                ?>
                                <select id="final_value_<?php echo $s['id']; ?>" name="final_value" style="width: 100%; padding: 0.3rem; margin-bottom: 0.5rem;" <?php echo (!empty($s['is_required'])) ? 'required' : ''; ?>>
                                    <option value=""><?php echo htmlspecialchars(__('moderate.select_placeholder')); ?></option>
                                    <option value="1" <?php echo ($s['proposed_value'] === '1') ? 'selected' : ''; ?>><?php echo htmlspecialchars($opt1_text); ?></option>
                                    <option value="0" <?php echo ($s['proposed_value'] === '0') ? 'selected' : ''; ?>><?php echo htmlspecialchars($opt2_text); ?></option>
                                </select><br>
                            <?php elseif (($s['data_type'] ?? '') === 'DATE'): ?>
                                <?php
                                    $user_fmt = $current_user['date_format'] ?? 'd/m/Y';
                                    $placeholder = 'YYYY-MM-DD';
                                    if ($user_fmt === 'd/m/Y' || $user_fmt === 'd/m/y') $placeholder = 'DD/MM/YYYY (e.g. 25/05/1500)';
                                    elseif ($user_fmt === 'd.m.Y') $placeholder = 'DD.MM.YYYY (e.g. 25.05.1500)';
                                    elseif ($user_fmt === 'm/d/Y') $placeholder = 'MM/DD/YYYY (e.g. 05/25/1500)';
                                ?>
                                <input type="text" id="final_value_<?php echo $s['id']; ?>" name="final_value" value="<?php echo htmlspecialchars($s['proposed_value']); ?>" placeholder="<?php echo htmlspecialchars($placeholder); ?>" <?php echo (!empty($s['is_required'])) ? 'required' : ''; ?> style="width: 100%; padding: 0.3rem; margin-bottom: 0.5rem;" title="<?php echo htmlspecialchars(__('moderate.historical_dates_title')); ?>"><br>
                            <?php else: ?>
                                <input type="text" id="final_value_<?php echo $s['id']; ?>" name="final_value" value="<?php echo htmlspecialchars($s['proposed_value']); ?>" <?php echo (!empty($s['is_required'])) ? 'required' : ''; ?> style="width: 100%; padding: 0.3rem; margin-bottom: 0.5rem;"><br>
                            <?php endif; ?>
                          
                            <div style="display: flex; gap: 0.5rem;">
                                <button type="submit" name="action" value="approve" class="btn btn-success approve-btn" style="padding: 0.25rem 0.5rem; font-size: 0.85rem;" onclick="return confirm('<?php echo htmlspecialchars(__('moderate.approve_confirm')); ?>');"><?php echo htmlspecialchars(__('moderate.approve_btn')); ?></button>
                                <button type="submit" name="action" value="reject" class="btn btn-danger" style="padding: 0.25rem 0.5rem; font-size: 0.85rem;" onclick="return confirm('<?php echo htmlspecialchars(__('moderate.decline_confirm')); ?>');"><?php echo htmlspecialchars(__('moderate.decline_btn')); ?></button>
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
                if (approveBtn && confirm('<?php echo htmlspecialchars(__('moderate.approve_confirm')); ?>')) {
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
