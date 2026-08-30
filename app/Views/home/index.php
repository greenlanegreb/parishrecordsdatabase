<?php
/**
 * MIGRATED FILE MAPPING
 * ---------------------
 * Original Old File: root/index.php
 * Migrated Date: 2026-08-05 06:40:21
 */
declare(strict_types=1);
if (!function_exists('parse_column_options')) {
    $optHelper = dirname(__DIR__, 3) . '/includes/column_options.php';
    if (is_file($optHelper)) {
        require_once $optHelper;
    }
}

/**
 * @var \PDO $pdo
 * @var int $totalTablesCount
 * @var int $totalColumnsCount
 * @var array<int, array<string, mixed>> $availableTables
 * @var int $activeTableId
 * @var string $datePlaceholder
 * @var array<int, array<string, mixed>> $columns
 * @var string $systemName
 * @var string $message
 * @var string $error
 * @var string $suggestReturnUrl
 * @var int $suggestTableId
 * @var bool $isAdmin
 * @var array|null $currentUser
 */

require_once ROOT_PATH . '/partials/header.php';
$basePath = defined('BASE_PATH') ? rtrim(BASE_PATH, '/') : '';
?>

<div class="container my-4">
    <!-- DYNAMIC NOTICES MODULE -->
    <?php include ROOT_PATH . '/partials/notices_banner.php'; ?>

    <?php if ($message !== ''): ?>
        <div class="alert alert-success alert-dismissible fade show shadow-sm" role="alert">
            <strong><?= htmlspecialchars($message, ENT_QUOTES, 'UTF-8') ?></strong>
            <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
        </div>
    <?php endif; ?>

    <?php if ($error !== ''): ?>
        <div class="alert alert-danger alert-dismissible fade show shadow-sm" role="alert">
            <strong><?= htmlspecialchars($error, ENT_QUOTES, 'UTF-8') ?></strong>
            <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
        </div>
    <?php endif; ?>

    <?php if ($totalTablesCount === 0 || empty($availableTables)): ?>
        <div class="card border-0 shadow-sm p-4 bg-warning bg-opacity-15 text-warning-emphasis mb-4">
            <h3 class="h5 fw-bold mb-2">⚠️ <?= htmlspecialchars(__('index.no_tables_heading'), ENT_QUOTES, 'UTF-8') ?></h3>
            <p class="mb-3"><?= htmlspecialchars(__('index.no_tables_desc'), ENT_QUOTES, 'UTF-8') ?></p>
            <?php if ($isAdmin): ?>
                <p><?= __('index.admin_create_table_guide', ['link' => $basePath . '/admin/tables']) ?></p>
                <div>
                    <a href="<?= $basePath ?>/admin/tables" class="btn btn-primary btn-sm px-4"><?= htmlspecialchars(__('index.go_to_manage_tables'), ENT_QUOTES, 'UTF-8') ?></a>
                </div>
            <?php elseif ($currentUser !== null): ?>
                <p class="mb-0"><?= htmlspecialchars(__('index.contact_admin_tables'), ENT_QUOTES, 'UTF-8') ?></p>
            <?php else: ?>
                <p class="mb-0"><?= __('index.guest_login_tables_guide', ['login_link' => $basePath . '/login']) ?></p>
            <?php endif; ?>
        </div>
    <?php elseif ($totalColumnsCount === 0): ?>
        <div class="card border-0 shadow-sm p-4 bg-warning bg-opacity-15 text-warning-emphasis mb-4">
            <h3 class="h5 fw-bold mb-2">⚠️ <?= htmlspecialchars(__('index.no_columns_heading'), ENT_QUOTES, 'UTF-8') ?></h3>
            <p class="mb-3"><?= htmlspecialchars(__('index.no_columns_desc'), ENT_QUOTES, 'UTF-8') ?></p>
            <?php if ($isAdmin): ?>
                <p><?= htmlspecialchars(__('index.admin_add_columns_guide'), ENT_QUOTES, 'UTF-8') ?></p>
                <div>
                    <a href="<?= $basePath ?>/admin/tables" class="btn btn-primary btn-sm px-4"><?= htmlspecialchars(__('index.go_to_manage_tables'), ENT_QUOTES, 'UTF-8') ?></a>
                </div>
            <?php else: ?>
                <p class="mb-0"><?= htmlspecialchars(__('index.contact_admin_columns'), ENT_QUOTES, 'UTF-8') ?></p>
            <?php endif; ?>
        </div>
    <?php else: ?>

        <!-- TABLE SELECTOR -->
        <?php if (count($availableTables) > 1): ?>
            <div class="card border-0 shadow-sm p-3 mb-4 bg-white">
                <div class="row align-items-center g-3">
                    <div class="col-auto">
                        <label for="public_table_selector" class="col-form-label fw-bold"><?= htmlspecialchars(__('index.select_directory_database'), ENT_QUOTES, 'UTF-8') ?></label>
                    </div>
                    <div class="col-auto" style="min-width: 280px;">
                        <select id="public_table_selector" class="form-select form-select-sm"
                                onchange="location.href='<?= $basePath ?>/?table_id='+this.value;">
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
                </div>
            </div>
        <?php endif; ?>

        <!-- SEARCH & EXPORT -->
        <section class="card border-0 shadow-sm p-4 mb-4 bg-white" aria-label="Advanced Search Section">
            <h3 class="h5 fw-bold text-dark mb-3"><?= htmlspecialchars(__('search.heading'), ENT_QUOTES, 'UTF-8') ?></h3>
            <form id="search-form">
                <input type="hidden" name="table_id" value="<?= $activeTableId ?>">
                <div class="row g-3 mb-4">
                    <?php foreach ($columns as $col): ?>
                        <?php 
                            if (!empty($col['exclude_from_public_search'])) {
                                continue; 
                            }
                            $cId = isset($col['id']) ? (int)$col['id'] : 0;
                            $cName = isset($col['column_name']) && is_string($col['column_name']) ? $col['column_name'] : '';
                            $dataType = isset($col['data_type']) && is_string($col['data_type']) ? $col['data_type'] : '';
                        ?>
                        <div class="col-md-4 col-sm-6">
                            <?php if ($dataType !== 'DATE'): ?>
                            <label for="filter_<?= $cId ?>" class="form-label small fw-bold">
                                <?= htmlspecialchars($cName, ENT_QUOTES, 'UTF-8') ?>
                            </label>
                            <?php endif; ?>

                            <?php if ($dataType === 'BOOLEAN'): ?>
                                <?php
                                    $fmt = isset($col['boolean_display_format']) && is_string($col['boolean_display_format']) ? $col['boolean_display_format'] : 'yes_no';
                                    $opt1 = __('index.opt_yes_true'); 
                                    $opt2 = __('index.opt_no_false');
                                    if ($fmt === 'male_female') { 
                                        $opt1 = __('index.opt_male'); 
                                        $opt2 = __('index.opt_female'); 
                                    } elseif ($fmt === 'true_false') { 
                                        $opt1 = __('index.opt_true'); 
                                        $opt2 = __('index.opt_false'); 
                                    } elseif ($fmt === 'tick_cross') { 
                                        $opt1 = __('index.opt_tick'); 
                                        $opt2 = __('index.opt_cross'); 
                                    }
                                ?>
                                <select id="filter_<?= $cId ?>"
                                        name="filters[<?= $cId ?>]"
                                        class="form-select form-select-sm"
                                        aria-label="<?= htmlspecialchars((__('index.filter_aria') !== 'index.filter_aria' ? __('index.filter_aria') : 'Search filter for') . ' ' . $cName, ENT_QUOTES, 'UTF-8') ?>">
                                    <option value=""><?= htmlspecialchars(__('index.option_all'), ENT_QUOTES, 'UTF-8') ?></option>
                                    <option value="1"><?= htmlspecialchars($opt1, ENT_QUOTES, 'UTF-8') ?></option>
                                    <option value="0"><?= htmlspecialchars($opt2, ENT_QUOTES, 'UTF-8') ?></option>
                                </select>

                            <?php elseif ($dataType === 'SELECT'): ?>
                                <?php
                                    $rawOpts = isset($col['field_options']) && is_string($col['field_options']) ? $col['field_options'] : '';
                                    $choiceOptions = function_exists('parse_column_options')
                                        ? parse_column_options($rawOpts)
                                        : array_values(array_filter(array_map('trim', preg_split('/\r\n|\r|\n/', $rawOpts) ?: [])));
                                ?>
                                <select id="filter_<?= $cId ?>"
                                        name="filters[<?= $cId ?>]"
                                        class="form-select form-select-sm"
                                        aria-label="<?= htmlspecialchars(__('index.filter_aria') !== 'index.filter_aria' ? __('index.filter_aria') : 'Search filter for', ENT_QUOTES, 'UTF-8') ?> <?= htmlspecialchars($cName, ENT_QUOTES, 'UTF-8') ?>">
                                    <option value=""><?= htmlspecialchars(__('index.option_all'), ENT_QUOTES, 'UTF-8') ?></option>
                                    <?php foreach ($choiceOptions as $opt): ?>
                                        <option value="<?= htmlspecialchars($opt, ENT_QUOTES, 'UTF-8') ?>"><?= htmlspecialchars($opt, ENT_QUOTES, 'UTF-8') ?></option>
                                    <?php endforeach; ?>
                                </select>
                            <?php elseif ($dataType === 'INT'): ?>
                                <input type="number"
                                       id="filter_<?= $cId ?>"
                                       name="filters[<?= $cId ?>]"
                                       class="form-control form-control-sm"
                                       <?= isset($col['min_value']) && $col['min_value'] !== null && $col['min_value'] !== '' ? 'min="' . (int)$col['min_value'] . '"' : '' ?>
                                       <?= isset($col['max_value']) && $col['max_value'] !== null && $col['max_value'] !== '' ? 'max="' . (int)$col['max_value'] . '"' : '' ?>
                                       aria-label="<?= htmlspecialchars(__('index.filter_aria') !== 'index.filter_aria' ? __('index.filter_aria') : 'Search filter for', ENT_QUOTES, 'UTF-8') ?> <?= htmlspecialchars($cName, ENT_QUOTES, 'UTF-8') ?>">
                            <?php elseif ($dataType === 'DATE'): ?>
                                <fieldset class="border-0 p-0 m-0">
                                    <legend class="form-label small fw-bold mb-1"><?= htmlspecialchars($cName, ENT_QUOTES, 'UTF-8') ?></legend>
                                    <div class="input-group input-group-sm">
                                        <label class="visually-hidden" for="date_from_<?= $cId ?>"><?= htmlspecialchars($cName . ' — ' . (__('index.date_from_label') !== 'index.date_from_label' ? __('index.date_from_label') : 'From'), ENT_QUOTES, 'UTF-8') ?></label>
                                        <input type="text"
                                               id="date_from_<?= $cId ?>"
                                               name="date_filters[<?= $cId ?>][from]"
                                               placeholder="<?= htmlspecialchars($datePlaceholder, ENT_QUOTES, 'UTF-8') ?>"
                                               class="form-control date-input"
                                               autocomplete="off">
                                        <span class="input-group-text bg-light text-muted small px-2" aria-hidden="true"><?= htmlspecialchars(__('index.date_to_label'), ENT_QUOTES, 'UTF-8') ?></span>
                                        <label class="visually-hidden" for="date_to_<?= $cId ?>"><?= htmlspecialchars($cName . ' — ' . (__('index.date_to_label') !== 'index.date_to_label' ? __('index.date_to_label') : 'To'), ENT_QUOTES, 'UTF-8') ?></label>
                                        <input type="text"
                                               id="date_to_<?= $cId ?>"
                                               name="date_filters[<?= $cId ?>][to]"
                                               placeholder="<?= htmlspecialchars($datePlaceholder, ENT_QUOTES, 'UTF-8') ?>"
                                               class="form-control date-input"
                                               autocomplete="off">
                                    </div>
                                </fieldset>

                            <?php else: ?>
                                <input type="text"
                                       id="filter_<?= $cId ?>"
                                       name="filters[<?= $cId ?>]"
                                       placeholder="<?= htmlspecialchars(__('index.search_placeholder'), ENT_QUOTES, 'UTF-8') ?>"
                                       class="form-control form-control-sm"
                                       aria-label="<?= htmlspecialchars((__('index.filter_aria') !== 'index.filter_aria' ? __('index.filter_aria') : 'Search filter for') . ' ' . $cName, ENT_QUOTES, 'UTF-8') ?>">
                            <?php endif; ?>
                        </div>
                    <?php endforeach; ?>
                </div>

                <!-- Action Bar -->
                <div class="d-flex gap-2 flex-wrap align-items-center pt-2 border-top">
                    <button type="button" id="clear-search" class="btn btn-secondary btn-sm px-3"><?= htmlspecialchars(__('search.reset'), ENT_QUOTES, 'UTF-8') ?></button>
                    <?php
                        $canExport = $canExport ?? false;
                        $mapsOn = isset($pdo) && $pdo instanceof PDO && function_exists('is_module_enabled') && is_module_enabled($pdo, 'maps');
                        $tableIdForMap = (int) ($activeTableId ?? 0);
                        $tableHasMap = $mapsOn && $tableIdForMap > 0
                            && class_exists(\App\Services\LocationValueService::class)
                            && \App\Services\LocationValueService::tableHasLocationColumn($pdo, $tableIdForMap);
                    ?>
                    <?php if (!empty($tableHasMap)): ?>
                        <a class="btn btn-outline-secondary btn-sm px-3" href="<?= htmlspecialchars($basePath ?? $baseUrl ?? '', ENT_QUOTES, 'UTF-8') ?>/tables/<?= (int)$activeTableId ?>/map"><?= htmlspecialchars(__('map.open_btn') !== 'map.open_btn' ? __('map.open_btn') : 'Map', ENT_QUOTES, 'UTF-8') ?></a>
                    <?php endif; ?>
                    <?php if (!empty($canExport)): ?>
                    <a href="#" id="export-csv-btn" class="btn btn-outline-secondary btn-sm px-3" role="button"><?= htmlspecialchars(__('index.download_entire_csv'), ENT_QUOTES, 'UTF-8') ?></a>
                    <a href="#" id="export-json-btn" class="btn btn-outline-secondary btn-sm px-3" role="button"><?= htmlspecialchars(__('index.download_entire_json'), ENT_QUOTES, 'UTF-8') ?></a>
                    <button type="button" id="copy-clipboard-btn" class="btn btn-outline-secondary btn-sm px-3"><?= htmlspecialchars(__('index.copy_entire_table'), ENT_QUOTES, 'UTF-8') ?></button>
                    <a href="#" id="print-records-btn" class="btn btn-outline-secondary btn-sm px-3" role="button"><?= htmlspecialchars(__('cols.print_btn') !== 'cols.print_btn' ? __('cols.print_btn') : 'Print', ENT_QUOTES, 'UTF-8') ?></a>
                    <?php endif; ?>
                </div>
            </form>
        </section>

        <?php require dirname(__DIR__, 3) . '/partials/column_visibility_bar.php'; ?>

        <!-- LIVE DATA TABLE -->
        <div class="card border-0 shadow-sm mb-4 bg-white overflow-hidden">
            <div class="table-responsive mb-0" aria-live="polite">
                <table id="data-table" class="table table-hover align-middle mb-0" role="table">
                    <thead class="table-light">
                        <tr>
                            <?php foreach (($visibleColumns ?? $columns) as $col): ?>
                                <?php 
                                    $cId = isset($col['id']) ? (int)$col['id'] : 0;
                                    $cName = isset($col['column_name']) && is_string($col['column_name']) ? $col['column_name'] : '';
                                ?>
                                <th class="sortable py-3 px-3" data-sort="col_<?= $cId ?>" scope="col" style="cursor: pointer; max-width: 14rem;">
                                    <?= htmlspecialchars($cName, ENT_QUOTES, 'UTF-8') ?> ↕
                                </th>
                            <?php endforeach; ?>
                            <?php if (!isset($activeTableId) || !function_exists('show_created_by_column') || show_created_by_column((int)$activeTableId)): ?>
                            <th class="py-3 px-3" scope="col"><?= htmlspecialchars(__('index.th_created_by'), ENT_QUOTES, 'UTF-8') ?></th>
                            <?php endif; ?>
                            <?php if (!isset($activeTableId) || !function_exists('show_created_at_column') || show_created_at_column((int)$activeTableId)): ?>
                            <th class="sortable py-3 px-3" data-sort="date" scope="col" style="cursor: pointer;"><?= htmlspecialchars(__('index.th_date_added'), ENT_QUOTES, 'UTF-8') ?> ↕</th>
                            <?php endif; ?>
                            <th class="py-3 text-end pe-3" scope="col"><?= htmlspecialchars(__('index.th_actions'), ENT_QUOTES, 'UTF-8') ?></th>
                        </tr>
                    </thead>
                    <tbody id="table-body">
                        <!-- Populated dynamically via AJAX -->
                    </tbody>
                </table>
            </div>
        </div>

        <!-- PAGINATION -->
        <nav aria-label="Table Data Navigation">
            <div id="pagination-container" class="pagination justify-content-center mb-0 gap-1"></div>
        </nav>

        <script>
