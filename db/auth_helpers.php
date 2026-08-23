<?php
declare(strict_types=1);
/**
 * MIGRATED FILE MAPPING
 * ---------------------
 * Original Old File: db/auth_helpers.php
 * Migrated Date: 2026-08-05 11:20:00
 */

// db/auth_helpers.php - Centralized Role, Permission, and Session Management
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

/**
 * Fetch current user data from the database along with their dynamic role details.
 * 
 * @param PDO $pdo
 * @return array<string, mixed>|null
 */
function get_current_user_data(PDO $pdo): ?array
{
    if (!isset($_SESSION['user_id'])) {
        return null;
    }

    $userId = is_numeric($_SESSION['user_id']) ? (int)$_SESSION['user_id'] : 0;
    if ($userId <= 0) {
        return null;
    }

    try {
        $stmt = $pdo->prepare("
            SELECT u.id, u.username, u.first_name, u.surname, u.email, u.role_id, r.role_name as role, u.points, u.two_fa_enabled, u.email_verified, u.attribution_display_mode, u.timezone, u.date_format, u.time_format, u.language, u.is_new_user
            FROM users u
            LEFT JOIN roles r ON u.role_id = r.id
            WHERE u.id = ?
        ");
        $stmt->execute([$userId]);
        $userData = $stmt->fetch(PDO::FETCH_ASSOC);
        return is_array($userData) ? $userData : null;
    } catch (\PDOException $e) {
        // SQLSTATE 42S22 = Unknown column, SQLSTATE 42S02 = Base table or view not found
        $errCode = (string)$e->getCode();
        $errMsg = $e->getMessage();
        if (in_array($errCode, ['42S22', '42S02'], true) || str_contains($errMsg, 'Unknown column') || str_contains($errMsg, 'Base table or view not found')) {

            // Check if there are actual pending migrations waiting to be run
            $schemaCurrent = function_exists('get_schema_version') ? get_schema_version($pdo) : 0;
            $schemaLatest = $schemaCurrent;
            $migrationsDir = __DIR__ . '/migrations';

            if (is_dir($migrationsDir)) {
                $globFiles = glob($migrationsDir . '/*.php');
                if ($globFiles !== false) {
                    foreach ($globFiles as $migFile) {
                        $m = [];
                        if (preg_match('/(\d+)_/', basename($migFile), $m)) {
                            $schemaLatest = max($schemaLatest, (int)$m[1]);
                        }
                    }
                }
            }

            // If a structural error occurred AND updates are waiting, route safely to the update gateway
            if ($schemaCurrent < $schemaLatest) {
                $base = defined('BASE_PATH') && is_string(BASE_PATH) ? rtrim(BASE_PATH, '/') : '';
                header('Location: ' . $base . '/update_database.php');
                exit;
            }
        }

        throw $e;
    }
}

/**
 * Map legacy permission keys to current names (safe during rollout).
 */
function normalize_permission_key(string $permissionKey): string
{
    if ($permissionKey === 'view_public') {
        return 'view_as_guest';
    }
    return $permissionKey;
}

/**
 * Check if the current user possesses a specific dynamic permission key.
 * Not logged in → treated as guest role id 4 for matrix checks.
 */
function has_permission(PDO $pdo, string $permissionKey, ?string $description = null): bool
{
    $permissionKey = normalize_permission_key($permissionKey);

    $user = get_current_user_data($pdo);
    $roleId = ($user !== null && isset($user['role_id']) && is_numeric($user['role_id'])) ? (int)$user['role_id'] : 4; // Fallback to guest (Role ID 4)

    /** @var array<string, bool> $permissionCache */
    static $permissionCache = [];
    $cacheKey = $roleId . '_' . $permissionKey;
    if (isset($permissionCache[$cacheKey])) {
        return $permissionCache[$cacheKey];
    }

    try {
        $stmt = $pdo->prepare("
            SELECT COUNT(*) FROM role_permissions rp
            JOIN permissions p ON rp.permission_id = p.id
            WHERE rp.role_id = ? AND p.permission_key = ?
        ");
        $stmt->execute([$roleId, $permissionKey]);
        $has = ((int)$stmt->fetchColumn() > 0);
    } catch (\Exception $e) {
        $has = false;
    }

    $permissionCache[$cacheKey] = $has;
    return $has;
}

/**
 * Does the guest role currently hold the given permission?
 * Used by public-facing pages so they don't each reinvent the query.
 */
function guest_has_permission(PDO $pdo, string $permissionKey): bool
{
    $permissionKey = normalize_permission_key($permissionKey);

    /** @var array<string, bool> $cache */
    static $cache = [];
    if (array_key_exists($permissionKey, $cache)) {
        return $cache[$permissionKey];
    }

    try {
        $stmt = $pdo->prepare("
            SELECT COUNT(*)
            FROM role_permissions rp
            JOIN roles r ON rp.role_id = r.id
            JOIN permissions p ON rp.permission_id = p.id
            WHERE p.permission_key = ?
              AND LOWER(r.role_name) = 'guest'
        ");
        $stmt->execute([$permissionKey]);
        $cache[$permissionKey] = ((int)$stmt->fetchColumn() > 0);
    } catch (\Exception $e) {
        $cache[$permissionKey] = false;
    }

    return $cache[$permissionKey];
}

/**
 * Can the current visitor (logged-in or guest) view this dynamic table?
 * - Logged in: needs view_table_{id}
 * - Guest: needs view_as_guest AND view_table_{id}
 */

function user_can_view_table(PDO $pdo, int $tableId, ?array $currentUser = null): bool
{
    if ($tableId < 1) {
        return false;
    }
    if ($currentUser === null) {
        $currentUser = get_current_user_data($pdo);
    }
    $tablePerm = 'view_table_' . $tableId;

    if ($currentUser !== null) {
        return has_permission($pdo, $tablePerm);
    }

    if (!guest_has_permission($pdo, 'view_as_guest')) {
        return false;
    }
    return guest_has_permission($pdo, $tablePerm);
}

/**
 * Check if the current user has an admin role.
 */

function is_admin(PDO $pdo): bool
{
    $user = get_current_user_data($pdo);
    return $user !== null && isset($user['role']) && $user['role'] === 'admin';
}

/**
 * Check if the current user is an admin or moderator.
 */
function is_moderator(PDO $pdo): bool
{
    $user = get_current_user_data($pdo);
    return $user !== null && isset($user['role']) && is_string($user['role']) && in_array($user['role'], ['admin', 'moderator'], true);
}

/**
 * Enforce a minimum permission requirement.
 * 
 * @return array<string, mixed>
 */

function require_permission(PDO $pdo, string $permissionKey, ?string $description = null): array
{
    if (!isset($_SESSION['user_id'])) {
        $base = defined('BASE_PATH') && is_string(BASE_PATH) ? rtrim(BASE_PATH, '/') : '';
        header('Location: ' . $base . '/login');
        exit;
    }

    if (!has_permission($pdo, $permissionKey, $description)) {
        require_once __DIR__ . '/../public/403.php';
        exit;
    }

    $user = get_current_user_data($pdo);
    if ($user === null) {
        $base = defined('BASE_PATH') && is_string(BASE_PATH) ? rtrim(BASE_PATH, '/') : '';
        header('Location: ' . $base . '/login');
        exit;
    }


        if (!empty($user['is_new_user']) && !request_is_onboarding_route()) {
        $base = defined('BASE_PATH') && is_string(BASE_PATH) ? rtrim(BASE_PATH, '/') : '';
        header('Location: ' . $base . '/user/onboarding');
        exit;
    }

    return $user;
}

/**
 * True when the current request is the onboarding wizard (MVC route).
 */
function request_is_onboarding_route(): bool
{
    $uri = isset($_SERVER['REQUEST_URI']) && is_string($_SERVER['REQUEST_URI'])
        ? $_SERVER['REQUEST_URI'] : '';
    $path = parse_url($uri, PHP_URL_PATH);
    if (!is_string($path) || $path === '') {
        return false;
    }
    // Match /user/onboarding with optional BASE_PATH prefix and trailing slash
    return (bool) preg_match('#/user/onboarding/?$#', $path)
        || str_contains($path, '/user/onboarding');
}

/**
 * Allow CSV/JSON export for a logged-in user with export_data,
 * or for an anonymous visitor when the guest role has export_data.
 *
 * @return array{id: int|string, username?: string, date_format?: string}|null
 *         User row when logged in; null when guest is allowed.
 */

function require_export_access(PDO $pdo): ?array
{
    if (isset($_SESSION['user_id'])) {
        return require_permission(
            $pdo,
            'export_data',
            'Export database records and search result sets to CSV'
        );
    }

    if (!guest_has_permission($pdo, 'export_data')) {
        $base = defined('BASE_PATH') && is_string(BASE_PATH) ? rtrim(BASE_PATH, '/') : '';
        header('Location: ' . $base . '/login');
        exit;
    }

    return null;
}

/**
 * Allow suggest-edit for a logged-in user with access_suggest_edit,
 * or for a guest when the guest role has access_suggest_edit.
 *
 * @return array{id: int|string, username?: string, date_format?: string}|null
 */
function require_suggest_edit_access(PDO $pdo): ?array
{
    if (isset($_SESSION['user_id'])) {
        return require_permission(
            $pdo,
            'access_suggest_edit',
            'Allows submitting edit suggestions for records'
        );
    }

    if (!guest_has_permission($pdo, 'access_suggest_edit')) {
        $base = defined('BASE_PATH') && is_string(BASE_PATH) ? rtrim(BASE_PATH, '/') : '';
        header('Location: ' . $base . '/login');
        exit;
    }

    return null;
}

/**
 * Whether the current visitor may use suggest-edit (for UI buttons).
 */
function can_suggest_edit(PDO $pdo): bool
{
    if (!is_module_enabled($pdo, 'moderation')) {
        return false;
    }
    if (isset($_SESSION['user_id'])) {
        return has_permission($pdo, 'access_suggest_edit');
    }
    return guest_has_permission($pdo, 'access_suggest_edit');
}

/**
 * Legacy support wrapper: Enforce minimum role or permissions.
 * 
 * @param string|array<int, string> $allowedRoles
 * @return array<string, mixed>
 */
function require_role(PDO $pdo, $allowedRoles): array
{
    if (!isset($_SESSION['user_id'])) {
        $base = defined('BASE_PATH') && is_string(BASE_PATH) ? rtrim(BASE_PATH, '/') : '';
        header('Location: ' . $base . '/login');
        exit;
    }
    
    $user = get_current_user_data($pdo);
    $userRole = ($user !== null && isset($user['role']) && is_string($user['role'])) ? $user['role'] : '';
    $allowedArray = is_array($allowedRoles) ? $allowedRoles : [$allowedRoles];

    if ($user === null || !in_array($userRole, $allowedArray, true)) {
        require_once __DIR__ . '/../public/403.php';
        exit;
    }

        if (!empty($user['is_new_user']) && !request_is_onboarding_route()) {
        $base = defined('BASE_PATH') && is_string(BASE_PATH) ? rtrim(BASE_PATH, '/') : '';
        header('Location: ' . $base . '/user/onboarding');
        exit;
    }

    return $user;
}

/**
 * Get the application/system name dynamically from the database.
 */
function get_system_name(PDO $pdo): string
{
    try {
        $stmt = $pdo->prepare("SELECT setting_value FROM site_settings WHERE setting_key = 'system_name'");
        $stmt->execute();
        $name = $stmt->fetchColumn();
        if (is_string($name) && $name !== '') {
            return $name;
        }
    } catch (\Exception $e) {
    }
    return "Parish Records Directory (pRD)";
}

if (!function_exists('generate_csrf_token')) {
    function generate_csrf_token(): string
    {
        if (session_status() === PHP_SESSION_NONE) {
            session_start();
        }
        if (empty($_SESSION['csrf_token']) || !is_string($_SESSION['csrf_token'])) {
            $_SESSION['csrf_token'] = bin2hex(random_bytes(32));
        }
        return $_SESSION['csrf_token'];
    }
}

if (!function_exists('csrf_field')) {
    function csrf_field(): string
    {
        $token = generate_csrf_token();
        return '<input type="hidden" name="csrf_token" value="' . htmlspecialchars($token, ENT_QUOTES, 'UTF-8') . '">';
    }
}

if (!function_exists('verify_csrf_token')) {
    function verify_csrf_token(): void
    {
        if (session_status() === PHP_SESSION_NONE) {
            session_start();
        }
        $submittedToken = isset($_POST['csrf_token']) && is_string($_POST['csrf_token']) ? $_POST['csrf_token'] : '';
        $sessionToken = isset($_SESSION['csrf_token']) && is_string($_SESSION['csrf_token']) ? $_SESSION['csrf_token'] : '';

        if ($sessionToken === '' || $submittedToken === '' || !hash_equals($sessionToken, $submittedToken)) {
            http_response_code(403);
            $remoteAddr = isset($_SERVER['REMOTE_ADDR']) && is_string($_SERVER['REMOTE_ADDR']) ? $_SERVER['REMOTE_ADDR'] : 'Unknown';
            error_log("CSRF token validation failed from IP: " . $remoteAddr);
            exit('Security Error: Invalid or missing CSRF token.');
        }
    }
}

if (!function_exists('initialize_action')) {
    /**
     * @param string|array<int, string> $allowedRoles
     * @return array<string, mixed>|null
     */
    function initialize_action(PDO $pdo, $allowedRoles = ['user', 'moderator', 'admin'], ?string $requiredMethod = 'POST'): ?array
    {
        if (session_status() === PHP_SESSION_NONE) {
            session_start();
        }
        $serverMethod = isset($_SERVER['REQUEST_METHOD']) && is_string($_SERVER['REQUEST_METHOD']) ? $_SERVER['REQUEST_METHOD'] : 'GET';
        
        if ($requiredMethod !== null && $serverMethod !== $requiredMethod) {
            http_response_code(405);
            exit('Method Not Allowed');
        }
        if ($requiredMethod === 'POST') {
            verify_csrf_token();
        }
        require_role($pdo, $allowedRoles);
        return get_current_user_data($pdo);
    }
}

/**
 * Standard bootstrap for admin pages.
 * Handles session, permission check, flash messages, and returns the current user.
 * 
 * @return array<string, mixed>
 */
function require_admin_page(PDO $pdo, string $permissionKey, ?string $description = null): array
{
    if (session_status() === PHP_SESSION_NONE) {
        session_start();
    }

    $currentUser = require_permission($pdo, $permissionKey, $description);

    $GLOBALS['message'] = isset($_SESSION['message']) && is_string($_SESSION['message']) ? $_SESSION['message'] : '';
    $GLOBALS['error']   = isset($_SESSION['error']) && is_string($_SESSION['error']) ? $_SESSION['error'] : '';
    unset($_SESSION['message'], $_SESSION['error']);

    return $currentUser;
}
