<?php
declare(strict_types=1);
$__rl = (defined('ROOT_PATH') ? ROOT_PATH : dirname(__DIR__, 4)) . '/includes/role_labels.php';
if (is_file($__rl)) {
    require_once $__rl;
}


/**
 * Translate with optional English fallback when the key is missing.
 */
$nt = static function (string $key, string $fallback): string {
    $t = __($key);
    return ($t !== $key && $t !== '') ? $t : $fallback;
};
/** @var array<int, array<string, mixed>> $rolesList */
$roleLabel = static function (array $r) use ($nt): string {
    $name = isset($r['role_name']) && is_string($r['role_name']) ? $r['role_name'] : '';
    $key = strtolower(str_replace([' ', '-'], '_', trim($name)));
    $map = [
        'guest' => ['role.label_guest', 'Public Visitor'],
        'user' => ['role.label_user', 'Data Entry User'],
        'admin' => ['role.label_admin', 'Administrator'],
        'moderator' => ['role.label_moderator', 'Moderator'],
    ];
    if (isset($map[$key])) {
        return $nt($map[$key][0], $map[$key][1]);
    }
    return $name !== '' ? str_replace('_', ' ', $name) : $nt('roles.unknown', 'Role');
};

$rolesList = $rolesList ?? [];

?>
<!-- TAB: Site Notices -->
<div class="tab-pane fade" id="panel-notices" role="tabpanel" aria-labelledby="tab-notices"><!-- prd-notice-audience-v2 -->

    <div class="d-flex justify-content-between align-items-center mb-3">
        <h4 class="h5 fw-bold text-dark mb-0"><?= htmlspecialchars(__('settings.notices_heading'), ENT_QUOTES, 'UTF-8') ?></h4>
    </div>

    <!-- Inline create form (collapsed by default) -->
    <div class="card shadow-sm border-0 mb-4">
        <div class="card-body">
            <details>
                <summary class="fw-bold fs-6 text-dark" style="cursor: pointer;">
                    <?= htmlspecialchars($nt('notices.add_new', 'Add new notice'), ENT_QUOTES, 'UTF-8') ?>
                </summary>
                <div class="mt-3 pt-3 border-top">
                    <form method="POST" action="<?= htmlspecialchars(($basePath ?? ''), ENT_QUOTES, 'UTF-8') ?>/admin/notices/inline-save" class="notice-audience-form">
                        <?= csrf_field() ?>
                        <input type="hidden" name="update_action" value="create">

                        <div class="mb-2">
                            <label class="form-label small fw-bold" for="notice_title_new"><?= htmlspecialchars($nt('notices.title_label', 'Title'), ENT_QUOTES, 'UTF-8') ?></label>
                            <input type="text" id="notice_title_new" name="title" class="form-control form-control-sm" required>
                        </div>

                        <div class="mb-2">
                            <label class="form-label small fw-bold" for="notice_content_new"><?= htmlspecialchars($nt('notices.content_label', 'Content'), ENT_QUOTES, 'UTF-8') ?></label>
                            <textarea id="notice_content_new" name="content" rows="3" class="form-control form-control-sm" required></textarea>
                        </div>

                        <div class="row g-2 mb-2">
                            <div class="col-6">
                                <label class="form-label small fw-bold" for="notice_order_new"><?= htmlspecialchars($nt('notices.display_order', 'Display order'), ENT_QUOTES, 'UTF-8') ?></label>
                                <input type="number" id="notice_order_new" name="display_order" value="0" class="form-control form-control-sm">
                            </div>
                            <div class="col-6 d-flex align-items-end gap-3 pb-1">
                                <div class="form-check mb-0">
                                    <input type="checkbox" name="is_active" id="new_is_active" value="1" class="form-check-input" checked>
                                    <label class="form-check-label small" for="new_is_active"><?= htmlspecialchars($nt('notices.active', 'Active'), ENT_QUOTES, 'UTF-8') ?></label>
                                </div>
                                <div class="form-check mb-0">
                                    <input type="checkbox" name="is_dismissible" id="new_is_dismissible" value="1" class="form-check-input" checked>
                                    <label class="form-check-label small" for="new_is_dismissible"><?= htmlspecialchars($nt('notices.dismissible', 'Dismissible'), ENT_QUOTES, 'UTF-8') ?></label>
                                </div>
                            </div>
                        </div>

                        <div class="mb-3 notice-audience-group">
                            <span class="form-label small fw-bold d-block"><?= htmlspecialchars($nt('notices.audience', 'Audience'), ENT_QUOTES, 'UTF-8') ?></span>
                            <div class="form-check form-check-inline">
                                <input type="checkbox" name="target_roles[]" value="everyone" class="form-check-input notice-everyone" id="new_role_everyone" checked>
                                <label class="form-check-label small" for="new_role_everyone"><?= htmlspecialchars($nt('notices.everyone', 'Everyone'), ENT_QUOTES, 'UTF-8') ?></label>
                            </div>
                            <?php foreach ($rolesList as $r):
                                $roleName = isset($r['role_name']) && is_string($r['role_name']) ? $r['role_name'] : '';
                                if ($roleName === '') {
                                    continue;
                                }
                                $roleId = 'new_role_' . preg_replace('/[^a-z0-9_]/i', '_', $roleName);
                            ?>
                                <div class="form-check form-check-inline">
                                    <input type="checkbox"
                                           name="target_roles[]"
                                           id="<?= htmlspecialchars($roleId, ENT_QUOTES, 'UTF-8') ?>"
                                           value="<?= htmlspecialchars($roleName, ENT_QUOTES, 'UTF-8') ?>"
                                           class="form-check-input notice-role">
                                    <label class="form-check-label small" for="<?= htmlspecialchars($roleId, ENT_QUOTES, 'UTF-8') ?>">
                                        <?= htmlspecialchars($roleLabel($r), ENT_QUOTES, 'UTF-8') ?>
                                    </label>
                                </div>
                            <?php endforeach; ?>
                        </div>

                        <button type="submit" class="btn btn-sm btn-primary"><?= htmlspecialchars($nt('notices.create_notice_btn', 'Create notice'), ENT_QUOTES, 'UTF-8') ?></button>
                    </form>
                </div>
            </details>
        </div>
    </div>

    <!-- Existing notices -->
    <?php if (empty($notices)): ?>
        <div class="card shadow-sm border-0 text-center py-5 text-muted bg-light">
            <?= htmlspecialchars(__('settings.no_notices'), ENT_QUOTES, 'UTF-8') ?>
        </div>
    <?php else: ?>
        <div class="d-flex flex-column gap-3">
            <?php foreach ($notices as $n): ?>
                <?php
                    $noticeId = isset($n['id']) ? (int) $n['id'] : 0;
                    $title = isset($n['title']) && is_string($n['title']) ? $n['title'] : '';
                    $content = isset($n['content']) && is_string($n['content']) ? $n['content'] : '';
                    $isActive = !empty($n['is_active']);
                    $isDismissible = !empty($n['is_dismissible']);
                    $displayOrder = isset($n['display_order']) ? (int) $n['display_order'] : 0;
                    $targetRoles = isset($n['target_roles']) && is_string($n['target_roles']) ? $n['target_roles'] : 'everyone';
                    $rolesSelected = array_map('trim', explode(',', $targetRoles));
                    $everyoneOn = in_array('everyone', $rolesSelected, true);
                ?>
                <div class="card shadow-sm border-0">
                    <div class="card-body">
                        <details>
                            <summary class="fw-bold fs-6 text-dark d-flex justify-content-between align-items-center" style="cursor: pointer;">
                                <span><?= htmlspecialchars($title, ENT_QUOTES, 'UTF-8') ?></span>
                                <span class="badge <?= $isActive ? 'bg-success' : 'bg-danger' ?>">
                                    <?= $isActive
                                        ? htmlspecialchars(__('settings.status_active'), ENT_QUOTES, 'UTF-8')
                                        : htmlspecialchars(__('settings.status_inactive'), ENT_QUOTES, 'UTF-8') ?>
                                </span>
                            </summary>
                            <div class="mt-3 pt-3 border-top">
                                <form method="POST" action="<?= htmlspecialchars(($basePath ?? ''), ENT_QUOTES, 'UTF-8') ?>/admin/notices/inline-save" class="notice-audience-form">
                                    <?= csrf_field() ?>
                                    <input type="hidden" name="notice_id" value="<?= $noticeId ?>">

                                    <div class="mb-2">
                                        <label class="form-label small fw-bold" for="notice_title_<?= $noticeId ?>"><?= htmlspecialchars($nt('notices.title_label', 'Title'), ENT_QUOTES, 'UTF-8') ?></label>
                                        <input type="text" id="notice_title_<?= $noticeId ?>" name="title" value="<?= htmlspecialchars($title, ENT_QUOTES, 'UTF-8') ?>" class="form-control form-control-sm" required>
                                    </div>

                                    <div class="mb-2">
                                        <label class="form-label small fw-bold" for="notice_content_<?= $noticeId ?>"><?= htmlspecialchars($nt('notices.content_label', 'Content'), ENT_QUOTES, 'UTF-8') ?></label>
                                        <textarea id="notice_content_<?= $noticeId ?>" name="content" rows="3" class="form-control form-control-sm" required><?= htmlspecialchars($content, ENT_QUOTES, 'UTF-8') ?></textarea>
                                    </div>

                                    <div class="row g-2 mb-2">
                                        <div class="col-6">
                                            <label class="form-label small fw-bold" for="notice_order_<?= $noticeId ?>"><?= htmlspecialchars($nt('notices.display_order', 'Display order'), ENT_QUOTES, 'UTF-8') ?></label>
                                            <input type="number" id="notice_order_<?= $noticeId ?>" name="display_order" value="<?= $displayOrder ?>" class="form-control form-control-sm">
                                        </div>
                                        <div class="col-6 d-flex align-items-end gap-3 pb-1">
                                            <div class="form-check mb-0">
                                                <input type="checkbox" name="is_active" id="active_<?= $noticeId ?>" value="1" class="form-check-input" <?= $isActive ? 'checked' : '' ?>>
                                                <label class="form-check-label small" for="active_<?= $noticeId ?>"><?= htmlspecialchars($nt('notices.active', 'Active'), ENT_QUOTES, 'UTF-8') ?></label>
                                            </div>
                                            <div class="form-check mb-0">
                                                <input type="checkbox" name="is_dismissible" id="dismiss_<?= $noticeId ?>" value="1" class="form-check-input" <?= $isDismissible ? 'checked' : '' ?>>
                                                <label class="form-check-label small" for="dismiss_<?= $noticeId ?>"><?= htmlspecialchars($nt('notices.dismissible', 'Dismissible'), ENT_QUOTES, 'UTF-8') ?></label>
                                            </div>
                                        </div>
                                    </div>

                                    <div class="mb-3 notice-audience-group">
                                        <span class="form-label small fw-bold d-block"><?= htmlspecialchars($nt('notices.audience', 'Audience'), ENT_QUOTES, 'UTF-8') ?></span>
                                        <div class="form-check form-check-inline">
                                            <input type="checkbox"
                                                   name="target_roles[]"
                                                   value="everyone"
                                                   class="form-check-input notice-everyone"
                                                   id="role_<?= $noticeId ?>_everyone"
                                                   <?= $everyoneOn ? 'checked' : '' ?>>
                                            <label class="form-check-label small" for="role_<?= $noticeId ?>_everyone">
                                                <?= htmlspecialchars($nt('notices.everyone', 'Everyone'), ENT_QUOTES, 'UTF-8') ?>
                                            </label>
                                        </div>
                                        <?php foreach ($rolesList as $r):
                                            $roleName = isset($r['role_name']) && is_string($r['role_name']) ? $r['role_name'] : '';
                                            if ($roleName === '') {
                                                continue;
                                            }
                                            $inputId = 'role_' . $noticeId . '_' . preg_replace('/[^a-z0-9_]/i', '_', $roleName);
                                            $checked = $everyoneOn || in_array($roleName, $rolesSelected, true);
                                        ?>
                                            <div class="form-check form-check-inline">
                                                <input type="checkbox"
                                                       name="target_roles[]"
                                                       id="<?= htmlspecialchars($inputId, ENT_QUOTES, 'UTF-8') ?>"
                                                       value="<?= htmlspecialchars($roleName, ENT_QUOTES, 'UTF-8') ?>"
                                                       class="form-check-input notice-role"
                                                       <?= $checked ? 'checked' : '' ?>
                                                       <?= $everyoneOn ? 'disabled' : '' ?>>
                                                <label class="form-check-label small" for="<?= htmlspecialchars($inputId, ENT_QUOTES, 'UTF-8') ?>">
                                                    <?= htmlspecialchars($roleLabel($r), ENT_QUOTES, 'UTF-8') ?>
                                                </label>
                                            </div>
                                        <?php endforeach; ?>
                                    </div>

                                    <div class="d-flex flex-wrap gap-2">
                                        <button type="submit" name="update_action" value="save" class="btn btn-sm btn-primary"><?= htmlspecialchars($nt('notices.save_btn', 'Save'), ENT_QUOTES, 'UTF-8') ?></button>
                                        <button type="submit" name="update_action" value="delete" class="btn btn-sm btn-outline-danger"
                                            onclick="return confirm('<?= htmlspecialchars($nt('notices.delete_confirm', 'Delete this notice?'), ENT_QUOTES, 'UTF-8') ?>');"><?= htmlspecialchars($nt('notices.delete_btn', 'Delete'), ENT_QUOTES, 'UTF-8') ?></button>
                                    </div>
                                </form>
                            </div>
                        </details>
                    </div>
                </div>
            <?php endforeach; ?>
        </div>
    <?php endif; ?>
</div>

<script>
(function () {
    function syncAudience(group) {
        const everyone = group.querySelector('.notice-everyone');
        const roles = group.querySelectorAll('.notice-role');
        if (!everyone) return;

        if (everyone.checked) {
            roles.forEach(function (cb) {
                cb.checked = true;
                cb.disabled = true;
            });
        } else {
            roles.forEach(function (cb) {
                cb.disabled = false;
            });
        }
    }

    function initAudienceGroups(root) {
        (root || document).querySelectorAll('.notice-audience-group').forEach(function (group) {
            syncAudience(group);
            const everyone = group.querySelector('.notice-everyone');
            if (everyone && !everyone.dataset.bound) {
                everyone.dataset.bound = '1';
                everyone.addEventListener('change', function () {
                    syncAudience(group);
                });
            }
        });
    }

    document.querySelectorAll('.notice-audience-form').forEach(function (form) {
        form.addEventListener('submit', function () {
            form.querySelectorAll('.notice-role:disabled').forEach(function (cb) {
                cb.disabled = false;
            });
        });
    });

    if (document.readyState === 'loading') {
        document.addEventListener('DOMContentLoaded', function () { initAudienceGroups(); });
    } else {
        initAudienceGroups();
    }
})();
</script>
