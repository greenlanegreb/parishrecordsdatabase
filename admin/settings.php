<?php
// admin/settings.php - Global Site Settings, Maintenance, Notices, and Permissions Management Interface
require_once '../db/db.php';
require_once '../db/auth_helpers.php';
require_once '../includes/functions.php';
session_start();

// Restrict access exclusively to administrators with manage_settings permission
require_permission($pdo, 'manage_settings', 'Manage global site settings, mail drivers, and maintenance mode');

// Retrieve core system name safely using centralized helpers or fallback
$current_system_name = function_exists('get_system_name') ? get_system_name($pdo) : "Parish Records Directory (PRD)";

// Helper function to pull settings with fallback safely
$get_setting_val = function($pdo, $key, $default) {
    try {
        $stmt = $pdo->prepare("SELECT setting_value FROM site_settings WHERE setting_key = ?");
        $stmt->execute([$key]);
        $val = $stmt->fetchColumn();
        return ($val !== false && $val !== null) ? $val : $default;
    } catch (Exception $e) {
        return $default;
    }
};

$current_mail_domain = $get_setting_val($pdo, 'mail_domain', 'deballiolsociety.org.uk');
$current_mail_driver = $get_setting_val($pdo, 'mail_driver', 'mail');
$current_smtp_host = $get_setting_val($pdo, 'smtp_host', '');
$current_smtp_port = $get_setting_val($pdo, 'smtp_port', '587');
$current_smtp_user = $get_setting_val($pdo, 'smtp_user', '');
$current_smtp_encryption = $get_setting_val($pdo, 'smtp_encryption', 'tls');

// Fetch maintenance settings values dynamically
$maintenance_mode = $get_setting_val($pdo, 'maintenance_mode', '0');
$maintenance_reason = $get_setting_val($pdo, 'maintenance_reason', 'Scheduled system maintenance and database updates.');
$maintenance_eta = $get_setting_val($pdo, 'maintenance_eta', 'Shortly');

$message = $_SESSION['message'] ?? '';
$error = $_SESSION['error'] ?? '';
unset($_SESSION['message'], $_SESSION['error']);

