<?php
declare(strict_types=1);
if (!function_exists('parse_column_options')) {
    $optHelper = dirname(__DIR__, 3) . '/includes/column_options.php';
    if (is_file($optHelper)) {
        require_once $optHelper;
    }
}
if (!function_exists('field_error_html')) {
    $ff = dirname(__DIR__, 3) . '/includes/form_fields.php';
    if (is_file($ff)) {
        require_once $ff;
    }
}
$fieldErrors = $fieldErrors ?? [];
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
                                    $errId = 'col_err_' . $colId;
                                    $inv = function_exists('field_invalid_attr') ? field_invalid_attr($fieldErrors, $colId, $errId) : '';
                                    $invClass = function_exists('field_has_error') && field_has_error($fieldErrors, $colId) ? ' is-invalid' : '';
                                ?>
                                <div class="<?= $dataType === 'LOCATION' ? 'col-12' : 'col-md-4' ?>">
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
                                        <select id="col_<?= $colId ?>" name="filters[<?= $colId ?>]" class="form-select form-select-sm<?= $invClass ?>" <?= $isRequired ? 'required' : '' ?><?= $inv ?>>
                                            <option value=""><?= htmlspecialchars(__('feedback.select_placeholder'), ENT_QUOTES, 'UTF-8') ?></option>
                                            <option value="1" <?= ($savedVal === '1') ? 'selected' : '' ?>><?= $opt1Text ?></option>
                                            <option value="0" <?= ($savedVal === '0') ? 'selected' : '' ?>><?= $opt2Text ?></option>
                                        </select>
                                    <?php elseif ($dataType === 'DATE'): ?>
                                        <input type="text" id="col_<?= $colId ?>" name="filters[<?= $colId ?>]" value="<?= htmlspecialchars($savedVal, ENT_QUOTES, 'UTF-8') ?>" placeholder="<?= htmlspecialchars(get_date_placeholder($currentUser['date_format'] ?? null), ENT_QUOTES, 'UTF-8') ?>" class="form-control form-control-sm date-input<?= $invClass ?>" title="<?= htmlspecialchars(__('data_entry.date_title_hint'), ENT_QUOTES, 'UTF-8') ?>" <?= $isRequired ? 'required' : '' ?><?= $inv ?>>
                                    <?php elseif ($dataType === 'SELECT'): ?>
                                        <select id="col_<?= $colId ?>"
                                                name="filters[<?= $colId ?>]<?= $allowMultiple ? '[]' : '' ?>"
                                                class="form-select form-select-sm<?= $invClass ?>"
                                                <?= $allowMultiple ? 'multiple' : '' ?>
                                                <?= $isRequired ? 'required' : '' ?>
                                                <?= $allowMultiple ? 'aria-multiselectable="true"' : '' ?>
                                                <?= $inv ?>>
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
                                    <?php elseif ($dataType === 'LOCATION'): ?>
                                        <?php
                                            $loc = \App\Services\LocationValueService::decode($savedVal !== '' ? $savedVal : null);
                                            $basePath = defined('BASE_PATH') ? rtrim((string) BASE_PATH, '/') : '';
                                        ?>
                                        <p class="form-text small"><?= htmlspecialchars(__('data_entry.location_help') !== 'data_entry.location_help' ? __('data_entry.location_help') : 'Search for the place as it is known today, pick a match, then you may word the label as the old name. Title and short text are required for the map popup.', ENT_QUOTES, 'UTF-8') ?></p>
                                        <label class="form-label small" for="loc_q_<?= $colId ?>"><?= htmlspecialchars(__('data_entry.location_search') !== 'data_entry.location_search' ? __('data_entry.location_search') : 'Find place', ENT_QUOTES, 'UTF-8') ?> <span class="text-danger fw-bold" aria-hidden="true">*</span></label>
                                        <div class="input-group input-group-sm mb-2">
                                            <input type="text" id="loc_q_<?= $colId ?>" class="form-control js-loc-q" autocomplete="off">
                                            <button type="button" class="btn btn-outline-secondary js-loc-search" data-col="<?= $colId ?>"><?= htmlspecialchars(__('data_entry.location_search_btn') !== 'data_entry.location_search_btn' ? __('data_entry.location_search_btn') : 'Search', ENT_QUOTES, 'UTF-8') ?></button>
                                        </div>
                                        <div id="loc_results_<?= $colId ?>" class="list-group mb-2 small" role="listbox" aria-label="<?= htmlspecialchars(__('data_entry.location_results') !== 'data_entry.location_results' ? __('data_entry.location_results') : 'Did you mean', ENT_QUOTES, 'UTF-8') ?>"></div>
                                        <input type="hidden" name="fields[<?= $colId ?>][q]" id="loc_hid_q_<?= $colId ?>" value="<?= htmlspecialchars($loc['q'] ?? '', ENT_QUOTES, 'UTF-8') ?>">
                                        <input type="hidden" name="fields[<?= $colId ?>][lat]" id="loc_lat_<?= $colId ?>" value="<?= htmlspecialchars((string) ($loc['lat'] ?? ''), ENT_QUOTES, 'UTF-8') ?>">
                                        <input type="hidden" name="fields[<?= $colId ?>][lng]" id="loc_lng_<?= $colId ?>" value="<?= htmlspecialchars((string) ($loc['lng'] ?? ''), ENT_QUOTES, 'UTF-8') ?>">
                                        <label class="form-label small" for="loc_label_<?= $colId ?>"><?= htmlspecialchars(__('data_entry.location_label') !== 'data_entry.location_label' ? __('data_entry.location_label') : 'Name to show (you may use the historic name)', ENT_QUOTES, 'UTF-8') ?> <span class="text-danger fw-bold" aria-hidden="true">*</span></label>
                                        <input type="text" class="form-control form-control-sm mb-2" id="loc_label_<?= $colId ?>" name="fields[<?= $colId ?>][label]" value="<?= htmlspecialchars($loc['label'] ?? '', ENT_QUOTES, 'UTF-8') ?>">
                                        <label class="form-label small" for="loc_title_<?= $colId ?>"><?= htmlspecialchars(__('data_entry.location_title') !== 'data_entry.location_title' ? __('data_entry.location_title') : 'Popup title', ENT_QUOTES, 'UTF-8') ?> <span class="text-danger fw-bold" aria-hidden="true">*</span></label>
                                        <input type="text" class="form-control form-control-sm mb-2" id="loc_title_<?= $colId ?>" name="fields[<?= $colId ?>][title]" value="<?= htmlspecialchars($loc['title'] ?? '', ENT_QUOTES, 'UTF-8') ?>" required>
                                        <label class="form-label small" for="loc_body_<?= $colId ?>"><?= htmlspecialchars(__('data_entry.location_body') !== 'data_entry.location_body' ? __('data_entry.location_body') : 'Popup text', ENT_QUOTES, 'UTF-8') ?> <span class="text-danger fw-bold" aria-hidden="true">*</span></label>
                                        <textarea class="form-control form-control-sm mb-2" id="loc_body_<?= $colId ?>" name="fields[<?= $colId ?>][body]" rows="2" required><?= htmlspecialchars($loc['body'] ?? '', ENT_QUOTES, 'UTF-8') ?></textarea>
                                        <fieldset class="mb-2">
                                            <legend class="form-label small"><?= htmlspecialchars(__('data_entry.location_color') !== 'data_entry.location_color' ? __('data_entry.location_color') : 'Pin colour', ENT_QUOTES, 'UTF-8') ?></legend>
                                            <?php foreach (\App\Services\LocationValueService::palette() as $sw): ?>
                                                <label class="me-2 small">
                                                    <input type="radio" name="fields[<?= $colId ?>][color]" value="<?= htmlspecialchars($sw['hex'], ENT_QUOTES, 'UTF-8') ?>" <?= (($loc['color'] ?? '') === $sw['hex'] || (($loc['color'] ?? '') === '' && $sw['hex'] === \App\Services\LocationValueService::defaultColor())) ? 'checked' : '' ?>>
                                                    <span style="display:inline-block;width:0.9rem;height:0.9rem;background:<?= htmlspecialchars($sw['hex'], ENT_QUOTES, 'UTF-8') ?>;border:1px solid #000;vertical-align:middle;" title="<?= htmlspecialchars($sw['label'], ENT_QUOTES, 'UTF-8') ?>"></span>
                                                    <span class="visually-hidden"><?= htmlspecialchars($sw['label'], ENT_QUOTES, 'UTF-8') ?></span>
                                                </label>
                                            <?php endforeach; ?>
                                        </fieldset>

                                        <div class="form-check mb-2">
                                            <input type="hidden" name="fields[<?= $colId ?>][show_on_map]" value="0">
                                            <input class="form-check-input" type="checkbox" name="fields[<?= $colId ?>][show_on_map]" value="1" id="loc_show_<?= $colId ?>" <?= (!isset($loc['show_on_map']) || $loc['show_on_map']) ? 'checked' : '' ?>>
                                            <label class="form-check-label small" for="loc_show_<?= $colId ?>"><?= htmlspecialchars(__('data_entry.location_show_on_map') !== 'data_entry.location_show_on_map' ? __('data_entry.location_show_on_map') : 'Show this place on the map', ENT_QUOTES, 'UTF-8') ?></label>
                                            <div class="form-text"><?= htmlspecialchars(__('data_entry.location_show_on_map_help') !== 'data_entry.location_show_on_map_help' ? __('data_entry.location_show_on_map_help') : 'Untick to keep the record in the table but hide the pin (e.g. not open yet).', ENT_QUOTES, 'UTF-8') ?></div>
                                        </div>
                                    <?php elseif ($dataType === 'INT'): ?>
                                        <input type="number" id="col_<?= $colId ?>" name="filters[<?= $colId ?>]" value="<?= htmlspecialchars($savedVal, ENT_QUOTES, 'UTF-8') ?>" class="form-control form-control-sm<?= $invClass ?>"
                                               <?= isset($col['min_value']) && $col['min_value'] !== null && $col['min_value'] !== '' ? 'min="' . (int)$col['min_value'] . '"' : '' ?>
                                               <?= isset($col['max_value']) && $col['max_value'] !== null && $col['max_value'] !== '' ? 'max="' . (int)$col['max_value'] . '"' : '' ?>
                                               <?= $isRequired ? 'required' : '' ?><?= $inv ?>>
                                    <?php else: ?>
                                        <input type="text" id="col_<?= $colId ?>" name="filters[<?= $colId ?>]" value="<?= htmlspecialchars($savedVal, ENT_QUOTES, 'UTF-8') ?>" placeholder="<?= htmlspecialchars(__('data_entry.enter_value_placeholder'), ENT_QUOTES, 'UTF-8') ?>" class="form-control form-control-sm<?= $invClass ?>" <?= $isRequired ? 'required' : '' ?><?= $inv ?>>
                                    <?php endif; ?>
                                    <?= function_exists('field_error_html') ? field_error_html($fieldErrors, $colId, $errId) : '' ?>
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
        document.querySelectorAll('.js-loc-search').forEach(function (btn) {
            btn.addEventListener('click', function () {
                const col = btn.getAttribute('data-col');
                const qEl = document.getElementById('loc_q_' + col);
                const box = document.getElementById('loc_results_' + col);
                const q = qEl ? qEl.value.trim() : '';
                if (q.length < 2 || !box) return;
                box.textContent = '';
                const base = <?= json_encode(defined('BASE_PATH') ? rtrim((string) BASE_PATH, '/') : '') ?>;
                fetch(base + '/api/geocode?q=' + encodeURIComponent(q), { credentials: 'same-origin' })
                    .then(function (r) { return r.json(); })
                    .then(function (data) {
                        const hits = data.results || [];
                        if (!hits.length) {
                            box.innerHTML = '<div class="text-danger"><?= htmlspecialchars(__('data_entry.location_none') !== 'data_entry.location_none' ? __('data_entry.location_none') : 'No matching place. Try a nearby town.', ENT_QUOTES, 'UTF-8') ?></div>';
                            return;
                        }
                        hits.forEach(function (h) {
                            const b = document.createElement('button');
                            b.type = 'button';
                            b.className = 'list-group-item list-group-item-action';
                            b.textContent = h.label;
                            b.addEventListener('click', function () {
                                document.getElementById('loc_hid_q_' + col).value = h.q || q;
                                document.getElementById('loc_lat_' + col).value = h.lat;
                                document.getElementById('loc_lng_' + col).value = h.lng;
                                const lab = document.getElementById('loc_label_' + col);
                                if (lab && lab.value.trim() === '') lab.value = h.label;
                                box.innerHTML = '';
                            });
                            box.appendChild(b);
                        });
                    })
                    .catch(function () {
                        box.innerHTML = '<div class="text-danger"><?= htmlspecialchars(__('data_entry.location_busy') !== 'data_entry.location_busy' ? __('data_entry.location_busy') : 'Place search is busy. Try again in a minute.', ENT_QUOTES, 'UTF-8') ?></div>';
                    });
            });
        });
    });
    </script>
