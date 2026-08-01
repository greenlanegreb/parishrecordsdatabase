<?php
// admin/settings.php - Global Site Settings, Maintenance, Notices, Permissions & Modules Interface
require_once '../db/db.php';
require_once '../db/auth_helpers.php';
require_once '../includes/functions.php';

// Standard admin bootstrap (permission + flash messages)
$current_user = require_admin_page($pdo, 'manage_settings', 'Manage global site settings, mail drivers, and maintenance mode');
$message = $GLOBALS['message'] ?? '';
$error   = $GLOBALS['error']    ?? '';

// Auto-register table-scoped permissions for any existing dynamic tables
try {
    $existing_tables = $pdo->query("SELECT id, table_name FROM dynamic_tables")->fetchAll(PDO::FETCH_ASSOC);
    foreach ($existing_tables as $et) {
        $t_id   = $et['id'];
        $t_name = $et['table_name'];
        $view_key  = 'view_table_' . $t_id;
        $view_desc = 'Allows viewing and searching records in table: ' . $t_name;
        $mod_key   = 'moderate_table_' . $t_id;
        $mod_desc  = 'Allows reviewing and moderating suggestions in table: ' . $t_name;
        $ins_p = $pdo->prepare("INSERT IGNORE INTO permissions (permission_key, description) VALUES (?, ?)");
        $ins_p->execute([$view_key, $view_desc]);
        $ins_p->execute([$mod_key, $mod_desc]);
    }
} catch (Exception $e) {}

$current_system_name = get_system_name($pdo);

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

$current_mail_domain      = $get_setting_val($pdo, 'mail_domain', '');
$current_mail_from        = $get_setting_val($pdo, 'mail_from', '');
$current_mail_driver      = $get_setting_val($pdo, 'mail_driver', 'mail');
$current_smtp_host        = $get_setting_val($pdo, 'smtp_host', '');
$current_smtp_port        = $get_setting_val($pdo, 'smtp_port', '587');
$current_smtp_user        = $get_setting_val($pdo, 'smtp_user', '');
$current_smtp_encryption  = $get_setting_val($pdo, 'smtp_encryption', 'tls');
$maintenance_mode         = $get_setting_val($pdo, 'maintenance_mode', '0');
$maintenance_reason       = $get_setting_val($pdo, 'maintenance_reason', 'Scheduled system maintenance and database updates.');
$maintenance_eta          = $get_setting_val($pdo, 'maintenance_eta', 'Shortly');
$current_default_language = $get_setting_val($pdo, 'default_language', 'en');

// CAPTCHA Configuration Settings
$current_captcha_provider = $get_setting_val($pdo, 'captcha_provider', 'none');
$current_turnstile_site   = $get_setting_val($pdo, 'turnstile_site_key', '');
$current_turnstile_secret = $get_setting_val($pdo, 'turnstile_secret_key', '');
$current_recaptcha_site   = $get_setting_val($pdo, 'recaptcha_site_key', '');
$current_recaptcha_secret = $get_setting_val($pdo, 'recaptcha_secret_key', '');
$current_hcaptcha_site    = $get_setting_val($pdo, 'hcaptcha_site_key', '');
$current_hcaptcha_secret  = $get_setting_val($pdo, 'hcaptcha_secret_key', '');

// Available language files in /lang
$available_languages = [];
$lang_dir = __DIR__ . '/../lang';
if (is_dir($lang_dir)) {
    foreach (glob($lang_dir . '/*.php') as $file) {
        $code = basename($file, '.php');
        if (preg_match('/^[a-z_]+$/', $code)) {
            $available_languages[] = $code;
        }
    }
    sort($available_languages);
}
if (!in_array('en', $available_languages, true)) {
    array_unshift($available_languages, 'en');
}

// Schema version status for update UI
$schema_current = function_exists('get_schema_version') ? get_schema_version($pdo) : 0;
$schema_latest  = $schema_current;
$migrations_dir = __DIR__ . '/../db/migrations';
if (is_dir($migrations_dir)) {
    foreach (glob($migrations_dir . '/*.php') as $mig_file) {
        if (preg_match('/(\d+)_/', basename($mig_file), $m)) {
            $schema_latest = max($schema_latest, (int) $m[1]);
        }
    }
}
$schema_needs_update = ($schema_current < $schema_latest);

// Module toggles state
$mod_moderation_val  = $get_setting_val($pdo, 'module_moderation_enabled', '1');
$mod_volunteers_val  = $get_setting_val($pdo, 'module_volunteers_enabled', '1');
$mod_feedback_val    = $get_setting_val($pdo, 'module_feedback_enabled', '1');
$mod_users_val       = $get_setting_val($pdo, 'module_users_enabled', '1');
$mod_leaderboard_val = $get_setting_val($pdo, 'module_leaderboard_enabled', '1');

$notices = $pdo->query("SELECT * FROM site_notices ORDER BY display_order ASC, id DESC")->fetchAll(PDO::FETCH_ASSOC);

