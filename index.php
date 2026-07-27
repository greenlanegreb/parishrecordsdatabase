<?php
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
$permission_key  = 'view_public';
$permission_desc = 'Allows viewing public table records and search directories';

$p_check = $pdo->prepare("SELECT id FROM permissions WHERE permission_key = ?");
$p_check->execute([$permission_key]);
$perm_id = $p_check->fetchColumn();

if (!$perm_id) {
    $ins_p = $pdo->prepare("INSERT IGNORE INTO permissions (permission_key, description) VALUES (?, ?)");
    $ins_p->execute([$permission_key, $permission_desc]);
    $p_check->execute([$permission_key]);
    $perm_id = $p_check->fetchColumn();
}

$current_user = function_exists('get_current_user_data') ? get_current_user_data($pdo) : null;

// Only the guest role controls public (unauthenticated) access
$has_public_permission = false;
if ($perm_id) {
    $gp_stmt = $pdo->prepare("
        SELECT COUNT(*) 
        FROM role_permissions rp
        JOIN roles r ON rp.role_id = r.id
        WHERE rp.permission_id = ? 
          AND LOWER(r.role_name) = 'guest'
    ");
    $gp_stmt->execute([$perm_id]);
    $has_public_permission = ($gp_stmt->fetchColumn() > 0);
}

if (!$current_user && !$has_public_permission) {
    $base = defined('BASE_PATH') ? rtrim(BASE_PATH, '/') : '';
    header('Location: ' . $base . '/user/login.php');
    exit;
}

// ------------------------------------------------------------------
// Tables the visitor is allowed to see
// ------------------------------------------------------------------
$tables_stmt = $pdo->query("SELECT id, table_name FROM dynamic_tables ORDER BY id ASC");
$all_tables  = $tables_stmt->fetchAll(PDO::FETCH_ASSOC);

$available_tables = [];
foreach ($all_tables as $t) {
    $perm_key = 'view_table_' . $t['id'];
    // Table 1 is always public; other tables require the specific permission
    // (or are shown to guests when they have the global view_public permission)
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
    $user_timezone    = $current_user['timezone']    ?? 'UTC';
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
$cols_stmt = $pdo->prepare(
    "SELECT * FROM table_columns WHERE table_id = ? ORDER BY sort_order ASC, column_name ASC"
);
$cols_stmt->execute([$active_table_id]);
$columns = $cols_stmt->fetchAll();

$system_name = (function_exists('get_system_name') && isset($pdo))
    ? get_system_name($pdo)
    : 'Parish Records Directory (PRD)';

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

<!-- TABLE SELECTOR (only when more than one table is available) -->
<?php if (count($available_tables) > 1): ?>
    <div style="background:rgba(0,0,0,0.02);padding:1rem;border-radius:6px;margin-bottom:1.5rem;display:flex;align-items:center;gap:1rem;flex-wrap:wrap;">
        <label for="public_table_selector" style="font-weight:bold;">Select Directory Database:</label>
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
    <h3>Multi-Column Search Filters</h3>
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
                            $opt1 = 'Yes / True'; $opt2 = 'No / False';
                            if ($fmt === 'male_female') { $opt1 = 'Male'; $opt2 = 'Female'; }
                            elseif ($fmt === 'true_false') { $opt1 = 'True'; $opt2 = 'False'; }
                            elseif ($fmt === 'tick_cross') { $opt1 = '✔ (Tick)'; $opt2 = '✘ (Cross)'; }
                        ?>
                        <select id="filter_<?php echo (int)$col['id']; ?>"
                                name="filters[<?php echo (int)$col['id']; ?>]"
                                style="width:100%;padding:0.4rem;box-sizing:border-box;"
                                aria-label="Search filter for <?php echo htmlspecialchars($col['column_name']); ?>">
                            <option value="">-- All --</option>
                            <option value="1"><?php echo $opt1; ?></option>
                            <option value="0"><?php echo $opt2; ?></option>
                        </select>

                    <?php elseif (($col['data_type'] ?? '') === 'DATE'): ?>
                        <div style="display:flex;gap:0.25rem;align-items:center;max-width:100%;">
                            <input type="text"
                                   name="date_filters[<?php echo (int)$col['id']; ?>][from]"
                                   placeholder="<?php echo htmlspecialchars($date_placeholder); ?>"
                                   title="From Date (<?php echo htmlspecialchars($date_placeholder); ?>)"
                                   style="width:100%;min-width:0;padding:0.3rem;"
                                   aria-label="From date filter for <?php echo htmlspecialchars($col['column_name']); ?>">
                            <span style="font-size:0.85rem;color:#666;">to</span>
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
                               placeholder="Search..."
                               style="width:100%;padding:0.4rem;box-sizing:border-box;"
                               aria-label="Search filter for <?php echo htmlspecialchars($col['column_name']); ?>">
                    <?php endif; ?>
                </div>
            <?php endforeach; ?>
        </div>
        <div style="display:flex;gap:10px;flex-wrap:wrap;">
            <button type="button" id="clear-search" class="btn" style="background-color:#6c757d;">Reset Search</button>
            <a href="#" id="export-csv-btn" class="btn btn-secondary" style="text-decoration:none;">Download Filtered Results as CSV</a>
        </div>
    </form>
</section>

<!-- LIVE DATA TABLE -->
<div aria-live="polite">
    <table id="data-table" role="table" style="width:100%;border-collapse:collapse;">
        <thead>
            <tr>
                <th class="sortable" data-sort="id" scope="col">Record ID ▼</th>
                <?php foreach ($columns as $col): ?>
                    <th class="sortable" data-sort="col_<?php echo (int)$col['id']; ?>" scope="col">
                        <?php echo htmlspecialchars($col['column_name']); ?> ↕
                    </th>
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

<!-- PAGINATION -->
<div id="pagination-container" style="margin-top:1.5rem;display:flex;gap:5px;align-items:center;flex-wrap:wrap;"></div>

<!-- PUBLIC SUGGESTION MODAL -->
<div id="suggestModal" style="display:none;position:fixed;top:0;left:0;width:100%;height:100%;background:rgba(0,0,0,0.5);z-index:1000;align-items:center;justify-content:center;">
    <div style="background:white;padding:2rem;border-radius:6px;width:100%;max-width:450px;box-shadow:0 4px 12px rgba(0,0,0,0.15);">
        <h3>Suggest Record Correction</h3>
        <p style="font-size:0.9rem;color:#666;margin-bottom:1rem;">
            Submit a correction or counter-information for this record. It will be reviewed by our moderation team.
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
                <label for="modal_column_name"><strong>Target Column:</strong></label><br>
                <select name="column_name" id="modal_column_name" style="width:100%;padding:0.4rem;" required>
                    <?php foreach ($columns as $col): ?>
                        <option value="<?php echo htmlspecialchars($col['column_name']); ?>">
                            <?php echo htmlspecialchars($col['column_name']); ?>
                        </option>
                    <?php endforeach; ?>
                </select>
            </div>

            <div style="margin-bottom:1rem;">
                <label for="modal_proposed_value"><strong>Proposed Correction / Value:</strong></label><br>
                <input type="text" name="proposed_value" id="modal_proposed_value"
                       placeholder="Enter updated information..."
                       style="width:100%;padding:0.4rem;box-sizing:border-box;" required>
            </div>

            <div style="display:flex;gap:10px;">
                <button type="submit" class="btn">Submit Suggestion</button>
                <button type="button" class="btn btn-secondary" onclick="closeSuggestModal()">Cancel</button>
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

    const exportBtn = document.getElementById('export-csv-btn');
    if (exportBtn) exportBtn.href = 'api/export.php?' + formData.toString();

    fetch('api/search.php?' + formData.toString())
        .then(r => r.text())
        .then(fullResponse => {
            const parts = fullResponse.split('|||TOTAL_PAGES:');
            const tableBody = document.getElementById('table-body');
            if (tableBody) tableBody.innerHTML = parts[0];

            if (parts[1]) {
                const metaParts = parts[1].split('|||CURRENT_PAGE:');
                const totalPages = parseInt(metaParts[0], 10);
                renderPagination(totalPages, currentPage);
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

if (searchForm) {
    searchForm.querySelectorAll('input, select').forEach(el => {
        el.addEventListener('input',  () => fetchFilteredData(1));
        el.addEventListener('change', () => fetchFilteredData(1));
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
        fetchFilteredData(1);
    });
}

fetchFilteredData(1);
</script>

<?php require_once 'partials/footer.php'; ?>
