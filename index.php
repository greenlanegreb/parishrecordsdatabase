<?php
// index.php - Public View-Only Multi-Table Directory Interface
require_once 'db/db.php';
require_once 'db/auth_helpers.php';
require_once 'includes/functions.php';
session_start();

// Enforce dynamic base view permission
require_permission($pdo, 'view_public', 'Allows viewing public table records and search directories');

// Fetch all dynamic tables
$tables_stmt = $pdo->query("SELECT id, table_name FROM dynamic_tables ORDER BY id ASC");
profiler_tables:
$all_tables = $tables_stmt->fetchAll(PDO::FETCH_ASSOC);

// Filter tables based on user permissions (Guests check view_table_X or default ID 1)
$available_tables = [];
foreach ($all_tables as $t) {
    $perm_key = 'view_table_' . $t['id'];
    // If it's table 1 (Parish Records), fallback to general view_public if custom perm isn't mapped yet
    if ($t['id'] === 1 || has_permission($pdo, $perm_key)) {
        $available_tables[] = $t;
    }
}

// Determine active table ID from query string or default to first available
$active_table_id = isset($_GET['table_id']) ? intval($_GET['table_id']) : (!empty($available_tables) ? $available_tables[0]['id'] : 1);

// Verify user has permission for the requested table
$active_perm = 'view_table_' . $active_table_id;
if ($active_table_id !== 1 && !has_permission($pdo, $active_perm)) {
    require_once __DIR__ . '/403.php';
    exit;
}

// Pull user preferences for date/timezone formatting
$user_date_format = 'd-m-Y';
$user_timezone = 'UTC';
$user_time_format = '24';
if (isset($_SESSION['user_id'])) {
    $current_user = get_current_user_data($pdo);
    if ($current_user) {
        $user_date_format = $current_user['date_format'] ?? 'd-m-Y';
        $user_timezone = $current_user['timezone'] ?? 'UTC';
        $user_time_format = $current_user['time_format'] ?? '24';
    }
}

$date_placeholder = 'DD-MM-YYYY';
if ($user_date_format === 'm/d/Y' || $user_date_format === 'm-d-Y') {
    $date_placeholder = 'MM-DD-YYYY';
} elseif ($user_date_format === 'Y/m/d' || $user_date_format === 'Y-m-d') {
    $date_placeholder = 'YYYY-MM-DD';
}

// Fetch columns specifically for the active table
$cols_stmt = $pdo->prepare("SELECT * FROM table_columns WHERE table_id = ? ORDER BY sort_order ASC, column_name ASC");
$cols_stmt->execute([$active_table_id]);
$columns = $cols_stmt->fetchAll();

