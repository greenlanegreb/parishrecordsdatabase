<?php
// admin/manage_user_emails.php - Admin interface view to customize user notification email templates
require_once '../db/db.php';
require_once '../db/auth_helpers.php';
require_once '../includes/functions.php';

if (!is_module_enabled($pdo, 'users')) {
    http_response_code(403);
    exit('403 Forbidden: The User Management module is currently disabled.');
}

$current_user = require_admin_page($pdo, 'manage_users', 'Manage user email templates');
$message = $GLOBALS['message'] ?? '';
$error   = $GLOBALS['error']   ?? '';

// Determine active template trigger (default to 'user_invitation')
$trigger_event = $_GET['trigger_event'] ?? 'user_invitation';
if (!in_array($trigger_event, ['user_invitation', 'password_reset'])) {
    $trigger_event = 'user_invitation';
}

// Fetch template row
$stmt = $pdo->prepare("SELECT * FROM user_email_templates WHERE trigger_event = ?");
$stmt->execute([$trigger_event]);
$template = $stmt->fetch(PDO::FETCH_ASSOC);

$subject = $template['subject'] ?? '';
$body = $template['body'] ?? '';
$template_name = $template['template_name'] ?? 'User Template';
?>
<?php require_once '../partials/header.php'; ?>

<div class="search-box-container" style="max-width: 800px; margin: 2rem auto;">
    <h3><?php echo htmlspecialchars(__('user_emails.heading')); ?></h3>
    <p><?php echo htmlspecialchars(__('user_emails.subheading')); ?></p>

    <?php if (!empty($message)): ?>
        <p class="alert-success" role="status"><strong><?php echo htmlspecialchars($message); ?></strong></p>
    <?php endif; ?>
    <?php if (!empty($error)): ?>
        <p class="alert-danger" role="alert"><strong><?php echo htmlspecialchars($error); ?></strong></p>
    <?php endif; ?>

    <!-- Template Selector Form -->
    <form method="GET" action="manage_user_emails.php" style="margin-bottom: 1.5rem; background: rgba(0,0,0,0.02); padding: 1rem; border-radius: 6px; border: 1px solid var(--border-color);">
        <label for="trigger_event"><strong><?php echo htmlspecialchars(__('user_emails.select_template_label')); ?></strong></label><br>
        <div style="display: flex; gap: 0.5rem; margin-top: 0.4rem;">
            <select id="trigger_event" name="trigger_event" class="volunteer-input" style="flex: 1; padding: 0.4rem;" onchange="this.form.submit()">
                <option value="user_invitation" <?php echo ($trigger_event === 'user_invitation') ? 'selected' : ''; ?>><?php echo htmlspecialchars(__('user_emails.opt_invitation')); ?></option>
                <option value="password_reset" <?php echo ($trigger_event === 'password_reset') ? 'selected' : ''; ?>><?php echo htmlspecialchars(__('user_emails.opt_reset')); ?></option>
            </select>
        </div>
    </form>

    <!-- Clear visual indicator banner showing active template -->
    <div style="background: #eef2f7; border-left: 4px solid var(--btn-bg, #007bff); padding: 0.75rem 1rem; border-radius: 4px; margin-bottom: 1.5rem;">
        <span style="font-size: 0.85rem; color: #555; text-transform: uppercase; letter-spacing: 0.5px; display: block; font-weight: bold;"><?php echo htmlspecialchars(__('user_emails.currently_editing')); ?></span>
        <strong style="font-size: 1.1rem; color: #222;"><?php echo htmlspecialchars($template_name); ?></strong>
        <span style="display: block; font-size: 0.85rem; color: #666; margin-top: 0.2rem;">
            <?php if ($trigger_event === 'user_invitation'): ?>
                <?php echo htmlspecialchars(__('user_emails.desc_invitation')); ?>
            <?php else: ?>
                <?php echo htmlspecialchars(__('user_emails.desc_reset')); ?>
            <?php endif; ?>
        </span>
    </div>

    <div style="background: rgba(0,0,0,0.02); padding: 1rem; border-radius: 6px; margin-bottom: 1.5rem; border: 1px solid var(--border-color);">
        <strong style="display: block; margin-bottom: 0.5rem;"><?php echo htmlspecialchars(__('feedback_emails.placeholders_heading')); ?></strong>
        <code style="background: #fff; padding: 0.2rem 0.4rem; border: 1px solid #ddd; margin-right: 0.5rem;">{first_name}</code>
        <code style="background: #fff; padding: 0.2rem 0.4rem; border: 1px solid #ddd; margin-right: 0.5rem;">{surname}</code>
        <code style="background: #fff; padding: 0.2rem 0.4rem; border: 1px solid #ddd; margin-right: 0.5rem;">{username}</code>
        <code style="background: #fff; padding: 0.2rem 0.4rem; border: 1px solid #ddd; margin-right: 0.5rem;">{email}</code>
        <code style="background: #fff; padding: 0.2rem 0.4rem; border: 1px solid #ddd; margin-right: 0.5rem;">{role_name}</code>
        <code style="background: #fff; padding: 0.2rem 0.4rem; border: 1px solid #ddd; margin-right: 0.5rem;">{invite_link}</code>
        <code style="background: #fff; padding: 0.2rem 0.4rem; border: 1px solid #ddd;">{system_name}</code>
    </div>

    <form method="POST" action="actions/save_user_email_template.php">
        <?php echo csrf_field(); ?>
        <input type="hidden" name="trigger_event" value="<?php echo htmlspecialchars($trigger_event); ?>">
        
        <div style="margin-bottom: 1.25rem;">
            <label for="subject"><strong><?php echo htmlspecialchars(__('feedback_emails.email_subject')); ?></strong></label><br>
            <input type="text" id="subject" name="subject" value="<?php echo htmlspecialchars($subject); ?>" required class="volunteer-input" style="width: 100%; padding: 0.5rem;">
        </div>

        <div style="margin-bottom: 1.5rem;">
            <label for="body"><strong><?php echo htmlspecialchars(__('user_emails.email_body_label')); ?></strong></label><br>
            <textarea id="body" name="body" rows="8" required class="volunteer-input" style="width: 100%; padding: 0.5rem; resize: vertical;"><?php echo htmlspecialchars($body); ?></textarea>
        </div>

        <button type="submit" class="btn"><?php echo htmlspecialchars(__('feedback_emails.save_template_btn')); ?></button>
        <a href="create_user.php" class="btn btn-secondary" style="text-decoration: none; margin-left: 0.5rem;"><?php echo htmlspecialchars(__('user_emails.back_to_creation')); ?></a>
    </form>
</div>

<?php require_once '../partials/footer.php'; ?>