let currentSort = 'id';
let currentDir = 'DESC';
let currentPage = 1;
const basePath = '<?= $basePath ?>';
const searchForm = document.getElementById('search-form');
function rebuildVisibleHeaders() {
    const row = document.querySelector('#data-table thead tr');
    if (!row) return;
    const extras = [
        { html: <?= json_encode(__('index.th_created_by'), JSON_UNESCAPED_UNICODE) ?>, sort: '', token: 'created_by' },
        { html: <?= json_encode(__('index.th_date_added') . ' ↕', JSON_UNESCAPED_UNICODE) ?>, sort: 'date', token: 'created_at' },
        { html: <?= json_encode(__('index.th_actions'), JSON_UNESCAPED_UNICODE) ?>, sort: '', extraClass: 'text-end pe-3' }
    ];
    row.innerHTML = '';
    document.querySelectorAll('.js-col-vis:checked').forEach(cb => {
        if (cb.value === 'created_by' || cb.value === 'created_at') return;
        const th = document.createElement('th');
        th.className = 'sortable py-3 px-3';
        th.scope = 'col';
        th.style.cursor = 'pointer';
        th.style.maxWidth = '14rem';
        th.dataset.sort = 'col_' + cb.value;
        const lab = document.querySelector('label[for="' + cb.id + '"]');
        th.textContent = ((lab ? lab.textContent : '') + ' ↕').trim();
        row.appendChild(th);
    });
    extras.forEach(item => {
        if (item.token) {
            const box = document.getElementById('colvis_' + item.token);
            if (box && !box.checked) return;
        }
        const th = document.createElement('th');
        th.className = 'py-3 px-3 ' + (item.extraClass || '');
        th.scope = 'col';
        if (item.sort) {
            th.classList.add('sortable');
            th.style.cursor = 'pointer';
            th.dataset.sort = item.sort;
        }
        th.textContent = item.html;
        row.appendChild(th);
    });
    document.querySelectorAll('#data-table th.sortable').forEach(th => {
        th.addEventListener('click', () => {
            const sortKey = th.getAttribute('data-sort');
            if (!sortKey) return;
            if (currentSort === sortKey) currentDir = currentDir === 'ASC' ? 'DESC' : 'ASC';
            else { currentSort = sortKey; currentDir = 'ASC'; }
            fetchFilteredData(1);
        });
    });
}
document.querySelectorAll('.js-col-vis').forEach(cb => {
    cb.addEventListener('change', () => {
        rebuildVisibleHeaders();
        fetchFilteredData(1);
    });
});


