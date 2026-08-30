<?php
declare(strict_types=1);
$canExport = $canExport ?? false;
$mapsOn = isset($pdo) && $pdo instanceof PDO && function_exists('is_module_enabled') && is_module_enabled($pdo, 'maps');
$tableIdForMap = (int) ($activeTableId ?? 0);
$tableHasMap = $mapsOn && $tableIdForMap > 0
    && class_exists(\App\Services\LocationValueService::class)
    && \App\Services\LocationValueService::tableHasLocationColumn($pdo, $tableIdForMap);


if (!function_exists('parse_column_options')) {
    $optHelper = dirname(__DIR__, 3) . '/includes/column_options.php';
    if (is_file($optHelper)) {
        require_once $optHelper;
    }
}
$userDateFormat = (isset($currentUser) && is_array($currentUser) && isset($currentUser['date_format']) && is_string($currentUser['date_format']))
    ? $currentUser['date_format']
    : null;
$datePlaceholder = function_exists('get_date_placeholder') ? get_date_placeholder($userDateFormat) : 'YYYY-MM-DD';
?>
<div class="card shadow-sm border-0 mb-4 bg-light">
    <div class="card-body">
        <details id="search-filter-details" <?= !empty($hasActiveSearch) ? 'open' : '' ?>>
            <summary class="fw-bold fs-6 text-dark" style="cursor: pointer; outline: none;">
                <?= htmlspecialchars(__('data_entry.search_summary'), ENT_QUOTES, 'UTF-8') ?>
            </summary>
            <div class="mt-3 pt-3 border-top">
                <form id="search-form" onsubmit="return false;">
                    <input type="hidden" name="table_id" value="<?= (int)$activeTableId ?>">
                    <div class="row g-3">
                        <?php foreach ($columns as $col): ?>
                            <?php
                                $colId   = isset($col['id']) ? (int)$col['id'] : 0;
                                $colName = isset($col['column_name']) && is_string($col['column_name']) ? $col['column_name'] : '';
                                $dataType = isset($col['data_type']) && is_string($col['data_type']) ? $col['data_type'] : '';
                                $isDate  = ($dataType === 'DATE');
                            ?>
                            <div class="<?= $isDate ? 'col-md-8' : 'col-md-4' ?>">
                                <?php if ($isDate): ?>
                                    <fieldset class="border-0 p-0 m-0">
                                        <legend class="form-label small fw-bold mb-1"><?= htmlspecialchars($colName, ENT_QUOTES, 'UTF-8') ?></legend>
                                        <div class="d-flex gap-2 align-items-center">
                                            <label class="visually-hidden" for="date_from_<?= $colId ?>"><?= htmlspecialchars($colName . ' — ' . (__('data_entry.date_from_label') !== 'data_entry.date_from_label' ? __('data_entry.date_from_label') : 'From'), ENT_QUOTES, 'UTF-8') ?></label>
                                            <input type="text"
                                                   id="date_from_<?= $colId ?>"
                                                   name="date_filters[<?= $colId ?>][from]"
                                                   value="<?= htmlspecialchars($dateFilters[$colId]['from'] ?? '', ENT_QUOTES, 'UTF-8') ?>"
                                                   class="form-control form-control-sm"
                                                   autocomplete="off"
                                                   placeholder="<?= htmlspecialchars($datePlaceholder, ENT_QUOTES, 'UTF-8') ?>">
                                            <span class="text-muted small text-nowrap" aria-hidden="true"><?= htmlspecialchars(__('data_entry.date_to_label'), ENT_QUOTES, 'UTF-8') ?></span>
                                            <label class="visually-hidden" for="date_to_<?= $colId ?>"><?= htmlspecialchars($colName . ' — ' . (__('data_entry.date_to_label') !== 'data_entry.date_to_label' ? __('data_entry.date_to_label') : 'To'), ENT_QUOTES, 'UTF-8') ?></label>
                                            <input type="text"
                                                   id="date_to_<?= $colId ?>"
                                                   name="date_filters[<?= $colId ?>][to]"
                                                   value="<?= htmlspecialchars($dateFilters[$colId]['to'] ?? '', ENT_QUOTES, 'UTF-8') ?>"
                                                   class="form-control form-control-sm"
                                                   autocomplete="off"
                                                   placeholder="<?= htmlspecialchars($datePlaceholder, ENT_QUOTES, 'UTF-8') ?>">
                                        </div>
                                    </fieldset>
                                <?php elseif ($dataType === 'SELECT'): ?>
                                    <?php
                                        $rawOpts = isset($col['field_options']) && is_string($col['field_options']) ? $col['field_options'] : '';
                                        $choiceOptions = function_exists('parse_column_options')
                                            ? parse_column_options($rawOpts)
                                            : array_values(array_filter(array_map('trim', preg_split('/\r\n|\r|\n/', $rawOpts) ?: [])));
                                        $searchVal = isset($searchFilters[$colId]) && is_string($searchFilters[$colId]) ? $searchFilters[$colId] : '';
                                    ?>
                                    <label class="form-label small fw-bold" for="filter_<?= $colId ?>"><?= htmlspecialchars($colName, ENT_QUOTES, 'UTF-8') ?></label>
                                    <select id="filter_<?= $colId ?>" name="filters[<?= $colId ?>]" class="form-select form-select-sm">
                                        <option value=""><?= htmlspecialchars(__('data_entry.filter_all_option'), ENT_QUOTES, 'UTF-8') ?></option>
                                        <?php foreach ($choiceOptions as $opt): ?>
                                            <option value="<?= htmlspecialchars($opt, ENT_QUOTES, 'UTF-8') ?>" <?= ($searchVal === $opt) ? 'selected' : '' ?>><?= htmlspecialchars($opt, ENT_QUOTES, 'UTF-8') ?></option>
                                        <?php endforeach; ?>
                                    </select>
                                <?php elseif ($dataType === 'BOOLEAN'): ?>
                                    <?php
                                        $displayFormat = isset($col['boolean_display_format']) && is_string($col['boolean_display_format']) ? $col['boolean_display_format'] : 'yes_no';
                                        $opt1Text = __('data_entry.bool_yes_true');
                                        $opt2Text = __('data_entry.bool_no_false');
                                        if ($displayFormat === 'male_female') { $opt1Text = __('data_entry.bool_male'); $opt2Text = __('data_entry.bool_female'); }
                                        elseif ($displayFormat === 'true_false') { $opt1Text = __('data_entry.bool_true'); $opt2Text = __('data_entry.bool_false'); }
                                        elseif ($displayFormat === 'tick_cross') { $opt1Text = __('data_entry.bool_tick'); $opt2Text = __('data_entry.bool_cross'); }
                                        $searchVal = isset($searchFilters[$colId]) && is_string($searchFilters[$colId]) ? $searchFilters[$colId] : '';
                                    ?>
                                    <label class="form-label small fw-bold" for="filter_<?= $colId ?>"><?= htmlspecialchars($colName, ENT_QUOTES, 'UTF-8') ?></label>
                                    <select id="filter_<?= $colId ?>" name="filters[<?= $colId ?>]" class="form-select form-select-sm">
                                        <option value=""><?= htmlspecialchars(__('data_entry.filter_all_option'), ENT_QUOTES, 'UTF-8') ?></option>
                                        <option value="1" <?= ($searchVal === '1') ? 'selected' : '' ?>><?= $opt1Text ?></option>
                                        <option value="0" <?= ($searchVal === '0') ? 'selected' : '' ?>><?= $opt2Text ?></option>
                                    </select>
                                <?php else: ?>
                                    <label class="form-label small fw-bold" for="filter_<?= $colId ?>"><?= htmlspecialchars($colName, ENT_QUOTES, 'UTF-8') ?></label>
                                    <input type="text"
                                           id="filter_<?= $colId ?>"
                                           name="filters[<?= $colId ?>]"
                                           value="<?= htmlspecialchars($searchFilters[$colId] ?? '', ENT_QUOTES, 'UTF-8') ?>"
                                           placeholder="<?= htmlspecialchars(__('data_entry.filter_placeholder'), ENT_QUOTES, 'UTF-8') ?>"
                                           class="form-control form-control-sm"
                                           autocomplete="off">
                                <?php endif; ?>
                            </div>
                        <?php endforeach; ?>
                    </div>

                    <div class="mt-3 d-flex gap-2 flex-wrap align-items-center">
                        <button type="button" id="clear-search" class="btn btn-sm btn-secondary">
                            <?= htmlspecialchars(__('data_entry.reset_filter_btn'), ENT_QUOTES, 'UTF-8') ?>
                        </button>
                    <?php if (!empty($tableHasMap)): ?>
                    <a class="btn btn-sm btn-outline-secondary" href="<?= htmlspecialchars($basePath ?? $baseUrl ?? '', ENT_QUOTES, 'UTF-8') ?>/tables/<?= (int)$activeTableId ?>/map"><?= htmlspecialchars(__('map.open_btn') !== 'map.open_btn' ? __('map.open_btn') : 'Map', ENT_QUOTES, 'UTF-8') ?></a>
                    <?php endif; ?>
                        <?php if (!empty($canExport)): ?>
                        <a href="#" id="export-csv-btn" class="btn btn-sm btn-outline-secondary ">
                            <?= htmlspecialchars(__('data_entry.csv_entire_btn'), ENT_QUOTES, 'UTF-8') ?>
                        </a>
                        <a href="#" id="export-json-btn" class="btn btn-sm btn-outline-secondary ">
                            <?= htmlspecialchars(__('data_entry.json_entire_btn'), ENT_QUOTES, 'UTF-8') ?>
                        </a>
                        <button type="button" id="copy-clipboard-btn" class="btn btn-sm btn-outline-secondary">
                            <?= htmlspecialchars(__('data_entry.copy_entire_btn'), ENT_QUOTES, 'UTF-8') ?>
                        </button>
                        <a href="#" id="print-records-btn" class="btn btn-sm btn-outline-secondary ">
                            <?= htmlspecialchars(__('cols.print_btn') !== 'cols.print_btn' ? __('cols.print_btn') : 'Print', ENT_QUOTES, 'UTF-8') ?>
                        </a>
                        <?php endif; ?>
                    </div>
                </form>
            </div>
        </details>
    </div>
</div>
