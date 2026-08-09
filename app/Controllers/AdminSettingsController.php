<?php
/**
 * MIGRATED FILE MAPPING
 * ---------------------
 * Original Old File: admin/settings.php/admin/actions/save_settings.php
 * Migrated Date: 2026-08-05 03:44:11
 */
declare(strict_types=1);

namespace App\Controllers;

use App\Services\SettingsService;
use Exception;
use PDO;

// Ensure auth helpers are loaded
$authHelperPath = __DIR__ . '/../../db/auth_helpers.php';
if (file_exists($authHelperPath)) {
    require_once $authHelperPath;
}

// Ensure general functions are loaded
$functionsPath = __DIR__ . '/../../includes/functions.php';
if (file_exists($functionsPath)) {
    require_once $functionsPath;
}

class AdminSettingsController
{
    private PDO $pdo;
    private SettingsService $settingsService;

    public function __construct(PDO $pdo)
    {
        $this->pdo = $pdo;
        $this->settingsService = new SettingsService($pdo);
    }

    public function index(): void
    {
        /** @var array{id: int, username: string, timezone?: string} $currentUser */
        $currentUser = \require_admin_page($this->pdo, 'manage_settings', 'Manage global site settings, mail drivers, and maintenance mode');

        $message = $_SESSION['message'] ?? '';
        $error = $_SESSION['error'] ?? '';
        unset($_SESSION['message'], $_SESSION['error']);

        // Auto-register table-scoped permissions for any existing dynamic tables
        $this->settingsService->autoRegisterTablePermissions();

        $currentSystemName = \get_system_name($this->pdo);

        $currentMailDomain = $this->settingsService->getSettingVal('mail_domain', '');
        $currentMailFrom = $this->settingsService->getSettingVal('mail_from', '');
        $currentMailDriver = $this->settingsService->getSettingVal('mail_driver', 'mail');
        $currentSmtpHost = $this->settingsService->getSettingVal('smtp_host', '');
        $currentSmtpPort = $this->settingsService->getSettingVal('smtp_port', '587');
        $currentSmtpUser = $this->settingsService->getSettingVal('smtp_user', '');
        $currentSmtpEncryption = $this->settingsService->getSettingVal('smtp_encryption', 'tls');
        $maintenanceMode = $this->settingsService->getSettingVal('maintenance_mode', '0');
        $maintenanceReason = $this->settingsService->getSettingVal('maintenance_reason', 'Scheduled system maintenance and database updates.');
        $maintenanceEta = $this->settingsService->getSettingVal('maintenance_eta', 'Shortly');
        $currentDefaultLanguage = $this->settingsService->getSettingVal('default_language', 'en');

        // CAPTCHA Configuration Settings
        $currentCaptchaProvider = $this->settingsService->getSettingVal('captcha_provider', 'none');
        $currentTurnstileSite = $this->settingsService->getSettingVal('turnstile_site_key', '');
        $currentTurnstileSecret = $this->settingsService->getSettingVal('turnstile_secret_key', '');
        $currentRecaptchaSite = $this->settingsService->getSettingVal('recaptcha_site_key', '');
        $currentRecaptchaSecret = $this->settingsService->getSettingVal('recaptcha_secret_key', '');
        $currentHcaptchaSite = $this->settingsService->getSettingVal('hcaptcha_site_key', '');
        $currentHcaptchaSecret = $this->settingsService->getSettingVal('hcaptcha_secret_key', '');

        // Available languages
        /** @var array<int, string> $availableLanguages */
        $availableLanguages = $this->settingsService->getAvailableLanguages();

        // Schema version status
        $schemaStatus = $this->settingsService->getSchemaStatus();
        $schemaCurrent = $schemaStatus['current'];
        $schemaLatest = $schemaStatus['latest'];
        $schemaNeedsUpdate = $schemaStatus['needsUpdate'];

        // Module toggles state
        $modModerationVal = $this->settingsService->getSettingVal('module_moderation_enabled', '1');
        $modVolunteersVal = $this->settingsService->getSettingVal('module_volunteers_enabled', '1');
        $modFeedbackVal = $this->settingsService->getSettingVal('module_feedback_enabled', '1');
        $modUsersVal = $this->settingsService->getSettingVal('module_users_enabled', '1');
        $modLeaderboardVal = $this->settingsService->getSettingVal('module_leaderboard_enabled', '1');

        /** @var array<int, array<string, mixed>> $notices */
        $notices = $this->settingsService->getNotices();

        /** @var array<int, array<string, mixed>> $auditLogs */
        $auditLogs = $this->settingsService->getAuditLogs();

        /** @var array<int, string> $distinctActions */
        $distinctActions = $this->settingsService->getDistinctActions();

        // Roles & Permissions matrix data processing (extracted from view)
        $rolesList = $this->settingsService->getRolesList();
        $permsList = $this->settingsService->getPermissionsList();
        $activeMappings = $this->settingsService->getActiveMappings();

        $modUsersActive = is_module_enabled($this->pdo, 'users');
        $modVolunteersActive = is_module_enabled($this->pdo, 'volunteers');
        $modFeedbackActive = is_module_enabled($this->pdo, 'feedback');
        $modModerationActive = is_module_enabled($this->pdo, 'moderation');
        $modLeaderboardActive = is_module_enabled($this->pdo, 'leaderboard');

        $categorizedPerms = [];
        foreach ($permsList as $p) {
            $pkey = isset($p['permission_key']) && is_string($p['permission_key']) ? $p['permission_key'] : '';
            if (($pkey === 'manage_users' || $pkey === 'invite_users' || $pkey === 'access_onboarding') && !$modUsersActive) continue;
            if (($pkey === 'manage_volunteers' || $pkey === 'submit_volunteer') && !$modVolunteersActive) continue;
            if (($pkey === 'manage_feedback' || $pkey === 'submit_feedback') && !$modFeedbackActive) continue;
            if (($pkey === 'access_suggest_edit' || $pkey === 'moderate_suggestions') && !$modModerationActive) continue;
            if (($pkey === 'view_leaderboard') && !$modLeaderboardActive) continue;

            $cat = $this->settingsService->getPermissionCategory($pkey);
            $categorizedPerms[$cat][] = $p;
        }

        $userTimezone = isset($currentUser['timezone']) && is_string($currentUser['timezone']) ? $currentUser['timezone'] : 'UTC';
        $fullFormatStr = \get_user_datetime_format($currentUser);

        require_once __DIR__ . '/../Views/admin/settings.php';
    }

