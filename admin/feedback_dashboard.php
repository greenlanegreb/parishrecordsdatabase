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

$tickets = $pdo->query("SELECT t.*, u.username FROM feedback_tickets t LEFT JOIN users u ON t.user_id = u.id ORDER BY t.created_at DESC")->fetchAll(PDO::FETCH_ASSOC);
?>
<?php require_once '../partials/header.php'; ?>

<div style="display: flex; justify-content: space-between; align-items: center; flex-wrap: wrap; gap: 1rem; margin-bottom: 1.5rem;">
    <div>
        <h3>Support Tickets & Feedback Dashboard</h3>
        <p style="margin: 0;">Manage public support requests, update statuses, and participate in direct dialogue.</p>
    </div>
    <div>
        <a href="manage_feedback_schema.php" class="btn btn-secondary" style="text-decoration: none;">⚙️ Manage Ticket Form Schema</a>
    </div>
</div>

<table class="data-table" role="table">
    <thead>
        <tr>
            <th scope="col">Ticket ID / Date</th>
            <th scope="col">Submitter</th>
            <th scope="col">Subject / Initial Info</th>
            <th scope="col">Status</th>
            <th scope="col">Actions</th>
        </tr>
    </thead>
    <tbody>
        <?php if (empty($tickets)): ?>
            <tr><td colspan="5" style="text-align: center; padding: 1.5rem;">No feedback tickets found.</td></tr>
        <?php else: ?>
            <?php foreach ($tickets as $t): ?>
                <tr>
                    <td>
                        <strong>#<?php echo $t['id']; ?></strong><br>
                        <small><?php echo format_user_time($t['created_at'], $user_timezone, $full_format_str); ?></small>
                    </td>
                    <td>
                        <strong><?php echo htmlspecialchars($t['name']); ?></strong><br>
                        <a href="mailto:<?php echo htmlspecialchars($t['email']); ?>"><?php echo htmlspecialchars($t['email']); ?></a>
                    </td>
                    <td><?php echo htmlspecialchars(substr($t['subject'] ?? 'General Inquiry', 0, 60)); ?>...</td>
                    <td>
                        <span style="font-weight: bold; color: <?php echo $t['status'] === 'Completed' ? 'green' : ($t['status'] === 'Rejected' ? 'red' : 'orange'); ?>;">
                            <?php echo htmlspecialchars($t['status']); ?>
                        </span>
                    </td>
                    <td>
                        <a href="view_ticket.php?id=<?php echo $t['id']; ?>" class="btn btn-secondary" style="padding: 0.3rem 0.6rem; font-size: 0.85rem; text-decoration: none; display: inline-block;">Open Ticket & Dialogue</a>
                    </td>
                </tr>
            <?php endforeach; ?>
        <?php endif; ?>
    </tbody>
</table>

<?php require_once '../partials/footer.php'; ?>
