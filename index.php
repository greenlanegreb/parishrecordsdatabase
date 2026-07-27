<?php
// index.php - Public View-Only Web Table Interface
require_once 'db/db.php';
require_once 'db/auth_helpers.php';
require_once 'includes/functions.php';
session_start();

// Enforce dynamic view permission (automatically registers 'view_public' if new)
require_permission($pdo, 'view_public', 'Allows viewing public table records and search directories');

// Check if a user is logged in and pull their profile preferences; otherwise fallback to DD-MM-YYYY defaults
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

// Dynamically generate placeholder for date search inputs based on active preference
$date_placeholder = 'DD-MM-YYYY';
if ($user_date_format === 'm/d/Y' || $user_date_format === 'm-d-Y') {
    $date_placeholder = 'MM-DD-YYYY';
} elseif ($user_date_format === 'Y/m/d' || $user_date_format === 'Y-m-d') {
    $date_placeholder = 'YYYY-MM-DD';
}

$cols_stmt = $pdo->query("SELECT * FROM table_columns ORDER BY sort_order ASC, column_name ASC");
$columns = $cols_stmt->fetchAll();

// Dynamic system name fallback
$system_name = (function_exists('get_system_name') && isset($pdo)) ? get_system_name($pdo) : "Parish Records Directory (PRD)";

