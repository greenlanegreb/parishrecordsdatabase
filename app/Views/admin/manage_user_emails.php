<?php
/**
 * MIGRATED FILE MAPPING
 * ---------------------
 * Original Old File: admin/manage_user_emails.php/admin/actions/save_user_email_template.php
 * Migrated Date: 2026-08-05 03:23:37
 */declare(strict_types=1);

/**
 * MIGRATED FILE MAPPING
 * ---------------------
 * Original Old File: admin/manage_user_emails.php
 * Migrated Date: 2026-08-04 09:45:10
 */

/** @string $message */
/** @string $error */
/** @string $triggerEvent */
/** @string $templateName */
/** @string $subject */
/** @string $body */

require_once __DIR__ . '/../partials/header.php';
?>

<div class="container py-4" style="max-width: 800px;">
    <h3 class="fw-bold mb-1"><?= htmlspecialchars(__('user_emails.heading'), ENT_QUOTES, 'UTF-8') ?></h3>
    <p class="text-muted mb-4"><?= htmlspecialchars(__('user_emails.subheading'), ENT_QUOTES, 'UTF-8') ?></p>

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
            <form method="GET" action="/admin/users/emails" class="mb-0">
                <label for="trigger_event" class="form-label fw-bold small"><?= htmlspecialchars(__('user_emails.select_template_label'), ENT_QUOTES, 'UTF-8') ?></label>
                <select id="trigger_event" name="trigger_event" class="form-select" onchange="this.form.submit()">
                    <option value="user_invitation" <?= ($triggerEvent === 'user_invitation') ? 'selected' : '' ?>><?= htmlspecialchars(__('user_emails.opt_invitation'), ENT_QUOTES, 'UTF-8') ?></option>
                    <option value="password_reset" <?= ($triggerEvent === 'password_reset') ? 'selected' : '' ?>><?= htmlspecialchars(__('user_emails.opt_reset'), ENT_QUOTES, 'UTF-8') ?></option>
                </select>
            </form>
        </div>
    </div>

    <!-- Clear visual indicator banner showing active template -->
    <div class="alert alert-primary border-start border-4 border-primary shadow-sm mb-4">
        <span class="small text-uppercase fw-bold text-muted d-block tracking-wide"><?= htmlspecialchars(__('user_emails.currently_editing'), ENT_QUOTES, 'UTF-8') ?></span>
        <strong class="fs-5 text-dark"><?= htmlspecialchars($templateName, ENT_QUOTES, 'UTF-8') ?></strong>
        <span class="d-block small text-muted mt-1">
            <?php if ($triggerEvent === 'user_invitation'): ?>
                <?= htmlspecialchars(__('user_emails.desc_invitation'), ENT_QUOTES, 'UTF-8') ?>
            <?php else: ?>
                <?= htmlspecialchars(__('user_emails.desc_reset'), ENT_QUOTES, 'UTF-8') ?>
            <?php endif; ?>
        </span>
    </div>

    <!-- Available Placeholders Box -->
    <div class="card shadow-sm border-0 bg-light p-3 mb-4">
        <strong class="d-block mb-2 text-dark small fw-bold"><?= htmlspecialchars(__('feedback_emails.placeholders_heading'), ENT_QUOTES, 'UTF-8') ?></strong>
        <div class="d-flex flex-wrap gap-2 font-monospace small">
            <span class="badge bg-white text-dark border px-2 py-1">{first_name}</span>
            <span class="badge bg-white text-dark border px-2 py-1">{surname}</span>
            <span class="badge bg-white text-dark border px-2 py-1">{username}</span>
            <span class="badge bg-white text-dark border px-2 py-1">{email}</span>
            <span class="badge bg-white text-dark border px-2 py-1">{role_name}</span>
            <span class="badge bg-white text-dark border px-2 py-1">{invite_link}</span>
            <span class="badge bg-white text-dark border px-2 py-1">{system_name}</span>
        </div>
    </div>

    <!-- Edit Form Card -->
    <div class="card shadow-sm border-0 p-4">
        <form method="POST" action="/admin/users/emails/store">
            <?= csrf_field() ?>
            <input type="hidden" name="trigger_event" value="<?= htmlspecialchars($triggerEvent, ENT_QUOTES, 'UTF-8') ?>">
            
            <div class="mb-3">
                <label for="subject" class="form-label fw-bold"><?= htmlspecialchars(__('feedback_emails.email_subject'), ENT_QUOTES, 'UTF-8') ?></label>
                <input type="text" id="subject" name="subject" value="<?= htmlspecialchars($subject, ENT_QUOTES, 'UTF-8') ?>" required class="form-control">
            </div>

            <div class="mb-4">
                <label for="body" class="form-label fw-bold"><?= htmlspecialchars(__('user_emails.email_body_label'), ENT_QUOTES, 'UTF-8') ?></label>
                <textarea id="body" name="body" rows="8" required class="form-control font-monospace"><?= htmlspecialchars($body, ENT_QUOTES, 'UTF-8') ?></textarea>
            </div>

            <div class="d-flex gap-2">
                <button type="submit" class="btn btn-primary"><?= htmlspecialchars(__('feedback_emails.save_template_btn'), ENT_QUOTES, 'UTF-8') ?></button>
                <a href="/admin/users/create" class="btn btn-outline-secondary"><?= htmlspecialchars(__('user_emails.back_to_creation'), ENT_QUOTES, 'UTF-8') ?></a>
            </div>
        </form>
    </div>
</div>

<?php require_once __DIR__ . '/../partials/footer.php'; ?>
