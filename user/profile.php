<?php
// profile.php - User profile and security settings view
require_once '../db/db.php';
require_once '../db/auth_helpers.php';
require_once '../includes/functions.php';
session_start();

require_permission($pdo, 'access_profile', 'Allows viewing and managing personal user profile and security settings');
$current_user = get_current_user_data($pdo);

$system_name = get_system_name($pdo);
$system_slug = strtolower(preg_replace('/[^a-zA-Z0-9]+/', '-', $system_name));

$message = $_SESSION['message'] ?? '';
$error   = $_SESSION['error']   ?? '';
unset($_SESSION['message'], $_SESSION['error']);

$profile_languages = [];
$lang_dir = __DIR__ . '/../lang';
if (is_dir($lang_dir)) {
    foreach (glob($lang_dir . '/*.php') as $file) {
        $code = basename($file, '.php');
        if (preg_match('/^[a-z_]+$/', $code)) {
            $profile_languages[] = $code;
        }
    }
    sort($profile_languages);
}
if (!in_array('en', $profile_languages, true)) {
    array_unshift($profile_languages, 'en');
}
$user_language = $current_user['language'] ?? '';

if (isset($_GET['action']) && $_GET['action'] === 'download_new_codes') {
    if (!empty($_SESSION['new_raw_backup_codes'])) {
        $codes_to_download = $_SESSION['new_raw_backup_codes'];
        unset($_SESSION['new_raw_backup_codes']);

        header('Content-Type: text/plain; charset=utf-8');
        header('Content-Disposition: attachment; filename="' . $system_slug . '-backup-codes.txt"');
        echo strtoupper($system_name) . " - NEW EMERGENCY BACKUP CODES\n";
        echo str_repeat("=", strlen($system_name) + 33) . "\n\n";
        echo "Keep these codes in a secure place. Each code can only be used once:\n\n";
        foreach ($codes_to_download as $code) {
            echo " - " . $code . "\n";
        }
        exit;
    }
}
?>
<?php require_once '../partials/header.php'; ?>

