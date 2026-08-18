<?php
/**
 * MIGRATED FILE MAPPING
 * ---------------------
 * Original Old File: admin/settings.php
 * Migrated Date: 2026-08-04 10:25:40
 */
declare(strict_types=1);
/** Translate with fallback when lang key is missing. @return string */
$__t = static function (string $key, string $fallback = ''): string {
    $v = function_exists('__') ? (string) __($key) : $key;
    if ($v !== $key && $v !== '') {
        return $v;
    }
    return $fallback !== '' ? $fallback : $key;
};

/** @var string $message */
/** @var string $error */
/** @var int $schemaCurrent */
/** @var int $schemaLatest */
/** @var bool $schemaNeedsUpdate */
/** @var string $currentSystemName */
/** @var string $currentDefaultLanguage */
/** @var array<int, string> $availableLanguages */
/** @var string $currentCaptchaProvider */
/** @var string $currentTurnstileSite */
/** @var string $currentTurnstileSecret */
/** @var string $currentRecaptchaSite */
/** @var string $currentRecaptchaSecret */
/** @var string $currentHcaptchaSite */
/** @var string $currentHcaptchaSecret */
/** @var string $currentMailDomain */
/** @var string $currentMailFrom */
/** @var string $currentMailDriver */
/** @var string $currentSmtpHost */
/** @var string $currentSmtpPort */
/** @var string $currentSmtpUser */
/** @var string $currentSmtpEncryption */
/** @var string $modModerationVal */
/** @var string $modVolunteersVal */
/** @var string $modFeedbackVal */
/** @var string $modUsersVal */
/** @var string $modLeaderboardVal */
/** @var string $maintenanceMode */
/** @var string $maintenanceReason */
/** @var string $maintenanceEta */
/** @var array<int, array<string, mixed>> $notices */
/** @var array<int, array<string, mixed>> $auditLogs */
/** @var array<int, string> $distinctActions */
/** @var string $userTimezone */
/** @var string $fullFormatStr */
/** @var array<int, array<string, mixed>> $rolesList */
/** @var array<int, array<int, true>> $activeMappings */
/** @var array<string, array<int, array<string, mixed>>> $categorizedPerms */
/** @var bool $canViewErrorLogs */
/** @var array<int, array<string, mixed>> $recentErrors */
/** @var array<string, mixed>|null $lookedUpError */
/** @var string $errorLookupId */
$canViewErrorLogs = $canViewErrorLogs ?? false;
$recentErrors     = $recentErrors ?? [];
$lookedUpError    = $lookedUpError ?? null;
$errorLookupId    = $errorLookupId ?? '';
/** @var bool $showDemoPacksTab */
$showDemoPacksTab = $showDemoPacksTab ?? false;
/** @var list<array{slug: string, label: string, summary: string, installed: bool, has_demo_data: bool}> $demoPacks */
$demoPacks = $demoPacks ?? [];
require_once ROOT_PATH . '/partials/header.php';
$basePath = defined('BASE_PATH') ? rtrim(BASE_PATH, '/') : '';
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
            <button class="nav-link active fw-bold" id="tab-core" data-bs-toggle="tab" data-bs-target="#panel-core" type="button" role="tab" aria-controls="panel-core" aria-selected="true"><?= htmlspecialchars($__t('settings.tab_core', 'Core'), ENT_QUOTES, 'UTF-8') ?></button>
        </li>
        <li class="nav-item" role="presentation">
            <button class="nav-link fw-bold text-secondary" id="tab-modules" data-bs-toggle="tab" data-bs-target="#panel-modules" type="button" role="tab" aria-controls="panel-modules" aria-selected="false"><?= htmlspecialchars($__t('settings.tab_modules', 'Modules'), ENT_QUOTES, 'UTF-8') ?></button>
        </li>
        <li class="nav-item" role="presentation">
            <button class="nav-link fw-bold text-secondary" id="tab-maintenance" data-bs-toggle="tab" data-bs-target="#panel-maintenance" type="button" role="tab" aria-controls="panel-maintenance" aria-selected="false"><?= htmlspecialchars($__t('settings.tab_maintenance', 'Maintenance'), ENT_QUOTES, 'UTF-8') ?></button>
        </li>
        <li class="nav-item" role="presentation">
            <button class="nav-link fw-bold text-secondary" id="tab-notices" data-bs-toggle="tab" data-bs-target="#panel-notices" type="button" role="tab" aria-controls="panel-notices" aria-selected="false"><?= htmlspecialchars($__t('settings.tab_notices', 'Notices'), ENT_QUOTES, 'UTF-8') ?></button>
        </li>
        <li class="nav-item" role="presentation">
            <button class="nav-link fw-bold text-secondary" id="tab-permissions" data-bs-toggle="tab" data-bs-target="#panel-permissions" type="button" role="tab" aria-controls="panel-permissions" aria-selected="false"><?= htmlspecialchars($__t('settings.tab_permissions', 'Permissions'), ENT_QUOTES, 'UTF-8') ?></button>
        </li>
        <li class="nav-item" role="presentation">
            <button class="nav-link fw-bold text-secondary" id="tab-audit" data-bs-toggle="tab" data-bs-target="#panel-audit" type="button" role="tab" aria-controls="panel-audit" aria-selected="false"><?= htmlspecialchars($__t('settings.tab_audit', 'Audit Log'), ENT_QUOTES, 'UTF-8') ?></button>
        </li>
        <?php if ($canViewErrorLogs): ?>
        <li class="nav-item" role="presentation">
            <button class="nav-link fw-bold text-secondary" id="tab-errors" data-bs-toggle="tab" data-bs-target="#panel-errors" type="button" role="tab" aria-controls="panel-errors" aria-selected="false"><?= htmlspecialchars($__t('settings.error_log_tab', 'Error Log'), ENT_QUOTES, 'UTF-8') ?></button>
        </li>
        <?php endif; ?>
        <?php if (!empty($showDemoPacksTab)): ?>
        <li class="nav-item" role="presentation">
            <button class="nav-link fw-bold text-secondary" id="tab-demo" data-bs-toggle="tab" data-bs-target="#panel-demo" type="button" role="tab" aria-controls="panel-demo" aria-selected="false"><?= htmlspecialchars($__t('settings.tab_demo', 'Demo packs'), ENT_QUOTES, 'UTF-8') ?></button>
        </li>
        <?php endif; ?>
    </ul>
    <div class="tab-content">
        <?php require __DIR__ . '/settings_parts/core.php'; ?>
        <?php require __DIR__ . '/settings_parts/modules.php'; ?>
        <?php require __DIR__ . '/settings_parts/maintenance.php'; ?>
        <?php require __DIR__ . '/settings_parts/notices.php'; ?>
        <?php require __DIR__ . '/settings_parts/permissions.php'; ?>
        <?php require __DIR__ . '/settings_parts/audit.php'; ?>
        <?php if ($canViewErrorLogs): ?>
            <?php require __DIR__ . '/settings_parts/errors.php'; ?>
        <?php endif; ?>
        <?php if (!empty($showDemoPacksTab)): ?>
            <?php require __DIR__ . '/settings_parts/demo.php'; ?>
        <?php endif; ?>
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
            const el = document.getElementById('test-mail-section');
            if (el) el.scrollIntoView({ behavior: 'smooth' });
        }, 200);
    } else if (urlParams.has('edit_role') || hash === '#tab-permissions') {
        targetTab = 'tab-permissions';
    } else if (hash === '#tab-modules') {
        targetTab = 'tab-modules';
    } else if (hash === '#tab-audit') {
        targetTab = 'tab-audit';
    } else if (hash === '#tab-notices') {
        targetTab = 'tab-notices';
    } else if (hash === '#tab-maintenance') {
        targetTab = 'tab-maintenance';
    } else if (hash === '#tab-errors' || urlParams.get('tab') === 'errors') {
        targetTab = 'tab-errors';
    } else if (hash === '#tab-demo' || urlParams.get('tab') === 'demo') {
        targetTab = 'tab-demo';
    }

    const tabTriggerEl = document.querySelector('#' + targetTab);
    if (tabTriggerEl && window.bootstrap && window.bootstrap.Tab) {
        const tabInstance = new bootstrap.Tab(tabTriggerEl);
        tabInstance.show();
    }
});
</script>
<?php require_once ROOT_PATH . '/partials/footer.php'; ?>