// Audit log data fetching
$audit_logs = $pdo->query("
    SELECT al.*, u.username 
    FROM audit_logs al 
    LEFT JOIN users u ON al.user_id = u.id 
    ORDER BY al.created_at DESC 
    LIMIT 250
")->fetchAll(PDO::FETCH_ASSOC);

$distinct_actions = $pdo->query("SELECT DISTINCT action FROM audit_logs ORDER BY action ASC")->fetchAll(PDO::FETCH_COLUMN);

$user_timezone = $current_user['timezone'] ?? 'UTC';
$full_format_str = get_user_datetime_format($current_user);

$edit_role = null;
if (isset($_GET['edit_role'])) {
    $edit_role_id = intval($_GET['edit_role']);
    $r_stmt = $pdo->prepare("SELECT * FROM roles WHERE id = ?");
    $r_stmt->execute([$edit_role_id]);
    $edit_role = $r_stmt->fetch(PDO::FETCH_ASSOC);
}
?>
<?php require_once '../partials/header.php'; ?>

<style>
@keyframes spin {
    0% { transform: rotate(0deg); }
    100% { transform: rotate(360deg); }
}
.spinner-icon {
    width: 14px;
    height: 14px;
    border: 2px solid rgba(0,0,0,0.1);
    border-top: 2px solid #000;
    border-radius: 50%;
    animation: spin 0.8s linear infinite;
    display: inline-block;
}
</style>

<div class="search-box-container" role="region" aria-label="Site Settings Form" style="max-width: 1100px; margin: 0 auto; font-size: 1rem;">
    <h3 style="font-size: 1.5rem; margin-bottom: 0.5rem;"><?php echo htmlspecialchars(__('settings.heading')); ?></h3>
    <p style="font-size: 1rem; color: #555; margin-bottom: 1.5rem;"><?php echo htmlspecialchars(__('settings.subheading')); ?></p>

    <?php if (!empty($error)): ?>
        <p class="alert-danger" role="alert" style="font-size: 1rem;"><strong><?php echo htmlspecialchars($error); ?></strong></p>
    <?php endif; ?>
    <?php if (!empty($message)): ?>
        <p class="alert-success" role="status" style="font-size: 1rem;"><strong><?php echo htmlspecialchars($message); ?></strong></p>
    <?php endif; ?>

    <!-- Accessible Tab Navigation -->
    <div role="tablist" aria-label="Settings Sections" style="display: flex; gap: 0.75rem; border-bottom: 2px solid var(--border-color); margin-bottom: 2rem; flex-wrap: wrap;">
        <button role="tab" aria-selected="true" aria-controls="panel-core" id="tab-core" onclick="switchTab('core')" class="tab-btn active-tab" style="padding: 0.75rem 1.25rem; cursor: pointer; border: none; background: none; font-size: 1.05rem; font-weight: bold; border-bottom: 3px solid #007bff; margin-bottom: -2px;"><?php echo htmlspecialchars(__('settings.tab_core')); ?></button>
        <button role="tab" aria-selected="false" aria-controls="panel-modules" id="tab-modules" onclick="switchTab('modules')" class="tab-btn" style="padding: 0.75rem 1.25rem; cursor: pointer; border: none; background: none; font-size: 1.05rem; font-weight: bold; color: #555; border-bottom: 3px solid transparent; margin-bottom: -2px;"><?php echo htmlspecialchars(__('settings.tab_modules')); ?></button>
        <button role="tab" aria-selected="false" aria-controls="panel-maintenance" id="tab-maintenance" onclick="switchTab('maintenance')" class="tab-btn" style="padding: 0.75rem 1.25rem; cursor: pointer; border: none; background: none; font-size: 1.05rem; font-weight: bold; color: #555; border-bottom: 3px solid transparent; margin-bottom: -2px;"><?php echo htmlspecialchars(__('settings.tab_maintenance')); ?></button>
        <button role="tab" aria-selected="false" aria-controls="panel-notices" id="tab-notices" onclick="switchTab('notices')" class="tab-btn" style="padding: 0.75rem 1.25rem; cursor: pointer; border: none; background: none; font-size: 1.05rem; font-weight: bold; color: #555; border-bottom: 3px solid transparent; margin-bottom: -2px;"><?php echo htmlspecialchars(__('settings.tab_notices')); ?></button>
        <button role="tab" aria-selected="false" aria-controls="panel-permissions" id="tab-permissions" onclick="switchTab('permissions')" class="tab-btn" style="padding: 0.75rem 1.25rem; cursor: pointer; border: none; background: none; font-size: 1.05rem; font-weight: bold; color: #555; border-bottom: 3px solid transparent; margin-bottom: -2px;"><?php echo htmlspecialchars(__('settings.tab_permissions')); ?></button>
        <button role="tab" aria-selected="false" aria-controls="panel-audit" id="tab-audit" onclick="switchTab('audit')" class="tab-btn" style="padding: 0.75rem 1.25rem; cursor: pointer; border: none; background: none; font-size: 1.05rem; font-weight: bold; color: #555; border-bottom: 3px solid transparent; margin-bottom: -2px;"><?php echo htmlspecialchars(__('settings.tab_audit')); ?></button>
    </div>

    <!-- TAB 1: Core & Mail Settings -->
    <div role="tabpanel" id="panel-core" aria-labelledby="tab-core" class="tab-panel">

        <!-- Database backup + schema updates -->
        <div style="margin-bottom: 2rem; padding: 1.25rem; border: 1px solid var(--border-color); border-radius: 6px; background: rgba(0,0,0,0.02);">
            <h4 style="margin-top: 0; color: #333; font-size: 1.2rem;"><?php echo htmlspecialchars(__('settings.db_updates_heading')); ?></h4>
            <p style="margin: 0.5rem 0; font-size: 1rem;">
                <?php echo htmlspecialchars(__('settings.schema_current')); ?> <strong><?php echo (int) $schema_current; ?></strong>
                &nbsp;·&nbsp;
                <?php echo htmlspecialchars(__('settings.schema_latest')); ?> <strong><?php echo (int) $schema_latest; ?></strong>
            </p>

            <form method="POST" action="actions/download_database_backup.php" style="margin: 0.75rem 0;">
                <?php echo csrf_field(); ?>
                <button type="submit" class="btn btn-secondary"><?php echo htmlspecialchars(__('settings.download_backup_btn')); ?></button>
            </form>
            <p style="font-size: 0.9rem; color: #555; margin: 0 0 1rem;">
                <?php echo htmlspecialchars(__('settings.download_backup_desc')); ?>
            </p>

            <?php if ($schema_needs_update): ?>
                <p style="font-size: 0.95rem; color: #856404; background: #fff3cd; padding: 0.75rem; border-radius: 4px;">
                    <?php echo htmlspecialchars(__('settings.schema_update_notice')); ?>
                </p>
                <form method="POST" action="actions/run_migrations.php" style="margin-top: 0.75rem;"
                      onsubmit="return confirm('<?php echo htmlspecialchars(__('settings.migration_confirm')); ?>');">
                    <?php echo csrf_field(); ?>
                    <button type="submit" class="btn"><?php echo htmlspecialchars(__('settings.update_db_btn')); ?></button>
                </form>
            <?php else: ?>
                <p style="margin: 0.5rem 0; font-size: 0.95rem; color: #155724;"><?php echo htmlspecialchars(__('settings.schema_uptodate')); ?></p>
            <?php endif; ?>
        </div>

        <form method="POST" action="actions/save_settings.php">
            <?php echo csrf_field(); ?>
            <h4 style="margin-top: 0; color: #333; font-size: 1.2rem;"><?php echo htmlspecialchars(__('settings.core_sys_heading')); ?></h4>
            <div style="margin-bottom: 1.25rem;">
                <label for="system_name" style="font-size: 1rem;"><strong><?php echo htmlspecialchars(__('settings.sys_name_label')); ?></strong></label><br>
                <input type="text" id="system_name" name="system_name" value="<?php echo htmlspecialchars($current_system_name); ?>" required class="volunteer-input" style="width: 100%; padding: 0.6rem; font-size: 1rem; margin-top: 0.4rem;">
            </div>
            <div style="margin-bottom: 1.25rem;">
                <label for="default_language" style="font-size: 1rem;"><strong><?php echo htmlspecialchars(__('settings.default_lang_label')); ?></strong></label><br>
                <select id="default_language" name="default_language" class="volunteer-input" style="width: 100%; max-width: 320px; padding: 0.6rem; font-size: 1rem; margin-top: 0.4rem;">
                    <?php foreach ($available_languages as $code): ?>
                        <option value="<?php echo htmlspecialchars($code); ?>" <?php echo ($current_default_language === $code) ? 'selected' : ''; ?>>
                            <?php echo htmlspecialchars(strtoupper($code)); ?>
                        </option>
                    <?php endforeach; ?>
                </select>
                <p style="margin: 0.4rem 0 0; font-size: 0.9rem; color: #555;"><?php echo htmlspecialchars(__('settings.default_lang_desc')); ?></p>
            </div>

            <!-- CAPTCHA / Anti-Bot Security Configuration -->
            <h4 style="margin-top: 2rem; color: #333; font-size: 1.2rem;"><?php echo htmlspecialchars(__('settings.captcha_heading')); ?></h4>
            <div style="margin-bottom: 1.25rem;">
                <label for="captcha_provider" style="font-size: 1rem;"><strong><?php echo htmlspecialchars(__('settings.captcha_provider_label')); ?></strong></label><br>
                <select id="captcha_provider" name="captcha_provider" class="volunteer-input" style="width: 100%; padding: 0.6rem; font-size: 1rem; margin-top: 0.4rem;" onchange="toggleCaptchaConfigs(this.value)">
                    <option value="none" <?php echo ($current_captcha_provider === 'none') ? 'selected' : ''; ?>><?php echo htmlspecialchars(__('settings.captcha_none')); ?></option>
                    <option value="turnstile" <?php echo ($current_captcha_provider === 'turnstile') ? 'selected' : ''; ?>><?php echo htmlspecialchars(__('settings.captcha_turnstile')); ?></option>
                    <option value="recaptcha" <?php echo ($current_captcha_provider === 'recaptcha') ? 'selected' : ''; ?>><?php echo htmlspecialchars(__('settings.captcha_recaptcha')); ?></option>
                    <option value="hcaptcha" <?php echo ($current_captcha_provider === 'hcaptcha') ? 'selected' : ''; ?>><?php echo htmlspecialchars(__('settings.captcha_hcaptcha')); ?></option>
                </select>
            </div>

            <!-- Turnstile Settings Block -->
            <div id="captcha_turnstile_block" style="background: rgba(0,0,0,0.02); padding: 1.25rem; border: 1px solid var(--border-color); border-radius: 6px; margin-bottom: 1.5rem; display: <?php echo ($current_captcha_provider === 'turnstile') ? 'block' : 'none'; ?>;">
                <h4 style="margin-top: 0; margin-bottom: 1rem; font-size: 1.1rem;"><?php echo htmlspecialchars(__('settings.turnstile_heading')); ?></h4>
                <div style="margin-bottom: 1rem;">
                    <label for="turnstile_site_key" style="font-size: 0.95rem;"><?php echo htmlspecialchars(__('settings.site_key_label')); ?></label><br>
                    <input type="text" id="turnstile_site_key" name="turnstile_site_key" value="<?php echo htmlspecialchars($current_turnstile_site); ?>" class="volunteer-input" style="width: 100%; padding: 0.5rem; font-size: 0.95rem;">
                </div>
                <div>
                    <label for="turnstile_secret_key" style="font-size: 0.95rem;"><?php echo htmlspecialchars(__('settings.secret_key_label')); ?></label><br>
                    <input type="password" id="turnstile_secret_key" name="turnstile_secret_key" value="<?php echo htmlspecialchars($current_turnstile_secret); ?>" placeholder="••••••••" class="volunteer-input" style="width: 100%; padding: 0.5rem; font-size: 0.95rem;">
                </div>
            </div>

            <!-- Google reCAPTCHA Settings Block -->
            <div id="captcha_recaptcha_block" style="background: rgba(0,0,0,0.02); padding: 1.25rem; border: 1px solid var(--border-color); border-radius: 6px; margin-bottom: 1.5rem; display: <?php echo ($current_captcha_provider === 'recaptcha') ? 'block' : 'none'; ?>;">
                <h4 style="margin-top: 0; margin-bottom: 1rem; font-size: 1.1rem;"><?php echo htmlspecialchars(__('settings.recaptcha_heading')); ?></h4>
                <div style="margin-bottom: 1rem;">
                    <label for="recaptcha_site_key" style="font-size: 0.95rem;"><?php echo htmlspecialchars(__('settings.site_key_label')); ?></label><br>
                    <input type="text" id="recaptcha_site_key" name="recaptcha_site_key" value="<?php echo htmlspecialchars($current_recaptcha_site); ?>" class="volunteer-input" style="width: 100%; padding: 0.5rem; font-size: 0.95rem;">
                </div>
                <div>
                    <label for="recaptcha_secret_key" style="font-size: 0.95rem;"><?php echo htmlspecialchars(__('settings.secret_key_label')); ?></label><br>
                    <input type="password" id="recaptcha_secret_key" name="recaptcha_secret_key" value="<?php echo htmlspecialchars($current_recaptcha_secret); ?>" placeholder="••••••••" class="volunteer-input" style="width: 100%; padding: 0.5rem; font-size: 0.95rem;">
                </div>
            </div>

            <!-- hCaptcha Settings Block -->
            <div id="captcha_hcaptcha_block" style="background: rgba(0,0,0,0.02); padding: 1.25rem; border: 1px solid var(--border-color); border-radius: 6px; margin-bottom: 1.5rem; display: <?php echo ($current_captcha_provider === 'hcaptcha') ? 'block' : 'none'; ?>;">
                <h4 style="margin-top: 0; margin-bottom: 1rem; font-size: 1.1rem;"><?php echo htmlspecialchars(__('settings.hcaptcha_heading')); ?></h4>
                <div style="margin-bottom: 1rem;">
                    <label for="hcaptcha_site_key" style="font-size: 0.95rem;"><?php echo htmlspecialchars(__('settings.site_key_label')); ?></label><br>
                    <input type="text" id="hcaptcha_site_key" name="hcaptcha_site_key" value="<?php echo htmlspecialchars($current_hcaptcha_site); ?>" class="volunteer-input" style="width: 100%; padding: 0.5rem; font-size: 0.95rem;">
                </div>
                <div>
                    <label for="hcaptcha_secret_key" style="font-size: 0.95rem;"><?php echo htmlspecialchars(__('settings.secret_key_label')); ?></label><br>
                    <input type="password" id="hcaptcha_secret_key" name="hcaptcha_secret_key" value="<?php echo htmlspecialchars($current_hcaptcha_secret); ?>" placeholder="••••••••" class="volunteer-input" style="width: 100%; padding: 0.5rem; font-size: 0.95rem;">
                </div>
            </div>

            <h4 style="margin-top: 2rem; color: #333; font-size: 1.2rem;"><?php echo htmlspecialchars(__('settings.mail_heading')); ?></h4>
            <div style="margin-bottom: 1.25rem;">
                <label for="mail_domain" style="font-size: 1rem;"><strong><?php echo htmlspecialchars(__('settings.mail_domain_label')); ?></strong></label><br>
                <input type="text" id="mail_domain" name="mail_domain" value="<?php echo htmlspecialchars($current_mail_domain); ?>" placeholder="e.g. example.com" class="volunteer-input" style="width: 100%; padding: 0.6rem; font-size: 1rem; margin-top: 0.4rem;">
            </div>
            <div style="margin-bottom: 1.25rem;">
                <label for="mail_from" style="font-size: 1rem;"><strong><?php echo htmlspecialchars(__('settings.mail_from_label')); ?></strong></label><br>
                <input type="email" id="mail_from" name="mail_from" value="<?php echo htmlspecialchars($current_mail_from); ?>" placeholder="e.g. notifications@example.com" class="volunteer-input" style="width: 100%; padding: 0.6rem; font-size: 1rem; margin-top: 0.4rem;">
                <p style="margin: 0.4rem 0 0; font-size: 0.9rem; color: #555;"><?php echo htmlspecialchars(__('settings.mail_from_desc')); ?></p>
            </div>
            <div style="margin-bottom: 1.25rem;">
                <label for="mail_driver" style="font-size: 1rem;"><strong><?php echo htmlspecialchars(__('settings.mail_driver_label')); ?></strong></label><br>
                <select id="mail_driver" name="mail_driver" class="volunteer-input" style="width: 100%; padding: 0.6rem; font-size: 1rem; margin-top: 0.4rem;" onchange="toggleSmtpFields(this.value)">
                    <option value="mail" <?php echo ($current_mail_driver === 'mail') ? 'selected' : ''; ?>><?php echo htmlspecialchars(__('settings.driver_native')); ?></option>
                    <option value="smtp" <?php echo ($current_mail_driver === 'smtp') ? 'selected' : ''; ?>><?php echo htmlspecialchars(__('settings.driver_smtp')); ?></option>
                </select>
            </div>
            <div id="smtp_settings_block" style="background: rgba(0,0,0,0.02); padding: 1.25rem; border: 1px solid var(--border-color); border-radius: 6px; margin-bottom: 1.5rem; display: <?php echo ($current_mail_driver === 'smtp') ? 'block' : 'none'; ?>;">
                <h4 style="margin-top: 0; margin-bottom: 1rem; font-size: 1.1rem;"><?php echo htmlspecialchars(__('settings.smtp_heading')); ?></h4>
                <div style="margin-bottom: 1rem;">
                    <label for="smtp_host" style="font-size: 0.95rem;"><?php echo htmlspecialchars(__('settings.smtp_host_label')); ?></label><br>
                    <input type="text" id="smtp_host" name="smtp_host" value="<?php echo htmlspecialchars($current_smtp_host); ?>" placeholder="e.g. smtp.example.com" class="volunteer-input" style="width: 100%; padding: 0.5rem; font-size: 0.95rem;">
                </div>
                <div style="display: flex; gap: 1rem; margin-bottom: 1rem;">
                    <div style="flex: 1;">
                        <label for="smtp_port" style="font-size: 0.95rem;"><?php echo htmlspecialchars(__('settings.smtp_port_label')); ?></label><br>
                        <input type="number" id="smtp_port" name="smtp_port" value="<?php echo htmlspecialchars($current_smtp_port); ?>" class="volunteer-input" style="width: 100%; padding: 0.5rem; font-size: 0.95rem;">
                    </div>
                    <div style="flex: 1;">
                        <label for="smtp_encryption" style="font-size: 0.95rem;"><?php echo htmlspecialchars(__('settings.smtp_encryption_label')); ?></label><br>
                        <select id="smtp_encryption" name="smtp_encryption" class="volunteer-input" style="width: 100%; padding: 0.5rem; font-size: 0.95rem;" onchange="updateSmtpPort(this.value)">
                            <option value="tls" <?php echo ($current_smtp_encryption === 'tls') ? 'selected' : ''; ?>><?php echo htmlspecialchars(__('settings.enc_tls')); ?></option>
                            <option value="ssl" <?php echo ($current_smtp_encryption === 'ssl') ? 'selected' : ''; ?>><?php echo htmlspecialchars(__('settings.enc_ssl')); ?></option>
                        </select>
                    </div>
                </div>
                <div style="margin-bottom: 1rem;">
                    <label for="smtp_user" style="font-size: 0.95rem;"><?php echo htmlspecialchars(__('settings.smtp_user_label')); ?></label><br>
                    <input type="text" id="smtp_user" name="smtp_user" value="<?php echo htmlspecialchars($current_smtp_user); ?>" class="volunteer-input" style="width: 100%; padding: 0.5rem; font-size: 0.95rem;">
                </div>
                <div>
                    <label for="smtp_pass" style="font-size: 0.95rem;"><?php echo htmlspecialchars(__('settings.smtp_pass_label')); ?></label><br>
                    <input type="password" id="smtp_pass" name="smtp_pass" placeholder="••••••••" class="volunteer-input" style="width: 100%; padding: 0.5rem; font-size: 0.95rem;">
                </div>
            </div>
            <button type="submit" class="btn" style="padding: 0.6rem 1.2rem; font-size: 1rem;"><?php echo htmlspecialchars(__('settings.save_core_mail_btn')); ?></button>
        </form>

        <hr style="border: 0.0625rem solid var(--border-color); margin: 2rem 0;">

        <div id="test-mail-section">
            <h4 style="margin-bottom: 0.75rem; color: #333; font-size: 1.2rem;"><?php echo htmlspecialchars(__('settings.test_mail_heading')); ?></h4>
            <form method="POST" action="actions/test_mail.php#test-mail-section" onsubmit="handleTestMailSubmit(this);">
                <?php echo csrf_field(); ?>
                <label for="test_email" style="font-size: 0.95rem;"><strong><?php echo htmlspecialchars(__('settings.test_email_label')); ?></strong></label><br>
                <div style="display: flex; gap: 0.75rem; margin-top: 0.4rem;">
                    <input type="email" id="test_email" name="test_email" placeholder="admin@example.com" required class="volunteer-input" style="flex: 1; padding: 0.6rem; font-size: 1rem;">
                    <button type="submit" id="test-mail-btn" class="btn btn-secondary" style="white-space: nowrap; padding: 0.6rem 1.2rem; font-size: 1rem; display: inline-flex; align-items: center; gap: 0.5rem;">
                        <span><?php echo htmlspecialchars(__('settings.send_test_btn')); ?></span>
                    </button>
                </div>
            </form>
        </div>
    </div>

    <!-- TAB 2: Modules Management -->
    <div role="tabpanel" id="panel-modules" aria-labelledby="tab-modules" class="tab-panel" style="display: none;">
        <form method="POST" action="actions/save_modules.php">
            <?php echo csrf_field(); ?>
            <h4 style="margin-top: 0; color: #333; font-size: 1.2rem;"><?php echo htmlspecialchars(__('settings.modules_heading')); ?></h4>
            <p style="font-size: 1rem; color: #555; margin-bottom: 1.5rem;"><?php echo htmlspecialchars(__('settings.modules_subheading')); ?></p>
            <div style="display: flex; flex-direction: column; gap: 1.25rem;">
                <div style="background: rgba(0,0,0,0.02); padding: 1.25rem; border: 1px solid var(--border-color); border-radius: 6px;">
                    <label style="cursor: pointer; font-weight: bold; font-size: 1.05rem; display: flex; align-items: center; gap: 0.75rem;">
                        <input type="checkbox" name="module_users_enabled" id="module_users_enabled" value="1" <?php echo ($mod_users_val === '1') ? 'checked' : ''; ?> onchange="handleUserManagementToggle(this);" style="transform: scale(1.3);">
                        <span><?php echo htmlspecialchars(__('settings.mod_users')); ?></span>
                    </label>
                    <p style="margin: 0.4rem 0 0 2rem; font-size: 0.95rem; color: #555;"><?php echo htmlspecialchars(__('settings.mod_users_desc')); ?></p>
                </div>
                <div style="background: rgba(0,0,0,0.02); padding: 1.25rem; border: 1px solid var(--border-color); border-radius: 6px;">
                    <label style="cursor: pointer; font-weight: bold; font-size: 1.05rem; display: flex; align-items: center; gap: 0.75rem;" id="leaderboard_label_wrapper">
                        <input type="checkbox" name="module_leaderboard_enabled" id="module_leaderboard_enabled" value="1" <?php echo ($mod_leaderboard_val === '1' && $mod_users_val === '1') ? 'checked' : ''; ?> <?php echo ($mod_users_val !== '1') ? 'disabled' : ''; ?> style="transform: scale(1.3);">
                        <span><?php echo htmlspecialchars(__('settings.mod_leaderboard')); ?></span>
                    </label>
                    <p style="margin: 0.4rem 0 0 2rem; font-size: 0.95rem; color: #555;" id="leaderboard_desc">
                        <?php echo htmlspecialchars(__('settings.mod_leaderboard_desc')); ?> <span id="leaderboard_dependency_note" style="color: #dc3545; font-weight: bold; display: <?php echo ($mod_users_val !== '1') ? 'inline' : 'none'; ?>;"><?php echo htmlspecialchars(__('settings.mod_leaderboard_note')); ?></span>
                    </p>
                </div>
                <div style="background: rgba(0,0,0,0.02); padding: 1.25rem; border: 1px solid var(--border-color); border-radius: 6px;">
                    <label style="cursor: pointer; font-weight: bold; font-size: 1.05rem; display: flex; align-items: center; gap: 0.75rem;">
                        <input type="checkbox" name="module_moderation_enabled" value="1" <?php echo ($mod_moderation_val === '1') ? 'checked' : ''; ?> style="transform: scale(1.3);">
                        <span><?php echo htmlspecialchars(__('settings.mod_moderation')); ?></span>
                    </label>
                    <p style="margin: 0.4rem 0 0 2rem; font-size: 0.95rem; color: #555;"><?php echo htmlspecialchars(__('settings.mod_moderation_desc')); ?></p>
                </div>
                <div style="background: rgba(0,0,0,0.02); padding: 1.25rem; border: 1px solid var(--border-color); border-radius: 6px;">
                    <label style="cursor: pointer; font-weight: bold; font-size: 1.05rem; display: flex; align-items: center; gap: 0.75rem;">
                        <input type="checkbox" name="module_volunteers_enabled" value="1" <?php echo ($mod_volunteers_val === '1') ? 'checked' : ''; ?> style="transform: scale(1.3);">
                        <span><?php echo htmlspecialchars(__('settings.mod_volunteers')); ?></span>
                    </label>
                    <p style="margin: 0.4rem 0 0 2rem; font-size: 0.95rem; color: #555;"><?php echo htmlspecialchars(__('settings.mod_volunteers_desc')); ?></p>
                </div>
                <div style="background: rgba(0,0,0,0.02); padding: 1.25rem; border: 1px solid var(--border-color); border-radius: 6px;">
                    <label style="cursor: pointer; font-weight: bold; font-size: 1.05rem; display: flex; align-items: center; gap: 0.75rem;">
                        <input type="checkbox" name="module_feedback_enabled" value="1" <?php echo ($mod_feedback_val === '1') ? 'checked' : ''; ?> style="transform: scale(1.3);">
                        <span><?php echo htmlspecialchars(__('settings.mod_feedback')); ?></span>
                    </label>
                    <p style="margin: 0.4rem 0 0 2rem; font-size: 0.95rem; color: #555;"><?php echo htmlspecialchars(__('settings.mod_feedback_desc')); ?></p>
                </div>
            </div>
            <div style="margin-top: 1.5rem;">
                <button type="submit" class="btn" style="padding: 0.6rem 1.2rem; font-size: 1rem;"><?php echo htmlspecialchars(__('settings.save_modules_btn')); ?></button>
            </div>
        </form>
    </div>

    <!-- TAB 3: Maintenance Mode Settings -->
    <div role="tabpanel" id="panel-maintenance" aria-labelledby="tab-maintenance" class="tab-panel" style="display: none;">
        <form method="POST" action="actions/save_maintenance.php">
            <?php echo csrf_field(); ?>
            <h4 style="margin-top: 0; color: #333; font-size: 1.2rem;"><?php echo htmlspecialchars(__('settings.maintenance_heading')); ?></h4>
            <div style="margin-bottom: 1.25rem;">
                <label style="cursor: pointer; font-size: 1.05rem;">
                    <input type="checkbox" name="maintenance_mode" value="1" <?php echo ($maintenance_mode === '1') ? 'checked' : ''; ?> style="transform: scale(1.2); margin-right: 0.5rem;">
                    <strong><?php echo htmlspecialchars(__('settings.maintenance_toggle')); ?></strong>
                </label>
            </div>
            <div style="margin-bottom: 1.25rem;">
                <label for="maintenance_reason" style="font-size: 1rem;"><strong><?php echo htmlspecialchars(__('settings.maintenance_reason_label')); ?></strong></label><br>
                <textarea id="maintenance_reason" name="maintenance_reason" rows="3" class="volunteer-input" style="width: 100%; padding: 0.6rem; font-size: 1rem; margin-top: 0.4rem;" required><?php echo htmlspecialchars($maintenance_reason); ?></textarea>
            </div>
            <div style="margin-bottom: 1.5rem;">
                <label for="maintenance_eta" style="font-size: 1rem;"><strong><?php echo htmlspecialchars(__('settings.maintenance_eta_label')); ?></strong></label><br>
                <input type="text" id="maintenance_eta" name="maintenance_eta" value="<?php echo htmlspecialchars($maintenance_eta); ?>" class="volunteer-input" style="width: 100%; max-width: 350px; padding: 0.6rem; font-size: 1rem; margin-top: 0.4rem;" required>
            </div>
            <button type="submit" class="btn btn-danger" style="padding: 0.6rem 1.2rem; font-size: 1rem;"><?php echo htmlspecialchars(__('settings.save_maintenance_btn')); ?></button>
        </form>
    </div>

    <!-- TAB 4: Site Notices -->
    <div role="tabpanel" id="panel-notices" aria-labelledby="tab-notices" class="tab-panel" style="display: none;">
        <div style="display: flex; justify-content: space-between; align-items: center; margin-bottom: 1.25rem;">
            <h4 style="margin: 0; color: #333; font-size: 1.2rem;"><?php echo htmlspecialchars(__('settings.notices_heading')); ?></h4>
            <a href="notices.php" class="btn btn-secondary" style="font-size: 0.95rem; text-decoration: none; padding: 0.5rem 1rem;"><?php echo htmlspecialchars(__('settings.add_notice_btn')); ?></a>
        </div>
        <?php if (empty($notices)): ?>
            <p style="padding: 1.5rem; background: rgba(0,0,0,0.02); border-radius: 6px; text-align: center; color: #555; font-size: 1rem;"><?php echo htmlspecialchars(__('settings.no_notices')); ?></p>
        <?php else: ?>
            <div class="accordion-container" style="display: flex; flex-direction: column; gap: 1rem;">
                <?php foreach ($notices as $n): ?>
                    <details style="background: #fdfdfd; border: 1px solid var(--border-color, #ccc); border-radius: 6px; padding: 1rem 1.25rem;">
                        <summary style="cursor: pointer; font-weight: bold; color: #333; font-size: 1.05rem; display: flex; justify-content: space-between; align-items: center;">
                            <span><?php echo htmlspecialchars($n['title']); ?></span>
                            <span style="font-size: 0.85rem; padding: 0.25rem 0.6rem; background: <?php echo $n['is_active'] ? '#d4edda; color: #155724;' : '#f8d7da; color: #721c24;'; ?> border-radius: 4px;"><?php echo $n['is_active'] ? htmlspecialchars(__('settings.status_active')) : htmlspecialchars(__('settings.status_inactive')); ?></span>
                        </summary>
                        <div style="margin-top: 1rem; padding-top: 1rem; border-top: 1px solid #eee;">
                            <form method="POST" action="actions/save_notice_inline.php">
                                <?php echo csrf_field(); ?>
                                <input type="hidden" name="notice_id" value="<?php echo $n['id']; ?>">
                                <label style="font-size: 0.9rem; font-weight: bold;"><?php echo htmlspecialchars(__('notices.title_label')); ?></label>
                                <input type="text" name="title" value="<?php echo htmlspecialchars($n['title']); ?>" class="volunteer-input" style="width: 100%; margin-bottom: 0.75rem; padding: 0.5rem; font-size: 0.95rem;" required><br>
                                <label style="font-size: 0.9rem; font-weight: bold;"><?php echo htmlspecialchars(__('settings.notice_content_label')); ?></label>
                                <textarea name="content" rows="3" class="volunteer-input" style="width: 100%; margin-bottom: 0.75rem; padding: 0.5rem; font-size: 0.95rem;" required><?php echo htmlspecialchars($n['content']); ?></textarea><br>
                                <button type="submit" name="update_action" value="save" class="btn" style="font-size: 0.95rem; padding: 0.4rem 1rem;"><?php echo htmlspecialchars(__('settings.save_notice_btn')); ?></button>
                            </form>
                        </div>
                    </details>
                <?php endforeach; ?>
            </div>
        <?php endif; ?>
    </div>

    <!-- TAB 5: Roles & Permissions Matrix -->
    <div role="tabpanel" id="panel-permissions" aria-labelledby="tab-permissions" class="tab-panel" style="display: none;">
        <h4 style="margin-top: 0; color: #333; font-size: 1.2rem;"><?php echo htmlspecialchars(__('settings.permissions_heading')); ?></h4>
        <p style="font-size: 1rem; color: #555; margin-bottom: 1.5rem;"><?php echo htmlspecialchars(__('settings.permissions_subheading')); ?></p>
        <?php
        $roles_list = $pdo->query("SELECT * FROM roles ORDER BY id ASC")->fetchAll(PDO::FETCH_ASSOC);
        $perms_list = $pdo->query("SELECT * FROM permissions ORDER BY id ASC")->fetchAll(PDO::FETCH_ASSOC);
        $active_mappings = [];
        $map_rows = $pdo->query("SELECT role_id, permission_id FROM role_permissions")->fetchAll(PDO::FETCH_ASSOC);
        foreach ($map_rows as $m) {
            $active_mappings[$m['role_id']][$m['permission_id']] = true;
        }

        // Module states
        $mod_users_active       = is_module_enabled($pdo, 'users');
        $mod_volunteers_active  = is_module_enabled($pdo, 'volunteers');
        $mod_feedback_active    = is_module_enabled($pdo, 'feedback');
        $mod_moderation_active  = is_module_enabled($pdo, 'moderation');
        $mod_leaderboard_active = is_module_enabled($pdo, 'leaderboard');

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
            if (in_array($pkey, ['access_suggest_edit', 'moderate_suggestions', 'manage_feedback'])) {
                return 'Moderation Workflow';
            }
            return 'Core System & Settings';
        }

        $categorized_perms = [];
        foreach ($perms_list as $p) {
            $pkey = $p['permission_key'];

            // Hide permissions if their corresponding module is disabled and they are otherwise unused externally
            if (($pkey === 'manage_users' || $pkey === 'invite_users' || $pkey === 'access_onboarding') && !$mod_users_active) continue;
            if (($pkey === 'manage_volunteers' || $pkey === 'submit_volunteer') && !$mod_volunteers_active) continue;
            if (($pkey === 'manage_feedback' || $pkey === 'submit_feedback') && !$mod_feedback_active) continue;
            if (($pkey === 'access_suggest_edit' || $pkey === 'moderate_suggestions') && !$mod_moderation_active) continue;
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
                                        <th style="padding: 0.75rem; width: 25%;"><?php echo htmlspecialchars(__('settings.th_role')); ?></th>
                                        <th style="padding: 0.75rem; width: 75%;"><?php echo htmlspecialchars(__('settings.th_capabilities')); ?></th>
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
                                                        <?php 
                                                            $is_checked = isset($active_mappings[$r['id']][$p['id']]);
                                                            // Lock/disable checkboxes for the 'admin' role (ID 1) if active in DB to prevent accidental lockout
                                                            $is_locked_admin = ($r['id'] == 1 && $is_checked);
                                                        ?>
                                                        <label style="cursor: pointer; display: inline-flex; align-items: center; gap: 0.4rem; font-size: 0.9rem; background: #f1f3f5; padding: 0.3rem 0.6rem; border-radius: 4px;" title="<?php echo htmlspecialchars($p['description']); ?>">
                                                            <input type="checkbox" 
                                                                   name="permissions[<?php echo $r['id']; ?>][<?php echo $p['id']; ?>]" 
                                                                   value="1" 
                                                                   <?php echo $is_checked ? 'checked' : ''; ?> 
                                                                   <?php echo $is_locked_admin ? 'disabled' : ''; ?>
                                                                   style="cursor: pointer; transform: scale(1.1);">
                                                            
                                                            <!-- If disabled, pass its value through a hidden input so form submission doesn't drop locked permissions -->
                                                            <?php if ($is_locked_admin): ?>
                                                                <input type="hidden" name="permissions[<?php echo $r['id']; ?>][<?php echo $p['id']; ?>]" value="1">
                                                            <?php endif; ?>

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
                <button type="submit" class="btn" style="padding: 0.6rem 1.5rem; font-size: 1rem;"><?php echo htmlspecialchars(__('settings.save_permissions_btn')); ?></button>
            </div>
        </form>
    </div>

    <!-- TAB 6: Audit Log -->
    <div role="tabpanel" id="panel-audit" aria-labelledby="tab-audit" class="tab-panel" style="display: none;">
        <h4 style="margin-top: 0; color: #333; font-size: 1.2rem;"><?php echo htmlspecialchars(__('settings.audit_heading')); ?></h4>
        <p style="font-size: 1rem; color: #555; margin-bottom: 1.5rem;"><?php echo htmlspecialchars(__('settings.audit_subheading')); ?></p>

        <!-- Audit Maintenance Actions -->
        <div style="background: rgba(0,0,0,0.02); border: 1px solid var(--border-color); padding: 1.25rem; border-radius: 6px; margin-bottom: 1.5rem; display: flex; flex-wrap: wrap; gap: 1rem; align-items: center;">
            <form method="POST" action="actions/purge_audit_logs.php" onsubmit="return confirm('<?php echo htmlspecialchars(__('settings.purge_all_confirm')); ?>');">
                <?php echo csrf_field(); ?>
                <input type="hidden" name="purge_type" value="all">
                <button type="submit" class="btn btn-danger" style="font-size: 0.9rem; padding: 0.5rem 1rem;"><?php echo htmlspecialchars(__('settings.clear_all_audit_btn')); ?></button>
            </form>

            <form method="POST" action="actions/purge_audit_logs.php" onsubmit="return confirm('<?php echo htmlspecialchars(__('settings.purge_records_confirm')); ?>');">
                <?php echo csrf_field(); ?>
                <input type="hidden" name="purge_type" value="records_only">
                <button type="submit" class="btn btn-secondary" style="font-size: 0.9rem; padding: 0.5rem 1rem;"><?php echo htmlspecialchars(__('settings.clear_records_audit_btn')); ?></button>
            </form>

            <?php foreach ($distinct_actions as $act): ?>
                <form method="POST" action="actions/purge_audit_logs.php" onsubmit="return confirm('Clear all audit logs matching action type: <?php echo htmlspecialchars($act); ?>?');">
                    <?php echo csrf_field(); ?>
                    <input type="hidden" name="purge_type" value="<?php echo htmlspecialchars($act); ?>">
                    <button type="submit" class="btn btn-secondary" style="font-size: 0.85rem; padding: 0.4rem 0.8rem;">Clear '<?php echo htmlspecialchars($act); ?>' Logs</button>
                </form>
            <?php endforeach; ?>
        </div>

        <!-- Full Audit Log Table View -->
        <div style="max-height: 600px; overflow-y: auto; border: 1px solid var(--border-color); border-radius: 6px;">
            <table class="data-table" style="width: 100%; border-collapse: collapse; text-align: left; background: #fff; font-size: 0.9rem;">
                <thead style="position: sticky; top: 0; background: #f8f9fa; z-index: 1;">
                    <tr style="border-bottom: 2px solid var(--border-color);">
                        <th style="padding: 0.75rem;"><?php echo htmlspecialchars(__('settings.th_id')); ?></th>
                        <th style="padding: 0.75rem;"><?php echo htmlspecialchars(__('settings.th_timestamp')); ?></th>
                        <th style="padding: 0.75rem;"><?php echo htmlspecialchars(__('settings.th_actor')); ?></th>
                        <th style="padding: 0.75rem;"><?php echo htmlspecialchars(__('settings.th_action')); ?></th>
                        <th style="padding: 0.75rem;"><?php echo htmlspecialchars(__('settings.th_record_id')); ?></th>
                        <th style="padding: 0.75rem;"><?php echo htmlspecialchars(__('settings.th_details')); ?></th>
                        <th style="padding: 0.75rem;"><?php echo htmlspecialchars(__('settings.th_ip')); ?></th>
                    </tr>
                </thead>
                <tbody>
                    <?php if (empty($audit_logs)): ?>
                        <tr><td colspan="7" style="padding: 1.5rem; text-align: center; color: #666;"><?php echo htmlspecialchars(__('settings.no_audit_logs')); ?></td></tr>
                    <?php else: ?>
                        <?php foreach ($audit_logs as $al): ?>
                            <tr style="border-bottom: 1px solid var(--border-color);">
                                <td style="padding: 0.75rem; font-weight: bold;"><?php echo $al['id']; ?></td>
                                <td style="padding: 0.75rem; white-space: nowrap;"><?php echo format_user_time($al['created_at'], $user_timezone, $full_format_str); ?></td>
                                <td style="padding: 0.75rem;"><?php echo htmlspecialchars($al['username'] ?? __('settings.system_guest')); ?></td>
                                <td style="padding: 0.75rem;"><span style="background: #e9ecef; padding: 0.1rem 0.4rem; border-radius: 3px; font-weight: bold; font-size: 0.8rem;"><?php echo htmlspecialchars($al['action']); ?></span></td>
                                <td style="padding: 0.75rem;"><?php echo $al['record_id'] ? '#' . $al['record_id'] : '—'; ?></td>
                                <td style="padding: 0.75rem; word-break: break-word;"><?php echo htmlspecialchars($al['details']); ?></td>
                                <td style="padding: 0.75rem; font-family: monospace; font-size: 0.85rem;"><?php echo htmlspecialchars($al['ip_address'] ?? 'N/A'); ?></td>
                            </tr>
                        <?php endforeach; ?>
                    <?php endif; ?>
                </tbody>
            </table>
        </div>
        <p style="font-size: 0.85rem; color: #666; margin-top: 0.75rem;"><?php echo htmlspecialchars(__('settings.audit_limit_note')); ?></p>
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
function toggleCaptchaConfigs(provider) {
    document.getElementById('captcha_turnstile_block').style.display = (provider === 'turnstile') ? 'block' : 'none';
    document.getElementById('captcha_recaptcha_block').style.display = (provider === 'recaptcha') ? 'block' : 'none';
    document.getElementById('captcha_hcaptcha_block').style.display = (provider === 'hcaptcha') ? 'block' : 'none';
}
function updateSmtpPort(encryptionType) {
    const portInput = document.getElementById('smtp_port');
    if (encryptionType === 'tls') {
        portInput.value = '587';
    } else if (encryptionType === 'ssl') {
        portInput.value = '465';
    }
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
function handleTestMailSubmit(form) {
    const btn = document.getElementById('test-mail-btn');
    btn.disabled = true;
    btn.style.opacity = '0.7';
    btn.style.cursor = 'wait';
    btn.innerHTML = '<span class="spinner-icon"></span> Sending Test Email...';
}
document.addEventListener('DOMContentLoaded', () => {
    const urlParams = new URLSearchParams(window.location.search);
    if (window.location.hash === '#test-mail-section') {
        switchTab('core');
        document.getElementById('test-mail-section').scrollIntoView({ behavior: 'smooth' });
    } else if (urlParams.has('edit_role') || window.location.hash === '#tab-permissions') {
        switchTab('permissions');
    } else if (window.location.hash === '#tab-modules') {
        switchTab('modules');
    } else if (window.location.hash === '#tab-audit') {
        switchTab('audit');
    }
});
</script>

<?php require_once '../partials/footer.php'; ?>
