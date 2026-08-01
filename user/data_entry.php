<?php
// user/data_entry.php - Main view for data entry, multi-table selection, collapsible search, pagination, and data export
require_once '../db/db.php';
require_once '../db/auth_helpers.php';
require_once '../includes/functions.php';
session_start();

require_permission($pdo, 'access_data_entry', 'Allows accessing the core data entry workstation and creating records');
$current_user = get_current_user_data($pdo);

// Check existence of tables and columns
$tables_count_stmt = $pdo->query("SELECT COUNT(*) FROM dynamic_tables");
$total_tables_count = intval($tables_count_stmt->fetchColumn());

$total_columns_count = 0;
if ($total_tables_count > 0) {
    $cols_count_stmt = $pdo->query("SELECT COUNT(*) FROM table_columns");
    $total_columns_count = intval($cols_count_stmt->fetchColumn());
}

$tables_stmt = $pdo->query("SELECT id, table_name FROM dynamic_tables ORDER BY id ASC");
$all_tables = $tables_stmt->fetchAll(PDO::FETCH_ASSOC);

$available_tables = [];
foreach ($all_tables as $t) {
    $perm_key = 'view_table_' . $t['id'];
    if ($t['id'] === 1 || has_permission($pdo, $perm_key)) {
        $available_tables[] = $t;
    }
}

$active_table_id = isset($_GET['table_id']) ? intval($_GET['table_id']) : (!empty($available_tables) ? $available_tables[0]['id'] : 1);

$active_perm = 'view_table_' . $active_table_id;
if ($active_table_id !== 1 && !has_permission($pdo, $active_perm)) {
    require_once __DIR__ . '/../403.php';
    exit;
}

$user_date_format = $current_user['date_format'] ?? 'd/m/Y';
$date_placeholder = __('data_entry.date_placeholder_ymd');
if ($user_date_format === 'd/m/Y') {
    $date_placeholder = __('data_entry.date_placeholder_dmy');
} elseif ($user_date_format === 'm/d/Y') {
    $date_placeholder = __('data_entry.date_placeholder_mdy');
}

$cols_stmt = $pdo->prepare("SELECT * FROM table_columns WHERE table_id = ? ORDER BY sort_order ASC, column_name ASC");
$cols_stmt->execute([$active_table_id]);
$columns = $cols_stmt->fetchAll();

if (isset($_GET['export_csv']) && $_GET['export_csv'] === '1') {
    generate_csv_export($pdo, 'data-entry-records-export');
}

$message = $_SESSION['message'] ?? '';
$error = $_SESSION['error'] ?? '';
$duplicate_warning = $_SESSION['duplicate_warning'] ?? false;
$matches = $_SESSION['duplicate_matches'] ?? [];
$submitted_data = $_SESSION['submitted_filters'] ?? [];
unset($_SESSION['message'], $_SESSION['error']);

$page = max(1, intval($_GET['page'] ?? 1));
$per_page = 10;
$offset = ($page - 1) * $per_page;
$search_filters = $_GET['filters'] ?? [];
$date_filters = $_GET['date_filters'] ?? [];

$has_active_search = false;
foreach ($search_filters as $val) {
    if ($val !== '' && $val !== null) { $has_active_search = true; break; }
}
if (!$has_active_search) {
    foreach ($date_filters as $df) {
        if (!empty($df['from']) || !empty($df['to'])) { $has_active_search = true; break; }
    }
}

$paginated_records = [];
$total_records = 0;
$total_pages = 1;

