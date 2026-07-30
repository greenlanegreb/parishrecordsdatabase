<?php
// feedback.php - Public Ticket Intake Portal (Schema-Driven & Data-Preserving)
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}
require_once 'db/db.php';
require_once 'db/auth_helpers.php';
require_once 'includes/functions.php';

if (!is_module_enabled($pdo, 'feedback')) {
    require_once __DIR__ . '/403.php';
    exit;
}

$system_name = get_system_name($pdo);
$columns_stmt = $pdo->query("SELECT * FROM feedback_columns ORDER BY sort_order ASC, column_name ASC");
$columns = $columns_stmt->fetchAll(PDO::FETCH_ASSOC);

$message = $_SESSION['message'] ?? '';
$error = $_SESSION['error'] ?? '';
$submitted_data = $_SESSION['submitted_feedback_fields'] ?? [];
unset($_SESSION['message'], $_SESSION['error'], $_SESSION['submitted_feedback_fields']);
?>
<?php require_once 'partials/header.php'; ?>

<div class="search-box-container" style="max-width: 600px; margin: 2rem auto;">
    <h3>Submit Support Ticket or Feedback</h3>
    <p>Fill out the form below to open a ticket with our team. Fields marked with <span style="color:red; font-weight:bold;">*</span> are mandatory.</p>

    <?php if (!empty($message)): ?>
        <p class="alert-success"><strong><?php echo htmlspecialchars($message); ?></strong></p>
    <?php endif; ?>
    <?php if (!empty($error)): ?>
        <p class="alert-danger"><strong><?php echo htmlspecialchars($error); ?></strong></p>
    <?php endif; ?>

    <form method="POST" action="user/actions/save_public_ticket.php">
        <?php echo csrf_field(); ?>
        
        <!-- Honeypot -->
        <div style="display:none;" aria-hidden="true">
            <label for="website_hp">Leave blank</label>
            <input type="text" id="website_hp" name="website_hp" tabindex="-1" autocomplete="off">
        </div>

        <?php if (empty($columns)): ?>
            <p style="color: #666; font-style: italic;">No feedback form fields have been configured yet by the administrator.</p>
        <?php else: ?>
            <?php foreach ($columns as $col): 
                $saved_val = $submitted_data[$col['id']] ?? '';
                $max_attr = !empty($col['max_length']) ? 'maxlength="' . intval($col['max_length']) . '"' : '';
                $subtype = $col['field_subtype'] ?? '';
                $options = array_filter(array_map('trim', explode(',', $col['field_options'] ?? '')));
                $allow_multi = !empty($col['allow_multiple']);
            ?>
                <div style="margin-bottom: 1rem;">
                    <label for="field_<?php echo $col['id']; ?>">
                        <strong><?php echo htmlspecialchars($col['column_name']); ?>:</strong>
                        <?php if (!empty($col['is_required'])): ?><span style="color:red; font-weight:bold;" title="Required Field">*</span><?php endif; ?>
                        <?php if (!empty($col['max_length'])): ?><span style="font-size:0.8rem; color:#666;">(Max: <?php echo $col['max_length']; ?> chars)</span><?php endif; ?>
                    </label><br>

                    <?php if (($col['data_type'] ?? '') === 'BOOLEAN'): ?>
                        <?php 
                            $fmt = $col['boolean_display_format'] ?? 'yes_no';
                            $opt1 = ($fmt === 'true_false') ? 'True' : 'Yes';
                            $opt2 = ($fmt === 'true_false') ? 'False' : 'No';
                        ?>
                        <select id="field_<?php echo $col['id']; ?>" name="fields[<?php echo $col['id']; ?>]" class="dashboard-input" style="width:100%; padding:0.4rem;" <?php echo !empty($col['is_required']) ? 'required' : ''; ?>>
                            <option value="">-- Select --</option>
                            <option value="1" <?php echo ($saved_val === '1') ? 'selected' : ''; ?>><?php echo $opt1; ?></option>
                            <option value="0" <?php echo ($saved_val === '0') ? 'selected' : ''; ?>><?php echo $opt2; ?></option>
                        </select>

                    <?php elseif ($subtype === 'email'): ?>
                        <input type="email" id="field_<?php echo $col['id']; ?>" name="fields[<?php echo $col['id']; ?>]" value="<?php echo htmlspecialchars($saved_val); ?>" class="dashboard-input" style="width:100%; padding:0.4rem;" <?php echo $max_attr; ?> <?php echo !empty($col['is_required']) ? 'required' : ''; ?>>

                    <?php elseif ($subtype === 'url'): ?>
                        <input type="url" id="field_<?php echo $col['id']; ?>" name="fields[<?php echo $col['id']; ?>]" value="<?php echo htmlspecialchars($saved_val); ?>" class="dashboard-input" style="width:100%; padding:0.4rem;" <?php echo $max_attr; ?> <?php echo !empty($col['is_required']) ? 'required' : ''; ?>>

                    <?php elseif ($subtype === 'number'): ?>
                        <input type="number" id="field_<?php echo $col['id']; ?>" name="fields[<?php echo $col['id']; ?>]" value="<?php echo htmlspecialchars($saved_val); ?>" class="dashboard-input" style="width:100%; padding:0.4rem;" <?php echo !empty($col['is_required']) ? 'required' : ''; ?>>

                    <?php elseif ($subtype === 'textarea'): ?>
                        <textarea id="field_<?php echo $col['id']; ?>" name="fields[<?php echo $col['id']; ?>]" rows="3" class="dashboard-input auto-expand-textarea" style="width:100%; padding:0.4rem; resize:vertical; overflow:hidden;" <?php echo $max_attr; ?> <?php echo !empty($col['is_required']) ? 'required' : ''; ?>><?php echo htmlspecialchars($saved_val); ?></textarea>

                    <?php elseif ($subtype === 'select' || $subtype === 'dropdown'): ?>
                        <?php $selected_vals = $allow_multi ? (is_array($saved_val) ? $saved_val : explode(', ', $saved_val)) : [$saved_val]; ?>
                        <select id="field_<?php echo $col['id']; ?>" name="fields[<?php echo $col['id']; ?><?php echo $allow_multi ? '[]' : ''; ?>]" class="dashboard-input" style="width:100%; padding:0.4rem;" <?php echo $allow_multi ? 'multiple size="4"' : ''; ?> <?php echo !empty($col['is_required']) ? 'required' : ''; ?>>
                            <?php if (!$allow_multi): ?><option value="">-- Select --</option><?php endif; ?>
                            <?php foreach ($options as $opt): ?>
                                <option value="<?php echo htmlspecialchars($opt); ?>" <?php echo in_array($opt, $selected_vals) ? 'selected' : ''; ?>><?php echo htmlspecialchars($opt); ?></option>
                            <?php endforeach; ?>
                        </select>
                        <?php if ($allow_multi): ?><small style="color:#666;">Hold Ctrl or Cmd to select multiple.</small><?php endif; ?>

                    <?php elseif ($subtype === 'checkbox' || ($subtype === 'radio' && $allow_multi)): ?>
                        <?php $selected_vals = is_array($saved_val) ? $saved_val : explode(', ', $saved_val); ?>
                        <?php foreach ($options as $opt): ?>
                            <label style="display: block; font-weight: normal; cursor: pointer; margin-bottom: 0.2rem;">
                                <input type="checkbox" name="fields[<?php echo $col['id']; ?>][]" value="<?php echo htmlspecialchars($opt); ?>" <?php echo in_array($opt, $selected_vals) ? 'checked' : ''; ?>> 
                                <?php echo htmlspecialchars($opt); ?>
                            </label>
                        <?php endforeach; ?>

                    <?php elseif ($subtype === 'radio'): ?>
                        <div style="display: flex; gap: 1rem; flex-wrap: wrap;">
                            <?php foreach ($options as $opt): ?>
                                <label style="font-weight: normal; cursor: pointer;">
                                    <input type="radio" name="fields[<?php echo $col['id']; ?>]" value="<?php echo htmlspecialchars($opt); ?>" <?php echo ($saved_val === $opt) ? 'checked' : ''; ?>> 
                                    <?php echo htmlspecialchars($opt); ?>
                                </label>
                            <?php endforeach; ?>
                        </div>

                    <?php else: ?>
                        <input type="text" id="field_<?php echo $col['id']; ?>" name="fields[<?php echo $col['id']; ?>]" value="<?php echo htmlspecialchars($saved_val); ?>" class="dashboard-input" style="width:100%; padding:0.4rem;" <?php echo $max_attr; ?> <?php echo !empty($col['is_required']) ? 'required' : ''; ?>>
                    <?php endif; ?>
                </div>
            <?php endforeach; ?>
            
            <button type="submit" class="btn" style="margin-top: 0.5rem;">Submit Ticket</button>
        <?php endif; ?>
    </form>
</div>

<script>
document.addEventListener('input', function (event) {
    if (event.target.classList.contains('auto-expand-textarea')) {
        event.target.style.height = 'auto';
        event.target.style.height = (event.target.scrollHeight) + 'px';
    }
});
</script>

<?php require_once 'partials/footer.php'; ?>
