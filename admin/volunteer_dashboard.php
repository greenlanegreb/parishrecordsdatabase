<?php
// admin/volunteer_dashboard.php - Admin view for volunteer submissions
require_once '../db/db.php';
require_once '../db/auth_helpers.php';
require_once '../includes/functions.php';
session_start();

// Enforce strict administrator privileges via central helper
require_role($pdo, ['admin']);
$current_user = get_current_user_data($pdo);

// Determine user timezone and dynamically compile the date/time format string using the central helper
$user_timezone = $current_user['timezone'] ?? 'UTC';
$full_format_str = get_user_datetime_format($current_user);

// Dynamic system name for mail subjects
$system_name = (function_exists('get_system_name') && isset($pdo)) ? get_system_name($pdo) : "Parish Records Directory (PRD)";

$message = $_SESSION['message'] ?? '';
$error = $_SESSION['error'] ?? '';
unset($_SESSION['message'], $_SESSION['error']);

$vol_stmt = $pdo->query("SELECT * FROM volunteers ORDER BY created_at DESC");
$volunteers = $vol_stmt->fetchAll();
?>

    <?php require_once '../partials/header.php'; ?>

    <h3>Volunteer Submissions Dashboard</h3>
    <p>Review individuals interested in volunteering for data entry and transcription work.</p>

    <?php if (!empty($message)): ?>
        <p class="alert-success"><strong><?php echo htmlspecialchars($message); ?></strong></p>
    <?php endif; ?>
    <?php if (!empty($error)): ?>
        <p class="alert-danger"><strong><?php echo htmlspecialchars($error); ?></strong></p>
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