if ($total_tables_count > 0 && $total_columns_count > 0) {
    $records_stmt = $pdo->prepare("
        SELECT r.id, r.created_at, u.id as user_id, u.username, u.first_name, u.surname, u.attribution_display_mode 
        FROM records r 
        LEFT JOIN users u ON r.created_by = u.id 
        WHERE r.table_id = ? 
        ORDER BY r.id DESC
    ");
    $records_stmt->execute([$active_table_id]);
    $all_records = $records_stmt->fetchAll();

    $values_stmt = $pdo->query("SELECT record_id, column_id, value_content FROM record_values");
    $raw_values = $values_stmt->fetchAll();
    $record_values = [];
    foreach ($raw_values as $val) {
        $record_values[$val['record_id']][$val['column_id']] = $val['value_content'];
    }

    $filtered_records = [];
    foreach ($all_records as $rec) {
        if (record_matches_filters($rec['id'], $record_values, $search_filters, $date_filters)) {
            $filtered_records[] = $rec;
        }
    }
    $total_records = count($filtered_records);
    $total_pages = ceil($total_records / $per_page);
    $paginated_records = array_slice($filtered_records, $offset, $per_page);
}
?>
    <?php require_once '../partials/header.php'; ?>

    <?php if (!empty($message)): ?>
        <p class="alert-success"><strong><?php echo htmlspecialchars($message); ?></strong></p>
    <?php endif; ?>
    <?php if (!empty($error)): ?>
        <p class="alert-danger"><strong><?php echo htmlspecialchars($error); ?></strong></p>
    <?php endif; ?>

    <?php if ($total_tables_count === 0): ?>
        <div class="search-box-container" style="background:#fff3cd;border:1px solid #ffeeba;padding:1.5rem;border-radius:6px;margin-bottom:2rem;color:#856404;">
            <h3><?php echo htmlspecialchars(__('data_entry.no_tables_heading')); ?></h3>
            <p><?php echo htmlspecialchars(__('data_entry.no_tables_desc')); ?></p>
            <?php if (is_admin($pdo)): ?>
                <p><?php echo __('data_entry.admin_tables_prompt'); ?></p>
                <a href="../admin/manage_tables.php" class="btn" style="margin-top:0.5rem;text-decoration:none;"><?php echo htmlspecialchars(__('data_entry.go_manage_tables')); ?></a>
            <?php else: ?>
                <p><?php echo htmlspecialchars(__('data_entry.contact_admin_tables')); ?></p>
            <?php endif; ?>
        </div>
    <?php elseif ($total_columns_count === 0): ?>
        <div class="search-box-container" style="background:#fff3cd;border:1px solid #ffeeba;padding:1.5rem;border-radius:6px;margin-bottom:2rem;color:#856404;">
            <h3><?php echo htmlspecialchars(__('data_entry.no_cols_heading')); ?></h3>
            <p><?php echo htmlspecialchars(__('data_entry.no_cols_desc')); ?></p>
            <?php if (is_admin($pdo)): ?>
                <p><?php echo __('data_entry.admin_cols_prompt'); ?></p>
                <a href="../admin/manage_tables.php" class="btn" style="margin-top:0.5rem;text-decoration:none;"><?php echo htmlspecialchars(__('data_entry.go_manage_tables')); ?></a>
            <?php else: ?>
                <p><?php echo htmlspecialchars(__('data_entry.contact_admin_cols')); ?></p>
            <?php endif; ?>
        </div>
    <?php else: ?>

        <?php if (count($available_tables) > 1): ?>
            <div style="background: rgba(0,0,0,0.02); padding: 1rem; border-radius: 6px; margin-bottom: 1.5rem; display: flex; align-items: center; gap: 1rem; flex-wrap: wrap;">
                <label for="data_entry_table_selector" style="font-weight: bold;"><?php echo htmlspecialchars(__('data_entry.active_table_label')); ?></label>
                <select id="data_entry_table_selector" class="profile-input" style="padding: 0.4rem; min-width: 250px;" onchange="location.href='data_entry.php?table_id=' + this.value;">
                    <?php foreach ($available_tables as $at): ?>
                        <option value="<?php echo $at['id']; ?>" <?php echo ($at['id'] === $active_table_id) ? 'selected' : ''; ?>>
                            <?php echo htmlspecialchars($at['table_name']); ?>
                        </option>
                    <?php endforeach; ?>
                </select>
            </div>
        <?php endif; ?>

        <?php if (!$duplicate_warning): ?>
            <details id="add-entry-details" class="search-box-container" style="margin-bottom: 2rem;" open>
                <summary style="cursor: pointer; font-weight: bold; font-size: 1.1rem; color: #333;">
                    <?php echo htmlspecialchars(__('data_entry.add_entry_summary')); ?>
                </summary>
                <div style="margin-top: 1rem; padding-top: 1rem; border-top: 1px solid var(--border-color);">
                    <form method="POST" action="actions/save_data_entry.php" id="data-entry-form">
                        <?php echo csrf_field(); ?>
                        <input type="hidden" name="action" value="insert_record">
                        <input type="hidden" name="table_id" value="<?php echo $active_table_id; ?>">
                        <div class="dashboard-grid">
                            <?php foreach ($columns as $col): 
                                $saved_val = $submitted_data[$col['id']] ?? '';
                            ?>
                                <div>
                                    <label for="col_<?php echo $col['id']; ?>">
                                        <strong><?php echo htmlspecialchars($col['column_name']); ?>:</strong>
                                        <?php if (!empty($col['is_required'])): ?>
                                            <span style="color: var(--danger-color); font-weight: bold;">*</span>
                                        <?php endif; ?>
                                    </label><br>
                                    
                                    <?php if (($col['data_type'] ?? '') === 'BOOLEAN'): ?>
                                        <?php 
                                            $display_format = $col['boolean_display_format'] ?? 'yes_no';
                                            $opt1_text = __('data_entry.bool_yes_true');
                                            $opt2_text = __('data_entry.bool_no_false');
                                            if ($display_format === 'male_female') { $opt1_text = __('data_entry.bool_male'); $opt2_text = __('data_entry.bool_female'); }
                                            elseif ($display_format === 'true_false') { $opt1_text = __('data_entry.bool_true'); $opt2_text = __('data_entry.bool_false'); }
                                            elseif ($display_format === 'tick_cross') { $opt1_text = __('data_entry.bool_tick'); $opt2_text = __('data_entry.bool_cross'); }
                                        ?>
                                        <select id="col_<?php echo $col['id']; ?>" name="filters[<?php echo $col['id']; ?>]" class="dashboard-input" <?php echo (!empty($col['is_required'])) ? 'required' : ''; ?>>
                                            <option value=""><?php echo htmlspecialchars(__('feedback.select_placeholder')); ?></option>
                                            <option value="1" <?php echo ($saved_val === '1') ? 'selected' : ''; ?>><?php echo $opt1_text; ?></option>
                                            <option value="0" <?php echo ($saved_val === '0') ? 'selected' : ''; ?>><?php echo $opt2_text; ?></option>
                                        </select>
                                    <?php elseif (($col['data_type'] ?? '') === 'DATE'): ?>
                                        <input type="text" id="col_<?php echo $col['id']; ?>" name="filters[<?php echo $col['id']; ?>]" value="<?php echo htmlspecialchars($saved_val); ?>" placeholder="<?php echo $date_placeholder; ?>" class="dashboard-input" title="<?php echo htmlspecialchars(__('data_entry.date_title_hint')); ?>" <?php echo (!empty($col['is_required'])) ? 'required' : ''; ?>>
                                    <?php else: ?>
                                        <input type="text" id="col_<?php echo $col['id']; ?>" name="filters[<?php echo $col['id']; ?>]" value="<?php echo htmlspecialchars($saved_val); ?>" placeholder="<?php echo htmlspecialchars(__('data_entry.enter_value_placeholder')); ?>" class="dashboard-input" <?php echo (!empty($col['is_required'])) ? 'required' : ''; ?>>
                                    <?php endif; ?>
                                </div>
                            <?php endforeach; ?>
                        </div>
                        <div style="margin-top: 1rem; display: flex; align-items: center; gap: 1rem; flex-wrap: wrap;">
                            <button type="submit" class="btn"><?php echo htmlspecialchars(__('data_entry.submit_data_btn')); ?></button>
                            <span style="font-size: 0.85rem; color: #666;"><?php echo __('data_entry.shortcuts_tip'); ?></span>
                        </div>
                    </form>
                </div>
            </details>
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

        <?php if ($duplicate_warning): ?>
            <div class="modal-duplicate">
                <h3><?php echo htmlspecialchars(__('data_entry.dup_heading')); ?></h3>
                <p><?php echo htmlspecialchars(__('data_entry.dup_desc')); ?></p>
                <ul>
                    <?php foreach ($matches as $match): ?>
                        <li><?php echo htmlspecialchars(sprintf(__('data_entry.dup_item_format'), $match['id'], $match['value_content'])); ?></li>
                    <?php endforeach; ?>
                </ul>
                <p><?php echo htmlspecialchars(__('data_entry.dup_prompt')); ?></p>
                <form method="POST" action="actions/save_data_entry.php">
                    <?php echo csrf_field(); ?>
                    <input type="hidden" name="action" value="insert_record">
                    <input type="hidden" name="table_id" value="<?php echo $active_table_id; ?>">
                    <?php foreach ($submitted_data as $cid => $cval): ?>
                        <input type="hidden" name="filters[<?php echo $cid; ?>]" value="<?php echo htmlspecialchars($cval); ?>">
                    <?php endforeach; ?>
                    <input type="hidden" name="confirm_duplicate" value="1">
                    <button type="submit" class="btn btn-danger"><?php echo htmlspecialchars(__('data_entry.dup_confirm_btn')); ?></button>
                    <a href="data_entry.php?table_id=<?php echo $active_table_id; ?>" class="btn btn-secondary" style="margin-left: 10px; text-decoration: none;"><?php echo htmlspecialchars(__('btn.cancel')); ?></a>
                </form>
            </div>
        <?php endif; ?>

        <details id="search-filter-details" class="search-box-container" style="margin-bottom: 2rem;" <?php echo $has_active_search ? 'open' : ''; ?>>
            <summary style="cursor: pointer; font-weight: bold; font-size: 1.1rem; color: #333;">
                <?php echo htmlspecialchars(__('data_entry.search_summary')); ?>
            </summary>
            <div style="margin-top: 1rem; padding-top: 1rem; border-top: 1px solid var(--border-color);">
                <form method="GET" action="data_entry.php" id="search-form">
                    <input type="hidden" name="table_id" value="<?php echo $active_table_id; ?>">
                    <input type="hidden" name="scroll_pos" id="scroll_pos_input" value="0">
                    <input type="hidden" name="focus_id" id="focus_id_input" value="">
                    <input type="hidden" name="search_open" id="search_open_input" value="<?php echo $has_active_search ? '1' : '0'; ?>">

                    <div class="dashboard-grid">
                        <?php foreach ($columns as $col): ?>
                            <div <?php echo (($col['data_type'] ?? '') === 'DATE') ? 'style="grid-column: span 2;"' : ''; ?>>
                                <label for="search_<?php echo $col['id']; ?>"><strong><?php echo htmlspecialchars($col['column_name']); ?>:</strong></label><br>
                                <?php if (($col['data_type'] ?? '') === 'DATE'): ?>
                                    <div style="display: flex; gap: 0.5rem; align-items: center; width: 100%;">
                                        <input type="text" id="search_date_from_<?php echo $col['id']; ?>" name="date_filters[<?php echo $col['id']; ?>][from]" value="<?php echo htmlspecialchars($date_filters[$col['id']]['from'] ?? ''); ?>" class="dashboard-input" placeholder="<?php echo $date_placeholder; ?>" style="flex: 1 1 0; min-width: 0; padding: 0.3rem;" title="<?php echo htmlspecialchars(__('data_entry.date_title_hint')); ?>">
                                        <span style="font-size: 0.85rem; color: #666; white-space: nowrap;"><?php echo htmlspecialchars(__('data_entry.date_to_label')); ?></span>
                                        <input type="text" id="search_date_to_<?php echo $col['id']; ?>" name="date_filters[<?php echo $col['id']; ?>][to]" value="<?php echo htmlspecialchars($date_filters[$col['id']]['to'] ?? ''); ?>" class="dashboard-input" placeholder="<?php echo $date_placeholder; ?>" style="flex: 1 1 0; min-width: 0; padding: 0.3rem;" title="<?php echo htmlspecialchars(__('data_entry.date_title_hint')); ?>">
                                    </div>
                                <?php elseif (($col['data_type'] ?? '') === 'BOOLEAN'): ?>
                                    <?php 
                                        $display_format = $col['boolean_display_format'] ?? 'yes_no';
                                        $opt1_text = __('data_entry.bool_yes_true');
                                        $opt2_text = __('data_entry.bool_no_false');
                                        if ($display_format === 'male_female') { $opt1_text = __('data_entry.bool_male'); $opt2_text = __('data_entry.bool_female'); }
                                        elseif ($display_format === 'true_false') { $opt1_text = __('data_entry.bool_true'); $opt2_text = __('data_entry.bool_false'); }
                                        elseif ($display_format === 'tick_cross') { $opt1_text = __('data_entry.bool_tick'); $opt2_text = __('data_entry.bool_cross'); }
                                        
                                        $search_val = $search_filters[$col['id']] ?? '';
                                    ?>
                                    <select id="search_<?php echo $col['id']; ?>" name="filters[<?php echo $col['id']; ?>]" class="dashboard-input">
                                        <option value=""><?php echo htmlspecialchars(__('data_entry.filter_all_option')); ?></option>
                                        <option value="1" <?php echo ($search_val === '1') ? 'selected' : ''; ?>><?php echo $opt1_text; ?></option>
                                        <option value="0" <?php echo ($search_val === '0') ? 'selected' : ''; ?>><?php echo $opt2_text; ?></option>
                                    </select>
                                <?php else: ?>
                                    <input type="text" id="search_<?php echo $col['id']; ?>" name="filters[<?php echo $col['id']; ?>]" value="<?php echo htmlspecialchars($search_filters[$col['id']] ?? ''); ?>" placeholder="<?php echo htmlspecialchars(__('data_entry.filter_placeholder')); ?>" class="dashboard-input">
                                <?php endif; ?>
                            </div>
                        <?php endforeach; ?>
                    </div>
                    <!-- Unified Sizing & Dynamic Label Action Bar -->
                    <div class="dashboard-actions-flex" style="margin-top: 1rem; display: flex; gap: 0.5rem; flex-wrap: wrap; align-items: center;">
                        <button type="submit" class="btn" id="apply-search-btn" style="box-sizing: border-box; height: 38px; display: inline-flex; align-items: center; justify-content: center; font-size: 0.95rem; line-height: normal; padding: 0.375rem 0.755rem; vertical-align: middle;"><?php echo htmlspecialchars(__('data_entry.apply_filters_btn')); ?></button>
                        <a href="data_entry.php?table_id=<?php echo $active_table_id; ?>" class="btn btn-secondary" id="reset-filter-btn" style="box-sizing: border-box; height: 38px; display: inline-flex; align-items: center; justify-content: center; font-size: 0.95rem; line-height: normal; padding: 0.375rem 0.755rem; text-decoration: none; vertical-align: middle; background-color: #6c757d; color: #fff;"><?php echo htmlspecialchars(__('data_entry.reset_filter_btn')); ?></a>
                        <a href="data_entry.php?table_id=<?php echo $active_table_id; ?>&export_csv=1&<?php echo htmlspecialchars(http_build_query(['filters' => $search_filters, 'date_filters' => $date_filters])); ?>" class="btn btn-secondary" id="export-csv-btn" style="box-sizing: border-box; height: 38px; display: inline-flex; align-items: center; justify-content: center; font-size: 0.95rem; line-height: normal; padding: 0.375rem 0.755rem; text-decoration: none; vertical-align: middle;"><?php echo htmlspecialchars(__('data_entry.csv_entire_btn')); ?></a>
                        <a href="../api/export_json.php?table_id=<?php echo $active_table_id; ?>&<?php echo htmlspecialchars(http_build_query(['filters' => $search_filters, 'date_filters' => $date_filters])); ?>" class="btn btn-secondary" id="export-json-btn" style="box-sizing: border-box; height: 38px; display: inline-flex; align-items: center; justify-content: center; font-size: 0.95rem; line-height: normal; padding: 0.375rem 0.755rem; text-decoration: none; vertical-align: middle;"><?php echo htmlspecialchars(__('data_entry.json_entire_btn')); ?></a>
                        <button type="button" id="copy-clipboard-btn" class="btn btn-secondary" style="box-sizing: border-box; height: 38px; display: inline-flex; align-items: center; justify-content: center; font-size: 0.95rem; line-height: normal; padding: 0.375rem 0.755rem; vertical-align: middle;"><?php echo htmlspecialchars(__('data_entry.copy_entire_btn')); ?></button>
                    </div>
                </form>
            </div>
        </details>

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

            // Function to check if any filters are active and update button labels accordingly
            function updateActionButtonsState() {
                if (!searchForm) return;
                let hasActiveFilter = false;
                searchForm.querySelectorAll('input[type="text"], select').forEach(input => {
                    // Ignore non-filter hidden inputs
                    if (input.name && input.name !== 'table_id' && input.value.trim() !== '') {
                        hasActiveFilter = true;
                    }
                });

                const csvBtn = document.getElementById('export-csv-btn');
                const jsonBtn = document.getElementById('export-json-btn');
                const copyBtn = document.getElementById('copy-clipboard-btn');

                if (csvBtn) csvBtn.textContent = hasActiveFilter ? '<?php echo htmlspecialchars(__('data_entry.csv_filtered_btn')); ?>' : '<?php echo htmlspecialchars(__('data_entry.csv_entire_btn')); ?>';
                if (jsonBtn) jsonBtn.textContent = hasActiveFilter ? '<?php echo htmlspecialchars(__('data_entry.json_filtered_btn')); ?>' : '<?php echo htmlspecialchars(__('data_entry.json_entire_btn')); ?>';
                if (copyBtn) copyBtn.textContent = hasActiveFilter ? '<?php echo htmlspecialchars(__('data_entry.copy_filtered_btn')); ?>' : '<?php echo htmlspecialchars(__('data_entry.copy_entire_btn')); ?>';
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
                    location.href = `data_entry.php?table_id=<?php echo $active_table_id; ?>&scroll_pos=${currentScroll}&search_open=1`;
                });
            }

            document.getElementById('copy-clipboard-btn')?.addEventListener('click', () => {
                const table = document.querySelector('.data-table');
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
                    alert('<?php echo htmlspecialchars(__('data_entry.clipboard_alert')); ?>');
                }).catch(err => {
                    console.error('Failed to copy text: ', err);
                });
            });

            // Initialize button states on page load
            updateActionButtonsState();
        });
        </script>

        <hr style="border: 0.0625rem solid var(--border-color); margin: 1.5rem 0;">

        <h3><?php echo htmlspecialchars(__('data_entry.existing_records_heading')); ?></h3>
        <table class="data-table" role="table">
            <thead>
                <tr>
                    <?php foreach ($columns as $col): ?>
                        <th scope="col"><?php echo htmlspecialchars($col['column_name']); ?></th>
                    <?php endforeach; ?>
                    <th scope="col"><?php echo htmlspecialchars(__('data_entry.th_added_by')); ?></th>
                    <th scope="col"><?php echo htmlspecialchars(__('data_entry.th_date_created')); ?></th>
                    <th scope="col"><?php echo htmlspecialchars(__('index.th_actions')); ?></th>
                </tr>
            </thead>
            <tbody>
                <?php if (empty($paginated_records)): ?>
                    <tr><td colspan="<?php echo count($columns) + 3; ?>"><?php echo htmlspecialchars(__('data_entry.no_records')); ?></td></tr>
                <?php else: ?>
                    <?php foreach ($paginated_records as $rec): ?>
                        <tr>
                            <?php foreach ($columns as $col): ?>
                                <td>
                                    <?php 
                                        $raw_val = $record_values[$rec['id']][$col['id']] ?? '';
                                        if (($col['data_type'] ?? '') === 'BOOLEAN') {
                                            $fmt = $col['boolean_display_format'] ?? 'yes_no';
                                            if ($fmt === 'male_female') {
                                                $is_true = filter_var($raw_val, FILTER_VALIDATE_BOOLEAN);
                                                echo ($raw_val !== '' && $raw_val !== null) ? ($is_true ? __('data_entry.bool_male') : __('data_entry.bool_female')) : __('data_entry.na_value');
                                            } else {
                                                echo htmlspecialchars(format_boolean_value($raw_val, $fmt));
                                            }
                                        } elseif (($col['data_type'] ?? '') === 'DATE') {
                                            echo htmlspecialchars(format_display_date($raw_val, $user_date_format));
                                        } else {
                                            echo htmlspecialchars($raw_val);
                                        }
                                    ?>
                                </td>
                            <?php endforeach; ?>
                            <td><em><?php echo htmlspecialchars(format_user_display_name($pdo, [
                                'id' => $rec['user_id'],
                                'username' => $rec['username'] ?? 'User_Anon',
                                'first_name' => $rec['first_name'] ?? '',
                                'surname' => $rec['surname'] ?? '',
                                'attribution_display_mode' => $rec['attribution_display_mode'] ?? 'initials_random'
                            ], $current_user)); ?></em></td>
                            <td><?php echo $rec['created_at']; ?></td>
                            <td>
                                <a href="../record_history.php?record_id=<?php echo $rec['id']; ?>" class="btn btn-secondary" style="padding: 0.2rem 0.4rem; font-size: 0.8rem; text-decoration: none; margin-right: 4px; display: inline-block;"><?php echo htmlspecialchars(__('api_search.history_btn')); ?></a>
                                <?php if (is_module_enabled($pdo, 'moderation')): ?>
                                    <a href="suggest_edit.php?record_id=<?php echo $rec['id']; ?>" class="btn" style="padding: 0.2rem 0.4rem; font-size: 0.8rem; text-decoration: none; display: inline-block;"><?php echo htmlspecialchars(__('api_search.suggest_edit_btn')); ?></a>
                                <?php endif; ?>
                            </td>
                        </tr>
                    <?php endforeach; ?>
                <?php endif; ?>
            </tbody>
        </table>

        <?php if ($total_pages > 1): ?>
            <div class="pagination-container">
                <span><?php echo htmlspecialchars(__('data_entry.page_label')); ?></span>
                <?php for ($i = 1; $i <= $total_pages; $i++): ?>
                    <?php 
                        $query_params = $_GET;
                        $query_params['page'] = $i;
                        $page_url = 'data_entry.php?' . http_build_query($query_params);
                    ?>
                    <a href="<?php echo $page_url; ?>" class="btn <?php echo ($i === $page) ? 'btn-active' : 'btn-secondary'; ?>" style="padding: 0.3rem 0.6rem; text-decoration: none;"><?php echo $i; ?></a>
                <?php endfor; ?>
            </div>
        <?php endif; ?>

    <?php endif; ?>

    <?php require_once '../partials/footer.php'; ?>
