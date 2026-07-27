<?php
// admin/users.php - Admin interface view for user account management
require_once '../db/db.php';
require_once '../db/auth_helpers.php';
session_start();

// Enforce strict administrator privileges via central helper
$current_user = require_role($pdo, ['admin']);

$message = $_SESSION['message'] ?? '';
$error = $_SESSION['error'] ?? '';
unset($_SESSION['message'], $_SESSION['error']);

$users_stmt = $pdo->query("SELECT id, username, email, role, points, email_verified, two_fa_enabled, is_active, created_at FROM users ORDER BY created_at DESC");
$users = $users_stmt->fetchAll();
?>

    <?php require_once '../partials/header.php'; ?>

    <div class="search-box-container" role="region" aria-label="Admin User Management" style="max-width: 100%;">
        <h3>User Account Management & Leaderboard Moderation</h3>
        <p>Inspect user statuses, reset 2FA configurations, manage points overrides, or suspend cheating accounts.</p>

        <?php if (!empty($error)): ?>
            <p class="alert-danger"><strong><?php echo htmlspecialchars($error); ?></strong></p>
        <?php endif; ?>
        <?php if (!empty($message)): ?>
            <p class="alert-success"><strong><?php echo htmlspecialchars($message); ?></strong></p>
        <?php endif; ?>

        <div style="overflow-x: auto;">
            <table class="data-table" role="table" style="width: 100%; border-collapse: collapse;">
                <thead>
                    <tr>
                        <th scope="col">ID</th>
                        <th scope="col">Username</th>
                        <th scope="col">Email</th>
                        <th scope="col">Role</th>
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
                            <tr>
                                <td><?php echo $u['id']; ?></td>
                                <td><?php echo htmlspecialchars($u['username']); ?></td>
                                <td><?php echo htmlspecialchars($u['email']); ?></td>
                                <td><?php echo htmlspecialchars($u['role']); ?></td>
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
                                        <input type="hidden" name="action" value="override_points">
                                        <input type="hidden" name="target_user_id" value="<?php echo $u['id']; ?>">
                                        <input type="number" name="new_points" value="<?php echo intval($u['points']); ?>" style="width: 70px; padding: 0.2rem;" aria-label="Points for <?php echo htmlspecialchars($u['username']); ?>">
                                        <button type="submit" class="btn btn-secondary" style="font-size: 0.8rem; padding: 0.2rem 0.5rem;">Set Score</button>
                                    </form>

                                    <div style="display: flex; gap: 0.4rem; flex-wrap: wrap;">
                                        <!-- Suspension Toggle Button -->
                                        <?php if ($u['id'] !== $current_user['id']): ?>
                                            <?php if ($u['is_active']): ?>
                                                <form method="POST" action="actions/save_user_management.php" onsubmit="return confirm('Suspend user and block access for cheating/violation?');" style="display:inline;">
                                                    <input type="hidden" name="action" value="suspend">
                                                    <input type="hidden" name="target_user_id" value="<?php echo $u['id']; ?>">
                                                    <button type="submit" class="btn btn-danger" style="font-size: 0.8rem; padding: 0.2rem 0.5rem;" aria-label="Suspend <?php echo htmlspecialchars($u['username']); ?>">Suspend</button>
                                                </form>
                                            <?php else: ?>
                                                <form method="POST" action="actions/save_user_management.php" style="display:inline;">
                                                    <input type="hidden" name="action" value="unsuspend">
                                                    <input type="hidden" name="target_user_id" value="<?php echo $u['id']; ?>">
                                                    <button type="submit" class="btn btn-reactivate" style="font-size: 0.8rem; padding: 0.2rem 0.5rem;" aria-label="Reactivate <?php echo htmlspecialchars($u['username']); ?>">Reactivate</button>
                                                </form>
                                            <?php endif; ?>
                                        <?php endif; ?>

                                        <!-- 2FA Reset Button -->
                                        <?php if ($u['two_fa_enabled']): ?>
                                            <form method="POST" action="actions/save_user_management.php" onsubmit="return confirm('Reset 2FA for this user?');" style="display:inline;">
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
