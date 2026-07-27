<?php
// admin/settings.php - Global Site Settings, Maintenance, Notices, Permissions & Modules Interface
require_once '../db/db.php';
require_once '../db/auth_helpers.php';
require_once '../includes/functions.php';
session_start();
require_permission($pdo, 'manage_settings', 'Manage global site settings, mail drivers, and maintenance mode');

try {
    $existing_tables = $pdo->query("SELECT id, table_name FROM dynamic_tables")->fetchAll(PDO::FETCH_ASSOC);
    foreach ($existing_tables as $et) {
        $t_id = $et['id'];
        $t_name = $et['table_name'];
        $view_key = 'view_table_' . $t_id;
        $view_desc = 'Allows viewing and searching records in table: ' . $t_name;
        $mod_key = 'moderate_table_' . $t_id;
        $mod_desc = 'Allows reviewing and moderating suggestions in table: ' . $t_name;
        
        $ins_p = $pdo->prepare("INSERT IGNORE INTO permissions (permission_key, description) VALUES (?, ?)");
        $ins_p->execute([$view_key, $view_desc]);
        $ins_p->execute([$mod_key, $mod_desc]);
    }
} catch (Exception $e) {}

$current_system_name = function_exists('get_system_name') ? get_system_name($pdo) : "Parish Records Directory (PRD)";

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

$maintenance_mode = $get_setting_val($pdo, 'maintenance_mode', '0');
$maintenance_reason = $get_setting_val($pdo, 'maintenance_reason', 'Scheduled system maintenance and database updates.');
$maintenance_eta = $get_setting_val($pdo, 'maintenance_eta', 'Shortly');

// Module toggles state
$mod_moderation_val  = $get_setting_val($pdo, 'module_moderation_enabled', '1');
$mod_volunteers_val  = $get_setting_val($pdo, 'module_volunteers_enabled', '1');
$mod_feedback_val    = $get_setting_val($pdo, 'module_feedback_enabled', '1');
$mod_users_val       = $get_setting_val($pdo, 'module_users_enabled', '1');
$mod_leaderboard_val = $get_setting_val($pdo, 'module_leaderboard_enabled', '1');

$message = $_SESSION['message'] ?? '';
$error = $_SESSION['error'] ?? '';
unset($_SESSION['message'], $_SESSION['error']);

$notices = $pdo->query("SELECT * FROM site_notices ORDER BY display_order ASC, id DESC")->fetchAll(PDO::FETCH_ASSOC);

