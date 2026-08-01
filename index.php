<?php
// Only send people to the installer when nothing is configured yet
if (!is_file(__DIR__ . '/db/db.php') && !is_file(__DIR__ . '/config.local.php')) {
    header('Location: install/');
    exit;
}

// index.php - Public View-Only Multi-Table Directory Interface
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}
require_once 'db/db.php';
require_once 'db/auth_helpers.php';
require_once 'includes/functions.php';

// ------------------------------------------------------------------
// Permission gate for public viewing
// ------------------------------------------------------------------
$current_user = function_exists('get_current_user_data') ? get_current_user_data($pdo) : null;

// Only the guest role controls public (unauthenticated) access
$has_public_permission = guest_has_permission($pdo, 'view_public');

if (!$current_user && !$has_public_permission) {
    $base = defined('BASE_PATH') ? rtrim(BASE_PATH, '/') : '';
    header('Location: ' . $base . '/user/login.php');
    exit;
}

// ------------------------------------------------------------------
// Check table and column existence for intelligent user guidance
// ------------------------------------------------------------------
$tables_count_stmt = $pdo->query("SELECT COUNT(*) FROM dynamic_tables");
$total_tables_count = intval($tables_count_stmt->fetchColumn());

$total_columns_count = 0;
if ($total_tables_count > 0) {
    $cols_count_stmt = $pdo->query("SELECT COUNT(*) FROM table_columns");
    $total_columns_count = intval($cols_count_stmt->fetchColumn());
}

// ------------------------------------------------------------------
// Tables the visitor is allowed to see
// ------------------------------------------------------------------
$tables_stmt = $pdo->query("SELECT id, table_name FROM dynamic_tables ORDER BY id ASC");
$all_tables = $tables_stmt->fetchAll(PDO::FETCH_ASSOC);
$available_tables = [];
foreach ($all_tables as $t) {
    $perm_key = 'view_table_' . $t['id'];
    if ($t['id'] === 1 || ($current_user ? has_permission($pdo, $perm_key) : $has_public_permission)) {
        $available_tables[] = $t;
    }
}
$active_table_id = isset($_GET['table_id'])
    ? intval($_GET['table_id'])
    : (!empty($available_tables) ? $available_tables[0]['id'] : 1);

// Extra safety for a requested table the visitor is not allowed to see
$active_perm = 'view_table_' . $active_table_id;
if ($active_table_id !== 1 && $current_user && !has_permission($pdo, $active_perm)) {
    require_once __DIR__ . '/403.php';
    exit;
}

// ------------------------------------------------------------------
// User display preferences (or sensible defaults for guests)
// ------------------------------------------------------------------
$user_date_format = 'd-m-Y';
$user_timezone    = 'UTC';
$user_time_format = '24';
if ($current_user) {
    $user_date_format = $current_user['date_format'] ?? 'd-m-Y';
    $user_timezone    = $current_user['timezone'] ?? 'UTC';
    $user_time_format = $current_user['time_format'] ?? '24';
}
$date_placeholder = 'DD-MM-YYYY';
if ($user_date_format === 'm/d/Y' || $user_date_format === 'm-d-Y') {
    $date_placeholder = 'MM-DD-YYYY';
} elseif ($user_date_format === 'Y/m/d' || $user_date_format === 'Y-m-d') {
    $date_placeholder = 'YYYY-MM-DD';
}

// ------------------------------------------------------------------
// Columns for the active table
// ------------------------------------------------------------------
$columns = [];
if ($total_tables_count > 0 && $total_columns_count > 0) {
    $cols_stmt = $pdo->prepare(
        "SELECT * FROM table_columns WHERE table_id = ? ORDER BY sort_order ASC, column_name ASC"
    );
    $cols_stmt->execute([$active_table_id]);
    $columns = $cols_stmt->fetchAll();
}

$system_name = get_system_name($pdo);

$message = $_SESSION['message'] ?? '';
$error   = $_SESSION['error']   ?? '';
unset($_SESSION['message'], $_SESSION['error']);
?>
<?php require_once 'partials/header.php'; ?>

<!-- DYNAMIC NOTICES MODULE -->
<?php include 'partials/notices_banner.php'; ?>

