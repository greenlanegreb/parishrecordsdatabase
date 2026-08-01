<?php
// admin/users.php - Admin interface view for user account management and invitations
require_once '../db/db.php';
require_once '../db/auth_helpers.php';
require_once '../includes/functions.php';

// Ensure the users module is enabled; otherwise block direct access
if (!is_module_enabled($pdo, 'users')) {
    http_response_code(403);
    exit('403 Forbidden: The User Management module is currently disabled.');
}

// Standard admin bootstrap (permission + flash messages)
$current_user = require_admin_page($pdo, 'manage_users', 'Manage user accounts, roles, and status');
$message = $GLOBALS['message'] ?? '';
$error   = $GLOBALS['error']   ?? '';

// Catch pre-filled data from volunteer portal bridge if present
$prefill_email = trim($_GET['email'] ?? '');
$prefill_first = trim($_GET['first_name'] ?? '');
$prefill_surname = trim($_GET['surname'] ?? '');
$volunteer_id  = intval($_GET['volunteer_id'] ?? 0);

// Determine the first admin user ID dynamically
$first_admin_id = 1;
try {
    $fa_stmt = $pdo->query("
        SELECT u.id FROM users u
        JOIN roles r ON u.role_id = r.id
        WHERE LOWER(r.role_name) = 'admin'
        ORDER BY u.created_at ASC, u.id ASC
        LIMIT 1
    ");
    $fa_id = $fa_stmt->fetchColumn();
    if ($fa_id) {
        $first_admin_id = intval($fa_id);
    }
} catch (Exception $e) {}

// Fetch users with their dynamic role names
$users_stmt = $pdo->query("
    SELECT u.id, u.username, u.email, u.points, u.email_verified, u.two_fa_enabled, u.is_active, u.created_at, u.role_id, r.role_name
    FROM users u
    LEFT JOIN roles r ON u.role_id = r.id
    ORDER BY u.created_at DESC
");
$users = $users_stmt->fetchAll(PDO::FETCH_ASSOC);

// Fetch all available roles for dropdowns
$roles_list = $pdo->query("SELECT id, role_name FROM roles ORDER BY id ASC")->fetchAll(PDO::FETCH_ASSOC);
?>
<?php require_once '../partials/header.php'; ?>

<div class="search-box-container" role="region" aria-label="Admin User Management" style="max-width: 100%;">
    <div style="display: flex; justify-content: space-between; align-items: center; margin-bottom: 1.5rem; flex-wrap: wrap; gap: 1rem;">
        <div>
            <h3><?php echo htmlspecialchars(__('admin_users.heading')); ?></h3>
            <p><?php echo htmlspecialchars(__('admin_users.subheading')); ?></p>
        </div>
        <div style="display: flex; gap: 0.5rem; flex-wrap: wrap;">
            <a href="manage_user_emails.php" class="btn btn-secondary" style="text-decoration: none;">✉️ <?php echo htmlspecialchars(__('admin_users.manage_templates_btn')); ?></a>
        </div>
    </div>

    <?php if (!empty($error)): ?>
        <p class="alert-danger" role="alert"><strong><?php echo htmlspecialchars($error); ?></strong></p>
    <?php endif; ?>
    <?php if (!empty($message)): ?>
        <p class="alert-success" role="status"><strong><?php echo htmlspecialchars($message); ?></strong></p>
    <?php endif; ?>

    <!-- Integrated Inline Invite User Accordion -->
    <details id="invite-user-section" style="background: rgba(0,0,0,0.02); border: 1px solid var(--border-color); border-radius: 6px; padding: 1rem 1.25rem; margin-bottom: 2rem;" <?php echo ($volunteer_id > 0) ? 'open' : ''; ?>>
        <summary style="cursor: pointer; font-weight: bold; color: #333; font-size: 1.1rem; outline: none;">
            ➕ <?php echo htmlspecialchars(__('create_user.heading')); ?>
        </summary>
        <div style="margin-top: 1rem; padding-top: 1rem; border-top: 1px solid #eee;">
            <p style="margin-top: 0; color: #555; font-size: 0.95rem;"><?php echo htmlspecialchars(__('create_user.subheading')); ?></p>
            <form method="POST" action="actions/save_user.php" style="max-width: 600px;">
                <?php echo csrf_field(); ?>
                <?php if ($volunteer_id > 0): ?>
                    <input type="hidden" name="volunteer_id" value="<?php echo $volunteer_id; ?>">
                <?php endif; ?>

                <!-- First Name & Surname -->
                <div style="display: flex; gap: 1rem; margin-bottom: 1rem;">
                    <div style="flex: 1;">
                        <label for="first_name"><strong><?php echo htmlspecialchars(__('create_user.first_name')); ?></strong></label><br>
                        <input type="text" id="first_name" name="first_name" value="<?php echo htmlspecialchars($prefill_first); ?>" class="profile-input" style="width:100%; padding:0.4rem;">
                    </div>
                    <div style="flex: 1;">
                        <label for="surname"><strong><?php echo htmlspecialchars(__('create_user.surname')); ?></strong></label><br>
                        <input type="text" id="surname" name="surname" value="<?php echo htmlspecialchars($prefill_surname); ?>" class="profile-input" style="width:100%; padding:0.4rem;">
                    </div>
                </div>

                <div style="margin-bottom: 1rem;">
                    <label for="username"><strong><?php echo htmlspecialchars(__('create_user.username_label')); ?></strong></label><br>
                    <input type="text" id="username" name="username" placeholder="<?php echo htmlspecialchars(__('create_user.username_placeholder')); ?>" class="profile-input" style="width:100%; padding:0.4rem;">
                    <small style="color:#666;"><?php echo htmlspecialchars(__('create_user.username_help')); ?></small>
                </div>
                
                <div style="margin-bottom: 1rem;">
                    <label for="email"><strong><?php echo htmlspecialchars(__('create_user.email_label')); ?></strong> <span style="color:red;">*</span></label><br>
                    <input type="email" id="email" name="email" value="<?php echo htmlspecialchars($prefill_email); ?>" required class="profile-input" style="width:100%; padding:0.4rem;">
                </div>
                
                <div style="margin-bottom: 1.5rem;">
                    <label for="role_id"><strong><?php echo htmlspecialchars(__('create_user.role_label')); ?></strong></label><br>
                    <select id="role_id" name="role_id" class="profile-input suggest-edit-select" style="width:100%; padding:0.4rem;">
                        <?php foreach ($roles_list as $r): ?>
                            <option value="<?php echo $r['id']; ?>" <?php echo ($r['role_name'] === 'user') ? 'selected' : ''; ?>>
                                <?php echo htmlspecialchars(ucwords($r['role_name'])); ?>
                            </option>
                        <?php endforeach; ?>
                    </select>
                </div>
                
                <button type="submit" class="btn"><?php echo htmlspecialchars(__('create_user.submit_btn')); ?></button>
            </form>
        </div>
    </details>

    <div style="overflow-x: auto;">
        <table class="data-table" role="table" style="width: 100%; border-collapse: collapse;">
            <thead>
                <tr>
                    <th scope="col"><?php echo htmlspecialchars(__('feedback_schema.th_id')); ?></th>
                    <th scope="col"><?php echo htmlspecialchars(__('admin_users.th_username')); ?></th>
                    <th scope="col"><?php echo htmlspecialchars(__('admin_users.th_email_override')); ?></th>
                    <th scope="col"><?php echo htmlspecialchars(__('admin_users.th_role_assignment')); ?></th>
                    <th scope="col"><?php echo htmlspecialchars(__('admin_users.th_score')); ?></th>
                    <th scope="col"><?php echo htmlspecialchars(__('admin_users.th_status')); ?></th>
                    <th scope="col"><?php echo htmlspecialchars(__('admin_users.th_2fa')); ?></th>
                    <th scope="col"><?php echo htmlspecialchars(__('admin_users.th_actions')); ?></th>
                </tr>
            </thead>
            <tbody>
                <?php if (empty($users)): ?>
                    <tr><td colspan="8" style="text-align: center; padding: 1rem;"><?php echo htmlspecialchars(__('admin_users.no_users')); ?></td></tr>
                <?php else: ?>
                    <?php foreach ($users as $u): ?>
                        <?php $is_first_admin = (intval($u['id']) === $first_admin_id); ?>
                        <tr>
                            <td><?php echo $u['id']; ?></td>
                            <td><?php echo htmlspecialchars($u['username']); ?></td>
                            <td>
                                <!-- Email Display & Inline Override Form -->
                                <form method="POST" action="actions/save_user_management.php" style="display: flex; gap: 0.3rem; align-items: center; margin-bottom: 0.3rem;">
                                    <?php echo csrf_field(); ?>
                                    <input type="hidden" name="action" value="update_email">
                                    <input type="hidden" name="target_user_id" value="<?php echo $u['id']; ?>">
                                    <input type="email" name="new_email" value="<?php echo htmlspecialchars($u['email']); ?>" style="padding: 0.2rem; font-size: 0.85rem; width: 160px;" required aria-label="Email for <?php echo htmlspecialchars($u['username']); ?>">
                                    <button type="submit" class="btn btn-secondary" style="font-size: 0.75rem; padding: 0.2rem 0.4rem;" title="<?php echo htmlspecialchars(__('admin_users.save_email_title')); ?>"><?php echo htmlspecialchars(__('btn.save')); ?></button>
                                </form>
                                <small style="color: #666;"><?php echo htmlspecialchars(__('admin_users.verified_label')); ?> <?php echo $u['email_verified'] ? htmlspecialchars(__('admin_users.yes')) : htmlspecialchars(__('admin_users.no')); ?></small>
                            </td>
                            <td>
                                <!-- Role Change Form -->
                                <?php if ($is_first_admin): ?>
                                    <span style="font-size: 0.85rem; color: #666; font-style: italic;">
                                        <?php echo htmlspecialchars(ucwords($u['role_name'] ?? 'Admin')); ?><br>
                                        <small>(<?php echo htmlspecialchars(__('admin_users.protected_admin')); ?>)</small>
                                    </span>
                                <?php else: ?>
                                    <form method="POST" action="actions/save_user_management.php" style="display: flex; gap: 0.3rem; align-items: center;">
                                        <?php echo csrf_field(); ?>
                                        <input type="hidden" name="action" value="change_role">
                                        <input type="hidden" name="target_user_id" value="<?php echo $u['id']; ?>">
                                        <select name="new_role_id" style="padding: 0.2rem; font-size: 0.85rem;" aria-label="Role for <?php echo htmlspecialchars($u['username']); ?>">
                                            <?php foreach ($roles_list as $r): ?>
                                                <option value="<?php echo $r['id']; ?>" <?php echo ($u['role_id'] == $r['id']) ? 'selected' : ''; ?>>
                                                    <?php echo htmlspecialchars(ucwords($r['role_name'])); ?>
                                                </option>
                                            <?php endforeach; ?>
                                        </select>
                                        <button type="submit" class="btn btn-secondary" style="font-size: 0.75rem; padding: 0.2rem 0.4rem;"><?php echo htmlspecialchars(__('admin_users.update_btn')); ?></button>
                                    </form>
                                <?php endif; ?>
                            </td>
                            <td><strong>⭐ <?php echo intval($u['points']); ?></strong></td>
                            <td>
                                <?php if ($u['is_active']): ?>
                                    <span class="user-status-active"><?php echo htmlspecialchars(__('admin_users.status_active')); ?></span>
                                <?php else: ?>
                                    <span class="user-status-suspended"><?php echo htmlspecialchars(__('admin_users.status_suspended')); ?></span>
                                <?php endif; ?>
                            </td>
                            <td><?php echo $u['two_fa_enabled'] ? '<span class="user-2fa-enabled">' . htmlspecialchars(__('admin_users.enabled')) . '</span>' : htmlspecialchars(__('admin_users.disabled')); ?></td>
                            <td style="display: flex; flex-direction: column; gap: 0.5rem; padding: 0.75rem;">
                                
                                <!-- Points Override Form -->
                                <form method="POST" action="actions/save_user_management.php" style="display: flex; gap: 0.3rem; align-items: center;">
                                    <?php echo csrf_field(); ?>
                                    <input type="hidden" name="action" value="override_points">
                                    <input type="hidden" name="target_user_id" value="<?php echo $u['id']; ?>">
                                    <input type="number" name="new_points" value="<?php echo intval($u['points']); ?>" style="width: 70px; padding: 0.2rem;" aria-label="Points for <?php echo htmlspecialchars($u['username']); ?>">
                                    <button type="submit" class="btn btn-secondary" style="font-size: 0.8rem; padding: 0.2rem 0.5rem;"><?php echo htmlspecialchars(__('admin_users.set_score_btn')); ?></button>
                                </form>

                                <div style="display: flex; gap: 0.4rem; flex-wrap: wrap;">
                                    <!-- Resend Invite Button -->
                                    <form method="POST" action="actions/save_user_management.php" onsubmit="return confirm('<?php echo htmlspecialchars(__('admin_users.resend_invite_confirm')); ?>');" style="display:inline;">
                                        <?php echo csrf_field(); ?>
                                        <input type="hidden" name="action" value="resend_invite">
                                        <input type="hidden" name="target_user_id" value="<?php echo $u['id']; ?>">
                                        <button type="submit" class="btn btn-secondary" style="font-size: 0.8rem; padding: 0.2rem 0.5rem;" aria-label="Resend invite to <?php echo htmlspecialchars($u['username']); ?>"><?php echo htmlspecialchars(__('admin_users.resend_invite_btn')); ?></button>
                                    </form>

                                    <!-- Send Password Reset Link Button -->
                                    <form method="POST" action="actions/save_user_management.php" onsubmit="return confirm('<?php echo htmlspecialchars(__('admin_users.reset_pwd_confirm')); ?>');" style="display:inline;">
                                        <?php echo csrf_field(); ?>
                                        <input type="hidden" name="action" value="send_password_reset">
                                        <input type="hidden" name="target_user_id" value="<?php echo $u['id']; ?>">
                                        <button type="submit" class="btn btn-secondary" style="font-size: 0.8rem; padding: 0.2rem 0.5rem;" aria-label="Send password reset to <?php echo htmlspecialchars($u['username']); ?>"><?php echo htmlspecialchars(__('admin_users.reset_password_btn')); ?></button>
                                    </form>

                                    <!-- Suspension Toggle Button & Delete Button -->
                                    <?php if ($u['id'] !== intval($current_user['id']) && !$is_first_admin): ?>
                                        <?php if ($u['is_active']): ?>
                                            <form method="POST" action="actions/save_user_management.php" onsubmit="return confirm('<?php echo htmlspecialchars(__('admin_users.suspend_confirm')); ?>');" style="display:inline;">
                                                <?php echo csrf_field(); ?>
                                                <input type="hidden" name="action" value="suspend">
                                                <input type="hidden" name="target_user_id" value="<?php echo $u['id']; ?>">
                                                <button type="submit" class="btn btn-danger" style="font-size: 0.8rem; padding: 0.2rem 0.5rem;" aria-label="Suspend <?php echo htmlspecialchars($u['username']); ?>"><?php echo htmlspecialchars(__('admin_users.suspend_btn')); ?></button>
                                            </form>
                                        <?php else: ?>
                                            <form method="POST" action="actions/save_user_management.php" style="display:inline;">
                                                <?php echo csrf_field(); ?>
                                                <input type="hidden" name="action" value="unsuspend">
                                                <input type="hidden" name="target_user_id" value="<?php echo $u['id']; ?>">
                                                <button type="submit" class="btn btn-reactivate" style="font-size: 0.8rem; padding: 0.2rem 0.5rem;" aria-label="Reactivate <?php echo htmlspecialchars($u['username']); ?>"><?php echo htmlspecialchars(__('admin_users.reactivate_btn')); ?></button>
                                            </form>
                                        <?php endif; ?>

                                        <!-- Permanent Clean Delete Button -->
                                        <form method="POST" action="actions/save_user_management.php" onsubmit="return confirm('Are you sure you want to permanently delete user <?php echo htmlspecialchars($u['username']); ?>? This action cannot be undone.');" style="display:inline;">
                                            <?php echo csrf_field(); ?>
                                            <input type="hidden" name="action" value="delete">
                                            <input type="hidden" name="target_user_id" value="<?php echo $u['id']; ?>">
                                            <button type="submit" class="btn btn-danger" style="font-size: 0.8rem; padding: 0.2rem 0.5rem;" aria-label="Delete <?php echo htmlspecialchars($u['username']); ?>">Delete</button>
                                        </form>
                                    <?php endif; ?>

                                    <!-- 2FA Reset / Disable Button -->
                                    <?php if ($u['two_fa_enabled']): ?>
                                        <form method="POST" action="actions/save_user_management.php" onsubmit="return confirm('<?php echo htmlspecialchars(__('admin_users.reset_2fa_confirm')); ?>');" style="display:inline;">
                                            <?php echo csrf_field(); ?>
                                            <input type="hidden" name="action" value="reset_2fa">
                                            <input type="hidden" name="target_user_id" value="<?php echo $u['id']; ?>">
                                            <button type="submit" class="btn btn-reset-2fa" style="font-size: 0.8rem; padding: 0.2rem 0.5rem;" aria-label="Reset 2FA for <?php echo htmlspecialchars($u['username']); ?>"><?php echo htmlspecialchars(__('admin_users.reset_2fa_btn')); ?></button>
                                        </form>
                                    <?php endif; ?>
                                </div>
                            </td>
                        </tr>
                    <?php endforeach; ?>
                <?php endif; ?>
            </tbody>
        </table>
    </div>
</div>

<?php require_once '../partials/footer.php'; ?>
