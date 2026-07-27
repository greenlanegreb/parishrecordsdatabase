<?php
// admin/manage_tables.php - Dynamic Table and Schema Management Interface
require_once '../db/db.php';
require_once '../db/auth_helpers.php';
require_once '../includes/functions.php';

// Standard admin bootstrap (permission + flash messages)
$current_user = require_admin_page($pdo, 'manage_tables', 'Manage dynamic database tables and column schema definitions');
$message = $GLOBALS['message'] ?? '';
$error   = $GLOBALS['error']   ?? '';

// Determine user timezone and dynamically compile the date/time format string
$user_timezone   = $current_user['timezone'] ?? 'UTC';
$full_format_str = get_user_datetime_format($current_user);

// Fetch all existing dynamic tables
$tables = $pdo->query("SELECT * FROM dynamic_tables ORDER BY id ASC")->fetchAll(PDO::FETCH_ASSOC);

// Determine active table selection (default to first table if not specified)
$active_table_id = isset($_GET['table_id']) ? intval($_GET['table_id']) : (!empty($tables) ? $tables[0]['id'] : 1);

// Find active table details
$active_table_info = null;
foreach ($tables as $t) {
    if ($t['id'] === $active_table_id) {
        $active_table_info = $t;
        break;
    }
}

// Check if we are editing an existing table definition
$edit_table = null;
if (isset($_GET['edit_table'])) {
    $edit_id = intval($_GET['edit_table']);
    $stmt = $pdo->prepare("SELECT * FROM dynamic_tables WHERE id = ?");
    $stmt->execute([$edit_id]);
    $edit_table = $stmt->fetch(PDO::FETCH_ASSOC);
}

// Check if we are editing an existing column schema within the active table
$edit_col = null;
if (isset($_GET['edit_column'])) {
    $edit_col_id = intval($_GET['edit_column']);
    $c_stmt = $pdo->prepare("SELECT * FROM table_columns WHERE id = ?");
    $c_stmt->execute([$edit_col_id]);
    $edit_col = $c_stmt->fetch(PDO::FETCH_ASSOC);
    if ($edit_col) {
        $active_table_id = intval($edit_col['table_id']); // Ensure active table matches the column being edited
    }
}

// Fetch columns for the currently active table ordered by sort_order first, then column name
$columns_stmt = $pdo->prepare("SELECT tc.*, u.username FROM table_columns tc JOIN users u ON tc.created_by = u.id WHERE tc.table_id = ? ORDER BY tc.sort_order ASC, tc.column_name ASC");
$columns_stmt->execute([$active_table_id]);
$columns = $columns_stmt->fetchAll(PDO::FETCH_ASSOC);
?>
<?php require_once '../partials/header.php'; ?>

