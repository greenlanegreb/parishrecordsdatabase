<?php
declare(strict_types=1);
/** Translate with fallback when lang key is missing. @return string */
$__t = static function (string $key, string $fallback = ''): string {
    $v = function_exists('__') ? (string) __($key) : $key;
    if ($v !== $key && $v !== '') {
        return $v;
    }
    return $fallback !== '' ? $fallback : $key;
};

?>
<!-- TAB 1: Core & Mail Settings -->
<div class="tab-pane fade show active" id="panel-core" role="tabpanel" aria-labelledby="tab-core">

    <!-- Database backup + schema updates -->
    <div class="card shadow-sm border-0 bg-light p-4 mb-4">
        <h4 class="h5 fw-bold text-dark mb-2"><?= htmlspecialchars(__('settings.db_updates_heading'), ENT_QUOTES, 'UTF-8') ?></h4>
        <p class="mb-3 text-muted small">
            <?= htmlspecialchars(__('settings.schema_current'), ENT_QUOTES, 'UTF-8') ?> <strong class="text-dark"><?= $schemaCurrent ?></strong>
            &nbsp;·&nbsp;
            <?= htmlspecialchars(__('settings.schema_latest'), ENT_QUOTES, 'UTF-8') ?> <strong class="text-dark"><?= $schemaLatest ?></strong>
        </p>

        <form method="POST" action="<?= $basePath ?>/admin/backup/download" class="mb-2">
            <?= csrf_field() ?>
            <button type="submit" class="btn btn-sm btn-outline-secondary"><?= htmlspecialchars(__('settings.download_backup_btn'), ENT_QUOTES, 'UTF-8') ?></button>
        </form>
        <p class="text-muted small mb-3">
            <?= htmlspecialchars(__('settings.download_backup_desc'), ENT_QUOTES, 'UTF-8') ?>
        </p>

        <?php if ($schemaNeedsUpdate): ?>
            <div class="alert alert-warning py-2 px-3 small mb-2">
                <?= htmlspecialchars(__('settings.schema_update_notice'), ENT_QUOTES, 'UTF-8') ?>
            </div>
            <form method="POST" action="<?= $basePath ?>/admin/migration/run" class="mt-2" onsubmit="return confirm('<?= htmlspecialchars(__('settings.migration_confirm'), ENT_QUOTES, 'UTF-8') ?>');">
                <?= csrf_field() ?>
                <button type="submit" class="btn btn-sm btn-primary"><?= htmlspecialchars(__('settings.update_db_btn'), ENT_QUOTES, 'UTF-8') ?></button>
            </form>
        <?php else: ?>
            <p class="text-success small fw-bold mb-0"><?= htmlspecialchars(__('settings.schema_uptodate'), ENT_QUOTES, 'UTF-8') ?></p>
        <?php endif; ?>
    </div>

    <!-- Core Settings & Mail Form Card -->
    <div class="card shadow-sm border-0 p-4 mb-4">
        <form method="POST" action="<?= $basePath ?>/admin/settings/store">
            <?= csrf_field() ?>
            <h4 class="h5 fw-bold text-dark mb-3"><?= htmlspecialchars(__('settings.core_sys_heading'), ENT_QUOTES, 'UTF-8') ?></h4>
            
            <div class="mb-3">
                <label for="system_name" class="form-label fw-bold"><?= htmlspecialchars(__('settings.sys_name_label'), ENT_QUOTES, 'UTF-8') ?></label>
                <input type="text" id="system_name" name="system_name" value="<?= htmlspecialchars($currentSystemName, ENT_QUOTES, 'UTF-8') ?>" required class="form-control">
            </div>

            <div class="mb-4">
                <label for="default_language" class="form-label fw-bold"><?= htmlspecialchars(__('settings.default_lang_label'), ENT_QUOTES, 'UTF-8') ?></label>
                <select id="default_language" name="default_language" class="form-select max-width-320">
                    <?php foreach ($availableLanguages as $code): ?>
                        <option value="<?= htmlspecialchars($code, ENT_QUOTES, 'UTF-8') ?>" <?= ($currentDefaultLanguage === $code) ? 'selected' : '' ?>>
                            <?= htmlspecialchars(strtoupper($code), ENT_QUOTES, 'UTF-8') ?>
                        </option>
                    <?php endforeach; ?>
                </select>
                <div class="form-text"><?= htmlspecialchars(__('settings.default_lang_desc'), ENT_QUOTES, 'UTF-8') ?></div>
            </div>

            <div class="mb-3">
              <label for="default_timezone" class="form-label fw-bold"><?= htmlspecialchars($__t('settings.default_timezone', 'Default timezone'), ENT_QUOTES, 'UTF-8') ?></label>
              <select id="default_timezone" name="default_timezone" class="form-select max-width-320">
                  <?php
                  $tzList = timezone_identifiers_list();
                  $currentDefaultTimezone = $currentDefaultTimezone ?? 'UTC';
                  foreach ($tzList as $tzId):
                  ?>
                      <option value="<?= htmlspecialchars($tzId, ENT_QUOTES, 'UTF-8') ?>"
                          <?= ($currentDefaultTimezone === $tzId) ? 'selected' : '' ?>>
                          <?= htmlspecialchars($tzId, ENT_QUOTES, 'UTF-8') ?>
                      </option>
                  <?php endforeach; ?>
              </select>
              <div class="form-text"><?= htmlspecialchars($__t('settings.timezone_helper', 'Used for guests and users who have not set a timezone in their profile. Stored timestamps stay UTC.'), ENT_QUOTES, 'UTF-8') ?></div>
          </div>

             <div class="mb-3">
              <label for="default_date_format" class="form-label fw-bold"><?= htmlspecialchars($__t('settings.default_date_format', 'Default date format'), ENT_QUOTES, 'UTF-8') ?></label>
              <select id="default_date_format" name="default_date_format" class="form-select max-width-320">
                  <?php
                  $currentDefaultDateFormat = $currentDefaultDateFormat ?? 'd/m/Y';
                  $dateOpts = [
                      'd/m/Y' => 'DD/MM/YYYY',
                      'd.m.Y' => 'DD.MM.YYYY',
                      'Y-m-d' => 'YYYY-MM-DD',
                      'm/d/Y' => 'MM/DD/YYYY',
                      'd-m-Y' => 'DD-MM-YYYY',
                  ];
                  foreach ($dateOpts as $val => $label):
                  ?>
                      <option value="<?= htmlspecialchars($val, ENT_QUOTES, 'UTF-8') ?>"
                          <?= ($currentDefaultDateFormat === $val) ? 'selected' : '' ?>>
                          <?= htmlspecialchars($label, ENT_QUOTES, 'UTF-8') ?>
                      </option>
                  <?php endforeach; ?>
              </select>
          </div>

            <div class="mb-3">
              <label for="default_time_format" class="form-label fw-bold"><?= htmlspecialchars($__t('settings.default_time_format', 'Default time format'), ENT_QUOTES, 'UTF-8') ?></label>
              <select id="default_time_format" name="default_time_format" class="form-select max-width-320">
                  <?php $currentDefaultTimeFormat = $currentDefaultTimeFormat ?? '24'; ?>
                  <option value="24" <?= ($currentDefaultTimeFormat === '24') ? 'selected' : '' ?>>24-hour (14:30)</option>
                  <option value="12" <?= ($currentDefaultTimeFormat === '12') ? 'selected' : '' ?>>12-hour (2:30 PM)</option>
              </select>
              <div class="form-text"><?= htmlspecialchars($__t('settings.default_lang_note', 'Default language is already set above. Profile settings override these for logged-in users.'), ENT_QUOTES, 'UTF-8') ?></div>
          </div>

            <div class="mb-3">
              <label for="footer_compiled_notice" class="form-label fw-bold"><?= htmlspecialchars($__t('settings.footer_compiled_notice', 'Footer source line'), ENT_QUOTES, 'UTF-8') ?></label>
              <input type="text"
                     id="footer_compiled_notice"
                     name="footer_compiled_notice"
                     value="<?= htmlspecialchars($currentFooterCompiledNotice ?? '', ENT_QUOTES, 'UTF-8') ?>"
                     class="form-control"
                     placeholder="Your Footer Goes Here.">
              <div class="form-text">
                 <?= htmlspecialchars($__t('settings.footer_notice_helper', 'Shown as the first line of the site footer. Leave blank to use the default text from the language file. Software credit and copyright stay fixed.'), ENT_QUOTES, 'UTF-8') ?>
              </div>
          </div>

            <fieldset class="mb-4">
                <legend class="form-label fw-bold"><?= htmlspecialchars($__t('settings.dup_heading', 'Duplicate Records Management'), ENT_QUOTES, 'UTF-8') ?></legend>
                <p class="form-text mt-0 mb-2"><?= htmlspecialchars($__t('settings.dup_help', 'If a new record looks like one you already have, pRD can compare it with other rows in the same table. You can warn people, stop the save, or switch this off.'), ENT_QUOTES, 'UTF-8') ?></p>
                <?php $currentDuplicateMode = $currentDuplicateMode ?? 'warn'; ?>
                <div class="form-check">
                    <input class="form-check-input" type="radio" name="duplicate_mode" id="dup_mode_off" value="off" <?= $currentDuplicateMode === 'off' ? 'checked' : '' ?>>
                    <label class="form-check-label" for="dup_mode_off"><?= htmlspecialchars($__t('settings.dup_mode_off', 'Do not check for similar records'), ENT_QUOTES, 'UTF-8') ?></label>
                </div>
                <div class="form-check">
                    <input class="form-check-input" type="radio" name="duplicate_mode" id="dup_mode_warn" value="warn" <?= $currentDuplicateMode === 'warn' ? 'checked' : '' ?>>
                    <label class="form-check-label" for="dup_mode_warn"><?= htmlspecialchars($__t('settings.dup_mode_warn', 'Show a warning — they can still save'), ENT_QUOTES, 'UTF-8') ?></label>
                </div>
                <div class="form-check">
                    <input class="form-check-input" type="radio" name="duplicate_mode" id="dup_mode_block" value="block" <?= $currentDuplicateMode === 'block' ? 'checked' : '' ?>>
                    <label class="form-check-label" for="dup_mode_block"><?= htmlspecialchars($__t('settings.dup_mode_block', 'Do not allow them to save'), ENT_QUOTES, 'UTF-8') ?></label>
                </div>
                <div class="form-check mb-3">
                    <input class="form-check-input" type="radio" name="duplicate_mode" id="dup_mode_flag" value="flag" <?= $currentDuplicateMode === 'flag' ? 'checked' : '' ?>>
                    <label class="form-check-label" for="dup_mode_flag"><?= htmlspecialchars($__t('settings.dup_mode_flag', 'Warn them, and note it for a moderator if they save anyway'), ENT_QUOTES, 'UTF-8') ?></label>
                </div>
                <?php $currentDuplicatePicky = $currentDuplicatePicky ?? 'similar'; ?>
                <label class="form-label fw-bold" for="duplicate_picky"><?= htmlspecialchars($__t('settings.dup_picky_label', 'How picky should the check be?'), ENT_QUOTES, 'UTF-8') ?></label>
                <select id="duplicate_picky" name="duplicate_picky" class="form-select max-width-320">
                    <option value="similar" <?= $currentDuplicatePicky === 'similar' ? 'selected' : '' ?>><?= htmlspecialchars($__t('settings.dup_picky_similar', 'Also catch near-misses (similar spelling)'), ENT_QUOTES, 'UTF-8') ?></option>
                    <option value="exact" <?= $currentDuplicatePicky === 'exact' ? 'selected' : '' ?>><?= htmlspecialchars($__t('settings.dup_picky_exact', 'Only when the words match closely'), ENT_QUOTES, 'UTF-8') ?></option>
                </select>
            </fieldset>

            <!-- CAPTCHA Configuration Settings -->
            <h4 class="h5 fw-bold text-dark mb-3"><?= htmlspecialchars(__('settings.captcha_heading'), ENT_QUOTES, 'UTF-8') ?></h4>
            <div class="mb-3">
                <label for="captcha_provider" class="form-label fw-bold"><?= htmlspecialchars(__('settings.captcha_provider_label'), ENT_QUOTES, 'UTF-8') ?></label>
                <select id="captcha_provider" name="captcha_provider" class="form-select" onchange="toggleCaptchaConfigs(this.value)">
                    <option value="none" <?= ($currentCaptchaProvider === 'none') ? 'selected' : '' ?>><?= htmlspecialchars(__('settings.captcha_none'), ENT_QUOTES, 'UTF-8') ?></option>
                    <option value="turnstile" <?= ($currentCaptchaProvider === 'turnstile') ? 'selected' : '' ?>><?= htmlspecialchars(__('settings.captcha_turnstile'), ENT_QUOTES, 'UTF-8') ?></option>
                    <option value="recaptcha" <?= ($currentCaptchaProvider === 'recaptcha') ? 'selected' : '' ?>><?= htmlspecialchars(__('settings.captcha_recaptcha'), ENT_QUOTES, 'UTF-8') ?></option>
                    <option value="hcaptcha" <?= ($currentCaptchaProvider === 'hcaptcha') ? 'selected' : '' ?>><?= htmlspecialchars(__('settings.captcha_hcaptcha'), ENT_QUOTES, 'UTF-8') ?></option>
                </select>
            </div>

            <!-- Turnstile Settings Block -->
            <div id="captcha_turnstile_block" class="card bg-light border-0 p-3 mb-3" style="display: <?= ($currentCaptchaProvider === 'turnstile') ? 'block' : 'none' ?>;">
                <h5 class="h6 fw-bold text-dark mb-2"><?= htmlspecialchars(__('settings.turnstile_heading'), ENT_QUOTES, 'UTF-8') ?></h5>
                <div class="mb-2">
                    <label for="turnstile_site_key" class="form-label small fw-bold"><?= htmlspecialchars(__('settings.site_key_label'), ENT_QUOTES, 'UTF-8') ?></label>
                    <input type="text" id="turnstile_site_key" name="turnstile_site_key" value="<?= htmlspecialchars($currentTurnstileSite, ENT_QUOTES, 'UTF-8') ?>" class="form-control form-control-sm">
                </div>
                <div>
                    <label for="turnstile_secret_key" class="form-label small fw-bold"><?= htmlspecialchars(__('settings.secret_key_label'), ENT_QUOTES, 'UTF-8') ?></label>
                    <input type="password" id="turnstile_secret_key" name="turnstile_secret_key" value="<?= htmlspecialchars($currentTurnstileSecret, ENT_QUOTES, 'UTF-8') ?>" placeholder="••••••••" class="form-control form-control-sm">
                </div>
            </div>

            <!-- Google reCAPTCHA Settings Block -->
            <div id="captcha_recaptcha_block" class="card bg-light border-0 p-3 mb-3" style="display: <?= ($currentCaptchaProvider === 'recaptcha') ? 'block' : 'none' ?>;">
                <h5 class="h6 fw-bold text-dark mb-2"><?= htmlspecialchars(__('settings.recaptcha_heading'), ENT_QUOTES, 'UTF-8') ?></h5>
                <div class="mb-2">
                    <label for="recaptcha_site_key" class="form-label small fw-bold"><?= htmlspecialchars(__('settings.site_key_label'), ENT_QUOTES, 'UTF-8') ?></label>
                    <input type="text" id="recaptcha_site_key" name="recaptcha_site_key" value="<?= htmlspecialchars($currentRecaptchaSite, ENT_QUOTES, 'UTF-8') ?>" class="form-control form-control-sm">
                </div>
                <div>
                    <label for="recaptcha_secret_key" class="form-label small fw-bold"><?= htmlspecialchars(__('settings.secret_key_label'), ENT_QUOTES, 'UTF-8') ?></label>
                    <input type="password" id="recaptcha_secret_key" name="recaptcha_secret_key" value="<?= htmlspecialchars($currentRecaptchaSecret, ENT_QUOTES, 'UTF-8') ?>" placeholder="••••••••" class="form-control form-control-sm">
                </div>
            </div>

            <!-- hCaptcha Settings Block -->
            <div id="captcha_hcaptcha_block" class="card bg-light border-0 p-3 mb-4" style="display: <?= ($currentCaptchaProvider === 'hcaptcha') ? 'block' : 'none' ?>;">
                <h5 class="h6 fw-bold text-dark mb-2"><?= htmlspecialchars(__('settings.hcaptcha_heading'), ENT_QUOTES, 'UTF-8') ?></h5>
                <div class="mb-2">
                    <label for="hcaptcha_site_key" class="form-label small fw-bold"><?= htmlspecialchars(__('settings.site_key_label'), ENT_QUOTES, 'UTF-8') ?></label>
                    <input type="text" id="hcaptcha_site_key" name="hcaptcha_site_key" value="<?= htmlspecialchars($currentHcaptchaSite, ENT_QUOTES, 'UTF-8') ?>" class="form-control form-control-sm">
                </div>
                <div>
                    <label for="hcaptcha_secret_key" class="form-label small fw-bold"><?= htmlspecialchars(__('settings.secret_key_label'), ENT_QUOTES, 'UTF-8') ?></label>
                    <input type="password" id="hcaptcha_secret_key" name="hcaptcha_secret_key" value="<?= htmlspecialchars($currentHcaptchaSecret, ENT_QUOTES, 'UTF-8') ?>" placeholder="••••••••" class="form-control form-control-sm">
                </div>
            </div>

            <h4 class="h5 fw-bold text-dark mb-3"><?= htmlspecialchars(__('settings.mail_heading'), ENT_QUOTES, 'UTF-8') ?></h4>
            <div class="mb-3">
                <label for="mail_domain" class="form-label fw-bold"><?= htmlspecialchars(__('settings.mail_domain_label'), ENT_QUOTES, 'UTF-8') ?></label>
                <input type="text" id="mail_domain" name="mail_domain" value="<?= htmlspecialchars($currentMailDomain, ENT_QUOTES, 'UTF-8') ?>" placeholder="e.g. example.com" class="form-control">
            </div>

            <div class="mb-3">
                <label for="mail_from" class="form-label fw-bold"><?= htmlspecialchars(__('settings.mail_from_label'), ENT_QUOTES, 'UTF-8') ?></label>
                <input type="email" id="mail_from" name="mail_from" value="<?= htmlspecialchars($currentMailFrom, ENT_QUOTES, 'UTF-8') ?>" placeholder="e.g. notifications@example.com" class="form-control">
                <div class="form-text"><?= htmlspecialchars(__('settings.mail_from_desc'), ENT_QUOTES, 'UTF-8') ?></div>
                <label for="map_tile_url" class="form-label fw-bold mt-3"><?= htmlspecialchars(__('settings.map_tile_url') !== 'settings.map_tile_url' ? __('settings.map_tile_url') : 'Map picture address (optional)', ENT_QUOTES, 'UTF-8') ?></label>
                <input type="text" id="map_tile_url" name="map_tile_url" value="<?= htmlspecialchars($currentMapTileUrl ?? '', ENT_QUOTES, 'UTF-8') ?>" class="form-control" placeholder="https://{s}.tile.openstreetmap.org/{z}/{x}/{y}.png" autocomplete="off">
                <div class="form-text"><?= htmlspecialchars(__('settings.map_tile_url_help') !== 'settings.map_tile_url_help' ? __('settings.map_tile_url_help') : 'Leave blank for the free OpenStreetMap pictures. Paid map services can go here; pRD does not ship a key.', ENT_QUOTES, 'UTF-8') ?></div>
            </div>

            <div class="mb-3">
                <label for="mail_driver" class="form-label fw-bold"><?= htmlspecialchars(__('settings.mail_driver_label'), ENT_QUOTES, 'UTF-8') ?></label>
                <select id="mail_driver" name="mail_driver" class="form-select" onchange="toggleSmtpFields(this.value)">
                    <option value="mail" <?= ($currentMailDriver === 'mail') ? 'selected' : '' ?>><?= htmlspecialchars(__('settings.driver_native'), ENT_QUOTES, 'UTF-8') ?></option>
                    <option value="smtp" <?= ($currentMailDriver === 'smtp') ? 'selected' : '' ?>><?= htmlspecialchars(__('settings.driver_smtp'), ENT_QUOTES, 'UTF-8') ?></option>
                </select>
            </div>

            <div id="smtp_settings_block" class="card bg-light border-0 p-3 mb-4" style="display: <?= ($currentMailDriver === 'smtp') ? 'block' : 'none' ?>;">
                <h5 class="h6 fw-bold text-dark mb-3"><?= htmlspecialchars(__('settings.smtp_heading'), ENT_QUOTES, 'UTF-8') ?></h5>
                <div class="mb-3">
                    <label for="smtp_host" class="form-label small fw-bold"><?= htmlspecialchars(__('settings.smtp_host_label'), ENT_QUOTES, 'UTF-8') ?></label>
                    <input type="text" id="smtp_host" name="smtp_host" value="<?= htmlspecialchars($currentSmtpHost, ENT_QUOTES, 'UTF-8') ?>" placeholder="e.g. smtp.example.com" class="form-control form-control-sm">
                </div>
                <div class="row g-2 mb-3">
                    <div class="col-md-6">
                        <label for="smtp_port" class="form-label small fw-bold"><?= htmlspecialchars(__('settings.smtp_port_label'), ENT_QUOTES, 'UTF-8') ?></label>
                        <input type="number" id="smtp_port" name="smtp_port" value="<?= htmlspecialchars($currentSmtpPort, ENT_QUOTES, 'UTF-8') ?>" class="form-control form-control-sm">
                    </div>
                    <div class="col-md-6">
                        <label for="smtp_encryption" class="form-label small fw-bold"><?= htmlspecialchars(__('settings.smtp_encryption_label'), ENT_QUOTES, 'UTF-8') ?></label>
                        <select id="smtp_encryption" name="smtp_encryption" class="form-select form-select-sm" onchange="updateSmtpPort(this.value)">
                            <option value="tls" <?= ($currentSmtpEncryption === 'tls') ? 'selected' : '' ?>><?= htmlspecialchars(__('settings.enc_tls'), ENT_QUOTES, 'UTF-8') ?></option>
                            <option value="ssl" <?= ($currentSmtpEncryption === 'ssl') ? 'selected' : '' ?>><?= htmlspecialchars(__('settings.enc_ssl'), ENT_QUOTES, 'UTF-8') ?></option>
                        </select>
                    </div>
                </div>
                <div class="mb-3">
                    <label for="smtp_user" class="form-label small fw-bold"><?= htmlspecialchars(__('settings.smtp_user_label'), ENT_QUOTES, 'UTF-8') ?></label>
                    <input type="text" id="smtp_user" name="smtp_user" value="<?= htmlspecialchars($currentSmtpUser, ENT_QUOTES, 'UTF-8') ?>" class="form-control form-control-sm">
                </div>
                <div>
                    <label for="smtp_pass" class="form-label small fw-bold"><?= htmlspecialchars(__('settings.smtp_pass_label'), ENT_QUOTES, 'UTF-8') ?></label>
                    <input type="password" id="smtp_pass" name="smtp_pass" placeholder="••••••••" class="form-control form-control-sm">
                </div>
            </div>

            <button type="submit" class="btn btn-primary"><?= htmlspecialchars(__('settings.save_core_mail_btn'), ENT_QUOTES, 'UTF-8') ?></button>
        </form>
    </div>

    <!-- Test Mail Section -->
    <div id="test-mail-section" class="card shadow-sm border-0 p-4">
        <h4 class="h5 fw-bold text-dark mb-3"><?= htmlspecialchars(__('settings.test_mail_heading'), ENT_QUOTES, 'UTF-8') ?></h4>
        <form method="POST" action="<?= $basePath ?>/admin/mail/test#test-mail-section" onsubmit="handleTestMailSubmit(this);">
            <?= csrf_field() ?>
            <label for="test_email" class="form-label fw-bold"><?= htmlspecialchars(__('settings.test_email_label'), ENT_QUOTES, 'UTF-8') ?></label>
            <div class="input-group">
                <input type="email" id="test_email" name="test_email" placeholder="admin@example.com" required class="form-control">
                <button type="submit" id="test-mail-btn" class="btn btn-outline-secondary d-inline-flex align-items-center gap-2">
                    <span><?= htmlspecialchars(__('settings.send_test_btn'), ENT_QUOTES, 'UTF-8') ?></span>
                </button>
            </div>
        </form>
    </div>
</div>
