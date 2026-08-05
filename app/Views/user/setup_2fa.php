<?php
/**
 * MIGRATED FILE MAPPING
 * ---------------------
 * Original Old File: user/setup_2fa.php/user/actions/save_setup_2fa.php
 * Migrated Date: 2026-08-05 05:23:49
 */declare(strict_types=1);

/**
 * MIGRATED FILE MAPPING
 * ---------------------
 * Original Old File: user/setup_2fa.php
 * Migrated Date: 2026-08-04 15:00:00
 */

/** @string $error */
/** @string $qrCodeUrl */
/** @string $secret */
/** @array<int, string> $rawBackupCodes */

require_once __DIR__ . '/../partials/header.php';
?>

<div class="container d-flex justify-content-center align-items-center py-5" style="min-height: 80vh;" role="region" aria-label="<?= htmlspecialchars(__('setup_2fa.aria_region'), ENT_QUOTES, 'UTF-8') ?>">
    <div class="card shadow-sm border-0 p-4 w-100" style="max-width: 480px;">
        <h3 class="fw-bold text-dark text-center mb-1"><?= htmlspecialchars(__('setup_2fa.heading'), ENT_QUOTES, 'UTF-8') ?></h3>
        <p class="text-muted small text-center mb-4"><?= htmlspecialchars(__('setup_2fa.subheading'), ENT_QUOTES, 'UTF-8') ?></p>

        <?php if (!empty($error)): ?>
            <div class="alert alert-danger alert-dismissible fade show text-center" role="alert">
                <strong><?= htmlspecialchars($error, ENT_QUOTES, 'UTF-8') ?></strong>
                <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
            </div>
        <?php endif; ?>

        <!-- QR Code Display -->
        <div class="text-center mb-4">
            <div class="bg-white p-3 border rounded d-inline-block shadow-sm mb-3">
                <img src="<?= htmlspecialchars($qrCodeUrl, ENT_QUOTES, 'UTF-8') ?>" alt="<?= htmlspecialchars(__('setup_2fa.qr_alt'), ENT_QUOTES, 'UTF-8') ?>" width="200" height="200" class="img-fluid">
            </div>
            <p class="small text-muted mb-0">
                <?= htmlspecialchars(__('setup_2fa.manual_prompt'), ENT_QUOTES, 'UTF-8') ?><br>
                <code class="user-select-all fw-bold text-dark bg-light px-2 py-1 rounded border d-inline-block mt-1"><?= htmlspecialchars($secret, ENT_QUOTES, 'UTF-8') ?></code>
            </p>
        </div>

        <hr class="my-4">

        <!-- Backup Recovery Codes Box -->
        <div class="card bg-light border-0 p-3 mb-4">
            <h4 class="h6 fw-bold text-danger mb-2"><?= htmlspecialchars(__('setup_2fa.backup_heading'), ENT_QUOTES, 'UTF-8') ?></h4>
            <p class="small text-muted mb-2"><?= __('setup_2fa.backup_desc') ?></p>
            <ul class="list-unstyled mb-3 font-monospace small">
                <?php foreach ($rawBackupCodes as $rc): ?>
                    <li><?= htmlspecialchars($rc, ENT_QUOTES, 'UTF-8') ?></li>
                <?php endforeach; ?>
            </ul>
            <a href="/user/setup_2fa.php?action=download_codes" class="btn btn-sm btn-outline-secondary text-decoration-none d-inline-block"><?= htmlspecialchars(__('setup_2fa.download_btn'), ENT_QUOTES, 'UTF-8') ?></a>
        </div>

        <form method="POST" action="/user/actions/save_setup_2fa.php">
            <?= csrf_field() ?>
            <div class="mb-3">
                <label for="code" class="form-label small fw-bold"><?= htmlspecialchars(__('setup_2fa.code_label'), ENT_QUOTES, 'UTF-8') ?></label>
                <input type="text" id="code" name="code" pattern="[0-9]{6}" maxlength="6" required autofocus class="form-control text-center font-monospace fs-5" aria-label="<?= htmlspecialchars(__('setup_2fa.aria_code_input'), ENT_QUOTES, 'UTF-8') ?>">
            </div>
            <button type="submit" class="btn btn-primary w-100"><?= htmlspecialchars(__('setup_2fa.submit_btn'), ENT_QUOTES, 'UTF-8') ?></button>
        </form>
        
        <p class="text-center mt-4 mb-0">
            <a href="/user/profile.php" class="small text-decoration-underline text-secondary"><?= htmlspecialchars(__('setup_2fa.cancel_link'), ENT_QUOTES, 'UTF-8') ?></a>
        </p>
    </div>
</div>

<?php require_once __DIR__ . '/../partials/footer.php'; ?>
