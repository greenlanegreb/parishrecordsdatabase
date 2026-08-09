<?php
declare(strict_types=1);
?>
<!-- Collapsible Column Form Container -->
<div class="card shadow-sm border-0 mb-4">
    <div class="card-body">
        <details id="create-column-details" <?= $editCol ? 'open' : '' ?>>
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
                        <input type="text" id="column_name" name="column_name" value="<?= $editCol ? htmlspecialchars((string)($editCol['column_name'] ?? ''), ENT_QUOTES, 'UTF-8') : '' ?>" required class="form-control max-width-400">
                    </div>

                    <div class="mb-3">
                        <label for="data_type" class="form-label fw-bold"><?= htmlspecialchars(__('feedback_schema.data_type_label'), ENT_QUOTES, 'UTF-8') ?></label>
                        <select id="data_type" name="data_type" class="form-select max-width-400" onchange="toggleFieldOptions(this.value)">
                            <option value="VARCHAR" <?= ($editCol && ($editCol['data_type'] ?? '') === 'VARCHAR') ? 'selected' : '' ?>><?= htmlspecialchars(__('feedback_schema.type_varchar'), ENT_QUOTES, 'UTF-8') ?></option>
                            <option value="TEXT" <?= ($editCol && ($editCol['data_type'] ?? '') === 'TEXT') ? 'selected' : '' ?>><?= htmlspecialchars(__('manage_tables.type_text_long'), ENT_QUOTES, 'UTF-8') ?></option>
                            <option value="INT" <?= ($editCol && ($editCol['data_type'] ?? '') === 'INT') ? 'selected' : '' ?>><?= htmlspecialchars(__('feedback_schema.type_int'), ENT_QUOTES, 'UTF-8') ?></option>
                            <option value="BOOLEAN" <?= ($editCol && ($editCol['data_type'] ?? '') === 'BOOLEAN') ? 'selected' : '' ?>><?= htmlspecialchars(__('feedback_schema.type_boolean'), ENT_QUOTES, 'UTF-8') ?></option>
                            <option value="DATE" <?= ($editCol && ($editCol['data_type'] ?? '') === 'DATE') ? 'selected' : '' ?>><?= htmlspecialchars(__('feedback_schema.type_date'), ENT_QUOTES, 'UTF-8') ?></option>
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

                    <button type="submit" class="btn btn-primary"><?= $editCol ? htmlspecialchars(__('feedback_schema.save_field_btn'), ENT_QUOTES, 'UTF-8') : htmlspecialchars(__('manage_tables.create_col_btn'), ENT_QUOTES, 'UTF-8') ?></button>
                    <?php if ($editCol): ?>
                        <a href="<?= $basePath ?>/admin/tables?table_id=<?= $activeTableId ?>" class="btn btn-outline-secondary ms-2"><?= htmlspecialchars(__('btn.cancel'), ENT_QUOTES, 'UTF-8') ?></a>
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
  
    boolWrapper.style.display = (val === 'BOOLEAN') ? 'block' : 'none';
    dateWrapper.style.display = (val === 'DATE') ? 'block' : 'none';
}
</script>