<div class="search-box-container profile-container" role="region" aria-label="<?php echo htmlspecialchars(__('profile.aria_region')); ?>">
    <h3><?php echo htmlspecialchars(__('profile.heading')); ?></h3>

    <?php if (!empty($error)): ?>
        <p class="alert-danger"><strong><?php echo htmlspecialchars($error); ?></strong></p>
    <?php endif; ?>
    <?php if (!empty($message)): ?>
        <p class="alert-success"><strong><?php echo htmlspecialchars($message); ?></strong></p>
    <?php endif; ?>

    <div style="margin-bottom: 2rem;">
        <h4><?php echo htmlspecialchars(__('profile.personal_details_heading')); ?></h4>
        <form method="POST" action="actions/save_profile.php">
            <?php echo csrf_field(); ?>
            <input type="hidden" name="action" value="update_personal_details">

            <label for="first_name"><?php echo htmlspecialchars(__('feedback.first_name_label')); ?></label><br>
            <input type="text" id="first_name" name="first_name" value="<?php echo htmlspecialchars($current_user['first_name'] ?? ''); ?>" autocomplete="given-name" class="profile-input" aria-label="<?php echo htmlspecialchars(__('feedback.first_name_label')); ?>"><br>

            <label for="surname"><?php echo htmlspecialchars(__('feedback.surname_label')); ?></label><br>
            <input type="text" id="surname" name="surname" value="<?php echo htmlspecialchars($current_user['surname'] ?? ''); ?>" autocomplete="family-name" class="profile-input" aria-label="<?php echo htmlspecialchars(__('feedback.surname_label')); ?>"><br>

            <label for="language"><?php echo htmlspecialchars(__('profile.language_label')); ?></label><br>
            <select id="language" name="language" class="profile-input" style="margin-bottom: 1rem;">
                <option value="" <?php echo ($user_language === '' || $user_language === null) ? 'selected' : ''; ?>><?php echo htmlspecialchars(__('profile.lang_site_default')); ?></option>
                <?php foreach ($profile_languages as $code): ?>
                    <option value="<?php echo htmlspecialchars($code); ?>" <?php echo ($user_language === $code) ? 'selected' : ''; ?>>
                        <?php echo htmlspecialchars(strtoupper($code)); ?>
                    </option>
                <?php endforeach; ?>
            </select><br>

            <label for="timezone"><?php echo htmlspecialchars(__('onboarding.timezone_label')); ?></label><br>
            <select id="timezone" name="timezone" class="profile-input" style="margin-bottom: 1rem;">
                <?php
                $current_tz = $current_user['timezone'] ?? 'UTC';
                $all_timezones = timezone_identifiers_list();
                $grouped_timezones = [];
                foreach ($all_timezones as $tz) {
                    $parts = explode('/', $tz);
                    if (count($parts) > 1 && in_array($parts[0], ['Africa', 'America', 'Antarctica', 'Arctic', 'Asia', 'Atlantic', 'Australia', 'Europe', 'Indian', 'Pacific'])) {
                        $region = $parts[0];
                        $city = str_replace('_', ' ', implode('/', array_slice($parts, 1)));
                        $grouped_timezones[$region][$tz] = $city;
                    }
                }
                echo '<option value="UTC" ' . ($current_tz === 'UTC' ? 'selected' : '') . '>UTC (Coordinated Universal Time)</option>';
                foreach ($grouped_timezones as $region => $zones) {
                    asort($zones);
                    echo "<optgroup label=\"{$region}\">";
                    foreach ($zones as $tz_key => $city_label) {
                        $selected = ($current_tz === $tz_key) ? 'selected' : '';
                        echo "<option value=\"{$tz_key}\" {$selected}>{$city_label}</option>";
                    }
                    echo "</optgroup>";
                }
                ?>
            </select><br>

            <label for="date_format"><?php echo htmlspecialchars(__('onboarding.date_format_label')); ?></label><br>
            <select id="date_format" name="date_format" class="profile-input" style="margin-bottom: 1rem;">
                <?php
                $current_fmt = $current_user['date_format'] ?? 'd/m/Y';
                $date_formats = [
                    'd/m/Y'   => '23/07/2026 (UK Slash - DD/MM/YYYY)',
                    'd/m/y'   => '23/07/26 (Short Year - DD/MM/YY)',
                    'd.m.Y'   => '23.07.2026 (Dots - DD.MM.YYYY)',
                    'm/d/Y'   => '07/23/2026 (US Style - MM/DD/YYYY)',
                    'l j F Y' => 'Thursday 23 July 2026 (Full Text)'
                ];
                foreach ($date_formats as $fmt_key => $fmt_label) {
                    $selected = ($current_fmt === $fmt_key) ? 'selected' : '';
                    echo "<option value=\"{$fmt_key}\" {$selected}>{$fmt_label}</option>";
                }
                ?>
            </select><br>

            <label for="time_format"><?php echo htmlspecialchars(__('onboarding.time_format_label')); ?></label><br>
            <select id="time_format" name="time_format" class="profile-input" style="margin-bottom: 1rem;">
                <?php $current_time_fmt = $current_user['time_format'] ?? '24'; ?>
                <option value="24" <?php echo ($current_time_fmt === '24') ? 'selected' : ''; ?>><?php echo htmlspecialchars(__('onboarding.time_24')); ?></option>
                <option value="12" <?php echo ($current_time_fmt === '12') ? 'selected' : ''; ?>><?php echo htmlspecialchars(__('onboarding.time_12')); ?></option>
                <option value="none" <?php echo ($current_time_fmt === 'none') ? 'selected' : ''; ?>><?php echo htmlspecialchars(__('onboarding.time_none')); ?></option>
            </select><br>

            <label for="attribution_display_mode"><?php echo htmlspecialchars(__('onboarding.attribution_label')); ?></label><br>
            <small style="color: #666; display: block; margin-bottom: 0.5rem;">
                <?php echo htmlspecialchars(__('onboarding.attribution_desc1')); ?><br>
                • <strong><?php echo htmlspecialchars(__('onboarding.attr_anon_title')); ?></strong> <?php echo htmlspecialchars(__('onboarding.attr_anon_text')); ?><br>
                • <strong><?php echo htmlspecialchars(__('onboarding.attr_public_title')); ?></strong> <?php echo htmlspecialchars(__('onboarding.attr_public_text')); ?><br>
                • <strong><?php echo htmlspecialchars(__('onboarding.attr_vol_title')); ?></strong> <?php echo htmlspecialchars(__('onboarding.attr_vol_text')); ?>
            </small>
            <select id="attribution_display_mode" name="attribution_display_mode" class="profile-input" style="margin-bottom: 1rem;">
                <?php $mode = !empty($current_user['attribution_display_mode']) ? $current_user['attribution_display_mode'] : 'initials_random'; ?>
                <option value="initials_random" <?php echo ($mode === 'initials_random') ? 'selected' : ''; ?>><?php echo htmlspecialchars(__('onboarding.attr_opt_anon')); ?></option>
                <option value="full_name" <?php echo ($mode === 'full_name') ? 'selected' : ''; ?>><?php echo htmlspecialchars(__('onboarding.attr_opt_public')); ?></option>
                <option value="volunteers_only" <?php echo ($mode === 'volunteers_only') ? 'selected' : ''; ?>><?php echo htmlspecialchars(__('onboarding.attr_opt_vol')); ?></option>
            </select><br>

            <button type="submit" class="btn" style="margin-top: 0.5rem;"><?php echo htmlspecialchars(__('profile.update_details_btn')); ?></button>
        </form>
    </div>

    <hr style="border: 0.0625rem solid var(--border-color); margin: 1.5rem 0;">

    <div style="margin-bottom: 2rem;">
        <h4><?php echo htmlspecialchars(__('profile.email_heading')); ?></h4>
        <p><?php echo htmlspecialchars(__('profile.current_email_label')); ?> <strong><?php echo htmlspecialchars($current_user['email']); ?></strong>
           <?php if ($current_user['email_verified']): ?>
               <span style="color: green; font-weight: bold; font-size: 0.85rem;"><?php echo htmlspecialchars(__('profile.email_verified')); ?></span>
           <?php else: ?>
               <span style="color: orange; font-weight: bold; font-size: 0.85rem;"><?php echo htmlspecialchars(__('profile.email_unverified')); ?></span>
           <?php endif; ?>
        </p>
        <form method="POST" action="actions/save_profile.php">
            <?php echo csrf_field(); ?>
            <input type="hidden" name="action" value="update_email">
            <label for="email"><?php echo htmlspecialchars(__('profile.change_email_label')); ?></label><br>
            <input type="email" id="email" name="email" required autocomplete="email" class="profile-input" aria-label="<?php echo htmlspecialchars(__('profile.aria_new_email')); ?>"><br>
            <button type="submit" class="btn"><?php echo htmlspecialchars(__('profile.update_email_btn')); ?></button>
        </form>
    </div>

    <hr style="border: 0.0625rem solid var(--border-color); margin: 1.5rem 0;">

    <div style="margin-bottom: 2rem;">
        <h4><?php echo htmlspecialchars(__('profile.password_heading')); ?></h4>
        <form method="POST" action="actions/save_profile.php">
            <?php echo csrf_field(); ?>
            <input type="hidden" name="action" value="update_password">
            <input type="text" name="username" value="<?php echo htmlspecialchars($current_user['username']); ?>" autocomplete="username" style="display: none;" aria-hidden="true">

            <label for="current_password"><?php echo htmlspecialchars(__('profile.current_password_label')); ?></label><br>
            <input type="password" id="current_password" name="current_password" autocomplete="current-password" required class="profile-input" aria-label="<?php echo htmlspecialchars(__('profile.current_password_label')); ?>"><br>
            <label for="new_password"><?php echo htmlspecialchars(__('profile.new_password_label')); ?></label><br>
            <input type="password" id="new_password" name="new_password" autocomplete="new-password" required class="profile-input" aria-label="<?php echo htmlspecialchars(__('profile.new_password_label')); ?>"><br>
            <label for="confirm_password"><?php echo htmlspecialchars(__('profile.confirm_password_label')); ?></label><br>
            <input type="password" id="confirm_password" name="confirm_password" autocomplete="new-password" required class="profile-input" aria-label="<?php echo htmlspecialchars(__('profile.confirm_password_label')); ?>"><br>
            <div style="margin: 0.75rem 0 1rem 0; display: flex; align-items: center; gap: 0.5rem;">
                <input type="checkbox" id="show_passwords" onclick="
                    const type = this.checked ? 'text' : 'password';
                    document.getElementById('current_password').type = type;
                    document.getElementById('new_password').type = type;
                    document.getElementById('confirm_password').type = type;
                " style="cursor: pointer;">
                <label for="show_passwords" style="cursor: pointer; font-size: 0.9rem; font-weight: normal; margin-bottom: 0;"><?php echo htmlspecialchars(__('profile.show_passwords_label')); ?></label>
            </div>
            <button type="submit" class="btn"><?php echo htmlspecialchars(__('profile.update_password_btn')); ?></button>
        </form>
    </div>

    <hr style="border: 0.0625rem solid var(--border-color); margin: 1.5rem 0;">

    <div>
        <h4><?php echo htmlspecialchars(__('profile.tfa_heading')); ?></h4>
        <p><?php echo htmlspecialchars(__('profile.tfa_status_label')); ?> <strong><?php echo $current_user['two_fa_enabled'] ? '<span style="color: green;">' . htmlspecialchars(__('profile.tfa_enabled')) . '</span>' : '<span style="color: gray;">' . htmlspecialchars(__('profile.tfa_disabled')) . '</span>'; ?></strong></p>

        <?php if (!$current_user['two_fa_enabled']): ?>
            <form method="POST" action="actions/save_profile.php">
                <?php echo csrf_field(); ?>
                <input type="hidden" name="action" value="setup_2fa">
                <button type="submit" class="btn"><?php echo htmlspecialchars(__('profile.setup_tfa_btn')); ?></button>
            </form>
        <?php else: ?>
            <p style="font-size: 0.9rem; color: #666;"><?php echo htmlspecialchars(__('profile.tfa_active_desc')); ?></p>
            <?php if (!empty($_SESSION['new_raw_backup_codes'])): ?>
                <div class="backup-codes-box">
                    <h5 style="margin-top: 0; color: var(--danger-color);"><?php echo htmlspecialchars(__('profile.backup_codes_heading')); ?></h5>
                    <ul class="backup-codes-list">
                        <?php foreach ($_SESSION['new_raw_backup_codes'] as $nrp): ?>
                            <li><?php echo htmlspecialchars($nrp); ?></li>
                        <?php endforeach; ?>
                    </ul>
                    <a href="profile.php?action=download_new_codes" class="btn btn-secondary" style="font-size: 0.9rem; text-decoration: none; display: inline-block;"><?php echo htmlspecialchars(__('profile.download_codes_btn')); ?></a>
                </div>
            <?php endif; ?>
            <form method="POST" action="actions/save_profile.php" style="margin-top: 1rem;">
                <?php echo csrf_field(); ?>
                <input type="hidden" name="action" value="generate_backup_codes">
                <button type="submit" class="btn btn-secondary" onclick="return confirm('<?php echo htmlspecialchars(__('profile.generate_codes_confirm')); ?>');"><?php echo htmlspecialchars(__('profile.generate_codes_btn')); ?></button>
            </form>
        <?php endif; ?>
    </div>
</div>

<?php require_once '../partials/footer.php'; ?>
