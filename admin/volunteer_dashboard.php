<?php
// admin/volunteer_dashboard.php - Dynamic Admin view for volunteer submissions
require_once '../db/db.php';
require_once '../db/auth_helpers.php';
require_once '../includes/functions.php';

if (!is_module_enabled($pdo, 'volunteers')) {
    http_response_code(403);
    exit('403 Forbidden: The Volunteer Portal module is currently disabled.');
}

$current_user = require_admin_page($pdo, 'manage_volunteers', 'Manage and review volunteer applications and submissions');
$message = $GLOBALS['message'] ?? '';
$error = $GLOBALS['error'] ?? '';

[$user_timezone, $full_format_str] = get_user_time_prefs($current_user);
$system_name = get_system_name($pdo);

// Fetch schema columns
$cols_stmt = $pdo->query("SELECT * FROM volunteer_columns ORDER BY sort_order ASC, column_name ASC");
$columns = $cols_stmt->fetchAll(PDO::FETCH_ASSOC);

// Fetch submissions
$subs_stmt = $pdo->query("SELECT vs.*, u.username FROM volunteer_submissions vs LEFT JOIN users u ON vs.created_by = u.id ORDER BY vs.created_at DESC");
$submissions = $subs_stmt->fetchAll(PDO::FETCH_ASSOC);

// Fetch submission values map
$vals_stmt = $pdo->query("SELECT submission_id, column_id, value_content FROM volunteer_submission_values");
$raw_values = $vals_stmt->fetchAll(PDO::FETCH_ASSOC);
$submission_values = [];
foreach ($raw_values as $val) {
    $submission_values[$val['submission_id']][$val['column_id']] = $val['value_content'];
}
?>
<?php require_once '../partials/header.php'; ?>

<div style="display: flex; justify-content: space-between; align-items: center; margin-bottom: 1.5rem;">
    <div>
        <h3>Volunteer Submissions Dashboard</h3>
        <p>Review individuals interested in volunteering for data entry and transcription work.</p>
    </div>
    <div>
        <a href="manage_volunteer_schema.php" class="btn btn-secondary" style="text-decoration: none;">⚙️ Manage Form Schema</a>
    </div>
</div>

<?php if (!empty($message)): ?>
    <p class="alert-success" role="status"><strong><?php echo htmlspecialchars($message); ?></strong></p>
<?php endif; ?>
<?php if (!empty($error)): ?>
    <p class="alert-danger" role="alert"><strong><?php echo htmlspecialchars($error); ?></strong></p>
<?php endif; ?>

<div style="overflow-x: auto;">
    <table class="data-table" role="table" style="width: 100%; border-collapse: collapse;">
        <thead>
            <tr>
                <th scope="col">ID</th>
                <?php foreach ($columns as $col): ?>
                    <th scope="col"><?php echo htmlspecialchars($col['column_name']); ?></th>
                <?php endforeach; ?>
                <th scope="col">Date Submitted</th>
                <th scope="col">Actions</th>
            </tr>
        </thead>
        <tbody>
            <?php if (empty($submissions)): ?>
                <tr><td colspan="<?php echo count($columns) + 3; ?>" style="text-align: center; color: #666; padding: 1rem;">No volunteer submissions found.</td></tr>
            <?php else: ?>
                <?php foreach ($submissions as $sub): ?>
                    <tr>
                        <td>#<?php echo $sub['id']; ?></td>
                        <?php foreach ($columns as $col): 
                            $raw_val = $submission_values[$sub['id']][$col['id']] ?? '';
                            if (($col['data_type'] ?? '') === 'BOOLEAN') {
                                $display_val = format_boolean_value($raw_val, $col['boolean_display_format'] ?? 'yes_no');
                            } elseif (($col['data_type'] ?? '') === 'DATE') {
                                $display_val = format_display_date($raw_val, $current_user['date_format'] ?? 'd/m/Y');
                            } else {
                                $display_val = nl2br(htmlspecialchars($raw_val));
                            }
                        ?>
                            <td><?php echo $display_val; ?></td>
                        <?php endforeach; ?>
                        <td><?php echo format_user_time($sub['created_at'], $user_timezone, $full_format_str); ?></td>
                        <td style="white-space: nowrap;">
                            <form method="POST" action="actions/save_volunteer.php" style="display: inline;" onsubmit="return confirm('Are you sure you want to delete this volunteer entry?');">
                                <?php echo csrf_field(); ?>
                                <input type="hidden" name="action" value="delete_volunteer">
                                <input type="hidden" name="volunteer_id" value="<?php echo $sub['id']; ?>">
                                <button type="submit" class="btn btn-danger" style="padding: 0.25rem 0.5rem; font-size: 0.85rem;">Delete</button>
                            </form>
                        </td>
                    </tr>
                <?php endforeach; ?>
            <?php endif; ?>
        </tbody>
    </table>
</div>

<?php require_once '../partials/footer.php'; ?>