    public function store(): void
    {
        $serverMethod = isset($_SERVER['REQUEST_METHOD']) && is_string($_SERVER['REQUEST_METHOD']) ? $_SERVER['REQUEST_METHOD'] : 'GET';
        if ($serverMethod !== 'POST') {
            http_response_code(405);
            exit('Method Not Allowed');
        }

        \verify_csrf_token();
        /** @var array{id: int, username: string} $currentUser */
        $currentUser = \require_permission($this->pdo, 'manage_settings', 'Manage global site settings, mail drivers, and maintenance mode');

        $post = $_POST;
        $systemName = isset($post['system_name']) && is_string($post['system_name']) ? trim($post['system_name']) : '';
        $rawLang = isset($post['default_language']) && is_string($post['default_language']) ? strtolower(trim($post['default_language'])) : 'en';
        $defaultLanguage = preg_replace('/[^a-z_]/', '', $rawLang) ?: 'en';
        
        $mailDomain = isset($post['mail_domain']) && is_string($post['mail_domain']) ? trim($post['mail_domain']) : '';
        $mailFrom = isset($post['mail_from']) && is_string($post['mail_from']) ? trim($post['mail_from']) : '';
        $mailDriver = isset($post['mail_driver']) && is_string($post['mail_driver']) ? trim($post['mail_driver']) : 'mail';
        $smtpHost = isset($post['smtp_host']) && is_string($post['smtp_host']) ? trim($post['smtp_host']) : '';
        $smtpPort = isset($post['smtp_port']) ? (int)$post['smtp_port'] : 587;
        $smtpUser = isset($post['smtp_user']) && is_string($post['smtp_user']) ? trim($post['smtp_user']) : '';
        $smtpPass = isset($post['smtp_pass']) && is_string($post['smtp_pass']) ? $post['smtp_pass'] : '';
        $smtpEncryption = isset($post['smtp_encryption']) && is_string($post['smtp_encryption']) ? trim($post['smtp_encryption']) : 'tls';

        // CAPTCHA parameters
        $captchaProvider = isset($post['captcha_provider']) && is_string($post['captcha_provider']) ? trim($post['captcha_provider']) : 'none';
        $turnstileSite = isset($post['turnstile_site_key']) && is_string($post['turnstile_site_key']) ? trim($post['turnstile_site_key']) : '';
        $turnstileSecret = isset($post['turnstile_secret_key']) && is_string($post['turnstile_secret_key']) ? trim($post['turnstile_secret_key']) : '';
        $recaptchaSite = isset($post['recaptcha_site_key']) && is_string($post['recaptcha_site_key']) ? trim($post['recaptcha_site_key']) : '';
        $recaptchaSecret = isset($post['recaptcha_secret_key']) && is_string($post['recaptcha_secret_key']) ? trim($post['recaptcha_secret_key']) : '';
        $hcaptchaSite = isset($post['hcaptcha_site_key']) && is_string($post['hcaptcha_site_key']) ? trim($post['hcaptcha_site_key']) : '';
        $hcaptchaSecret = isset($post['hcaptcha_secret_key']) && is_string($post['hcaptcha_secret_key']) ? trim($post['hcaptcha_secret_key']) : '';

        // Only allow languages that have a file under lang/
        $langFile = __DIR__ . '/../../lang/' . $defaultLanguage . '.php';
        if (!is_file($langFile)) {
            $defaultLanguage = 'en';
        }

        if ($systemName !== '') {
            $stmt = $this->pdo->prepare("INSERT INTO site_settings (setting_key, setting_value) VALUES (?, ?) ON DUPLICATE KEY UPDATE setting_value = ?");

            $stmt->execute(['system_name', $systemName, $systemName]);
            $stmt->execute(['default_language', $defaultLanguage, $defaultLanguage]);

            // CAPTCHA persistence
            $stmt->execute(['captcha_provider', $captchaProvider, $captchaProvider]);
            $stmt->execute(['turnstile_site_key', $turnstileSite, $turnstileSite]);
            if ($turnstileSecret !== '') {
                $stmt->execute(['turnstile_secret_key', $turnstileSecret, $turnstileSecret]);
            }
            
            $stmt->execute(['recaptcha_site_key', $recaptchaSite, $recaptchaSite]);
            if ($recaptchaSecret !== '') {
                $stmt->execute(['recaptcha_secret_key', $recaptchaSecret, $recaptchaSecret]);
            }

            $stmt->execute(['hcaptcha_site_key', $hcaptchaSite, $hcaptchaSite]);
            if ($hcaptchaSecret !== '') {
                $stmt->execute(['hcaptcha_secret_key', $hcaptchaSecret, $hcaptchaSecret]);
            }

            $stmt->execute(['mail_domain', $mailDomain, $mailDomain]);
            $stmt->execute(['mail_from', $mailFrom, $mailFrom]);

            $stmt->execute(['mail_driver', $mailDriver, $mailDriver]);
            $stmt->execute(['smtp_host', $smtpHost, $smtpHost]);
            $stmt->execute(['smtp_port', $smtpPort, $smtpPort]);
            $stmt->execute(['smtp_user', $smtpUser, $smtpUser]);

            if ($smtpPass !== '') {
                $stmt->execute(['smtp_pass', $smtpPass, $smtpPass]);
            }

            $stmt->execute(['smtp_encryption', $smtpEncryption, $smtpEncryption]);

            $_SESSION['message'] = "Global site settings, mail configurations, and security parameters updated successfully.";
            $remoteAddr = isset($_SERVER['REMOTE_ADDR']) && is_string($_SERVER['REMOTE_ADDR']) ? $_SERVER['REMOTE_ADDR'] : '127.0.0.1';
            $audit = $this->pdo->prepare("INSERT INTO audit_logs (user_id, action, details, ip_address) VALUES (?, 'UPDATE_SETTINGS', ?, ?)");
            $audit->execute([$currentUser['id'], "Updated global site settings, mail drivers, and CAPTCHA configurations", $remoteAddr]);
        } else {
            $_SESSION['error'] = "System name cannot be empty.";
        }

        header('Location: ' . BASE_PATH . '/admin/settings');
        exit;
    }
}