$edit_role = null;
if (isset($_GET['edit_role'])) {
    $edit_role_id = intval($_GET['edit_role']);
    $r_stmt = $pdo->prepare("SELECT * FROM roles WHERE id = ?");
    $r_stmt->execute([$edit_role_id]);
    $edit_role = $r_stmt->fetch(PDO::FETCH_ASSOC);
}
?>
<?php require_once '../partials/header.php'; ?>
<div class="search-box-container" role="region" aria-label="Site Settings Form" style="max-width: 1100px; margin: 0 auto; font-size: 1rem;">
    <h3 style="font-size: 1.5rem; margin-bottom: 0.5rem;">Global Site Settings, Modules & Permissions</h3>
    <p style="font-size: 1rem; color: #555; margin-bottom: 1.5rem;">Manage core configurations, mail drivers, feature modules, maintenance mode, site announcements, and role capabilities.</p>
    <?php if (!empty($error)): ?>
        <p class="alert-danger" role="alert" style="font-size: 1rem;"><strong><?php echo htmlspecialchars($error); ?></strong></p>
    <?php endif; ?>
    <?php if (!empty($message)): ?>
        <p class="alert-success" role="status" style="font-size: 1rem;"><strong><?php echo htmlspecialchars($message); ?></strong></p>
    <?php endif; ?>
    <!-- Accessible Tab Navigation -->
    <div role="tablist" aria-label="Settings Sections" style="display: flex; gap: 0.75rem; border-bottom: 2px solid var(--border-color); margin-bottom: 2rem; flex-wrap: wrap;">
        <button role="tab" aria-selected="true" aria-controls="panel-core" id="tab-core" onclick="switchTab('core')" class="tab-btn active-tab" style="padding: 0.75rem 1.25rem; cursor: pointer; border: none; background: none; font-size: 1.05rem; font-weight: bold; border-bottom: 3px solid #007bff; margin-bottom: -2px;">Core & Mail</button>
        <button role="tab" aria-selected="false" aria-controls="panel-modules" id="tab-modules" onclick="switchTab('modules')" class="tab-btn" style="padding: 0.75rem 1.25rem; cursor: pointer; border: none; background: none; font-size: 1.05rem; font-weight: bold; color: #555; border-bottom: 3px solid transparent; margin-bottom: -2px;">Modules</button>
        <button role="tab" aria-selected="false" aria-controls="panel-maintenance" id="tab-maintenance" onclick="switchTab('maintenance')" class="tab-btn" style="padding: 0.75rem 1.25rem; cursor: pointer; border: none; background: none; font-size: 1.05rem; font-weight: bold; color: #555; border-bottom: 3px solid transparent; margin-bottom: -2px;">Maintenance</button>
        <button role="tab" aria-selected="false" aria-controls="panel-notices" id="tab-notices" onclick="switchTab('notices')" class="tab-btn" style="padding: 0.75rem 1.25rem; cursor: pointer; border: none; background: none; font-size: 1.05rem; font-weight: bold; color: #555; border-bottom: 3px solid transparent; margin-bottom: -2px;">Site Notices</button>
        <button role="tab" aria-selected="false" aria-controls="panel-permissions" id="tab-permissions" onclick="switchTab('permissions')" class="tab-btn" style="padding: 0.75rem 1.25rem; cursor: pointer; border: none; background: none; font-size: 1.05rem; font-weight: bold; color: #555; border-bottom: 3px solid transparent; margin-bottom: -2px;">Roles & Permissions</button>
    </div>
    <!-- TAB 1: Core & Mail Settings -->
    <div role="tabpanel" id="panel-core" aria-labelledby="tab-core" class="tab-panel">
        <form method="POST" action="actions/save_settings.php">
            <?php echo csrf_field(); ?>
            <h4 style="margin-top: 0; color: #333; font-size: 1.2rem;">Core System Settings</h4>
            <div style="margin-bottom: 1.25rem;">
                <label for="system_name" style="font-size: 1rem;"><strong>System / Application Name:</strong></label><br>
                <input type="text" id="system_name" name="system_name" value="<?php echo htmlspecialchars($current_system_name); ?>" required class="volunteer-input" style="width: 100%; padding: 0.6rem; font-size: 1rem; margin-top: 0.4rem;">
            </div>
            <h4 style="margin-top: 2rem; color: #333; font-size: 1.2rem;">Mail Delivery Configuration</h4>
            <div style="margin-bottom: 1.25rem;">
                <label for="mail_domain" style="font-size: 1rem;"><strong>System Mail Domain (From Address / Envelope):</strong></label><br>
                <input type="text" id="mail_domain" name="mail_domain" value="<?php echo htmlspecialchars($current_mail_domain); ?>" placeholder="e.g. deballiolsociety.org.uk" required class="volunteer-input" style="width: 100%; padding: 0.6rem; font-size: 1rem; margin-top: 0.4rem;">
            </div>
            <div style="margin-bottom: 1.25rem;">
                <label for="mail_driver" style="font-size: 1rem;"><strong>Mail Driver / Engine:</strong></label><br>
                <select id="mail_driver" name="mail_driver" class="volunteer-input" style="width: 100%; padding: 0.6rem; font-size: 1rem; margin-top: 0.4rem;" onchange="toggleSmtpFields(this.value)">
                    <option value="mail" <?php echo ($current_mail_driver === 'mail') ? 'selected' : ''; ?>>Native Mail (Local Postfix Relay)</option>
                    <option value="smtp" <?php echo ($current_mail_driver === 'smtp') ? 'selected' : ''; ?>>Authenticated SMTP (PHPMailer)</option>
                </select>
            </div>
            <div id="smtp_settings_block" style="background: rgba(0,0,0,0.02); padding: 1.25rem; border: 1px solid var(--border-color); border-radius: 6px; margin-bottom: 1.5rem; display: <?php echo ($current_mail_driver === 'smtp') ? 'block' : 'none'; ?>;">
                <h4 style="margin-top: 0; margin-bottom: 1rem; font-size: 1.1rem;">SMTP Server Configurations</h4>
                <div style="margin-bottom: 1rem;">
                    <label for="smtp_host" style="font-size: 0.95rem;">SMTP Host:</label><br>
                    <input type="text" id="smtp_host" name="smtp_host" value="<?php echo htmlspecialchars($current_smtp_host); ?>" placeholder="e.g. smtp.example.com" class="volunteer-input" style="width: 100%; padding: 0.5rem; font-size: 0.95rem;">
                </div>
                <div style="display: flex; gap: 1rem; margin-bottom: 1rem;">
                    <div style="flex: 1;">
                        <label for="smtp_port" style="font-size: 0.95rem;">Port:</label><br>
                        <input type="number" id="smtp_port" name="smtp_port" value="<?php echo htmlspecialchars($current_smtp_port); ?>" class="volunteer-input" style="width: 100%; padding: 0.5rem; font-size: 0.95rem;">
                    </div>
                    <div style="flex: 1;">
                        <label for="smtp_encryption" style="font-size: 0.95rem;">Encryption:</label><br>
                        <select id="smtp_encryption" name="smtp_encryption" class="volunteer-input" style="width: 100%; padding: 0.5rem; font-size: 0.95rem;">
                            <option value="tls" <?php echo ($current_smtp_encryption === 'tls') ? 'selected' : ''; ?>>TLS (Port 587)</option>
                            <option value="ssl" <?php echo ($current_smtp_encryption === 'ssl') ? 'selected' : ''; ?>>SSL (Port 465)</option>
                        </select>
                    </div>
                </div>
                <div style="margin-bottom: 1rem;">
                    <label for="smtp_user" style="font-size: 0.95rem;">SMTP Username:</label><br>
                    <input type="text" id="smtp_user" name="smtp_user" value="<?php echo htmlspecialchars($current_smtp_user); ?>" class="volunteer-input" style="width: 100%; padding: 0.5rem; font-size: 0.95rem;">
                </div>
                <div>
                    <label for="smtp_pass" style="font-size: 0.95rem;">SMTP Password (Leave blank to keep current):</label><br>
                    <input type="password" id="smtp_pass" name="smtp_pass" placeholder="••••••••" class="volunteer-input" style="width: 100%; padding: 0.5rem; font-size: 0.95rem;">
                </div>
            </div>
            <button type="submit" class="btn" style="padding: 0.6rem 1.2rem; font-size: 1rem;">Save Core & Mail Settings</button>
        </form>
        <hr style="border: 0.0625rem solid var(--border-color); margin: 2rem 0;">
        <h4 style="margin-bottom: 0.75rem; color: #333; font-size: 1.2rem;">Test Mail Configuration</h4>
        <form method="POST" action="actions/test_mail.php">
            <?php echo csrf_field(); ?>
            <label for="test_email" style="font-size: 0.95rem;"><strong>Recipient Email Address:</strong></label><br>
            <div style="display: flex; gap: 0.75rem; margin-top: 0.4rem;">
                <input type="email" id="test_email" name="test_email" placeholder="admin@example.com" required class="volunteer-input" style="flex: 1; padding: 0.6rem; font-size: 1rem;">
                <button type="submit" class="btn btn-secondary" style="white-space: nowrap; padding: 0.6rem 1.2rem; font-size: 1rem;">Send Test Email</button>
            </div>
        </form>
    </div>
    <!-- TAB 2: Modules Management -->
    <div role="tabpanel" id="panel-modules" aria-labelledby="tab-modules" class="tab-panel" style="display: none;">
        <form method="POST" action="actions/save_modules.php">
            <?php echo csrf_field(); ?>
            <h4 style="margin-top: 0; color: #333; font-size: 1.2rem;">Application Module Toggles & Efficiency Controls</h4>
            <p style="font-size: 1rem; color: #555; margin-bottom: 1.5rem;">Enable or disable features to optimize application execution efficiency and adapt PRD to your specific deployment needs.</p>
            
            <div style="display: flex; flex-direction: column; gap: 1.25rem;">
                <div style="background: rgba(0,0,0,0.02); padding: 1.25rem; border: 1px solid var(--border-color); border-radius: 6px;">
                    <label style="cursor: pointer; font-weight: bold; font-size: 1.05rem; display: flex; align-items: center; gap: 0.75rem;">
                        <input type="checkbox" name="module_users_enabled" id="module_users_enabled" value="1" <?php echo ($mod_users_val === '1') ? 'checked' : ''; ?> onchange="handleUserManagementToggle(this);" style="transform: scale(1.3);">
                        <span>User Management & Multi-User Access</span>
                    </label>
                    <p style="margin: 0.4rem 0 0 2rem; font-size: 0.95rem; color: #555;">Enables registration, user management, and multi-user authentication. (Profile access remains available for single-user security).</p>
                </div>
                <div style="background: rgba(0,0,0,0.02); padding: 1.25rem; border: 1px solid var(--border-color); border-radius: 6px;">
                    <label style="cursor: pointer; font-weight: bold; font-size: 1.05rem; display: flex; align-items: center; gap: 0.75rem;" id="leaderboard_label_wrapper">
                        <input type="checkbox" name="module_leaderboard_enabled" id="module_leaderboard_enabled" value="1" <?php echo ($mod_leaderboard_val === '1' && $mod_users_val === '1') ? 'checked' : ''; ?> <?php echo ($mod_users_val !== '1') ? 'disabled' : ''; ?> style="transform: scale(1.3);">
                        <span>Leaderboard & Gamification</span>
                    </label>
                    <p style="margin: 0.4rem 0 0 2rem; font-size: 0.95rem; color: #555;" id="leaderboard_desc">
                        Recognizes transcription efforts and star scores. <span id="leaderboard_dependency_note" style="color: #dc3545; font-weight: bold; display: <?php echo ($mod_users_val !== '1') ? 'inline' : 'none'; ?>;">(Requires User Management & Multi-User Access)</span>
                    </p>
                </div>
                <div style="background: rgba(0,0,0,0.02); padding: 1.25rem; border: 1px solid var(--border-color); border-radius: 6px;">
                    <label style="cursor: pointer; font-weight: bold; font-size: 1.05rem; display: flex; align-items: center; gap: 0.75rem;">
                        <input type="checkbox" name="module_moderation_enabled" value="1" <?php echo ($mod_moderation_val === '1') ? 'checked' : ''; ?> style="transform: scale(1.3);">
                        <span>Moderation Workflow</span>
                    </label>
                    <p style="margin: 0.4rem 0 0 2rem; font-size: 0.95rem; color: #555;">Enables edit suggestions review and moderation queue.</p>
                </div>
                <div style="background: rgba(0,0,0,0.02); padding: 1.25rem; border: 1px solid var(--border-color); border-radius: 6px;">
                    <label style="cursor: pointer; font-weight: bold; font-size: 1.05rem; display: flex; align-items: center; gap: 0.75rem;">
                        <input type="checkbox" name="module_volunteers_enabled" value="1" <?php echo ($mod_volunteers_val === '1') ? 'checked' : ''; ?> style="transform: scale(1.3);">
                        <span>Volunteer Portal & Submissions</span>
                    </label>
                    <p style="margin: 0.4rem 0 0 2rem; font-size: 0.95rem; color: #555;">Enables public volunteer interest form and admin management dashboard.</p>
                </div>
                <div style="background: rgba(0,0,0,0.02); padding: 1.25rem; border: 1px solid var(--border-color); border-radius: 6px;">
                    <label style="cursor: pointer; font-weight: bold; font-size: 1.05rem; display: flex; align-items: center; gap: 0.75rem;">
                        <input type="checkbox" name="module_feedback_enabled" value="1" <?php echo ($mod_feedback_val === '1') ? 'checked' : ''; ?> style="transform: scale(1.3);">
                        <span>Feedback Submissions</span>
                    </label>
                    <p style="margin: 0.4rem 0 0 2rem; font-size: 0.95rem; color: #555;">Enables public feedback form and admin tracking dashboard.</p>
                </div>
            </div>
            <div style="margin-top: 1.5rem;">
                <button type="submit" class="btn" style="padding: 0.6rem 1.2rem; font-size: 1rem;">Save Module Configurations</button>
            </div>
        </form>
    </div>
    <!-- TAB 3: Maintenance Mode Settings -->
    <div role="tabpanel" id="panel-maintenance" aria-labelledby="tab-maintenance" class="tab-panel" style="display: none;">
        <form method="POST" action="actions/save_maintenance.php">
            <?php echo csrf_field(); ?>
            <h4 style="margin-top: 0; color: #333; font-size: 1.2rem;">System Maintenance Mode</h4>
            <div style="margin-bottom: 1.25rem;">
                <label style="cursor: pointer; font-size: 1.05rem;">
                    <input type="checkbox" name="maintenance_mode" value="1" <?php echo ($maintenance_mode === '1') ? 'checked' : ''; ?> style="transform: scale(1.2); margin-right: 0.5rem;"> 
                    <strong>Enable Maintenance Mode (Take Site Offline)</strong>
                </label>
            </div>
            <div style="margin-bottom: 1.25rem;">
                <label for="maintenance_reason" style="font-size: 1rem;"><strong>Reason / Message for Users:</strong></label><br>
                <textarea id="maintenance_reason" name="maintenance_reason" rows="3" class="volunteer-input" style="width: 100%; padding: 0.6rem; font-size: 1rem; margin-top: 0.4rem;" required><?php echo htmlspecialchars($maintenance_reason); ?></textarea>
            </div>
            <div style="margin-bottom: 1.5rem;">
                <label for="maintenance_eta" style="font-size: 1rem;"><strong>Expected Return Time (ETA):</strong></label><br>
                <input type="text" id="maintenance_eta" name="maintenance_eta" value="<?php echo htmlspecialchars($maintenance_eta); ?>" class="volunteer-input" style="width: 100%; max-width: 350px; padding: 0.6rem; font-size: 1rem; margin-top: 0.4rem;" required>
            </div>
            <button type="submit" class="btn btn-danger" style="padding: 0.6rem 1.2rem; font-size: 1rem;">Save Maintenance Settings</button>
        </form>
    </div>
    <!-- TAB 4: Site Notices -->
    <div role="tabpanel" id="panel-notices" aria-labelledby="tab-notices" class="tab-panel" style="display: none;">
        <div style="display: flex; justify-content: space-between; align-items: center; margin-bottom: 1.25rem;">
            <h4 style="margin: 0; color: #333; font-size: 1.2rem;">Site Notices & Announcements</h4>
            <a href="notices.php" class="btn btn-secondary" style="font-size: 0.95rem; text-decoration: none; padding: 0.5rem 1rem;">+ Add New Notice</a>
        </div>
        <?php if (empty($notices)): ?>
            <p style="padding: 1.5rem; background: rgba(0,0,0,0.02); border-radius: 6px; text-align: center; color: #555; font-size: 1rem;">No notices configured yet.</p>
        <?php else: ?>
            <div class="accordion-container" style="display: flex; flex-direction: column; gap: 1rem;">
                <?php foreach ($notices as $n): ?>
                    <details style="background: #fdfdfd; border: 1px solid var(--border-color, #ccc); border-radius: 6px; padding: 1rem 1.25rem;">
                        <summary style="cursor: pointer; font-weight: bold; color: #333; font-size: 1.05rem; display: flex; justify-content: space-between; align-items: center;">
                            <span><?php echo htmlspecialchars($n['title']); ?></span>
                            <span style="font-size: 0.85rem; padding: 0.25rem 0.6rem; background: <?php echo $n['is_active'] ? '#d4edda; color: #155724;' : '#f8d7da; color: #721c24;'; ?> border-radius: 4px;"><?php echo $n['is_active'] ? 'Active' : 'Inactive'; ?></span>
                        </summary>
                        <div style="margin-top: 1rem; padding-top: 1rem; border-top: 1px solid #eee;">
                            <form method="POST" action="actions/save_notice_inline.php">
                                <?php echo csrf_field(); ?>
                                <input type="hidden" name="notice_id" value="<?php echo $n['id']; ?>">
                                <label style="font-size: 0.9rem; font-weight: bold;">Title:</label>
                                <input type="text" name="title" value="<?php echo htmlspecialchars($n['title']); ?>" class="volunteer-input" style="width: 100%; margin-bottom: 0.75rem; padding: 0.5rem; font-size: 0.95rem;" required><br>
                                <label style="font-size: 0.9rem; font-weight: bold;">Content:</label>
                                <textarea name="content" rows="3" class="volunteer-input" style="width: 100%; margin-bottom: 0.75rem; padding: 0.5rem; font-size: 0.95rem;" required><?php echo htmlspecialchars($n['content']); ?></textarea><br>
                                <button type="submit" name="update_action" value="save" class="btn" style="font-size: 0.95rem; padding: 0.4rem 1rem;">Save Notice</button>
                            </form>
                        </div>
                    </details>
                <?php endforeach; ?>
            </div>
        <?php endif; ?>
    </div>
    <!-- TAB 5: Roles & Permissions Matrix (Collapsible Grouped Sections with Global Save) -->
    <div role="tabpanel" id="panel-permissions" aria-labelledby="tab-permissions" class="tab-panel" style="display: none;">
        <h4 style="margin-top: 0; color: #333; font-size: 1.2rem;">Dynamic Role & Permission Matrix</h4>
        <p style="font-size: 1rem; color: #555; margin-bottom: 1.5rem;">Permissions are grouped by system functions. Expand sections to configure capabilities, then save your updates at the bottom.</p>
        <?php
        $roles_list = $pdo->query("SELECT * FROM roles ORDER BY id ASC")->fetchAll(PDO::FETCH_ASSOC);
        $perms_list = $pdo->query("SELECT * FROM permissions ORDER BY id ASC")->fetchAll(PDO::FETCH_ASSOC);
        $active_mappings = [];
        $map_rows = $pdo->query("SELECT role_id, permission_id FROM role_permissions")->fetchAll(PDO::FETCH_ASSOC);
        foreach ($map_rows as $m) {
            $active_mappings[$m['role_id']][$m['permission_id']] = true;
        }

        // Module status checks for filtering permission groups
        $mod_users_active = is_module_enabled($pdo, 'users');
        $mod_volunteers_active = is_module_enabled($pdo, 'volunteers');
        $mod_feedback_active = is_module_enabled($pdo, 'feedback');
        $mod_moderation_active = is_module_enabled($pdo, 'moderation');
        $mod_leaderboard_active = is_module_enabled($pdo, 'leaderboard');

        // Helper categorization function for permissions
        function get_permission_category($pkey) {
            if (str_starts_with($pkey, 'view_table_') || str_starts_with($pkey, 'moderate_table_')) {
                return 'Dynamic Tables & Records';
            }
            if (in_array($pkey, ['manage_users', 'invite_users', 'access_onboarding', 'view_leaderboard'])) {
                return 'Users & Gamification Module';
            }
            if (in_array($pkey, ['manage_volunteers', 'submit_volunteer', 'manage_feedback', 'submit_feedback'])) {
                return 'Portals & Submissions Module';
            }
            if (in_array($pkey, ['access_suggest_edit', 'moderate_submissions', 'manage_feedback'])) {
                return 'Moderation Workflow';
            }
            return 'Core System & Settings';
        }

        $categorized_perms = [];
        foreach ($perms_list as $p) {
            $pkey = $p['permission_key'];
            // Filter out permissions whose parent module is currently disabled
            if (($pkey === 'manage_users' || $pkey === 'invite_users' || $pkey === 'access_onboarding') && !$mod_users_active) continue;
            if (($pkey === 'manage_volunteers' || $pkey === 'submit_volunteer') && !$mod_volunteers_active) continue;
            if (($pkey === 'manage_feedback' || $pkey === 'submit_feedback') && !$mod_feedback_active) continue;
            if (($pkey === 'access_suggest_edit') && !$mod_moderation_active) continue;
            if (($pkey === 'view_leaderboard') && !$mod_leaderboard_active) continue;

            $cat = get_permission_category($pkey);
            $categorized_perms[$cat][] = $p;
        }
        ?>
        <form method="POST" action="actions/save_permissions.php">
            <?php echo csrf_field(); ?>
            <div style="display: flex; flex-direction: column; gap: 1rem;">
                <?php foreach ($categorized_perms as $category_name => $cat_perms): ?>
                    <details style="background: #fafafa; border: 1px solid var(--border-color); border-radius: 8px; padding: 1rem 1.25rem;">
                        <summary style="cursor: pointer; font-weight: bold; color: #007bff; font-size: 1.1rem; outline: none; display: flex; justify-content: space-between; align-items: center;">
                            <span><?php echo htmlspecialchars($category_name); ?> <span style="font-weight: normal; color: #666; font-size: 0.85rem;">(<?php echo count($cat_perms); ?> permissions)</span></span>
                        </summary>
                        
                        <div style="margin-top: 1rem; padding-top: 1rem; border-top: 1px solid #e9ecef; overflow-x: auto;">
                            <table class="data-table" style="width: 100%; border-collapse: collapse; text-align: left; background: #fff;">
                                <thead>
                                    <tr style="border-bottom: 2px solid var(--border-color); background: #f8f9fa;">
                                        <th style="padding: 0.75rem; width: 25%;">Role</th>
                                        <th style="padding: 0.75rem; width: 75%;">Assigned Capabilities in this Group</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    <?php foreach ($roles_list as $r): ?>
                                        <tr style="border-bottom: 1px solid var(--border-color);">
                                            <td style="padding: 0.85rem; font-weight: bold; text-transform: capitalize; vertical-align: top;">
                                                <?php echo htmlspecialchars($r['role_name']); ?>
                                            </td>
                                            <td style="padding: 0.85rem;">
                                                <div style="display: flex; flex-wrap: wrap; gap: 1rem;">
                                                    <?php foreach ($cat_perms as $p): ?>
                                                        <label style="cursor: pointer; display: inline-flex; align-items: center; gap: 0.4rem; font-size: 0.9rem; background: #f1f3f5; padding: 0.3rem 0.6rem; border-radius: 4px;" title="<?php echo htmlspecialchars($p['description']); ?>">
                                                            <input type="checkbox" name="permissions[<?php echo $r['id']; ?>][<?php echo $p['id']; ?>]" value="1" <?php echo isset($active_mappings[$r['id']][$p['id']]) ? 'checked' : ''; ?> style="cursor: pointer; transform: scale(1.1);">
                                                            <span><?php echo htmlspecialchars($p['permission_key']); ?></span>
                                                        </label>
                                                    <?php endforeach; ?>
                                                </div>
                                            </td>
                                        </tr>
                                    <?php endforeach; ?>
                                </tbody>
                            </table>
                        </div>
                    </details>
                <?php endforeach; ?>
            </div>
            <div style="margin-top: 2rem;">
                <button type="submit" class="btn" style="padding: 0.6rem 1.5rem; font-size: 1rem;">Save Permissions Matrix</button>
            </div>
        </form>
    </div>
