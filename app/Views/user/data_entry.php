<?php
/**
 * MIGRATED FILE MAPPING
 * ---------------------
 * Original Old File: user/data_entry.php
 * Migrated Date: 2026-08-04 12:00:00
 */
declare(strict_types=1);
/** Translate with fallback when lang key is missing. @return string */
$__t = static function (string $key, string $fallback = ''): string {
    $v = function_exists('__') ? (string) __($key) : $key;
    if ($v !== $key && $v !== '') {
        return $v;
    }
    return $fallback !== '' ? $fallback : $key;
};


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

require_once ROOT_PATH . '/partials/header.php';
$basePath = defined('BASE_PATH') ? rtrim(BASE_PATH, '/') : '';
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
            <h3 class="h5 fw-bold mb-2"><?= htmlspecialchars($__t('data_entry.no_tables_heading', 'No Tables Configured'), ENT_QUOTES, 'UTF-8') ?></h3>
            <p class="mb-2"><?= htmlspecialchars($__t('data_entry.no_tables_desc', 'There are no tables available for data entry.'), ENT_QUOTES, 'UTF-8') ?></p>
            <?php if ($isAdmin): ?>
                <p class="mb-2"><?= htmlspecialchars($__t('data_entry.admin_tables_prompt', 'If you are an administrator, please go to Admin → Tables to add a table, then add at least one column.'), ENT_QUOTES, 'UTF-8') ?></p>
                <a href="<?= $basePath ?>/admin/tables" class="btn btn-sm btn-primary mt-1 text-decoration-none"><?= htmlspecialchars($__t('data_entry.go_manage_tables', 'Admin → Tables'), ENT_QUOTES, 'UTF-8') ?></a>
            <?php else: ?>
                <p class="mb-0"><?= htmlspecialchars($__t('data_entry.contact_admin_tables', 'Please contact an administrator to set up tables.'), ENT_QUOTES, 'UTF-8') ?></p>
            <?php endif; ?>
        </div>
    <?php elseif ($totalColumnsCount === 0): ?>
        <div class="card shadow-sm border-0 bg-warning bg-opacity-10 text-warning-emphasis p-4 mb-4">
            <h3 class="h5 fw-bold mb-2"><?= htmlspecialchars($__t('data_entry.no_cols_heading', 'No Columns Configured'), ENT_QUOTES, 'UTF-8') ?></h3>
            <p class="mb-2"><?= htmlspecialchars($__t('data_entry.no_cols_desc', 'This table has no active columns defined.'), ENT_QUOTES, 'UTF-8') ?></p>
            <?php if ($isAdmin): ?>
                <p class="mb-2"><?= htmlspecialchars($__t('data_entry.admin_cols_prompt', 'If you are an administrator, please go to Admin → Tables to manage columns and add at least one column to this table.'), ENT_QUOTES, 'UTF-8') ?></p>
                <a href="<?= $basePath ?>/admin/tables" class="btn btn-sm btn-primary mt-1 text-decoration-none"><?= htmlspecialchars($__t('data_entry.go_manage_tables', 'Admin → Tables'), ENT_QUOTES, 'UTF-8') ?></a>
            <?php else: ?>
                <p class="mb-0"><?= htmlspecialchars($__t('data_entry.contact_admin_cols', 'Please contact an administrator to configure columns.'), ENT_QUOTES, 'UTF-8') ?></p>
            <?php endif; ?>
        </div>
    <?php else: ?>

        <?php require __DIR__ . '/data_entry_parts/table_selector.php'; ?>
        <?php require __DIR__ . '/data_entry_parts/duplicate_modal.php'; ?>
        <?php require __DIR__ . '/data_entry_parts/entry_form.php'; ?>
        <?php require __DIR__ . '/data_entry_parts/filter_panel.php'; ?>

        <script>
        document.addEventListener('DOMContentLoaded', () => {
            const basePath = '<?= $basePath ?>';
            const searchForm = document.getElementById('search-form');
            const dataEntryForm = document.getElementById('data-entry-form');
            let currentPage = 1;

            // ---------- Keyboard Shortcuts ----------
            if (dataEntryForm) {
                dataEntryForm.addEventListener('keydown', (e) => {
                    if ((e.ctrlKey || e.metaKey) && e.key === 'Enter') {
                        e.preventDefault();
                        dataEntryForm.submit();
                    }
                    if (e.key === 'Escape' && (e.target.tagName === 'INPUT' || e.target.tagName === 'SELECT')) {
                        e.preventDefault();
                        e.target.value = '';
                    }
                });
            }

            // ---------- Accordion / State management ----------
            const addEntry = document.getElementById('add-entry-details');
            const searchFilter = document.getElementById('search-filter-details');
            const scrollInput = document.getElementById('scroll_pos_input');
            const focusInput = document.getElementById('focus_id_input');
            const searchOpenInput = document.getElementById('search_open_input');
            const applyBtn = document.getElementById('apply-search-btn');

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

            // ---------- Debounce ----------
            function debounce(fn, delay = 400) {
                let timer = null;
                return function (...args) {
                    clearTimeout(timer);
                    timer = setTimeout(() => fn.apply(this, args), delay);
                };
            }

            // ---------- Fetch ----------
            function fetchFilteredData(page = 1) {
                currentPage = page;
                const formData = new URLSearchParams();
                formData.append('sort', 'id');
                formData.append('dir', 'DESC');
                formData.append('page', currentPage);

                if (searchForm) {
                    const tableIdInput = searchForm.querySelector('input[name="table_id"]');
                    if (tableIdInput) formData.append('table_id', tableIdInput.value);

                    searchForm.querySelectorAll('input[type="text"], select').forEach(input => {
                        if (input.name && input.value.trim() !== '') {
                            formData.append(input.name, input.value.trim());
                        }
                    });
                }

                // Update export and button links
                const csvBtn = document.getElementById('export-csv-btn');
                const jsonBtn = document.getElementById('export-json-btn');
                document.querySelectorAll('.js-col-vis:checked').forEach(cb => formData.append('cols[]', cb.value));
                document.querySelectorAll('#data-entry-table thead th[data-col-id]').forEach(th => {
                    const id = th.getAttribute('data-col-id');
                    if (id && id !== 'actions') formData.append('col_order[]', id);
                });
                if (csvBtn) csvBtn.href = basePath + '/api/export?' + formData.toString();
                if (jsonBtn) jsonBtn.href = basePath + '/api/export-json?' + formData.toString();
                const printBtn = document.getElementById('print-records-btn');
                if (printBtn) printBtn.href = basePath + '/print/records?' + formData.toString();
                let hasActiveFilter = false;
                if (searchForm) {
                    searchForm.querySelectorAll('input[type="text"], select').forEach(input => {
                        if (input.name && input.name !== 'table_id' && input.value.trim() !== '') {
                            hasActiveFilter = true;
                        }
                    });
                }
                if (printBtn) {
                    printBtn.textContent = hasActiveFilter
                        ? <?= json_encode(__('cols.print_filtered') !== 'cols.print_filtered' ? __('cols.print_filtered') : 'Print Filtered') ?>
                        : <?= json_encode(__('cols.print_entire') !== 'cols.print_entire' ? __('cols.print_entire') : 'Print All') ?>;
                }

                fetch(basePath + '/api/search?' + formData.toString())
                    .then(r => {
                        if (!r.ok) throw new Error('Search failed');
                        return r.json();
                    })
                    .then(data => {
                        const tbody = document.getElementById('data-entry-table-body');
                        if (tbody) tbody.innerHTML = data.html || '';
                        var deTbl = document.getElementById('data-entry-table');
                        if (deTbl) deTbl.dispatchEvent(new Event('prd-rows-updated'));
                        renderPagination(data.total_pages || 1, data.current_page || 1);
                    })
                    .catch(() => {
                        const tbody = document.getElementById('data-entry-table-body');
                        if (tbody) {
                            const errorMsg = '<?= htmlspecialchars($__t('data_entry.error_loading', 'Error loading records'), ENT_QUOTES, 'UTF-8') ?>';
                            tbody.innerHTML = '<tr><td colspan="99" class="text-center py-4 text-muted">' + errorMsg + '</td></tr>';
                        }
                    });
            }

            const debouncedFetch = debounce(() => fetchFilteredData(1), 400);

            function renderPagination(totalPages, activePage) {
                const container = document.getElementById('data-entry-pagination');
                if (!container) return;
                container.innerHTML = '';
                if (totalPages <= 1) return;

                for (let i = 1; i <= totalPages; i++) {
                    const li = document.createElement('li');
                    li.className = 'page-item ' + (i === activePage ? 'active' : '');
                    const btn = document.createElement('button');
                    btn.type = 'button';
                    btn.className = 'page-link';
                    btn.textContent = i;
                    btn.addEventListener('click', () => fetchFilteredData(i));
                    li.appendChild(btn);
                    container.appendChild(li);
                }
            }

            // ---------- Live listeners ----------
            if (searchForm) {
                const allInputs = searchForm.querySelectorAll('input, select');
                allInputs.forEach(el => {
                    el.addEventListener('focus', () => {
                        if (focusInput) focusInput.value = el.id;
                    });

                    const isDate = el.name && el.name.includes('date_filters');
                    if (isDate) {
                        el.addEventListener('input', () => {
                            const val = el.value.trim();
                            if (/\d{4}/.test(val)) debouncedFetch();
                        });
                        el.addEventListener('blur', () => fetchFilteredData(1));
                    } else {
                        el.addEventListener('input', debouncedFetch);
                        el.addEventListener('change', () => fetchFilteredData(1));
                    }
                });

                if (applyBtn) {
                    applyBtn.addEventListener('click', () => {
                        if (scrollInput) scrollInput.value = window.scrollY;
                        if (searchOpenInput && searchFilter) searchOpenInput.value = searchFilter.open ? '1' : '0';
                    });
                }
            }

            // Clear / Reset button (handles both old ID & Grok ID style if present)
            const resetBtn = document.getElementById('reset-filter-btn') || document.getElementById('clear-search');
            if (resetBtn) {
                resetBtn.addEventListener('click', (e) => {
                    e.preventDefault();
                    if (searchForm) {
                        searchForm.querySelectorAll('input[type="text"]').forEach(i => i.value = '');
                        searchForm.querySelectorAll('select').forEach(s => s.selectedIndex = 0);
                    }
                    const currentScroll = window.scrollY;
                    location.href = basePath + '/data-entry?table_id=<?= $activeTableId ?>&scroll_pos=' + currentScroll + '&search_open=1';
                });
            }

            // Clipboard
            document.getElementById('copy-clipboard-btn')?.addEventListener('click', (e) => {
                e.preventDefault();
                const table = document.getElementById('data-entry-table') || document.querySelector('.table');
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
                    alert('<?= htmlspecialchars($__t('data_entry.clipboard_alert', 'Copied to clipboard!'), ENT_QUOTES, 'UTF-8') ?>');
                }).catch(err => {
                    console.error('Failed to copy text: ', err);
                });
            });

            // Dynamic delegate for Suggest Edit buttons
            document.body.addEventListener('click', (e) => {
                const btn = e.target.closest('.suggest-edit-btn');
                if (btn) {
                    const recordId = btn.getAttribute('data-record-id');
                    const currentUrl = window.location.href;
                    window.location.href = basePath + '/user/suggest-edit?record_id=' + recordId + '&return=' + encodeURIComponent(currentUrl);
                }
            });

                        function rebuildVisibleHeaders() {
                const row = document.querySelector('#data-entry-table thead tr');
                if (!row) return;
                row.innerHTML = '';
                document.querySelectorAll('.js-col-vis:checked').forEach(cb => {
                    if (cb.value === 'created_by' || cb.value === 'created_at') return;
                    const th = document.createElement('th');
                    th.scope = 'col';
                    th.className = 'py-3 text-nowrap';
                    th.dataset.colId = cb.value;
                    const lab = document.querySelector('label[for="' + cb.id + '"]');
                    th.textContent = lab ? lab.textContent.trim() : '';
                    row.appendChild(th);
                });
                const act = document.createElement('th');
                act.scope = 'col';
                act.className = 'py-3 text-end pe-3';
                act.dataset.colId = 'actions';
                act.textContent = <?= json_encode(__('index.th_actions'), JSON_UNESCAPED_UNICODE) ?>;
                row.appendChild(act);
            }
            document.querySelectorAll('.js-col-vis').forEach(cb => {
                cb.addEventListener('change', () => {
                    rebuildVisibleHeaders();
                    fetchFilteredData(currentPage);
                });
            });

            // Initial load
            fetchFilteredData(1);
        });
        </script>

        <hr class="my-4">

        <?php require dirname(__DIR__, 3) . '/partials/column_visibility_bar.php'; ?>
        <?php require __DIR__ . '/data_entry_parts/records_table.php'; ?>

    <?php endif; ?>
</div>

<?php require_once ROOT_PATH . '/partials/footer.php'; ?>
