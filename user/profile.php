<?php
// profile.php - User profile and security settings view
require_once '../db/db.php';
require_once '../db/auth_helpers.php';
require_once '../includes/functions.php';
session_start();

// Enforce standard user/moderator/admin authentication via central helper
require_role($pdo, ['user', 'moderator', 'admin']);
$current_user = get_current_user_data($pdo);

// Pull dynamic system name from database with a clean fallback
$system_name = (function_exists('get_system_name') && isset($pdo)) ? get_system_name($pdo) : "Parish Records Directory (PRD)";
$system_slug = strtolower(preg_replace('/[^a-zA-Z0-9]+/', '-', $system_name));

$message = $_SESSION['message'] ?? '';
$error = $_SESSION['error'] ?? '';
unset($_SESSION['message'], $_SESSION['error']);

// Handle direct download of newly generated codes from profile
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

    <div class="search-box-container profile-container" role="region" aria-label="User Profile Management">
        <h3>User Profile & Security</h3>

        <?php if (!empty($error)): ?>
            <p class="alert-danger"><strong><?php echo htmlspecialchars($error); ?></strong></p>
        <?php endif; ?>
        <?php if (!empty($message)): ?>
            <p class="alert-success"><strong><?php echo htmlspecialchars($message); ?></strong></p>
        <?php endif; ?>

        <!-- Personal Details Section -->
        <div style="margin-bottom: 2rem;">
            <h4>Personal Details</h4>
            <form method="POST" action="actions/save_profile.php">
                <?php echo csrf_field(); ?>
                <input type="hidden" name="action" value="update_personal_details">
                
                <label for="first_name">First Name:</label><br>
                <input type="text" id="first_name" name="first_name" value="<?php echo htmlspecialchars($current_user['first_name'] ?? ''); ?>" autocomplete="given-name" class="profile-input" aria-label="First Name"><br>

                <label for="surname">Surname:</label><br>
                <input type="text" id="surname" name="surname" value="<?php echo htmlspecialchars($current_user['surname'] ?? ''); ?>" autocomplete="family-name" class="profile-input" aria-label="Surname"><br>

                <label for="timezone">Timezone / Region:</label><br>
                <select id="timezone" name="timezone" class="profile-input" style="margin-bottom: 1rem;">
                    <?php 
                    $current_tz = $current_user['timezone'] ?? 'UTC';
                    $all_timezones = timezone_identifiers_list();
                    
                    // Group timezones by their primary region/continent globally
                    $grouped_timezones = [];
                    foreach ($all_timezones as $tz) {
                        $parts = explode('/', $tz);
                        if (count($parts) > 1 && in_array($parts[0], ['Africa', 'America', 'Antarctica', 'Arctic', 'Asia', 'Atlantic', 'Australia', 'Europe', 'Indian', 'Pacific'])) {
                            $region = $parts[0];
                            $city = str_replace('_', ' ', implode('/', array_slice($parts, 1)));
                            $grouped_timezones[$region][$tz] = $city;
                        }
                    }
                    
                    // Keep UTC prominent at the very top
                    echo '<option value="UTC" ' . ($current_tz === 'UTC' ? 'selected' : '') . '>UTC (Coordinated Universal Time)</option>';

                    // Loop through each global region and populate option groups alphabetically
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

                <label for="date_format">Date Display Format:</label><br>
                <select id="date_format" name="date_format" class="profile-input" style="margin-bottom: 1rem;">
                    <?php 
                    $current_fmt = $current_user['date_format'] ?? 'd/m/Y';
                    $date_formats = [
                        'd/m/Y' => '23/07/2026 (UK Slash - DD/MM/YYYY)',
                        'd/m/y' => '23/07/26 (Short Year - DD/MM/YY)',
                        'd.m.Y' => '23.07.2026 (Dots - DD.MM.YYYY)',
                        'm/d/Y' => '07/23/2026 (US Style - MM/DD/YYYY)',
                        'l j F Y' => 'Thursday 23 July 2026 (Full Text)'
                    ];
                    foreach ($date_formats as $fmt_key => $fmt_label) {
                        $selected = ($current_fmt === $fmt_key) ? 'selected' : '';
                        echo "<option value=\"{$fmt_key}\" {$selected}>{$fmt_label}</option>";
                    }
                    ?>
                </select><br>

                <label for="time_format">Clock Format (Time Display):</label><br>
                <select id="time_format" name="time_format" class="profile-input" style="margin-bottom: 1rem;">
                    <?php 
                    $current_time_fmt = $current_user['time_format'] ?? '24';
                    ?>
                    <option value="24" <?php echo ($current_time_fmt === '24') ? 'selected' : ''; ?>>24-Hour (e.g., 16:07)</option>
                    <option value="12" <?php echo ($current_time_fmt === '12') ? 'selected' : ''; ?>>12-Hour AM/PM (e.g., 04:07 PM)</option>
                    <option value="none" <?php echo ($current_time_fmt === 'none') ? 'selected' : ''; ?>>Date Only (Hide Time Completely)</option>
                </select><br>

                <label for="leaderboard_display_mode">Leaderboard Display Preference:</label><br>
                <select id="leaderboard_display_mode" name="leaderboard_display_mode" class="profile-input" style="margin-bottom: 1rem;">
                    <?php $mode = !empty($current_user['leaderboard_display_mode']) ? $current_user['leaderboard_display_mode'] : 'initials_random'; ?>
                    <option value="initials_random" <?php echo ($mode === 'initials_random') ? 'selected' : ''; ?>>Anonymous (Initials & Random Number) - Recommended</option>
                    <option value="full_name" <?php echo ($mode === 'full_name') ? 'selected' : ''; ?>>Public (Show Full Name)</option>
                    <option value="volunteers_only" <?php echo ($mode === 'volunteers_only') ? 'selected' : ''; ?>>Volunteers Only (Hide from Public)</option>
                </select><br>

                <button type="submit" class="btn" style="margin-top: 0.5rem;">Update Personal Details</button>
            </form>
        </div>

        <hr style="border: 0.0625rem solid var(--border-color); margin: 1.5rem 0;">

        <!-- Email Management Section -->
        <div style="margin-bottom: 2rem;">
            <h4>Email Address</h4>
            <p>Current Email: <strong><?php echo htmlspecialchars($current_user['email']); ?></strong> 
               <?php if ($current_user['email_verified']): ?>
                   <span style="color: green; font-weight: bold; font-size: 0.85rem;">(Verified)</span>
               <?php else: ?>
                   <span style="color: orange; font-weight: bold; font-size: 0.85rem;">(Unverified - Check inbox)</span>
               <?php endif; ?>
            </p>
            <form method="POST" action="actions/save_profile.php">
                <?php echo csrf_field(); ?>
                <input type="hidden" name="action" value="update_email">
                <label for="email">Change Email Address:</label><br>
                <input type="email" id="email" name="email" required autocomplete="email" class="profile-input" aria-label="New email address"><br>
                <button type="submit" class="btn">Update Email & Verify</button>
            </form>
        </div>

        <hr style="border: 0.0625rem solid var(--border-color); margin: 1.5rem 0;">

        <!-- Password Change Section -->
        <div style="margin-bottom: 2rem;">
            <h4>Change Password</h4>
            <form method="POST" action="actions/save_profile.php">
                <?php echo csrf_field(); ?>
                <input type="hidden" name="action" value="update_password">
                <input type="text" name="username" value="<?php echo htmlspecialchars($current_user['username']); ?>" autocomplete="username" style="display: none;" aria-hidden="true">
                
                <label for="current_password">Current Password:</label><br>
                <input type="password" id="current_password" name="current_password" autocomplete="current-password" required class="profile-input" aria-label="Current password"><br>

                <label for="new_password">New Password (min 8 chars):</label><br>
                <input type="password" id="new_password" name="new_password" autocomplete="new-password" required class="profile-input" aria-label="New password"><br>

                <label for="confirm_password">Confirm New Password:</label><br>
                <input type="password" id="confirm_password" name="confirm_password" autocomplete="new-password" required class="profile-input" aria-label="Confirm new password"><br>

                <!-- Show/Hide Passwords Checkbox Toggle -->
                <div style="margin: 0.75rem 0 1rem 0; display: flex; align-items: center; gap: 0.5rem;">
                    <input type="checkbox" id="show_passwords" onclick="
                        const type = this.checked ? 'text' : 'password';
                        document.getElementById('current_password').type = type;
                        document.getElementById('new_password').type = type;
                        document.getElementById('confirm_password').type = type;
                    " style="cursor: pointer;">
                    <label for="show_passwords" style="cursor: pointer; font-size: 0.9rem; font-weight: normal; margin-bottom: 0;">Show passwords in plain text</label>
                </div>

                <button type="submit" class="btn">Update Password</button>
            </form>
        </div>

        <hr style="border: 0.0625rem solid var(--border-color); margin: 1.5rem 0;">

        <!-- 2FA Management Section -->
        <div>
            <h4>Two-Factor Authentication (2FA)</h4>
            <p>Status: <strong><?php echo $current_user['two_fa_enabled'] ? '<span style="color: green;">Enabled</span>' : '<span style="color: gray;">Disabled</span>'; ?></strong></p>
            
            <?php if (!$current_user['two_fa_enabled']): ?>
                <form method="POST" action="actions/save_profile.php">
                    <?php echo csrf_field(); ?>
                    <input type="hidden" name="action" value="setup_2fa">
                    <button type="submit" class="btn">Set Up Google Authenticator</button>
                </form>
            <?php else: ?>
                <p style="font-size: 0.9rem; color: #666;">2FA is actively protecting your account login.</p>

                <?php if (!empty($_SESSION['new_raw_backup_codes'])): ?>
                    <div class="backup-codes-box">
                        <h5 style="margin-top: 0; color: var(--danger-color);">Your New Backup Codes</h5>
                        <ul class="backup-codes-list">
                            <?php foreach ($_SESSION['new_raw_backup_codes'] as $nrp): ?>
                                <li><?php echo htmlspecialchars($nrp); ?></li>
                            <?php endforeach; ?>
                        </ul>
                        <a href="profile.php?action=download_new_codes" class="btn btn-secondary" style="font-size: 0.9rem; text-decoration: none; display: inline-block;">Download New Codes as .txt</a>
                    </div>
                <?php endif; ?>

                <form method="POST" action="actions/save_profile.php" style="margin-top: 1rem;">
                    <?php echo csrf_field(); ?>
                    <input type="hidden" name="action" value="generate_backup_codes">
                    <button type="submit" class="btn btn-secondary" onclick="return confirm('Are you sure? This will invalidate any existing backup codes.');">Generate New Backup Codes</button>
                </form>
            <?php endif; ?>
        </div>
    </div>

    <?php require_once '../partials/footer.php'; ?>
