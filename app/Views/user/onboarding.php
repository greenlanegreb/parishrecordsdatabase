<?php
/**
 * MIGRATED FILE MAPPING
 * ---------------------
 * Original Old File: user/onboarding.php/user/actions/save_onboarding.php
 * Migrated Date: 2026-08-05 05:05:55
 */
declare(strict_types=1);

/**
 * MIGRATED FILE MAPPING
 * ---------------------
 * Original Old File: user/onboarding.php
 * Migrated Date: 2026-08-04 13:00:00
 */

/** @string $error */
/** @array{id: int|string, username: string, first_name?: string, surname?: string, is_new_user?: int|string, timezone?: string, date_format?: string, time_format?: string, attribution_display_mode?: string} $currentUser */

require_once ROOT_PATH . '/partials/header.php';
$basePath = defined('BASE_PATH') ? rtrim(BASE_PATH, '/') : '';
?>

<div class="container d-flex justify-content-center align-items-center py-5" style="min-height: 80vh;" role="region" aria-label="<?= htmlspecialchars(__('onboarding.aria_region') ?? 'Onboarding', ENT_QUOTES, 'UTF-8') ?>">
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

        <form method="POST" action="<?= $basePath ?>/user/onboarding">
            <?= csrf_field() ?>
            <input type="hidden" name="apply_language" id="apply_language" value="0">
            <div class="mb-3">
                <label for="first_name" class="form-label small fw-bold"><?= htmlspecialchars(__('feedback.first_name_label'), ENT_QUOTES, 'UTF-8') ?></label>
                <input type="text" id="first_name" name="first_name" value="<?= htmlspecialchars($currentUser['first_name'] ?? '', ENT_QUOTES, 'UTF-8') ?>" required autocomplete="given-name" class="form-control form-control-sm">
            </div>

            <div class="mb-3">
                <label for="surname" class="form-label small fw-bold"><?= htmlspecialchars(__('feedback.surname_label'), ENT_QUOTES, 'UTF-8') ?></label>
                <input type="text" id="surname" name="surname" value="<?= htmlspecialchars($currentUser['surname'] ?? '', ENT_QUOTES, 'UTF-8') ?>" required autocomplete="family-name" class="form-control form-control-sm">
            </div>

            <div class="mb-3">
                <label for="language" class="form-label small fw-bold"><?= htmlspecialchars(__('onboarding.language_label') !== 'onboarding.language_label' ? __('onboarding.language_label') : __('profile.language_label'), ENT_QUOTES, 'UTF-8') ?></label>
                <select id="language" name="language" class="form-select form-select-sm"
                        onchange="document.getElementById('apply_language').value='1'; this.form.submit();">
                    <option value="" <?= ($userLanguage === '' || $userLanguage === '0') ? 'selected' : '' ?>><?= htmlspecialchars(__('onboarding.lang_site_default') !== 'onboarding.lang_site_default' ? __('onboarding.lang_site_default') : __('profile.lang_site_default'), ENT_QUOTES, 'UTF-8') ?></option>
                    <?php
                    $langFlags = [
                        'en' => '🇬🇧', 'fr' => '🇫🇷', 'es' => '🇪🇸', 'de' => '🇩🇪', 'it' => '🇮🇹',
                        'nl' => '🇳🇱', 'pt' => '🇵🇹', 'pl' => '🇵🇱', 'cy' => '🏴󠁧󠁢󠁷󠁬󠁳󠁿', 'gd' => '🏴󠁧󠁢󠁳󠁣󠁴󠁿', 'ga' => '🇮🇪',
                    ];
                    foreach (($onboardingLanguages ?? []) as $code):
                        $flag = $langFlags[strtolower(substr((string)$code, 0, 2))] ?? '🌐';
                    ?>
                        <option value="<?= htmlspecialchars($code, ENT_QUOTES, 'UTF-8') ?>" <?= ($userLanguage === $code) ? 'selected' : '' ?>>
                            <?= $flag ?> <?= htmlspecialchars(strtoupper($code), ENT_QUOTES, 'UTF-8') ?>
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
                        'd/m/Y'  => __('onboarding.date_fmt_dmy'),
                        'd/m/y'  => __('onboarding.date_fmt_dmy_short'),
                        'd.m.Y'  => __('onboarding.date_fmt_dots'),
                        'm/d/Y'  => __('onboarding.date_fmt_mdy'),
                        'l j F Y' => __('onboarding.date_fmt_full'),
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
                    <option value="initials_random" <?= ($mode === 'initials_random') ? 'selected' : '' ?>><?= htmlspecialchars(__('onboarding.attr_opt_anon'), ENT_QUOTES, 'UTF-8') ?></option>
                    <option value="full_name" <?= ($mode === 'full_name') ? 'selected' : '' ?>><?= htmlspecialchars(__('onboarding.attr_opt_public'), ENT_QUOTES, 'UTF-8') ?></option>
                    <option value="volunteers_only" <?= ($mode === 'volunteers_only') ? 'selected' : '' ?>><?= htmlspecialchars(__('onboarding.attr_opt_vol'), ENT_QUOTES, 'UTF-8') ?></option>
                </select>
            </div>

            <div class="d-grid gap-2">
                <button type="submit" name="after_save" value="profile" class="btn btn-primary py-2"
                        onclick="document.getElementById('apply_language').value='0';">
                    <?= htmlspecialchars(__('onboarding.btn_continue_2fa') !== 'onboarding.btn_continue_2fa' ? __('onboarding.btn_continue_2fa') : 'Continue to 2FA', ENT_QUOTES, 'UTF-8') ?>
                </button>
                <button type="submit" name="after_save" value="continue" class="btn btn-outline-secondary py-2"
                        onclick="document.getElementById('apply_language').value='0';">
                    <?= htmlspecialchars(__('onboarding.btn_skip_for_now') !== 'onboarding.btn_skip_for_now' ? __('onboarding.btn_skip_for_now') : 'Skip for now', ENT_QUOTES, 'UTF-8') ?>
                </button>
            </div>
        </form>

        <p class="text-muted small mt-3 mb-0">
            <?= htmlspecialchars(__('onboarding.security_hint') !== 'onboarding.security_hint' ? __('onboarding.security_hint') : 'You can change personal settings anytime by clicking your username in the menu.', ENT_QUOTES, 'UTF-8') ?>
        </p>
    </div>
