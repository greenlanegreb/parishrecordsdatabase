<?php
// volunteer.php - Public Volunteer Interest Submission Form View
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}
require_once 'db/db.php';
require_once 'db/auth_helpers.php';
require_once 'includes/functions.php';
require_once 'includes/security_engine.php';

if (!is_module_enabled($pdo, 'volunteers')) {
    http_response_code(403);
    exit('403 Forbidden: The Volunteer Portal module is currently disabled.');
}

$current_user = function_exists('get_current_user_data') ? get_current_user_data($pdo) : null;
$has_guest_permission = guest_has_permission($pdo, 'submit_volunteer');

if (!$current_user && !$has_guest_permission) {
    $current_user = require_permission($pdo, 'submit_volunteer', 'Allows submitting volunteer interest and transcription applications');
}

$system_name = get_system_name($pdo);

// Fetch admin-configured form title and introduction
$settings_stmt = $pdo->query("SELECT setting_key, setting_value FROM volunteer_form_settings");
$form_settings = [];
while ($row = $settings_stmt->fetch(PDO::FETCH_ASSOC)) {
    $form_settings[$row['setting_key']] = $row['setting_value'];
}
$form_title = $form_settings['form_title'] ?? 'Volunteer for Data Entry';
$form_intro = $form_settings['form_intro'] ?? 'Interested in helping transcribe and contribute? Let us know a little about yourself and any relevant experience.';

$columns_stmt = $pdo->query("SELECT * FROM volunteer_columns ORDER BY sort_order ASC, column_name ASC");
$columns = $columns_stmt->fetchAll(PDO::FETCH_ASSOC);

$message = $_SESSION['message'] ?? '';
$error = $_SESSION['error'] ?? '';
$submitted_data = $_SESSION['submitted_volunteer_fields'] ?? [];
$submitted_first = $_SESSION['submitted_volunteer_first'] ?? '';
$submitted_surname = $_SESSION['submitted_volunteer_surname'] ?? '';
$submitted_email = $_SESSION['submitted_volunteer_email'] ?? '';

unset($_SESSION['message'], $_SESSION['error'], $_SESSION['submitted_volunteer_fields'], $_SESSION['submitted_volunteer_first'], $_SESSION['submitted_volunteer_surname'], $_SESSION['submitted_volunteer_email']);
?>
<?php require_once 'partials/header.php'; ?>

