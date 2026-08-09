<?php
/**
 * MIGRATED FILE MAPPING
 * ---------------------
 * Original Old File: user/profile.php/user/actions/save_profile.php
 * Migrated Date: 2026-08-05 05:11:34
 */
declare(strict_types=1);

/**
 * MIGRATED FILE MAPPING
 * ---------------------
 * Original Old File: user/profile.php
 * Migrated Date: 2026-08-04 13:30:00
 */

/** @string $error */
/** @string $message */
/** @array{id: int|string, username: string, email: string, email_verified?: int|string, first_name?: string, surname?: string, timezone?: string, date_format?: string, time_format?: string, attribution_display_mode?: string, language?: string, two_fa_enabled?: int|string} $currentUser */
/** @array<int, string> $profileLanguages */
/** @string $userLanguage */

require_once ROOT_PATH . '/partials/header.php';
$basePath = defined('BASE_PATH') ? rtrim(BASE_PATH, '/') : '';
?>

<div class="container py-4" style="max-width: 800px;" role="region" aria-label="<?= htmlspecialchars(__('profile.aria_region'), ENT_QUOTES, 'UTF-8') ?>">
    <h3 class="fw-bold text-dark mb-4"><?= htmlspecialchars(__('profile.heading'), ENT_QUOTES, 'UTF-8') ?></h3>

    <?php if (!empty($error)): ?>
        <div class="alert alert-danger alert-dismissible fade show" role="alert">
            <strong><?= htmlspecialchars($error, ENT_QUOTES, 'UTF-8') ?></strong>
            <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
        </div>
    <?php endif; ?>
    <?php if (!empty($message)): ?>
        <div class="alert alert-success alert-dismissible fade show" role="alert">
            <strong><?= htmlspecialchars($message, ENT_QUOTES, 'UTF-8') ?></strong>
            <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
        </div>
    <?php endif; ?>

    <!-- Personal Details Card -->
    <div class="card shadow-sm border-0 p-4 mb-4">
        <h4 class="h5 fw-bold text-dark mb-3"><?= htmlspecialchars(__('profile.personal_details_heading'), ENT_QUOTES, 'UTF-8') ?></h4>
        <form method="POST" action="<?= $basePath ?>/profile">
            <?= csrf_field() ?>
            <input type="hidden" name="action" value="update_personal_details">

            <div class="mb-3">
                <label for="first_name" class="form-label small fw-bold"><?= htmlspecialchars(__('feedback.first_name_label'), ENT_QUOTES, 'UTF-8') ?></label>
                <input type="text" id="first_name" name="first_name" value="<?= htmlspecialchars($currentUser['first_name'] ?? '', ENT_QUOTES, 'UTF-8') ?>" autocomplete="given-name" class="form-control form-control-sm" aria-label="<?= htmlspecialchars(__('feedback.first_name_label'), ENT_QUOTES, 'UTF-8') ?>">
            </div>

            <div class="mb-3">
                <label for="surname" class="form-label small fw-bold"><?= htmlspecialchars(__('feedback.surname_label'), ENT_QUOTES, 'UTF-8') ?></label>
                <input type="text" id="surname" name="surname" value="<?= htmlspecialchars($currentUser['surname'] ?? '', ENT_QUOTES, 'UTF-8') ?>" autocomplete="family-name" class="form-control form-control-sm" aria-label="<?= htmlspecialchars(__('feedback.surname_label'), ENT_QUOTES, 'UTF-8') ?>">
            </div>

            <div class="mb-3">
                <label for="language" class="form-label small fw-bold"><?= htmlspecialchars(__('profile.language_label'), ENT_QUOTES, 'UTF-8') ?></label>
                <select id="language" name="language" class="form-select form-select-sm">
                    <option value="" <?= ($userLanguage === '' || $userLanguage === '0') ? 'selected' : '' ?>><?= htmlspecialchars(__('profile.lang_site_default'), ENT_QUOTES, 'UTF-8') ?></option>
                    <?php foreach ($profileLanguages as $code): ?>
                        <option value="<?= htmlspecialchars($code, ENT_QUOTES, 'UTF-8') ?>" <?= ($userLanguage === $code) ? 'selected' : '' ?>>
                            <?= htmlspecialchars(strtoupper($code), ENT_QUOTES, 'UTF-8') ?>
                        </option>
                    <?php endforeach; ?>
                </select>
            </div>

            <div class="mb-3">
                <label for="timezone" class="form-label small fw-bold"><?= htmlspecialchars(__('onboarding.timezone_label'), ENT_QUOTES, 'UTF-8') ?></label>
                <select id="timezone" name="timezone" class="form-select form-select-sm">
                    <?php
                    $currentTz = isset($currentUser['timezone']) && is_string($currentUser['timezone']) ? $currentUser['timezone'] : 'UTC';
                    $allTimezones = timezone_identifiers_list();
                    /** @var array<string, array<string, string>> $groupedTimezones */
                    $groupedTimezones = [];
                    foreach ($allTimezones as $tz) {
                        $parts = explode('/', $tz);
                        if (count($parts) > 1 && in_array($parts[0], ['Africa', 'America', 'Antarctica', 'Arctic', 'Asia', 'Atlantic', 'Australia', 'Europe', 'Indian', 'Pacific'], true)) {
                            $region = $parts[0];
                            $city = str_replace('_', ' ', implode('/', array_slice($parts, 1)));
                            $groupedTimezones[$region][$tz] = $city;
                        }
                    }
                    echo '<option value="UTC" ' . ($currentTz === 'UTC' ? 'selected' : '') . '>UTC (Coordinated Universal Time)</option>';
                    foreach ($groupedTimezones as $region => $zones) {
                        asort($zones);
                        echo "<optgroup label=\"{$region}\">";
                        foreach ($zones as $tzKey => $cityLabel) {
                            $selected = ($currentTz === $tzKey) ? 'selected' : '';
                            echo "<option value=\"{$tzKey}\" {$selected}>{$cityLabel}</option>";
                        }
                        echo "</optgroup>";
                    }
                    ?>
                </select>
            </div>

            <div class="mb-3">
                <label for="date_format" class="form-label small fw-bold"><?= htmlspecialchars(__('onboarding.date_format_label'), ENT_QUOTES, 'UTF-8') ?></label>
                <select id="date_format" name="date_format" class="form-select form-select-sm">
                    <?php
                    $currentFmt = isset($currentUser['date_format']) && is_string($currentUser['date_format']) ? $currentUser['date_format'] : 'd/m/Y';
                    $dateFormats = [
                        'd/m/Y'  => '23/07/2026 (UK Slash - DD/MM/YYYY)',
                        'd/m/y'  => '23/07/26 (Short Year - DD/MM/YY)',
                        'd.m.Y'  => '23.07.2026 (Dots - DD.MM.YYYY)',
                        'm/d/Y'  => '07/23/2026 (US Style - MM/DD/YYYY)',
                        'l j F Y' => 'Thursday 23 July 2026 (Full Text)'
                    ];
                    foreach ($dateFormats as $fmtKey => $fmtLabel) {
                        $selected = ($currentFmt === $fmtKey) ? 'selected' : '';
                        echo "<option value=\"{$fmtKey}\" {$selected}>{$fmtLabel}</option>";
                    }
                    ?>
                </select>
            </div>

            <div class="mb-3">
                <label for="time_format" class="form-label small fw-bold"><?= htmlspecialchars(__('onboarding.time_format_label'), ENT_QUOTES, 'UTF-8') ?></label>
                <select id="time_format" name="time_format" class="form-select form-select-sm">
                    <?php $currentTimeFmt = isset($currentUser['time_format']) && is_string($currentUser['time_format']) ? $currentUser['time_format'] : '24'; ?>
                    <option value="24" <?= ($currentTimeFmt === '24') ? 'selected' : '' ?>><?= htmlspecialchars(__('onboarding.time_24'), ENT_QUOTES, 'UTF-8') ?></option>
                    <option value="12" <?= ($currentTimeFmt === '12') ? 'selected' : '' ?>><?= htmlspecialchars(__('onboarding.time_12'), ENT_QUOTES, 'UTF-8') ?></option>
                    <option value="none" <?= ($currentTimeFmt === 'none') ? 'selected' : '' ?>><?= htmlspecialchars(__('onboarding.time_none'), ENT_QUOTES, 'UTF-8') ?></option>
                </select>
            </div>

            <div class="mb-3">
                <label for="attribution_display_mode" class="form-label small fw-bold"><?= htmlspecialchars(__('onboarding.attribution_label'), ENT_QUOTES, 'UTF-8') ?></label>
                <small class="text-muted d-block mb-2">
                    <?= htmlspecialchars(__('onboarding.attribution_desc1'), ENT_QUOTES, 'UTF-8') ?><br>
                    • <strong><?= htmlspecialchars(__('onboarding.attr_anon_title'), ENT_QUOTES, 'UTF-8') ?></strong> <?= htmlspecialchars(__('onboarding.attr_anon_text'), ENT_QUOTES, 'UTF-8') ?><br>
                    • <strong><?= htmlspecialchars(__('onboarding.attr_public_title'), ENT_QUOTES, 'UTF-8') ?></strong> <?= htmlspecialchars(__('onboarding.attr_public_text'), ENT_QUOTES, 'UTF-8') ?><br>
                    • <strong><?= htmlspecialchars(__('onboarding.attr_vol_title'), ENT_QUOTES, 'UTF-8') ?></strong> <?= htmlspecialchars(__('onboarding.attr_vol_text'), ENT_QUOTES, 'UTF-8') ?>
                </small>
                <select id="attribution_display_mode" name="attribution_display_mode" class="form-select form-select-sm">
                    <?php $mode = !empty($currentUser['attribution_display_mode']) ? $currentUser['attribution_display_mode'] : 'initials_random'; ?>
                    <option value="initials_random" <?= ($mode === 'initials_random') ? 'selected' : '' ?>><?= htmlspecialchars(__('onboarding.attr_opt_anon'), ENT_QUOTES, 'UTF-8') ?></option>
                    <option value="full_name" <?= ($mode === 'full_name') ? 'selected' : '' ?>><?= htmlspecialchars(__('onboarding.attr_opt_public'), ENT_QUOTES, 'UTF-8') ?></option>
                    <option value="volunteers_only" <?= ($mode === 'volunteers_only') ? 'selected' : '' ?>><?= htmlspecialchars(__('onboarding.attr_opt_vol'), ENT_QUOTES, 'UTF-8') ?></option>
                </select>
            </div>

            <button type="submit" class="btn btn-sm btn-primary"><?= htmlspecialchars(__('profile.update_details_btn'), ENT_QUOTES, 'UTF-8') ?></button>
        </form>
    </div>

    <!-- Email Settings Card -->
    <div class="card shadow-sm border-0 p-4 mb-4">
        <h4 class="h5 fw-bold text-dark mb-2"><?= htmlspecialchars(__('profile.email_heading'), ENT_QUOTES, 'UTF-8') ?></h4>
        <p class="small text-muted mb-3">
            <?= htmlspecialchars(__('profile.current_email_label'), ENT_QUOTES, 'UTF-8') ?> <strong><?= htmlspecialchars($currentUser['email'], ENT_QUOTES, 'UTF-8') ?></strong>
            <?php if (!empty($currentUser['email_verified'])): ?>
                <span class="badge bg-success ms-2"><?= htmlspecialchars(__('profile.email_verified'), ENT_QUOTES, 'UTF-8') ?></span>
            <?php else: ?>
                <span class="badge bg-warning text-dark ms-2"><?= htmlspecialchars(__('profile.email_unverified'), ENT_QUOTES, 'UTF-8') ?></span>
            <?php endif; ?>
        </p>
        <form method="POST" action="<?= $basePath ?>/profile">
            <?= csrf_field() ?>
            <input type="hidden" name="action" value="update_email">
            <div class="mb-3">
                <label for="email" class="form-label small fw-bold"><?= htmlspecialchars(__('profile.change_email_label'), ENT_QUOTES, 'UTF-8') ?></label>
                <input type="email" id="email" name="email" required autocomplete="email" class="form-control form-control-sm" aria-label="<?= htmlspecialchars(__('profile.aria_new_email'), ENT_QUOTES, 'UTF-8') ?>">
            </div>
            <button type="submit" class="btn btn-sm btn-primary"><?= htmlspecialchars(__('profile.update_email_btn'), ENT_QUOTES, 'UTF-8') ?></button>
        </form>
    </div>

    <!-- Password Security Card -->
    <div class="card shadow-sm border-0 p-4 mb-4">
        <h4 class="h5 fw-bold text-dark mb-3"><?= htmlspecialchars(__('profile.password_heading'), ENT_QUOTES, 'UTF-8') ?></h4>
        <form method="POST" action="<?= $basePath ?>/profile">
            <?= csrf_field() ?>
            <input type="hidden" name="action" value="update_password">
            <input type="text" name="username" value="<?= htmlspecialchars($currentUser['username'], ENT_QUOTES, 'UTF-8') ?>" autocomplete="username" class="d-none" aria-hidden="true">

            <div class="mb-3">
                <label for="current_password" class="form-label small fw-bold"><?= htmlspecialchars(__('profile.current_password_label'), ENT_QUOTES, 'UTF-8') ?></label>
                <input type="password" id="current_password" name="current_password" autocomplete="current-password" required class="form-control form-control-sm" aria-label="<?= htmlspecialchars(__('profile.current_password_label'), ENT_QUOTES, 'UTF-8') ?>">
            </div>
            <div class="mb-3">
                <label for="new_password" class="form-label small fw-bold"><?= htmlspecialchars(__('profile.new_password_label'), ENT_QUOTES, 'UTF-8') ?></label>
                <input type="password" id="new_password" name="new_password" autocomplete="new-password" required class="form-control form-control-sm" aria-label="<?= htmlspecialchars(__('profile.new_password_label'), ENT_QUOTES, 'UTF-8') ?>">
            </div>
            <div class="mb-3">
                <label for="confirm_password" class="form-label small fw-bold"><?= htmlspecialchars(__('profile.confirm_password_label'), ENT_QUOTES, 'UTF-8') ?></label>
                <input type="password" id="confirm_password" name="confirm_password" autocomplete="new-password" required class="form-control form-control-sm" aria-label="<?= htmlspecialchars(__('profile.confirm_password_label'), ENT_QUOTES, 'UTF-8') ?>">
            </div>
            <div class="form-check mb-3">
                <input type="checkbox" id="show_passwords" class="form-check-input" onclick="
                    const type = this.checked ? 'text' : 'password';
                    document.getElementById('current_password').type = type;
                    document.getElementById('new_password').type = type;
                    document.getElementById('confirm_password').type = type;
                ">
                <label for="show_passwords" class="form-check-label small"><?= htmlspecialchars(__('profile.show_passwords_label'), ENT_QUOTES, 'UTF-8') ?></label>
            </div>
            <button type="submit" class="btn btn-sm btn-primary"><?= htmlspecialchars(__('profile.update_password_btn'), ENT_QUOTES, 'UTF-8') ?></button>
        </form>
    </div>

    <!-- 2FA & Backup Codes Card -->
    <div class="card shadow-sm border-0 p-4">
        <h4 class="h5 fw-bold text-dark mb-2"><?= htmlspecialchars(__('profile.tfa_heading'), ENT_QUOTES, 'UTF-8') ?></h4>
        <p class="small text-muted mb-3">
            <?= htmlspecialchars(__('profile.tfa_status_label'), ENT_QUOTES, 'UTF-8') ?> 
            <strong>
                <?= !empty($currentUser['two_fa_enabled']) ? '<span class="text-success">' . htmlspecialchars(__('profile.tfa_enabled'), ENT_QUOTES, 'UTF-8') . '</span>' : '<span class="text-secondary">' . htmlspecialchars(__('profile.tfa_disabled'), ENT_QUOTES, 'UTF-8') . '</span>' ?>
            </strong>
        </p>

        <?php if (empty($currentUser['two_fa_enabled'])): ?>
            <form method="POST" action="<?= $basePath ?>/profile">
                <?= csrf_field() ?>
                <input type="hidden" name="action" value="setup_2fa">
                <button type="submit" class="btn btn-sm btn-primary"><?= htmlspecialchars(__('profile.setup_tfa_btn'), ENT_QUOTES, 'UTF-8') ?></button>
            </form>
        <?php else: ?>
            <p class="small text-muted mb-3"><?= htmlspecialchars(__('profile.tfa_active_desc'), ENT_QUOTES, 'UTF-8') ?></p>
            <?php if (!empty($_SESSION['new_raw_backup_codes']) && is_array($_SESSION['new_raw_backup_codes'])): ?>
                <div class="card bg-light border-0 p-3 mb-3">
                    <h5 class="h6 fw-bold text-danger mb-2"><?= htmlspecialchars(__('profile.backup_codes_heading'), ENT_QUOTES, 'UTF-8') ?></h5>
                    <ul class="list-unstyled mb-3 font-monospace small">
                        <?php foreach ($_SESSION['new_raw_backup_codes'] as $nrp): ?>
                            <li><?= htmlspecialchars($nrp, ENT_QUOTES, 'UTF-8') ?></li>
                        <?php endforeach; ?>
                    </ul>
                    <a href="<?= $basePath ?>/profile?action=download_new_codes" class="btn btn-sm btn-outline-secondary text-decoration-none d-inline-block"><?= htmlspecialchars(__('profile.download_codes_btn'), ENT_QUOTES, 'UTF-8') ?></a>
                </div>
            <?php endif; ?>
            <form method="POST" action="<?= $basePath ?>/profile" class="mt-2">
                <?= csrf_field() ?>
                <input type="hidden" name="action" value="generate_backup_codes">
                <button type="submit" class="btn btn-sm btn-outline-secondary" onclick="return confirm('<?= htmlspecialchars(__('profile.generate_codes_confirm'), ENT_QUOTES, 'UTF-8') ?>');"><?= htmlspecialchars(__('profile.generate_codes_btn'), ENT_QUOTES, 'UTF-8') ?></button>
            </form>
        <?php endif; ?>
    </div>
</div>

<?php require_once ROOT_PATH . '/partials/footer.php'; ?>
