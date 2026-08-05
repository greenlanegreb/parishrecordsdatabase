<?php
/**
 * MIGRATED FILE MAPPING
 * ---------------------
 * Original Old File: user/set_password.php/user/actions/save_password.php
 * Migrated Date: 2026-08-05 05:19:08
 */declare(strict_types=1);

/**
 * MIGRATED FILE MAPPING
 * ---------------------
 * Original Old File: user/set_password.php
 * Migrated Date: 2026-08-04 14:30:00
 */

/** @string $error */
/** @string $message */
/** @string $token */
/** @array{id?: int|string, username: string} $user */

require_once __DIR__ . '/../partials/header.php';
?>

<div class="container d-flex justify-content-center align-items-center py-5" style="min-height: 80vh;" role="region" aria-label="<?= htmlspecialchars(__('set_password.aria_region'), ENT_QUOTES, 'UTF-8') ?>">
    <div class="card shadow-sm border-0 p-4 w-100" style="max-width: 450px;">
        <?php if (!empty($error)): ?>
            <div class="alert alert-danger alert-dismissible fade show" role="alert">
                <strong><?= htmlspecialchars($error, ENT_QUOTES, 'UTF-8') ?></strong>
                <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
            </div>
        <?php endif; ?>
        
        <?php if (!empty($message)): ?>
            <div class="alert alert-success alert-dismissible fade show" role="alert">
                <strong><?= htmlspecialchars($message, ENT_QUOTES, 'UTF-8') ?></strong>
            </div>
            <a href="/user/login.php" class="btn btn-primary w-100 text-decoration-none mt-2"><?= htmlspecialchars(__('set_password.proceed_login_btn'), ENT_QUOTES, 'UTF-8') ?></a>
        <?php else: ?>
            <?php 
                $username = isset($user['username']) && is_string($user['username']) ? $user['username'] : 'User';
            ?>
            <h3 class="fw-bold text-dark mb-1"><?= htmlspecialchars(sprintf(__('set_password.heading_format'), $username), ENT_QUOTES, 'UTF-8') ?></h3>
            <p class="text-muted small mb-4">
                <?= htmlspecialchars(sprintf(__('set_password.subheading_format'), $username), ENT_QUOTES, 'UTF-8') ?>
            </p>

            <form method="POST" action="/user/actions/save_password.php">
                <?= csrf_field() ?>
                <input type="hidden" name="token" value="<?= htmlspecialchars($token, ENT_QUOTES, 'UTF-8') ?>">
                
                <div class="mb-3">
                    <label for="password" class="form-label small fw-bold"><?= htmlspecialchars(__('set_password.new_password_label'), ENT_QUOTES, 'UTF-8') ?></label>
                    <input type="password" id="password" name="password" required class="form-control">
                </div>

                <div class="mb-3">
                    <label for="confirm_password" class="form-label small fw-bold"><?= htmlspecialchars(__('set_password.confirm_password_label'), ENT_QUOTES, 'UTF-8') ?></label>
                    <input type="password" id="confirm_password" name="confirm_password" required class="form-control">
                </div>

                <div class="form-check mb-3">
                    <input type="checkbox" id="show_passwords" class="form-check-input" onclick="togglePasswordVisibility()">
                    <label for="show_passwords" class="form-check-label small"><?= htmlspecialchars(__('set_password.show_password_label'), ENT_QUOTES, 'UTF-8') ?></label>
                </div>

                <button type="submit" class="btn btn-primary w-100 mt-2"><?= htmlspecialchars(__('set_password.save_password_btn'), ENT_QUOTES, 'UTF-8') ?></button>
            </form>

            <script>
            function togglePasswordVisibility() {
                const pwd = document.getElementById('password');
                const confirmPwd = document.getElementById('confirm_password');
                const type = document.getElementById('show_passwords').checked ? 'text' : 'password';
                pwd.type = type;
                confirmPwd.type = type;
            }
            </script>
        <?php endif; ?>
    </div>
</div>

<?php require_once __DIR__ . '/../partials/footer.php'; ?>
