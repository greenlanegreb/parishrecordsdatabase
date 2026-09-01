<?php
declare(strict_types=1);

namespace App\Controllers;

use App\Services\DemoPackService;
use App\Services\SettingsService;
use Exception;
use PDO;

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
        $graphEarly = dirname(__DIR__, 2) . '/includes/permission_graph.php';
        if (is_file($graphEarly)) {
            require_once $graphEarly;
        }
        if (!isset($_SESSION['user_id'])) {
            $base = defined('BASE_PATH') ? rtrim((string) BASE_PATH, '/') : '';
            header('Location: ' . $base . '/login');
            exit;
        }
        if (!function_exists('user_can_open_settings') || !user_can_open_settings($this->pdo)) {
            require_once dirname(__DIR__, 2) . '/public/403.php';
            exit;
        }
        /** @var array{id: int, username: string, timezone?: string}|null $currentUser */
        $currentUser = get_current_user_data($this->pdo);
        if ($currentUser === null) {
            require_once dirname(__DIR__, 2) . '/public/403.php';
            exit;
        }

        $message = $_SESSION['message'] ?? '';
        $error   = $_SESSION['error'] ?? '';
        $fieldErrors = $_SESSION['field_errors'] ?? [];
        unset($_SESSION['message'], $_SESSION['error'], $_SESSION['field_errors']);

        // Auto-register table-scoped permissions
        $this->settingsService->autoRegisterTablePermissions();

        $graph = dirname(__DIR__, 2) . '/includes/permission_graph.php';
        if (is_file($graph)) {
            require_once $graph;
        }
        if (function_exists('prune_guest_role_permissions')) {
            try {
                prune_guest_role_permissions($this->pdo);
            } catch (Exception $e) {
                // ignore if tables are missing
            }
        }

        $currentSystemName = get_system_name($this->pdo);

        // Mail settings
        $currentMailDomain     = $this->settingsService->getSettingVal('mail_domain', '');
        $currentMailFrom       = $this->settingsService->getSettingVal('mail_from', '');
        $currentMapTileUrl       = $this->settingsService->getSettingVal('map_tile_url', '');
        $currentMapTileProvider  = $this->settingsService->getSettingVal('map_tile_provider', 'default');
        $currentMapTileApiKey    = $this->settingsService->getSettingVal('map_tile_api_key', '');
        $currentMapGeocodeProvider = $this->settingsService->getSettingVal('map_geocode_provider', 'nominatim');
        $currentMapGeocodeApiKey = $this->settingsService->getSettingVal('map_geocode_api_key', '');
        $currentMailDriver     = $this->settingsService->getSettingVal('mail_driver', 'mail');
        $currentSmtpHost       = $this->settingsService->getSettingVal('smtp_host', '');
        $currentSmtpPort       = $this->settingsService->getSettingVal('smtp_port', '587');
        $currentSmtpUser       = $this->settingsService->getSettingVal('smtp_user', '');
        $currentSmtpEncryption = $this->settingsService->getSettingVal('smtp_encryption', 'tls');

        // Maintenance
        $maintenanceMode   = $this->settingsService->getSettingVal('maintenance_mode', '0');
        $maintenanceReason = $this->settingsService->getSettingVal('maintenance_reason', 'Scheduled system maintenance and database updates.');
        $maintenanceEta    = $this->settingsService->getSettingVal('maintenance_eta', 'Shortly');

        // Language
        $currentDuplicateMode  = $this->settingsService->getSettingVal('duplicate_mode', 'warn');
        $currentDuplicatePicky = $this->settingsService->getSettingVal('duplicate_picky', 'similar');

        $currentDefaultLanguage = $this->settingsService->getSettingVal('default_language', 'en');
        $currentDefaultTimezone   = $this->settingsService->getSettingVal('default_timezone', 'UTC');
        $currentDefaultDateFormat = $this->settingsService->getSettingVal('default_date_format', 'd/m/Y');
        $currentDefaultTimeFormat = $this->settingsService->getSettingVal('default_time_format', '24');
        $currentFooterCompiledNotice = $this->settingsService->getSettingVal('footer_compiled_notice', '');

        // CAPTCHA
        $currentCaptchaProvider = $this->settingsService->getSettingVal('captcha_provider', 'none');
        $currentTurnstileSite   = $this->settingsService->getSettingVal('turnstile_site_key', '');
        $currentTurnstileSecret = $this->settingsService->getSettingVal('turnstile_secret_key', '');
        $currentRecaptchaSite   = $this->settingsService->getSettingVal('recaptcha_site_key', '');
        $currentRecaptchaSecret = $this->settingsService->getSettingVal('recaptcha_secret_key', '');
        $currentHcaptchaSite    = $this->settingsService->getSettingVal('hcaptcha_site_key', '');
        $currentHcaptchaSecret  = $this->settingsService->getSettingVal('hcaptcha_secret_key', '');

        // Available languages + schema status
        $availableLanguages = $this->settingsService->getAvailableLanguages();
        $schemaStatus       = $this->settingsService->getSchemaStatus();
        $schemaCurrent      = $schemaStatus['current'];
        $schemaLatest       = $schemaStatus['latest'];
        $schemaNeedsUpdate  = $schemaStatus['needsUpdate'];

        // Module toggles
        $modModerationVal  = $this->settingsService->getSettingVal('module_moderation_enabled', '1');
        $modVolunteersVal  = $this->settingsService->getSettingVal('module_volunteers_enabled', '1');
        $modFeedbackVal    = $this->settingsService->getSettingVal('module_feedback_enabled', '1');
        $modUsersVal       = $this->settingsService->getSettingVal('module_users_enabled', '1');
        $modLeaderboardVal = $this->settingsService->getSettingVal('module_leaderboard_enabled', '1');
        $modMapsVal        = $this->settingsService->getSettingVal('module_maps_enabled', '1');

        // Notices + Audit
        $notices         = $this->settingsService->getNotices();
        $auditLogs       = $this->settingsService->getAuditLogs();
        $distinctActions = $this->settingsService->getDistinctActions();

        // Roles & Permissions
        $rolesList      = $this->settingsService->getRolesList();
        $permsList      = $this->settingsService->getPermissionsList();
        $activeMappings = $this->settingsService->getActiveMappings();

        // Needed by the permissions tab
        $modUsersActive       = is_module_enabled($this->pdo, 'users');
        $modVolunteersActive  = is_module_enabled($this->pdo, 'volunteers');
        $modFeedbackActive    = is_module_enabled($this->pdo, 'feedback');
        $modModerationActive  = is_module_enabled($this->pdo, 'moderation');
        $modLeaderboardActive = is_module_enabled($this->pdo, 'leaderboard');

        $categorizedPerms = [];
        foreach ($permsList as $p) {
            $pkey = isset($p['permission_key']) && is_string($p['permission_key']) ? $p['permission_key'] : '';

            if (($pkey === 'manage_users' || $pkey === 'invite_users' || $pkey === 'access_onboarding') && !$modUsersActive) {
                continue;
            }
            if (($pkey === 'manage_volunteers' || $pkey === 'submit_volunteer') && !$modVolunteersActive) {
                continue;
            }
            if (($pkey === 'manage_feedback' || $pkey === 'submit_feedback') && !$modFeedbackActive) {
                continue;
            }
            if (($pkey === 'access_suggest_edit' || $pkey === 'moderate_suggestions') && !$modModerationActive) {
                continue;
            }
            if ($pkey === 'view_leaderboard' && !$modLeaderboardActive) {
                continue;
            }

            $cat = $this->settingsService->getPermissionCategory($pkey);
            $categorizedPerms[$cat][] = $p;
        }

        // Error log tab (permission-gated)
        $canViewErrorLogs = has_permission($this->pdo, 'view_error_logs');
        $recentErrors     = [];
        $lookedUpError    = null;
        $errorLookupId    = '';

        if ($canViewErrorLogs) {
            $recentErrors  = $this->settingsService->getRecentErrors(50);
            $errorLookupId = isset($_GET['error_id']) && is_string($_GET['error_id'])
                ? trim($_GET['error_id']) : '';
            if ($errorLookupId !== '') {
                $lookedUpError = $this->settingsService->findErrorById($errorLookupId);
            }
        }

        $userTimezone  = isset($currentUser['timezone']) && is_string($currentUser['timezone'])
            ? $currentUser['timezone'] : 'UTC';
        $fullFormatStr = get_user_datetime_format($currentUser);
        $basePath      = defined('BASE_PATH') && is_string(BASE_PATH) ? rtrim(BASE_PATH, '/') : '';

        $demoPacks = [];
        $showDemoPacksTab = false;
        try {
            $demoPacks = (new DemoPackService($this->pdo))->listPacks();
            $showDemoPacksTab = $demoPacks !== [];
        } catch (Exception $e) {
            $showDemoPacksTab = false;
        }

        $canManageSettings = has_permission($this->pdo, 'manage_settings');
        $canAuditLogs = has_permission($this->pdo, 'manage_audit_logs') || has_permission($this->pdo, 'purge_audit_entry');
        $canManageNotices = has_permission($this->pdo, 'manage_notices');
        if (!$canManageSettings) {
            $showDemoPacksTab = false;
        }

        require_once __DIR__ . '/../Views/admin/settings.php';
    }

    public function store(): void
    {
        if (($_SERVER['REQUEST_METHOD'] ?? 'GET') !== 'POST') {
            http_response_code(405);
            exit('Method Not Allowed');
        }

        verify_csrf_token();

        /** @var array{id: int, username: string} $currentUser */
        $currentUser = require_user_permission(
            $this->pdo,
            'manage_settings',
            'Manage global site settings, mail drivers, and maintenance mode'
        );

        try {
            $this->settingsService->saveCoreSettings($_POST, $currentUser);
            $ok = true;
            $msg = function_exists('__') && __('settings.saved') !== 'settings.saved'
                ? __('settings.saved')
                : 'Saved.';
        } catch (Exception $e) {
            $ok = false;
            $msg = $e->getMessage();
        }

        $ajax = isset($_SERVER['HTTP_X_REQUESTED_WITH'])
            && strtolower((string) $_SERVER['HTTP_X_REQUESTED_WITH']) === 'xmlhttprequest';
        if ($ajax) {
            header('Content-Type: application/json; charset=utf-8');
            echo json_encode(['ok' => $ok, 'message' => $msg]);
            exit;
        }
        if ($ok) {
            flash_success('Global site settings, mail configurations, and security parameters updated successfully.');
        } else {
            flash_error($msg);
        }
        redirect('/admin/settings');
    }
}
