<?php
/**
 * MIGRATED FILE MAPPING
 * ---------------------
 * Original Old File: admin/settings.php/admin/actions/save_settings.php
 * Migrated Date: 2026-08-05 03:44:11
 */declare(strict_types=1);


namespace App\Controllers;

use Exception;
use PDO;

class AdminSettingsController
{
    private PDO $pdo;

    public function __construct(PDO $pdo)
    {
        $this->pdo = $pdo;
    }

    public function index(): void
    {
        if (session_status() === PHP_SESSION_NONE) {
            session_start();
        }

        /** @var array{id: int, username: string, timezone?: string} $currentUser */
        $currentUser = require_admin_page($this->pdo, 'manage_settings', 'Manage global site settings, mail drivers, and maintenance mode');

        $message = $_SESSION['message'] ?? '';
        $error = $_SESSION['error'] ?? '';
        unset($_SESSION['message'], $_SESSION['error']);

        // Auto-register table-scoped permissions for any existing dynamic tables
        try {
            $existingTables = $this->pdo->query("SELECT id, table_name FROM dynamic_tables");
            /** @var array<int, array<string, mixed>> $tableRows */
            $tableRows = $existingTables !== false ? $existingTables->fetchAll(PDO::FETCH_ASSOC) : [];
            foreach ($tableRows as $et) {
                $tId = isset($et['id']) ? (int)$et['id'] : 0;
                $tName = isset($et['table_name']) && is_string($et['table_name']) ? $et['table_name'] : '';
                $viewKey = 'view_table_' . $tId;
                $viewDesc = 'Allows viewing and searching records in table: ' . $tName;
                $modKey = 'moderate_table_' . $tId;
                $modDesc = 'Allows reviewing and moderating suggestions in table: ' . $tName;
                
                $insP = $this->pdo->prepare("INSERT IGNORE INTO permissions (permission_key, description) VALUES (?, ?)");
                $insP->execute([$viewKey, $viewDesc]);
                $insP->execute([$modKey, $modDesc]);
            }
        } catch (Exception $e) {
            // Ignore database table discovery errors if not yet seeded
        }

        $currentSystemName = get_system_name($this->pdo);

        $getSettingVal = function(PDO $pdo, string $key, string $default): string {
            try {
                $stmt = $pdo->prepare("SELECT setting_value FROM site_settings WHERE setting_key = ?");
                $stmt->execute([$key]);
                $val = $stmt->fetchColumn();
                return ($val !== false && $val !== null && is_string($val)) ? $val : $default;
            } catch (Exception $e) {
                return $default;
            }
        };

        $currentMailDomain = $getSettingVal($this->pdo, 'mail_domain', '');
        $currentMailFrom = $getSettingVal($this->pdo, 'mail_from', '');
        $currentMailDriver = $getSettingVal($this->pdo, 'mail_driver', 'mail');
        $currentSmtpHost = $getSettingVal($this->pdo, 'smtp_host', '');
        $currentSmtpPort = $getSettingVal($this->pdo, 'smtp_port', '587');
        $currentSmtpUser = $getSettingVal($this->pdo, 'smtp_user', '');
        $currentSmtpEncryption = $getSettingVal($this->pdo, 'smtp_encryption', 'tls');
        $maintenanceMode = $getSettingVal($this->pdo, 'maintenance_mode', '0');
        $maintenanceReason = $getSettingVal($this->pdo, 'maintenance_reason', 'Scheduled system maintenance and database updates.');
        $maintenanceEta = $getSettingVal($this->pdo, 'maintenance_eta', 'Shortly');
        $currentDefaultLanguage = $getSettingVal($this->pdo, 'default_language', 'en');

        // CAPTCHA Configuration Settings
        $currentCaptchaProvider = $getSettingVal($this->pdo, 'captcha_provider', 'none');
        $currentTurnstileSite = $getSettingVal($this->pdo, 'turnstile_site_key', '');
        $currentTurnstileSecret = $getSettingVal($this->pdo, 'turnstile_secret_key', '');
        $currentRecaptchaSite = $getSettingVal($this->pdo, 'recaptcha_site_key', '');
        $currentRecaptchaSecret = $getSettingVal($this->pdo, 'recaptcha_secret_key', '');
        $currentHcaptchaSite = $getSettingVal($this->pdo, 'hcaptcha_site_key', '');
        $currentHcaptchaSecret = $getSettingVal($this->pdo, 'hcaptcha_secret_key', '');

        // Available language files in /lang
        /** @var array<int, string> $availableLanguages */
        $availableLanguages = [];
        $langDir = __DIR__ . '/../../lang';
        if (is_dir($langDir)) {
            $globFiles = glob($langDir . '/*.php');
            if ($globFiles !== false) {
                foreach ($globFiles as $file) {
                    $code = basename($file, '.php');
                    if (preg_match('/^[a-z_]+$/', $code)) {
                        $availableLanguages[] = $code;
                    }
                }
            }
            sort($availableLanguages);
        }
        if (!in_array('en', $availableLanguages, true)) {
            array_unshift($availableLanguages, 'en');
        }

        // Schema version status for update UI
        $schemaCurrent = function_exists('get_schema_version') ? get_schema_version($this->pdo) : 0;
        $schemaLatest = $schemaCurrent;
        $migrationsDir = __DIR__ . '/../../db/migrations';
        if (is_dir($migrationsDir)) {
            $migGlob = glob($migrationsDir . '/*.php');
            if ($migGlob !== false) {
                foreach ($migGlob as $migFile) {
                    $m = [];
                    if (preg_match('/(\d+)_/', basename($migFile), $m)) {
                        $schemaLatest = max($schemaLatest, (int)$m[1]);
                    }
                }
            }
        }
        $schemaNeedsUpdate = ($schemaCurrent < $schemaLatest);

        // Module toggles state
        $modModerationVal = $getSettingVal($this->pdo, 'module_moderation_enabled', '1');
        $modVolunteersVal = $getSettingVal($this->pdo, 'module_volunteers_enabled', '1');
        $modFeedbackVal = $getSettingVal($this->pdo, 'module_feedback_enabled', '1');
        $modUsersVal = $getSettingVal($this->pdo, 'module_users_enabled', '1');
        $modLeaderboardVal = $getSettingVal($this->pdo, 'module_leaderboard_enabled', '1');

        $noticesStmt = $this->pdo->query("SELECT * FROM site_notices ORDER BY display_order ASC, id DESC");
        /** @var array<int, array<string, mixed>> $notices */
        $notices = $noticesStmt !== false ? $noticesStmt->fetchAll(PDO::FETCH_ASSOC) : [];

        // Audit log data fetching
        $auditStmt = $this->pdo->query("
            SELECT al.*, u.username 
            FROM audit_logs al 
            LEFT JOIN users u ON al.user_id = u.id 
            ORDER BY al.created_at DESC 
            LIMIT 250
        ");
        /** @var array<int, array<string, mixed>> $auditLogs */
        $auditLogs = $auditStmt !== false ? $auditStmt->fetchAll(PDO::FETCH_ASSOC) : [];

        $actionsStmt = $this->pdo->query("SELECT DISTINCT action FROM audit_logs ORDER BY action ASC");
        /** @var array<int, string> $distinctActions */
        $distinctActions = $actionsStmt !== false ? $actionsStmt->fetchAll(PDO::FETCH_COLUMN) : [];

        $userTimezone = isset($currentUser['timezone']) && is_string($currentUser['timezone']) ? $currentUser['timezone'] : 'UTC';
        $fullFormatStr = get_user_datetime_format($currentUser);

        require_once __DIR__ . '/../Views/admin/settings.php';
    }

    public function store(): void
    {
        if (session_status() === PHP_SESSION_NONE) {
            session_start();
        }

        $serverMethod = isset($_SERVER['REQUEST_METHOD']) && is_string($_SERVER['REQUEST_METHOD']) ? $_SERVER['REQUEST_METHOD'] : 'GET';
        if ($serverMethod !== 'POST') {
            http_response_code(405);
            exit('Method Not Allowed');
        }

        verify_csrf_token();
        /** @var array{id: int, username: string} $currentUser */
        $currentUser = require_permission($this->pdo, 'manage_settings', 'Manage global site settings, mail drivers, and maintenance mode');

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

        header('Location: /admin/settings');
        exit;
    }
}
