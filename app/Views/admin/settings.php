<?php
/**
 * MIGRATED FILE MAPPING
 * ---------------------
 * Original Old File: admin/settings.php/admin/actions/save_settings.php
 * Migrated Date: 2026-08-05 03:44:52
 */declare(strict_types=1);

/**
 * MIGRATED FILE MAPPING
 * ---------------------
 * Original Old File: admin/settings.php
 * Migrated Date: 2026-08-04 10:25:40
 */

/** @string $message */
/** @string $error */
/** @int $schemaCurrent */
/** @int $schemaLatest */
/** @bool $schemaNeedsUpdate */
/** @string $currentSystemName */
/** @string $currentDefaultLanguage */
/** @array<int, string> $availableLanguages */
/** @string $currentCaptchaProvider */
/** @string $currentTurnstileSite */
/** @string $currentTurnstileSecret */
/** @string $currentRecaptchaSite */
/** @string $currentRecaptchaSecret */
/** @string $currentHcaptchaSite */
/** @string $currentHcaptchaSecret */
/** @string $currentMailDomain */
/** @string $currentMailFrom */
/** @string $currentMailDriver */
/** @string $currentSmtpHost */
/** @string $currentSmtpPort */
/** @string $currentSmtpUser */
/** @string $currentSmtpEncryption */
/** @string $modModerationVal */
/** @string $modVolunteersVal */
/** @string $modFeedbackVal */
/** @string $modUsersVal */
/** @string $modLeaderboardVal */
/** @string $maintenanceMode */
/** @string $maintenanceReason */
/** @string $maintenanceEta */
/** @array<int, array<string, mixed>> $notices */
/** @array<int, array<string, mixed>> $auditLogs */
/** @array<int, string> $distinctActions */
/** @string $userTimezone */
/** @string $fullFormatStr */

require_once __DIR__ . '/../partials/header.php';
?>

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

