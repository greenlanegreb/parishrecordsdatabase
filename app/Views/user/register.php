<?php
/**
 * MIGRATED FILE MAPPING
 * ---------------------
 * Original Old File: user/register.php/user/actions/save_register.php
 * Migrated Date: 2026-08-05 05:15:34
 */
declare(strict_types=1);

/**
 * MIGRATED FILE MAPPING
 * ---------------------
 * Original Old File: user/register.php
 * Migrated Date: 2026-08-04 14:00:00
 */

/** @string $message */
/** @string $error */

require_once ROOT_PATH . '/partials/header.php';
$basePath = defined('BASE_PATH') ? rtrim(BASE_PATH, '/') : '';
?>

<div class="container d-flex justify-content-center align-items-center py-5" style="min-height: 80vh;" role="region" aria-label="<?= htmlspecialchars(__('register.aria_region'), ENT_QUOTES, 'UTF-8') ?>">
    <div class="card shadow-sm border-0 p-4 w-100" style="max-width: 420px;">
        <h3 class="fw-bold text-dark mb-4"><?= htmlspecialchars(__('register.heading'), ENT_QUOTES, 'UTF-8') ?></h3>

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
        
        <form method="POST" action="<?= $basePath ?>/register">
            <?= csrf_field() ?>
            <div class="mb-3">
                <label for="username" class="form-label small fw-bold"><?= htmlspecialchars(__('register.username_label'), ENT_QUOTES, 'UTF-8') ?></label>
                <input type="text" id="username" name="username" required class="form-control">
            </div>

            <div class="mb-3">
                <label for="email" class="form-label small fw-bold"><?= htmlspecialchars(__('register.email_label', 'Email Address'), ENT_QUOTES, 'UTF-8') ?></label>
                <input type="email" id="email" name="email" required class="form-control">
            </div>

            <div class="mb-3">
                <label for="password" class="form-label small fw-bold"><?= htmlspecialchars(__('login.password_label'), ENT_QUOTES, 'UTF-8') ?></label>
                <input type="password" id="password" name="password" required class="form-control">
            </div>

            <button type="submit" class="btn btn-primary w-100 mt-2"><?= htmlspecialchars(__('register.submit_btn'), ENT_QUOTES, 'UTF-8') ?></button>
        </form>
    </div>
</div>

<?php require_once ROOT_PATH . '/partials/footer.php'; ?>
