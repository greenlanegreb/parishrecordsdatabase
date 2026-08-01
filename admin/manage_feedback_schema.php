<?php
// admin/manage_feedback_schema.php - Schema Management for Feedback Ticket Fields
require_once '../db/db.php';
require_once '../db/auth_helpers.php';
require_once '../includes/functions.php';

if (!is_module_enabled($pdo, 'feedback')) {
    http_response_code(403);
    exit('403 Forbidden: The Feedback Submissions module is currently disabled.');
}

$current_user = require_admin_page($pdo, 'manage_feedback', 'Manage feedback ticket schema definitions');
[$user_timezone, $full_format_str] = get_user_time_prefs($current_user);

// Fetch form title & intro settings
$settings_stmt = $pdo->query("SELECT setting_key, setting_value FROM feedback_form_settings");
$form_settings = [];
while ($row = $settings_stmt->fetch(PDO::FETCH_ASSOC)) {
    $form_settings[$row['setting_key']] = $row['setting_value'];
}
$form_title = $form_settings['form_title'] ?? 'Submit Support Ticket or Feedback';
$form_intro = $form_settings['form_intro'] ?? 'Fill out the form below to open a ticket with our team.';

$edit_col = null;
if (isset($_GET['edit_column'])) {
    $c_stmt = $pdo->prepare("SELECT * FROM feedback_columns WHERE id = ?");
    $c_stmt->execute([intval($_GET['edit_column'])]);
    $edit_col = $c_stmt->fetch(PDO::FETCH_ASSOC);
}

$columns = $pdo->query("SELECT fc.*, u.username FROM feedback_columns fc LEFT JOIN users u ON fc.created_by = u.id ORDER BY fc.sort_order ASC, fc.column_name ASC")->fetchAll(PDO::FETCH_ASSOC);
?>
<?php require_once '../partials/header.php'; ?>

<!-- Include SortableJS CDN for drag-and-drop row reordering -->
<script src="https://cdnjs.cloudflare.com/ajax/libs/Sortable/1.15.0/Sortable.min.js"></script>

