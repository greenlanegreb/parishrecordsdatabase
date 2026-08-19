<?php
/**
 * MIGRATED FILE MAPPING
 * ---------------------
 * Extracted data-fetching & business logic for Admin Settings.
 */
declare(strict_types=1);

namespace App\Services;

use Exception;
use PDO;

class SettingsService
{
    private PDO $pdo;

    public function __construct(PDO $pdo)
    {
        $this->pdo = $pdo;
    }

    /**
     * Auto-register table-scoped permissions for any existing dynamic tables.
     */
    public function autoRegisterTablePermissions(): void
    {
        try {
            $existingTables = $this->pdo->query('SELECT id, table_name FROM dynamic_tables');
            /** @var array<int, array<string, mixed>> $tableRows */
            $tableRows = $existingTables !== false ? $existingTables->fetchAll(PDO::FETCH_ASSOC) : [];
            foreach ($tableRows as $et) {
                $tId = isset($et['id']) ? (int) $et['id'] : 0;
                $tName = isset($et['table_name']) && is_string($et['table_name']) ? $et['table_name'] : '';
                $viewKey = 'view_table_' . $tId;
                $viewDesc = 'Allows viewing and searching records in table: ' . $tName;
                $modKey = 'moderate_table_' . $tId;
                $modDesc = 'Allows reviewing and moderating suggestions in table: ' . $tName;
                $insP = $this->pdo->prepare('INSERT IGNORE INTO permissions (permission_key, description) VALUES (?, ?)');
                $insP->execute([$viewKey, $viewDesc]);
                $insP->execute([$modKey, $modDesc]);
            }
        } catch (Exception $e) {
            // Ignore database table discovery errors if not yet seeded
        }
    }

    public function getSettingVal(string $key, string $default): string
    {
        try {
            $stmt = $this->pdo->prepare('SELECT setting_value FROM site_settings WHERE setting_key = ?');
            $stmt->execute([$key]);
            $val = $stmt->fetchColumn();
            return ($val !== false && $val !== null && is_string($val)) ? $val : $default;
        } catch (Exception $e) {
            return $default;
        }
    }

    /**
     * @return array<int, string>
     */
    public function getAvailableLanguages(): array
    {
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
        return $availableLanguages;
    }

    /**
     * @return array{current: int, latest: int, needsUpdate: bool}
     */
    public function getSchemaStatus(): array
    {
        $schemaCurrent = function_exists('get_schema_version') ? \get_schema_version($this->pdo) : 0;
        $schemaLatest = $schemaCurrent;
        $migrationsDir = __DIR__ . '/../../db/migrations';
        if (is_dir($migrationsDir)) {
            $migGlob = glob($migrationsDir . '/*.php');
            if ($migGlob !== false) {
                foreach ($migGlob as $migFile) {
                    $m = [];
                    if (preg_match('/(\d+)_/', basename($migFile), $m)) {
                        $schemaLatest = max($schemaLatest, (int) $m[1]);
                    }
                }
            }
        }
        return [
            'current' => $schemaCurrent,
            'latest' => $schemaLatest,
            'needsUpdate' => ($schemaCurrent < $schemaLatest),
        ];
    }

    /**
     * @return array<int, array<string, mixed>>
     */
    public function getNotices(): array
    {
        $noticesStmt = $this->pdo->query('SELECT * FROM site_notices ORDER BY display_order ASC, id DESC');
        return $noticesStmt !== false ? $noticesStmt->fetchAll(PDO::FETCH_ASSOC) : [];
    }

