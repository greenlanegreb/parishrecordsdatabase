<?php
declare(strict_types=1);
/** @var array{action?: string, post?: array<string, mixed>}|null $formDraft */
$formDraft = $formDraft ?? null;
$draftPost = (is_array($formDraft) && isset($formDraft['post']) && is_array($formDraft['post'])) ? $formDraft['post'] : [];
$draftAction = is_array($formDraft) && isset($formDraft['action']) ? (string) $formDraft['action'] : '';
$useColDraft = in_array($draftAction, ['create', 'update'], true);
$draftColName = $useColDraft && isset($draftPost['column_name']) && is_string($draftPost['column_name'])
    ? $draftPost['column_name'] : null;
$keepColumnFormOpen = $editCol
    || (isset($_GET['add_column']) && (string) $_GET['add_column'] === '1')
    || ($draftColName !== null && $draftColName !== '');
?>
<!-- Collapsible Column Form Container -->
<div class="card shadow-sm border-0 mb-4">
    <div class="card-body">
        <details id="create-column-details" <?= $keepColumnFormOpen ? 'open' : '' ?>>
            <summary class="fw-bold fs-5 text-dark" style="cursor: pointer;">
                <?= $editCol ? htmlspecialchars(__('manage_tables.edit_col_summary'), ENT_QUOTES, 'UTF-8') . ' ' . htmlspecialchars((string)($editCol['column_name'] ?? ''), ENT_QUOTES, 'UTF-8') : htmlspecialchars(__('manage_tables.add_col_summary_prefix'), ENT_QUOTES, 'UTF-8') . ' "' . htmlspecialchars((string)($activeTableInfo['table_name'] ?? ''), ENT_QUOTES, 'UTF-8') . '"' ?>
            </summary>
          
            <div class="mt-3 pt-3 border-top">
                <form method="POST" action="<?= $basePath ?>/admin/tables">
                    <?= csrf_field() ?>
                    <input type="hidden" name="action" value="<?= $editCol ? 'update' : 'create' ?>">
                    <input type="hidden" name="table_id" value="<?= $activeTableId ?>">
                    <?php if ($editCol): ?>
                        <input type="hidden" name="column_id" value="<?= (int)($editCol['id'] ?? 0) ?>">
                    <?php endif; ?>
                  
                    <div class="mb-3">
                        <label for="column_name" class="form-label fw-bold"><?= htmlspecialchars(__('manage_tables.col_name_label'), ENT_QUOTES, 'UTF-8') ?> <span class="text-danger">*</span></label>
                        <input type="text" id="column_name" name="column_name" value="<?= htmlspecialchars((string)($editCol['column_name'] ?? $draftColName ?? ''), ENT_QUOTES, 'UTF-8') ?>" required class="form-control max-width-400" <?= (!$editCol && $keepColumnFormOpen) ? 'autofocus' : '' ?>>
                    </div>

                    <div class="mb-3">
                        <label for="data_type" class="form-label fw-bold"><?= htmlspecialchars(__('feedback_schema.data_type_label'), ENT_QUOTES, 'UTF-8') ?></label>
                        <select id="data_type" name="data_type" class="form-select max-width-400" onchange="toggleFieldOptions(this.value)">
                            <option value="VARCHAR" <?= ($editCol && ($editCol['data_type'] ?? '') === 'VARCHAR') ? 'selected' : '' ?>><?= htmlspecialchars(__('feedback_schema.type_varchar'), ENT_QUOTES, 'UTF-8') ?></option>
                            <option value="TEXT" <?= ($editCol && ($editCol['data_type'] ?? '') === 'TEXT') ? 'selected' : '' ?>><?= htmlspecialchars(__('manage_tables.type_text_long'), ENT_QUOTES, 'UTF-8') ?></option>
                            <option value="INT" <?= ($editCol && ($editCol['data_type'] ?? '') === 'INT') ? 'selected' : '' ?>><?= htmlspecialchars(__('feedback_schema.type_int'), ENT_QUOTES, 'UTF-8') ?></option>
                            <option value="BOOLEAN" <?= ($editCol && ($editCol['data_type'] ?? '') === 'BOOLEAN') ? 'selected' : '' ?>><?= htmlspecialchars(__('feedback_schema.type_boolean'), ENT_QUOTES, 'UTF-8') ?></option>
                            <option value="DATE" <?= ($editCol && ($editCol['data_type'] ?? '') === 'DATE') ? 'selected' : '' ?>><?= htmlspecialchars(__('feedback_schema.type_date'), ENT_QUOTES, 'UTF-8') ?></option>
                            <option value="SELECT" <?= ($editCol && ($editCol['data_type'] ?? '') === 'SELECT') ? 'selected' : '' ?>><?= htmlspecialchars(__('manage_tables.type_choice') !== 'manage_tables.type_choice' ? __('manage_tables.type_choice') : 'Choice list', ENT_QUOTES, 'UTF-8') ?></option>
                        </select>
                    </div>

                    <!-- Dynamic Boolean Display Style Option -->
                    <div id="boolean_options_wrapper" class="mb-3" style="display: <?= ($editCol && ($editCol['data_type'] ?? '') === 'BOOLEAN') ? 'block' : 'none' ?>;">
                        <label for="boolean_display_format" class="form-label fw-bold"><?= htmlspecialchars(__('feedback_schema.boolean_format'), ENT_QUOTES, 'UTF-8') ?></label>
                        <select id="boolean_display_format" name="boolean_display_format" class="form-select max-width-400">
                            <option value="yes_no" <?= ($editCol && (string)($editCol['boolean_display_format'] ?? '') === 'yes_no') ? 'selected' : '' ?>><?= htmlspecialchars(__('index.opt_yes_true'), ENT_QUOTES, 'UTF-8') ?></option>
                            <option value="true_false" <?= ($editCol && (string)($editCol['boolean_display_format'] ?? '') === 'true_false') ? 'selected' : '' ?>><?= htmlspecialchars(__('index.opt_true'), ENT_QUOTES, 'UTF-8') ?></option>
                            <option value="tick_cross" <?= ($editCol && (string)($editCol['boolean_display_format'] ?? '') === 'tick_cross') ? 'selected' : '' ?>><?= htmlspecialchars(__('index.opt_tick'), ENT_QUOTES, 'UTF-8') ?></option>
                            <option value="male_female" <?= ($editCol && (string)($editCol['boolean_display_format'] ?? '') === 'male_female') ? 'selected' : '' ?>><?= htmlspecialchars(__('index.opt_male'), ENT_QUOTES, 'UTF-8') ?> / <?= htmlspecialchars(__('index.opt_female'), ENT_QUOTES, 'UTF-8') ?></option>
                        </select>
                    </div>

                    <!-- Dynamic Date Search Behavior Option -->
                    <div id="date_options_wrapper" class="mb-3" style="display: <?= ($editCol && ($editCol['data_type'] ?? '') === 'DATE') ? 'block' : 'none' ?>;">
                        <label for="date_search_behavior" class="form-label fw-bold"><?= htmlspecialchars(__('manage_tables.date_behavior_label'), ENT_QUOTES, 'UTF-8') ?></label>
                        <select id="date_search_behavior" name="date_search_behavior" class="form-select max-width-400">
                            <option value="manual_only" <?= ($editCol && (string)($editCol['date_search_behavior'] ?? '') === 'manual_only') ? 'selected' : '' ?>><?= htmlspecialchars(__('manage_tables.date_bhv_manual'), ENT_QUOTES, 'UTF-8') ?></option>
                            <option value="admin_only" <?= ($editCol && (string)($editCol['date_search_behavior'] ?? '') === 'admin_only') ? 'selected' : '' ?>><?= htmlspecialchars(__('manage_tables.date_bhv_admin'), ENT_QUOTES, 'UTF-8') ?></option>
                            <option value="all_dates" <?= ($editCol && (string)($editCol['date_search_behavior'] ?? '') === 'all_dates') ? 'selected' : '' ?>><?= htmlspecialchars(__('manage_tables.date_bhv_all'), ENT_QUOTES, 'UTF-8') ?></option>
                        </select>
                    </div>

                    <div id="choice_options_wrapper" class="mb-3" style="display: <?= ($editCol && ($editCol['data_type'] ?? '') === 'SELECT') ? 'block' : 'none' ?>;">
                        <label for="field_options" class="form-label fw-bold"><?= htmlspecialchars(__('manage_tables.choice_options_label') !== 'manage_tables.choice_options_label' ? __('manage_tables.choice_options_label') : 'Choices (one per line)', ENT_QUOTES, 'UTF-8') ?></label>
                        <textarea id="field_options" name="field_options" rows="5" class="form-control max-width-400"><?= $editCol ? htmlspecialchars((string)($editCol['field_options'] ?? ''), ENT_QUOTES, 'UTF-8') : '' ?></textarea>
                        <div class="form-text"><?= htmlspecialchars(__('manage_tables.choice_options_help') !== 'manage_tables.choice_options_help' ? __('manage_tables.choice_options_help') : 'Example: Baptism, Marriage, Burial — each on its own line.', ENT_QUOTES, 'UTF-8') ?></div>
                        <div class="form-check mt-2">
                            <input type="checkbox" id="allow_multiple" name="allow_multiple" value="1" class="form-check-input" <?= ($editCol && !empty($editCol['allow_multiple'])) ? 'checked' : '' ?>>
                            <label for="allow_multiple" class="form-check-label"><?= htmlspecialchars(__('manage_tables.allow_multiple_label') !== 'manage_tables.allow_multiple_label' ? __('manage_tables.allow_multiple_label') : 'Allow more than one choice (multi-select)', ENT_QUOTES, 'UTF-8') ?></label>
                        </div>
                    </div>

                    <div id="int_bounds_wrapper" class="mb-3" style="display: <?= ($editCol && ($editCol['data_type'] ?? '') === 'INT') ? 'block' : 'none' ?>;">
                        <div class="row g-2" style="max-width: 400px;">
                            <div class="col">
                                <label for="min_value" class="form-label fw-bold"><?= htmlspecialchars(__('manage_tables.min_value_label') !== 'manage_tables.min_value_label' ? __('manage_tables.min_value_label') : 'Minimum', ENT_QUOTES, 'UTF-8') ?></label>
                                <input type="number" id="min_value" name="min_value" value="<?= $editCol && $editCol['min_value'] !== null && $editCol['min_value'] !== '' ? htmlspecialchars((string)$editCol['min_value'], ENT_QUOTES, 'UTF-8') : '' ?>" class="form-control">
                            </div>
                            <div class="col">
                                <label for="max_value" class="form-label fw-bold"><?= htmlspecialchars(__('manage_tables.max_value_label') !== 'manage_tables.max_value_label' ? __('manage_tables.max_value_label') : 'Maximum', ENT_QUOTES, 'UTF-8') ?></label>
                                <input type="number" id="max_value" name="max_value" value="<?= $editCol && $editCol['max_value'] !== null && $editCol['max_value'] !== '' ? htmlspecialchars((string)$editCol['max_value'], ENT_QUOTES, 'UTF-8') : '' ?>" class="form-control">
                            </div>
                        </div>
                    </div>

                    <div class="mb-3">
                        <label for="max_length" class="form-label fw-bold"><?= htmlspecialchars(__('feedback_schema.max_length_label'), ENT_QUOTES, 'UTF-8') ?></label>
                        <input type="number" id="max_length" name="max_length" value="<?= $editCol ? htmlspecialchars((string)($editCol['max_length'] ?? ''), ENT_QUOTES, 'UTF-8') : '' ?>" placeholder="e.g. 255 characters" class="form-control max-width-400">
                    </div>

                    <div class="mb-3 form-check">
                        <input type="checkbox" id="is_required" name="is_required" value="1" <?= ($editCol && !empty($editCol['is_required'])) ? 'checked' : '' ?> class="form-check-input">
                        <label for="is_required" class="form-check-label"><?= htmlspecialchars(__('manage_tables.req_toggle_label'), ENT_QUOTES, 'UTF-8') ?></label>
                    </div>

                    <div class="mb-3 form-check">
                        <input type="checkbox" id="exclude_from_public_search" name="exclude_from_public_search" value="1" <?= ($editCol && !empty($editCol['exclude_from_public_search'])) ? 'checked' : '' ?> class="form-check-input">
                        <label for="exclude_from_public_search" class="form-check-label"><?= htmlspecialchars(__('manage_tables.exclude_search_label'), ENT_QUOTES, 'UTF-8') ?></label>
                    </div>

                    <?php if ($editCol): ?>
                        <button type="submit" class="btn btn-primary"><?= htmlspecialchars(__('feedback_schema.save_field_btn'), ENT_QUOTES, 'UTF-8') ?></button>
                        <a href="<?= $basePath ?>/admin/tables?table_id=<?= $activeTableId ?>" class="btn btn-outline-secondary ms-2"><?= htmlspecialchars(__('btn.cancel'), ENT_QUOTES, 'UTF-8') ?></a>
                    <?php else: ?>
                        <button type="submit" name="after_save" value="done" class="btn btn-primary"><?= htmlspecialchars(__('manage_tables.create_col_btn') !== 'manage_tables.create_col_btn' ? __('manage_tables.create_col_btn') : 'Save column', ENT_QUOTES, 'UTF-8') ?></button>
                        <button type="submit" name="after_save" value="add_another" class="btn btn-outline-primary ms-2"><?= htmlspecialchars(__('manage_tables.save_and_add_col') !== 'manage_tables.save_and_add_col' ? __('manage_tables.save_and_add_col') : 'Save and add another', ENT_QUOTES, 'UTF-8') ?></button>
                    <?php endif; ?>
                </form>
            </div>
        </details>
    </div>
</div>

<script>
function toggleFieldOptions(val) {
    var boolWrapper = document.getElementById('boolean_options_wrapper');
    var dateWrapper = document.getElementById('date_options_wrapper');
    var choiceWrapper = document.getElementById('choice_options_wrapper');
    var intWrapper = document.getElementById('int_bounds_wrapper');
    if (boolWrapper) boolWrapper.style.display = (val === 'BOOLEAN') ? 'block' : 'none';
    if (dateWrapper) dateWrapper.style.display = (val === 'DATE') ? 'block' : 'none';
    if (choiceWrapper) choiceWrapper.style.display = (val === 'SELECT') ? 'block' : 'none';
    if (intWrapper) intWrapper.style.display = (val === 'INT') ? 'block' : 'none';
}
<?php if ($keepColumnFormOpen): ?>
document.addEventListener('DOMContentLoaded', function () {
    var box = document.getElementById('create-column-details');
    var name = document.getElementById('column_name');
    if (box && typeof box.scrollIntoView === 'function') {
        box.scrollIntoView({ behavior: 'smooth', block: 'start' });
    }
    if (name && !name.value) {
        name.focus({ preventScroll: false });
    }
});
<?php endif; ?>
</script>
