<?php
// admin/settings.php - Global Site Settings and Notices Management Interface
require_once '../db/db.php';
require_once '../db/auth_helpers.php';
session_start();

// Restrict access exclusively to administrators
require_role($pdo, ['admin']);

$current_system_name = get_system_name($pdo);

// Fetch maintenance settings values dynamically
$maintenance_mode = '0';
$maintenance_reason = 'Scheduled system maintenance and database updates.';
$maintenance_eta = 'Shortly';

try {
    $stmt = $pdo->query("SELECT setting_key, setting_value FROM site_settings WHERE setting_key IN ('maintenance_mode', 'maintenance_reason', 'maintenance_eta')");
    while ($row = $stmt->fetch(PDO::FETCH_ASSOC)) {
        if ($row['setting_key'] === 'maintenance_mode') $maintenance_mode = $row['setting_value'];
        if ($row['setting_key'] === 'maintenance_reason') $maintenance_reason = $row['setting_value'];
        if ($row['setting_key'] === 'maintenance_eta') $maintenance_eta = $row['setting_value'];
    }
} catch (PDOException $e) {
    // Fallback if keys don't exist yet
}

$message = $_SESSION['message'] ?? '';
$error = $_SESSION['error'] ?? '';
unset($_SESSION['message'], $_SESSION['error']);

// Fetch all existing notices
$notices = $pdo->query("SELECT * FROM site_notices ORDER BY display_order ASC, id DESC")->fetchAll(PDO::FETCH_ASSOC);
?>

<?php require_once '../partials/header.php'; ?>

