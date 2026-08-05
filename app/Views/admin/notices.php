<?php
/**
 * MIGRATED FILE MAPPING
 * ---------------------
 * Original Old File: admin/notices.php
 * Migrated Date: 2026-08-05 03:41:45
 */declare(strict_types=1);

/**
 * MIGRATED FILE MAPPING
 * ---------------------
 * Original Old File: admin/notices.php
 * Migrated Date: 2026-08-04 10:15:33
 */

/** @string $message */
/** @string $error */
/** @array<int, array<string, mixed>> $notices */

require_once __DIR__ . '/../partials/header.php';
?>

<div class="container py-4" role="region" aria-label="Notices Management" style="max-width: 1100px;">
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

    <h3 class="fw-bold mb-1"><?= htmlspecialchars(__('notices.heading'), ENT_QUOTES, 'UTF-8') ?></h3>
    <p class="text-muted mb-4"><?= htmlspecialchars(__('notices.subheading'), ENT_QUOTES, 'UTF-8') ?></p>

    <!-- Create Notice Form Card -->
    <div class="card shadow-sm border-0 mb-4">
        <div class="card-body p-4">
            <h4 class="h5 fw-bold mb-3"><?= htmlspecialchars(__('notices.create_heading'), ENT_QUOTES, 'UTF-8') ?></h4>
            <form method="POST" action="/admin/notices/store">
                <?= csrf_field() ?>
                <input type="hidden" name="action" value="create">
               
                <div class="mb-3">
                    <label for="title" class="form-label fw-bold"><?= htmlspecialchars(__('notices.title_label'), ENT_QUOTES, 'UTF-8') ?></label>
                    <input type="text" id="title" name="title" required class="form-control">
                </div>

                <div class="mb-3">
                    <label for="content" class="form-label fw-bold"><?= htmlspecialchars(__('notices.content_label'), ENT_QUOTES, 'UTF-8') ?></label>
                    <textarea id="content" name="content" rows="3" required class="form-control"></textarea>
                </div>

                <div class="mb-3">
                    <label class="form-label fw-bold d-block"><?= htmlspecialchars(__('notices.target_roles_label'), ENT_QUOTES, 'UTF-8') ?></label>
                    <div class="d-flex gap-3 flex-wrap">
                        <div class="form-check">
                            <input type="checkbox" id="role_everyone" name="target_roles[]" value="everyone" checked class="form-check-input">
                            <label for="role_everyone" class="form-check-label"><?= htmlspecialchars(__('notices.role_everyone'), ENT_QUOTES, 'UTF-8') ?></label>
                        </div>
                        <div class="form-check">
                            <input type="checkbox" id="role_public" name="target_roles[]" value="public" class="form-check-input">
                            <label for="role_public" class="form-check-label"><?= htmlspecialchars(__('notices.role_public'), ENT_QUOTES, 'UTF-8') ?></label>
                        </div>
                        <div class="form-check">
                            <input type="checkbox" id="role_user" name="target_roles[]" value="user" class="form-check-input">
                            <label for="role_user" class="form-check-label"><?= htmlspecialchars(__('notices.role_users'), ENT_QUOTES, 'UTF-8') ?></label>
                        </div>
                        <div class="form-check">
                            <input type="checkbox" id="role_moderator" name="target_roles[]" value="moderator" class="form-check-input">
                            <label for="role_moderator" class="form-check-label"><?= htmlspecialchars(__('notices.role_moderators'), ENT_QUOTES, 'UTF-8') ?></label>
                        </div>
                        <div class="form-check">
                            <input type="checkbox" id="role_admin" name="target_roles[]" value="admin" class="form-check-input">
                            <label for="role_admin" class="form-check-label"><?= htmlspecialchars(__('notices.role_admins'), ENT_QUOTES, 'UTF-8') ?></label>
                        </div>
                    </div>
                </div>

                <div class="row g-3 align-items-center mb-4">
                    <div class="col-auto">
                        <div class="form-check">
                            <input type="checkbox" id="is_dismissible" name="is_dismissible" value="1" checked class="form-check-input">
                            <label for="is_dismissible" class="form-check-label"><?= htmlspecialchars(__('notices.dismissible_label'), ENT_QUOTES, 'UTF-8') ?></label>
                        </div>
                    </div>
                    <div class="col-auto">
                        <div class="d-flex align-items-center gap-2">
                            <label for="display_order" class="form-label mb-0 fw-bold small"><?= htmlspecialchars(__('notices.display_order_label'), ENT_QUOTES, 'UTF-8') ?></label>
                            <input type="number" id="display_order" name="display_order" value="0" class="form-control form-control-sm" style="width: 80px;">
                        </div>
                    </div>
                </div>

                <button type="submit" class="btn btn-primary"><?= htmlspecialchars(__('notices.publish_btn'), ENT_QUOTES, 'UTF-8') ?></button>
            </form>
        </div>
    </div>

    <!-- Existing Notices Table Card -->
    <h4 class="h5 fw-bold mb-3"><?= htmlspecialchars(__('notices.existing_heading'), ENT_QUOTES, 'UTF-8') ?></h4>
    <div class="card shadow-sm border-0">
        <div class="table-responsive">
            <table class="table table-hover align-middle mb-0" role="table">
                <thead class="table-light">
                    <tr>
                        <th scope="col" class="py-3 ps-3"><?= htmlspecialchars(__('notices.th_order'), ENT_QUOTES, 'UTF-8') ?></th>
                        <th scope="col" class="py-3"><?= htmlspecialchars(__('notices.th_title'), ENT_QUOTES, 'UTF-8') ?></th>
                        <th scope="col" class="py-3"><?= htmlspecialchars(__('notices.th_target_roles'), ENT_QUOTES, 'UTF-8') ?></th>
                        <th scope="col" class="py-3"><?= htmlspecialchars(__('notices.th_dismissible'), ENT_QUOTES, 'UTF-8') ?></th>
                        <th scope="col" class="py-3 pe-3 text-end"><?= htmlspecialchars(__('index.th_actions'), ENT_QUOTES, 'UTF-8') ?></th>
                    </tr>
                </thead>
                <tbody>
                    <?php if (empty($notices)): ?>
                        <tr>
                            <td colspan="5" class="text-center py-4 text-muted"><?= htmlspecialchars(__('notices.no_notices'), ENT_QUOTES, 'UTF-8') ?></td>
                        </tr>
                    <?php else: ?>
                        <?php foreach ($notices as $n): ?>
                            <?php 
                                $noticeId = isset($n['id']) ? (int)$n['id'] : 0;
                                $displayOrder = isset($n['display_order']) ? (int)$n['display_order'] : 0;
                                $title = isset($n['title']) && is_string($n['title']) ? $n['title'] : '';
                                $content = isset($n['content']) && is_string($n['content']) ? $n['content'] : '';
                                $targetRoles = isset($n['target_roles']) && is_string($n['target_roles']) ? $n['target_roles'] : '';
                                $isDismissible = !empty($n['is_dismissible']);
                            ?>
                            <tr>
                                <td class="ps-3"><?= $displayOrder ?></td>
                                <td>
                                    <span class="fw-bold"><?= htmlspecialchars($title, ENT_QUOTES, 'UTF-8') ?></span><br>
                                    <small class="text-muted"><?= htmlspecialchars(substr($content, 0, 80), ENT_QUOTES, 'UTF-8') ?>...</small>
                                </td>
                                <td><code class="text-secondary"><?= htmlspecialchars($targetRoles, ENT_QUOTES, 'UTF-8') ?></code></td>
                                <td>
                                    <?= $isDismissible ? htmlspecialchars(__('notices.yes'), ENT_QUOTES, 'UTF-8') : htmlspecialchars(__('notices.no_sticky'), ENT_QUOTES, 'UTF-8') ?>
                                </td>
                                <td class="pe-3 text-end text-nowrap">
                                    <form method="POST" action="/admin/notices/store" class="d-inline" onsubmit="return confirm('<?= htmlspecialchars(__('notices.delete_confirm'), ENT_QUOTES, 'UTF-8') ?>');">
                                        <?= csrf_field() ?>
                                        <input type="hidden" name="action" value="delete">
                                        <input type="hidden" name="notice_id" value="<?= $noticeId ?>">
                                        <button type="submit" class="btn btn-sm btn-danger"><?= htmlspecialchars(__('btn.delete'), ENT_QUOTES, 'UTF-8') ?></button>
                                    </form>
                                </td>
                            </tr>
                        <?php endforeach; ?>
                    <?php endif; ?>
                </tbody>
            </table>
        </div>
    </div>
</div>

<?php require_once __DIR__ . '/../partials/footer.php'; ?>
