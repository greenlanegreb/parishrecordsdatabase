<?php
declare(strict_types=1);
?>
<?php if (!$duplicateWarning): ?>
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
                                    $savedVal = isset($submittedData[$colId]) && is_string($submittedData[$colId]) ? $submittedData[$colId] : '';
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
<?php endif; ?>