// -------------------------------------------------
// Debounce helper
// -------------------------------------------------
function debounce(fn, delay = 400) {
    let timer = null;
    return function (...args) {
        clearTimeout(timer);
        timer = setTimeout(() => fn.apply(this, args), delay);
    };
}

function fetchFilteredData(page = 1) {
    currentPage = page;
    const formData = new URLSearchParams();
    formData.append('sort', currentSort);
    formData.append('dir', currentDir);
    formData.append('page', currentPage);

    if (searchForm) {
        const tableIdInput = searchForm.querySelector('input[name="table_id"]');
        if (tableIdInput) formData.append('table_id', tableIdInput.value);
    document.querySelectorAll('.js-col-vis:checked').forEach(cb => formData.append('cols[]', cb.value));

        searchForm.querySelectorAll('input[type="text"], select').forEach(input => {
            if (input.value.trim() !== '') {
                formData.append(input.name, input.value.trim());
            }
        });
    }

    const exportCsvBtn = document.getElementById('export-csv-btn');
    const exportJsonBtn = document.getElementById('export-json-btn');
    if (exportCsvBtn) exportCsvBtn.href = basePath + '/api/export?' + formData.toString();
    if (exportJsonBtn) exportJsonBtn.href = basePath + '/api/export-json?' + formData.toString();
    const printBtn = document.getElementById('print-records-btn');
    if (printBtn) printBtn.href = basePath + '/print/records?' + formData.toString();

    fetch(basePath + '/api/search?' + formData.toString())
        .then(r => {
            if (!r.ok) throw new Error('Search request failed');
            return r.json();
        })
        .then(data => {
            const tableBody = document.getElementById('table-body');
            if (tableBody) tableBody.innerHTML = data.html || '';
            renderPagination(data.total_pages || 0, data.current_page || 1);
        })
        .catch(() => {
            const tableBody = document.getElementById('table-body');
            if (tableBody) {
                tableBody.innerHTML = '<tr><td colspan="99" class="text-center py-4 text-muted"><?= htmlspecialchars(__('search.load_error'), ENT_QUOTES, 'UTF-8') ?></td></tr>';
            }
        });
}

