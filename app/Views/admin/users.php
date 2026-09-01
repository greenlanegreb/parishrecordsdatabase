<?php
/**
 * MIGRATED FILE MAPPING
 * ---------------------
 * Original Old File: admin/users.php
 * Migrated Date: 2026-08-04 10:40:00
 */
declare(strict_types=1);
/** Translate with fallback when lang key is missing. @return string */
$__t = static function (string $key, string $fallback = ''): string {
    $v = function_exists('__') ? (string) __($key) : $key;
    if ($v !== $key && $v !== '') {
        return $v;
    }
    return $fallback !== '' ? $fallback : $key;
};

/** @string $message */
/** @string $error */
/** @string $prefillEmail */
/** @string $prefillFirst */
/** @string $prefillSurname */
/** @int $volunteerId */
/** @int $firstAdminId */
/** @array<int, array<string, mixed>> $users */
/** @array<int, array<string, mixed>> $rolesList */
/** @array{id: int, username: string} $currentUser */
require_once ROOT_PATH . '/partials/header.php';
$basePath = defined('BASE_PATH') ? rtrim(BASE_PATH, '/') : '';
if ($basePath === '') {
    $scriptName = $_SERVER['SCRIPT_NAME'] ?? '';
    $basePath = rtrim(str_replace('/index.php', '', $scriptName), '/');
    if (($pos = strpos($basePath, '/app/Views')) !== false) {
        $basePath = substr($basePath, 0, $pos);
    }
    if (strpos($basePath, '/projects/prd') !== false) {
        $basePath = '/projects/prd';
    }
}
?>
<div class="container py-4" role="region" aria-label="Admin User Management" style="max-width: 1300px;">
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

    <div class="d-flex justify-content-between align-items-center mb-4 flex-wrap gap-3">
        <div>
            <h3 class="fw-bold mb-1"><?= htmlspecialchars($__t('admin_users.heading', 'User Management'), ENT_QUOTES, 'UTF-8') ?></h3>
            <p class="text-muted mb-0"><?= htmlspecialchars($__t('admin_users.subheading', 'Manage registered users, roles, email verification, and security settings.'), ENT_QUOTES, 'UTF-8') ?></p>
        </div>
        <div>
            <a href="<?= $basePath ?>/admin/users/emails" class="btn btn-outline-secondary">✉️ <?= htmlspecialchars($__t('admin_users.manage_templates_btn', 'Manage Email Templates'), ENT_QUOTES, 'UTF-8') ?></a>
        </div>
    </div>

    <!-- Integrated Inline Invite User Accordion Card -->
    <div class="card shadow-sm border-0 mb-4 bg-light">
        <div class="card-body">
            <details id="invite-user-section" <?= ($volunteerId > 0) ? 'open' : '' ?>>
                <summary class="fw-bold text-dark fs-6" style="cursor: pointer; outline: none;">
                    ➕ <?= htmlspecialchars($__t('create_user.heading', 'Invite New User'), ENT_QUOTES, 'UTF-8') ?>
                </summary>
                <div class="mt-3 pt-3 border-top">
                    <p class="text-muted small mb-3"><?= htmlspecialchars($__t('create_user.subheading', 'Send an account invitation email to a new user.'), ENT_QUOTES, 'UTF-8') ?></p>
                    <form method="POST" action="<?= $basePath ?>/admin/users/create" style="max-width: 650px;">
                        <?= csrf_field() ?>
                        <?php if ($volunteerId > 0): ?>
                            <input type="hidden" name="volunteer_id" value="<?= $volunteerId ?>">
                        <?php endif; ?>
                        <div class="row g-2 mb-3">
                            <div class="col-md-6">
                                <label for="first_name" class="form-label small fw-bold"><?= htmlspecialchars($__t('create_user.first_name', 'First Name'), ENT_QUOTES, 'UTF-8') ?></label>
                                <input type="text" id="first_name" name="first_name" value="<?= htmlspecialchars($prefillFirst, ENT_QUOTES, 'UTF-8') ?>" class="form-control form-control-sm">
                            </div>
                            <div class="col-md-6">
                                <label for="surname" class="form-label small fw-bold"><?= htmlspecialchars($__t('create_user.surname', 'Surname'), ENT_QUOTES, 'UTF-8') ?></label>
                                <input type="text" id="surname" name="surname" value="<?= htmlspecialchars($prefillSurname, ENT_QUOTES, 'UTF-8') ?>" class="form-control form-control-sm">
                            </div>
                        </div>
                        <div class="mb-3">
                            <label for="username" class="form-label small fw-bold"><?= htmlspecialchars($__t('create_user.username_label', 'Username'), ENT_QUOTES, 'UTF-8') ?></label>
                            <input type="text" id="username" name="username" placeholder="<?= htmlspecialchars($__t('create_user.username_placeholder', 'Enter username'), ENT_QUOTES, 'UTF-8') ?>" class="form-control form-control-sm">
                            <div class="form-text"><?= htmlspecialchars($__t('create_user.username_help', 'Optional. If left blank, one will be generated from email.'), ENT_QUOTES, 'UTF-8') ?></div>
                        </div>
                        <div class="mb-3">
                            <label for="email" class="form-label small fw-bold"><?= htmlspecialchars($__t('create_user.email_label', 'Email Address'), ENT_QUOTES, 'UTF-8') ?> <span class="text-danger">*</span></label>
                            <input type="email" id="email" name="email" value="<?= htmlspecialchars($prefillEmail, ENT_QUOTES, 'UTF-8') ?>" required class="form-control form-control-sm">
                        </div>
                        <div class="mb-4">
                            <label for="role_id" class="form-label small fw-bold"><?= htmlspecialchars($__t('create_user.role_label', 'Role'), ENT_QUOTES, 'UTF-8') ?></label>
                            <select id="role_id" name="role_id" class="form-select form-select-sm" style="min-width: 14rem;">
                                <?php foreach ($rolesList as $r): ?>
                                    <?php
                                        $rId = isset($r['id']) ? (int)$r['id'] : 0;
                                        $rName = isset($r['role_name']) && is_string($r['role_name']) ? $r['role_name'] : '';
                                        if (strtolower($rName) === 'guest') {
                                            continue;
                                        }
                                    ?>
                                    <option value="<?= $rId ?>" <?= ($rName === 'user') ? 'selected' : '' ?>>
                                        <?= htmlspecialchars(function_exists('role_display_name') ? role_display_name($rName) : ucwords($rName), ENT_QUOTES, 'UTF-8') ?>
                                    </option>
                                <?php endforeach; ?>
                            </select>
                        </div>
                        <button type="submit" class="btn btn-sm btn-primary"><?= htmlspecialchars($__t('create_user.submit_btn', 'Send Invitation'), ENT_QUOTES, 'UTF-8') ?></button>
                    </form>
                </div>
            </details>
        </div>
    </div>

    <div class="card shadow-sm border-0 mb-3">
        <div class="card-body py-3">
            <label for="user-search" class="form-label small fw-bold mb-1"><?= htmlspecialchars($__t('admin_users.find_user', 'Find user'), ENT_QUOTES, 'UTF-8') ?></label>
            <input type="search"
                   id="user-search"
                   class="form-control"
                   placeholder="<?= htmlspecialchars($__t('admin_users.search_placeholder', 'Username, email, or role…'), ENT_QUOTES, 'UTF-8') ?>"
                   autocomplete="off">
            <div class="form-text"><?= htmlspecialchars($__t('admin_users.search_help', 'Filters the list as you type. Clear the box to show everyone again.'), ENT_QUOTES, 'UTF-8') ?></div>
            <div id="user-search-empty" class="alert alert-warning small mt-2 mb-0 d-none"><?= htmlspecialchars($__t('admin_users.no_search_match', 'No users match that search.'), ENT_QUOTES, 'UTF-8') ?></div>
        </div>
    </div>

    <!-- Users Data Table Card -->
    <div class="card shadow-sm border-0 mb-4">
        <div>
            <table class="table table-striped table-hover align-middle mb-0 w-100" role="table">
                <thead class="table-light">
                    <tr>
                        <th scope="col" class="py-3 ps-3"><?= htmlspecialchars($__t('admin_users.th_username', 'Username'), ENT_QUOTES, 'UTF-8') ?></th>
                        <th scope="col" class="py-3"><?= htmlspecialchars($__t('admin_users.th_email_override', 'Email'), ENT_QUOTES, 'UTF-8') ?></th>
                        <th scope="col" class="py-3"><?= htmlspecialchars($__t('admin_users.th_role_assignment', 'Role'), ENT_QUOTES, 'UTF-8') ?></th>
                        <th scope="col" class="py-3"><?= htmlspecialchars($__t('admin_users.th_score', 'Points'), ENT_QUOTES, 'UTF-8') ?></th>
                        <th scope="col" class="py-3"><?= htmlspecialchars($__t('admin_users.th_status', 'Status'), ENT_QUOTES, 'UTF-8') ?></th>
                        <th scope="col" class="py-3"><?= htmlspecialchars($__t('admin_users.th_2fa', '2FA'), ENT_QUOTES, 'UTF-8') ?></th>
                        <th scope="col" class="py-3 pe-3"><?= htmlspecialchars($__t('admin_users.th_actions', 'Actions'), ENT_QUOTES, 'UTF-8') ?></th>
                    </tr>
                </thead>
                <tbody>
                    <?php if (empty($users)): ?>
                        <tr>
                            <td colspan="7" class="text-center py-4 text-muted"><?= htmlspecialchars($__t('admin_users.no_users', 'No users found.'), ENT_QUOTES, 'UTF-8') ?></td>
                        </tr>
                    <?php else: ?>
                        <?php foreach ($users as $u): ?>
                            <?php
                                $uId = isset($u['id']) ? (int)$u['id'] : 0;
                                $uUsername = isset($u['username']) && is_string($u['username']) ? $u['username'] : '';
                                $uEmail = isset($u['email']) && is_string($u['email']) ? $u['email'] : '';
                                $uPoints = isset($u['points']) ? (int)$u['points'] : 0;
                                $uVerified = !empty($u['email_verified']) || !empty($u['has_login_password']);
                                $u2fa = !empty($u['two_fa_enabled']);
                                $uActive = !empty($u['is_active']);
                                $uRoleId = isset($u['role_id']) ? (int)$u['role_id'] : 0;
                                $uRoleName = isset($u['role_name']) && is_string($u['role_name']) ? $u['role_name'] : 'User';
                                $isFirstAdmin = ($uId === $firstAdminId);
                            ?>
                            <tr data-search="<?= htmlspecialchars(
                                strtolower($uUsername . ' ' . $uEmail . ' ' . $uRoleName . ' ' . $uId),
                                ENT_QUOTES,
                                'UTF-8'
                            ) ?>">
                                <td class="ps-3 fw-bold text-dark"><?= htmlspecialchars($uUsername, ENT_QUOTES, 'UTF-8') ?></td>
                                <td>
                                    <form method="POST" action="<?= $basePath ?>/admin/users" class="d-flex gap-1 align-items-center mb-1">
                                        <?= csrf_field() ?>
                                        <input type="hidden" name="action" value="update_email">
                                        <input type="hidden" name="target_user_id" value="<?= $uId ?>">
                                        <label class="visually-hidden" for="new_email_<?= (int)$uId ?>"><?= htmlspecialchars((__('admin_users.email_for') !== 'admin_users.email_for' ? __('admin_users.email_for') : 'Email for') . ' ' . $uUsername, ENT_QUOTES, 'UTF-8') ?></label>
                                        <input type="email" id="new_email_<?= (int)$uId ?>" name="new_email" value="<?= htmlspecialchars($uEmail, ENT_QUOTES, 'UTF-8') ?>" class="form-control form-control-sm" style="min-width: 16rem; width: 100%;" required aria-label="<?= htmlspecialchars((__('admin_users.email_for') !== 'admin_users.email_for' ? __('admin_users.email_for') : 'Email for') . ' ' . $uUsername, ENT_QUOTES, 'UTF-8') ?>">
                                        <button type="submit" class="btn btn-sm btn-outline-dark py-0 px-2" style="font-size: 0.75rem;" title="<?= htmlspecialchars($__t('admin_users.save_email_title', 'Save Email'), ENT_QUOTES, 'UTF-8') ?>"><?= htmlspecialchars($__t('btn.save', 'Save'), ENT_QUOTES, 'UTF-8') ?></button>
                                        <?php if ($uVerified): ?>
                                            <span class="badge bg-success"><?= htmlspecialchars($__t('admin_users.verified_badge', 'Verified'), ENT_QUOTES, 'UTF-8') ?></span>
                                        <?php else: ?>
                                            <span class="badge bg-light text-dark border"><?= htmlspecialchars($__t('admin_users.not_verified_badge', 'Not Verified'), ENT_QUOTES, 'UTF-8') ?></span>
                                        <?php endif; ?>
                                    </form>
                                </td>
                                <td>
                                    <?php if ($isFirstAdmin): ?>
                                        <div class="d-flex gap-1 align-items-center">
                                            <select class="form-select form-select-sm" style="min-width: 12rem;" disabled aria-label="<?= htmlspecialchars($__t('admin_users.protected_admin', 'Protected Admin'), ENT_QUOTES, 'UTF-8') ?>">
                                                <option selected><?= htmlspecialchars(function_exists('role_display_name') ? role_display_name($uRoleName) : $uRoleName, ENT_QUOTES, 'UTF-8') ?></option>
                                            </select>
                                            <button type="button" class="btn btn-sm btn-outline-secondary py-0 px-2" style="font-size: 0.75rem;" disabled><?= htmlspecialchars($__t('admin_users.update_btn', 'Update'), ENT_QUOTES, 'UTF-8') ?></button>
                                        </div>
                                    <?php else: ?>
                                        <form method="POST" action="<?= $basePath ?>/admin/users" class="d-flex gap-1 align-items-center">
                                            <?= csrf_field() ?>
                                            <input type="hidden" name="action" value="change_role">
                                            <input type="hidden" name="target_user_id" value="<?= $uId ?>">
                                            <select name="new_role_id" class="form-select form-select-sm" style="min-width: 12rem;" aria-label="Role for <?= htmlspecialchars($uUsername, ENT_QUOTES, 'UTF-8') ?>">
                                                <?php foreach ($rolesList as $r): ?>
                                                    <?php
                                                        $rId = isset($r['id']) ? (int)$r['id'] : 0;
                                                        $rName = isset($r['role_name']) && is_string($r['role_name']) ? $r['role_name'] : '';
                                                        if (strtolower($rName) === 'guest') {
                                                            continue;
                                                        }
                                                    ?>
                                                    <option value="<?= $rId ?>" <?= ($uRoleId === $rId) ? 'selected' : '' ?>>
                                                        <?= htmlspecialchars(function_exists('role_display_name') ? role_display_name($rName) : ucwords($rName), ENT_QUOTES, 'UTF-8') ?>
                                                    </option>
                                                <?php endforeach; ?>
                                            </select>
                                            <button type="submit" class="btn btn-sm btn-outline-secondary py-0 px-2" style="font-size: 0.75rem;"><?= htmlspecialchars($__t('admin_users.update_btn', 'Update'), ENT_QUOTES, 'UTF-8') ?></button>
                                        </form>
                                    <?php endif; ?>
                                </td>
                                <td><span class="badge bg-light text-dark border fw-bold">⭐ <?= $uPoints ?></span></td>
                                <td>
                                    <?php if ($uActive): ?>
                                        <span class="badge bg-success"><?= htmlspecialchars($__t('admin_users.status_active', 'Active'), ENT_QUOTES, 'UTF-8') ?></span>
                                    <?php else: ?>
                                        <span class="badge bg-warning text-dark"><?= htmlspecialchars($__t('admin_users.status_suspended', 'Suspended'), ENT_QUOTES, 'UTF-8') ?></span>
                                    <?php endif; ?>
                                </td>
                                <td>
                                    <?php if ($u2fa): ?>
                                        <form method="POST" action="<?= $basePath ?>/admin/users" class="d-flex flex-column gap-1" onsubmit="return confirm('<?= htmlspecialchars($__t('admin_users.reset_2fa_confirm', 'Turn off two-factor authentication for this user?'), ENT_QUOTES, 'UTF-8') ?>');">
                                            <?= csrf_field() ?>
                                            <input type="hidden" name="action" value="reset_2fa">
                                            <input type="hidden" name="target_user_id" value="<?= $uId ?>">
                                            <button type="submit" class="btn btn-sm btn-outline-dark" aria-label="<?= htmlspecialchars($__t('admin_users.reset_2fa_btn', 'Turn Off 2FA') . ' — ' . $uUsername, ENT_QUOTES, 'UTF-8') ?>"><?= htmlspecialchars($__t('admin_users.reset_2fa_btn', 'Turn Off 2FA'), ENT_QUOTES, 'UTF-8') ?></button>
                                        </form>
                                    <?php else: ?>
                                        <span class="text-muted"><?= htmlspecialchars($__t('admin_users.disabled', 'Disabled'), ENT_QUOTES, 'UTF-8') ?></span>
                                    <?php endif; ?>
                                </td>
                                <td class="pe-3">
                                    <div class="dropdown">
                                        <button class="btn btn-sm btn-outline-dark dropdown-toggle" type="button" data-bs-toggle="dropdown" data-bs-offset="0,4" aria-expanded="false">
                                            <?= htmlspecialchars($__t('admin_users.th_actions', 'Actions'), ENT_QUOTES, 'UTF-8') ?>
                                        </button>
                                        <ul class="dropdown-menu dropdown-menu-end shadow-sm">
                                            <li>
                                                <form method="POST" action="<?= $basePath ?>/admin/users" class="px-3 py-2">
                                                    <?= csrf_field() ?>
                                                    <input type="hidden" name="action" value="override_points">
                                                    <input type="hidden" name="target_user_id" value="<?= $uId ?>">
                                                    <label class="form-label small mb-1" for="new_points_<?= (int)$uId ?>"><?= htmlspecialchars($__t('admin_users.th_score', 'Points'), ENT_QUOTES, 'UTF-8') ?></label>
                                                    <div class="d-flex gap-1">
                                                        <input type="number" id="new_points_<?= (int)$uId ?>" name="new_points" value="<?= $uPoints ?>" class="form-control form-control-sm">
                                                        <button type="submit" class="btn btn-sm btn-outline-dark"><?= htmlspecialchars($__t('admin_users.set_score_btn', 'Set'), ENT_QUOTES, 'UTF-8') ?></button>
                                                    </div>
                                                </form>
                                            </li>
                                            <li><hr class="dropdown-divider"></li>
                                            <li>
                                                <form method="POST" action="<?= $basePath ?>/admin/users" onsubmit="return confirm('<?= htmlspecialchars($__t('admin_users.resend_invite_confirm', 'Are you sure you want to resend the invitation email?'), ENT_QUOTES, 'UTF-8') ?>');">
                                                    <?= csrf_field() ?>
                                                    <input type="hidden" name="action" value="resend_invite">
                                                    <input type="hidden" name="target_user_id" value="<?= $uId ?>">
                                                    <button type="submit" class="dropdown-item"><?= htmlspecialchars($__t('admin_users.resend_invite_btn', 'Resend Invite'), ENT_QUOTES, 'UTF-8') ?></button>
                                                </form>
                                            </li>
                                            <li>
                                                <form method="POST" action="<?= $basePath ?>/admin/users" onsubmit="return confirm('<?= htmlspecialchars($__t('admin_users.reset_pwd_confirm', 'Send password reset link to user?'), ENT_QUOTES, 'UTF-8') ?>');">
                                                    <?= csrf_field() ?>
                                                    <input type="hidden" name="action" value="send_password_reset">
                                                    <input type="hidden" name="target_user_id" value="<?= $uId ?>">
                                                    <button type="submit" class="dropdown-item"><?= htmlspecialchars($__t('admin_users.reset_password_btn', 'Reset Password'), ENT_QUOTES, 'UTF-8') ?></button>
                                                </form>
                                            </li>
                                            <?php if ($u2fa): ?>
                                            <li>
                                                <form method="POST" action="<?= $basePath ?>/admin/users" onsubmit="return confirm('<?= htmlspecialchars($__t('admin_users.reset_2fa_confirm', 'Turn off two-factor authentication for this user?'), ENT_QUOTES, 'UTF-8') ?>');">
                                                    <?= csrf_field() ?>
                                                    <input type="hidden" name="action" value="reset_2fa">
                                                    <input type="hidden" name="target_user_id" value="<?= $uId ?>">
                                                    <button type="submit" class="dropdown-item"><?= htmlspecialchars($__t('admin_users.reset_2fa_btn', 'Turn Off 2FA'), ENT_QUOTES, 'UTF-8') ?></button>
                                                </form>
                                            </li>
                                            <?php endif; ?>
                                            <?php if ($uId !== (int)$currentUser['id'] && !$isFirstAdmin): ?>
                                            <li><hr class="dropdown-divider"></li>
                                            <?php if ($uActive): ?>
                                            <li>
                                                <form method="POST" action="<?= $basePath ?>/admin/users" onsubmit="return confirm('<?= htmlspecialchars($__t('admin_users.suspend_confirm', 'Are you sure you want to suspend this user?'), ENT_QUOTES, 'UTF-8') ?>');">
                                                    <?= csrf_field() ?>
                                                    <input type="hidden" name="action" value="suspend">
                                                    <input type="hidden" name="target_user_id" value="<?= $uId ?>">
                                                    <button type="submit" class="dropdown-item text-danger"><?= htmlspecialchars($__t('admin_users.suspend_btn', 'Suspend'), ENT_QUOTES, 'UTF-8') ?></button>
                                                </form>
                                            </li>
                                            <?php else: ?>
                                            <li>
                                                <form method="POST" action="<?= $basePath ?>/admin/users">
                                                    <?= csrf_field() ?>
                                                    <input type="hidden" name="action" value="unsuspend">
                                                    <input type="hidden" name="target_user_id" value="<?= $uId ?>">
                                                    <button type="submit" class="dropdown-item"><?= htmlspecialchars($__t('admin_users.reactivate_btn', 'Reactivate'), ENT_QUOTES, 'UTF-8') ?></button>
                                                </form>
                                            </li>
                                            <?php endif; ?>
                                            <li>
                                                <form method="POST" action="<?= $basePath ?>/admin/users" onsubmit="return confirm('Are you sure you want to permanently delete user <?= htmlspecialchars($uUsername, ENT_QUOTES, 'UTF-8') ?>? This action cannot be undone.');">
                                                    <?= csrf_field() ?>
                                                    <input type="hidden" name="action" value="delete">
                                                    <input type="hidden" name="target_user_id" value="<?= $uId ?>">
                                                    <button type="submit" class="dropdown-item text-danger"><?= htmlspecialchars($__t('admin_users.delete_btn', 'Delete'), ENT_QUOTES, 'UTF-8') ?></button>
                                                </form>
                                            </li>
                                            <?php endif; ?>
                                        </ul>
                                    </div>
                                </td>

                            </tr>
                        <?php endforeach; ?>
                    <?php endif; ?>
                </tbody>
            </table>
        </div>
    </div>
</div>
<script>
(function () {
    const input = document.getElementById('user-search');
    const empty = document.getElementById('user-search-empty');
    if (!input) return;
    const rows = Array.from(document.querySelectorAll('tbody tr[data-search]'));
    function applyFilter() {
        const q = (input.value || '').trim().toLowerCase();
        let visible = 0;
        rows.forEach(function (row) {
            const hay = row.getAttribute('data-search') || '';
            const show = q === '' || hay.indexOf(q) !== -1;
            row.classList.toggle('d-none', !show);
            if (show) visible++;
        });
        if (empty) {
            empty.classList.toggle('d-none', q === '' || visible > 0);
        }
    }
    input.addEventListener('input', applyFilter);
    input.addEventListener('search', applyFilter);
})();
</script>
<?php require_once ROOT_PATH . '/partials/footer.php'; ?>
