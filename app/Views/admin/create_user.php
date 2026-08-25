<?php
/**
 * MIGRATED FILE MAPPING
 * ---------------------
 * Original Old File: admin/create_user.php
 * Migrated Date: 2026-08-04 09:21:39
 */

/** @var string $error */
/** @var string $message */
/** @var string $prefillEmail */
/** @var string $prefillFirst */
/** @var string $prefillSurname */
/** @var int $volunteerId */
/** @var array<int, array{id: int, role_name: string}> $rolesList */

$basePath = defined('BASE_PATH') ? rtrim(BASE_PATH, '/') : '';

require_once ROOT_PATH . '/partials/header.php';
?>

<div class="container py-5" style="max-width: 600px;">
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

    <div class="card shadow-sm border-0 p-4">
        <h3 class="fw-bold mb-1"><?= htmlspecialchars(__('create_user.heading'), ENT_QUOTES, 'UTF-8') ?></h3>
        <p class="text-muted mb-4"><?= htmlspecialchars(__('create_user.subheading'), ENT_QUOTES, 'UTF-8') ?></p>
        
        <form method="POST" action="<?= $basePath ?>/admin/users/create">
            <?= csrf_field() ?>
            
            <?php if ($volunteerId > 0): ?>
                <input type="hidden" name="volunteer_id" value="<?= $volunteerId ?>">
            <?php endif; ?>

            <!-- First Name & Surname -->
            <div class="row g-3 mb-3">
                <div class="col-md-6">
                    <label for="first_name" class="form-label fw-bold"><?= htmlspecialchars(__('create_user.first_name'), ENT_QUOTES, 'UTF-8') ?></label>
                    <input type="text" id="first_name" name="first_name" value="<?= htmlspecialchars($prefillFirst, ENT_QUOTES, 'UTF-8') ?>" class="form-control">
                </div>
                <div class="col-md-6">
                    <label for="surname" class="form-label fw-bold"><?= htmlspecialchars(__('create_user.surname'), ENT_QUOTES, 'UTF-8') ?></label>
                    <input type="text" id="surname" name="surname" value="<?= htmlspecialchars($prefillSurname, ENT_QUOTES, 'UTF-8') ?>" class="form-control">
                </div>
            </div>

            <div class="mb-3">
                <label for="username" class="form-label fw-bold"><?= htmlspecialchars(__('create_user.username_label'), ENT_QUOTES, 'UTF-8') ?></label>
                <input type="text"
                       id="username"
                       name="username"
                       value="<?= htmlspecialchars($prefillUsername ?? '', ENT_QUOTES, 'UTF-8') ?>"
                       placeholder="<?= htmlspecialchars(__('create_user.username_placeholder'), ENT_QUOTES, 'UTF-8') ?>"
                       class="form-control">
                <div class="form-text"><?= htmlspecialchars(__('create_user.username_help'), ENT_QUOTES, 'UTF-8') ?></div>
            </div>
            
            <div class="mb-3">
                <label for="email" class="form-label fw-bold"><?= htmlspecialchars(__('create_user.email_label'), ENT_QUOTES, 'UTF-8') ?> <span class="text-danger">*</span></label>
                <input type="email" id="email" name="email" value="<?= htmlspecialchars($prefillEmail, ENT_QUOTES, 'UTF-8') ?>" required class="form-control">
            </div>
            
            <div class="mb-4">
                <label for="role_id" class="form-label fw-bold"><?= htmlspecialchars(__('create_user.role_label'), ENT_QUOTES, 'UTF-8') ?></label>
                <select id="role_id" name="role_id" class="form-select">
                    <?php foreach ($rolesList as $r): ?>
                        <option value="<?= $r['id'] ?>" <?= ($r['role_name'] === 'user') ? 'selected' : '' ?>>
                            <?= htmlspecialchars(function_exists('role_display_name') ? role_display_name((string)$r['role_name']) : ucwords((string)$r['role_name']), ENT_QUOTES, 'UTF-8') ?>
                        </option>
                    <?php endforeach; ?>
                </select>
            </div>
            
            <button type="submit" class="btn btn-primary w-100 py-2"><?= htmlspecialchars(__('create_user.submit_btn'), ENT_QUOTES, 'UTF-8') ?></button>
        </form>
    </div>
</div>

<?php require_once ROOT_PATH . '/partials/footer.php'; ?>