<?php if (!empty($message)): ?>
    <p class="alert-success" role="status"><strong><?php echo htmlspecialchars($message); ?></strong></p>
<?php endif; ?>
<?php if (!empty($error)): ?>
    <p class="alert-danger" role="alert"><strong><?php echo htmlspecialchars($error); ?></strong></p>
<?php endif; ?>

<?php if ($total_tables_count === 0): ?>
    <div class="search-box-container" style="background:#fff3cd;border:1px solid #ffeeba;padding:1.5rem;border-radius:6px;margin-bottom:2rem;color:#856404;">
        <h3>⚠️ <?php echo htmlspecialchars(__('index.no_tables_heading')); ?></h3>
        <p><?php echo htmlspecialchars(__('index.no_tables_desc')); ?></p>
        <?php if ($current_user && is_admin($pdo)): ?>
            <p><?php echo __('index.admin_create_table_guide', ['link' => 'admin/manage_tables.php']); ?></p>
            <a href="admin/manage_tables.php" class="btn" style="margin-top:0.5rem;text-decoration:none;"><?php echo htmlspecialchars(__('index.go_to_manage_tables')); ?></a>
        <?elseif ($current_user): ?>
            <p><?php echo htmlspecialchars(__('index.contact_admin_tables')); ?></p>
        <?php else: ?>
            <p><?php echo __('index.guest_login_tables_guide', ['login_link' => 'user/login.php']); ?></p>
        <?php endif; ?>
    </div>
<?php elseif ($total_columns_count === 0): ?>
    <div class="search-box-container" style="background:#fff3cd;border:1px solid #ffeeba;padding:1.5rem;border-radius:6px;margin-bottom:2rem;color:#856404;">
        <h3>⚠️ <?php echo htmlspecialchars(__('index.no_columns_heading')); ?></h3>
        <p><?php echo htmlspecialchars(__('index.no_columns_desc')); ?></p>
        <?php if ($current_user && is_admin($pdo)): ?>
            <p><?php echo htmlspecialchars(__('index.admin_add_columns_guide')); ?></p>
            <a href="admin/manage_tables.php" class="btn" style="margin-top:0.5rem;text-decoration:none;"><?php echo htmlspecialchars(__('index.go_to_manage_tables')); ?></a>
        <?php else: ?>
            <p><?php echo htmlspecialchars(__('index.contact_admin_columns')); ?></p>
        <?php endif; ?>
    </div>