<div class="search-box-container" style="max-width: 1100px; margin: 0 auto;">
    <h3><?php echo htmlspecialchars(__('feedback_schema.heading')); ?></h3>
    <p><?php echo htmlspecialchars(__('feedback_schema.subheading')); ?></p>

    <div style="margin-bottom: 1.5rem;">
        <a href="feedback_dashboard.php" class="btn btn-secondary" style="text-decoration: none;">← <?php echo htmlspecialchars(__('feedback_emails.back_to_dashboard')); ?></a>
    </div>

    <!-- Form Title & Introduction Settings Box -->
    <details class="search-box-container" style="margin-bottom: 2rem; background: rgba(0,0,0,0.01);">
        <summary style="cursor: pointer; font-weight: bold; font-size: 1.15rem; color: #333; padding: 0.25rem 0;">
            ✏️ <?php echo htmlspecialchars(__('feedback_schema.settings_summary')); ?>
        </summary>
        <div style="margin-top: 1rem; padding-top: 1rem; border-top: 1px solid var(--border-color);">
            <form method="POST" action="actions/save_feedback_schema.php">
                <?php echo csrf_field(); ?>
                <input type="hidden" name="action" value="update_settings">
                
                <label for="form_title"><?php echo htmlspecialchars(__('feedback_schema.form_title_label')); ?></label><br>
                <input type="text" id="form_title" name="form_title" value="<?php echo htmlspecialchars($form_title); ?>" class="volunteer-input" style="width: 100%; max-width: 600px; padding: 0.4rem; margin-bottom: 1rem;" required><br>

                <label for="form_intro"><?php echo htmlspecialchars(__('feedback_schema.form_intro_label')); ?></label><br>
                <textarea id="form_intro" name="form_intro" rows="3" class="volunteer-input" style="width: 100%; max-width: 600px; padding: 0.4rem; margin-bottom: 1rem; resize: vertical;" required><?php echo htmlspecialchars($form_intro); ?></textarea><br>

                <button type="submit" class="btn"><?php echo htmlspecialchars(__('feedback_schema.save_settings_btn')); ?></button>
            </form>
        </div>
    </details>

    <!-- Collapsible Column Form Container -->
    <details class="search-box-container" id="create-column-details" <?php echo $edit_col ? 'open' : ''; ?> style="margin-bottom: 2rem; background: rgba(0,0,0,0.01);">
        <summary style="cursor: pointer; font-weight: bold; font-size: 1.15rem; color: #333; padding: 0.25rem 0;">
            <?php echo $edit_col ? htmlspecialchars(__('feedback_schema.edit_field_title')) . ' ' . htmlspecialchars($edit_col['column_name']) : htmlspecialchars(__('feedback_schema.add_field_title')); ?>
        </summary>
        
        <div style="margin-top: 1rem; padding-top: 1rem; border-top: 1px solid var(--border-color);">
            <form method="POST" action="actions/save_feedback_schema.php">
                <?php echo csrf_field(); ?>
                <input type="hidden" name="action" value="<?php echo $edit_col ? 'update' : 'create'; ?>">
                <?php if ($edit_col): ?>
                    <input type="hidden" name="column_id" value="<?php echo $edit_col['id']; ?>">
                <?php endif; ?>
                
                <label for="column_name"><?php echo htmlspecialchars(__('feedback_schema.field_name_label')); ?> <span style="color: red;">*</span></label><br>
                <input type="text" id="column_name" name="column_name" value="<?php echo $edit_col ? htmlspecialchars($edit_col['column_name']) : ''; ?>" required class="volunteer-input" style="width: 100%; max-width: 400px; padding: 0.4rem; margin-bottom: 1rem;"><br>

                <label for="data_type"><?php echo htmlspecialchars(__('feedback_schema.data_type_label')); ?></label><br>
                <select id="data_type" name="data_type" class="volunteer-input" style="width: 100%; max-width: 400px; padding: 0.4rem; margin-bottom: 1rem;" onchange="updateSubtypeOptions(this.value)">
                    <option value="VARCHAR" <?php echo ($edit_col && $edit_col['data_type'] === 'VARCHAR') ? 'selected' : ''; ?>><?php echo htmlspecialchars(__('feedback_schema.type_varchar')); ?></option>
                    <option value="TEXT" <?php echo ($edit_col && $edit_col['data_type'] === 'TEXT') ? 'selected' : ''; ?>><?php echo htmlspecialchars(__('feedback_schema.type_text')); ?></option>
                    <option value="INT" <?php echo ($edit_col && $edit_col['data_type'] === 'INT') ? 'selected' : ''; ?>><?php echo htmlspecialchars(__('feedback_schema.type_int')); ?></option>
                    <option value="BOOLEAN" <?php echo ($edit_col && $edit_col['data_type'] === 'BOOLEAN') ? 'selected' : ''; ?>><?php echo htmlspecialchars(__('feedback_schema.type_boolean')); ?></option>
                    <option value="DATE" <?php echo ($edit_col && $edit_col['data_type'] === 'DATE') ? 'selected' : ''; ?>><?php echo htmlspecialchars(__('feedback_schema.type_date')); ?></option>
                </select><br>

                <!-- Sub-type Selector -->
                <div id="subtype_wrapper" style="margin-bottom: 1rem;">
                    <label for="field_subtype"><?php echo htmlspecialchars(__('feedback_schema.subtype_label')); ?></label><br>
                    <select id="field_subtype" name="field_subtype" class="volunteer-input" style="width: 100%; max-width: 400px; padding: 0.4rem;" onchange="toggleExtraFieldOptions()">
                        <option value=""><?php echo htmlspecialchars(__('feedback_schema.subtype_standard')); ?></option>
                    </select>
                </div>

                <!-- Options Input Box (Multi-line Textarea) -->
                <div id="field_options_wrapper" style="display: none; margin-bottom: 1rem;">
                    <label for="field_options"><?php echo htmlspecialchars(__('feedback_schema.options_label')); ?></label><br>
                    <textarea id="field_options" name="field_options" rows="4" placeholder="Low, Medium, High&#10;Urgent, Non-Urgent" class="volunteer-input" style="width: 100%; max-width: 400px; padding: 0.4rem; resize: vertical;"><?php echo $edit_col ? htmlspecialchars($edit_col['field_options'] ?? '') : ''; ?></textarea><br>
                    <small style="color: #666;"><?php echo htmlspecialchars(__('feedback_schema.options_help')); ?></small>
                </div>

                <!-- Allow Multiple Checkbox -->
                <div id="allow_multiple_wrapper" style="display: none; margin-bottom: 1rem; align-items: center; gap: 0.5rem;">
                    <input type="checkbox" id="allow_multiple" name="allow_multiple" value="1" <?php echo ($edit_col && !empty($edit_col['allow_multiple'])) ? 'checked' : ''; ?> style="cursor: pointer;">
                    <label for="allow_multiple" style="cursor: pointer; font-weight: normal; margin-bottom: 0;"><?php echo htmlspecialchars(__('feedback_schema.allow_multiple')); ?></label>
                </div>

                <div id="boolean_options_wrapper" style="display: <?php echo ($edit_col && $edit_col['data_type'] === 'BOOLEAN') ? 'block' : 'none'; ?>; margin-bottom: 1rem;">
                    <label for="boolean_display_format"><?php echo htmlspecialchars(__('feedback_schema.boolean_format')); ?></label><br>
                    <select id="boolean_display_format" name="boolean_display_format" class="volunteer-input" style="width: 100%; max-width: 400px; padding: 0.4rem;">
                        <option value="yes_no" <?php echo ($edit_col && ($edit_col['boolean_display_format'] ?? '') === 'yes_no') ? 'selected' : ''; ?>><?php echo htmlspecialchars(__('index.opt_yes_true')); ?></option>
                        <option value="true_false" <?php echo ($edit_col && ($edit_col['boolean_display_format'] ?? '') === 'true_false') ? 'selected' : ''; ?>><?php echo htmlspecialchars(__('index.opt_true')); ?></option>
                        <option value="tick_cross" <?php echo ($edit_col && ($edit_col['boolean_display_format'] ?? '') === 'tick_cross') ? 'selected' : ''; ?>><?php echo htmlspecialchars(__('index.opt_tick')); ?></option>
                    </select>
                </div>

                <label for="max_length"><?php echo htmlspecialchars(__('feedback_schema.max_length_label')); ?></label><br>
                <input type="number" id="max_length" name="max_length" value="<?php echo $edit_col ? htmlspecialchars($edit_col['max_length'] ?? '') : ''; ?>" placeholder="e.g. 255" class="volunteer-input" style="width: 100%; max-width: 400px; padding: 0.4rem; margin-bottom: 1rem;"><br>

                <div style="margin-bottom: 1rem; display: flex; align-items: center; gap: 0.5rem;">
                    <input type="checkbox" id="is_required" name="is_required" value="1" <?php echo ($edit_col && !empty($edit_col['is_required'])) ? 'checked' : ''; ?> style="cursor: pointer;">
                    <label for="is_required" style="cursor: pointer; font-weight: normal; margin-bottom: 0;"><?php echo htmlspecialchars(__('feedback_schema.is_required_label')); ?> (<span style="color:red;">*</span>)</label>
                </div>

                <button type="submit" class="btn"><?php echo $edit_col ? htmlspecialchars(__('feedback_schema.save_field_btn')) : htmlspecialchars(__('feedback_schema.create_field_btn')); ?></button>
                <?php if ($edit_col): ?>
                    <a href="manage_feedback_schema.php" class="btn btn-secondary" style="margin-left: 0.5rem; text-decoration: none;"><?php echo htmlspecialchars(__('btn.cancel')); ?></a>
                <?php endif; ?>
            </form>
        </div>
    </details>

    <script>
    function updateSubtypeOptions(dataType) {
        var subSelect = document.getElementById('field_subtype');
        var currentSubtype = "<?php echo $edit_col['field_subtype'] ?? ''; ?>";
        subSelect.innerHTML = '<option value=""><?php echo htmlspecialchars(__('feedback_schema.subtype_standard')); ?></option>';

        if (dataType === 'VARCHAR') {
            subSelect.innerHTML += '<option value="email"><?php echo htmlspecialchars(__('feedback_schema.sub_email')); ?></option><option value="url"><?php echo htmlspecialchars(__('feedback_schema.sub_url')); ?></option><option value="select"><?php echo htmlspecialchars(__('feedback_schema.sub_select')); ?></option><option value="radio"><?php echo htmlspecialchars(__('feedback_schema.sub_radio')); ?></option><option value="checkbox"><?php echo htmlspecialchars(__('feedback_schema.sub_checkbox')); ?></option>';
        } else if (dataType === 'TEXT') {
            subSelect.innerHTML += '<option value="textarea"><?php echo htmlspecialchars(__('feedback_schema.sub_textarea')); ?></option>';
        } else if (dataType === 'INT') {
            subSelect.innerHTML += '<option value="number"><?php echo htmlspecialchars(__('feedback_schema.sub_number')); ?></option>';
        }

        subSelect.value = currentSubtype;
        toggleExtraFieldOptions();
    }

    function toggleExtraFieldOptions() {
        var dtype = document.getElementById('data_type').value;
        var subtype = document.getElementById('field_subtype').value;
        var optWrapper = document.getElementById('field_options_wrapper');
        var multiWrapper = document.getElementById('allow_multiple_wrapper');
        var boolWrapper = document.getElementById('boolean_options_wrapper');

        boolWrapper.style.display = (dtype === 'BOOLEAN') ? 'block' : 'none';

        if (['select', 'dropdown', 'radio', 'checkbox'].includes(subtype)) {
            optWrapper.style.display = 'block';
            multiWrapper.style.display = 'flex';
        } else {
            optWrapper.style.display = 'none';
            multiWrapper.style.display = 'none';
        }
    }

    document.addEventListener('DOMContentLoaded', function() {
        updateSubtypeOptions(document.getElementById('data_type').value);
    });
    </script>

    <hr style="border: 0.0625rem solid var(--border-color); margin: 1.5rem 0;">

    <h3><?php echo htmlspecialchars(__('feedback_schema.existing_fields_heading')); ?></h3>
    <div style="overflow-x: auto;">
        <table class="data-table" role="table" style="width: 100%; border-collapse: collapse;">
            <thead>
                <tr>
                    <th scope="col" style="width: 50px; text-align: center;"><?php echo htmlspecialchars(__('feedback_schema.th_move')); ?></th>
                    <th scope="col"><?php echo htmlspecialchars(__('feedback_schema.th_field_name')); ?></th>
                    <th scope="col"><?php echo htmlspecialchars(__('feedback_schema.th_data_type')); ?></th>
                    <th scope="col"><?php echo htmlspecialchars(__('feedback_schema.th_subtype')); ?></th>
                    <th scope="col"><?php echo htmlspecialchars(__('feedback_schema.th_required')); ?></th>
                    <th scope="col"><?php echo htmlspecialchars(__('feedback_schema.th_max_length')); ?></th>
                    <th scope="col"><?php echo htmlspecialchars(__('feedback_schema.th_created_by')); ?></th>
                    <th scope="col"><?php echo htmlspecialchars(__('index.th_actions')); ?></th>
                </tr>
            </thead>
            <tbody id="sortable-columns-body">
                <?php if (empty($columns)): ?>
                    <tr><td colspan="8" style="text-align: center; color: #666; padding: 1rem;"><?php echo htmlspecialchars(__('feedback_schema.no_fields')); ?></td></tr>
                <?php else: ?>
                    <?php foreach ($columns as $col): ?>
                        <tr data-column-id="<?php echo $col['id']; ?>" style="cursor: grab;">
                            <td style="text-align: center; color: #888; font-size: 1.2rem;" title="Drag to reorder">☰</td>
                            <td><strong><?php echo htmlspecialchars($col['column_name']); ?></strong></td>
                            <td><code><?php echo htmlspecialchars($col['data_type']); ?></code></td>
                            <td><code><?php echo htmlspecialchars($col['field_subtype'] ?: __('feedback_schema.subtype_standard_lower')); ?></code></td>
                            <td><?php echo !empty($col['is_required']) ? '<span style="color: green; font-weight: bold;">Yes</span>' : '<span style="color: gray;">No</span>'; ?></td>
                            <td><?php echo !empty($col['max_length']) ? (int)$col['max_length'] : 'N/A'; ?></td>
                            <td><?php echo htmlspecialchars($col['username'] ?? __('feedback_schema.system_user')); ?></td>
                            <td style="white-space: nowrap;">
                                <a href="manage_feedback_schema.php?edit_column=<?php echo $col['id']; ?>#create-column-details" class="btn btn-secondary" style="padding: 0.25rem 0.5rem; font-size: 0.85rem; text-decoration: none; margin-right: 4px;"><?php echo htmlspecialchars(__('btn.save')); // using save or generic edit if available, let's create edit key ?>Edit</a>
                                <form method="POST" action="actions/save_feedback_schema.php" style="display:inline;" onsubmit="return confirm('<?php echo htmlspecialchars(__('feedback_schema.delete_confirm')); ?>');">
                                    <?php echo csrf_field(); ?>
                                    <input type="hidden" name="action" value="delete">
                                    <input type="hidden" name="column_id" value="<?php echo $col['id']; ?>">
                                    <button type="submit" class="btn btn-danger" style="padding: 0.25rem 0.5rem; font-size: 0.85rem;"><?php echo htmlspecialchars(__('btn.delete')); ?></button>
                                </form>
                            </td>
                        </tr>
                    <?php endforeach; ?>
                <?php endif; ?>
            </tbody>
        </table>
    </div>

    <?php if (!empty($columns)): ?>
    <script>
    document.addEventListener('DOMContentLoaded', function () {
        var tbody = document.getElementById('sortable-columns-body');
        if (tbody) {
            Sortable.create(tbody, {
                animation: 150,
                ghostClass: 'sortable-ghost',
                onEnd: function () {
                    var rows = tbody.querySelectorAll('tr[data-column-id]');
                    var sortOrders = {};
                    rows.forEach(function (row, index) {
                        sortOrders[row.getAttribute('data-column-id')] = index + 1;
                    });
                    var formData = new URLSearchParams();
                    formData.append('action', 'update_order_batch');
                    formData.append('csrf_token', '<?php echo generate_csrf_token(); ?>');
                    for (var id in sortOrders) {
                        formData.append('sort_orders[' + id + ']', sortOrders[id]);
                    }
                    fetch('actions/save_feedback_schema.php', { method: 'POST', body: formData });
                }
            });
        }
    });
    </script>
    <style>.sortable-ghost { opacity: 0.4; background: #f0f0f0 !important; }</style>
    <?php endif; ?>
</div>

<?php require_once '../partials/footer.php'; ?>
