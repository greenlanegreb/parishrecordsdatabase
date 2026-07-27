<?php
// user/data_entry.php - Main view for data entry, collapsible search, pagination, and CSV export
require_once '../db/db.php';
require_once '../db/auth_helpers.php';
require_once '../includes/functions.php';
session_start();

// Enforce standard user/moderator/admin authentication via central helper
require_role($pdo, ['user', 'moderator', 'admin']);
$current_user = get_current_user_data($pdo);

// Pull user date format preference for displaying stored ISO dates cleanly and generating smart placeholders
$user_date_format = $current_user['date_format'] ?? 'd/m/Y';

// Dynamically generate a clear placeholder string based on user format preference
$date_placeholder = 'YYYY-MM-DD';
if ($user_date_format === 'd/m/Y') {
    $date_placeholder = 'DD/MM/YYYY';
} elseif ($user_date_format === 'm/d/Y') {
    $date_placeholder = 'MM/DD/YYYY';
}

// Fetch dynamic table columns ordered by custom sort_order
$cols_stmt = $pdo->query("SELECT * FROM table_columns ORDER BY sort_order ASC, column_name ASC");
$columns = $cols_stmt->fetchAll();

// Handle CSV Export Request directly using the centralized helper
if (isset($_GET['export_csv']) && $_GET['export_csv'] === '1') {
    generate_csv_export($pdo, 'data-entry-records-export');
}

$message = $_SESSION['message'] ?? '';
$error = $_SESSION['error'] ?? '';
$duplicate_warning = $_SESSION['duplicate_warning'] ?? false;
$matches = $_SESSION['duplicate_matches'] ?? [];
$submitted_data = $_SESSION['submitted_filters'] ?? [];
unset($_SESSION['message'], $_SESSION['error']);

// Pagination and Filtering Setup
$page = max(1, intval($_GET['page'] ?? 1));
$per_page = 10;
$offset = ($page - 1) * $per_page;

$search_filters = $_GET['filters'] ?? [];
$date_filters = $_GET['date_filters'] ?? [];

