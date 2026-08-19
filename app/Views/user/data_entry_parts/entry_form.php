<?php
declare(strict_types=1);
if (!function_exists('parse_column_options')) {
    $optHelper = dirname(__DIR__, 3) . '/includes/column_options.php';
    if (is_file($optHelper)) {
        require_once $optHelper;
    }
}
?>
    <div class="card shadow-sm border-0 mb-4 bg-light">
        <div class="card-body">
            <details id="add-entry-details" open>
                <summary class="fw-bold fs-6 text-dark" style="cursor: pointer; outline: none;">
                    <?= htmlspecialchars(__('data_entry.add_entry_summary'), ENT_QUOTES, 'UTF-8') ?>
                </summary>
                <div class="mt-3 pt-3 border-top">
                    <form method="POST" action="" id="data-entry-form">
                        <?= csrf_field() ?>
                        <input type="hidden" name="action" value="insert_record">
                        <input type="hidden" name="table_id" value="<?= $activeTableId ?>">
                        <div class="row g-3">
                            <?php foreach ($columns as $col): ?>
                                <?php 
                                    $colId = isset($col['id']) ? (int)$col['id'] : 0;
                                    $colName = isset($col['column_name']) && is_string($col['column_name']) ? $col['column_name'] : '';
                                    $isRequired = !empty($col['is_required']);
                                    $dataType = isset($col['data_type']) && is_string($col['data_type']) ? $col['data_type'] : '';
                                    $savedVal = '';
                                    if (isset($submittedData[$colId])) {
                                        $savedVal = is_array($submittedData[$colId])
                                            ? implode(', ', array_map('strval', $submittedData[$colId]))
                                            : (is_scalar($submittedData[$colId]) ? (string) $submittedData[$colId] : '');
                                    }
                                    $choiceOptions = [];
                                    $allowMultiple = !empty($col['allow_multiple']);
                                    if ($dataType === 'SELECT') {
                                        $rawOpts = isset($col['field_options']) && is_string($col['field_options']) ? $col['field_options'] : '';
                                        $choiceOptions = function_exists('parse_column_options')
                                            ? parse_column_options($rawOpts)
                                            : array_values(array_filter(array_map('trim', preg_split('/\r\n|\r|\n/', $rawOpts) ?: [])));
                                    }
                                    $savedChoices = $savedVal === '' ? [] : array_map('trim', explode(',', $savedVal));
                                ?>
                                <div class="col-md-4">
                                    <label for="col_<?= $colId ?>" class="form-label small fw-bold">
                                        <?= htmlspecialchars($colName, ENT_QUOTES, 'UTF-8') ?>:
                                        <?php if ($isRequired): ?>
                                            <span class="text-danger fw-bold">*</span>
                                        <?php endif; ?>
                                    </label>
                                    
                                    <?php if ($dataType === 'BOOLEAN'): ?>
                                        <?php 
                                            $displayFormat = isset($col['boolean_display_format']) && is_string($col['boolean_display_format']) ? $col['boolean_display_format'] : 'yes_no';
                                            $opt1Text = __('data_entry.bool_yes_true');
                                            $opt2Text = __('data_entry.bool_no_false');
                                            if ($displayFormat === 'male_female') { $opt1Text = __('data_entry.bool_male'); $opt2Text = __('data_entry.bool_female'); }
                                            elseif ($displayFormat === 'true_false') { $opt1Text = __('data_entry.bool_true'); $opt2Text = __('data_entry.bool_false'); }
                                            elseif ($displayFormat === 'tick_cross') { $opt1Text = __('data_entry.bool_tick'); $opt2Text = __('data_entry.bool_cross'); }
                                        ?>
                                        <select id="col_<?= $colId ?>" name="filters[<?= $colId ?>]" class="form-select form-select-sm" <?= $isRequired ? 'required' : '' ?>>
                                            <option value=""><?= htmlspecialchars(__('feedback.select_placeholder'), ENT_QUOTES, 'UTF-8') ?></option>
                                            <option value="1" <?= ($savedVal === '1') ? 'selected' : '' ?>><?= $opt1Text ?></option>
                                            <option value="0" <?= ($savedVal === '0') ? 'selected' : '' ?>><?= $opt2Text ?></option>
                                        </select>
                                    <?php elseif ($dataType === 'DATE'): ?>
                                        <input type="text" id="col_<?= $colId ?>" name="filters[<?= $colId ?>]" value="<?= htmlspecialchars($savedVal, ENT_QUOTES, 'UTF-8') ?>" placeholder="<?= htmlspecialchars(get_date_placeholder($currentUser['date_format'] ?? null), ENT_QUOTES, 'UTF-8') ?>" class="form-control form-control-sm date-input" title="<?= htmlspecialchars(__('data_entry.date_title_hint'), ENT_QUOTES, 'UTF-8') ?>" <?= $isRequired ? 'required' : '' ?>>
                                    <?php elseif ($dataType === 'SELECT'): ?>
                                        <select id="col_<?= $colId ?>"
                                                name="filters[<?= $colId ?>]<?= $allowMultiple ? '[]' : '' ?>"
                                                class="form-select form-select-sm"
                                                <?= $allowMultiple ? 'multiple' : '' ?>
                                                <?= $isRequired ? 'required' : '' ?>
                                                <?= $allowMultiple ? 'aria-multiselectable="true"' : '' ?>>
                                            <?php if (!$allowMultiple): ?>
                                                <option value=""><?= htmlspecialchars(__('feedback.select_placeholder'), ENT_QUOTES, 'UTF-8') ?></option>
                                            <?php endif; ?>
                                            <?php foreach ($choiceOptions as $opt): ?>
                                                <option value="<?= htmlspecialchars($opt, ENT_QUOTES, 'UTF-8') ?>" <?= in_array($opt, $savedChoices, true) ? 'selected' : '' ?>><?= htmlspecialchars($opt, ENT_QUOTES, 'UTF-8') ?></option>
                                            <?php endforeach; ?>
                                        </select>
                                        <?php if ($allowMultiple): ?>
                                            <div class="form-text"><?= htmlspecialchars(__('data_entry.multiselect_hint') !== 'data_entry.multiselect_hint' ? __('data_entry.multiselect_hint') : 'Hold Ctrl (or Cmd) to choose more than one.', ENT_QUOTES, 'UTF-8') ?></div>
                                        <?php endif; ?>
                                    <?php elseif ($dataType === 'INT'): ?>
                                        <input type="number" id="col_<?= $colId ?>" name="filters[<?= $colId ?>]" value="<?= htmlspecialchars($savedVal, ENT_QUOTES, 'UTF-8') ?>" class="form-control form-control-sm"
                                               <?= isset($col['min_value']) && $col['min_value'] !== null && $col['min_value'] !== '' ? 'min="' . (int)$col['min_value'] . '"' : '' ?>
                                               <?= isset($col['max_value']) && $col['max_value'] !== null && $col['max_value'] !== '' ? 'max="' . (int)$col['max_value'] . '"' : '' ?>
                                               <?= $isRequired ? 'required' : '' ?>>
                                    <?php else: ?>
                                        <input type="text" id="col_<?= $colId ?>" name="filters[<?= $colId ?>]" value="<?= htmlspecialchars($savedVal, ENT_QUOTES, 'UTF-8') ?>" placeholder="<?= htmlspecialchars(__('data_entry.enter_value_placeholder'), ENT_QUOTES, 'UTF-8') ?>" class="form-control form-control-sm" <?= $isRequired ? 'required' : '' ?>>
                                    <?php endif; ?>
                                </div>
                            <?php endforeach; ?>
                        </div>
                        <div class="mt-3 d-flex align-items-center gap-3 flex-wrap">
                            <button type="submit" class="btn btn-sm btn-primary"><?= htmlspecialchars(__('data_entry.submit_data_btn'), ENT_QUOTES, 'UTF-8') ?></button>
                            <span class="text-muted small"><?= __('data_entry.shortcuts_tip') ?></span>
                        </div>
                    </form>
                </div>
            </details>
        </div>
    </div>

    <script>
    document.addEventListener('DOMContentLoaded', () => {
        const dateInputs = document.querySelectorAll('#data-entry-form input.date-input');
        dateInputs.forEach(input => {
            const validateDate = () => {
                const val = input.value.trim();
                if (val === '') return true;

                // Enforce 4-digit years for historical, current, and future flexibility
                const dateRegex = /^(\d{1,2}[\.\/\-]\d{1,2}[\.\/\-]\d{4})|(\d{4}[\.\/\-]\d{1,2}[\.\/\-]\d{1,2})$/;
                if (!dateRegex.test(val)) {
                    alert("Please enter a valid date with a 4-digit year (e.g., DD/MM/YYYY, DD.MM.YYYY, or YYYY-MM-DD).");
                    input.value = '';
                    input.focus();
                    return false;
                }
                return true;
            };

            input.addEventListener('blur', validateDate);
        });

        const entryForm = document.getElementById('data-entry-form');
        if (entryForm) {
            entryForm.addEventListener('submit', (e) => {
                dateInputs.forEach(input => {
                    const val = input.value.trim();
                    if (val !== '') {
                        const dateRegex = /^(\d{1,2}[\.\/\-]\d{1,2}[\.\/\-]\d{4})|(\d{4}[\.\/\-]\d{1,2}[\.\/\-]\d{1,2})$/;
                        if (!dateRegex.test(val)) {
                            e.preventDefault();
                            alert("Please enter a valid date with a 4-digit year before submitting.");
                            input.value = '';
                            input.focus();
                        }
                    }
                });
            });
        }
    });
    </script>
