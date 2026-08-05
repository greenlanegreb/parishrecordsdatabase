<?php
/**
 * MIGRATED FILE MAPPING
 * ---------------------
 * Original Old File: user/forgot_password.php/user/actions/save_forgot_password.php
 * Migrated Date: 2026-08-05 04:55:57
 */declare(strict_types=1);

/**
 * MIGRATED FILE MAPPING
 * ---------------------
 * Original Old File: user/forgot_password.php
 * Migrated Date: 2026-08-04 12:15:00
 */

/** @string $error */
/** @string $message */

require_once __DIR__ . '/../partials/header.php';
?>

<div class="container d-flex justify-content-center align-items-center py-5" style="min-height: 80vh;" role="region" aria-label="<?= htmlspecialchars(__('forgot_password.aria_region'), ENT_QUOTES, 'UTF-8') ?>">
    <div class="card shadow-sm border-0 p-4 w-100" style="max-width: 420px;">
        <h3 class="fw-bold text-dark mb-1"><?= htmlspecialchars(__('forgot_password.heading'), ENT_QUOTES, 'UTF-8') ?></h3>
        <p class="text-muted small mb-4"><?= htmlspecialchars(__('forgot_password.subheading'), ENT_QUOTES, 'UTF-8') ?></p>

        <?php if (!empty($error)): ?>
            <div class="alert alert-danger alert-dismissible fade show" role="alert">
                <strong><?= htmlspecialchars($error, ENT_QUOTES, 'UTF-8') ?></strong>
                <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
            </div>
        <?php endif; ?>
        <?php if (!empty($message)): ?>
            <div class="alert alert-success alert-dismissible fade show" role="alert">
                <strong><?= htmlspecialchars($message, ENT_QUOTES, 'UTF-8') ?></strong>
                <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
            </div>
        <?php endif; ?>

        <form method="POST" action="/user/actions/save_forgot_password.php">
            <?= csrf_field() ?>
            <div class="mb-3">
                <label for="email" class="form-label small fw-bold"><?= htmlspecialchars(__('forgot_password.email_label'), ENT_QUOTES, 'UTF-8') ?></label>
                <input type="email" id="email" name="email" required class="form-control" autocomplete="email">
            </div>

            <button type="submit" class="btn btn-primary w-100 mt-2"><?= htmlspecialchars(__('forgot_password.submit_btn'), ENT_QUOTES, 'UTF-8') ?></button>
        </form>

        <p class="text-center mt-4 mb-0">
            <a href="/user/login.php" class="text-decoration-underline small text-secondary"><?= htmlspecialchars(__('forgot_password.back_login_link'), ENT_QUOTES, 'UTF-8') ?></a>
        </p>
    </div>
</div>

<?php require_once __DIR__ . '/../partials/footer.php'; ?>