    /**
     * @return array<int, array<string, mixed>>
     */
    public function getAuditLogs(): array
    {
        $auditStmt = $this->pdo->query('
            SELECT al.*, u.username
            FROM audit_logs al
            LEFT JOIN users u ON al.user_id = u.id
            ORDER BY al.created_at DESC
            LIMIT 250
        ');
        return $auditStmt !== false ? $auditStmt->fetchAll(PDO::FETCH_ASSOC) : [];
    }

    /**
     * @return array<int, string>
     */
    public function getDistinctActions(): array
    {
        $actionsStmt = $this->pdo->query('SELECT DISTINCT action FROM audit_logs ORDER BY action ASC');
        return $actionsStmt !== false ? $actionsStmt->fetchAll(PDO::FETCH_COLUMN) : [];
    }

    /**
     * @return array<int, array<string, mixed>>
     */
    public function getRolesList(): array
    {
        $rolesListStmt = $this->pdo->query('SELECT * FROM roles ORDER BY id ASC');
        return $rolesListStmt !== false ? $rolesListStmt->fetchAll(PDO::FETCH_ASSOC) : [];
    }

    /**
     * @return array<int, array<string, mixed>>
     */
    public function getPermissionsList(): array
    {
        $permsListStmt = $this->pdo->query('SELECT * FROM permissions ORDER BY id ASC');
        return $permsListStmt !== false ? $permsListStmt->fetchAll(PDO::FETCH_ASSOC) : [];
    }

    /**
     * @return array<int, array<int, true>>
     */
    public function getActiveMappings(): array
    {
        $activeMappings = [];
        $mapRowsStmt = $this->pdo->query('SELECT role_id, permission_id FROM role_permissions');
        $mapRows = $mapRowsStmt !== false ? $mapRowsStmt->fetchAll(PDO::FETCH_ASSOC) : [];
        foreach ($mapRows as $m) {
            $rId = isset($m['role_id']) ? (int) $m['role_id'] : 0;
            $pId = isset($m['permission_id']) ? (int) $m['permission_id'] : 0;
            $activeMappings[$rId][$pId] = true;
        }
        return $activeMappings;
    }

    public function getPermissionCategory(string $pkey): string
    {
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
        if (in_array($pkey, ['view_error_logs', 'manage_audit_logs', 'purge_audit_entry'], true)) {
            return 'Core System & Settings';
        }
        return 'Core System & Settings';
    }

    /**
     * Save core site settings (system name, language, locale defaults, mail, CAPTCHA, etc.)
     *
     * @param array<string, mixed> $post
     * @param array{id: int, username: string} $currentUser
     */
    public function saveCoreSettings(array $post, array $currentUser): void
    {
        $systemName = isset($post['system_name']) && is_string($post['system_name']) ? trim($post['system_name']) : '';
        if ($systemName === '') {
            throw new Exception('System name cannot be empty.');
        }

        $rawLang = isset($post['default_language']) && is_string($post['default_language'])
            ? strtolower(trim($post['default_language'])) : 'en';
        $defaultLanguage = preg_replace('/[^a-z_]/', '', $rawLang) ?: 'en';
        $langFile = __DIR__ . '/../../lang/' . $defaultLanguage . '.php';
        if (!is_file($langFile)) {
            $defaultLanguage = 'en';
        }

        $defaultTimezone = isset($post['default_timezone']) && is_string($post['default_timezone'])
            ? trim($post['default_timezone']) : 'UTC';
        if (!in_array($defaultTimezone, timezone_identifiers_list(), true)) {
            $defaultTimezone = 'UTC';
        }

        $footerCompiledNotice = isset($post['footer_compiled_notice']) && is_string($post['footer_compiled_notice'])
        ? trim($post['footer_compiled_notice']) : '';

        $defaultDateFormat = isset($post['default_date_format']) && is_string($post['default_date_format'])
            ? trim($post['default_date_format']) : 'd/m/Y';
        $allowedDates = ['d/m/Y', 'd.m.Y', 'Y-m-d', 'm/d/Y', 'd-m-Y'];
        if (!in_array($defaultDateFormat, $allowedDates, true)) {
            $defaultDateFormat = 'd/m/Y';
        }

        $defaultTimeFormat = isset($post['default_time_format']) && is_string($post['default_time_format'])
            ? trim($post['default_time_format']) : '24';
        if ($defaultTimeFormat !== '12' && $defaultTimeFormat !== '24') {
            $defaultTimeFormat = '24';
        }

        $mailDomain = isset($post['mail_domain']) && is_string($post['mail_domain']) ? trim($post['mail_domain']) : '';
        $mailFrom = isset($post['mail_from']) && is_string($post['mail_from']) ? trim($post['mail_from']) : '';
        $mailDriver = isset($post['mail_driver']) && is_string($post['mail_driver']) ? trim($post['mail_driver']) : 'mail';
        $smtpHost = isset($post['smtp_host']) && is_string($post['smtp_host']) ? trim($post['smtp_host']) : '';
        $smtpPort = isset($post['smtp_port']) ? (int) $post['smtp_port'] : 587;
        $smtpUser = isset($post['smtp_user']) && is_string($post['smtp_user']) ? trim($post['smtp_user']) : '';
        $smtpPass = isset($post['smtp_pass']) && is_string($post['smtp_pass']) ? $post['smtp_pass'] : '';
        $smtpEncryption = isset($post['smtp_encryption']) && is_string($post['smtp_encryption'])
            ? trim($post['smtp_encryption']) : 'tls';

        $captchaProvider = isset($post['captcha_provider']) && is_string($post['captcha_provider'])
            ? trim($post['captcha_provider']) : 'none';
        $turnstileSite = isset($post['turnstile_site_key']) && is_string($post['turnstile_site_key'])
            ? trim($post['turnstile_site_key']) : '';
        $turnstileSecret = isset($post['turnstile_secret_key']) && is_string($post['turnstile_secret_key'])
            ? trim($post['turnstile_secret_key']) : '';
        $recaptchaSite = isset($post['recaptcha_site_key']) && is_string($post['recaptcha_site_key'])
            ? trim($post['recaptcha_site_key']) : '';
        $recaptchaSecret = isset($post['recaptcha_secret_key']) && is_string($post['recaptcha_secret_key'])
            ? trim($post['recaptcha_secret_key']) : '';
        $hcaptchaSite = isset($post['hcaptcha_site_key']) && is_string($post['hcaptcha_site_key'])
            ? trim($post['hcaptcha_site_key']) : '';
        $hcaptchaSecret = isset($post['hcaptcha_secret_key']) && is_string($post['hcaptcha_secret_key'])
            ? trim($post['hcaptcha_secret_key']) : '';

        $stmt = $this->pdo->prepare(
            'INSERT INTO site_settings (setting_key, setting_value) VALUES (?, ?)
             ON DUPLICATE KEY UPDATE setting_value = ?'
        );

        $stmt->execute(['system_name', $systemName, $systemName]);
        $stmt->execute(['default_language', $defaultLanguage, $defaultLanguage]);
        $stmt->execute(['default_timezone', $defaultTimezone, $defaultTimezone]);
        $stmt->execute(['footer_compiled_notice', $footerCompiledNotice, $footerCompiledNotice]);
        $stmt->execute(['default_date_format', $defaultDateFormat, $defaultDateFormat]);
        $stmt->execute(['default_time_format', $defaultTimeFormat, $defaultTimeFormat]);

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
        $stmt->execute(['smtp_port', (string) $smtpPort, (string) $smtpPort]);
        $stmt->execute(['smtp_user', $smtpUser, $smtpUser]);
        if ($smtpPass !== '') {
            $stmt->execute(['smtp_pass', $smtpPass, $smtpPass]);
        }
        $stmt->execute(['smtp_encryption', $smtpEncryption, $smtpEncryption]);

        $dupMode = isset($post['duplicate_mode']) && is_string($post['duplicate_mode'])
            ? trim($post['duplicate_mode']) : 'warn';
        if (!in_array($dupMode, ['off', 'warn', 'block', 'flag'], true)) {
            $dupMode = 'warn';
        }
        $dupPicky = isset($post['duplicate_picky']) && is_string($post['duplicate_picky'])
            ? trim($post['duplicate_picky']) : 'similar';
        if (!in_array($dupPicky, ['exact', 'similar'], true)) {
            $dupPicky = 'similar';
        }
        $stmt->execute(['duplicate_mode', $dupMode, $dupMode]);
        $stmt->execute(['duplicate_picky', $dupPicky, $dupPicky]);

        $remoteAddr = isset($_SERVER['REMOTE_ADDR']) && is_string($_SERVER['REMOTE_ADDR'])
            ? $_SERVER['REMOTE_ADDR'] : '127.0.0.1';
        audit(
            $this->pdo,
            (int) $currentUser['id'],
            'UPDATE_SETTINGS',
            'Updated global site settings, locale defaults, mail drivers, and CAPTCHA configurations',
            $remoteAddr
        );
    }

    private function getErrorLogPath(): string
    {
        return dirname(__DIR__, 2) . '/logs/error_structured.log';
    }

    /**
     * @return array<string, mixed>|null
     */
    public function findErrorById(string $id): ?array
    {
        $id = trim($id);
        if ($id === '' || !preg_match('/^E-\d{8}-[A-F0-9]+$/i', $id)) {
            return null;
        }
        $path = $this->getErrorLogPath();
        if (!is_file($path) || !is_readable($path)) {
            return null;
        }
        $lines = @file($path, FILE_IGNORE_NEW_LINES | FILE_SKIP_EMPTY_LINES);
        if ($lines === false) {
            return null;
        }
        for ($i = count($lines) - 1; $i >= 0; $i--) {
            $row = json_decode($lines[$i], true);
            if (!is_array($row)) {
                continue;
            }
            if (isset($row['id']) && strcasecmp((string) $row['id'], $id) === 0) {
                return $row;
            }
        }
        return null;
    }

    /**
     * @return array<int, array<string, mixed>>
     */
    public function getRecentErrors(int $limit = 50): array
    {
        $limit = max(1, min(200, $limit));
        $path = $this->getErrorLogPath();
        if (!is_file($path) || !is_readable($path)) {
            return [];
        }
        $lines = @file($path, FILE_IGNORE_NEW_LINES | FILE_SKIP_EMPTY_LINES);
        if ($lines === false || $lines === []) {
            return [];
        }
        $out = [];
        for ($i = count($lines) - 1; $i >= 0 && count($out) < $limit; $i--) {
            $row = json_decode($lines[$i], true);
            if (is_array($row) && isset($row['id'])) {
                $out[] = $row;
            }
        }
        return $out;
    }
}
