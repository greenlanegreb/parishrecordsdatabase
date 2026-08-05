<?php
/**
 * MIGRATED FILE MAPPING
 * ---------------------
 * Original Old File: user/verify_2fa.php/user/actions/save_verify_2fa.php
 * Migrated Date: 2026-08-05 05:32:17
 */declare(strict_types=1);

/**
 * MIGRATED FILE MAPPING
 * ---------------------
 * Original Old File: user/verify_2fa.php
 * Migrated Date: 2026-08-04 16:00:00
 */

/** @string $error */

require_once __DIR__ . '/../partials/header.php';
?>

<div class="container d-flex justify-content-center align-items-center py-5" style="min-height: 80vh;" role="region" aria-label="<?= htmlspecialchars(__('verify_2fa.aria_region'), ENT_QUOTES, 'UTF-8') ?>">
    <div class="card shadow-sm border-0 p-4 w-100" style="max-width: 420px;">
        <h3 class="fw-bold text-dark mb-1"><?= htmlspecialchars(__('verify_2fa.heading'), ENT_QUOTES, 'UTF-8') ?></h3>
        <p class="text-muted small mb-4"><?= htmlspecialchars(__('verify_2fa.subheading'), ENT_QUOTES, 'UTF-8') ?></p>

        <?php if (!empty($error)): ?>
            <div class="alert alert-danger alert-dismissible fade show text-center" role="alert">
                <strong><?= htmlspecialchars($error, ENT_QUOTES, 'UTF-8') ?></strong>
                <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
            </div>
        <?php endif; ?>

        <form method="POST" action="/user/actions/save_verify_2fa.php">
            <?= csrf_field() ?>
            <div class="mb-3">
                <label for="code" class="form-label small fw-bold"><?= htmlspecialchars(__('verify_2fa.code_label'), ENT_QUOTES, 'UTF-8') ?></label>
                <input type="text" id="code" name="code" required autofocus class="form-control text-center font-monospace fs-5" aria-label="<?= htmlspecialchars(__('verify_2fa.aria_code_input'), ENT_QUOTES, 'UTF-8') ?>">
            </div>
            <button type="submit" class="btn btn-primary w-100"><?= htmlspecialchars(__('verify_2fa.submit_btn'), ENT_QUOTES, 'UTF-8') ?></button>
        </form>

        <p class="text-center mt-4 mb-0">
            <a href="/user/login.php" class="small text-decoration-underline text-secondary"><?= htmlspecialchars(__('forgot_password.back_login_link'), ENT_QUOTES, 'UTF-8') ?></a>
        </p>
    </div>
</div>

<?php require_once __DIR__ . '/../partials/footer.php'; ?>