<?php else: ?>

    <!-- TABLE SELECTOR (only when more than one table is available) -->
    <?php if (count($available_tables) > 1): ?>
        <div style="background:rgba(0,0,0,0.02);padding:1rem;border-radius:6px;margin-bottom:1.5rem;display:flex;align-items:center;gap:1rem;flex-wrap:wrap;">
            <label for="public_table_selector" style="font-weight:bold;"><?php echo htmlspecialchars(__('index.select_directory_database')); ?></label>
            <select id="public_table_selector" class="profile-input" style="padding:0.4rem;min-width:250px;"
                    onchange="location.href='index.php?table_id='+this.value;">
                <?php foreach ($available_tables as $at): ?>
                    <option value="<?php echo (int)$at['id']; ?>"
                        <?php echo ($at['id'] === $active_table_id) ? 'selected' : ''; ?>>
                        <?php echo htmlspecialchars($at['table_name']); ?>
                    </option>
                <?php endforeach; ?>
            </select>
        </div>
    <?php endif; ?>

    <!-- SEARCH & EXPORT -->
    <section class="search-box-container" aria-label="Advanced Search Section">
        <h3><?php echo htmlspecialchars(__('search.heading')); ?></h3>
        <form id="search-form">
            <input type="hidden" name="table_id" value="<?php echo (int)$active_table_id; ?>">
            <div style="display:grid;grid-template-columns:repeat(auto-fit,minmax(200px,1fr));gap:1rem;margin-bottom:1rem;">
                <?php foreach ($columns as $col): ?>
                    <?php if (!empty($col['exclude_from_public_search'])) continue; ?>
                    <div>
                        <label for="filter_<?php echo (int)$col['id']; ?>">
                            <strong><?php echo htmlspecialchars($col['column_name']); ?>:</strong>
                        </label><br>

                        <?php if (($col['data_type'] ?? '') === 'BOOLEAN'): ?>
                            <?php
                                $fmt = $col['boolean_display_format'] ?? 'yes_no';
                                $opt1 = __('index.opt_yes_true'); $opt2 = __('index.opt_no_false');
                                if ($fmt === 'male_female') { $opt1 = __('index.opt_male'); $opt2 = __('index.opt_female'); }
                                elseif ($fmt === 'true_false') { $opt1 = __('index.opt_true'); $opt2 = __('index.opt_false'); }
                                elseif ($fmt === 'tick_cross') { $opt1 = __('index.opt_tick'); $opt2 = __('index.opt_cross'); }
                            ?>
                            <select id="filter_<?php echo (int)$col['id']; ?>"
                                    name="filters[<?php echo (int)$col['id']; ?>]"
                                    style="width:100%;padding:0.4rem;box-sizing:border-box;"
                                    aria-label="Search filter for <?php echo htmlspecialchars($col['column_name']); ?>">
                                <option value=""><?php echo htmlspecialchars(__('index.option_all')); ?></option>
                                <option value="1"><?php echo htmlspecialchars($opt1); ?></option>
                                <option value="0"><?php echo htmlspecialchars($opt2); ?></option>
                            </select>

                        <?php elseif (($col['data_type'] ?? '') === 'DATE'): ?>
                            <div style="display:flex;gap:0.25rem;align-items:center;max-width:100%;">
                                <input type="text"
                                       name="date_filters[<?php echo (int)$col['id']; ?>][from]"
                                       placeholder="<?php echo htmlspecialchars($date_placeholder); ?>"
                                       title="From Date (<?php echo htmlspecialchars($date_placeholder); ?>)"
                                       style="width:100%;min-width:0;padding:0.3rem;"
                                       aria-label="From date filter for <?php echo htmlspecialchars($col['column_name']); ?>">
                                <span style="font-size:0.85rem;color:#666;"><?php echo htmlspecialchars(__('index.date_to_label')); ?></span>
                                <input type="text"
                                       name="date_filters[<?php echo (int)$col['id']; ?>][to]"
                                       placeholder="<?php echo htmlspecialchars($date_placeholder); ?>"
                                       title="To Date (<?php echo htmlspecialchars($date_placeholder); ?>)"
                                       style="width:100%;min-width:0;padding:0.3rem;"
                                       aria-label="To date filter for <?php echo htmlspecialchars($col['column_name']); ?>">
                            </div>

                        <?php else: ?>
                            <input type="text"
                                   id="filter_<?php echo (int)$col['id']; ?>"
                                   name="filters[<?php echo (int)$col['id']; ?>]"
                                   placeholder="<?php echo htmlspecialchars(__('index.search_placeholder')); ?>"
                                   style="width:100%;padding:0.4rem;box-sizing:border-box;"
                                   aria-label="Search filter for <?php echo htmlspecialchars($col['column_name']); ?>">
                        <?php endif; ?>
                    </div>
                <?php endforeach; ?>
            </div>
            <!-- Unified Sizing & Dynamic Label Action Bar -->
            <div style="display:flex;gap:10px;flex-wrap:wrap;align-items:center;">
                <button type="button" id="clear-search" class="btn" style="box-sizing: border-box; height: 38px; display: inline-flex; align-items: center; justify-content: center; font-size: 0.95rem; line-height: normal; padding: 0.375rem 0.755rem; vertical-align: middle; background-color:#6c757d; color:#fff;"><?php echo htmlspecialchars(__('search.reset')); ?></button>
                <a href="#" id="export-csv-btn" class="btn btn-secondary" style="box-sizing: border-box; height: 38px; display: inline-flex; align-items: center; justify-content: center; font-size: 0.95rem; line-height: normal; padding: 0.375rem 0.755rem; text-decoration:none; vertical-align: middle;"><?php echo htmlspecialchars(__('index.download_entire_csv')); ?></a>
                <a href="#" id="export-json-btn" class="btn btn-secondary" style="box-sizing: border-box; height: 38px; display: inline-flex; align-items: center; justify-content: center; font-size: 0.95rem; line-height: normal; padding: 0.375rem 0.755rem; text-decoration:none; vertical-align: middle;"><?php echo htmlspecialchars(__('index.download_entire_json')); ?></a>
                <button type="button" id="copy-clipboard-btn" class="btn btn-secondary" style="box-sizing: border-box; height: 38px; display: inline-flex; align-items: center; justify-content: center; font-size: 0.95rem; line-height: normal; padding: 0.375rem 0.755rem; vertical-align: middle;"><?php echo htmlspecialchars(__('index.copy_entire_table')); ?></button>
            </div>
        </form>
    </section>

    <!-- LIVE DATA TABLE -->
    <div aria-live="polite">
        <table id="data-table" role="table" style="width:100%;border-collapse:collapse;">
            <thead>
                <tr>
                    <th class="sortable" data-sort="id" scope="col"><?php echo htmlspecialchars(__('index.th_record_id')); ?> ▼</th>
                    <?php foreach ($columns as $col): ?>
                        <th class="sortable" data-sort="col_<?php echo (int)$col['id']; ?>" scope="col">
                            <?php echo htmlspecialchars($col['column_name']); ?> ↕
                        </th>
                    <?php endforeach; ?>
                    <th scope="col"><?php echo htmlspecialchars(__('index.th_created_by')); ?></th>
                    <th class="sortable" data-sort="date" scope="col"><?php echo htmlspecialchars(__('index.th_date_added')); ?> ↕</th>
                    <th scope="col"><?php echo htmlspecialchars(__('index.th_actions')); ?></th>
                </tr>
            </thead>
            <tbody id="table-body">
                <!-- Populated dynamically via AJAX -->
            </tbody>
        </table>
    </div>

    <!-- PAGINATION -->
    <div id="pagination-container" style="margin-top:1.5rem;display:flex;gap:5px;align-items:center;flex-wrap:wrap;"></div>

    <!-- PUBLIC SUGGESTION MODAL -->
    <div id="suggestModal" style="display:none;position:fixed;top:0;left:0;width:100%;height:100%;background:rgba(0,0,0,0.5);z-index:1000;align-items:center;justify-content:center;">
        <div style="background:white;padding:2rem;border-radius:6px;width:100%;max-width:450px;box-shadow:0 4px 12px rgba(0,0,0,0.15);">
            <h3><?php echo htmlspecialchars(__('index.modal_heading')); ?></h3>
            <p style="font-size:0.9rem;color:#666;margin-bottom:1rem;">
                <?php echo htmlspecialchars(__('index.modal_desc')); ?>
            </p>
            <form method="POST" action="user/actions/save_public_suggestion.php">
                <?php echo function_exists('csrf_field') ? csrf_field() : ''; ?>
                <input type="hidden" name="record_id" id="modal_record_id">

                <!-- Honeypot -->
                <div style="display:none;" aria-hidden="true">
                    <label for="website_hp">Leave this field blank</label>
                    <input type="text" id="website_hp" name="website_hp" tabindex="-1" autocomplete="off">
                </div>

                <div style="margin-bottom:1rem;">
                    <label for="modal_column_name"><strong><?php echo htmlspecialchars(__('index.modal_target_column')); ?></strong></label><br>
                    <select name="column_name" id="modal_column_name" style="width:100%;padding:0.4rem;" required>
                        <?php foreach ($columns as $col): ?>
                            <option value="<?php echo htmlspecialchars($col['column_name']); ?>">
                                <?php echo htmlspecialchars($col['column_name']); ?>
                            </option>
                        <?php endforeach; ?>
                    </select>
                </div>

                <div style="margin-bottom:1rem;">
                    <label for="modal_proposed_value"><strong><?php echo htmlspecialchars(__('index.modal_proposed_value')); ?></strong></label><br>
                    <input type="text" name="proposed_value" id="modal_proposed_value"
                           placeholder="<?php echo htmlspecialchars(__('index.modal_input_placeholder')); ?>"
                           style="width:100%;padding:0.4rem;box-sizing:border-box;" required>
                </div>

                <div style="display:flex;gap:10px;">
                    <button type="submit" class="btn"><?php echo htmlspecialchars(__('index.modal_submit_btn')); ?></button>
                    <button type="button" class="btn btn-secondary" onclick="closeSuggestModal()"><?php echo htmlspecialchars(__('btn.cancel')); ?></button>
                </div>
            </form>
        </div>
    </div>

    <script>
    let currentSort = 'id';
    let currentDir  = 'DESC';
    let currentPage = 1;

    const searchForm = document.getElementById('search-form');

    function fetchFilteredData(page = 1) {
        currentPage = page;
        const formData = new URLSearchParams();
        formData.append('sort', currentSort);
        formData.append('dir',  currentDir);
        formData.append('page', currentPage);

        const tableIdInput = searchForm.querySelector('input[name="table_id"]');
        if (tableIdInput) formData.append('table_id', tableIdInput.value);

        searchForm.querySelectorAll('input[type="text"], select').forEach(input => {
            if (input.value.trim() !== '') formData.append(input.name, input.value.trim());
        });

        const exportCsvBtn = document.getElementById('export-csv-btn');
        const exportJsonBtn = document.getElementById('export-json-btn');
        if (exportCsvBtn) exportCsvBtn.href = 'api/export.php?' + formData.toString();
        if (exportJsonBtn) exportJsonBtn.href = 'api/export_json.php?' + formData.toString();

        fetch('api/search.php?' + formData.toString())
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
                    tableBody.innerHTML = '<tr><td colspan="99"><?php echo htmlspecialchars(__('search.load_error')); ?></td></tr>';
                }
            });
    }

    function renderPagination(totalPages, activePage) {
        const container = document.getElementById('pagination-container');
        if (!container) return;
        container.innerHTML = '';
        if (totalPages <= 1) return;

        for (let i = 1; i <= totalPages; i++) {
            const btn = document.createElement('button');
            btn.type = 'button';
            btn.textContent = i;
            btn.className = 'btn ' + (i === activePage ? 'btn-active' : 'btn-secondary');
            btn.style.padding = '0.3rem 0.6rem';
            btn.addEventListener('click', () => fetchFilteredData(i));
            container.appendChild(btn);
        }
    }

    function openSuggestModal(recordId) {
        const el = document.getElementById('modal_record_id');
        const modal = document.getElementById('suggestModal');
        if (el) el.value = recordId;
        if (modal) modal.style.display = 'flex';
    }

    function closeSuggestModal() {
        const modal = document.getElementById('suggestModal');
        if (modal) modal.style.display = 'none';
    }

    document.querySelectorAll('th.sortable').forEach(th => {
        th.addEventListener('click', () => {
            const sortKey = th.getAttribute('data-sort');
            if (currentSort === sortKey) {
                currentDir = currentDir === 'ASC' ? 'DESC' : 'ASC';
            } else {
                currentSort = sortKey;
                currentDir  = 'ASC';
            }
            document.querySelectorAll('th.sortable').forEach(h => {
                h.textContent = h.textContent.replace(/ [▲▼↕]/g, '') + ' ↕';
            });
            th.textContent = th.textContent.replace(/ [▲▼↕]/g, '') + (currentDir === 'ASC' ? ' ▲' : ' ▼');
            fetchFilteredData(1);
        });
    });

    // Dynamic label toggle function for filter state
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

        if (csvBtn) csvBtn.textContent = hasActiveFilter ? '<?php echo __('index.download_filtered_csv'); ?>' : '<?php echo __('index.download_entire_csv'); ?>';
        if (jsonBtn) jsonBtn.textContent = hasActiveFilter ? '<?php echo __('index.download_filtered_json'); ?>' : '<?php echo __('index.download_entire_json'); ?>';
        if (copyBtn) copyBtn.textContent = hasActiveFilter ? '<?php echo __('index.copy_filtered_table'); ?>' : '<?php echo __('index.copy_entire_table'); ?>';
    }

    if (searchForm) {
        searchForm.querySelectorAll('input, select').forEach(el => {
            el.addEventListener('input',  () => {
                updateActionButtonsState();
                fetchFilteredData(1);
            });
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
            currentDir  = 'DESC';
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
            alert('<?php echo __('index.clipboard_success'); ?>');
        }).catch(err => {
            console.error('Failed to copy text: ', err);
        });
    });

    // Initialize button states on load
    updateActionButtonsState();
    fetchFilteredData(1);
    </script>

<?php endif; ?>

<?php require_once 'partials/footer.php'; ?>
