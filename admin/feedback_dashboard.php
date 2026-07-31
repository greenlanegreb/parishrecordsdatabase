<?php
// admin/feedback_dashboard.php - Ticket Management & Dialogue Dashboard
require_once '../db/db.php';
require_once '../db/auth_helpers.php';
require_once '../includes/functions.php';

if (!is_module_enabled($pdo, 'feedback')) {
    http_response_code(403);
    exit('403 Forbidden: The Feedback Submissions module is currently disabled.');
}

$current_user = require_admin_page($pdo, 'manage_feedback', 'Manage feedback tickets and dialogue');
[$user_timezone, $full_format_str] = get_user_time_prefs($current_user);
$system_name = get_system_name($pdo);

// Handle deletion action if triggered
if ($_SERVER['REQUEST_METHOD'] === 'POST' && ($_POST['action'] ?? '') === 'delete_ticket') {
    verify_csrf_token();
    $ticket_id = intval($_POST['ticket_id'] ?? 0);
    if ($ticket_id > 0) {
        $pdo->prepare("DELETE FROM feedback_ticket_values WHERE ticket_id = ?")->execute([$ticket_id]);
        $pdo->prepare("DELETE FROM feedback_ticket_replies WHERE ticket_id = ?")->execute([$ticket_id]);
        $pdo->prepare("DELETE FROM feedback_tickets WHERE id = ?")->execute([$ticket_id]);
        $_SESSION['message'] = "Ticket #{$ticket_id} deleted successfully.";
        header('Location: feedback_dashboard.php');
        exit;
    }
}

$tickets = $pdo->query("SELECT t.*, u.username FROM feedback_tickets t LEFT JOIN users u ON t.user_id = u.id ORDER BY t.created_at DESC")->fetchAll(PDO::FETCH_ASSOC);

$message = $_SESSION['message'] ?? '';
$error = $_SESSION['error'] ?? '';
unset($_SESSION['message'], $_SESSION['error']);
?>
<?php require_once '../partials/header.php'; ?>

<div style="display: flex; justify-content: space-between; align-items: center; flex-wrap: wrap; gap: 1rem; margin-bottom: 1.5rem;">
    <div>
        <h3>Support Tickets & Feedback Dashboard</h3>
        <p style="margin: 0;">Manage public support requests, update statuses, and participate in direct dialogue.</p>
    </div>
    <div style="display: flex; gap: 0.5rem; flex-wrap: wrap;">
        <a href="manage_feedback_emails.php" class="btn btn-secondary" style="text-decoration: none;">✉️ Manage Email Templates</a>
        <a href="manage_feedback_schema.php" class="btn btn-secondary" style="text-decoration: none;">⚙️ Manage Ticket Form Schema</a>
    </div>
</div>

<?php if (!empty($message)): ?>
    <p class="alert-success" role="status"><strong><?php echo htmlspecialchars($message); ?></strong></p>
<?php endif; ?>
<?php if (!empty($error)): ?>
    <p class="alert-danger" role="alert"><strong><?php echo htmlspecialchars($error); ?></strong></p>
<?php endif; ?>

<table class="data-table" role="table">
    <thead>
        <tr>
            <th scope="col">Ticket ID / Date</th>
            <th scope="col">Submitter</th>
            <th scope="col">Subject / Initial Info</th>
            <th scope="col">Status</th>
            <th scope="col" style="text-align: right;">Actions</th>
        </tr>
    </thead>
    <tbody>
        <?php if (empty($tickets)): ?>
            <tr><td colspan="5" style="text-align: center; padding: 1.5rem;">No feedback tickets found.</td></tr>
        <?php else: ?>
            <?php foreach ($tickets as $t): ?>
                <?php 
                    $full_name = trim(($t['first_name'] ?? '') . ' ' . ($t['surname'] ?? ''));
                    if ($full_name === '') $full_name = 'Anonymous';
                ?>
                <tr>
                    <td>
                        <strong>#<?php echo $t['id']; ?></strong><br>
                        <small><?php echo format_user_time($t['created_at'], $user_timezone, $full_format_str); ?></small>
                    </td>
                    <td>
                        <strong><?php echo htmlspecialchars($full_name); ?></strong><br>
                        <a href="mailto:<?php echo htmlspecialchars($t['email']); ?>"><?php echo htmlspecialchars($t['email']); ?></a>
                    </td>
                    <td><?php echo htmlspecialchars(substr($t['subject'] ?? 'General Inquiry', 0, 60)); ?>...</td>
                    <td>
                        <span style="font-weight: bold; color: <?php echo $t['status'] === 'Completed' ? 'green' : ($t['status'] === 'Rejected' ? 'red' : 'orange'); ?>;">
                            <?php echo htmlspecialchars($t['status']); ?>
                        </span>
                    </td>
                    <td style="white-space: nowrap; text-align: right;">
                        <a href="view_ticket.php?id=<?php echo $t['id']; ?>" class="btn btn-secondary" style="padding: 0.3rem 0.6rem; font-size: 0.85rem; text-decoration: none; display: inline-block;">Open Ticket & Dialogue</a>
                        
                        <!-- Delete Ticket Form Button -->
                        <form method="POST" action="feedback_dashboard.php" style="display: inline; margin-left: 4px;" onsubmit="return confirm('Delete this support ticket and all its replies?');">
                            <?php echo csrf_field(); ?>
                            <input type="hidden" name="action" value="delete_ticket">
                            <input type="hidden" name="ticket_id" value="<?php echo $t['id']; ?>">
                            <button type="submit" class="btn btn-danger" style="padding: 0.25rem 0.5rem; font-size: 0.85rem;">Delete</button>
                        </form>
                    </td>
                </tr>
            <?php endforeach; ?>
        <?php endif; ?>
    </tbody>
</table>

<?php require_once '../partials/footer.php'; ?>