<div class="container py-4" role="region" aria-label="Site Settings Form" style="max-width: 1100px;">
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

    <h3 class="fw-bold mb-1"><?= htmlspecialchars(__('settings.heading'), ENT_QUOTES, 'UTF-8') ?></h3>
    <p class="text-muted mb-4"><?= htmlspecialchars(__('settings.subheading'), ENT_QUOTES, 'UTF-8') ?></p>

    <!-- Accessible Bootstrap Nav Tabs -->
    <ul class="nav nav-tabs mb-4" role="tablist" aria-label="Settings Sections">
        <li class="nav-item" role="presentation">
            <button class="nav-link active fw-bold" id="tab-core" data-bs-toggle="tab" data-bs-target="#panel-core" type="button" role="tab" aria-controls="panel-core" aria-selected="true"><?= htmlspecialchars(__('settings.tab_core'), ENT_QUOTES, 'UTF-8') ?></button>
        </li>
        <li class="nav-item" role="presentation">
            <button class="nav-link fw-bold text-secondary" id="tab-modules" data-bs-toggle="tab" data-bs-target="#panel-modules" type="button" role="tab" aria-controls="panel-modules" aria-selected="false"><?= htmlspecialchars(__('settings.tab_modules'), ENT_QUOTES, 'UTF-8') ?></button>
        </li>
        <li class="nav-item" role="presentation">
            <button class="nav-link fw-bold text-secondary" id="tab-maintenance" data-bs-toggle="tab" data-bs-target="#panel-maintenance" type="button" role="tab" aria-controls="panel-maintenance" aria-selected="false"><?= htmlspecialchars(__('settings.tab_maintenance'), ENT_QUOTES, 'UTF-8') ?></button>
        </li>
        <li class="nav-item" role="presentation">
            <button class="nav-link fw-bold text-secondary" id="tab-notices" data-bs-toggle="tab" data-bs-target="#panel-notices" type="button" role="tab" aria-controls="panel-notices" aria-selected="false"><?= htmlspecialchars(__('settings.tab_notices'), ENT_QUOTES, 'UTF-8') ?></button>
        </li>
        <li class="nav-item" role="presentation">
            <button class="nav-link fw-bold text-secondary" id="tab-permissions" data-bs-toggle="tab" data-bs-target="#panel-permissions" type="button" role="tab" aria-controls="panel-permissions" aria-selected="false"><?= htmlspecialchars(__('settings.tab_permissions'), ENT_QUOTES, 'UTF-8') ?></button>
        </li>
        <li class="nav-item" role="presentation">
            <button class="nav-link fw-bold text-secondary" id="tab-audit" data-bs-toggle="tab" data-bs-target="#panel-audit" type="button" role="tab" aria-controls="panel-audit" aria-selected="false"><?= htmlspecialchars(__('settings.tab_audit'), ENT_QUOTES, 'UTF-8') ?></button>
        </li>
    </ul>

    <div class="tab-content">
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

                <form method="POST" action="/admin/actions/download_database_backup.php" class="mb-2">
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
                    <form method="POST" action="/admin/actions/run_migrations.php" class="mt-2" onsubmit="return confirm('<?= htmlspecialchars(__('settings.migration_confirm'), ENT_QUOTES, 'UTF-8') ?>');">
                        <?= csrf_field() ?>
                        <button type="submit" class="btn btn-sm btn-primary"><?= htmlspecialchars(__('settings.update_db_btn'), ENT_QUOTES, 'UTF-8') ?></button>
                    </form>
                <?php else: ?>
                    <p class="text-success small fw-bold mb-0"><?= htmlspecialchars(__('settings.schema_uptodate'), ENT_QUOTES, 'UTF-8') ?></p>
                <?php endif; ?>
            </div>

            <!-- Core Settings & Mail Form Card -->
            <div class="card shadow-sm border-0 p-4 mb-4">
                <form method="POST" action="/admin/settings/store">
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
                <form method="POST" action="/admin/actions/test_mail.php#test-mail-section" onsubmit="handleTestMailSubmit(this);">
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

        <!-- TAB 2: Modules Management -->
        <div class="tab-pane fade" id="panel-modules" role="tabpanel" aria-labelledby="tab-modules">
            <div class="card shadow-sm border-0 p-4">
                <form method="POST" action="/admin/actions/save_modules.php">
                    <?= csrf_field() ?>
                    <h4 class="h5 fw-bold text-dark mb-1"><?= htmlspecialchars(__('settings.modules_heading'), ENT_QUOTES, 'UTF-8') ?></h4>
                    <p class="text-muted small mb-4"><?= htmlspecialchars(__('settings.modules_subheading'), ENT_QUOTES, 'UTF-8') ?></p>
                    
                    <div class="d-flex flex-column gap-3 mb-4">
                        <div class="card bg-light border-0 p-3">
                            <div class="form-check">
                                <input type="checkbox" name="module_users_enabled" id="module_users_enabled" value="1" <?= ($modUsersVal === '1') ? 'checked' : '' ?> onchange="handleUserManagementToggle(this);" class="form-check-input fs-5">
                                <label for="module_users_enabled" class="form-check-label fw-bold text-dark"><?= htmlspecialchars(__('settings.mod_users'), ENT_QUOTES, 'UTF-8') ?></label>
                            </div>
                            <p class="text-muted small mb-0 mt-1 ms-4"><?= htmlspecialchars(__('settings.mod_users_desc'), ENT_QUOTES, 'UTF-8') ?></p>
                        </div>

                        <div class="card bg-light border-0 p-3">
                            <div class="form-check">
                                <input type="checkbox" name="module_leaderboard_enabled" id="module_leaderboard_enabled" value="1" <?= ($modLeaderboardVal === '1' && $modUsersVal === '1') ? 'checked' : '' ?> <?= ($modUsersVal !== '1') ? 'disabled' : '' ?> class="form-check-input fs-5">
                                <label for="module_leaderboard_enabled" class="form-check-label fw-bold text-dark"><?= htmlspecialchars(__('settings.mod_leaderboard'), ENT_QUOTES, 'UTF-8') ?></label>
                            </div>
                            <p class="text-muted small mb-0 mt-1 ms-4">
                                <?= htmlspecialchars(__('settings.mod_leaderboard_desc'), ENT_QUOTES, 'UTF-8') ?> 
                                <span id="leaderboard_dependency_note" class="text-danger fw-bold" style="display: <?= ($modUsersVal !== '1') ? 'inline' : 'none' ?>;"><?= htmlspecialchars(__('settings.mod_leaderboard_note'), ENT_QUOTES, 'UTF-8') ?></span>
                            </p>
                        </div>

                        <div class="card bg-light border-0 p-3">
                            <div class="form-check">
                                <input type="checkbox" name="module_moderation_enabled" id="module_moderation_enabled" value="1" <?= ($modModerationVal === '1') ? 'checked' : '' ?> class="form-check-input fs-5">
                                <label for="module_moderation_enabled" class="form-check-label fw-bold text-dark"><?= htmlspecialchars(__('settings.mod_moderation'), ENT_QUOTES, 'UTF-8') ?></label>
                            </div>
                            <p class="text-muted small mb-0 mt-1 ms-4"><?= htmlspecialchars(__('settings.mod_moderation_desc'), ENT_QUOTES, 'UTF-8') ?></p>
                        </div>

                        <div class="card bg-light border-0 p-3">
                            <div class="form-check">
                                <input type="checkbox" name="module_volunteers_enabled" id="module_volunteers_enabled" value="1" <?= ($modVolunteersVal === '1') ? 'checked' : '' ?> class="form-check-input fs-5">
                                <label for="module_volunteers_enabled" class="form-check-label fw-bold text-dark"><?= htmlspecialchars(__('settings.mod_volunteers'), ENT_QUOTES, 'UTF-8') ?></label>
                            </div>
                            <p class="text-muted small mb-0 mt-1 ms-4"><?= htmlspecialchars(__('settings.mod_volunteers_desc'), ENT_QUOTES, 'UTF-8') ?></p>
                        </div>

                        <div class="card bg-light border-0 p-3">
                            <div class="form-check">
                                <input type="checkbox" name="module_feedback_enabled" id="module_feedback_enabled" value="1" <?= ($modFeedbackVal === '1') ? 'checked' : '' ?> class="form-check-input fs-5">
                                <label for="module_feedback_enabled" class="form-check-label fw-bold text-dark"><?= htmlspecialchars(__('settings.mod_feedback'), ENT_QUOTES, 'UTF-8') ?></label>
                            </div>
                            <p class="text-muted small mb-0 mt-1 ms-4"><?= htmlspecialchars(__('settings.mod_feedback_desc'), ENT_QUOTES, 'UTF-8') ?></p>
                        </div>
                    </div>

                    <button type="submit" class="btn btn-primary"><?= htmlspecialchars(__('settings.save_modules_btn'), ENT_QUOTES, 'UTF-8') ?></button>
                </form>
            </div>
        </div>

        <!-- TAB 3: Maintenance Mode & Cron Settings -->
        <div class="tab-pane fade" id="panel-maintenance" role="tabpanel" aria-labelledby="tab-maintenance">
            <div class="card shadow-sm border-0 p-4 mb-4">
                <form method="POST" action="/admin/actions/save_maintenance.php">
                    <?= csrf_field() ?>
                    <h4 class="h5 fw-bold text-dark mb-3"><?= htmlspecialchars(__('settings.maintenance_heading'), ENT_QUOTES, 'UTF-8') ?></h4>
                    
                    <div class="mb-3 form-check">
                        <input type="checkbox" name="maintenance_mode" id="maintenance_mode" value="1" <?= ($maintenanceMode === '1') ? 'checked' : '' ?> class="form-check-input fs-5">
                        <label for="maintenance_mode" class="form-check-label fw-bold text-dark"><?= htmlspecialchars(__('settings.maintenance_toggle'), ENT_QUOTES, 'UTF-8') ?></label>
                    </div>

                    <div class="mb-3">
                        <label for="maintenance_reason" class="form-label fw-bold"><?= htmlspecialchars(__('settings.maintenance_reason_label'), ENT_QUOTES, 'UTF-8') ?></label>
                        <textarea id="maintenance_reason" name="maintenance_reason" rows="3" required class="form-control"><?= htmlspecialchars($maintenanceReason, ENT_QUOTES, 'UTF-8') ?></textarea>
                    </div>

                    <div class="mb-4">
                        <label for="maintenance_eta" class="form-label fw-bold"><?= htmlspecialchars(__('settings.maintenance_eta_label'), ENT_QUOTES, 'UTF-8') ?></label>
                        <input type="text" id="maintenance_eta" name="maintenance_eta" value="<?= htmlspecialchars($maintenanceEta, ENT_QUOTES, 'UTF-8') ?>" required class="form-control max-width-350">
                    </div>

                    <button type="submit" class="btn btn-danger"><?= htmlspecialchars(__('settings.save_maintenance_btn'), ENT_QUOTES, 'UTF-8') ?></button>
                </form>
            </div>

            <!-- Intelligent Cron Discovery & Token Maintenance Tool -->
            <div class="card shadow-sm border-0 bg-light p-4">
                <h4 class="h5 fw-bold text-dark mb-2"><?= htmlspecialchars(__('settings.cron_maintenance_heading'), ENT_QUOTES, 'UTF-8') ?></h4>
                <p class="text-muted small mb-3"><?= htmlspecialchars(__('settings.cron_maintenance_desc'), ENT_QUOTES, 'UTF-8') ?></p>
                
                <div class="mb-3">
                    <label class="form-label small fw-bold"><?= htmlspecialchars(__('settings.cron_command_label'), ENT_QUOTES, 'UTF-8') ?></label>
                    <input type="text" readonly value="<?= htmlspecialchars(PHP_BINARY, ENT_QUOTES, 'UTF-8') ?> <?= htmlspecialchars(realpath(__DIR__ . '/../../db/actions/cron_token_cleanup.php') ?: '', ENT_QUOTES, 'UTF-8') ?>" class="form-control font-monospace bg-white" onclick="this.select();">
                </div>

                <form method="POST" action="/admin/actions/cron_token_cleanup.php">
                    <?= csrf_field() ?>
                    <button type="submit" class="btn btn-sm btn-outline-secondary"><?= htmlspecialchars(__('settings.run_token_cleanup_btn'), ENT_QUOTES, 'UTF-8') ?></button>
                </form>
            </div>
        </div>

        <!-- TAB 4: Site Notices -->
        <div class="tab-pane fade" id="panel-notices" role="tabpanel" aria-labelledby="tab-notices">
            <div class="d-flex justify-content-between align-items-center mb-3">
                <h4 class="h5 fw-bold text-dark mb-0"><?= htmlspecialchars(__('settings.notices_heading'), ENT_QUOTES, 'UTF-8') ?></h4>
                <a href="/admin/notices" class="btn btn-sm btn-outline-secondary"><?= htmlspecialchars(__('settings.add_notice_btn'), ENT_QUOTES, 'UTF-8') ?></a>
            </div>

            <?php if (empty($notices)): ?>
                <div class="card shadow-sm border-0 text-center py-5 text-muted bg-light">
                    <?= htmlspecialchars(__('settings.no_notices'), ENT_QUOTES, 'UTF-8') ?>
                </div>
            <?php else: ?>
                <div class="d-flex flex-column gap-3">
                    <?php foreach ($notices as $n): ?>
                        <?php 
                            $noticeId = isset($n['id']) ? (int)$n['id'] : 0;
                            $title = isset($n['title']) && is_string($n['title']) ? $n['title'] : '';
                            $content = isset($n['content']) && is_string($n['content']) ? $n['content'] : '';
                            $isActive = !empty($n['is_active']);
                        ?>
                        <div class="card shadow-sm border-0">
                            <div class="card-body">
                                <details>
                                    <summary class="fw-bold fs-6 text-dark d-flex justify-content-between align-items-center" style="cursor: pointer;">
                                        <span><?= htmlspecialchars($title, ENT_QUOTES, 'UTF-8') ?></span>
                                        <span class="badge <?= $isActive ? 'bg-success' : 'bg-danger' ?>"><?= $isActive ? htmlspecialchars(__('settings.status_active'), ENT_QUOTES, 'UTF-8') : htmlspecialchars(__('settings.status_inactive'), ENT_QUOTES, 'UTF-8') ?></span>
                                    </summary>
                                    <div class="mt-3 pt-3 border-top">
                                        <form method="POST" action="/admin/actions/save_notice_inline.php">
                                            <?= csrf_field() ?>
                                            <input type="hidden" name="notice_id" value="<?= $noticeId ?>">
                                            
                                            <div class="mb-2">
                                                <label class="form-label small fw-bold"><?= htmlspecialchars(__('notices.title_label'), ENT_QUOTES, 'UTF-8') ?></label>
                                                <input type="text" name="title" value="<?= htmlspecialchars($title, ENT_QUOTES, 'UTF-8') ?>" class="form-control form-control-sm" required>
                                            </div>

                                            <div class="mb-3">
                                                <label class="form-label small fw-bold"><?= htmlspecialchars(__('settings.notice_content_label'), ENT_QUOTES, 'UTF-8') ?></label>
                                                <textarea name="content" rows="3" class="form-control form-control-sm" required><?= htmlspecialchars($content, ENT_QUOTES, 'UTF-8') ?></textarea>
                                            </div>

                                            <button type="submit" name="update_action" value="save" class="btn btn-sm btn-primary"><?= htmlspecialchars(__('settings.save_notice_btn'), ENT_QUOTES, 'UTF-8') ?></button>
                                        </form>
                                    </div>
                                </details>
                            </div>
                        </div>
                    <?php endforeach; ?>
                </div>
            <?php endif; ?>
        </div>

        <!-- TAB 5: Roles & Permissions Matrix -->
        <div class="tab-pane fade" id="panel-permissions" role="tabpanel" aria-labelledby="tab-permissions">
            <h4 class="h5 fw-bold text-dark mb-1"><?= htmlspecialchars(__('settings.permissions_heading'), ENT_QUOTES, 'UTF-8') ?></h4>
            <p class="text-muted small mb-4"><?= htmlspecialchars(__('settings.permissions_subheading'), ENT_QUOTES, 'UTF-8') ?></p>

            <!-- Role Creation Form Card -->
            <div class="card shadow-sm border-0 bg-light p-4 mb-4">
                <h5 class="h6 fw-bold text-dark mb-3"><?= htmlspecialchars(__('settings.create_role_heading'), ENT_QUOTES, 'UTF-8') ?></h5>
                <form method="POST" action="/admin/actions/save_role.php" class="row g-3 align-items-end">
                    <?= csrf_field() ?>
                    <div class="col-md-8">
                        <label for="role_name" class="form-label small fw-bold"><?= htmlspecialchars(__('settings.role_name_label'), ENT_QUOTES, 'UTF-8') ?></label>
                        <input type="text" id="role_name" name="role_name" placeholder="e.g. archivist" required class="form-control">
                    </div>
                    <div class="col-md-4">
                        <button type="submit" class="btn btn-primary w-100"><?= htmlspecialchars(__('settings.create_role_btn'), ENT_QUOTES, 'UTF-8') ?></button>
                    </div>
                </form>
            </div>

            <?php
            $rolesListStmt = $pdo->query("SELECT * FROM roles ORDER BY id ASC");
            /** @var array<int, array<string, mixed>> $rolesList */
            $rolesList = $rolesListStmt !== false ? $rolesListStmt->fetchAll(PDO::FETCH_ASSOC) : [];

            $permsListStmt = $pdo->query("SELECT * FROM permissions ORDER BY id ASC");
            /** @var array<int, array<string, mixed>> $permsList */
            $permsList = $permsListStmt !== false ? $permsListStmt->fetchAll(PDO::FETCH_ASSOC) : [];

            /** @array<int, array<int, true>> $activeMappings */
            $activeMappings = [];
            $mapRowsStmt = $pdo->query("SELECT role_id, permission_id FROM role_permissions");
            /** @var array<int, array<string, mixed>> $mapRows */
            $mapRows = $mapRowsStmt !== false ? $mapRowsStmt->fetchAll(PDO::FETCH_ASSOC) : [];
            foreach ($mapRows as $m) {
                $rId = isset($m['role_id']) ? (int)$m['role_id'] : 0;
                $pId = isset($m['permission_id']) ? (int)$m['permission_id'] : 0;
                $activeMappings[$rId][$pId] = true;
            }

            $modUsersActive = is_module_enabled($pdo, 'users');
            $modVolunteersActive = is_module_enabled($pdo, 'volunteers');
            $modFeedbackActive = is_module_enabled($pdo, 'feedback');
            $modModerationActive = is_module_enabled($pdo, 'moderation');
            $modLeaderboardActive = is_module_enabled($pdo, 'leaderboard');

            $getPermissionCategory = static function(string $pkey): string {
                if (str_starts_with($pkey, 'view_table_') || str_starts_with($pkey, 'moderate_table_')) {
                    return 'Dynamic Tables & Records';
                }
                if (in_array($pkey, ['manage_users', 'invite_users', 'access_onboarding', 'view_leaderboard'], true)) {
                    return 'Users & Gamification Module';
                }
                if (in_array($pkey, ['manage_volunteers', 'submit_volunteer', 'manage_feedback', 'submit_feedback'], true)) {
                    return 'Portals & Submissions Module';
                }
                if (in_array($pkey, ['access_suggest_edit', 'moderate_suggestions', 'manage_feedback'], true)) {
                    return 'Moderation Workflow';
                }
                return 'Core System & Settings';
            };

            /** @array<string, array<int, array<string, mixed>>> $categorizedPerms */
            $categorizedPerms = [];
            foreach ($permsList as $p) {
                $pkey = isset($p['permission_key']) && is_string($p['permission_key']) ? $p['permission_key'] : '';
                if (($pkey === 'manage_users' || $pkey === 'invite_users' || $pkey === 'access_onboarding') && !$modUsersActive) continue;
                if (($pkey === 'manage_volunteers' || $pkey === 'submit_volunteer') && !$modVolunteersActive) continue;
                if (($pkey === 'manage_feedback' || $pkey === 'submit_feedback') && !$modFeedbackActive) continue;
                if (($pkey === 'access_suggest_edit' || $pkey === 'moderate_suggestions') && !$modModerationActive) continue;
                if (($pkey === 'view_leaderboard') && !$modLeaderboardActive) continue;

                $cat = $getPermissionCategory($pkey);
                $categorizedPerms[$cat][] = $p;
            }
            ?>

            <form method="POST" action="/admin/actions/save_permissions.php">
                <?= csrf_field() ?>
                <div class="d-flex flex-column gap-3">
                    <?php foreach ($categorizedPerms as $categoryName => $catPerms): ?>
                        <div class="card shadow-sm border-0">
                            <div class="card-body">
                                <details>
                                    <summary class="fw-bold fs-6 text-primary" style="cursor: pointer;">
                                        <?= htmlspecialchars($categoryName, ENT_QUOTES, 'UTF-8') ?> <span class="fw-normal text-muted small">(<?= count($catPerms) ?> permissions)</span>
                                    </summary>
                                    <div class="mt-3 pt-3 border-top table-responsive">
                                        <table class="table table-hover align-middle mb-0" role="table">
                                            <thead class="table-light">
                                                <tr>
                                                    <th scope="col" style="width: 25%;" class="py-2"><?= htmlspecialchars(__('settings.th_role'), ENT_QUOTES, 'UTF-8') ?></th>
                                                    <th scope="col" style="width: 75%;" class="py-2"><?= htmlspecialchars(__('settings.th_capabilities'), ENT_QUOTES, 'UTF-8') ?></th>
                                                </tr>
                                            </thead>
                                            <tbody>
                                                <?php foreach ($rolesList as $r): ?>
                                                    <?php 
                                                        $rId = isset($r['id']) ? (int)$r['id'] : 0;
                                                        $rName = isset($r['role_name']) && is_string($r['role_name']) ? $r['role_name'] : '';
                                                    ?>
                                                    <tr>
                                                        <td class="fw-bold text-capitalize align-top">
                                                            <div class="d-flex flex-column gap-1">
                                                                <span><?= htmlspecialchars($rName, ENT_QUOTES, 'UTF-8') ?></span>
                                                                <?php if ($rId > 4): ?>
                                                                    <form method="POST" action="/admin/actions/save_role.php" onsubmit="return confirm('<?= htmlspecialchars(__('settings.delete_role_confirm'), ENT_QUOTES, 'UTF-8') ?>');" class="d-inline">
                                                                        <?= csrf_field() ?>
                                                                        <input type="hidden" name="delete_role_id" value="<?= $rId ?>">
                                                                        <button type="submit" class="btn btn-sm btn-danger py-0 px-2" style="font-size: 0.75rem;">Delete</button>
                                                                    </form>
                                                                <?php endif; ?>
                                                            </div>
                                                        </td>
                                                        <td>
                                                            <div class="d-flex flex-wrap gap-2">
                                                                <?php foreach ($catPerms as $p): ?>
                                                                    <?php 
                                                                        $pId = isset($p['id']) ? (int)$p['id'] : 0;
                                                                        $pkey = isset($p['permission_key']) && is_string($p['permission_key']) ? $p['permission_key'] : '';
                                                                        $pDesc = isset($p['description']) && is_string($p['description']) ? $p['description'] : '';
                                                                        
                                                                        $isChecked = isset($activeMappings[$rId][$pId]);
                                                                        $isLockedAdmin = ($rId === 1 && $isChecked);
                                                                    ?>
                                                                    <div class="form-check bg-light border rounded px-2 py-1 m-0" title="<?= htmlspecialchars($pDesc, ENT_QUOTES, 'UTF-8') ?>">
                                                                        <input type="checkbox" name="permissions[<?= $rId ?>][<?= $pId ?>]" value="1" <?= $isChecked ? 'checked' : '' ?> <?= $isLockedAdmin ? 'disabled' : '' ?> class="form-check-input">
                                                                        <?php if ($isLockedAdmin): ?>
                                                                            <input type="hidden" name="permissions[<?= $rId ?>][<?= $pId ?>]" value="1">
                                                                        <?php endif; ?>
                                                                        <label class="form-check-label small font-monospace"><?= htmlspecialchars($pkey, ENT_QUOTES, 'UTF-8') ?></label>
                                                                    </div>
                                                                <?php endforeach; ?>
                                                            </div>
                                                        </td>
                                                    </tr>
                                                <?php endforeach; ?>
                                            </tbody>
                                        </table>
                                    </div>
                                </details>
                            </div>
                        </div>
                    <?php endforeach; ?>
                </div>

                <div class="mt-4">
                    <button type="submit" class="btn btn-primary"><?= htmlspecialchars(__('settings.save_permissions_btn'), ENT_QUOTES, 'UTF-8') ?></button>
                </div>
            </form>
        </div>

        <!-- TAB 6: Audit Log -->
        <div class="tab-pane fade" id="panel-audit" role="tabpanel" aria-labelledby="tab-audit">
            <h4 class="h5 fw-bold text-dark mb-1"><?= htmlspecialchars(__('settings.audit_heading'), ENT_QUOTES, 'UTF-8') ?></h4>
            <p class="text-muted small mb-3"><?= htmlspecialchars(__('settings.audit_subheading'), ENT_QUOTES, 'UTF-8') ?></p>

            <!-- Audit Maintenance Actions -->
            <div class="card shadow-sm border-0 bg-light p-3 mb-4 d-flex flex-row flex-wrap gap-2 align-items-center">
                <form method="POST" action="/admin/actions/purge_audit_logs.php" onsubmit="return confirm('<?= htmlspecialchars(__('settings.purge_all_confirm'), ENT_QUOTES, 'UTF-8') ?>');">
                    <?= csrf_field() ?>
                    <input type="hidden" name="purge_type" value="all">
                    <button type="submit" class="btn btn-sm btn-danger"><?= htmlspecialchars(__('settings.clear_all_audit_btn'), ENT_QUOTES, 'UTF-8') ?></button>
                </form>

                <form method="POST" action="/admin/actions/purge_audit_logs.php" onsubmit="return confirm('<?= htmlspecialchars(__('settings.purge_records_confirm'), ENT_QUOTES, 'UTF-8') ?>');">
                    <?= csrf_field() ?>
                    <input type="hidden" name="purge_type" value="records_only">
                    <button type="submit" class="btn btn-sm btn-outline-secondary"><?= htmlspecialchars(__('settings.clear_records_audit_btn'), ENT_QUOTES, 'UTF-8') ?></button>
                </form>

                <?php foreach ($distinctActions as $act): ?>
                    <form method="POST" action="/admin/actions/purge_audit_logs.php" onsubmit="return confirm('Clear all audit logs matching action type: <?= htmlspecialchars($act, ENT_QUOTES, 'UTF-8') ?>?');">
                        <?= csrf_field() ?>
                        <input type="hidden" name="purge_type" value="<?= htmlspecialchars($act, ENT_QUOTES, 'UTF-8') ?>">
                        <button type="submit" class="btn btn-sm btn-outline-secondary">Clear '<?= htmlspecialchars($act, ENT_QUOTES, 'UTF-8') ?>' Logs</button>
                    </form>
                <?php endforeach; ?>
            </div>

            <!-- Full Audit Log Table View -->
            <div class="card shadow-sm border-0">
                <div class="table-responsive" style="max-height: 600px;">
                    <table class="table table-hover align-middle mb-0 small" role="table">
                        <thead class="table-light sticky-top">
                            <tr>
                                <th scope="col" class="py-3 ps-3"><?= htmlspecialchars(__('settings.th_id'), ENT_QUOTES, 'UTF-8') ?></th>
                                <th scope="col" class="py-3"><?= htmlspecialchars(__('settings.th_timestamp'), ENT_QUOTES, 'UTF-8') ?></th>
                                <th scope="col" class="py-3"><?= htmlspecialchars(__('settings.th_actor'), ENT_QUOTES, 'UTF-8') ?></th>
                                <th scope="col" class="py-3"><?= htmlspecialchars(__('settings.th_action'), ENT_QUOTES, 'UTF-8') ?></th>
                                <th scope="col" class="py-3"><?= htmlspecialchars(__('settings.th_record_id'), ENT_QUOTES, 'UTF-8') ?></th>
                                <th scope="col" class="py-3"><?= htmlspecialchars(__('settings.th_details'), ENT_QUOTES, 'UTF-8') ?></th>
                                <th scope="col" class="py-3 pe-3"><?= htmlspecialchars(__('settings.th_ip'), ENT_QUOTES, 'UTF-8') ?></th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php if (empty($auditLogs)): ?>
                                <tr>
                                    <td colspan="7" class="text-center py-4 text-muted"><?= htmlspecialchars(__('settings.no_audit_logs'), ENT_QUOTES, 'UTF-8') ?></td>
                                </tr>
                            <?php else: ?>
                                <?php foreach ($auditLogs as $al): ?>
                                    <?php 
                                        $alId = isset($al['id']) ? (int)$al['id'] : 0;
                                        $alCreatedAt = isset($al['created_at']) && is_string($al['created_at']) ? $al['created_at'] : '';
                                        $alUsername = isset($al['username']) && is_string($al['username']) ? $al['username'] : __('settings.system_guest');
                                        $alAction = isset($al['action']) && is_string($al['action']) ? $al['action'] : '';
                                        $alRecordId = isset($al['record_id']) ? (int)$al['record_id'] : 0;
                                        $alDetails = isset($al['details']) && is_string($al['details']) ? $al['details'] : '';
                                        $alIp = isset($al['ip_address']) && is_string($al['ip_address']) ? $al['ip_address'] : 'N/A';
                                    ?>
                                    <tr>
                                        <td class="ps-3 fw-bold"><?= $alId ?></td>
                                        <td class="text-nowrap"><?= format_user_time($alCreatedAt, $userTimezone, $fullFormatStr) ?></td>
                                        <td><?= htmlspecialchars($alUsername, ENT_QUOTES, 'UTF-8') ?></td>
                                        <td><span class="badge bg-light text-dark border"><?= htmlspecialchars($alAction, ENT_QUOTES, 'UTF-8') ?></span></td>
                                        <td><?= $alRecordId > 0 ? '#' . $alRecordId : '—' ?></td>
                                        <td class="text-break"><?= htmlspecialchars($alDetails, ENT_QUOTES, 'UTF-8') ?></td>
                                        <td class="pe-3 font-monospace"><?= htmlspecialchars($alIp, ENT_QUOTES, 'UTF-8') ?></td>
                                    </tr>
                                <?php endforeach; ?>
                            <?php endif; ?>
                        </tbody>
                    </table>
                </div>
            </div>
            <p class="text-muted small mt-2"><?= htmlspecialchars(__('settings.audit_limit_note'), ENT_QUOTES, 'UTF-8') ?></p>
        </div>
    </div>
</div>

<script>
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
    const hash = window.location.hash;
    
    let targetTab = 'tab-core';
    if (hash === '#test-mail-section') {
        targetTab = 'tab-core';
        setTimeout(() => {
            document.getElementById('test-mail-section').scrollIntoView({ behavior: 'smooth' });
        }, 200);
    } else if (urlParams.has('edit_role') || hash === '#tab-permissions') {
        targetTab = 'tab-permissions';
    } else if (hash === '#tab-modules') {
        targetTab = 'tab-modules';
    } else if (hash === '#tab-audit') {
        targetTab = 'tab-audit';
    } else if (hash === '#tab-maintenance') {
        targetTab = 'tab-maintenance';
    }

    const tabTriggerEl = document.querySelector('#' + targetTab);
    if (tabTriggerEl && window.bootstrap && window.bootstrap.Tab) {
        const tabInstance = new bootstrap.Tab(tabTriggerEl);
        tabInstance.show();
    }
});
</script>

<?php require_once __DIR__ . '/../partials/footer.php'; ?>
