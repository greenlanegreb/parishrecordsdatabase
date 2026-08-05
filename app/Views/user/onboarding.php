<?php
/**
 * MIGRATED FILE MAPPING
 * ---------------------
 * Original Old File: user/onboarding.php/user/actions/save_onboarding.php
 * Migrated Date: 2026-08-05 05:05:55
 */declare(strict_types=1);

/**
 * MIGRATED FILE MAPPING
 * ---------------------
 * Original Old File: user/onboarding.php
 * Migrated Date: 2026-08-04 13:00:00
 */

/** @string $error */
/** @array{id: int|string, username: string, first_name?: string, surname?: string, is_new_user?: int|string, timezone?: string, date_format?: string, time_format?: string, attribution_display_mode?: string} $currentUser */

?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?= htmlspecialchars(__('onboarding.page_title'), ENT_QUOTES, 'UTF-8') ?></title>
    <!-- Bootstrap 5 CSS CDN -->
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet" integrity="sha384-QWTKZyjpPEjISv5WaRU9OFeRpok6YctnYmDr5pNlyT2bRjXh0JMhjY6hW+ALEwIH" crossorigin="anonymous">
    <link rel="stylesheet" href="/assets/style.css">
</head>
<body class="bg-light d-flex justify-content-center align-items-center min-vh-100">
    <div class="card shadow-sm border-0 p-4 w-100" style="max-width: 550px;">
        <h2 class="fw-bold text-primary mb-2"><?= htmlspecialchars(__('onboarding.heading'), ENT_QUOTES, 'UTF-8') ?> 🎉</h2>
        <p class="text-muted small mb-4">
            <?= htmlspecialchars(__('onboarding.subheading'), ENT_QUOTES, 'UTF-8') ?>
        </p>

        <?php if (!empty($error)): ?>
            <div class="alert alert-danger alert-dismissible fade show" role="alert">
                <strong><?= htmlspecialchars($error, ENT_QUOTES, 'UTF-8') ?></strong>
                <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
            </div>
        <?php endif; ?>

        <form method="POST" action="/user/actions/save_onboarding.php">
            <?= csrf_field() ?>
            <div class="mb-3">
                <label for="first_name" class="form-label small fw-bold"><?= htmlspecialchars(__('feedback.first_name_label'), ENT_QUOTES, 'UTF-8') ?></label>
                <input type="text" id="first_name" name="first_name" value="<?= htmlspecialchars($currentUser['first_name'] ?? '', ENT_QUOTES, 'UTF-8') ?>" required autocomplete="given-name" class="form-control form-control-sm">
            </div>

            <div class="mb-3">
                <label for="surname" class="form-label small fw-bold"><?= htmlspecialchars(__('feedback.surname_label'), ENT_QUOTES, 'UTF-8') ?></label>
                <input type="text" id="surname" name="surname" value="<?= htmlspecialchars($currentUser['surname'] ?? '', ENT_QUOTES, 'UTF-8') ?>" required autocomplete="family-name" class="form-control form-control-sm">
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
                        'd/m/Y' => '23/07/2026 (UK Slash - DD/MM/YYYY)',
                        'd/m/y' => '23/07/26 (Short Year - DD/MM/YY)',
                        'd.m.Y' => '23.07.2026 (Dots - DD.MM.YYYY)',
                        'm/d/Y' => '07/23/2026 (US Style - MM/DD/YYYY)',
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
                    <option value="24" <?= ($currentTimeFmt === '24') ? 'selected' : '' ?>><= htmlspecialchars(__('onboarding.time_24'), ENT_QUOTES, 'UTF-8') ?></option>
                    <option value="12" <?= ($currentTimeFmt === '12') ? 'selected' : '' ?>><= htmlspecialchars(__('onboarding.time_12'), ENT_QUOTES, 'UTF-8') ?></option>
                    <option value="none" <?= ($currentTimeFmt === 'none') ? 'selected' : '' ?>><= htmlspecialchars(__('onboarding.time_none'), ENT_QUOTES, 'UTF-8') ?></option>
                </select>
            </div>

            <div class="mb-4">
                <label for="attribution_display_mode" class="form-label small fw-bold"><?= htmlspecialchars(__('onboarding.attribution_label'), ENT_QUOTES, 'UTF-8') ?></label>
                <small class="text-muted d-block mb-2" style="line-height: 1.4;">
                    <?= htmlspecialchars(__('onboarding.attribution_desc1'), ENT_QUOTES, 'UTF-8') ?><br>
                    • <strong><?= htmlspecialchars(__('onboarding.attr_anon_title'), ENT_QUOTES, 'UTF-8') ?></strong> <?= htmlspecialchars(__('onboarding.attr_anon_text'), ENT_QUOTES, 'UTF-8') ?><br>
                    • <strong><?= htmlspecialchars(__('onboarding.attr_public_title'), ENT_QUOTES, 'UTF-8') ?></strong> <?= htmlspecialchars(__('onboarding.attr_public_text'), ENT_QUOTES, 'UTF-8') ?><br>
                    • <strong><?= htmlspecialchars(__('onboarding.attr_vol_title'), ENT_QUOTES, 'UTF-8') ?></strong> <?= htmlspecialchars(__('onboarding.attr_vol_text'), ENT_QUOTES, 'UTF-8') ?>
                </small>
                <select id="attribution_display_mode" name="attribution_display_mode" class="form-select form-select-sm">
                    <?php $mode = !empty($currentUser['attribution_display_mode']) ? $currentUser['attribution_display_mode'] : 'initials_random'; ?>
                    <option value="initials_random" <?= ($mode === 'initials_random') ? 'selected' : '' ?>><= htmlspecialchars(__('onboarding.attr_opt_anon'), ENT_QUOTES, 'UTF-8') ?></option>
                    <option value="full_name" <?= ($mode === 'full_name') ? 'selected' : '' ?>><= htmlspecialchars(__('onboarding.attr_opt_public'), ENT_QUOTES, 'UTF-8') ?></option>
                    <option value="volunteers_only" <?= ($mode === 'volunteers_only') ? 'selected' : '' ?>><= htmlspecialchars(__('onboarding.attr_opt_vol'), ENT_QUOTES, 'UTF-8') ?></option>
                </select>
            </div>

            <button type="submit" class="btn btn-primary w-100 py-2"><?= htmlspecialchars(__('onboarding.submit_btn'), ENT_QUOTES, 'UTF-8') ?></button>
        </form>
    </div>
    <!-- Bootstrap 5 JS Bundle CDN -->
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js" integrity="sha384-YvpcrYf0tY3lHB60NNkmXc5s9fDVZLESaAA55NDzOxhy9GkcIdslK1eN7N6jIeHz" crossorigin="anonymous"></script>
</body>
</html>