<div class="search-box-container volunteer-container" role="region" aria-label="<?php echo htmlspecialchars(__('volunteer.aria_region')); ?>" style="max-width: 600px; margin: 2rem auto;">
    <h3><?php echo htmlspecialchars($form_title); ?></h3>
    <p><?php echo nl2br(htmlspecialchars($form_intro)); ?></p>

    <?php if (!empty($error)): ?>
        <p class="alert-danger" role="alert"><strong><?php echo htmlspecialchars($error); ?></strong></p>
    <?php endif; ?>
    <?php if (!empty($message)): ?>
        <p class="alert-success" role="status"><strong><?php echo htmlspecialchars($message); ?></strong></p>
    <?php endif; ?>

    <form method="POST" action="user/actions/save_public_volunteer.php">
        <?php echo csrf_field(); ?>
        
        <div class="honeypot-field" aria-hidden="true" style="display:none;">
            <label for="website_url"><?php echo htmlspecialchars(__('volunteer.honeypot_label')); ?></label>
            <input type="text" id="website_url" name="website_url" value="" autocomplete="off" tabindex="-1">
        </div>

        <!-- First Name & Surname Static Core Fields -->
        <div style="display: flex; gap: 1rem; margin-bottom: 1rem;">
            <div style="flex: 1;">
                <label for="volunteer_first_name"><strong><?php echo htmlspecialchars(__('feedback.first_name_label')); ?></strong> <span style="color:red; font-weight:bold;">*</span></label><br>
                <input type="text" id="volunteer_first_name" name="volunteer_first_name" value="<?php echo htmlspecialchars($submitted_first); ?>" required class="volunteer-input" style="width:100%; padding:0.4rem;">
            </div>
            <div style="flex: 1;">
                <label for="volunteer_surname"><strong><?php echo htmlspecialchars(__('feedback.surname_label')); ?></strong> <span style="color:red; font-weight:bold;">*</span></label><br>
                <input type="text" id="volunteer_surname" name="volunteer_surname" value="<?php echo htmlspecialchars($submitted_surname); ?>" required class="volunteer-input" style="width:100%; padding:0.4rem;">
            </div>
        </div>

        <div style="margin-bottom: 1rem;">
            <label for="volunteer_email"><strong><?php echo htmlspecialchars(__('forgot_password.email_label')); ?></strong> <span style="color:red; font-weight:bold;">*</span></label><br>
            <input type="email" id="volunteer_email" name="volunteer_email" value="<?php echo htmlspecialchars($submitted_email); ?>" required class="volunteer-input" style="width:100%; padding:0.4rem;">
        </div>

        <hr style="border: 0; border-top: 1px solid #ddd; margin: 1.5rem 0;">

        <!-- Dynamic Custom Fields -->
        <?php if (!empty($columns)): ?>
            <?php foreach ($columns as $col): 
                $saved_val = $submitted_data[$col['id']] ?? '';
                $max_attr = !empty($col['max_length']) ? 'maxlength="' . intval($col['max_length']) . '"' : '';
                $subtype = $col['field_subtype'] ?? '';
                $options = array_filter(array_map('trim', explode(',', $col['field_options'] ?? '')));
                $allow_multi = !empty($col['allow_multiple']);
                $is_field_required = (!empty($col['is_required']) && !($allow_multi || $subtype === 'checkbox'));
            ?>
                <div style="margin-bottom: 1rem;">
                    <label for="field_<?php echo $col['id']; ?>">
                        <strong><?php echo htmlspecialchars($col['column_name']); ?>:</strong>
                        <?php if (!empty($col['is_required'])): ?><span style="color:red; font-weight:bold;" title="<?php echo htmlspecialchars(__('volunteer.required_field_title')); ?>">*</span><?php endif; ?>
                    </label><br>

                    <?php if (($col['data_type'] ?? '') === 'BOOLEAN'): ?>
                        <?php 
                            $fmt = $col['boolean_display_format'] ?? 'yes_no';
                            $opt1 = ($fmt === 'true_false') ? __('data_entry.bool_true') : __('data_entry.bool_yes_true');
                            $opt2 = ($fmt === 'true_false') ? __('data_entry.bool_false') : __('data_entry.bool_no_false');
                        ?>
                        <select id="field_<?php echo $col['id']; ?>" name="fields[<?php echo $col['id']; ?>]" class="volunteer-input" style="width:100%; padding:0.4rem;" <?php echo $is_field_required ? 'required' : ''; ?>>
                            <option value=""><?php echo htmlspecialchars(__('feedback.select_placeholder')); ?></option>
                            <option value="1" <?php echo ($saved_val === '1') ? 'selected' : ''; ?>><?php echo $opt1; ?></option>
                            <option value="0" <?php echo ($saved_val === '0') ? 'selected' : ''; ?>><?php echo $opt2; ?></option>
                        </select>

                    <?php elseif ($subtype === 'email'): ?>
                        <input type="email" id="field_<?php echo $col['id']; ?>" name="fields[<?php echo $col['id']; ?>]" value="<?php echo htmlspecialchars($saved_val); ?>" class="volunteer-input" style="width:100%; padding:0.4rem;" <?php echo $max_attr; ?> <?php echo $is_field_required ? 'required' : ''; ?>>

                    <?php elseif ($subtype === 'url'): ?>
                        <input type="url" id="field_<?php echo $col['id']; ?>" name="fields[<?php echo $col['id']; ?>]" value="<?php echo htmlspecialchars($saved_val); ?>" class="volunteer-input" style="width:100%; padding:0.4rem;" <?php echo $max_attr; ?> <?php echo $is_field_required ? 'required' : ''; ?>>

                    <?php elseif ($subtype === 'number'): ?>
                        <input type="number" id="field_<?php echo $col['id']; ?>" name="fields[<?php echo $col['id']; ?>]" value="<?php echo htmlspecialchars($saved_val); ?>" class="volunteer-input" style="width:100%; padding:0.4rem;" <?php echo $is_field_required ? 'required' : ''; ?>>

                    <?php elseif ($subtype === 'textarea'): ?>
                        <textarea id="field_<?php echo $col['id']; ?>" name="fields[<?php echo $col['id']; ?>]" rows="3" class="volunteer-textarea auto-expand-textarea" style="width:100%; padding:0.4rem; resize:vertical; overflow:hidden;" <?php echo $max_attr; ?> <?php echo $is_field_required ? 'required' : ''; ?>><?php echo htmlspecialchars($saved_val); ?></textarea>

                    <?php elseif ($subtype === 'select' || $subtype === 'dropdown'): ?>
                        <?php $selected_vals = $allow_multi ? (is_array($saved_val) ? $saved_val : explode(', ', $saved_val)) : [$saved_val]; ?>
                        <select id="field_<?php echo $col['id']; ?>" name="fields[<?php echo $col['id']; ?>]<?php echo $allow_multi ? '[]' : ''; ?>" class="volunteer-input" style="width:100%; padding:0.4rem;" <?php echo $allow_multi ? 'multiple size="4"' : ''; ?>>
                            <?php if (!$allow_multi): ?><option value=""><?php echo htmlspecialchars(__('feedback.select_placeholder')); ?></option><?php endif; ?>
                            <?php foreach ($options as $opt): ?>
                                <option value="<?php echo htmlspecialchars($opt); ?>" <?php echo in_array($opt, $selected_vals) ? 'selected' : ''; ?>><?php echo htmlspecialchars($opt); ?></option>
                            <?php endforeach; ?>
                        </select>
                        <?php if ($allow_multi): ?><small style="color:#666;"><?php echo htmlspecialchars(__('volunteer.multi_select_hint')); ?></small><?php endif; ?>

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
                        <input type="text" id="field_<?php echo $col['id']; ?>" name="fields[<?php echo $col['id']; ?>]" value="<?php echo htmlspecialchars($saved_val); ?>" class="volunteer-input" style="width:100%; padding:0.4rem;" <?php echo $max_attr; ?> <?php echo $is_field_required ? 'required' : ''; ?>>
                    <?php endif; ?>
                </div>
            <?php endforeach; ?>
        <?php endif; ?>
        
        <!-- Dynamic CAPTCHA Widget -->
        <?php echo render_form_captcha_widget($pdo); ?>

        <button type="submit" class="btn" style="margin-top: 1rem;"><?php echo htmlspecialchars(__('volunteer.submit_btn')); ?></button>
    </form>
</div>

<?php require_once 'partials/footer.php'; ?>