<div class="search-box-container" role="region" aria-label="Site Settings Form">
    <h3>Global Site Settings & Notices</h3>
    <p>Manage core application configurations, maintenance mode, and modular site notices.</p>

    <?php if (!empty($error)): ?>
        <p class="alert-danger"><strong><?php echo htmlspecialchars($error); ?></strong></p>
    <?php endif; ?>
    <?php if (!empty($message)): ?>
        <p class="alert-success"><strong><?php echo htmlspecialchars($message); ?></strong></p>
    <?php endif; ?>

    <!-- Core System Settings -->
    <form method="POST" action="actions/save_settings.php" style="margin-bottom: 2rem;">
        <label for="system_name"><strong>System / Application Name:</strong></label><br>
        <input type="text" id="system_name" name="system_name" value="<?php echo htmlspecialchars($current_system_name); ?>" required class="volunteer-input" style="width: 100%; max-width: 400px; padding: 0.4rem; margin-top: 0.3rem; margin-bottom: 1rem;"><br>

        <button type="submit" class="btn">Save Core Settings</button>
    </form>

    <hr style="border: 0.0625rem solid var(--border-color); margin: 2rem 0;">

    <!-- System Maintenance Mode Section -->
    <h4 style="margin-bottom: 0.5rem;">System Maintenance Mode</h4>
    <p style="font-size: 0.9rem; color: #666; margin-bottom: 1rem;">Take the application offline to protect user data work during updates. Only administrators will be able to bypass this lock.</p>

    <form method="POST" action="actions/save_maintenance.php" style="margin-bottom: 2rem;">
        <div style="margin-bottom: 1rem;">
            <label style="cursor: pointer;">
                <input type="checkbox" name="maintenance_mode" value="1" <?php echo ($maintenance_mode === '1') ? 'checked' : ''; ?>> 
                <strong>Enable Maintenance Mode (Take Site Offline)</strong>
            </label>
        </div>

        <div style="margin-bottom: 1rem;">
            <label for="maintenance_reason" style="font-size: 0.85rem;"><strong>Reason / Message for Users:</strong></label><br>
            <textarea id="maintenance_reason" name="maintenance_reason" rows="2" class="volunteer-input" style="width: 100%; max-width: 600px; padding: 0.4rem; margin-top: 0.3rem;" required><?php echo htmlspecialchars($maintenance_reason); ?></textarea>
        </div>

        <div style="margin-bottom: 1rem;">
            <label for="maintenance_eta" style="font-size: 0.85rem;"><strong>Expected Return Time (ETA):</strong></label><br>
            <input type="text" id="maintenance_eta" name="maintenance_eta" value="<?php echo htmlspecialchars($maintenance_eta); ?>" class="volunteer-input" style="width: 100%; max-width: 300px; padding: 0.4rem; margin-top: 0.3rem;" required>
        </div>

        <button type="submit" class="btn btn-danger">Save Maintenance Settings</button>
    </form>

    <hr style="border: 0.0625rem solid var(--border-color); margin: 2rem 0;">

    <!-- Notices Modular Management Section -->
    <div style="display: flex; justify-content: space-between; align-items: center; margin-bottom: 1rem;">
        <h4 style="margin: 0;">Site Notices & Announcements</h4>
        <a href="notices.php" class="btn btn-secondary" style="font-size: 0.85rem; text-decoration: none;">+ Add New Notice</a>
    </div>
    <p style="font-size: 0.9rem; color: #666; margin-bottom: 1.5rem;">Expand any notice accordion section below to review or manage its content, targeting roles, and visibility parameters.</p>

    <?php if (empty($notices)): ?>
        <p style="padding: 1rem; background: rgba(0,0,0,0.02); border-radius: 4px; text-align: center; color: #666;">No notices configured yet. Click "+ Add New Notice" to create one.</p>
    <?php else: ?>
        <div class="accordion-container" style="display: flex; flex-direction: column; gap: 0.75rem;">
            <?php foreach ($notices as $n): ?>
                <?php 
                    $selected_roles = array_map('trim', explode(',', $n['target_roles'] ?? ''));
                    $is_everyone = in_array('everyone', $selected_roles);
                ?>
                <details style="background: #fdfdfd; border: 1px solid var(--border-color, #ccc); border-radius: 6px; padding: 0.75rem 1rem; box-shadow: 0 1px 3px rgba(0,0,0,0.02);">
                    <summary style="cursor: pointer; font-weight: bold; color: #333; display: flex; justify-content: space-between; align-items: center;">
                        <span>
                            <?php echo htmlspecialchars($n['title']); ?> 
                            <span style="font-weight: normal; font-size: 0.8rem; color: #666; margin-left: 0.5rem;">(Target: <?php echo htmlspecialchars($n['target_roles']); ?>)</span>
                        </span>
                        <span style="font-size: 0.8rem; padding: 0.2rem 0.5rem; background: <?php echo $n['is_active'] ? '#d4edda; color: #155724;' : '#f8d7da; color: #721c24;'; ?> border-radius: 4px;">
                            <?php echo $n['is_active'] ? 'Active' : 'Inactive'; ?>
                        </span>
                    </summary>
                    
                    <div style="margin-top: 1rem; padding-top: 1rem; border-top: 1px solid #eee;">
                        <form method="POST" action="actions/save_notice_inline.php">
                            <input type="hidden" name="notice_id" value="<?php echo $n['id']; ?>">
                            
                            <label style="font-size: 0.85rem;">Title:</label><br>
                            <input type="text" name="title" value="<?php echo htmlspecialchars($n['title']); ?>" class="volunteer-input" style="width: 100%; margin-bottom: 0.5rem; padding: 0.3rem;" required><br>

                            <label style="font-size: 0.85rem;">Content:</label><br>
                            <textarea name="content" rows="3" class="volunteer-input" style="width: 100%; margin-bottom: 0.5rem; padding: 0.3rem;" required><?php echo htmlspecialchars($n['content']); ?></textarea><br>

                            <label style="font-size: 0.85rem; font-weight: bold;">Target Audience / Groups:</label><br>
                            <div style="display: flex; gap: 1rem; flex-wrap: wrap; margin-bottom: 0.75rem; background: rgba(0,0,0,0.01); padding: 0.5rem; border: 1px solid #eee; border-radius: 4px;">
                                <label style="font-weight: normal; font-size: 0.85rem; cursor: pointer;">
                                    <input type="checkbox" name="target_roles[]" value="everyone" class="everyone-checkbox" data-notice-id="<?php echo $n['id']; ?>" <?php echo $is_everyone ? 'checked' : ''; ?>> <strong>Everyone</strong> (Overrides all)
                                </label>
                                <label style="font-weight: normal; font-size: 0.85rem; cursor: pointer;">
                                    <input type="checkbox" name="target_roles[]" value="public" class="role-checkbox-<?php echo $n['id']; ?>" <?php echo ($is_everyone || in_array('public', $selected_roles)) ? 'checked' : ''; ?> <?php echo $is_everyone ? 'disabled style="opacity:0.6;"' : ''; ?>> Public (Guests / Unauthenticated)
                                </label>
                                <label style="font-weight: normal; font-size: 0.85rem; cursor: pointer;">
                                    <input type="checkbox" name="target_roles[]" value="moderator" class="role-checkbox-<?php echo $n['id']; ?>" <?php echo ($is_everyone || in_array('moderator', $selected_roles)) ? 'checked' : ''; ?> <?php echo $is_everyone ? 'disabled style="opacity:0.6;"' : ''; ?>> Moderators
                                </label>
                                <label style="font-weight: normal; font-size: 0.85rem; cursor: pointer;">
                                    <input type="checkbox" name="target_roles[]" value="admin" class="role-checkbox-<?php echo $n['id']; ?>" <?php echo ($is_everyone || in_array('admin', $selected_roles)) ? 'checked' : ''; ?> <?php echo $is_everyone ? 'disabled style="opacity:0.6;"' : ''; ?>> Administrators
                                </label>
                            </div>

                            <div style="display: flex; gap: 1.5rem; margin-bottom: 1rem; font-size: 0.9rem; align-items: center;">
                                <label><input type="checkbox" name="is_dismissible" value="1" <?php echo $n['is_dismissible'] ? 'checked' : ''; ?>> Dismissible (Has Close 'X')</label>
                                <label><input type="checkbox" name="is_active" value="1" <?php echo $n['is_active'] ? 'checked' : ''; ?>> Active State</label>
                                <div>
                                    <label style="font-size: 0.85rem;">Order:</label>
                                    <input type="number" name="display_order" value="<?php echo intval($n['display_order']); ?>" style="width: 60px; padding: 0.2rem;">
                                </div>
                            </div>

                            <div style="display: flex; gap: 0.5rem;">
                                <button type="submit" name="update_action" value="save" class="btn" style="font-size: 0.85rem; padding: 0.3rem 0.8rem;">Save Notice</button>
                                <button type="submit" name="update_action" value="delete" class="btn btn-danger" style="font-size: 0.85rem; padding: 0.3rem 0.8rem;" onclick="return confirm('Delete this notice completely?');">Delete Notice</button>
                            </div>
                        </form>
                    </div>
                </details>
            <?php endforeach; ?>
        </div>
    <?php endif; ?>
</div>

<script>
document.addEventListener('DOMContentLoaded', () => {
    document.querySelectorAll('.everyone-checkbox').forEach(everyoneBox => {
        const noticeId = everyoneBox.getAttribute('data-notice-id');
        const roleBoxes = document.querySelectorAll('.role-checkbox-' + noticeId);

        const toggleRoles = (isEveryoneChecked) => {
            roleBoxes.forEach(box => {
                box.checked = isEveryoneChecked;
                box.disabled = isEveryoneChecked;
                box.style.opacity = isEveryoneChecked ? '0.6' : '1';
            });
        };

        everyoneBox.addEventListener('change', () => {
            toggleRoles(everyoneBox.checked);
        });
    });
});
</script>

<?php require_once '../partials/footer.php'; ?>
