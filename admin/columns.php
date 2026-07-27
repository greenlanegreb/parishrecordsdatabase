<?php
// admin/columns.php - Admin interface view for managing dynamic columns
require_once '../db/db.php';
require_once '../db/auth_helpers.php';
require_once '../includes/functions.php';
session_start();

// Enforce strict administrator privileges via central helper
require_role($pdo, 'admin');
$current_user = get_current_user_data($pdo);

// Determine user timezone, date display format, and clock/time format rules
$user_timezone = $current_user['timezone'] ?? 'UTC';
$user_date_format = $current_user['date_format'] ?? 'd/m/Y';
$user_time_format = $current_user['time_format'] ?? '24';

// Dynamically compile the PHP date/time format string based on decoupled preferences
if ($user_time_format === '12') {
    $full_format_str = $user_date_format . ' h:i A';
} elseif ($user_time_format === '24') {
    $full_format_str = $user_date_format . ' H:i';
} else {
    $full_format_str = $user_date_format; // Date only
}

// Pull session flash messages safely
$error = $_SESSION['error'] ?? '';
$message = $_SESSION['message'] ?? '';
unset($_SESSION['error'], $_SESSION['message']);

// Check if we are editing an existing column
$edit_col = null;
if (isset($_GET['edit'])) {
    $edit_id = intval($_GET['edit']);
    $stmt = $pdo->prepare("SELECT * FROM table_columns WHERE id = ?");
    $stmt->execute([$edit_id]);
    $edit_col = $stmt->fetch();
}

// Fetch columns ordered by sort_order first, then column name for reliable tie-breaking
$cols_stmt = $pdo->query("SELECT tc.*, u.username FROM table_columns tc JOIN users u ON tc.created_by = u.id ORDER BY tc.sort_order ASC, tc.column_name ASC");
$columns = $cols_stmt->fetchAll();