// Debounced version used while typing
const debouncedFetch = debounce(() => fetchFilteredData(1), 400);

function renderPagination(totalPages, activePage) {
    const container = document.getElementById('pagination-container');
    if (!container) return;
    container.innerHTML = '';
    if (totalPages <= 1) return;

    for (let i = 1; i <= totalPages; i++) {
        const li = document.createElement('li');
        li.className = 'page-item ' + (i === activePage ? 'active' : '');

        const btn = document.createElement('button');
        btn.type = 'button';
        btn.textContent = i;
        btn.className = 'page-link';
        btn.addEventListener('click', () => fetchFilteredData(i));

        li.appendChild(btn);
        container.appendChild(li);
    }
}

document.querySelectorAll('th.sortable').forEach(th => {
    th.addEventListener('click', () => {
        const sortKey = th.getAttribute('data-sort');
        if (currentSort === sortKey) {
            currentDir = currentDir === 'ASC' ? 'DESC' : 'ASC';
        } else {
            currentSort = sortKey;
            currentDir = 'ASC';
        }
        document.querySelectorAll('th.sortable').forEach(h => {
            h.textContent = h.textContent.replace(/ [▲▼↕]/g, '') + ' ↕';
        });
        th.textContent = th.textContent.replace(/ [▲▼↕]/g, '') + (currentDir === 'ASC' ? ' ▲' : ' ▼');
        fetchFilteredData(1);
    });
});

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
    if (csvBtn) csvBtn.textContent = hasActiveFilter ? '<?= __('index.download_filtered_csv') ?>' : '<?= __('index.download_entire_csv') ?>';
    if (jsonBtn) jsonBtn.textContent = hasActiveFilter ? '<?= __('index.download_filtered_json') ?>' : '<?= __('index.download_entire_json') ?>';
    if (copyBtn) copyBtn.textContent = hasActiveFilter ? '<?= __('index.copy_filtered_table') ?>' : '<?= __('index.copy_entire_table') ?>';
    const printBtn = document.getElementById('print-records-btn');
    if (printBtn) printBtn.textContent = hasActiveFilter
        ? <?= json_encode(__('cols.print_filtered') !== 'cols.print_filtered' ? __('cols.print_filtered') : 'Print filtered') ?>
        : <?= json_encode(__('cols.print_entire') !== 'cols.print_entire' ? __('cols.print_entire') : 'Print all') ?>;
}

