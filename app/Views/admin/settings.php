<?php
/**
 * MIGRATED FILE MAPPING
 * ---------------------
 * Original Old File: admin/settings.php
 * Migrated Date: 2026-08-04 10:25:40
 */
declare(strict_types=1);
/** Translate with fallback when lang key is missing. @return string */
$prdTitleCase = static function (string $text): string {
    $small = ['a','an','and','as','at','but','by','for','from','in','into','of','on','or','the','to','with','via'];
    $words = preg_split('/(\s+)/', $text, -1, PREG_SPLIT_DELIM_CAPTURE) ?: [];
    $i = 0;
    $out = '';
    foreach ($words as $w) {
        if ($w === '' || preg_match('/^\s+$/', $w)) {
            $out .= $w;
            continue;
        }
        $low = function_exists('mb_strtolower') ? mb_strtolower($w) : strtolower($w);
        $first = function_exists('mb_substr') ? mb_substr($low, 0, 1) : substr($low, 0, 1);
        $rest = function_exists('mb_substr') ? mb_substr($low, 1) : substr($low, 1);
        $cap = (function_exists('mb_strtoupper') ? mb_strtoupper($first) : strtoupper($first)) . $rest;
        if ($i > 0 && in_array($low, $small, true)) {
            $out .= $low;
        } else {
            $out .= $cap;
        }
        $i++;
    }
    return $out;
};
$__t = static function (string $key, string $fallback = '') use ($prdTitleCase): string {
    $v = function_exists('__') ? (string) __($key) : $key;
    if ($v === $key || $v === '') {
        $v = $fallback !== '' ? $fallback : $key;
    }
    return $prdTitleCase($v);
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
$canManageSettings = $canManageSettings ?? (function_exists('has_permission') && isset($pdo) && $pdo instanceof PDO && has_permission($pdo, 'manage_settings'));
$canAuditLogs = $canAuditLogs ?? (function_exists('has_permission') && isset($pdo) && $pdo instanceof PDO && has_permission($pdo, 'manage_audit_logs'));
$canManageNotices = $canManageNotices ?? (function_exists('has_permission') && isset($pdo) && $pdo instanceof PDO && has_permission($pdo, 'manage_notices'));
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
#main-content .form-check {
    margin-bottom: 0.7rem;
}
.perm-grid {
    display: grid;
    grid-template-columns: repeat(auto-fill, minmax(16rem, 1fr));
    gap: 0.5rem 1rem;
    align-items: start;
}
.perm-role-table tbody tr td {
    padding-top: 1rem;
    padding-bottom: 1rem;
    border-bottom: 1px solid #dee2e6;
}
#panel-appearance input[type=color] {
    min-width: 3rem;
    min-height: 2.25rem;
}
</style>
<div class="container-fluid px-3 px-md-4 px-xl-5 py-4" role="region" aria-label="Site Settings Form">
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
    <p class="text-muted mb-4"><?= htmlspecialchars($__t('settings.subheading', 'Manage Core Configurations, Mail Drivers, Security/CAPTCHA Options, Feature Modules, Maintenance Mode, Site Announcements, and Role Capabilities.'), ENT_QUOTES, 'UTF-8') ?></p>
    <!-- Accessible Bootstrap Nav Tabs -->
    <div class="prd-tabs-scroll">
    <ul class="nav nav-tabs mb-0 flex-nowrap" role="tablist" aria-label="Settings Sections">
        <?php if (!empty($canManageSettings)): ?>
        <li class="nav-item" role="presentation">
            <button class="nav-link fw-bold" id="tab-core" data-bs-toggle="tab" data-bs-target="#panel-core" type="button" role="tab" aria-controls="panel-core" aria-selected="false"><?= htmlspecialchars($__t('settings.tab_core', 'Core'), ENT_QUOTES, 'UTF-8') ?></button>
        </li>
        <li class="nav-item" role="presentation">
            <button class="nav-link fw-bold text-secondary" id="tab-appearance" data-bs-toggle="tab" data-bs-target="#panel-appearance" type="button" role="tab" aria-controls="panel-appearance" aria-selected="false"><?= htmlspecialchars($__t('settings.tab_appearance', 'Appearance'), ENT_QUOTES, 'UTF-8') ?></button>
        </li>
        <?php endif; ?>
        <?php if (!empty($canManageSettings)): ?>
        <li class="nav-item" role="presentation">
            <button class="nav-link fw-bold text-secondary" id="tab-modules" data-bs-toggle="tab" data-bs-target="#panel-modules" type="button" role="tab" aria-controls="panel-modules" aria-selected="false"><?= htmlspecialchars($__t('settings.tab_modules', 'Modules'), ENT_QUOTES, 'UTF-8') ?></button>
        </li>
        <?php endif; ?>
        <?php if (!empty($canManageSettings)): ?>
        <li class="nav-item" role="presentation">
            <button class="nav-link fw-bold text-secondary" id="tab-maintenance" data-bs-toggle="tab" data-bs-target="#panel-maintenance" type="button" role="tab" aria-controls="panel-maintenance" aria-selected="false"><?= htmlspecialchars($__t('settings.tab_maintenance', 'Maintenance'), ENT_QUOTES, 'UTF-8') ?></button>
        </li>
        <?php endif; ?>
        <?php if (!empty($canManageNotices)): ?>
        <li class="nav-item" role="presentation">
            <button class="nav-link fw-bold text-secondary" id="tab-notices" data-bs-toggle="tab" data-bs-target="#panel-notices" type="button" role="tab" aria-controls="panel-notices" aria-selected="false"><?= htmlspecialchars($__t('settings.tab_notices', 'Notices'), ENT_QUOTES, 'UTF-8') ?></button>
        </li>
        <?php endif; ?>
        <?php if (!empty($canManageSettings)): ?>
        <li class="nav-item" role="presentation">
            <button class="nav-link fw-bold text-secondary" id="tab-permissions" data-bs-toggle="tab" data-bs-target="#panel-permissions" type="button" role="tab" aria-controls="panel-permissions" aria-selected="false"><?= htmlspecialchars($__t('settings.tab_permissions', 'Permissions'), ENT_QUOTES, 'UTF-8') ?></button>
        </li>
        <?php endif; ?>
        <?php if (!empty($canAuditLogs)): ?>
        <li class="nav-item" role="presentation">
            <button class="nav-link fw-bold text-secondary" id="tab-audit" data-bs-toggle="tab" data-bs-target="#panel-audit" type="button" role="tab" aria-controls="panel-audit" aria-selected="false"><?= htmlspecialchars($__t('settings.tab_audit', 'Audit Log'), ENT_QUOTES, 'UTF-8') ?></button>
        </li>
        <?php endif; ?>
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
    </div>
    <div class="tab-content">
        <?php if (!empty($canManageSettings)): ?>
            <?php require __DIR__ . '/settings_parts/core.php'; ?>
            <?php require __DIR__ . '/settings_parts/appearance.php'; ?>
            <?php require __DIR__ . '/settings_parts/modules.php'; ?>
            <?php require __DIR__ . '/settings_parts/maintenance.php'; ?>
            <?php require __DIR__ . '/settings_parts/permissions.php'; ?>
        <?php endif; ?>
        <?php if (!empty($canManageNotices)): ?>
            <?php require __DIR__ . '/settings_parts/notices.php'; ?>
        <?php endif; ?>
        <?php if (!empty($canAuditLogs)): ?>
            <?php require __DIR__ . '/settings_parts/audit.php'; ?>
        <?php endif; ?>
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
    const hash = (window.location.hash || '').replace(/^#/, '');
    const tabParam = (urlParams.get('tab') || '').toLowerCase().replace(/^#/, '').replace(/^tab-/, '');

    const resolveButtonId = () => {
        if (urlParams.has('edit_role')) return 'tab-permissions';
        if (tabParam === 'test-mail-section' || hash === 'test-mail-section') return 'tab-core';
        const name = tabParam || hash.replace(/^tab-/, '');
        if (!name) return 'tab-core';
        const id = name.startsWith('tab-') ? name : ('tab-' + name);
        return document.getElementById(id) ? id : 'tab-core';
    };

    const activateTab = (buttonId) => {
        const trigger = document.getElementById(buttonId);
        if (!trigger) return;

        // Always clear first — avoids Core + Notices both having .show.active
        document.querySelectorAll('.nav-tabs [role="tab"]').forEach((el) => {
            el.classList.remove('active');
            el.setAttribute('aria-selected', 'false');
        });
        document.querySelectorAll('.tab-content > .tab-pane').forEach((el) => {
            el.classList.remove('show', 'active');
        });

        trigger.classList.add('active');
        trigger.setAttribute('aria-selected', 'true');
        const targetSel = trigger.getAttribute('data-bs-target');
        const pane = targetSel ? document.querySelector(targetSel) : null;
        if (pane) {
            pane.classList.add('show', 'active');
        }

        if (window.bootstrap && window.bootstrap.Tab) {
            try {
                bootstrap.Tab.getOrCreateInstance(trigger).show();
            } catch (e) { /* classes already set */ }
        }

        if (tabParam === 'test-mail-section' || hash === 'test-mail-section') {
            setTimeout(() => {
                const el = document.getElementById('test-mail-section');
                if (el) el.scrollIntoView({ behavior: 'smooth' });
            }, 150);
        }
    };

    activateTab(resolveButtonId());
    function refreshAppearanceSwatches() {
        const pane = document.getElementById('panel-appearance');
        if (!pane) return;
        pane.querySelectorAll('.tab-pane').forEach((p) => p.classList.remove('show', 'active'));
        const colors = document.getElementById('appear-colors');
        if (colors) colors.classList.add('show', 'active');
        document.querySelectorAll('#appear-colors-tab, [data-bs-target="#appear-colors"]').forEach((b) => {
            b.classList.add('active');
            b.setAttribute('aria-selected', 'true');
        });
        pane.querySelectorAll('input[type=color]').forEach((el) => {
            const parent = el.parentNode;
            if (!parent) return;
            const neu = el.cloneNode(true);
            parent.replaceChild(neu, el);
        });
    }
    document.getElementById('tab-appearance')?.addEventListener('shown.bs.tab', () => setTimeout(refreshAppearanceSwatches, 200));
    document.getElementById('tab-appearance')?.addEventListener('click', () => setTimeout(refreshAppearanceSwatches, 250));
});
</script>