</div>
<script>
function switchTab(tabId) {
    document.querySelectorAll('.tab-panel').forEach(panel => panel.style.display = 'none');
    document.querySelectorAll('.tab-btn').forEach(btn => {
        btn.style.color = '#555';
        btn.style.borderBottomColor = 'transparent';
        btn.setAttribute('aria-selected', 'false');
    });
    document.getElementById('panel-' + tabId).style.display = 'block';
    const activeBtn = document.getElementById('tab-' + tabId);
    activeBtn.style.color = '#000';
    activeBtn.style.borderBottomColor = '#007bff';
    activeBtn.setAttribute('aria-selected', 'true');
}
function toggleSmtpFields(val) {
    document.getElementById('smtp_settings_block').style.display = (val === 'smtp') ? 'block' : 'none';
}
function handleUserManagementToggle(checkbox) {
    const leaderboardBox = document.getElementById('module_leaderboard_enabled');
    const note = document.getElementById('leaderboard_dependency_note');
    if (!checkbox.checked) {
        leaderboardBox.checked = false;
        leaderboardBox.disabled = true;
        note.style.display = 'inline';
    } else {
        leaderboardBox.disabled = false;
        note.style.display = 'none';
    }
}
document.addEventListener('DOMContentLoaded', () => {
    const urlParams = new URLSearchParams(window.location.search);
    if (urlParams.has('edit_role') || window.location.hash === '#tab-permissions') {
        switchTab('permissions');
    } else if (window.location.hash === '#tab-modules') {
        switchTab('modules');
    }
});
</script>
<?php require_once '../partials/footer.php'; ?>
