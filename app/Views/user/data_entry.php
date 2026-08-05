<?php
/**
 * MIGRATED FILE MAPPING
 * ---------------------
 * Original Old File: user/data_entry.php/user/actions/save_data_entry.php
 * Migrated Date: 2026-08-05 04:53:40
 */declare(strict_types=1);

/**
 * MIGRATED FILE MAPPING
 * ---------------------
 * Original Old File: user/data_entry.php
 * Migrated Date: 2026-08-04 12:00:00
 */

/** @string $message */
/** @string $error */
/** @int $totalTablesCount */
/** @int $totalColumnsCount */
/** @array<int, array<string, mixed>> $availableTables */
/** @int $activeTableId */
/** @string $userDateFormat */
/** @string $datePlaceholder */
/** @array<int, array<string, mixed>> $columns */
/** @bool $duplicateWarning */
/** @array<int, array<string, mixed>> $matches */
/** @array<int, string> $submittedData */
/** @bool $hasActiveSearch */
/** @array<int, string> $searchFilters */
/** @array<int, array{from?: string, to?: string}> $dateFilters */
/** @array<int, array<string, mixed>> $paginatedRecords */
/** @array<int, array<int, string>> $recordValues */
/** @int $totalPages */
/** @int $page */
/** @bool $isAdmin */
/** @bool $isModerationEnabled */
/** @array{id: int|string, username: string} $currentUser */

require_once __DIR__ . '/../partials/header.php';
?>