<script>
document.addEventListener('shown.bs.tab', function (ev) {
    var id = ev.target && ev.target.id ? String(ev.target.id) : '';
    if (id.indexOf('tab-') !== 0) return;
    var tab = id.slice(4);
    var url = new URL(window.location.href);
    url.searchParams.set('tab', tab);
    url.hash = '';
    if (window.history && window.history.replaceState) {
        window.history.replaceState({}, '', url.pathname + url.search);
    }
});
</script>
<script>
(function () {
    var form = document.getElementById('settings-core-form');
    if (!form) return;
    var timer = null;
    var lastEl = null;
    var saved = <?= json_encode(function_exists('__') && __('settings.saved') !== 'settings.saved' ? __('settings.saved') : 'Saved.', JSON_UNESCAPED_UNICODE) ?>;
    function mark(el) {
        if (!el || !el.parentElement) return;
        var tag = el.parentElement.querySelector('.appear-saved');
        if (!tag) {
            tag = document.createElement('span');
            tag.className = 'appear-saved small text-success ms-2';
            el.parentElement.appendChild(tag);
        }
        tag.hidden = false;
        tag.textContent = saved;
    }
    function persist() {
        var data = new FormData(form);
        fetch(form.action, {
            method: 'POST',
            body: data,
            credentials: 'same-origin',
            headers: { 'X-Requested-With': 'XMLHttpRequest' }
        }).then(function (r) { return r.json(); }).then(function () {
            mark(lastEl);
        }).catch(function () {});
    }
    form.addEventListener('change', function (e) {
        lastEl = e.target;
        clearTimeout(timer);
        timer = setTimeout(persist, 250);
    });
    form.addEventListener('focusout', function (e) {
        if (!e.target) return;
        lastEl = e.target;
        clearTimeout(timer);
        timer = setTimeout(persist, 250);
    });
})();
</script>
<?php require_once ROOT_PATH . '/partials/footer.php'; ?>
