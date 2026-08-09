<?php
/**
 * MIGRATED FILE MAPPING
 * ---------------------
 * Original Old File: admin/manage_user_emails.php
 * Migrated Date: 2026-08-04 09:45:10
 */
declare(strict_types=1);

/** @string $message */
/** @string $error */
/** @string $triggerEvent */
/** @string $templateName */
/** @string $subject */
/** @string $body */

require_once ROOT_PATH . '/partials/header.php';
$basePath = defined('BASE_PATH') ? rtrim(BASE_PATH, '/') : '';
?>

<div class="container py-4" style="max-width: 800px;">
    <h3 class="fw-bold mb-1"><?= htmlspecialchars(__('user_emails.heading') ?? 'Manage User Notification Email Templates', ENT_QUOTES, 'UTF-8') ?></h3>
    <p class="text-muted mb-4"><?= htmlspecialchars(__('user_emails.subheading') ?? 'Configure automated emails for user invitations and password resets.', ENT_QUOTES, 'UTF-8') ?></p>

    <!-- Feedback Alerts -->
    <?php if (!empty($message)): ?>
        <div class="alert alert-success alert-dismissible fade show" role="alert">
            <strong><?= htmlspecialchars($message, ENT_QUOTES, 'UTF-8') ?></strong>
            <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
        </div>
    <?php endif; ?>
    <?php if (!empty($error)): ?>
        <div class="alert alert-danger alert-dismissible fade show" role="alert">
            <strong><?= htmlspecialchars($error, ENT_QUOTES, 'UTF-8') ?></strong>
            <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
        </div>
    <?php endif; ?>

    <!-- Template Selector Form -->
    <div class="card shadow-sm border-0 mb-4 bg-light">
        <div class="card-body">
            <form method="GET" action="<?= $basePath ?>/admin/users/emails" class="mb-0">
                <label for="trigger_event" class="form-label fw-bold small"><?= htmlspecialchars(__('user_emails.select_template_label') ?? 'Select Template to Edit', ENT_QUOTES, 'UTF-8') ?></label>
                <select id="trigger_event" name="trigger_event" class="form-select" onchange="this.form.submit()">
                    <option value="user_invitation" <?= ($triggerEvent === 'user_invitation') ? 'selected' : '' ?>><?= htmlspecialchars(__('user_emails.opt_invitation') ?? 'User Invitation', ENT_QUOTES, 'UTF-8') ?></option>
                    <option value="password_reset" <?= ($triggerEvent === 'password_reset') ? 'selected' : '' ?>><?= htmlspecialchars(__('user_emails.opt_reset') ?? 'Password Reset', ENT_QUOTES, 'UTF-8') ?></option>
                </select>
            </form>
        </div>
    </div>

    <!-- Clear visual indicator banner showing active template -->
    <div class="alert alert-primary border-start border-4 border-primary shadow-sm mb-4">
        <span class="small text-uppercase fw-bold text-muted d-block tracking-wide"><?= htmlspecialchars(__('user_emails.currently_editing') ?? 'Currently Editing', ENT_QUOTES, 'UTF-8') ?></span>
        <strong class="fs-5 text-dark"><?= htmlspecialchars($templateName, ENT_QUOTES, 'UTF-8') ?></strong>
        <span class="d-block small text-muted mt-1">
            <?php if ($triggerEvent === 'user_invitation'): ?>
                <?= htmlspecialchars(__('user_emails.desc_invitation') ?? 'Dispatched automatically when an administrator creates or invites a new user account.', ENT_QUOTES, 'UTF-8') ?>
            <?php else: ?>
                <?= htmlspecialchars(__('user_emails.desc_reset') ?? 'Dispatched when a user requests a password reset.', ENT_QUOTES, 'UTF-8') ?>
            <?php endif; ?>
        </span>
    </div>

    <!-- Available Placeholders Box -->
    <div class="card shadow-sm border-0 bg-light p-3 mb-4">
        <strong class="d-block mb-3 text-dark fw-bold"><?= htmlspecialchars(__('feedback_emails.placeholders_heading') ?? 'Available Placeholders', ENT_QUOTES, 'UTF-8') ?></strong>
        <div class="d-flex flex-wrap gap-2 font-monospace">
            <span class="badge bg-white text-dark border px-3 py-2 fs-6 fw-semibold">{first_name}</span>
            <span class="badge bg-white text-dark border px-3 py-2 fs-6 fw-semibold">{surname}</span>
            <span class="badge bg-white text-dark border px-3 py-2 fs-6 fw-semibold">{username}</span>
            <span class="badge bg-white text-dark border px-3 py-2 fs-6 fw-semibold">{email}</span>
            <span class="badge bg-white text-dark border px-3 py-2 fs-6 fw-semibold">{role_name}</span>
            <span class="badge bg-white text-dark border px-3 py-2 fs-6 fw-semibold">{invite_link}</span>
            <span class="badge bg-white text-dark border px-3 py-2 fs-6 fw-semibold">{system_name}</span>
        </div>
    </div>

    <!-- Edit Form Card -->
    <div class="card shadow-sm border-0 p-4">
        <form method="POST" action="<?= $basePath ?>/admin/users/emails/store">
            <?= csrf_field() ?>
            <input type="hidden" name="trigger_event" value="<?= htmlspecialchars($triggerEvent, ENT_QUOTES, 'UTF-8') ?>">
            
            <div class="mb-3">
                <label for="subject" class="form-label fw-bold"><?= htmlspecialchars(__('feedback_emails.email_subject') ?? 'Email Subject', ENT_QUOTES, 'UTF-8') ?></label>
                <input type="text" id="subject" name="subject" value="<?= htmlspecialchars($subject, ENT_QUOTES, 'UTF-8') ?>" required class="form-control">
            </div>

            <div class="mb-4">
                <label for="body" class="form-label fw-bold"><?= htmlspecialchars(__('user_emails.email_body_label') ?? 'Email Body Content', ENT_QUOTES, 'UTF-8') ?></label>
                <textarea id="body" name="body" rows="8" required class="form-control font-monospace"><?= htmlspecialchars($body, ENT_QUOTES, 'UTF-8') ?></textarea>
            </div>

            <div class="d-flex gap-2">
                <button type="submit" class="btn btn-primary"><?= htmlspecialchars(__('feedback_emails.save_template_btn') ?? 'Save Template', ENT_QUOTES, 'UTF-8') ?></button>
                <a href="<?= $basePath ?>/admin/users" class="btn btn-outline-secondary"><?= htmlspecialchars(__('user_emails.back_to_creation') ?? 'Back to User Management', ENT_QUOTES, 'UTF-8') ?></a>
            </div>
        </form>
    </div>
</div>

<?php require_once ROOT_PATH . '/partials/footer.php'; ?>
