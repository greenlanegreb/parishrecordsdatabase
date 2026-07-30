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
    <h3>Feedback Form Schema Management</h3>
    <p>Configure custom fields, data types, character limits, sub-types, and options for incoming feedback tickets.</p>

    <div style="margin-bottom: 1.5rem;">
        <a href="feedback_dashboard.php" class="btn btn-secondary" style="text-decoration: none;">← Back to Feedback Tickets Dashboard</a>
    </div>

    <!-- Collapsible Column Form Container -->
    <details class="search-box-container" id="create-column-details" <?php echo $edit_col ? 'open' : ''; ?> style="margin-bottom: 2rem; background: rgba(0,0,0,0.01);">
        <summary style="cursor: pointer; font-weight: bold; font-size: 1.15rem; color: #333; padding: 0.25rem 0;">
            <?php echo $edit_col ? 'Edit Ticket Field: ' . htmlspecialchars($edit_col['column_name']) : '+ Add New Ticket Form Field'; ?>
        </summary>
        
        <div style="margin-top: 1rem; padding-top: 1rem; border-top: 1px solid var(--border-color);">
            <form method="POST" action="actions/save_feedback_schema.php">
                <?php echo csrf_field(); ?>
                <input type="hidden" name="action" value="<?php echo $edit_col ? 'update' : 'create'; ?>">
                <?php if ($edit_col): ?>
                    <input type="hidden" name="column_id" value="<?php echo $edit_col['id']; ?>">
                <?php endif; ?>
                
                <label for="column_name">Field Label / Name: <span style="color: red;">*</span></label><br>
                <input type="text" id="column_name" name="column_name" value="<?php echo $edit_col ? htmlspecialchars($edit_col['column_name']) : ''; ?>" required class="volunteer-input" style="width: 100%; max-width: 400px; padding: 0.4rem; margin-bottom: 1rem;"><br>

                <label for="data_type">Data Type:</label><br>
                <select id="data_type" name="data_type" class="volunteer-input" style="width: 100%; max-width: 400px; padding: 0.4rem; margin-bottom: 1rem;" onchange="updateSubtypeOptions(this.value)">
                    <option value="VARCHAR" <?php echo ($edit_col && $edit_col['data_type'] === 'VARCHAR') ? 'selected' : ''; ?>>VARCHAR (Short Text)</option>
                    <option value="TEXT" <?php echo ($edit_col && $edit_col['data_type'] === 'TEXT') ? 'selected' : ''; ?>>TEXT (Long Paragraph / Message)</option>
                    <option value="INT" <?php echo ($edit_col && $edit_col['data_type'] === 'INT') ? 'selected' : ''; ?>>INT (Whole Number)</option>
                    <option value="BOOLEAN" <?php echo ($edit_col && $edit_col['data_type'] === 'BOOLEAN') ? 'selected' : ''; ?>>BOOLEAN (Yes/No Flag)</option>
                    <option value="DATE" <?php echo ($edit_col && $edit_col['data_type'] === 'DATE') ? 'selected' : ''; ?>>DATE (Calendar Date)</option>
                </select><br>

                <!-- Sub-type Selector -->
                <div id="subtype_wrapper" style="margin-bottom: 1rem;">
                    <label for="field_subtype">Field Sub-type / Input Render Style:</label><br>
                    <select id="field_subtype" name="field_subtype" class="volunteer-input" style="width: 100%; max-width: 400px; padding: 0.4rem;" onchange="toggleExtraFieldOptions()">
                        <option value="">-- Standard --</option>
                    </select>
                </div>

                <!-- Options Input Box (Multi-line Textarea) -->
                <div id="field_options_wrapper" style="display: none; margin-bottom: 1rem;">
                    <label for="field_options">Options (comma-separated or one per line):</label><br>
                    <textarea id="field_options" name="field_options" rows="4" placeholder="Low, Medium, High&#10;Urgent, Non-Urgent" class="volunteer-input" style="width: 100%; max-width: 400px; padding: 0.4rem; resize: vertical;"><?php echo $edit_col ? htmlspecialchars($edit_col['field_options'] ?? '') : ''; ?></textarea><br>
                    <small style="color: #666;">Provide choices separated by commas or line breaks.</small>
                </div>

                <!-- Allow Multiple Checkbox -->
                <div id="allow_multiple_wrapper" style="display: none; margin-bottom: 1rem; align-items: center; gap: 0.5rem;">
                    <input type="checkbox" id="allow_multiple" name="allow_multiple" value="1" <?php echo ($edit_col && !empty($edit_col['allow_multiple'])) ? 'checked' : ''; ?> style="cursor: pointer;">
                    <label for="allow_multiple" style="cursor: pointer; font-weight: normal; margin-bottom: 0;">Allow selecting multiple options (Multi-select)</label>
                </div>

                <div id="boolean_options_wrapper" style="display: <?php echo ($edit_col && $edit_col['data_type'] === 'BOOLEAN') ? 'block' : 'none'; ?>; margin-bottom: 1rem;">
                    <label for="boolean_display_format">Boolean Display Format:</label><br>
                    <select id="boolean_display_format" name="boolean_display_format" class="volunteer-input" style="width: 100%; max-width: 400px; padding: 0.4rem;">
                        <option value="yes_no" <?php echo ($edit_col && ($edit_col['boolean_display_format'] ?? '') === 'yes_no') ? 'selected' : ''; ?>>Yes / No</option>
                        <option value="true_false" <?php echo ($edit_col && ($edit_col['boolean_display_format'] ?? '') === 'true_false') ? 'selected' : ''; ?>>True / False</option>
                        <option value="tick_cross" <?php echo ($edit_col && ($edit_col['boolean_display_format'] ?? '') === 'tick_cross') ? 'selected' : ''; ?>>Tick / Cross (✔ / ✘)</option>
                    </select>
                </div>

                <label for="max_length">Max Size / Length (Optional character limit):</label><br>
                <input type="number" id="max_length" name="max_length" value="<?php echo $edit_col ? htmlspecialchars($edit_col['max_length'] ?? '') : ''; ?>" placeholder="e.g. 255" class="volunteer-input" style="width: 100%; max-width: 400px; padding: 0.4rem; margin-bottom: 1rem;"><br>

                <div style="margin-bottom: 1rem; display: flex; align-items: center; gap: 0.5rem;">
                    <input type="checkbox" id="is_required" name="is_required" value="1" <?php echo ($edit_col && !empty($edit_col['is_required'])) ? 'checked' : ''; ?> style="cursor: pointer;">
                    <label for="is_required" style="cursor: pointer; font-weight: normal; margin-bottom: 0;">Make this field mandatory for submitters (<span style="color:red;">*</span>)</label>
                </div>

                <button type="submit" class="btn"><?php echo $edit_col ? 'Save Field Changes' : 'Create Ticket Field'; ?></button>
                <?php if ($edit_col): ?>
                    <a href="manage_feedback_schema.php" class="btn btn-secondary" style="margin-left: 0.5rem; text-decoration: none;">Cancel</a>
                <?php endif; ?>
            </form>
        </div>
    </details>

    <script>
    function updateSubtypeOptions(dataType) {
        var subSelect = document.getElementById('field_subtype');
        var currentSubtype = "<?php echo $edit_col['field_subtype'] ?? ''; ?>";
        subSelect.innerHTML = '<option value="">-- Standard --</option>';

        if (dataType === 'VARCHAR') {
            subSelect.innerHTML += '<option value="email">Email</option><option value="url">URL</option><option value="select">Dropdown Select</option><option value="radio">Radio Group</option><option value="checkbox">Checkboxes</option>';
        } else if (dataType === 'TEXT') {
            subSelect.innerHTML += '<option value="textarea">Paragraph Box</option>';
        } else if (dataType === 'INT') {
            subSelect.innerHTML += '<option value="number">Number Input</option>';
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

    <h3>Existing Ticket Fields</h3>
    <div style="overflow-x: auto;">
        <table class="data-table" role="table" style="width: 100%; border-collapse: collapse;">
            <thead>
                <tr>
                    <th scope="col" style="width: 50px; text-align: center;">Move</th>
                    <th scope="col">Field Name</th>
                    <th scope="col">Data Type</th>
                    <th scope="col">Sub-type</th>
                    <th scope="col">Required?</th>
                    <th scope="col">Max Length</th>
                    <th scope="col">Created By</th>
                    <th scope="col">Actions</th>
                </tr>
            </thead>
            <tbody id="sortable-columns-body">
                <?php if (empty($columns)): ?>
                    <tr><td colspan="8" style="text-align: center; color: #666; padding: 1rem;">No custom ticket fields defined yet.</td></tr>
                <?php else: ?>
                    <?php foreach ($columns as $col): ?>
                        <tr data-column-id="<?php echo $col['id']; ?>" style="cursor: grab;">
                            <td style="text-align: center; color: #888; font-size: 1.2rem;" title="Drag to reorder">☰</td>
                            <td><strong><?php echo htmlspecialchars($col['column_name']); ?></strong></td>
                            <td><code><?php echo htmlspecialchars($col['data_type']); ?></code></td>
                            <td><code><?php echo htmlspecialchars($col['field_subtype'] ?: 'standard'); ?></code></td>
                            <td><?php echo !empty($col['is_required']) ? '<span style="color: green; font-weight: bold;">Yes</span>' : '<span style="color: gray;">No</span>'; ?></td>
                            <td><?php echo !empty($col['max_length']) ? (int)$col['max_length'] : 'N/A'; ?></td>
                            <td><?php echo htmlspecialchars($col['username'] ?? 'System'); ?></td>
                            <td style="white-space: nowrap;">
                                <a href="manage_feedback_schema.php?edit_column=<?php echo $col['id']; ?>#create-column-details" class="btn btn-secondary" style="padding: 0.25rem 0.5rem; font-size: 0.85rem; text-decoration: none; margin-right: 4px;">Edit</a>
                                <form method="POST" action="actions/save_feedback_schema.php" style="display:inline;" onsubmit="return confirm('Delete this field and all associated response values?');">
                                    <?php echo csrf_field(); ?>
                                    <input type="hidden" name="action" value="delete">
                                    <input type="hidden" name="column_id" value="<?php echo $col['id']; ?>">
                                    <button type="submit" class="btn btn-danger" style="padding: 0.25rem 0.5rem; font-size: 0.85rem;">Delete</button>
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
