<?php
/**
 * MIGRATED FILE MAPPING
 * ---------------------
 * Original Old File: root/index.php
 * Migrated Date: 2026-08-05 06:40:21
 */
declare(strict_types=1);

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

    <?php if ($totalTablesCount === 0): ?>
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
                            <label for="filter_<?= $cId ?>" class="form-label small fw-bold">
                                <?= htmlspecialchars($cName, ENT_QUOTES, 'UTF-8') ?>:
                            </label>

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
                                        aria-label="Search filter for <?= htmlspecialchars($cName, ENT_QUOTES, 'UTF-8') ?>">
                                    <option value=""><?= htmlspecialchars(__('index.option_all'), ENT_QUOTES, 'UTF-8') ?></option>
                                    <option value="1"><?= htmlspecialchars($opt1, ENT_QUOTES, 'UTF-8') ?></option>
                                    <option value="0"><?= htmlspecialchars($opt2, ENT_QUOTES, 'UTF-8') ?></option>
                                </select>

                            <?php elseif ($dataType === 'DATE'): ?>
                                <div class="input-group input-group-sm">
                                    <input type="text"
                                           name="date_filters[<?= $cId ?>][from]"
                                           placeholder="<?= htmlspecialchars($datePlaceholder, ENT_QUOTES, 'UTF-8') ?>"
                                           title="From Date (<?= htmlspecialchars($datePlaceholder, ENT_QUOTES, 'UTF-8') ?>)"
                                           class="form-control"
                                           aria-label="From date filter for <?= htmlspecialchars($cName, ENT_QUOTES, 'UTF-8') ?>">
                                    <span class="input-group-text bg-light text-muted small px-2"><?= htmlspecialchars(__('index.date_to_label'), ENT_QUOTES, 'UTF-8') ?></span>
                                    <input type="text"
                                           name="date_filters[<?= $cId ?>][to]"
                                           placeholder="<?= htmlspecialchars($datePlaceholder, ENT_QUOTES, 'UTF-8') ?>"
                                           title="To Date (<?= htmlspecialchars($datePlaceholder, ENT_QUOTES, 'UTF-8') ?>)"
                                           class="form-control"
                                           aria-label="To date filter for <?= htmlspecialchars($cName, ENT_QUOTES, 'UTF-8') ?>">
                                </div>

                            <?php else: ?>
                                <input type="text"
                                       id="filter_<?= $cId ?>"
                                       name="filters[<?= $cId ?>]"
                                       placeholder="<?= htmlspecialchars(__('index.search_placeholder'), ENT_QUOTES, 'UTF-8') ?>"
                                       class="form-control form-control-sm"
                                       aria-label="Search filter for <?= htmlspecialchars($cName, ENT_QUOTES, 'UTF-8') ?>">
                            <?php endif; ?>
                        </div>
                    <?php endforeach; ?>
                </div>

                <!-- Action Bar -->
                <div class="d-flex gap-2 flex-wrap align-items-center pt-2 border-top">
                    <button type="button" id="clear-search" class="btn btn-secondary btn-sm px-3"><?= htmlspecialchars(__('search.reset'), ENT_QUOTES, 'UTF-8') ?></button>
                    <a href="#" id="export-csv-btn" class="btn btn-outline-secondary btn-sm px-3 text-decoration-none"><?= htmlspecialchars(__('index.download_entire_csv'), ENT_QUOTES, 'UTF-8') ?></a>
                    <a href="#" id="export-json-btn" class="btn btn-outline-secondary btn-sm px-3 text-decoration-none"><?= htmlspecialchars(__('index.download_entire_json'), ENT_QUOTES, 'UTF-8') ?></a>
                    <button type="button" id="copy-clipboard-btn" class="btn btn-outline-secondary btn-sm px-3"><?= htmlspecialchars(__('index.copy_entire_table'), ENT_QUOTES, 'UTF-8') ?></button>
                </div>
            </form>
        </section>

        <!-- LIVE DATA TABLE -->
        <div class="card border-0 shadow-sm mb-4 bg-white overflow-hidden">
            <div class="table-responsive mb-0" aria-live="polite">
                <table id="data-table" class="table table-hover align-middle mb-0" role="table">
                    <thead class="table-light">
                        <tr>
                            <?php foreach ($columns as $col): ?>
                                <?php 
                                    $cId = isset($col['id']) ? (int)$col['id'] : 0;
                                    $cName = isset($col['column_name']) && is_string($col['column_name']) ? $col['column_name'] : '';
                                ?>
                                <th class="sortable py-3 px-3" data-sort="col_<?= $cId ?>" scope="col" style="cursor: pointer;">
                                    <?= htmlspecialchars($cName, ENT_QUOTES, 'UTF-8') ?> ↕
                                </th>
                            <?php endforeach; ?>
                            <th class="py-3 px-3" scope="col"><?= htmlspecialchars(__('index.th_created_by'), ENT_QUOTES, 'UTF-8') ?></th>
                            <th class="sortable py-3 px-3" data-sort="date" scope="col" style="cursor: pointer;"><?= htmlspecialchars(__('index.th_date_added'), ENT_QUOTES, 'UTF-8') ?> ↕</th>
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

<?php require_once ROOT_PATH . '/partials/footer.php'; ?>