$message = $_SESSION['message'] ?? '';
$error = $_SESSION['error'] ?? '';
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

    <!-- SEARCH BUILDER & EXPORT CONTROLS -->
    <section class="search-box-container" aria-label="Advanced Search Section">
        <h3>Multi-Column Search Filters</h3>
        <form id="search-form">
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
                                if ($display_format === 'male_female') {
                                    $opt1_text = 'Male';
                                    $opt2_text = 'Female';
                                } elseif ($display_format === 'true_false') {
                                    $opt1_text = 'True';
                                    $opt2_text = 'False';
                                } elseif ($display_format === 'tick_cross') {
                                    $opt1_text = '✔ (Tick)';
                                    $opt2_text = '✘ (Cross)';
                                }
                            ?>
                            <select id="filter_<?php echo $col['id']; ?>" name="filters[<?php echo $col['id']; ?>]" style="width: 100%; padding: 0.4rem; box-sizing: border-box;" aria-label="Search filter for <?php echo htmlspecialchars($col['column_name']); ?>">
                                <option value="">-- All --</option>
                                <option value="1"><?php echo $opt1_text; ?></option>
                                <option value="0"><?php echo $opt2_text; ?></option>
                            </select>
                        <?php elseif (($col['data_type'] ?? '') === 'DATE'): ?>
                            <div style="display: flex; gap: 0.25rem; align-items: center; max-width: 100%;">
                                <input type="text" name="date_filters[<?php echo $col['id']; ?>][from]" placeholder="<?php echo $date_placeholder; ?>" title="From Date (<?php echo $date_placeholder; ?>)" style="width: 100%; min-width: 0; padding: 0.3rem;" aria-label="From date filter for <?php echo htmlspecialchars($col['column_name']); ?>">
                                <span style="font-size: 0.85rem; color: #666;">to</span>
                                <input type="text" name="date_filters[<?php echo $col['id']; ?>][to]" placeholder="<?php echo $date_placeholder; ?>" title="To Date (<?php echo $date_placeholder; ?>)" style="width: 100%; min-width: 0; padding: 0.3rem;" aria-label="To date filter for <?php echo htmlspecialchars($col['column_name']); ?>">
                            </div>
                        <?php else: ?>
                            <input type="text" id="filter_<?php echo $col['id']; ?>" name="filters[<?php echo $col['id']; ?>]" placeholder="Search..." style="width: 100%; padding: 0.4rem; box-sizing: border-box;" aria-label="Search filter for <?php echo htmlspecialchars($col['column_name']); ?>">
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

    <!-- LIVE DATA TABLE WITH SORTABLE HEADERS -->
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
                <!-- Populated dynamically via AJAX search -->
            </tbody>
        </table>
    </div>

    <!-- PAGINATION CONTROLS -->
    <div id="pagination-container" style="margin-top: 1.5rem; display: flex; gap: 5px; align-items: center; flex-wrap: wrap;"></div>

    <!-- PUBLIC SUGGESTION MODAL -->
    <div id="suggestModal" style="display:none; position:fixed; top:0; left:0; width:100%; height:100%; background:rgba(0,0,0,0.5); z-index:1000; align-items:center; justify-content:center;">
        <div style="background:white; padding:2rem; border-radius:6px; width:100%; max-width:450px; box-shadow: 0 4px 12px rgba(0,0,0,0.15);">
            <h3>Suggest Record Correction</h3>
            <p style="font-size: 0.9rem; color: #666; margin-bottom: 1rem;">Submit a correction or counter-information for this record. It will be reviewed by our moderation team.</p>
            <form method="POST" action="user/actions/save_public_suggestion.php">
                <?php echo csrf_field(); ?>
                <input type="hidden" name="record_id" id="modal_record_id">
                
                <!-- Honeypot Anti-Spam Field (Hidden from real users, targeted by automated bots) -->
                <div style="display:none;" aria-hidden="true">
                    <label for="website_hp">Leave this field blank</label>
                    <input type="text" id="website_hp" name="website_hp" tabindex="-1" autocomplete="off">
                </div>

                <div style="margin-bottom: 1rem;">
                    <label for="modal_column_name"><strong>Target Column:</strong></label><br>
                    <select name="column_name" id="modal_column_name" style="width:100%; padding:0.4rem;" required>
                        <?php foreach ($columns as $col): ?>
                            <option value="<?php echo htmlspecialchars($col['column_name']); ?>"><?php echo htmlspecialchars($col['column_name']); ?></option>
                        <?php endforeach; ?>
                    </select>
                </div>

                <div style="margin-bottom: 1rem;">
                    <label for="modal_proposed_value"><strong>Proposed Correction / Value:</strong></label><br>
                    <input type="text" name="proposed_value" id="modal_proposed_value" placeholder="Enter updated information..." style="width:100%; padding:0.4rem; box-sizing: border-box;" required>
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

        const searchForm = document.getElementById('search-form');

        function fetchFilteredData(page = 1) {
            currentPage = page;
            const formData = new URLSearchParams();
            formData.append('sort', currentSort);
            formData.append('dir', currentDir);
            formData.append('page', currentPage);

            const inputs = searchForm.querySelectorAll('input[type="text"], select');
            inputs.forEach(input => {
                if (input.value.trim() !== '') {
                    formData.append(input.name, input.value.trim());
                }
            });

            const exportBtn = document.getElementById('export-csv-btn');
            exportBtn.href = 'api/export.php?' + formData.toString();

            fetch('api/search.php?' + formData.toString())
                .then(response => response.text())
                .then(fullResponse => {
                    const parts = fullResponse.split('|||TOTAL_PAGES:');
                    document.getElementById('table-body').innerHTML = parts[0];

                    if (parts[1]) {
                        const metaParts = parts[1].split('|||CURRENT_PAGE:');
                        const totalPages = parseInt(metaParts[0]);
                        renderPagination(totalPages, currentPage);
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

                document.querySelectorAll('th.sortable').forEach(header => {
                    header.textContent = header.textContent.replace(/ [▲▼↕]/g, '') + ' ↕';
                });
                th.textContent = th.textContent.replace(/ [▲▼↕]/g, '') + (currentDir === 'ASC' ? ' ▲' : ' ▼');

                fetchFilteredData(1);
            });
        });

        searchForm.querySelectorAll('input, select').forEach(element => {
            element.addEventListener('input', () => fetchFilteredData(1));
            element.addEventListener('change', () => fetchFilteredData(1));
        });

        document.getElementById('clear-search').addEventListener('click', () => {
            searchForm.querySelectorAll('input[type="text"]').forEach(input => input.value = '');
            searchForm.querySelectorAll('select').forEach(select => select.selectedIndex = 0);
            currentSort = 'id';
            currentDir = 'DESC';
            document.querySelectorAll('th.sortable').forEach(header => {
                header.textContent = header.textContent.replace(/ [▲▼↕]/g, '') + ' ↕';
            });
            fetchFilteredData(1);
        });

        fetchFilteredData(1);
    </script>
    <?php require_once 'partials/footer.php'; ?>