$system_name = (function_exists('get_system_name') && isset($pdo)) ? get_system_name($pdo) : "Parish Records Directory (PRD)";
$message = $_SESSION['message'] ?? '';
$error = $_SESSION['error'] ?? '';
unset($_SESSION['message'], $_SESSION['error']);
?>
    <?php require_once 'partials/header.php'; ?>
    
    <?php include 'partials/notices_banner.php'; ?>
    <?php if (!empty($message)): ?>
        <p class="alert-success" role="status"><strong><?php echo htmlspecialchars($message); ?></strong></p>
    <?php endif; ?>
    <?php if (!empty($error)): ?>
        <p class="alert-danger" role="alert"><strong><?php echo htmlspecialchars($error); ?></strong></p>
    <?php endif; ?>

    <!-- TABLE SELECTOR BAR (Only shown if multiple tables are available) -->
    <?php if (count($available_tables) > 1): ?>
        <div style="background: rgba(0,0,0,0.02); padding: 1rem; border-radius: 6px; margin-bottom: 1.5rem; display: flex; align-items: center; gap: 1rem; flex-wrap: wrap;">
            <label for="public_table_selector" style="font-weight: bold;">Select Directory Database:</label>
            <select id="public_table_selector" class="profile-input" style="padding: 0.4rem; min-width: 250px;" onchange="location.href='index.php?table_id=' + this.value;">
                <?php foreach ($available_tables as $at): ?>
                    <option value="<?php echo $at['id']; ?>" <?php echo ($at['id'] === $active_table_id) ? 'selected' : ''; ?>>
                        <?php echo htmlspecialchars($at['table_name']); ?>
                    </option>
                <?php endforeach; ?>
            </select>
        </div>
    <?php endif; ?>

    <!-- SEARCH BUILDER & EXPORT CONTROLS -->
    <section class="search-box-container" aria-label="Advanced Search Section">
        <h3>Multi-Column Search Filters</h3>
        <form id="search-form">
            <input type="hidden" name="table_id" value="<?php echo $active_table_id; ?>">
            <div style="display: grid; grid-template-columns: repeat(auto-fit, minmax(200px, 1fr)); gap: 1rem; margin-bottom: 1rem;">
                <?php foreach ($columns as $col): ?>
                    <?php if (!empty($col['exclude_from_public_search'])) continue; ?>
                    <div>
                        <label for="filter_<?php echo $col['id']; ?>"><strong><?php echo htmlspecialchars($col['column_name']); ?>:</strong></label><br>
                        
                        <?php if (($col['data_type'] ?? '') === 'BOOLEAN'): ?>
                            <?php 
                                $display_format = $col['boolean_display_format'] ?? 'yes_no';
                                $opt1_text = 'Yes / True';
                                $opt2_text = 'No / False';
                                if ($display_format === 'male_female') { $opt1_text = 'Male'; $opt2_text = 'Female'; }
                                elseif ($display_format === 'true_false') { $opt1_text = 'True'; $opt2_text = 'False'; }
                                elseif ($display_format === 'tick_cross') { $opt1_text = '✔ (Tick)'; $opt2_text = '✘ (Cross)'; }
                            ?>
                            <select id="filter_<?php echo $col['id']; ?>" name="filters[<?php echo $col['id']; ?>]" style="width: 100%; padding: 0.4rem;" aria-label="Search filter for <?php echo htmlspecialchars($col['column_name']); ?>">
                                <option value="">-- All --</option>
                                <option value="1"><?php echo $opt1_text; ?></option>
                                <option value="0"><?php echo $opt2_text; ?></option>
                            </select>
                        <?php elseif (($col['data_type'] ?? '') === 'DATE'): ?>
                            <div style="display: flex; gap: 0.25rem; align-items: center; max-width: 100%;">
                                <input type="text" name="date_filters[<?php echo $col['id']; ?>][from]" placeholder="<?php echo $date_placeholder; ?>" style="width: 100%; padding: 0.3rem;" aria-label="From date filter">
                                <span style="font-size: 0.85rem; color: #666;">to</span>
                                <input type="text" name="date_filters[<?php echo $col['id']; ?>][to]" placeholder="<?php echo $date_placeholder; ?>" style="width: 100%; padding: 0.3rem;" aria-label="To date filter">
                            </div>
                        <?php else: ?>
                            <input type="text" id="filter_<?php echo $col['id']; ?>" name="filters[<?php echo $col['id']; ?>]" placeholder="Search..." style="width: 100%; padding: 0.4rem;" aria-label="Search filter">
                        <?php endif; ?>
                    </div>
                <?php endforeach; ?>
            </div>
            <div style="display: flex; gap: 10px; flex-wrap: wrap;">
                <button type="button" id="clear-search" class="btn" style="background-color: #6c757d;">Reset Search</button>
                <a href="#" id="export-csv-btn" class="btn btn-secondary" style="text-decoration: none;">Download Filtered Results as CSV</a>
            </div>
        </form>
    </section>

    <!-- LIVE DATA TABLE -->
    <div aria-live="polite">
        <table id="data-table" role="table" style="width: 100%; border-collapse: collapse;">
            <thead>
                <tr>
                    <th class="sortable" data-sort="id" scope="col">Record ID ▼</th>
                    <?php foreach ($columns as $col): ?>
                        <th class="sortable" data-sort="col_<?php echo $col['id']; ?>" scope="col"><?php echo htmlspecialchars($col['column_name']); ?> ↕</th>
                    <?php endforeach; ?>
                    <th scope="col">Created By</th>
                    <th class="sortable" data-sort="date" scope="col">Date Added ↕</th>
                    <th scope="col">Actions</th>
                </tr>
            </thead>
            <tbody id="table-body">
                <!-- Populated dynamically via AJAX -->
            </tbody>
        </table>
    </div>
    <div id="pagination-container" style="margin-top: 1.5rem; display: flex; gap: 5px; align-items: center; flex-wrap: wrap;"></div>

    <!-- PUBLIC SUGGESTION MODAL -->
    <div id="suggestModal" style="display:none; position:fixed; top:0; left:0; width:100%; height:100%; background:rgba(0,0,0,0.5); z-index:1000; align-items:center; justify-content:center;">
        <div style="background:white; padding:2rem; border-radius:6px; width:100%; max-width:450px;">
            <h3>Suggest Record Correction</h3>
            <form method="POST" action="user/actions/save_public_suggestion.php">
                <?php echo csrf_field(); ?>
                <input type="hidden" name="record_id" id="modal_record_id">
                <div style="margin-bottom: 1rem;">
                    <label for="modal_column_name"><strong>Target Column:</strong></label><br>
                    <select name="column_name" id="modal_column_name" style="width:100%; padding:0.4rem;" required>
                        <?php foreach ($columns as $col): ?>
                            <option value="<?php echo htmlspecialchars($col['column_name']); ?>"><?php echo htmlspecialchars($col['column_name']); ?></option>
                        <?php endforeach; ?>
                    </select>
                </div>
                <div style="margin-bottom: 1rem;">
                    <label for="modal_proposed_value"><strong>Proposed Value:</strong></label><br>
                    <input type="text" name="proposed_value" id="modal_proposed_value" style="width:100%; padding:0.4rem;" required>
                </div>
                <div style="display: flex; gap: 10px;">
                    <button type="submit" class="btn">Submit Suggestion</button>
                    <button type="button" class="btn btn-secondary" onclick="closeSuggestModal()">Cancel</button>
                </div>
            </form>
        </div>
    </div>

    <script>
        let currentSort = 'id';
        let currentDir = 'DESC';
        let currentPage = 1;
        const searchForm = id => document.getElementById('search-form');

        function fetchFilteredData(page = 1) {
            currentPage = page;
            const formElement = document.getElementById('search-form');
            const formData = new URLSearchParams(new FormData(formElement));
            formData.append('sort', currentSort);
            formData.append('dir', currentDir);
            formData.append('page', currentPage);

            const exportBtn = document.getElementById('export-csv-btn');
            exportBtn.href = 'api/export.php?' + formData.toString();

            fetch('api/search.php?' + formData.toString())
                .then(response => response.text())
                .then(fullResponse => {
                    const parts = fullResponse.split('|||TOTAL_PAGES:');
                    document.getElementById('table-body').innerHTML = parts[0];
                    if (parts[1]) {
                        const metaParts = parts[1].split('|||CURRENT_PAGE:');
                        renderPagination(parseInt(metaParts[0]), currentPage);
                    }
                });
        }

        function renderPagination(totalPages, activePage) {
            const container = document.getElementById('pagination-container');
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
            document.getElementById('modal_record_id').value = recordId;
            document.getElementById('suggestModal').style.display = 'flex';
        }
        function closeSuggestModal() {
            document.getElementById('suggestModal').style.display = 'none';
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
                fetchFilteredData(1);
            });
        });

        const formEl = document.getElementById('search-form');
        formEl.querySelectorAll('input, select').forEach(element => {
            element.addEventListener('input', () => fetchFilteredData(1));
            element.addEventListener('change', () => fetchFilteredData(1));
        });

        document.getElementById('clear-search').addEventListener('click', () => {
            formEl.querySelectorAll('input[type="text"]').forEach(input => input.value = '');
            formEl.querySelectorAll('select').forEach(select => select.selectedIndex = 0);
            fetchFilteredData(1);
        });

        fetchFilteredData(1);
    </script>
    <?php require_once 'partials/footer.php'; ?>
