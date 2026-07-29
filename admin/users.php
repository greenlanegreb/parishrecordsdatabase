<?php
// admin/users.php - Admin interface view for user account management
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
$error   = $GLOBALS['error']    ?? '';

// Determine the first admin user ID dynamically (the earliest created user with the 'admin' role, fallback to ID 1)
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
} catch (Exception $e) {
    // Fallback safely to ID 1 if query fails
}

// Fetch users with their dynamic role names
$users_stmt = $pdo->query("
    SELECT u.id, u.username, u.email, u.points, u.email_verified, u.two_fa_enabled, u.is_active, u.created_at, u.role_id, r.role_name
    FROM users u
    LEFT JOIN roles r ON u.role_id = r.id
    ORDER BY u.created_at DESC
");
$users = $users_stmt->fetchAll(PDO::FETCH_ASSOC);

// Fetch all available roles for the role-change dropdown
$roles_list = $pdo->query("SELECT id, role_name FROM roles ORDER BY id ASC")->fetchAll(PDO::FETCH_ASSOC);
?>
<?php require_once '../partials/header.php'; ?>

<div class="search-box-container" role="region" aria-label="Admin User Management" style="max-width: 100%;">
    <h3>User Account Management & Leaderboard Moderation</h3>
    <p>Inspect user statuses, assign roles, reset 2FA configurations, manage points overrides, or suspend cheating accounts.</p>

    <?php if (!empty($error)): ?>
        <p class="alert-danger" role="alert"><strong><?php echo htmlspecialchars($error); ?></strong></p>
    <?php endif; ?>
    <?php if (!empty($message)): ?>
        <p class="alert-success" role="status"><strong><?php echo htmlspecialchars($message); ?></strong></p>
    <?php endif; ?>

    <div style="overflow-x: auto;">
        <table class="data-table" role="table" style="width: 100%; border-collapse: collapse;">
            <thead>
                <tr>
                    <th scope="col">ID</th>
                    <th scope="col">Username</th>
                    <th scope="col">Email</th>
                    <th scope="col">Role Assignment</th>
                    <th scope="col">Score</th>
                    <th scope="col">Status</th>
                    <th scope="col">2FA</th>
                    <th scope="col">Actions & Moderation</th>
                </tr>
            </thead>
            <tbody>
                <?php if (empty($users)): ?>
                    <tr><td colspan="8" style="text-align: center; padding: 1rem;">No users found.</td></tr>
                <?php else: ?>
                    <?php foreach ($users as $u): ?>
                        <?php $is_first_admin = (intval($u['id']) === $first_admin_id); ?>
                        <tr>
                            <td><?php echo $u['id']; ?></td>
                            <td><?php echo htmlspecialchars($u['username']); ?></td>
                            <td><?php echo htmlspecialchars($u['email']); ?></td>
                            <td>
                                <!-- Role Change Form -->
                                <?php if ($is_first_admin): ?>
                                    <span style="font-size: 0.85rem; color: #666; font-style: italic;">
                                        <?php echo htmlspecialchars(ucwords($u['role_name'] ?? 'Admin')); ?><br>
                                        <small>(Protected Primary Admin)</small>
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
                                        <button type="submit" class="btn btn-secondary" style="font-size: 0.75rem; padding: 0.2rem 0.4rem;">Update</button>
                                    </form>
                                <?php endif; ?>
                            </td>
                            <td><strong>⭐ <?php echo intval($u['points']); ?></strong></td>
                            <td>
                                <?php if ($u['is_active']): ?>
                                    <span class="user-status-active">Active</span>
                                <?php else: ?>
                                    <span class="user-status-suspended">Suspended</span>
                                <?php endif; ?>
                            </td>
                            <td><?php echo $u['two_fa_enabled'] ? '<span class="user-2fa-enabled">Enabled</span>' : 'Disabled'; ?></td>
                            <td style="display: flex; flex-direction: column; gap: 0.5rem; padding: 0.75rem;">
                                
                                <!-- Points Override Form -->
                                <form method="POST" action="actions/save_user_management.php" style="display: flex; gap: 0.3rem; align-items: center;">
                                    <?php echo csrf_field(); ?>
                                    <input type="hidden" name="action" value="override_points">
                                    <input type="hidden" name="target_user_id" value="<?php echo $u['id']; ?>">
                                    <input type="number" name="new_points" value="<?php echo intval($u['points']); ?>" style="width: 70px; padding: 0.2rem;" aria-label="Points for <?php echo htmlspecialchars($u['username']); ?>">
                                    <button type="submit" class="btn btn-secondary" style="font-size: 0.8rem; padding: 0.2rem 0.5rem;">Set Score</button>
                                </form>
                                <div style="display: flex; gap: 0.4rem; flex-wrap: wrap;">
                                    <!-- Suspension Toggle Button (Prevent suspending the primary admin too) -->
                                    <?php if ($u['id'] !== $current_user['id'] && !$is_first_admin): ?>
                                        <?php if ($u['is_active']): ?>
                                            <form method="POST" action="actions/save_user_management.php" onsubmit="return confirm('Suspend user and block access for cheating/violation?');" style="display:inline;">
                                                <?php echo csrf_field(); ?>
                                                <input type="hidden" name="action" value="suspend">
                                                <input type="hidden" name="target_user_id" value="<?php echo $u['id']; ?>">
                                                <button type="submit" class="btn btn-danger" style="font-size: 0.8rem; padding: 0.2rem 0.5rem;" aria-label="Suspend <?php echo htmlspecialchars($u['username']); ?>">Suspend</button>
                                            </form>
                                        <?php else: ?>
                                            <form method="POST" action="actions/save_user_management.php" style="display:inline;">
                                                <?php echo csrf_field(); ?>
                                                <input type="hidden" name="action" value="unsuspend">
                                                <input type="hidden" name="target_user_id" value="<?php echo $u['id']; ?>">
                                                <button type="submit" class="btn btn-reactivate" style="font-size: 0.8rem; padding: 0.2rem 0.5rem;" aria-label="Reactivate <?php echo htmlspecialchars($u['username']); ?>">Reactivate</button>
                                            </form>
                                        <?php endif; ?>
                                    <?php endif; ?>
                                    <!-- 2FA Reset Button -->
                                    <?php if ($u['two_fa_enabled']): ?>
                                        <form method="POST" action="actions/save_user_management.php" onsubmit="return confirm('Reset 2FA for this user?');" style="display:inline;">
                                            <?php echo csrf_field(); ?>
                                            <input type="hidden" name="action" value="reset_2fa">
                                            <input type="hidden" name="target_user_id" value="<?php echo $u['id']; ?>">
                                            <button type="submit" class="btn btn-reset-2fa" style="font-size: 0.8rem; padding: 0.2rem 0.5rem;" aria-label="Reset 2FA for <?php echo htmlspecialchars($u['username']); ?>">Reset 2FA</button>
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
