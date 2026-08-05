<?php
/**
 * MIGRATED FILE MAPPING
 * ---------------------
 * Original Old File: admin/manage_volunteer_schema.php/admin/actions/save_volunteer_schema.php
 * Migrated Date: 2026-08-05 03:32:40
 */declare(strict_types=1);

/**
 * MIGRATED FILE MAPPING
 * ---------------------
 * Original Old File: admin/manage_volunteer_schema.php
 * Migrated Date: 2026-08-04 09:55:12
 */

/** @string $message */
/** @string $error */
/** @string $formTitle */
/** @string $formIntro */
/** @array<string, mixed>|false $editCol */
/** @array<int, array<string, mixed>> $columns */

require_once __DIR__ . '/../partials/header.php';
?>

<script src="https://cdnjs.cloudflare.com/ajax/libs/Sortable/1.15.0/Sortable.min.js"></script>

<div class="container py-4" style="max-width: 1100px;">
    <h3 class="fw-bold mb-1"><?= htmlspecialchars(__('volunteer_schema.heading'), ENT_QUOTES, 'UTF-8') ?></h3>
    <p class="text-muted mb-3"><?= htmlspecialchars(__('volunteer_schema.subheading'), ENT_QUOTES, 'UTF-8') ?></p>

    <div class="mb-4">
        <a href="/admin/volunteers" class="btn btn-outline-secondary">← <?= htmlspecialchars(__('volunteer_schema.back_to_dashboard'), ENT_QUOTES, 'UTF-8') ?></a>
    </div>

    <!-- Feedback Alerts -->
    <?php if (!empty($message)): ?>
        <div class="alert alert-success alert-dismissible fade show" role="alert">
            <strong><?= htmlspecialchars($message, ENT_QUOTES, 'UTF-8') ?></strong>
            <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
        </div>
    <?php endif; ?>
    <?php if (!empty($error)): ?>
        <div class="alert alert-danger alert-dismissible fade show" role="alert">
            <strong><?= htmlspecialchars($error, ENT_QUOTES, 'UTF-8') ?></strong>
            <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
        </div>
    <?php endif; ?>

    <!-- Form Title & Introduction Settings Box -->
    <div class="card shadow-sm border-0 mb-4">
        <div class="card-body">
            <details>
                <summary class="fw-bold fs-5 text-dark" style="cursor: pointer;">
                    ✏️ <?= htmlspecialchars(__('volunteer_schema.settings_summary'), ENT_QUOTES, 'UTF-8') ?>
                </summary>
                <div class="mt-3 pt-3 border-top">
                    <form method="POST" action="/admin/volunteers/schema/store">
                        <?= csrf_field() ?>
                        <input type="hidden" name="action" value="update_settings">
                        
                        <div class="mb-3">
                            <label for="form_title" class="form-label fw-bold"><?= htmlspecialchars(__('feedback_schema.form_title_label'), ENT_QUOTES, 'UTF-8') ?></label>
                            <input type="text" id="form_title" name="form_title" value="<?= htmlspecialchars($formTitle, ENT_QUOTES, 'UTF-8') ?>" class="form-control max-width-600" required>
                        </div>

                        <div class="mb-3">
                            <label for="form_intro" class="form-label fw-bold"><?= htmlspecialchars(__('feedback_schema.form_intro_label'), ENT_QUOTES, 'UTF-8') ?></label>
                            <textarea id="form_intro" name="form_intro" rows="3" class="form-control max-width-600" required><?= htmlspecialchars($formIntro, ENT_QUOTES, 'UTF-8') ?></textarea>
                        </div>

                        <button type="submit" class="btn btn-primary"><?= htmlspecialchars(__('feedback_schema.save_settings_btn'), ENT_QUOTES, 'UTF-8') ?></button>
                    </form>
                </div>
            </details>
        </div>
    </div>

    <!-- Create/Edit Column Form Container -->
    <div class="card shadow-sm border-0 mb-4">
        <div class="card-body">
            <details id="create-column-details" <?= $editCol ? 'open' : '' ?>>
                <summary class="fw-bold fs-5 text-dark" style="cursor: pointer;">
                    <?= $editCol ? htmlspecialchars(__('volunteer_schema.edit_field_title'), ENT_QUOTES, 'UTF-8') . ' ' . htmlspecialchars((string)($editCol['column_name'] ?? ''), ENT_QUOTES, 'UTF-8') : htmlspecialchars(__('volunteer_schema.add_field_title'), ENT_QUOTES, 'UTF-8') ?>
                </summary>
                
                <div class="mt-3 pt-3 border-top">
                    <form method="POST" action="/admin/volunteers/schema/store">
                        <?= csrf_field() ?>
                        <input type="hidden" name="action" value="<?= $editCol ? 'update' : 'create' ?>">
                        <?php if ($editCol): ?>
                            <input type="hidden" name="column_id" value="<?= (int)($editCol['id'] ?? 0) ?>">
                        <?php endif; ?>
                        
                        <div class="mb-3">
                            <label for="column_name" class="form-label fw-bold"><?= htmlspecialchars(__('feedback_schema.field_name_label'), ENT_QUOTES, 'UTF-8') ?> <span class="text-danger">*</span></label>
                            <input type="text" id="column_name" name="column_name" value="<?= $editCol ? htmlspecialchars((string)($editCol['column_name'] ?? ''), ENT_QUOTES, 'UTF-8') : '' ?>" required class="form-control max-width-400">
                        </div>

                        <div class="mb-3">
                            <label for="data_type" class="form-label fw-bold"><?= htmlspecialchars(__('feedback_schema.data_type_label'), ENT_QUOTES, 'UTF-8') ?></label>
                            <select id="data_type" name="data_type" class="form-select max-width-400" onchange="updateSubtypeOptions(this.value)">
                                <option value="VARCHAR" <?= ($editCol && ($editCol['data_type'] ?? '') === 'VARCHAR') ? 'selected' : '' ?>><?= htmlspecialchars(__('feedback_schema.type_varchar'), ENT_QUOTES, 'UTF-8') ?></option>
                                <option value="TEXT" <?= ($editCol && ($editCol['data_type'] ?? '') === 'TEXT') ? 'selected' : '' ?>><?= htmlspecialchars(__('feedback_schema.type_text'), ENT_QUOTES, 'UTF-8') ?></option>
                                <option value="INT" <?= ($editCol && ($editCol['data_type'] ?? '') === 'INT') ? 'selected' : '' ?>><?= htmlspecialchars(__('feedback_schema.type_int'), ENT_QUOTES, 'UTF-8') ?></option>
                                <option value="BOOLEAN" <?= ($editCol && ($editCol['data_type'] ?? '') === 'BOOLEAN') ? 'selected' : '' ?>><?= htmlspecialchars(__('feedback_schema.type_boolean'), ENT_QUOTES, 'UTF-8') ?></option>
                                <option value="DATE" <?= ($editCol && ($editCol['data_type'] ?? '') === 'DATE') ? 'selected' : '' ?>><?= htmlspecialchars(__('feedback_schema.type_date'), ENT_QUOTES, 'UTF-8') ?></option>
                            </select>
                        </div>

                        <div id="subtype_wrapper" class="mb-3">
                            <label for="field_subtype" class="form-label fw-bold"><?= htmlspecialchars(__('feedback_schema.subtype_label'), ENT_QUOTES, 'UTF-8') ?></label>
                            <select id="field_subtype" name="field_subtype" class="form-select max-width-400" onchange="toggleExtraFieldOptions()">
                                <option value=""><?= htmlspecialchars(__('feedback_schema.subtype_standard'), ENT_QUOTES, 'UTF-8') ?></option>
                            </select>
                        </div>

                        <div id="field_options_wrapper" class="mb-3" style="display: none;">
                            <label for="field_options" class="form-label fw-bold"><?= htmlspecialchars(__('feedback_schema.options_label'), ENT_QUOTES, 'UTF-8') ?></label>
                            <textarea id="field_options" name="field_options" rows="4" placeholder="Low, Medium, High&#10;Urgent, Non-Urgent" class="form-control max-width-400"><?= $editCol ? htmlspecialchars((string)($editCol['field_options'] ?? ''), ENT_QUOTES, 'UTF-8') : '' ?></textarea>
                            <div class="form-text"><?= htmlspecialchars(__('feedback_schema.options_help'), ENT_QUOTES, 'UTF-8') ?></div>
                        </div>

                        <div id="allow_multiple_wrapper" class="mb-3 form-check" style="display: none;">
                            <input type="checkbox" id="allow_multiple" name="allow_multiple" value="1" <?= ($editCol && !empty($editCol['allow_multiple'])) ? 'checked' : '' ?> class="form-check-input">
                            <label for="allow_multiple" class="form-check-label"><?= htmlspecialchars(__('feedback_schema.allow_multiple'), ENT_QUOTES, 'UTF-8') ?></label>
                        </div>

                        <div id="boolean_options_wrapper" class="mb-3" style="display: <?= ($editCol && ($editCol['data_type'] ?? '') === 'BOOLEAN') ? 'block' : 'none' ?>;">
                            <label for="boolean_display_format" class="form-label fw-bold"><?= htmlspecialchars(__('feedback_schema.boolean_format'), ENT_QUOTES, 'UTF-8') ?></label>
                            <select id="boolean_display_format" name="boolean_display_format" class="form-select max-width-400">
                                <option value="yes_no" <?= ($editCol && (string)($editCol['boolean_display_format'] ?? '') === 'yes_no') ? 'selected' : '' ?>><?= htmlspecialchars(__('index.opt_yes_true'), ENT_QUOTES, 'UTF-8') ?></option>
                                <option value="true_false" <?= ($editCol && (string)($editCol['boolean_display_format'] ?? '') === 'true_false') ? 'selected' : '' ?>><?= htmlspecialchars(__('index.opt_true'), ENT_QUOTES, 'UTF-8') ?></option>
                                <option value="tick_cross" <?= ($editCol && (string)($editCol['boolean_display_format'] ?? '') === 'tick_cross') ? 'selected' : '' ?>><?= htmlspecialchars(__('index.opt_tick'), ENT_QUOTES, 'UTF-8') ?></option>
                            </select>
                        </div>

                        <div class="mb-3">
                            <label for="max_length" class="form-label fw-bold"><?= htmlspecialchars(__('feedback_schema.max_length_label'), ENT_QUOTES, 'UTF-8') ?></label>
                            <input type="number" id="max_length" name="max_length" value="<?= $editCol ? htmlspecialchars((string)($editCol['max_length'] ?? ''), ENT_QUOTES, 'UTF-8') : '' ?>" placeholder="e.g. 255" class="form-control max-width-400">
                        </div>

                        <div class="mb-3 form-check">
                            <input type="checkbox" id="is_required" name="is_required" value="1" <?= ($editCol && !empty($editCol['is_required'])) ? 'checked' : '' ?> class="form-check-input">
                            <label for="is_required" class="form-check-label"><?= htmlspecialchars(__('feedback_schema.is_required_label'), ENT_QUOTES, 'UTF-8') ?> (<span class="text-danger">*</span>)</label>
                        </div>

                        <button type="submit" class="btn btn-primary"><?= $editCol ? htmlspecialchars(__('feedback_schema.save_field_btn'), ENT_QUOTES, 'UTF-8') : htmlspecialchars(__('volunteer_schema.create_field_btn'), ENT_QUOTES, 'UTF-8') ?></button>
                        <?php if ($editCol): ?>
                            <a href="/admin/volunteers/schema" class="btn btn-outline-secondary ms-2"><?= htmlspecialchars(__('btn.cancel'), ENT_QUOTES, 'UTF-8') ?></a>
                        <?php endif; ?>
                    </form>
                </div>
            </details>
        </div>
    </div>

    <script>
    function updateSubtypeOptions(dataType) {
        var subSelect = document.getElementById('field_subtype');
        var currentSubtype = "<?= $editCol && isset($editCol['field_subtype']) && is_string($editCol['field_subtype']) ? $editCol['field_subtype'] : '' ?>";
        subSelect.innerHTML = '<option value=""><?= htmlspecialchars(__('feedback_schema.subtype_standard'), ENT_QUOTES, 'UTF-8') ?></option>';

        if (dataType === 'VARCHAR') {
            subSelect.innerHTML += '<option value="email"><?= htmlspecialchars(__('feedback_schema.sub_email'), ENT_QUOTES, 'UTF-8') ?></option><option value="url"><?= htmlspecialchars(__('feedback_schema.sub_url'), ENT_QUOTES, 'UTF-8') ?></option><option value="select"><?= htmlspecialchars(__('feedback_schema.sub_select'), ENT_QUOTES, 'UTF-8') ?></option><option value="radio"><?= htmlspecialchars(__('feedback_schema.sub_radio'), ENT_QUOTES, 'UTF-8') ?></option><option value="checkbox"><?= htmlspecialchars(__('feedback_schema.sub_checkbox'), ENT_QUOTES, 'UTF-8') ?></option>';
        } else if (dataType === 'TEXT') {
            subSelect.innerHTML += '<option value="textarea"><?= htmlspecialchars(__('feedback_schema.sub_textarea'), ENT_QUOTES, 'UTF-8') ?></option>';
        } else if (dataType === 'INT') {
            subSelect.innerHTML += '<option value="number"><?= htmlspecialchars(__('feedback_schema.sub_number'), ENT_QUOTES, 'UTF-8') ?></option>';
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
            multiWrapper.style.display = 'block';
        } else {
            optWrapper.style.display = 'none';
            multiWrapper.style.display = 'none';
        }
    }

    document.addEventListener('DOMContentLoaded', function() {
        updateSubtypeOptions(document.getElementById('data_type').value);
    });
    </script>

    <hr class="my-4">

    <h3 class="fw-bold mb-3"><?= htmlspecialchars(__('volunteer_schema.existing_fields_heading'), ENT_QUOTES, 'UTF-8') ?></h3>
    <div class="card shadow-sm border-0">
        <div class="table-responsive">
            <table class="table table-hover align-middle mb-0" role="table">
                <thead class="table-light">
                    <tr>
                        <th scope="col" style="width: 50px;" class="text-center py-3 ps-3"><?= htmlspecialchars(__('feedback_schema.th_move'), ENT_QUOTES, 'UTF-8') ?></th>
                        <th scope="col" class="py-3"><?= htmlspecialchars(__('feedback_schema.th_field_name'), ENT_QUOTES, 'UTF-8') ?></th>
                        <th scope="col" class="py-3"><?= htmlspecialchars(__('feedback_schema.th_data_type'), ENT_QUOTES, 'UTF-8') ?></th>
                        <th scope="col" class="py-3"><?= htmlspecialchars(__('feedback_schema.th_subtype'), ENT_QUOTES, 'UTF-8') ?></th>
                        <th scope="col" class="py-3"><?= htmlspecialchars(__('feedback_schema.th_required'), ENT_QUOTES, 'UTF-8') ?></th>
                        <th scope="col" class="py-3 pe-3 text-end"><?= htmlspecialchars(__('index.th_actions'), ENT_QUOTES, 'UTF-8') ?></th>
                    </tr>
                </thead>
                <tbody id="sortable-columns-body">
                    <?php if (empty($columns)): ?>
                        <tr>
                            <td colspan="6" class="text-center py-4 text-muted"><?= htmlspecialchars(__('volunteer_schema.no_fields'), ENT_QUOTES, 'UTF-8') ?></td>
                        </tr>
                    <?php else: ?>
                        <?php foreach ($columns as $col): ?>
                            <?php 
                                $colId = isset($col['id']) ? (int)$col['id'] : 0;
                                $colName = isset($col['column_name']) && is_string($col['column_name']) ? $col['column_name'] : '';
                                $dataType = isset($col['data_type']) && is_string($col['data_type']) ? $col['data_type'] : '';
                                $fieldSubtype = isset($col['field_subtype']) && is_string($col['field_subtype']) ? $col['field_subtype'] : '';
                                $isRequired = !empty($col['is_required']);
                            ?>
                            <tr data-column-id="<?= $colId ?>" style="cursor: grab;">
                                <td class="text-center text-muted ps-3 fs-5" title="Drag to reorder">☰</td>
                                <td><span class="fw-bold"><?= htmlspecialchars($colName, ENT_QUOTES, 'UTF-8') ?></span></td>
                                <td><code class="text-dark"><?= htmlspecialchars($dataType, ENT_QUOTES, 'UTF-8') ?></code></td>
                                <td><code class="text-secondary"><?= htmlspecialchars($fieldSubtype !== '' ? $fieldSubtype : __('feedback_schema.subtype_standard_lower'), ENT_QUOTES, 'UTF-8') ?></code></td>
                                <td><?= $isRequired ? '<span class="text-success fw-bold">Yes</span>' : '<span class="text-muted">No</span>' ?></td>
                                <td class="text-end pe-3 text-nowrap">
                                    <a href="/admin/volunteers/schema?edit_column=<?= $colId ?>#create-column-details" class="btn btn-sm btn-outline-secondary me-1"><?= htmlspecialchars(__('feedback_schema.edit_btn'), ENT_QUOTES, 'UTF-8') ?></a>
                                    
                                    <form method="POST" action="/admin/volunteers/schema/store" class="d-inline" onsubmit="return confirm('<?= htmlspecialchars(__('volunteer_schema.delete_confirm'), ENT_QUOTES, 'UTF-8') ?>');">
                                        <?= csrf_field() ?>
                                        <input type="hidden" name="action" value="delete">
                                        <input type="hidden" name="column_id" value="<?= $colId ?>">
                                        <button type="submit" class="btn btn-sm btn-danger"><?= htmlspecialchars(__('btn.delete'), ENT_QUOTES, 'UTF-8') ?></button>
                                    </form>
                                </td>
                            </tr>
                        <?php endforeach; ?>
                    <?php endif; ?>
                </tbody>
            </table>
        </div>
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
                    formData.append('csrf_token', '<?= generate_csrf_token() ?>');
                    for (var id in sortOrders) {
                        formData.append('sort_orders[' + id + ']', sortOrders[id]);
                    }
                    fetch('/admin/volunteers/schema/store', { method: 'POST', body: formData });
                }
            });
        }
    });
    </script>
    <style>.sortable-ghost { opacity: 0.4; background: #f8f9fa !important; }</style>
    <?php endif; ?>
</div>

<?php require_once __DIR__ . '/../partials/footer.php'; ?>