if (searchForm) {
    searchForm.querySelectorAll('input, select').forEach(el => {
        // Live typing → debounced
        el.addEventListener('input', () => {
            updateActionButtonsState();
            debouncedFetch();
        });
        // Selects / blur → immediate
        el.addEventListener('change', () => {
            updateActionButtonsState();
            fetchFilteredData(1);
        });
    });
}

const clearBtn = document.getElementById('clear-search');
if (clearBtn) {
    clearBtn.addEventListener('click', () => {
        if (searchForm) {
            searchForm.querySelectorAll('input[type="text"]').forEach(i => i.value = '');
            searchForm.querySelectorAll('select').forEach(s => s.selectedIndex = 0);
        }
        currentSort = 'id';
        currentDir = 'DESC';
        document.querySelectorAll('th.sortable').forEach(h => {
            h.textContent = h.textContent.replace(/ [▲▼↕]/g, '') + ' ↕';
        });
        updateActionButtonsState();
        fetchFilteredData(1);
    });
}

document.getElementById('copy-clipboard-btn')?.addEventListener('click', () => {
    const table = document.getElementById('data-table');
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
        alert('<?= __('index.clipboard_success') ?>');
    }).catch(err => {
        console.error('Failed to copy text: ', err);
    });
});

// Dynamic delegate for Suggest Edit buttons
const tableBodyEl = document.getElementById('table-body');
if (tableBodyEl) {
    tableBodyEl.addEventListener('click', (e) => {
        const btn = e.target.closest('.suggest-edit-btn');
        if (btn) {
            const recordId = btn.getAttribute('data-record-id');
            const currentUrl = window.location.href;
            window.location.href = basePath + '/user/suggest-edit?record_id=' + recordId + '&return=' + encodeURIComponent(currentUrl);
        }
    });
}

updateActionButtonsState();
fetchFilteredData(1);
</script>


    <?php endif; ?>
</div>

<?php require_once ROOT_PATH . '/partials/date_input_script.php'; ?>
<?php require_once ROOT_PATH . '/partials/footer.php'; ?>
