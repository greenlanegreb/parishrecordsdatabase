<?php
// admin/feedback_dashboard.php - Admin feedback management view
require_once '../db/db.php';
require_once '../db/auth_helpers.php';
require_once '../includes/functions.php';
session_start();

// Enforce dynamic permission check (automatically registers 'manage_feedback' if new)
require_permission($pdo, 'manage_feedback', 'Manage and moderate public feedback and submissions');

$current_user = get_current_user_data($pdo);

// Determine user timezone and dynamically compile the date/time format string using the central helper
$user_timezone = $current_user['timezone'] ?? 'UTC';
$full_format_str = get_user_datetime_format($current_user);

// Dynamic system name for mail subjects
$system_name = (function_exists('get_system_name') && isset($pdo)) ? get_system_name($pdo) : "Parish Records Directory (PRD)";

$message = $_SESSION['message'] ?? '';
$error = $_SESSION['error'] ?? '';
unset($_SESSION['message'], $_SESSION['error']);

$feedback_stmt = $pdo->query("SELECT * FROM feedback ORDER BY created_at DESC");
$feedbacks = $feedback_stmt->fetchAll();
?>

    <?php require_once '../partials/header.php'; ?>

    <h3>Feedback Submissions Dashboard</h3>
    <p>Review feedback, update status workflow, log reasons/notes, and communicate with submitters.</p>

    <?php if (!empty($message)): ?>
        <p class="alert-success" role="status"><strong><?php echo htmlspecialchars($message); ?></strong></p>
    <?php endif; ?>
    <?php if (!empty($error)): ?>
        <p class="alert-danger" role="alert"><strong><?php echo htmlspecialchars($error); ?></strong></p>
    <?php endif; ?>

    <table class="data-table" role="table">
        <thead>
            <tr>
                <th scope="col">ID / Date</th>
                <th scope="col">Submitter</th>
                <th scope="col">Message</th>
                <th scope="col">Status & Notes Management</th>
                <th scope="col">Actions</th>
            </tr>
        </thead>
        <tbody>
            <?php if (empty($feedbacks)): ?>
                <tr><td colspan="5">No feedback submissions found.</td></tr>
            <?php else: ?>
                <?php foreach ($feedbacks as $fb): ?>
                    <?php 
                        $mail_subject = urlencode("Regarding your Feedback - " . $system_name);
                    ?>
                    <tr>
                        <td>
                            <strong>#<?php echo $fb['id']; ?></strong><br>
                            <small><?php echo format_user_time($fb['created_at'], $user_timezone, $full_format_str); ?></small>
                        </td>
                        <td>
                            <strong><?php echo htmlspecialchars($fb['name']); ?></strong><br>
                            <a href="mailto:<?php echo htmlspecialchars($fb['email']); ?>?subject=<?php echo $mail_subject; ?>"><?php echo htmlspecialchars($fb['email']); ?></a>
                        </td>
                        <td><?php echo nl2br(htmlspecialchars($fb['message'])); ?></td>
                        <td>
                            <form method="POST" action="actions/save_feedback.php" class="feedback-form-group">
                                <?php echo csrf_field(); ?>
                                <input type="hidden" name="action" value="update_feedback">
                                <input type="hidden" name="feedback_id" value="<?php echo $fb['id']; ?>">
                                
                                <label class="feedback-label">Status:</label>
                                <select name="status" class="feedback-select">
                                    <option value="Pending" <?php echo ($fb['status'] === 'Pending') ? 'selected' : ''; ?>>Pending</option>
                                    <option value="Completed" <?php echo ($fb['status'] === 'Completed') ? 'selected' : ''; ?>>Completed</option>
                                    <option value="Revised from Proposal" <?php echo ($fb['status'] === 'Revised from Proposal') ? 'selected' : ''; ?>>Revised from Proposal</option>
                                    <option value="Rejected" <?php echo ($fb['status'] === 'Rejected') ? 'selected' : ''; ?>>Rejected</option>
                                </select>

                                <label class="feedback-label">Reasons / Notes:</label>
                                <textarea name="admin_notes" rows="2" placeholder="Add rationale or notes..." class="feedback-textarea"><?php echo htmlspecialchars($fb['admin_notes'] ?? ''); ?></textarea>

                                <button type="submit" class="btn feedback-btn-save">Save Status/Notes</button>
                            </form>
                        </td>
                        <td class="feedback-actions-cell">
                            <a href="mailto:<?php echo htmlspecialchars($fb['email']); ?>?subject=<?php echo $mail_subject; ?>" class="btn btn-secondary feedback-email-btn">Email User</a>
                            
                            <form method="POST" action="actions/save_feedback.php" onsubmit="return confirm('Are you sure you want to delete this feedback entry?');">
                                <?php echo csrf_field(); ?>
                                <input type="hidden" name="action" value="delete_feedback">
                                <input type="hidden" name="feedback_id" value="<?php echo $fb['id']; ?>">
                                <button type="submit" class="btn btn-danger" style="width: 100%;">Delete</button>
                            </form>
                        </td>
                    </tr>
                <?php endforeach; ?>
            <?php endif; ?>
        </tbody>
    </table>

    <?php require_once '../partials/footer.php'; ?>
