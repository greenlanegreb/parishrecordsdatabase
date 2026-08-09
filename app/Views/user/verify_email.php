<?php
/**
 * MIGRATED FILE MAPPING
 * ---------------------
 * Original Old File: user/verify_email.php
 * Migrated Date: 2026-08-05 05:34:58
 */
declare(strict_types=1);

/**
 * MIGRATED FILE MAPPING
 * ---------------------
 * Original Old File: user/verify_email.php
 * Migrated Date: 2026-08-04 16:30:00
 */

/** @string $error */
/** @string $message */

require_once ROOT_PATH . '/partials/header.php';
$basePath = defined('BASE_PATH') ? rtrim(BASE_PATH, '/') : '';
?>

<div class="container d-flex justify-content-center align-items-center py-5" style="min-height: 80vh;" role="region" aria-label="<?= htmlspecialchars(__('verify_email.aria_region'), ENT_QUOTES, 'UTF-8') ?>">
    <div class="card shadow-sm border-0 p-4 w-100 text-center" style="max-width: 480px;">
        <h3 class="fw-bold text-dark mb-4"><?= htmlspecialchars(__('verify_email.heading'), ENT_QUOTES, 'UTF-8') ?></h3>

        <?php if (!empty($error)): ?>
            <div class="alert alert-danger alert-dismissible fade show my-3" role="alert">
                <strong><?= htmlspecialchars($error, ENT_QUOTES, 'UTF-8') ?></strong>
                <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
            </div>
        <?php endif; ?>
        
        <?php if (!empty($message)): ?>
            <div class="alert alert-success alert-dismissible fade show my-3" role="alert">
                <strong><?= htmlspecialchars($message, ENT_QUOTES, 'UTF-8') ?></strong>
                <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
            </div>
            <a href="<?= $basePath ?>/login" class="btn btn-primary w-100 text-decoration-none mt-3"><?= htmlspecialchars(__('verify_email.login_btn'), ENT_QUOTES, 'UTF-8') ?></a>
        <?php endif; ?>
    </div>
</div>

<?php require_once ROOT_PATH . '/partials/footer.php'; ?>