// Helper function to format timestamps based on user timezone and compiled format string
function format_user_time($utc_timestamp, $timezone_str, $format_str) {
    if (empty($utc_timestamp)) return 'N/A';
    try {
        $dt = new DateTime($utc_timestamp, new DateTimeZone('UTC'));
        $dt->setTimezone(new DateTimeZone($timezone_str));
        return $dt->format($format_str);
    } catch (Exception $e) {
        return $utc_timestamp;
    }
}
?>

    <?php require_once '../partials/header.php'; ?>

    <?php if (!empty($error)): ?>
        <p style="color: var(--danger-color); font-weight: bold;"><?php echo htmlspecialchars($error); ?></p>
    <?php endif; ?>
    <?php if (!empty($message)): ?>
        <p style="color: green; font-weight: bold;"><?php echo htmlspecialchars($message); ?></p>
    <?php endif; ?>

    <div class="search-box-container" id="create-column-form">
        <h3><?php echo $edit_col ? 'Edit Dynamic Column: ' . htmlspecialchars($edit_col['column_name']) : 'Create New Table Column'; ?></h3>
        <form method="POST" action="actions/save_column.php">
            <input type="hidden" name="action" value="<?php echo $edit_col ? 'update' : 'create'; ?>">
            <?php if ($edit_col): ?>
                <input type="hidden" name="column_id" value="<?php echo $edit_col['id']; ?>">
            <?php endif; ?>
            
            <label for="column_name">Column Name:</label><br>
            <input type="text" id="column_name" name="column_name" value="<?php echo $edit_col ? htmlspecialchars($edit_col['column_name']) : ''; ?>" required style="width: 100%; max-width: 400px; padding: 0.4rem; margin-bottom: 1rem;"><br>

            <label for="data_type">Data Type:</label><br>
            <select id="data_type" name="data_type" style="width: 100%; max-width: 400px; padding: 0.4rem; margin-bottom: 1rem;" onchange="toggleFieldOptions(this.value)">
                <option value="VARCHAR" <?php echo ($edit_col && $edit_col['data_type'] === 'VARCHAR') ? 'selected' : ''; ?>>VARCHAR (Short Text)</option>
                <option value="TEXT" <?php echo ($edit_col && $edit_col['data_type'] === 'TEXT') ? 'selected' : ''; ?>>TEXT (Long Paragraph)</option>
                <option value="INT" <?php echo ($edit_col && $edit_col['data_type'] === 'INT') ? 'selected' : ''; ?>>INT (Whole Number)</option>
                <option value="BOOLEAN" <?php echo ($edit_col && $edit_col['data_type'] === 'BOOLEAN') ? 'selected' : ''; ?>>BOOLEAN (Yes/No Flag)</option>
                <option value="DATE" <?php echo ($edit_col && $edit_col['data_type'] === 'DATE') ? 'selected' : ''; ?>>DATE (Calendar Date)</option>
            </select><br>

            <!-- Dynamic Boolean Display Style Option -->
            <div id="boolean_options_wrapper" style="display: <?php echo ($edit_col && $edit_col['data_type'] === 'BOOLEAN') ? 'block' : 'none'; ?>; margin-bottom: 1rem;">
                <label for="boolean_display_format">Boolean Display Format:</label><br>
                <select id="boolean_display_format" name="boolean_display_format" style="width: 100%; max-width: 400px; padding: 0.4rem;">
                    <option value="yes_no" <?php echo ($edit_col && $edit_col['boolean_display_format'] === 'yes_no') ? 'selected' : ''; ?>>Yes / No</option>
                    <option value="true_false" <?php echo ($edit_col && $edit_col['boolean_display_format'] === 'true_false') ? 'selected' : ''; ?>>True / False</option>
                    <option value="tick_cross" <?php echo ($edit_col && $edit_col['boolean_display_format'] === 'tick_cross') ? 'selected' : ''; ?>>Tick / Cross (✔ / ✘)</option>
                    <option value="male_female" <?php echo ($edit_col && $edit_col['boolean_display_format'] === 'male_female') ? 'selected' : ''; ?>>Male / Female</option>
                </select>
            </div>

            <!-- Dynamic Date Search Behavior Option -->
            <div id="date_options_wrapper" style="display: <?php echo ($edit_col && $edit_col['data_type'] === 'DATE') ? 'block' : 'none'; ?>; margin-bottom: 1rem;">
                <label for="date_search_behavior">Date Search Behavior:</label><br>
                <select id="date_search_behavior" name="date_search_behavior" style="width: 100%; max-width: 400px; padding: 0.4rem;">
                    <option value="manual_only" <?php echo ($edit_col && ($edit_col['date_search_behavior'] ?? '') === 'manual_only') ? 'selected' : ''; ?>>Dates in database (manual entry only)</option>
                    <option value="admin_only" <?php echo ($edit_col && ($edit_col['date_search_behavior'] ?? '') === 'admin_only') ? 'selected' : ''; ?>>Administrative dates only</option>
                    <option value="all_dates" <?php echo ($edit_col && ($edit_col['date_search_behavior'] ?? '') === 'all_dates') ? 'selected' : ''; ?>>All dates including administrative</option>
                </select>
            </div>

            <label for="max_length">Max Size / Length (Optional limit):</label><br>
            <input type="number" id="max_length" name="max_length" value="<?php echo $edit_col ? htmlspecialchars($edit_col['max_length'] ?? '') : ''; ?>" placeholder="e.g. 255" style="width: 100%; max-width: 400px; padding: 0.4rem; margin-bottom: 1rem;"><br>

            <label for="sort_order">Display Sort Order (Number):</label><br>
            <input type="number" id="sort_order" name="sort_order" value="<?php echo $edit_col ? intval($edit_col['sort_order']) : 0; ?>" style="width: 100%; max-width: 400px; padding: 0.4rem; margin-bottom: 1rem;"><br>

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
                <a href="columns.php" class="btn btn-secondary" style="margin-left: 0.5rem; text-decoration: none;">Cancel</a>
            <?php endif; ?>
        </form>
    </div>

    <script>
    function toggleFieldOptions(val) {
        var boolWrapper = document.getElementById('boolean_options_wrapper');
        var dateWrapper = document.getElementById('date_options_wrapper');
        
        boolWrapper.style.display = (val === 'BOOLEAN') ? 'block' : 'none';
        dateWrapper.style.display = (val === 'DATE') ? 'block' : 'none';
    }
    </script>

    <hr style="border: 0.0625rem solid var(--border-color); margin: 1.5rem 0;">

    <h3>Existing Dynamic Columns</h3>
    <table border="1" cellpadding="8" cellspacing="0" role="table" style="width: 100%; border-collapse: collapse;">
        <thead>
            <tr>
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
                <tr><td colspan="10">No dynamic columns defined yet.</td></tr>
            <?php else: ?>
                <?php foreach ($columns as $col): ?>
                    <tr>
                        <td>
                            <!-- Inline Quick-Update Form for Sort Order -->
                            <form method="POST" action="actions/save_column.php" style="display: flex; gap: 4px; align-items: center;">
                                <input type="hidden" name="action" value="update_order">
                                <input type="hidden" name="column_id" value="<?php echo $col['id']; ?>">
                                <input type="number" name="sort_order" value="<?php echo intval($col['sort_order']); ?>" aria-label="Sort order for <?php echo htmlspecialchars($col['column_name']); ?>" style="width: 50px; padding: 0.2rem;">
                                <button type="submit" class="btn btn-secondary" style="padding: 0.2rem 0.4rem; font-size: 0.75rem;" title="Save Order">✔</button>
                            </form>
                        </td>
                        <td><?php echo htmlspecialchars($col['column_name']); ?></td>
                        <td><?php echo htmlspecialchars($col['data_type']); ?></td>
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
                                <span style="color: var(--danger-color); font-weight: bold;">Hidden</span>
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
                        <td><?php echo htmlspecialchars($col['username']); ?></td>
                        <td><?php echo format_user_time($col['created_at'], $user_timezone, $full_format_str); ?></td>
                        <td>
                            <a href="columns.php?edit=<?php echo $col['id']; ?>#create-column-form" class="btn btn-secondary" style="padding: 0.25rem 0.5rem; font-size: 0.85rem; text-decoration: none; margin-right: 4px;">Edit</a>
                            
                            <form method="POST" action="actions/save_column.php" onsubmit="return confirm('WARNING: Deleting this column will also remove all associated cell data across all records. Are you sure?');" style="display:inline;">
                                <input type="hidden" name="action" value="delete">
                                <input type="hidden" name="column_id" value="<?php echo $col['id']; ?>">
                                <button type="submit" class="btn btn-danger" style="padding: 0.25rem 0.5rem; font-size: 0.85rem;">Delete Column</button>
                            </form>
                        </td>
                    </tr>
                <?php endforeach; ?>
            <?php endif; ?>
        </tbody>
    </table>

    <?php require_once '../partials/footer.php'; ?>