$records_stmt = $pdo->query("SELECT r.id, r.created_at, u.username FROM records r LEFT JOIN users u ON r.created_by = u.id ORDER BY r.id DESC");
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
?>

    <?php require_once '../partials/header.php'; ?>

    <?php if (!empty($message)): ?>
        <p class="alert-success"><strong><?php echo htmlspecialchars($message); ?></strong></p>
    <?php endif; ?>
    <?php if (!empty($error)): ?>
        <p class="alert-danger"><strong><?php echo htmlspecialchars($error); ?></strong></p>
    <?php endif; ?>

    <!-- COLLAPSIBLE SEARCH & FILTER SECTION (NOW AT THE TOP) -->
    <details class="search-box-container" style="margin-bottom: 2rem;">
        <summary style="cursor: pointer; font-weight: bold; font-size: 1.1rem; color: #333;">
            🔍 Search & Filter Existing Records (Click to expand)
        </summary>
        <div style="margin-top: 1rem; padding-top: 1rem; border-top: 1px solid var(--border-color);">
            <form method="GET">
                <div class="dashboard-grid">
                    <?php foreach ($columns as $col): ?>
                        <div>
                            <label for="search_<?php echo $col['id']; ?>"><strong><?php echo htmlspecialchars($col['column_name']); ?>:</strong></label><br>
                            <?php if (($col['data_type'] ?? '') === 'DATE'): ?>
                                <div style="display: flex; gap: 0.25rem; align-items: center; max-width: 100%;">
                                    <input type="text" name="date_filters[<?php echo $col['id']; ?>][from]" value="<?php echo htmlspecialchars($date_filters[$col['id']]['from'] ?? ''); ?>" class="dashboard-input" placeholder="<?php echo $date_placeholder; ?>" title="From Date (<?php echo $date_placeholder; ?>)" style="width: 100%; min-width: 0; padding: 0.3rem;">
                                    <span style="font-size: 0.85rem; color: #666;">to</span>
                                    <input type="text" name="date_filters[<?php echo $col['id']; ?>][to]" value="<?php echo htmlspecialchars($date_filters[$col['id']]['to'] ?? ''); ?>" class="dashboard-input" placeholder="<?php echo $date_placeholder; ?>" title="To Date (<?php echo $date_placeholder; ?>)" style="width: 100%; min-width: 0; padding: 0.3rem;">
                                </div>
                            <?php else: ?>
                                <input type="text" id="search_<?php echo $col['id']; ?>" name="filters[<?php echo $col['id']; ?>]" value="<?php echo htmlspecialchars($search_filters[$col['id']] ?? ''); ?>" placeholder="Filter (partial match)..." class="dashboard-input">
                            <?php endif; ?>
                        </div>
                    <?php endforeach; ?>
                </div>
                <div class="dashboard-actions-flex" style="margin-top: 1rem;">
                    <button type="submit" class="btn">Apply Search Filters</button>
                    <a href="data_entry.php" class="btn btn-secondary" style="text-decoration: none;">Reset Filter</a>
                    <a href="data_entry.php?export_csv=1&<?php echo htmlspecialchars(http_build_query(['filters' => $search_filters, 'date_filters' => $date_filters])); ?>" class="btn btn-secondary" style="text-decoration: none;">Download Results as CSV</a>
                </div>
            </form>
        </div>
    </details>

    <!-- DUPLICATE WARNING MODAL -->
    <?php if ($duplicate_warning): ?>
        <div class="modal-duplicate">
            <h3>⚠️ Potential Duplicate Warning</h3>
            <p>We found matching entries already in the system:</p>
            <ul>
                <?php foreach ($matches as $match): ?>
                    <li>Record ID: <?php echo $match['id']; ?> — Value: <?php echo htmlspecialchars($match['value_content']); ?></li>
                <?php endforeach; ?>
            </ul>
            <p>Do you still wish to proceed and save this duplicate entry?</p>
            <form method="POST" action="actions/save_data_entry.php">
                <?php echo csrf_field(); ?>
                <input type="hidden" name="action" value="insert_record">
                <?php foreach ($submitted_data as $cid => $cval): ?>
                    <input type="hidden" name="filters[<?php echo $cid; ?>]" value="<?php echo htmlspecialchars($cval); ?>">
                <?php endforeach; ?>
                <input type="hidden" name="confirm_duplicate" value="1">
                <button type="submit" class="btn btn-danger">Yes, Confirm and Save Duplicate</button>
                <a href="data_entry.php" class="btn btn-secondary" style="margin-left: 10px; text-decoration: none;">Cancel</a>
            </form>
        </div>
    <?php endif; ?>

    <!-- DATA ENTRY FORM -->
    <?php if (!$duplicate_warning): ?>
        <div class="search-box-container" style="margin-bottom: 2rem;">
            <h3>Add New Data Entry</h3>
            <form method="POST" action="actions/save_data_entry.php" id="data-entry-form">
                <?php echo csrf_field(); ?>
                <input type="hidden" name="action" value="insert_record">
                <div class="dashboard-grid">
                    <?php foreach ($columns as $col): ?>
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
                                    $opt1_text = 'Yes / No';
                                    $opt2_text = 'No / Off';

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
                                <select id="col_<?php echo $col['id']; ?>" name="filters[<?php echo $col['id']; ?>]" class="dashboard-input" <?php echo (!empty($col['is_required'])) ? 'required' : ''; ?>>
                                    <option value="">-- Select --</option>
                                    <option value="1"><?php echo $opt1_text; ?></option>
                                    <option value="0"><?php echo $opt2_text; ?></option>
                                </select>
                            <?php elseif (($col['data_type'] ?? '') === 'DATE'): ?>
                                <input type="text" id="col_<?php echo $col['id']; ?>" name="filters[<?php echo $col['id']; ?>]" placeholder="<?php echo $date_placeholder; ?>" class="dashboard-input" <?php echo (!empty($col['is_required'])) ? 'required' : ''; ?>>
                            <?php else: ?>
                                <input type="text" id="col_<?php echo $col['id']; ?>" name="filters[<?php echo $col['id']; ?>]" placeholder="Enter value..." class="dashboard-input" <?php echo (!empty($col['is_required'])) ? 'required' : ''; ?>>
                            <?php endif; ?>
                        </div>
                    <?php endforeach; ?>
                </div>
                <div style="margin-top: 1rem; display: flex; align-items: center; gap: 1rem; flex-wrap: wrap;">
                    <button type="submit" class="btn">Submit Data</button>
                    <span style="font-size: 0.85rem; color: #666;">💡 Tips: Press <strong>Ctrl + Enter</strong> to submit, or <strong>Esc</strong> to clear the current field.</span>
                </div>
            </form>
        </div>

        <script>
        // Keyboard shortcuts for rapid data entry
        document.addEventListener('DOMContentLoaded', () => {
            const form = document.getElementById('data-entry-form');
            if (form) {
                form.addEventListener('keydown', (e) => {
                    // Ctrl + Enter to submit form
                    if ((e.ctrlKey || e.metaKey) && e.key === 'Enter') {
                        e.preventDefault();
                        form.submit();
                    }
                    
                    // Escape key to clear the currently focused input field
                    if (e.key === 'Escape' && (e.target.tagName === 'INPUT' || e.target.tagName === 'SELECT')) {
                        e.preventDefault();
                        e.target.value = '';
                    }
                });
            }
        });
        </script>
    <?php endif; ?>

    <hr style="border: 0.0625rem solid var(--border-color); margin: 1.5rem 0;">

    <!-- EXISTING RECORDS TABLE -->
    <h3>Existing Records Table</h3>
    <table class="data-table" role="table">
        <thead>
            <tr>
                <th scope="col">ID</th>
                <?php foreach ($columns as $col): ?>
                    <th scope="col"><?php echo htmlspecialchars($col['column_name']); ?></th>
                <?php endforeach; ?>
                <th scope="col">Added By</th>
                <th scope="col">Date Created</th>
                <th scope="col">Actions</th>
            </tr>
        </thead>
        <tbody>
            <?php if (empty($paginated_records)): ?>
                <tr><td colspan="<?php echo count($columns) + 4; ?>">No records found.</td></tr>
            <?php else: ?>
                <?php foreach ($paginated_records as $rec): ?>
                    <tr>
                        <td>#<?php echo $rec['id']; ?></td>
                        <?php foreach ($columns as $col): ?>
                            <td>
                                <?php 
                                    $raw_val = $record_values[$rec['id']][$col['id']] ?? '';
                                    if (($col['data_type'] ?? '') === 'BOOLEAN') {
                                        echo htmlspecialchars(format_boolean_value($raw_val, $col['boolean_display_format'] ?? 'yes_no'));
                                    } elseif (($col['data_type'] ?? '') === 'DATE') {
                                        echo htmlspecialchars(format_display_date($raw_val, $user_date_format));
                                    } else {
                                        echo htmlspecialchars($raw_val);
                                    }
                                ?>
                            </td>
                        <?php endforeach; ?>
                        <td><em><?php echo htmlspecialchars($rec['username'] ?? 'User_Anon'); ?></em></td>
                        <td><?php echo $rec['created_at']; ?></td>
                        <td>
                            <a href="suggest_edit.php?record_id=<?php echo $rec['id']; ?>" class="suggest-link">Suggest Edit</a>
                        </td>
                    </tr>
                <?php endforeach; ?>
            <?php endif; ?>
        </tbody>
    </table>

    <!-- PAGINATION LINKS -->
    <?php if ($total_pages > 1): ?>
        <div class="pagination-container">
            <span>Page:</span>
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

    <?php require_once '../partials/footer.php'; ?>