<div class="search-box-container" role="region" aria-label="Dynamic Table Management" style="max-width: 1100px; margin: 0 auto;">
    <h3>Dynamic Table & Schema Management</h3>
    <p>Create, inspect, modify, or safely decommission dynamic application tables and their underlying column schemas.</p>

    <?php if (!empty($error)): ?>
        <p class="alert-danger" role="alert"><strong><?php echo htmlspecialchars($error); ?></strong></p>
    <?php endif; ?>
    <?php if (!empty($message)): ?>
        <p class="alert-success" role="status"><strong><?php echo htmlspecialchars($message); ?></strong></p>
    <?php endif; ?>

    <!-- Table Selector Bar & Quick Management -->
    <div style="display: flex; justify-content: space-between; align-items: center; background: rgba(0,0,0,0.02); padding: 1rem; border: 1px solid var(--border-color); border-radius: 6px; margin-bottom: 2rem; flex-wrap: wrap; gap: 1rem;">
        <div>
            <label for="table_switcher" style="font-size: 0.85rem; font-weight: bold;">Select Active Table Schema:</label><br>
            <select id="table_switcher" class="volunteer-input" style="padding: 0.4rem; margin-top: 0.3rem; min-width: 250px;" onchange="if(this.value) window.location.href='manage_tables.php?table_id=' + this.value;">
                <?php foreach ($tables as $t): ?>
                    <option value="<?php echo $t['id']; ?>" <?php echo ($t['id'] === $active_table_id) ? 'selected' : ''; ?>>
                        <?php echo htmlspecialchars($t['table_name']); ?> (ID: <?php echo $t['id']; ?>)
                    </option>
                <?php endforeach; ?>
            </select>
        </div>
       
        <?php if ($active_table_info): ?>
            <div style="display: flex; gap: 0.5rem; align-items: center;">
                <a href="manage_tables.php?edit_table=<?php echo $active_table_info['id']; ?>&table_id=<?php echo $active_table_id; ?>" class="btn btn-secondary" style="font-size: 0.85rem; text-decoration: none; padding: 0.4rem 0.8rem;">Edit Table Metadata</a>
                <?php if ($active_table_info['id'] > 1): ?>
                    <form method="POST" action="actions/save_manage_tables.php" style="display: inline;" onsubmit="return confirm('WARNING: Deleting table \'<?php echo htmlspecialchars($active_table_info['table_name']); ?>\' will permanently delete all its columns and recorded contents. Are you absolutely sure?');">
                        <?php echo csrf_field(); ?>
                        <input type="hidden" name="action" value="delete_table">
                        <input type="hidden" name="table_id" value="<?php echo $active_table_info['id']; ?>">
                        <button type="submit" class="btn btn-danger" style="font-size: 0.85rem; padding: 0.4rem 0.8rem;">Delete Table</button>
                    </form>
                <?php endif; ?>
            </div>
        <?php endif; ?>
    </div>

    <!-- Create New Table / Edit Table Collapsible Section -->
    <details id="table-form-details" style="background: rgba(0,0,0,0.02); border: 1px solid var(--border-color); border-radius: 6px; margin-bottom: 2rem; padding: 1rem 1.25rem;" <?php echo $edit_table ? 'open' : ''; ?>>
        <summary style="cursor: pointer; font-weight: bold; color: #333; outline: none;">
            <?php echo $edit_table ? 'Edit Table Definition: ' . htmlspecialchars($edit_table['table_name']) : '+ Create New Dynamic Table'; ?>
        </summary>
       
        <div style="margin-top: 1rem; border-top: 1px solid var(--border-color); padding-top: 1rem;">
            <form method="POST" action="actions/save_manage_tables.php">
                <?php echo csrf_field(); ?>
                <input type="hidden" name="action" value="<?php echo $edit_table ? 'update_table' : 'create_table'; ?>">
                <input type="hidden" name="table_id" value="<?php echo $active_table_id; ?>">
                <?php if ($edit_table): ?>
                    <input type="hidden" name="table_id" value="<?php echo $edit_table['id']; ?>">
                <?php endif; ?>
                <div style="margin-bottom: 1rem;">
                    <label for="table_name"><strong>Friendly Table Name:</strong> <span style="color: red;">*</span></label><br>
                    <input type="text" id="table_name" name="table_name" value="<?php echo $edit_table ? htmlspecialchars($edit_table['table_name']) : ''; ?>" placeholder="e.g. Parish Records" required class="volunteer-input" style="width: 100%; max-width: 400px; padding: 0.4rem; margin-top: 0.3rem;">
                </div>
                <div style="margin-bottom: 1rem;">
                    <label for="table_description"><strong>Description / Purpose:</strong></label><br>
                    <textarea id="table_description" name="description" rows="2" placeholder="Brief summary of records stored in this table..." class="volunteer-input" style="width: 100%; max-width: 500px; padding: 0.4rem; margin-top: 0.3rem;"><?php echo $edit_table ? htmlspecialchars($edit_table['description'] ?? '') : ''; ?></textarea>
                </div>
                <div>
                    <button type="submit" class="btn"><?php echo $edit_table ? 'Save Table Changes' : 'Create Table Schema'; ?></button>
                    <?php if ($edit_table): ?>
                        <a href="manage_tables.php?table_id=<?php echo $active_table_id; ?>" class="btn btn-secondary" style="margin-left: 0.5rem; text-decoration: none; padding: 0.35rem 0.7rem; font-size: 0.9rem;">Cancel</a>
                    <?php endif; ?>
                </div>
            </form>
        </div>
    </details>

    <?php if ($active_table_info): ?>
        <hr style="border: 0.0625rem solid var(--border-color); margin: 2rem 0;">

        <!-- Collapsible Column Form Container -->
        <details class="search-box-container" id="create-column-details" <?php echo $edit_col ? 'open' : ''; ?> style="margin-bottom: 2rem; background: rgba(0,0,0,0.01);">
            <summary style="cursor: pointer; font-weight: bold; font-size: 1.15rem; color: #333; padding: 0.25rem 0;">
                <?php echo $edit_col ? 'Edit Dynamic Column: ' . htmlspecialchars($edit_col['column_name']) : '+ Add New Table Column for "' . htmlspecialchars($active_table_info['table_name']) . '"'; ?>
            </summary>
           
            <div style="margin-top: 1rem; padding-top: 1rem; border-top: 1px solid var(--border-color, #ccc);">
                <form method="POST" action="actions/save_manage_tables.php">
                    <?php echo csrf_field(); ?>
                    <input type="hidden" name="action" value="<?php echo $edit_col ? 'update' : 'create'; ?>">
                    <input type="hidden" name="table_id" value="<?php echo $active_table_id; ?>">
                    <?php if ($edit_col): ?>
                        <input type="hidden" name="column_id" value="<?php echo $edit_col['id']; ?>">
                    <?php endif; ?>
                   
                    <label for="column_name">Column Name: <span style="color: red;">*</span></label><br>
                    <input type="text" id="column_name" name="column_name" value="<?php echo $edit_col ? htmlspecialchars($edit_col['column_name']) : ''; ?>" required class="volunteer-input" style="width: 100%; max-width: 400px; padding: 0.4rem; margin-bottom: 1rem;"><br>

                    <label for="data_type">Data Type:</label><br>
                    <select id="data_type" name="data_type" class="volunteer-input" style="width: 100%; max-width: 400px; padding: 0.4rem; margin-bottom: 1rem;" onchange="toggleFieldOptions(this.value)">
                        <option value="VARCHAR" <?php echo ($edit_col && $edit_col['data_type'] === 'VARCHAR') ? 'selected' : ''; ?>>VARCHAR (Short Text)</option>
                        <option value="TEXT" <?php echo ($edit_col && $edit_col['data_type'] === 'TEXT') ? 'selected' : ''; ?>>TEXT (Long Paragraph)</option>
                        <option value="INT" <?php echo ($edit_col && $edit_col['data_type'] === 'INT') ? 'selected' : ''; ?>>INT (Whole Number)</option>
                        <option value="BOOLEAN" <?php echo ($edit_col && $edit_col['data_type'] === 'BOOLEAN') ? 'selected' : ''; ?>>BOOLEAN (Yes/No Flag)</option>
                        <option value="DATE" <?php echo ($edit_col && $edit_col['data_type'] === 'DATE') ? 'selected' : ''; ?>>DATE (Calendar Date)</option>
                    </select><br>

                    <!-- Dynamic Boolean Display Style Option -->
                    <div id="boolean_options_wrapper" style="display: <?php echo ($edit_col && $edit_col['data_type'] === 'BOOLEAN') ? 'block' : 'none'; ?>; margin-bottom: 1rem;">
                        <label for="boolean_display_format">Boolean Display Format:</label><br>
                        <select id="boolean_display_format" name="boolean_display_format" class="volunteer-input" style="width: 100%; max-width: 400px; padding: 0.4rem;">
                            <option value="yes_no" <?php echo ($edit_col && ($edit_col['boolean_display_format'] ?? '') === 'yes_no') ? 'selected' : ''; ?>>Yes / No</option>
                            <option value="true_false" <?php echo ($edit_col && ($edit_col['boolean_display_format'] ?? '') === 'true_false') ? 'selected' : ''; ?>>True / False</option>
                            <option value="tick_cross" <?php echo ($edit_col && ($edit_col['boolean_display_format'] ?? '') === 'tick_cross') ? 'selected' : ''; ?>>Tick / Cross (✔ / ✘)</option>
                            <option value="male_female" <?php echo ($edit_col && ($edit_col['boolean_display_format'] ?? '') === 'male_female') ? 'selected' : ''; ?>>Male / Female</option>
                        </select>
                    </div>

                    <!-- Dynamic Date Search Behavior Option -->
                    <div id="date_options_wrapper" style="display: <?php echo ($edit_col && $edit_col['data_type'] === 'DATE') ? 'block' : 'none'; ?>; margin-bottom: 1rem;">
                        <label for="date_search_behavior">Date Search Behavior:</label><br>
                        <select id="date_search_behavior" name="date_search_behavior" class="volunteer-input" style="width: 100%; max-width: 400px; padding: 0.4rem;">
                            <option value="manual_only" <?php echo ($edit_col && ($edit_col['date_search_behavior'] ?? '') === 'manual_only') ? 'selected' : ''; ?>>Dates in database (manual entry only)</option>
                            <option value="admin_only" <?php echo ($edit_col && ($edit_col['date_search_behavior'] ?? '') === 'admin_only') ? 'selected' : ''; ?>>Administrative dates only</option>
                            <option value="all_dates" <?php echo ($edit_col && ($edit_col['date_search_behavior'] ?? '') === 'all_dates') ? 'selected' : ''; ?>>All dates including administrative</option>
                        </select>
                    </div>

                    <label for="max_length">Max Size / Length (Optional limit):</label><br>
                    <input type="number" id="max_length" name="max_length" value="<?php echo $edit_col ? htmlspecialchars($edit_col['max_length'] ?? '') : ''; ?>" placeholder="e.g. 255" class="volunteer-input" style="width: 100%; max-width: 400px; padding: 0.4rem; margin-bottom: 1rem;"><br>

                    <label for="sort_order">Display Sort Order (Number):</label><br>
                    <input type="number" id="sort_order" name="sort_order" value="<?php echo $edit_col ? intval($edit_col['sort_order']) : 0; ?>" class="volunteer-input" style="width: 100%; max-width: 400px; padding: 0.4rem; margin-bottom: 1rem;"><br>

                    <!-- Required Field Toggle Option -->
                    <div style="margin-bottom: 1rem; display: flex; align-items: center; gap: 0.5rem;">
                        <input type="checkbox" id="is_required" name="is_required" value="1" <?php echo ($edit_col && !empty($edit_col['is_required'])) ? 'checked' : ''; ?> style="cursor: pointer;">
                        <label for="is_required" style="cursor: pointer; font-weight: normal; margin-bottom: 0;">Make this column required (mandatory data entry)</label>
                    </div>

                    <!-- Exclude from Public Search Toggle Option -->
                    <div style="margin-bottom: 1rem; display: flex; align-items: center; gap: 0.5rem;">
                        <input type="checkbox" id="exclude_from_public_search" name="exclude_from_public_search" value="1" <?php echo ($edit_col && !empty($edit_col['exclude_from_public_search'])) ? 'checked' : ''; ?> style="cursor: pointer;">
                        <label for="exclude_from_public_search" style="cursor: pointer; font-weight: normal; margin-bottom: 0;">Exclude this column from public search (index.php)</label>
                    </div>

                    <button type="submit" class="btn"><?php echo $edit_col ? 'Save Changes' : 'Create Column'; ?></button>
                    <?php if ($edit_col): ?>
                        <a href="manage_tables.php?table_id=<?php echo $active_table_id; ?>" class="btn btn-secondary" style="margin-left: 0.5rem; text-decoration: none;">Cancel</a>
                    <?php endif; ?>
                </form>
            </div>
        </details>

        <script>
        function toggleFieldOptions(val) {
            var boolWrapper = document.getElementById('boolean_options_wrapper');
            var dateWrapper = document.getElementById('date_options_wrapper');
           
            boolWrapper.style.display = (val === 'BOOLEAN') ? 'block' : 'none';
            dateWrapper.style.display = (val === 'DATE') ? 'block' : 'none';
        }
        </script>

        <hr style="border: 0.0625rem solid var(--border-color); margin: 1.5rem 0;">

        <div style="display: flex; justify-content: space-between; align-items: center; margin-bottom: 1rem;">
            <h3 style="margin: 0;">Existing Columns for "<?php echo htmlspecialchars($active_table_info['table_name']); ?>"</h3>
        </div>

        <!-- Single Batch Form wrapping the table for sort order updates -->
        <form method="POST" action="actions/save_manage_tables.php">
            <?php echo csrf_field(); ?>
            <input type="hidden" name="action" value="update_order_batch">
            <input type="hidden" name="table_id" value="<?php echo $active_table_id; ?>">
            <div style="overflow-x: auto;">
                <table border="1" cellpadding="8" cellspacing="0" role="table" class="data-table" style="width: 100%; border-collapse: collapse;">
                    <thead>
                        <tr style="border-bottom: 2px solid var(--border-color);">
                            <th scope="col">Sort Order</th>
                            <th scope="col">Column Name</th>
                            <th scope="col">Data Type</th>
                            <th scope="col">Required?</th>
                            <th scope="col">Public Search?</th>
                            <th scope="col">Display Format</th>
                            <th scope="col">Max Length</th>
                            <th scope="col">Created By</th>
                            <th scope="col">Date Created</th>
                            <th scope="col">Actions</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php if (empty($columns)): ?>
                            <tr><td colspan="10" style="text-align: center; color: #666; padding: 1rem;">No dynamic columns defined for this table yet.</td></tr>
                        <?php else: ?>
                            <?php foreach ($columns as $col): ?>
                                <tr style="border-bottom: 1px solid var(--border-color);">
                                    <td>
                                        <input type="number" name="sort_orders[<?php echo $col['id']; ?>]" value="<?php echo intval($col['sort_order']); ?>" aria-label="Sort order for <?php echo htmlspecialchars($col['column_name']); ?>" style="width: 60px; padding: 0.3rem;">
                                    </td>
                                    <td><strong><?php echo htmlspecialchars($col['column_name']); ?></strong></td>
                                    <td><code style="font-family: monospace;"><?php echo htmlspecialchars($col['data_type']); ?></code></td>
                                    <td>
                                        <?php if (!empty($col['is_required'])): ?>
                                            <span style="color: green; font-weight: bold;">Yes</span>
                                        <?php else: ?>
                                            <span style="color: gray;">No</span>
                                        <?php endif; ?>
                                    </td>
                                    <td>
                                        <?php if (empty($col['exclude_from_public_search'])): ?>
                                            <span style="color: green; font-weight: bold;">Yes</span>
                                        <?php else: ?>
                                            <span style="color: var(--danger-color, #dc3545); font-weight: bold;">Hidden</span>
                                        <?php endif; ?>
                                    </td>
                                    <td>
                                        <?php
                                            if ($col['data_type'] === 'BOOLEAN') {
                                                echo htmlspecialchars($col['boolean_display_format'] ?? 'yes_no');
                                            } elseif ($col['data_type'] === 'DATE') {
                                                echo htmlspecialchars($col['date_search_behavior'] ?? 'manual_only');
                                            } else {
                                                echo 'N/A';
                                            }
                                        ?>
                                    </td>
                                    <td><?php echo $col['max_length'] ?? 'N/A'; ?></td>
                                    <td><?php echo htmlspecialchars($col['username'] ?? 'System'); ?></td>
                                    <td><?php echo format_user_time($col['created_at'], $user_timezone, $full_format_str); ?></td>
                                    <td style="white-space: nowrap;">
                                        <a href="manage_tables.php?table_id=<?php echo $active_table_id; ?>&edit_column=<?php echo $col['id']; ?>#create-column-details" class="btn btn-secondary" style="padding: 0.25rem 0.5rem; font-size: 0.85rem; text-decoration: none; margin-right: 4px;">Edit</a>
                                       
                                        <button type="submit" name="action" value="delete" formaction="actions/save_manage_tables.php" onclick="document.getElementById('delete_col_id').value='<?php echo $col['id']; ?>'; return confirm('WARNING: Deleting this column will also remove all associated cell data across all records. Are you sure?');" class="btn btn-danger" style="padding: 0.25rem 0.5rem; font-size: 0.85rem;" formnovalidate>Delete</button>
                                    </td>
                                </tr>
                            <?php endforeach; ?>
                        <?php endif; ?>
                    </tbody>
                </table>
            </div>
            <input type="hidden" name="column_id" id="delete_col_id" value="">
            <?php if (!empty($columns)): ?>
                <div style="margin-top: 1rem;">
                    <button type="submit" class="btn">Save All Sort Orders</button>
                </div>
            <?php endif; ?>
        </form>
    <?php endif; ?>
</div>

<?php require_once '../partials/footer.php'; ?>