// Fetch all existing notices
$notices = $pdo->query("SELECT * FROM site_notices ORDER BY display_order ASC, id DESC")->fetchAll(PDO::FETCH_ASSOC);
?>
<?php require_once '../partials/header.php'; ?>
<div class="search-box-container" role="region" aria-label="Site Settings Form" style="max-width: 900px; margin: 0 auto;">
    <h3>Global Site Settings, Notices & Permissions</h3>
    <p>Manage core configurations, mail drivers, server health, maintenance mode, application announcements, and role capabilities.</p>

    <?php if (!empty($error)): ?>
        <p class="alert-danger" role="alert"><strong><?php echo htmlspecialchars($error); ?></strong></p>
    <?php endif; ?>
    <?php if (!empty($message)): ?>
        <p class="alert-success" role="status"><strong><?php echo htmlspecialchars($message); ?></strong></p>
    <?php endif; ?>

    <!-- Accessible Tab Navigation -->
    <div role="tablist" aria-label="Settings Sections" style="display: flex; gap: 0.5rem; border-bottom: 2px solid var(--border-color); margin-bottom: 1.5rem; flex-wrap: wrap;">
        <button role="tab" aria-selected="true" aria-controls="panel-core" id="tab-core" onclick="switchTab('core')" class="tab-btn active-tab" style="padding: 0.5rem 1rem; cursor: pointer; border: none; background: none; font-weight: bold; border-bottom: 3px solid #007bff; margin-bottom: -2px;">Core & Mail</button>
        <button role="tab" aria-selected="false" aria-controls="panel-maintenance" id="tab-maintenance" onclick="switchTab('maintenance')" class="tab-btn" style="padding: 0.5rem 1rem; cursor: pointer; border: none; background: none; font-weight: bold; color: #666; border-bottom: 3px solid transparent; margin-bottom: -2px;">Maintenance</button>
        <button role="tab" aria-selected="false" aria-controls="panel-notices" id="tab-notices" onclick="switchTab('notices')" class="tab-btn" style="padding: 0.5rem 1rem; cursor: pointer; border: none; background: none; font-weight: bold; color: #666; border-bottom: 3px solid transparent; margin-bottom: -2px;">Site Notices</button>
        <button role="tab" aria-selected="false" aria-controls="panel-permissions" id="tab-permissions" onclick="switchTab('permissions')" class="tab-btn" style="padding: 0.5rem 1rem; cursor: pointer; border: none; background: none; font-weight: bold; color: #666; border-bottom: 3px solid transparent; margin-bottom: -2px;">Roles & Permissions</button>
    </div>

    <!-- TAB 1: Core & Mail Settings -->
    <div role="tabpanel" id="panel-core" aria-labelledby="tab-core" class="tab-panel">
        <form method="POST" action="actions/save_settings.php">
            <?php echo csrf_field(); ?>
            <h4 style="margin-top: 0; color: #333;">Core System Settings</h4>
            <div style="margin-bottom: 1rem;">
                <label for="system_name"><strong>System / Application Name:</strong></label><br>
                <input type="text" id="system_name" name="system_name" value="<?php echo htmlspecialchars($current_system_name); ?>" required class="volunteer-input" style="width: 100%; padding: 0.4rem; margin-top: 0.3rem;">
            </div>

            <h4 style="margin-top: 1.5rem; color: #333;">Mail Delivery Configuration</h4>
            <div style="margin-bottom: 1rem;">
                <label for="mail_domain"><strong>System Mail Domain (From Address / Envelope):</strong></label><br>
                <input type="text" id="mail_domain" name="mail_domain" value="<?php echo htmlspecialchars($current_mail_domain); ?>" placeholder="e.g. deballiolsociety.org.uk" required class="volunteer-input" style="width: 100%; padding: 0.4rem; margin-top: 0.3rem;">
                <small style="display: block; color: #666; margin-top: 0.2rem;">Used to stamp outgoing email headers independently of the web server URL.</small>
            </div>
            <div style="margin-bottom: 1rem;">
                <label for="mail_driver"><strong>Mail Driver / Engine:</strong></label><br>
                <select id="mail_driver" name="mail_driver" class="volunteer-input" style="width: 100%; padding: 0.4rem; margin-top: 0.3rem;" onchange="toggleSmtpFields(this.value)">
                    <option value="mail" <?php echo ($current_mail_driver === 'mail') ? 'selected' : ''; ?>>Native Mail (Local Postfix Relay)</option>
                    <option value="smtp" <?php echo ($current_mail_driver === 'smtp') ? 'selected' : ''; ?>>Authenticated SMTP (PHPMailer)</option>
                </select>
            </div>

            <!-- SMTP Configuration Block -->
            <div id="smtp_settings_block" style="background: rgba(0,0,0,0.02); padding: 1rem; border: 1px solid var(--border-color); border-radius: 6px; margin-bottom: 1.5rem; display: <?php echo ($current_mail_driver === 'smtp') ? 'block' : 'none'; ?>;">
                <h4 style="margin-top: 0; margin-bottom: 0.75rem; font-size: 1rem;">SMTP Server Configurations</h4>
                <div style="margin-bottom: 0.75rem;">
                    <label for="smtp_host" style="font-size: 0.85rem;">SMTP Host:</label><br>
                    <input type="text" id="smtp_host" name="smtp_host" value="<?php echo htmlspecialchars($current_smtp_host); ?>" placeholder="e.g. smtp.example.com" class="volunteer-input" style="width: 100%; padding: 0.3rem;">
                </div>
                <div style="display: flex; gap: 1rem; margin-bottom: 0.75rem;">
                    <div style="flex: 1;">
                        <label for="smtp_port" style="font-size: 0.85rem;">Port:</label><br>
                        <input type="number" id="smtp_port" name="smtp_port" value="<?php echo htmlspecialchars($current_smtp_port); ?>" class="volunteer-input" style="width: 100%; padding: 0.3rem;">
                    </div>
                    <div style="flex: 1;">
                        <label for="smtp_encryption" style="font-size: 0.85rem;">Encryption:</label><br>
                        <select id="smtp_encryption" name="smtp_encryption" class="volunteer-input" style="width: 100%; padding: 0.3rem;">
                            <option value="tls" <?php echo ($current_smtp_encryption === 'tls') ? 'selected' : ''; ?>>TLS (Port 587)</option>
                            <option value="ssl" <?php echo ($current_smtp_encryption === 'ssl') ? 'selected' : ''; ?>>SSL (Port 465)</option>
                        </select>
                    </div>
                </div>
                <div style="margin-bottom: 0.75rem;">
                    <label for="smtp_user" style="font-size: 0.85rem;">SMTP Username:</label><br>
                    <input type="text" id="smtp_user" name="smtp_user" value="<?php echo htmlspecialchars($current_smtp_user); ?>" class="volunteer-input" style="width: 100%; padding: 0.3rem;">
                </div>
                <div>
                    <label for="smtp_pass" style="font-size: 0.85rem;">SMTP Password (Leave blank to keep current):</label><br>
                    <input type="password" id="smtp_pass" name="smtp_pass" placeholder="••••••••" class="volunteer-input" style="width: 100%; padding: 0.3rem;">
                </div>
            </div>
            <button type="submit" class="btn">Save Core & Mail Settings</button>
        </form>

        <hr style="border: 0.0625rem solid var(--border-color); margin: 2rem 0;">
        
        <!-- Test Mail Sub-section -->
        <h4 style="margin-bottom: 0.5rem; color: #333;">Test Mail Configuration</h4>
        <p style="font-size: 0.9rem; color: #666; margin-bottom: 1rem;">Send a test message using your current mail driver to verify delivery integrity.</p>
        <form method="POST" action="actions/test_mail.php">
            <?php echo csrf_field(); ?>
            <label for="test_email" style="font-size: 0.85rem;"><strong>Recipient Email Address:</strong></label><br>
            <div style="display: flex; gap: 0.5rem; margin-top: 0.3rem;">
                <input type="email" id="test_email" name="test_email" placeholder="admin@example.com" required class="volunteer-input" style="flex: 1; padding: 0.4rem;">
                <button type="submit" class="btn btn-secondary" style="white-space: nowrap;">Send Test Email</button>
            </div>
        </form>
    </div>

    <!-- TAB 2: Maintenance Mode Settings -->
    <div role="tabpanel" id="panel-maintenance" aria-labelledby="tab-maintenance" class="tab-panel" style="display: none;">
        <form method="POST" action="actions/save_maintenance.php">
            <?php echo csrf_field(); ?>
            <h4 style="margin-top: 0; color: #333;">System Maintenance Mode</h4>
            <p style="font-size: 0.9rem; color: #666; margin-bottom: 1rem;">Take the application offline for updates. Only administrators will retain access.</p>
            <div style="margin-bottom: 1rem;">
                <label style="cursor: pointer;">
                    <input type="checkbox" name="maintenance_mode" value="1" <?php echo ($maintenance_mode === '1') ? 'checked' : ''; ?>> 
                    <strong>Enable Maintenance Mode (Take Site Offline)</strong>
                </label>
            </div>
            <div style="margin-bottom: 1rem;">
                <label for="maintenance_reason" style="font-size: 0.85rem;"><strong>Reason / Message for Users:</strong></label><br>
                <textarea id="maintenance_reason" name="maintenance_reason" rows="3" class="volunteer-input" style="width: 100%; padding: 0.4rem; margin-top: 0.3rem;" required><?php echo htmlspecialchars($maintenance_reason); ?></textarea>
            </div>
            <div style="margin-bottom: 1.5rem;">
                <label for="maintenance_eta" style="font-size: 0.85rem;"><strong>Expected Return Time (ETA):</strong></label><br>
                <input type="text" id="maintenance_eta" name="maintenance_eta" value="<?php echo htmlspecialchars($maintenance_eta); ?>" class="volunteer-input" style="width: 100%; max-width: 300px; padding: 0.4rem; margin-top: 0.3rem;" required>
            </div>
            <button type="submit" class="btn btn-danger">Save Maintenance Settings</button>
        </form>
    </div>

    <!-- TAB 3: Site Notices -->
    <div role="tabpanel" id="panel-notices" aria-labelledby="tab-notices" class="tab-panel" style="display: none;">
        <div style="display: flex; justify-content: space-between; align-items: center; margin-bottom: 1rem;">
            <h4 style="margin: 0; color: #333;">Site Notices & Announcements</h4>
            <a href="notices.php" class="btn btn-secondary" style="font-size: 0.85rem; text-decoration: none;">+ Add New Notice</a>
        </div>
        <p style="font-size: 0.9rem; color: #666; margin-bottom: 1.5rem;">Expand any notice accordion section below to manage content, targeting roles, and visibility flags.</p>
        
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
                                <?php echo csrf_field(); ?>
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
                                        <input type="checkbox" name="target_roles[]" value="guest" class="role-checkbox-<?php echo $n['id']; ?>" <?php echo ($is_everyone || in_array('guest', $selected_roles) || in_array('public', $selected_roles)) ? 'checked' : ''; ?> <?php echo $is_everyone ? 'disabled style="opacity:0.6;"' : ''; ?>> Guest (Public)
                                    </label>
                                    <label style="font-weight: normal; font-size: 0.85rem; cursor: pointer;">
                                        <input type="checkbox" name="target_roles[]" value="user" class="role-checkbox-<?php echo $n['id']; ?>" <?php echo ($is_everyone || in_array('user', $selected_roles)) ? 'checked' : ''; ?> <?php echo $is_everyone ? 'disabled style="opacity:0.6;"' : ''; ?>> Users
                                    </label>
                                    <label style="font-weight: normal; font-size: 0.85rem; cursor: pointer;">
                                        <input type="checkbox" name="target_roles[]" value="moderator" class="role-checkbox-<?php echo $n['id']; ?>" <?php echo ($is_everyone || in_array('moderator', $selected_roles)) ? 'checked' : ''; ?> <?php echo $is_everyone ? 'disabled style="opacity:0.6;"' : ''; ?>> Moderators
                                    </label>
                                    <label style="font-weight: normal; font-size: 0.85rem; cursor: pointer;">
                                        <input type="checkbox" name="target_roles[]" value="admin" class="role-checkbox-<?php echo $n['id']; ?>" <?php echo ($is_everyone || in_array('admin', $selected_roles)) ? 'checked' : ''; ?> <?php echo $is_everyone ? 'disabled style="opacity:0.6;"' : ''; ?>> Administrators
                                    </label>
                                </div>
                                <div style="display: flex; gap: 1.5rem; margin-bottom: 1rem; font-size: 0.9rem; align-items: center;">
                                    <label><input type="checkbox" name="is_dismissible" value="1" <?php echo $n['is_dismissible'] ? 'checked' : ''; ?>> Dismissible (Has 'X')</label>
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

    <!-- TAB 4: Roles & Permissions Matrix -->
    <div role="tabpanel" id="panel-permissions" aria-labelledby="tab-permissions" class="tab-panel" style="display: none;">
        <h4 style="margin-top: 0; color: #333;">Dynamic Role & Permission Matrix</h4>
        <p style="font-size: 0.9rem; color: #666; margin-bottom: 1.5rem;">Configure which capabilities are assigned to each system role. Permissions defined in your code automatically register here.</p>

        <!-- Add Custom Role Sub-section -->
        <div style="background: rgba(0,0,0,0.02); padding: 1.25rem; border: 1px solid var(--border-color); border-radius: 6px; margin-bottom: 2rem;">
            <h5 style="margin-top: 0; margin-bottom: 0.5rem; color: #333;">Create New Custom Role</h5>
            <p style="font-size: 0.85rem; color: #666; margin-bottom: 1rem;">Add a new role category (e.g., 'archivist', 'data_clerk') to assign capabilities below.</p>
            
            <form method="POST" action="actions/save_role.php" style="display: flex; gap: 1rem; align-items: flex-end; flex-wrap: wrap;">
                <?php echo csrf_field(); ?>
                <div style="flex: 1; min-width: 200px;">
                    <label for="role_name" style="font-size: 0.85rem;">Role Identifier Name:</label><br>
                    <input type="text" id="role_name" name="role_name" placeholder="e.g. archivist" required class="volunteer-input" style="width: 100%; padding: 0.35rem; margin-top: 0.2rem;">
                </div>
                <div style="flex: 2; min-width: 250px;">
                    <label for="role_description" style="font-size: 0.85rem;">Description / Purpose:</label><br>
                    <input type="text" id="role_description" name="description" placeholder="e.g. Specialized access for historical document archiving" class="volunteer-input" style="width: 100%; padding: 0.35rem; margin-top: 0.2rem;">
                </div>
                <div>
                    <button type="submit" class="btn" style="white-space: nowrap;">Create Role</button>
                </div>
            </form>
        </div>

        <?php
        // Fetch roles and permissions dynamically
        $roles_list = $pdo->query("SELECT * FROM roles ORDER BY id ASC")->fetchAll(PDO::FETCH_ASSOC);
        $perms_list = $pdo->query("SELECT * FROM permissions ORDER BY id ASC")->fetchAll(PDO::FETCH_ASSOC);
        
        // Fetch current mappings
        $active_mappings = [];
        $map_rows = $pdo->query("SELECT role_id, permission_id FROM role_permissions")->fetchAll(PDO::FETCH_ASSOC);
        foreach ($map_rows as $m) {
            $active_mappings[$m['role_id']][$m['permission_id']] = true;
        }
        ?>

        <form method="POST" action="actions/save_permissions.php">
            <?php echo csrf_field(); ?>
            <div style="overflow-x: auto;">
                <table class="data-table" style="width: 100%; border-collapse: collapse; text-align: left;">
                    <thead>
                        <tr style="border-bottom: 2px solid var(--border-color);">
                            <th style="padding: 0.75rem;">Permission / Capability</th>
                            <th style="padding: 0.75rem;">Description</th>
                            <?php foreach ($roles_list as $r): ?>
                                <th style="padding: 0.75rem; text-align: center; text-transform: capitalize;"><?php echo htmlspecialchars($r['role_name']); ?></th>
                            <?php endforeach; ?>
                        </tr>
                    </thead>
                    <tbody>
                        <?php foreach ($perms_list as $p): ?>
                            <tr style="border-bottom: 1px solid var(--border-color);">
                                <td style="padding: 0.75rem;"><strong><?php echo htmlspecialchars($p['permission_key']); ?></strong></td>
                                <td style="padding: 0.75rem; color: #666; font-size: 0.85rem;"><?php echo htmlspecialchars($p['description']); ?></td>
                                <?php foreach ($roles_list as $r): ?>
                                    <td style="padding: 0.75rem; text-align: center;">
                                        <?php 
                                            $is_checked = isset($active_mappings[$r['id']][$p['id']]);
                                            $is_locked_admin = ($r['role_name'] === 'admin' && $p['permission_key'] === 'manage_settings');
                                        ?>
                                        <input type="checkbox" name="permissions[<?php echo $r['id']; ?>][<?php echo $p['id']; ?>]" value="1" <?php echo $is_checked ? 'checked' : ''; ?> <?php echo $is_locked_admin ? 'onclick="return false;" title="Admin settings permission is locked for safety"' : ''; ?> style="cursor: pointer; transform: scale(1.2);">
                                    </td>
                                <?php endforeach; ?>
                            </tr>
                        <?php endforeach; ?>
                    </tbody>
                </table>
            </div>
            <div style="margin-top: 1.5rem;">
                <button type="submit" class="btn">Save Permissions Matrix</button>
            </div>
        </form>
    </div>
</div>

<script>
function switchTab(tabId) {
    // Hide all panels
    document.querySelectorAll('.tab-panel').forEach(panel => panel.style.display = 'none');
    
    // Remove active styles from all tab buttons
    document.querySelectorAll('.tab-btn').forEach(btn => {
        btn.style.color = '#666';
        btn.style.borderBottomColor = 'transparent';
        btn.setAttribute('aria-selected', 'false');
    });

    // Show selected panel
    document.getElementById('panel-' + tabId).style.display = 'block';
    
    // Highlight selected tab button
    const activeBtn = document.getElementById('tab-' + tabId);
    activeBtn.style.color = '#000';
    activeBtn.style.borderBottomColor = '#007bff';
    activeBtn.setAttribute('aria-selected', 'true');
}

function toggleSmtpFields(val) {
    const block = document.getElementById('smtp_settings_block');
    block.style.display = (val === 'smtp') ? 'block' : 'none';
}

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