</div>


<script>
(function () {
    var form = document.querySelector('form[action*="onboarding"]') || document.querySelector('form');
    if (!form) return;
    var key = 'prd_onboarding_draft';
    function save() {
        var data = {};
        form.querySelectorAll('input, select, textarea').forEach(function (el) {
            if (!el.name || el.type === 'hidden' || el.type === 'password') return;
            if (el.type === 'checkbox' || el.type === 'radio') {
                if (el.checked) data[el.name] = el.value;
            } else {
                data[el.name] = el.value;
            }
        });
        try { sessionStorage.setItem(key, JSON.stringify(data)); } catch (e) {}
    }
    function restore() {
        var raw;
        try { raw = sessionStorage.getItem(key); } catch (e) { return; }
        if (!raw) return;
        var data;
        try { data = JSON.parse(raw); } catch (e) { return; }
        Object.keys(data).forEach(function (name) {
            var el = form.querySelector('[name="' + name + '"]');
            if (!el || el.type === 'hidden') return;
            if (el.type === 'checkbox' || el.type === 'radio') {
                el.checked = (el.value === data[name]);
            } else if (el.value === '' || name === 'language') {
                // always keep language from server after switch; restore other empty fields
                if (name !== 'language') el.value = data[name];
            } else if (name === 'first_name' || name === 'surname') {
                if (!el.value) el.value = data[name];
            }
        });
        // Always restore typed names/settings even if server sent empty strings
        ['first_name','surname','timezone','date_format','time_format','attribution_display_mode'].forEach(function (name) {
            var el = form.querySelector('[name="' + name + '"]');
            if (el && data[name] !== undefined && data[name] !== '') el.value = data[name];
        });
    }
    form.addEventListener('input', save);
    form.addEventListener('change', save);
    restore();
    // Nav language links: save then let them proceed
    document.querySelectorAll('a[href*="lang="]').forEach(function (a) {
        a.addEventListener('click', save);
    });
})();
</script>

<?php require_once ROOT_PATH . '/partials/footer.php'; ?>
