<?php
// user/onboarding.php - First-time user setup wizard
require_once '../db/db.php';
require_once '../db/auth_helpers.php';
session_start();

// Enforce dynamic permission check for onboarding access (automatically registers 'access_onboarding' if new)
require_permission($pdo, 'access_onboarding', 'Allows accessing the first-time user onboarding setup wizard');
$current_user = get_current_user_data($pdo);

// If they are no longer marked as a new user, redirect them away from the wizard
if (empty($current_user['is_new_user'])) {
    header('Location: data_entry.php');
    exit;
}

$error = $_SESSION['error'] ?? '';
unset($_SESSION['error']);
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>Welcome - Account Setup Wizard</title>
    <link rel="stylesheet" href="../assets/style.css">
</head>
<body style="display: flex; justify-content: center; align-items: center; min-height: 100vh; background-color: #f4f7f6;">
    <div class="search-box-container" style="width: 100%; max-width: 550px; background: white; padding: 2.5rem; border-radius: 8px; box-shadow: 0 4px 15px rgba(0,0,0,0.05);">
        <h2 style="margin-top: 0; color: var(--primary-color, #007bff);">Welcome to the Team! 🎉</h2>
        <p style="color: #666; font-size: 0.95rem; margin-bottom: 1.5rem;">
            Before you start entering records, please take a moment to configure your regional display settings and privacy preferences. You can always update these later in your profile.
        </p>

        <?php if (!empty($error)): ?>
            <p class="alert-danger"><strong><?php echo htmlspecialchars($error); ?></strong></p>
        <?php endif; ?>

        <form method="POST" action="actions/save_onboarding.php">
            <?php echo csrf_field(); ?>
            <div style="margin-bottom: 1rem;">
                <label for="first_name"><strong>First Name:</strong></label><br>
                <input type="text" id="first_name" name="first_name" value="<?php echo htmlspecialchars($current_user['first_name'] ?? ''); ?>" required autocomplete="given-name" class="profile-input" style="width:100%; padding: 0.4rem;">
            </div>

            <div style="margin-bottom: 1rem;">
                <label for="surname"><strong>Surname:</strong></label><br>
                <input type="text" id="surname" name="surname" value="<?php echo htmlspecialchars($current_user['surname'] ?? ''); ?>" required autocomplete="family-name" class="profile-input" style="width:100%; padding: 0.4rem;">
            </div>

            <div style="margin-bottom: 1rem;">
                <label for="timezone"><strong>Timezone / Region:</strong></label><br>
                <select id="timezone" name="timezone" class="profile-input" style="width:100%; padding: 0.4rem;">
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
                </select>
            </div>

            <div style="margin-bottom: 1rem;">
                <label for="date_format"><strong>Date Display Format:</strong></label><br>
                <select id="date_format" name="date_format" class="profile-input" style="width:100%; padding: 0.4rem;">
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
                </select>
            </div>

            <div style="margin-bottom: 1rem;">
                <label for="time_format"><strong>Clock Format (Time Display):</strong></label><br>
                <select id="time_format" name="time_format" class="profile-input" style="width:100%; padding: 0.4rem;">
                    <?php $current_time_fmt = $current_user['time_format'] ?? '24'; ?>
                    <option value="24" <?php echo ($current_time_fmt === '24') ? 'selected' : ''; ?>>24-Hour (e.g., 16:07)</option>
                    <option value="12" <?php echo ($current_time_fmt === '12') ? 'selected' : ''; ?>>12-Hour AM/PM (e.g., 04:07 PM)</option>
                    <option value="none" <?php echo ($current_time_fmt === 'none') ? 'selected' : ''; ?>>Date Only (Hide Time Completely)</option>
                </select>
            </div>

            <div style="margin-bottom: 1.5rem;">
                <label for="leaderboard_display_mode"><strong>Leaderboard Display Preference:</strong></label><br>
                <select id="leaderboard_display_mode" name="leaderboard_display_mode" class="profile-input" style="width:100%; padding: 0.4rem;">
                    <?php $mode = !empty($current_user['leaderboard_display_mode']) ? $current_user['leaderboard_display_mode'] : 'initials_random'; ?>
                    <option value="initials_random" <?php echo ($mode === 'initials_random') ? 'selected' : ''; ?>>Anonymous (Initials & Random Number) - Recommended</option>
                    <option value="full_name" <?php echo ($mode === 'full_name') ? 'selected' : ''; ?>>Public (Show Full Name)</option>
                    <option value="volunteers_only" <?php echo ($mode === 'volunteers_only') ? 'selected' : ''; ?>>Volunteers Only (Hide from Public)</option>
                </select>
            </div>

            <button type="submit" class="btn" style="width: 100%; padding: 0.75rem; font-size: 1rem;">Save Preferences & Enter Dashboard</button>
        </form>
    </div>
</body>
</html>
