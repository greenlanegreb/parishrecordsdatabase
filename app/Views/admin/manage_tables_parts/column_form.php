<?php
declare(strict_types=1);
/** @var array{action?: string, post?: array<string, mixed>}|null $formDraft */
$formDraft = $formDraft ?? null;
$draftPost = (is_array($formDraft) && isset($formDraft['post']) && is_array($formDraft['post'])) ? $formDraft['post'] : [];
$draftAction = is_array($formDraft) && isset($formDraft['action']) ? (string) $formDraft['action'] : '';
$useColDraft = in_array($draftAction, ['create', 'update'], true);
$draftColName = $useColDraft && isset($draftPost['column_name']) && is_string($draftPost['column_name'])
    ? $draftPost['column_name'] : null;
$colSrc = $editCol ?: ($useColDraft ? $draftPost : []);
$formType = isset($colSrc['data_type']) && is_string($colSrc['data_type']) ? $colSrc['data_type'] : 'VARCHAR';
$colStr = static function (string $key, string $default = '') use ($colSrc): string {
    if (!isset($colSrc[$key]) || $colSrc[$key] === null) {
        return $default;
    }
    return is_scalar($colSrc[$key]) ? (string) $colSrc[$key] : $default;
};
$colOn = static function (string $key) use ($colSrc): bool {
    if (!isset($colSrc[$key])) {
        return false;
    }
    $v = $colSrc[$key];
    return $v === '1' || $v === 1 || $v === true || $v === 'on';
};
$keepColumnFormOpen = $editCol
    || (isset($_GET['add_column']) && (string) $_GET['add_column'] === '1')
    || $useColDraft;
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
                            <option value="VARCHAR" <?= ($formType === 'VARCHAR') ? 'selected' : '' ?>><?= htmlspecialchars(__('feedback_schema.type_varchar'), ENT_QUOTES, 'UTF-8') ?></option>
                            <option value="TEXT" <?= ($formType === 'TEXT') ? 'selected' : '' ?>><?= htmlspecialchars(__('manage_tables.type_text_long'), ENT_QUOTES, 'UTF-8') ?></option>
                            <option value="INT" <?= ($formType === 'INT') ? 'selected' : '' ?>><?= htmlspecialchars(__('feedback_schema.type_int'), ENT_QUOTES, 'UTF-8') ?></option>
                            <option value="BOOLEAN" <?= ($formType === 'BOOLEAN') ? 'selected' : '' ?>><?= htmlspecialchars(__('feedback_schema.type_boolean'), ENT_QUOTES, 'UTF-8') ?></option>
                            <option value="DATE" <?= ($formType === 'DATE') ? 'selected' : '' ?>><?= htmlspecialchars(__('feedback_schema.type_date'), ENT_QUOTES, 'UTF-8') ?></option>
                            <option value="TIME" <?= ($formType === 'TIME') ? 'selected' : '' ?>><?= htmlspecialchars((__('manage_tables.type_time') !== 'manage_tables.type_time') ? __('manage_tables.type_time') : 'Time', ENT_QUOTES, 'UTF-8') ?></option>
                            <option value="SELECT" <?= ($formType === 'SELECT') ? 'selected' : '' ?>><?= htmlspecialchars(__('manage_tables.type_choice') !== 'manage_tables.type_choice' ? __('manage_tables.type_choice') : 'Choice list', ENT_QUOTES, 'UTF-8') ?></option>
                            <option value="LOCATION" <?= ($formType === 'LOCATION') ? 'selected' : '' ?>><?= htmlspecialchars(__('manage_tables.type_location') !== 'manage_tables.type_location' ? __('manage_tables.type_location') : 'Location (map pin)', ENT_QUOTES, 'UTF-8') ?></option>
                        </select>
                    </div>

                    <!-- Dynamic Boolean Display Style Option -->
                    <div id="boolean_options_wrapper" class="mb-3" style="display: <?= ($formType === 'BOOLEAN') ? 'block' : 'none' ?>;">
                        <label for="boolean_display_format" class="form-label fw-bold"><?= htmlspecialchars(__('feedback_schema.boolean_format'), ENT_QUOTES, 'UTF-8') ?></label>
                        <select id="boolean_display_format" name="boolean_display_format" class="form-select max-width-400">
                                                        <option value="yes_no" <?= ($colStr('boolean_display_format', 'yes_no') === 'yes_no') ? 'selected' : '' ?>><?= htmlspecialchars(__('manage_tables.bool_yes_no') !== 'manage_tables.bool_yes_no' ? __('manage_tables.bool_yes_no') : 'Yes / No', ENT_QUOTES, 'UTF-8') ?></option>
                            <option value="true_false" <?= ($colStr('boolean_display_format') === 'true_false') ? 'selected' : '' ?>><?= htmlspecialchars(__('manage_tables.bool_true_false') !== 'manage_tables.bool_true_false' ? __('manage_tables.bool_true_false') : 'True / False', ENT_QUOTES, 'UTF-8') ?></option>
                            <option value="tick_cross" <?= ($colStr('boolean_display_format') === 'tick_cross') ? 'selected' : '' ?>><?= htmlspecialchars(__('manage_tables.bool_tick_cross') !== 'manage_tables.bool_tick_cross' ? __('manage_tables.bool_tick_cross') : 'Tick / Cross', ENT_QUOTES, 'UTF-8') ?></option>
                            <option value="male_female" <?= ($colStr('boolean_display_format') === 'male_female') ? 'selected' : '' ?>><?= htmlspecialchars(__('manage_tables.bool_male_female') !== 'manage_tables.bool_male_female' ? __('manage_tables.bool_male_female') : 'Male / Female', ENT_QUOTES, 'UTF-8') ?></option>
                        </select>
                    </div>

                    <!-- Dynamic Date Search Behavior Option -->
                    <div id="date_options_wrapper" class="mb-3" style="display: <?= ($formType === 'DATE') ? 'block' : 'none' ?>;">
                        <label for="date_search_behavior" class="form-label fw-bold"><?= htmlspecialchars(__('manage_tables.date_behavior_label'), ENT_QUOTES, 'UTF-8') ?></label>
                        <select id="date_search_behavior" name="date_search_behavior" class="form-select max-width-400">
                            <option value="manual_only" <?= ($colStr('date_search_behavior', 'manual_only') === 'manual_only') ? 'selected' : '' ?>><?= htmlspecialchars(__('manage_tables.date_bhv_manual'), ENT_QUOTES, 'UTF-8') ?></option>
                            <option value="admin_only" <?= ($colStr('date_search_behavior') === 'admin_only') ? 'selected' : '' ?>><?= htmlspecialchars(__('manage_tables.date_bhv_admin'), ENT_QUOTES, 'UTF-8') ?></option>
                            <option value="all_dates" <?= ($colStr('date_search_behavior') === 'all_dates') ? 'selected' : '' ?>><?= htmlspecialchars(__('manage_tables.date_bhv_all'), ENT_QUOTES, 'UTF-8') ?></option>
                        </select>
                    </div>

                    <div id="choice_options_wrapper" class="mb-3" style="display: <?= ($formType === 'SELECT') ? 'block' : 'none' ?>;">
                        <label for="field_options" class="form-label fw-bold"><?= htmlspecialchars(__('manage_tables.choice_options_label') !== 'manage_tables.choice_options_label' ? __('manage_tables.choice_options_label') : 'Choices (one per line)', ENT_QUOTES, 'UTF-8') ?></label>
                        <textarea id="field_options" name="field_options" rows="5" class="form-control max-width-400"><?= htmlspecialchars($colStr('field_options'), ENT_QUOTES, 'UTF-8') ?></textarea>
                        <div class="form-text"><?= htmlspecialchars(__('manage_tables.choice_options_help') !== 'manage_tables.choice_options_help' ? __('manage_tables.choice_options_help') : 'Example: Baptism, Marriage, Burial — each on its own line.', ENT_QUOTES, 'UTF-8') ?></div>
                        <div class="form-check mt-2">
                            <input type="checkbox" id="allow_multiple" name="allow_multiple" value="1" class="form-check-input" <?= $colOn('allow_multiple') ? 'checked' : '' ?>>
                            <label for="allow_multiple" class="form-check-label"><?= htmlspecialchars(__('manage_tables.allow_multiple_label') !== 'manage_tables.allow_multiple_label' ? __('manage_tables.allow_multiple_label') : 'Allow more than one choice (multi-select)', ENT_QUOTES, 'UTF-8') ?></label>
                        </div>
                    </div>

                    <div id="int_bounds_wrapper" class="mb-3" style="display: <?= ($formType === 'INT') ? 'block' : 'none' ?>;">
                        <div class="row g-2" style="max-width: 400px;">
                            <div class="col">
                                <label for="min_value" class="form-label fw-bold"><?= htmlspecialchars(__('manage_tables.min_value_label') !== 'manage_tables.min_value_label' ? __('manage_tables.min_value_label') : 'Minimum', ENT_QUOTES, 'UTF-8') ?></label>
                                <input type="number" id="min_value" name="min_value" value="<?= htmlspecialchars($colStr('min_value'), ENT_QUOTES, 'UTF-8') ?>" class="form-control">
                            </div>
                            <div class="col">
                                <label for="max_value" class="form-label fw-bold"><?= htmlspecialchars(__('manage_tables.max_value_label') !== 'manage_tables.max_value_label' ? __('manage_tables.max_value_label') : 'Maximum', ENT_QUOTES, 'UTF-8') ?></label>
                                <input type="number" id="max_value" name="max_value" value="<?= htmlspecialchars($colStr('max_value'), ENT_QUOTES, 'UTF-8') ?>" class="form-control">
                            </div>
                        </div>
                    </div>

                    <div class="mb-3">
                        <label for="max_length" class="form-label fw-bold"><?= htmlspecialchars(__('feedback_schema.max_length_label'), ENT_QUOTES, 'UTF-8') ?></label>
                        <input type="number" id="max_length" name="max_length" value="<?= htmlspecialchars($colStr('max_length'), ENT_QUOTES, 'UTF-8') ?>" placeholder="e.g. 255 characters" class="form-control max-width-400">
                    </div>

                    <div class="mb-3 form-check">
                        <input type="checkbox" id="is_required" name="is_required" value="1" <?= $colOn('is_required') ? 'checked' : '' ?> class="form-check-input">
                        <label for="is_required" class="form-check-label"><?= htmlspecialchars((__('manage_tables.req_toggle_label') !== 'manage_tables.req_toggle_label') ? __('manage_tables.req_toggle_label') : 'Make This Column Required (Mandatory Data Entry)', ENT_QUOTES, 'UTF-8') ?></label>
                    </div>

                    <div class="mb-3 form-check">
                        <input type="checkbox" id="exclude_from_public_search" name="exclude_from_public_search" value="1" <?= $colOn('exclude_from_public_search') ? 'checked' : '' ?> class="form-check-input">
                        <label for="exclude_from_public_search" class="form-check-label"><?= htmlspecialchars((__('manage_tables.exclude_search_label') !== 'manage_tables.exclude_search_label') ? __('manage_tables.exclude_search_label') : 'Exclude This Column From Public Search', ENT_QUOTES, 'UTF-8') ?></label>
                    </div>
                    <div class="mb-3 form-check">
                        <input type="checkbox" id="show_in_list" name="show_in_list" value="1" <?= (!isset($editCol) || $colOn('show_in_list') || !array_key_exists('show_in_list', $editCol ?? [])) ? 'checked' : '' ?> class="form-check-input">
                        <label for="show_in_list" class="form-check-label"><?= htmlspecialchars(__('manage_tables.show_in_list') !== 'manage_tables.show_in_list' ? __('manage_tables.show_in_list') : 'Show On The Search / Data-Entry List', ENT_QUOTES, 'UTF-8') ?></label>
                    </div>
                    <div class="mb-3 form-check">
                        <input type="checkbox" id="show_in_record" name="show_in_record" value="1" <?= (!isset($editCol) || $colOn('show_in_record') || !array_key_exists('show_in_record', $editCol ?? [])) ? 'checked' : '' ?> class="form-check-input">
                        <label for="show_in_record" class="form-check-label"><?= htmlspecialchars(__('manage_tables.show_in_record') !== 'manage_tables.show_in_record' ? __('manage_tables.show_in_record') : 'Show On The Full Record Page', ENT_QUOTES, 'UTF-8') ?></label>
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