<div class="container-fluid py-4" style="max-width: 1500px;" role="region" aria-label="Data Entry Workstation">
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

    <?php if ($totalTablesCount === 0): ?>
        <div class="card shadow-sm border-0 bg-warning bg-opacity-10 text-warning-emphasis p-4 mb-4">
            <h3 class="h5 fw-bold mb-2"><?= htmlspecialchars(__('data_entry.no_tables_heading'), ENT_QUOTES, 'UTF-8') ?></h3>
            <p class="mb-2"><?= htmlspecialchars(__('data_entry.no_tables_desc'), ENT_QUOTES, 'UTF-8') ?></p>
            <?php if ($isAdmin): ?>
                <p class="mb-2"><?= __('data_entry.admin_tables_prompt') ?></p>
                <a href="/admin/manage_tables.php" class="btn btn-sm btn-primary mt-1 text-decoration-none"><?= htmlspecialchars(__('data_entry.go_manage_tables'), ENT_QUOTES, 'UTF-8') ?></a>
            <?php else: ?>
                <p class="mb-0"><?= htmlspecialchars(__('data_entry.contact_admin_tables'), ENT_QUOTES, 'UTF-8') ?></p>
            <?php endif; ?>
        </div>
    <?php elseif ($totalColumnsCount === 0): ?>
        <div class="card shadow-sm border-0 bg-warning bg-opacity-10 text-warning-emphasis p-4 mb-4">
            <h3 class="h5 fw-bold mb-2"><?= htmlspecialchars(__('data_entry.no_cols_heading'), ENT_QUOTES, 'UTF-8') ?></h3>
            <p class="mb-2"><?= htmlspecialchars(__('data_entry.no_cols_desc'), ENT_QUOTES, 'UTF-8') ?></p>
            <?php if ($isAdmin): ?>
                <p class="mb-2"><?= __('data_entry.admin_cols_prompt') ?></p>
                <a href="/admin/manage_tables.php" class="btn btn-sm btn-primary mt-1 text-decoration-none"><?= htmlspecialchars(__('data_entry.go_manage_tables'), ENT_QUOTES, 'UTF-8') ?></a>
            <?php else: ?>
                <p class="mb-0"><?= htmlspecialchars(__('data_entry.contact_admin_cols'), ENT_QUOTES, 'UTF-8') ?></p>
            <?php endif; ?>
        </div>
    <?php else: ?>

        <?php if (count($availableTables) > 1): ?>
            <div class="card shadow-sm border-0 bg-light p-3 mb-4 d-flex flex-row align-items-center gap-3 flex-wrap">
                <label for="data_entry_table_selector" class="fw-bold"><?= htmlspecialchars(__('data_entry.active_table_label'), ENT_QUOTES, 'UTF-8') ?></label>
                <select id="data_entry_table_selector" class="form-select form-select-sm" style="max-width: 300px;" onchange="location.href='/user/data_entry.php?table_id=' + this.value;">
                    <?php foreach ($availableTables as $at): ?>
                        <?php 
                            $atId = isset($at['id']) ? (int)$at['id'] : 0;
                            $atName = isset($at['table_name']) && is_string($at['table_name']) ? $at['table_name'] : '';
                        ?>
                        <option value="<?= $atId ?>" <?= ($atId === $activeTableId) ? 'selected' : '' ?>>
                            <?= htmlspecialchars($atName, ENT_QUOTES, 'UTF-8') ?>
                        </option>
                    <?php endforeach; ?>
                </select>
            </div>
        <?php endif; ?>

        <?php if (!$duplicateWarning): ?>
            <div class="card shadow-sm border-0 mb-4 bg-light">
                <div class="card-body">
                    <details id="add-entry-details" open>
                        <summary class="fw-bold fs-6 text-dark" style="cursor: pointer; outline: none;">
                            <?= htmlspecialchars(__('data_entry.add_entry_summary'), ENT_QUOTES, 'UTF-8') ?>
                        </summary>
                        <div class="mt-3 pt-3 border-top">
                            <form method="POST" action="/user/actions/save_data_entry.php" id="data-entry-form">
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
                                                <input type="text" id="col_<?= $colId ?>" name="filters[<?= $colId ?>]" value="<?= htmlspecialchars($savedVal, ENT_QUOTES, 'UTF-8') ?>" placeholder="<?= $datePlaceholder ?>" class="form-control form-control-sm" title="<?= htmlspecialchars(__('data_entry.date_title_hint'), ENT_QUOTES, 'UTF-8') ?>" <?= $isRequired ? 'required' : '' ?>>
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
                const form = document.getElementById('data-entry-form');
                if (form) {
                    form.addEventListener('keydown', (e) => {
                        if ((e.ctrlKey || e.metaKey) && e.key === 'Enter') {
                            e.preventDefault();
                            form.submit();
                        }
                        if (e.key === 'Escape' && (e.target.tagName === 'INPUT' || e.target.tagName === 'SELECT')) {
                            e.preventDefault();
                            e.target.value = '';
                        }
                    });
                }
            });
            </script>
        <?php endif; ?>

        <?php if ($duplicateWarning): ?>
            <div class="alert alert-danger shadow-sm border-0 mb-4 p-4">
                <h4 class="h5 fw-bold mb-2"><?= htmlspecialchars(__('data_entry.dup_heading'), ENT_QUOTES, 'UTF-8') ?></h4>
                <p class="mb-3"><?= htmlspecialchars(__('data_entry.dup_desc'), ENT_QUOTES, 'UTF-8') ?></p>
                <ul class="mb-3">
                    <?php foreach ($matches as $match): ?>
                        <?php 
                            $mId = isset($match['id']) ? (int)$match['id'] : 0;
                            $mVal = isset($match['value_content']) && is_string($match['value_content']) ? $match['value_content'] : '';
                        ?>
                        <li><?= htmlspecialchars(sprintf(__('data_entry.dup_item_format'), $mId, $mVal), ENT_QUOTES, 'UTF-8') ?></li>
                    <?php endforeach; ?>
                </ul>
                <p class="mb-3"><?= htmlspecialchars(__('data_entry.dup_prompt'), ENT_QUOTES, 'UTF-8') ?></p>
                <form method="POST" action="/user/actions/save_data_entry.php">
                    <?= csrf_field() ?>
                    <input type="hidden" name="action" value="insert_record">
                    <input type="hidden" name="table_id" value="<?= $activeTableId ?>">
                    <?php foreach ($submittedData as $cid => $cval): ?>
                        <input type="hidden" name="filters[<?= (int)$cid ?>]" value="<?= htmlspecialchars((string)$cval, ENT_QUOTES, 'UTF-8') ?>">
                    <?php endforeach; ?>
                    <input type="hidden" name="confirm_duplicate" value="1">
                    <button type="submit" class="btn btn-sm btn-danger"><?= htmlspecialchars(__('data_entry.dup_confirm_btn'), ENT_QUOTES, 'UTF-8') ?></button>
                    <a href="/user/data_entry.php?table_id=<?= $activeTableId ?>" class="btn btn-sm btn-secondary ms-2 text-decoration-none"><?= htmlspecialchars(__('btn.cancel'), ENT_QUOTES, 'UTF-8') ?></a>
                </form>
            </div>
        <?php endif; ?>

        <!-- Search / Filter Card -->
        <div class="card shadow-sm border-0 mb-4 bg-light">
            <div class="card-body">
                <details id="search-filter-details" <?= $hasActiveSearch ? 'open' : '' ?>>
                    <summary class="fw-bold fs-6 text-dark" style="cursor: pointer; outline: none;">
                        <?= htmlspecialchars(__('data_entry.search_summary'), ENT_QUOTES, 'UTF-8') ?>
                    </summary>
                    <div class="mt-3 pt-3 border-top">
                        <form method="GET" action="/user/data_entry.php" id="search-form">
                            <input type="hidden" name="table_id" value="<?= $activeTableId ?>">
                            <input type="hidden" name="scroll_pos" id="scroll_pos_input" value="0">
                            <input type="hidden" name="focus_id" id="focus_id_input" value="">
                            <input type="hidden" name="search_open" id="search_open_input" value="<?= $hasActiveSearch ? '1' : '0' ?>">

                            <div class="row g-3">
                                <?php foreach ($columns as $col): ?>
                                    <?php 
                                        $colId = isset($col['id']) ? (int)$col['id'] : 0;
                                        $colName = isset($col['column_name']) && is_string($col['column_name']) ? $col['column_name'] : '';
                                        $dataType = isset($col['data_type']) && is_string($col['data_type']) ? $col['data_type'] : '';
                                        $isDate = ($dataType === 'DATE');
                                    ?>
                                    <div class="<?= $isDate ? 'col-md-8' : 'col-md-4' ?>">
                                        <label for="search_<?= $colId ?>" class="form-label small fw-bold"><?= htmlspecialchars($colName, ENT_QUOTES, 'UTF-8') ?>:</label>
                                        
                                        <?php if ($isDate): ?>
                                            <div class="d-flex gap-2 align-items-center">
                                                <input type="text" id="search_date_from_<?= $colId ?>" name="date_filters[<?= $colId ?>][from]" value="<?= htmlspecialchars($date_filters[$colId]['from'] ?? '', ENT_QUOTES, 'UTF-8') ?>" class="form-control form-control-sm" placeholder="<?= $datePlaceholder ?>" title="<?= htmlspecialchars(__('data_entry.date_title_hint'), ENT_QUOTES, 'UTF-8') ?>">
                                                <span class="text-muted small text-nowrap"><?= htmlspecialchars(__('data_entry.date_to_label'), ENT_QUOTES, 'UTF-8') ?></span>
                                                <input type="text" id="search_date_to_<?= $colId ?>" name="date_filters[<?= $colId ?>][to]" value="<?= htmlspecialchars($date_filters[$colId]['to'] ?? '', ENT_QUOTES, 'UTF-8') ?>" class="form-control form-control-sm" placeholder="<?= $datePlaceholder ?>" title="<?= htmlspecialchars(__('data_entry.date_title_hint'), ENT_QUOTES, 'UTF-8') ?>">
                                            </div>
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
                                            <select id="search_<?= $colId ?>" name="filters[<?= $colId ?>]" class="form-select form-select-sm">
                                                <option value=""><?= htmlspecialchars(__('data_entry.filter_all_option'), ENT_QUOTES, 'UTF-8') ?></option>
                                                <option value="1" <?= ($searchVal === '1') ? 'selected' : '' ?>><?= $opt1Text ?></option>
                                                <option value="0" <?= ($searchVal === '0') ? 'selected' : '' ?>><?= $opt2Text ?></option>
                                            </select>
                                        <?php else: ?>
                                            <input type="text" id="search_<?= $colId ?>" name="filters[<?= $colId ?>]" value="<?= htmlspecialchars($searchFilters[$colId] ?? '', ENT_QUOTES, 'UTF-8') ?>" placeholder="<?= htmlspecialchars(__('data_entry.filter_placeholder'), ENT_QUOTES, 'UTF-8') ?>" class="form-control form-control-sm">
                                        <?php endif; ?>
                                    </div>
                                <?php endforeach; ?>
                            </div>

                            <!-- Unified Action Bar -->
                            <div class="mt-3 d-flex gap-2 flex-wrap align-items-center">
                                <button type="submit" class="btn btn-sm btn-primary" id="apply-search-btn"><?= htmlspecialchars(__('data_entry.apply_filters_btn'), ENT_QUOTES, 'UTF-8') ?></button>
                                <a href="/user/data_entry.php?table_id=<?= $activeTableId ?>" class="btn btn-sm btn-secondary text-decoration-none" id="reset-filter-btn"><?= htmlspecialchars(__('data_entry.reset_filter_btn'), ENT_QUOTES, 'UTF-8') ?></a>
                                <a href="/user/data_entry.php?table_id=<?= $activeTableId ?>&export_csv=1&<?= htmlspecialchars(http_build_query(['filters' => $searchFilters, 'date_filters' => $dateFilters]), ENT_QUOTES, 'UTF-8') ?>" class="btn btn-sm btn-outline-secondary text-decoration-none" id="export-csv-btn"><?= htmlspecialchars(__('data_entry.csv_entire_btn'), ENT_QUOTES, 'UTF-8') ?></a>
                                <a href="/api/export_json.php?table_id=<?= $activeTableId ?>&<?= htmlspecialchars(http_build_query(['filters' => $searchFilters, 'date_filters' => $dateFilters]), ENT_QUOTES, 'UTF-8') ?>" class="btn btn-sm btn-outline-secondary text-decoration-none" id="export-json-btn"><?= htmlspecialchars(__('data_entry.json_entire_btn'), ENT_QUOTES, 'UTF-8') ?></a>
                                <button type="button" id="copy-clipboard-btn" class="btn btn-sm btn-outline-secondary"><?= htmlspecialchars(__('data_entry.copy_entire_btn'), ENT_QUOTES, 'UTF-8') ?></button>
                            </div>
                        </form>
                    </div>
                </details>
            </div>
        </div>

        <script>
        document.addEventListener('DOMContentLoaded', () => {
            const addEntry = document.getElementById('add-entry-details');
            const searchFilter = document.getElementById('search-filter-details');
            const searchForm = document.getElementById('search-form');
            const scrollInput = document.getElementById('scroll_pos_input');
            const focusInput = document.getElementById('focus_id_input');
            const searchOpenInput = document.getElementById('search_open_input');
            const applyBtn = document.getElementById('apply-search-btn');
            const resetBtn = document.getElementById('reset-filter-btn');

            const urlParams = new URLSearchParams(window.location.search);
            const savedScroll = urlParams.get('scroll_pos');
            const savedFocusId = urlParams.get('focus_id');
            const savedSearchOpen = urlParams.get('search_open');

            if (savedSearchOpen === '1' && searchFilter) {
                searchFilter.open = true;
                if (addEntry) addEntry.open = false;
            }

            if (addEntry && searchFilter) {
                addEntry.addEventListener('toggle', () => {
                    if (addEntry.open) {
                        searchFilter.open = false;
                        if (searchOpenInput) searchOpenInput.value = '0';
                    }
                });
                searchFilter.addEventListener('toggle', () => {
                    if (searchFilter.open) {
                        addEntry.open = false;
                        if (searchOpenInput) searchOpenInput.value = '1';
                    }
                });
            }

            if (savedScroll) {
                window.scrollTo(0, parseInt(savedScroll, 10));
            }
            if (savedFocusId) {
                const elToFocus = document.getElementById(savedFocusId);
                if (elToFocus) {
                    elToFocus.focus();
                    if (elToFocus.tagName === 'INPUT' && elToFocus.type === 'text') {
                        const val = elToFocus.value;
                        elToFocus.value = '';
                        elToFocus.value = val;
                    }
                }
            }

            function updateActionButtonsState() {
                if (!searchForm) return;
                let hasActiveFilter = false;
                searchForm.querySelectorAll('input[type="text"], select').forEach(input => {
                    if (input.name && input.name !== 'table_id' && input.value.trim() !== '') {
                        hasActiveFilter = true;
                    }
                });

                const csvBtn = document.getElementById('export-csv-btn');
                const jsonBtn = document.getElementById('export-json-btn');
                const copyBtn = document.getElementById('copy-clipboard-btn');

                if (csvBtn) csvBtn.textContent = hasActiveFilter ? '<?= htmlspecialchars(__('data_entry.csv_filtered_btn'), ENT_QUOTES, 'UTF-8') ?>' : '<?= htmlspecialchars(__('data_entry.csv_entire_btn'), ENT_QUOTES, 'UTF-8') ?>';
                if (jsonBtn) jsonBtn.textContent = hasActiveFilter ? '<?= htmlspecialchars(__('data_entry.json_filtered_btn'), ENT_QUOTES, 'UTF-8') ?>' : '<?= htmlspecialchars(__('data_entry.json_entire_btn'), ENT_QUOTES, 'UTF-8') ?>';
                if (copyBtn) copyBtn.textContent = hasActiveFilter ? '<?= htmlspecialchars(__('data_entry.copy_filtered_btn'), ENT_QUOTES, 'UTF-8') ?>' : '<?= htmlspecialchars(__('data_entry.copy_entire_btn'), ENT_QUOTES, 'UTF-8') ?>';
            }

            if (searchForm && applyBtn) {
                const allInputs = searchForm.querySelectorAll('input, select');
                allInputs.forEach(input => {
                    input.addEventListener('focus', () => {
                        if (focusInput) focusInput.value = input.id;
                    });
                    input.addEventListener('input', updateActionButtonsState);
                    input.addEventListener('change', updateActionButtonsState);
                });

                applyBtn.addEventListener('click', () => {
                    if (scrollInput) scrollInput.value = window.scrollY;
                    if (searchOpenInput && searchFilter) searchOpenInput.value = searchFilter.open ? '1' : '0';
                });
            }

            if (resetBtn) {
                resetBtn.addEventListener('click', (e) => {
                    e.preventDefault();
                    const currentScroll = window.scrollY;
                    location.href = `/user/data_entry.php?table_id=<?= $activeTableId ?>&scroll_pos=${currentScroll}&search_open=1`;
                });
            }

            document.getElementById('copy-clipboard-btn')?.addEventListener('click', () => {
                const table = document.querySelector('.table');
                if (!table) return;

                let textContent = '';
                table.querySelectorAll('tr').forEach(row => {
                    let rowData = [];
                    row.querySelectorAll('th, td').forEach(cell => {
                        rowData.push(cell.innerText.trim());
                    });
                    textContent += rowData.join('\t') + '\n';
                });

                navigator.clipboard.writeText(textContent).then(() => {
                    alert('<?= htmlspecialchars(__('data_entry.clipboard_alert'), ENT_QUOTES, 'UTF-8') ?>');
                }).catch(err => {
                    console.error('Failed to copy text: ', err);
                });
            });

            updateActionButtonsState();
        });
        </script>

        <hr class="my-4">

        <h3 class="h5 fw-bold mb-3"><?= htmlspecialchars(__('data_entry.existing_records_heading'), ENT_QUOTES, 'UTF-8') ?></h3>
        
        <!-- Existing Records Table Card -->
        <div class="card shadow-sm border-0 mb-4">
            <div class="table-responsive">
                <table class="table table-hover align-middle mb-0" role="table">
                    <thead class="table-light">
                        <tr>
                            <?php foreach ($columns as $col): ?>
                                <?php $cName = isset($col['column_name']) && is_string($col['column_name']) ? $col['column_name'] : ''; ?>
                                <th scope="col" class="py-3"><?= htmlspecialchars($cName, ENT_QUOTES, 'UTF-8') ?></th>
                            <?php endforeach; ?>
                            <th scope="col" class="py-3"><?= htmlspecialchars(__('data_entry.th_added_by'), ENT_QUOTES, 'UTF-8') ?></th>
                            <th scope="col" class="py-3"><?= htmlspecialchars(__('data_entry.th_date_created'), ENT_QUOTES, 'UTF-8') ?></th>
                            <th scope="col" class="py-3 text-end pe-3"><?= htmlspecialchars(__('index.th_actions'), ENT_QUOTES, 'UTF-8') ?></th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php if (empty($paginatedRecords)): ?>
                            <tr>
                                <td colspan="<?= count($columns) + 3 ?>" class="text-center py-4 text-muted"><?= htmlspecialchars(__('data_entry.no_records'), ENT_QUOTES, 'UTF-8') ?></td>
                            </tr>
                        <?php else: ?>
                            <?php foreach ($paginatedRecords as $rec): ?>
                                <?php 
                                    $recId = isset($rec['id']) ? (int)$rec['id'] : 0;
                                    $userId = isset($rec['user_id']) ? (int)$rec['user_id'] : 0;
                                    $username = isset($rec['username']) && is_string($rec['username']) ? $rec['username'] : 'User_Anon';
                                    $firstName = isset($rec['first_name']) && is_string($rec['first_name']) ? $rec['first_name'] : '';
                                    $surname = isset($rec['surname']) && is_string($rec['surname']) ? $rec['surname'] : '';
                                    $attrMode = isset($rec['attribution_display_mode']) && is_string($rec['attribution_display_mode']) ? $rec['attribution_display_mode'] : 'initials_random';
                                    $recCreated = isset($rec['created_at']) && is_string($rec['created_at']) ? $rec['created_at'] : '';
                                ?>
                                <tr>
                                    <?php foreach ($columns as $col): ?>
                                        <?php 
                                            $colId = isset($col['id']) ? (int)$col['id'] : 0;
                                            $dataType = isset($col['data_type']) && is_string($col['data_type']) ? $col['data_type'] : '';
                                            $rawVal = $recordValues[$recId][$colId] ?? '';
                                        ?>
                                        <td>
                                            <?php 
                                                if ($dataType === 'BOOLEAN') {
                                                    $fmt = isset($col['boolean_display_format']) && is_string($col['boolean_display_format']) ? $col['boolean_display_format'] : 'yes_no';
                                                    if ($fmt === 'male_female') {
                                                        $isTrue = filter_var($rawVal, FILTER_VALIDATE_BOOLEAN);
                                                        echo ($rawVal !== '' && $rawVal !== null) ? ($isTrue ? __('data_entry.bool_male') : __('data_entry.bool_female')) : __('data_entry.na_value');
                                                    } else {
                                                        echo htmlspecialchars(format_boolean_value($rawVal, $fmt), ENT_QUOTES, 'UTF-8');
                                                    }
                                                } elseif ($dataType === 'DATE') {
                                                    echo htmlspecialchars(format_display_date($rawVal, $userDateFormat), ENT_QUOTES, 'UTF-8');
                                                } else {
                                                    echo htmlspecialchars($rawVal, ENT_QUOTES, 'UTF-8');
                                                }
                                            ?>
                                        </td>
                                    <?php endforeach; ?>
                                    <td>
                                        <em class="text-muted">
                                            <?= htmlspecialchars(format_user_display_name($pdo, [
                                                'id' => $userId,
                                                'username' => $username,
                                                'first_name' => $firstName,
                                                'surname' => $surname,
                                                'attribution_display_mode' => $attrMode
                                            ], $currentUser), ENT_QUOTES, 'UTF-8') ?>
                                        </em>
                                    </td>
                                    <td><?= htmlspecialchars($recCreated, ENT_QUOTES, 'UTF-8') ?></td>
                                    <td class="text-end pe-3 text-nowrap">
                                        <a href="/record_history.php?record_id=<?= $recId ?>" class="btn btn-sm btn-outline-secondary py-0 px-2 text-decoration-none me-1" style="font-size: 0.75rem;"><?= htmlspecialchars(__('api_search.history_btn'), ENT_QUOTES, 'UTF-8') ?></a>
                                        <?php if ($isModerationEnabled): ?>
                                            <a href="/user/suggest_edit.php?record_id=<?= $recId ?>" class="btn btn-sm btn-outline-primary py-0 px-2 text-decoration-none" style="font-size: 0.75rem;"><?= htmlspecialchars(__('api_search.suggest_edit_btn'), ENT_QUOTES, 'UTF-8') ?></a>
                                        <?php endif; ?>
                                    </td>
                                </tr>
                            <?php endforeach; ?>
                        <?php endif; ?>
                    </tbody>
                </table>
            </div>
        </div>

        <?php if ($totalPages > 1): ?>
            <nav aria-label="Records Pagination" class="d-flex justify-content-center">
                <ul class="pagination pagination-sm">
                    <li class="page-item disabled"><span class="page-link"><?= htmlspecialchars(__('data_entry.page_label'), ENT_QUOTES, 'UTF-8') ?></span></li>
                    <?php for ($i = 1; $i <= $totalPages; $i++): ?>
                        <?php 
                            $queryParams = $_GET;
                            $queryParams['page'] = $i;
                            $pageUrl = '/user/data_entry.php?' . http_build_query($queryParams);
                        ?>
                        <li class="page-item <?= ($i === $page) ? 'active' : '' ?>">
                            <a href="<?= $pageUrl ?>" class="page-link"><?= $i ?></a>
                        </li>
                    <?php endfor; ?>
                </ul>
            </nav>
        <?php endif; ?>

    <?php endif; ?>
</div>

<?php require_once __DIR__ . '/../partials/footer.php'; ?>
