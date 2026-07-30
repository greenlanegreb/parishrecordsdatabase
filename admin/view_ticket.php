<?php
// admin/view_ticket.php - Detailed Ticket View and Threaded Dialogue
require_once '../db/db.php';
require_once '../db/auth_helpers.php';
require_once '../includes/functions.php';

if (!is_module_enabled($pdo, 'feedback')) {
    http_response_code(403);
    exit('403 Forbidden');
}

$current_user = require_admin_page($pdo, 'manage_feedback', 'View and reply to feedback tickets');
[$user_timezone, $full_format_str] = get_user_time_prefs($current_user);
$system_name = get_system_name($pdo);

$ticket_id = intval($_GET['id'] ?? 0);
$stmt = $pdo->prepare("SELECT t.*, u.username FROM feedback_tickets t LEFT JOIN users u ON t.user_id = u.id WHERE t.id = ?");
$stmt->execute([$ticket_id]);
$ticket = $stmt->fetch(PDO::FETCH_ASSOC);

if (!$ticket) {
    header('Location: feedback_dashboard.php');
    exit;
}

// Fetch dynamic custom field responses associated with this ticket
$dyn_stmt = $pdo->prepare("
    SELECT fc.column_name, ftv.value_content 
    FROM feedback_ticket_values ftv
    JOIN feedback_columns fc ON ftv.column_id = fc.id
    WHERE ftv.ticket_id = ?
    ORDER BY fc.sort_order ASC
");
$dyn_stmt->execute([$ticket_id]);
$dyn_values = $dyn_stmt->fetchAll(PDO::FETCH_ASSOC);

$replies = $pdo->prepare("SELECT r.*, u.username FROM feedback_ticket_replies r LEFT JOIN users u ON r.user_id = u.id WHERE r.ticket_id = ? ORDER BY r.created_at ASC");
$replies->execute([$ticket_id]);
$thread = $replies->fetchAll(PDO::FETCH_ASSOC);
?>
<?php require_once '../partials/header.php'; ?>

<div style="max-width: 800px; margin: 0 auto;">
    <div style="margin-bottom: 1rem;">
        <a href="feedback_dashboard.php" class="btn btn-secondary" style="text-decoration: none;">← Back to Tickets Dashboard</a>
    </div>

    <div class="search-box-container" style="margin-bottom: 2rem;">
        <h3>Ticket #<?php echo $ticket['id']; ?>: <?php echo htmlspecialchars($ticket['subject'] ?? 'Support Request'); ?></h3>
        <p><strong>Submitted by:</strong> <?php echo htmlspecialchars($ticket['name']); ?> (<a href="mailto:<?php echo htmlspecialchars($ticket['email']); ?>"><?php echo htmlspecialchars($ticket['email']); ?></a>) on <?php echo format_user_time($ticket['created_at'], $user_timezone, $full_format_str); ?></p>
        
        <!-- Custom Schema Fields Response Data -->
        <?php if (!empty($dyn_values)): ?>
            <div style="margin-top: 1rem; padding-top: 1rem; border-top: 1px solid var(--border-color);">
                <h5 style="margin-bottom: 0.5rem; color: #333;">Submitted Form Fields:</h5>
                <ul style="margin: 0; padding-left: 1.2rem;">
                    <?php foreach ($dyn_values as $dv): ?>
                        <li style="margin-bottom: 0.3rem;"><strong><?php echo htmlspecialchars($dv['column_name']); ?>:</strong> <?php echo nl2br(htmlspecialchars($dv['value_content'])); ?></li>
                    <?php endforeach; ?>
                </ul>
            </div>
        <?php endif; ?>

        <form method="POST" action="actions/save_ticket_reply.php" style="margin-top: 1rem; padding-top: 1rem; border-top: 1px solid var(--border-color);">
            <?php echo csrf_field(); ?>
            <input type="hidden" name="action" value="update_status">
            <input type="hidden" name="ticket_id" value="<?php echo $ticket['id']; ?>">
            <label><strong>Ticket Status:</strong></label>
            <select name="status" class="volunteer-input" style="padding: 0.3rem;" onchange="this.form.submit()">
                <option value="Pending" <?php echo $ticket['status'] === 'Pending' ? 'selected' : ''; ?>>Pending</option>
                <option value="In Progress" <?php echo $ticket['status'] === 'In Progress' ? 'selected' : ''; ?>>In Progress</option>
                <option value="Completed" <?php echo $ticket['status'] === 'Completed' ? 'selected' : ''; ?>>Completed</option>
                <option value="Rejected" <?php echo $ticket['status'] === 'Rejected' ? 'selected' : ''; ?>>Rejected</option>
            </select>
        </form>
    </div>

    <h4>Dialogue Thread</h4>
    <?php if (empty($thread)): ?>
        <p style="color: #666;">No replies recorded yet.</p>
    <?php else: ?>
        <?php foreach ($thread as $rep): ?>
            <div style="background: <?php echo $rep['is_admin_reply'] ? '#f8f9fa' : '#e9ecef'; ?>; border: 1px solid var(--border-color); padding: 1rem; border-radius: 6px; margin-bottom: 1rem;">
                <p style="margin: 0 0 0.5rem 0; font-size: 0.85rem; color: #555;">
                    <strong><?php echo $rep['is_admin_reply'] ? '🛡️ Admin (' . htmlspecialchars($rep['username'] ?? 'Staff') . ')' : htmlspecialchars($ticket['name']); ?></strong> — 
                    <em><?php echo format_user_time($rep['created_at'], $user_timezone, $full_format_str); ?></em>
                </p>
                <div style="white-space: pre-wrap;"><?php echo htmlspecialchars($rep['message']); ?></div>
            </div>
        <?php endforeach; ?>
    <?php endif; ?>

    <!-- Reply Box -->
    <div class="search-box-container" style="margin-top: 2rem;">
        <h4>Post Reply & Notify Submitter</h4>
        <form method="POST" action="actions/save_ticket_reply.php">
            <?php echo csrf_field(); ?>
            <input type="hidden" name="action" value="post_reply">
            <input type="hidden" name="ticket_id" value="<?php echo $ticket['id']; ?>">
            
            <textarea name="reply_message" rows="4" placeholder="Type your response here..." class="volunteer-input" style="width: 100%; padding: 0.5rem; margin-bottom: 1rem;" required></textarea>
            
            <div style="display: flex; gap: 10px; align-items: center;">
                <button type="submit" class="btn">Send Reply & Email Submitter</button>
            </div>
        </form>
    </div>
</div>

<?php require_once '../partials/footer.php'; ?>
