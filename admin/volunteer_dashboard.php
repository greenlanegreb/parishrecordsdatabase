<?php
// admin/volunteer_dashboard.php - Admin view for volunteer submissions
require_once '../db/db.php';
require_once '../db/auth_helpers.php';
require_once '../includes/functions.php';

// Ensure the volunteers module is enabled; otherwise block direct access
if (!is_module_enabled($pdo, 'volunteers')) {
    http_response_code(403);
    exit('403 Forbidden: The Volunteer Portal module is currently disabled.');
}

// Standard admin bootstrap (permission + flash messages)
$current_user = require_admin_page($pdo, 'manage_volunteers', 'Manage and review volunteer applications and submissions');
$message = $GLOBALS['message'] ?? '';
$error   = $GLOBALS['error']   ?? '';

// Determine user timezone and dynamically compile the date/time format string
$user_timezone   = $current_user['timezone'] ?? 'UTC';
$full_format_str = get_user_datetime_format($current_user);

// Dynamic system name for mail subjects
$system_name = get_system_name($pdo);

$vol_stmt = $pdo->query("SELECT * FROM volunteers ORDER BY created_at DESC");
$volunteers = $vol_stmt->fetchAll();
?>
<?php require_once '../partials/header.php'; ?>

<h3>Volunteer Submissions Dashboard</h3>
<p>Review individuals interested in volunteering for data entry and transcription work.</p>

<?php if (!empty($message)): ?>
    <p class="alert-success" role="status"><strong><?php echo htmlspecialchars($message); ?></strong></p>
<?php endif; ?>
<?php if (!empty($error)): ?>
    <p class="alert-danger" role="alert"><strong><?php echo htmlspecialchars($error); ?></strong></p>
<?php endif; ?>

<table class="data-table" role="table">
    <thead>
        <tr>
            <th scope="col">ID</th>
            <th scope="col">Name</th>
            <th scope="col">Email</th>
            <th scope="col">Experience / Message</th>
            <th scope="col">Date Submitted</th>
            <th scope="col">Actions</th>
        </tr>
    </thead>
    <tbody>
        <?php if (empty($volunteers)): ?>
            <tr><td colspan="6">No volunteer submissions found.</td></tr>
        <?php else: ?>
            <?php foreach ($volunteers as $vol): ?>
                <?php
                    $mail_subject = urlencode("Regarding your Volunteer Interest - " . $system_name);
                ?>
                <tr>
                    <td>#<?php echo $vol['id']; ?></td>
                    <td><?php echo htmlspecialchars($vol['name']); ?></td>
                    <td><?php echo htmlspecialchars($vol['email']); ?></td>
                    <td><?php echo nl2br(htmlspecialchars($vol['experience'])); ?></td>
                    <td><?php echo format_user_time($vol['created_at'], $user_timezone, $full_format_str); ?></td>
                    <td class="volunteer-actions-cell">
                        <!-- Email Mailto Button -->
                        <a href="mailto:<?php echo htmlspecialchars($vol['email']); ?>?subject=<?php echo $mail_subject; ?>" class="btn btn-secondary volunteer-email-btn">Email</a>
                       
                        <!-- Delete Form Button -->
                        <form method="POST" action="actions/save_volunteer.php" style="display: inline;" onsubmit="return confirm('Are you sure you want to delete this volunteer entry?');">
                            <?php echo csrf_field(); ?>
                            <input type="hidden" name="action" value="delete_volunteer">
                            <input type="hidden" name="volunteer_id" value="<?php echo $vol['id']; ?>">
                            <button type="submit" class="btn btn-danger volunteer-delete-btn">Delete</button>
                        </form>
                    </td>
                </tr>
            <?php endforeach; ?>
        <?php endif; ?>
    </tbody>
</table>

<?php require_once '../partials/footer.php'; ?>
