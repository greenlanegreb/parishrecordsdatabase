<?php
// admin/manage_feedback_emails.php - Manage feedback ticket email templates and triggers
require_once '../db/db.php';
require_once '../db/auth_helpers.php';
require_once '../includes/functions.php';

if (!is_module_enabled($pdo, 'feedback')) {
    http_response_code(403);
    exit('403 Forbidden: The Feedback module is currently disabled.');
}

$current_user = require_admin_page($pdo, 'manage_feedback', 'Manage feedback email templates and triggers');

$templates = $pdo->query("SELECT * FROM feedback_email_templates ORDER BY id ASC")->fetchAll(PDO::FETCH_ASSOC);
$columns = $pdo->query("SELECT column_name FROM feedback_columns ORDER BY sort_order ASC")->fetchAll(PDO::FETCH_COLUMN);
?>
<?php require_once '../partials/header.php'; ?>

<div class="search-box-container" style="max-width: 1100px; margin: 0 auto;">
    <h3><?php echo htmlspecialchars(__('feedback_emails.heading')); ?></h3>
    <p><?php echo htmlspecialchars(__('feedback_emails.subheading')); ?></p>

    <div style="margin-bottom: 1.5rem;">
        <a href="feedback_dashboard.php" class="btn btn-secondary" style="text-decoration: none;">← <?php echo htmlspecialchars(__('feedback_emails.back_to_dashboard')); ?></a>
    </div>

    <div style="display: flex; gap: 2rem; flex-wrap: wrap;">
        <!-- Left Column: Template Editor List -->
        <div style="flex: 2; min-width: 300px;">
            <?php foreach ($templates as $tpl): ?>
                <details class="search-box-container" style="margin-bottom: 1.5rem; background: rgba(0,0,0,0.01);">
                    <summary style="cursor: pointer; font-weight: bold; font-size: 1.1rem; color: #333; padding: 0.25rem 0;">
                        <?php echo htmlspecialchars($tpl['template_name']); ?> <code style="font-size: 0.8rem; background: #eee; padding: 2px 6px; border-radius: 4px; margin-left: 8px;"><?php echo htmlspecialchars($tpl['trigger_event']); ?></code>
                    </summary>
                    <div style="margin-top: 1rem; padding-top: 1rem; border-top: 1px solid var(--border-color);">
                        <form method="POST" action="actions/save_feedback_email_template.php">
                            <?php echo csrf_field(); ?>
                            <input type="hidden" name="template_id" value="<?php echo $tpl['id']; ?>">

                            <label for="subject_<?php echo $tpl['id']; ?>"><strong><?php echo htmlspecialchars(__('feedback_emails.email_subject')); ?></strong></label><br>
                            <input type="text" id="subject_<?php echo $tpl['id']; ?>" name="subject" value="<?php echo htmlspecialchars($tpl['subject']); ?>" required class="dashboard-input" style="width: 100%; padding: 0.4rem; margin-bottom: 1rem;"><br>

                            <label for="body_<?php echo $tpl['id']; ?>"><strong><?php echo htmlspecialchars(__('feedback_emails.email_body')); ?></strong></label><br>
                            <textarea id="body_<?php echo $tpl['id']; ?>" name="body" rows="8" required class="dashboard-input" style="width: 100%; padding: 0.4rem; margin-bottom: 1rem; font-family: monospace; resize: vertical;"><?php echo htmlspecialchars($tpl['body']); ?></textarea><br>

                            <button type="submit" class="btn"><?php echo htmlspecialchars(__('feedback_emails.save_template_btn')); ?></button>
                        </form>
                    </div>
                </details>
            <?php endforeach; ?>
        </div>

        <!-- Right Column: Available Placeholders Sidebar -->
        <div style="flex: 1; min-width: 280px; background: #f8f9fa; padding: 1.2rem; border-radius: 8px; border: 1px solid #ddd; align-self: flex-start; overflow-wrap: break-word; word-break: break-word;">
            <h4 style="margin-top: 0; color: #333;"><?php echo htmlspecialchars(__('feedback_emails.placeholders_heading')); ?></h4>
            <p style="font-size: 0.9rem; color: #666;"><?php echo htmlspecialchars(__('feedback_emails.placeholders_desc')); ?></p>
            
            <hr style="border: 0; border-top: 1px solid #ddd; margin: 0.8rem 0;">
            
            <strong style="font-size: 0.9rem; display: block; margin-bottom: 4px;"><?php echo htmlspecialchars(__('feedback_emails.fixed_tags')); ?></strong>
            <ul style="padding-left: 1.2rem; font-size: 0.85rem; margin-bottom: 1rem; font-family: monospace;">
                <li>{first_name}</li>
                <li>{surname}</li>
                <li>{email}</li>
                <li>{ticket_id}</li>
                <li>{subject}</li>
                <li>{status}</li>
                <li>{system_name}</li>
            </ul>

            <?php if (!empty($columns)): ?>
                <strong style="font-size: 0.9rem; display: block; margin-bottom: 4px;"><?php echo htmlspecialchars(__('feedback_emails.custom_tags')); ?></strong>
                <p style="font-size: 0.80rem; color: #666; margin-bottom: 4px;"><?php echo htmlspecialchars(__('feedback_emails.custom_tags_desc')); ?></p>
                <ul style="padding-left: 1.2rem; font-size: 0.85rem; font-family: monospace; color: #0066cc;">
                    <?php foreach ($columns as $col_label): 
                        $tag = '{' . strtolower(preg_replace('/[^a-zA-Z0-9_]/', '_', trim($col_label))) . '}';
                    ?>
                        <li style="margin-bottom: 6px;"><?php echo htmlspecialchars($tag); ?><br><span style="color:#666; font-size:0.75rem; font-family:sans-serif;">(<?php echo htmlspecialchars($col_label); ?>)</span></li>
                    <?php endforeach; ?>
                </ul>
            <?php endif; ?>
        </div>
    </div>
</div>

<?php require_once '../partials/footer.php'; ?>
