<?php
// db/auth_helpers.php - Centralized Role, Permission, and Session Management
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

/**
 * Fetch current user data from the database along with their dynamic role details.
 */
function get_current_user_data($pdo) {
    if (!isset($_SESSION['user_id'])) {
        return null;
    }
   
    $stmt = $pdo->prepare("
        SELECT u.id, u.username, u.first_name, u.surname, u.email, u.role_id, r.role_name as role, u.points, u.two_fa_enabled, u.email_verified, u.leaderboard_display_mode, u.timezone, u.date_format, u.time_format, u.is_new_user
        FROM users u
        LEFT JOIN roles r ON u.role_id = r.id
        WHERE u.id = ?
    ");
    $stmt->execute([$_SESSION['user_id']]);
    return $stmt->fetch(PDO::FETCH_ASSOC);
}

/**
 * Check if the current user possesses a specific dynamic permission key,
 * and automatically register the permission in the database if it's new.
 */
function has_permission($pdo, $permission_key, $description = null) {
    // 1. Auto-register permission in the database so it appears in the admin matrix automatically
    try {
        static $registered_cache = [];
        if (!isset($registered_cache[$permission_key])) {
            $stmt = $pdo->prepare("INSERT INTO permissions (permission_key, description) VALUES (?, ?) ON DUPLICATE KEY UPDATE description = COALESCE(VALUES(description), description)");
            $stmt->execute([$permission_key, $description ?? ucwords(str_replace('_', ' ', $permission_key))]);
            $registered_cache[$permission_key] = true;
        }
    } catch (Exception $e) {
        // Fail silently if DB lacks table during early migrations
    }
    $user = get_current_user_data($pdo);
    $role_id = ($user && !empty($user['role_id'])) ? $user['role_id'] : 4; // Fallback to guest (Role ID 4)
    static $permission_cache = [];
    $cache_key = $role_id . '_' . $permission_key;
    if (isset($permission_cache[$cache_key])) {
        return $permission_cache[$cache_key];
    }
    $stmt = $pdo->prepare("
        SELECT COUNT(*) FROM role_permissions rp
        JOIN permissions p ON rp.permission_id = p.id
        WHERE rp.role_id = ? AND p.permission_key = ?
    ");
    $stmt->execute([$role_id, $permission_key]);
    $has = ($stmt->fetchColumn() > 0);
   
    $permission_cache[$cache_key] = $has;
    return $has;
}

/**
 * Does the guest role currently hold the given permission?
 * Used by public-facing pages so they don't each reinvent the query.
 */
function guest_has_permission($pdo, $permission_key) {
    static $cache = [];
    if (array_key_exists($permission_key, $cache)) {
        return $cache[$permission_key];
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
        $stmt->execute([$permission_key]);
        $cache[$permission_key] = ((int)$stmt->fetchColumn() > 0);
    } catch (Exception $e) {
        $cache[$permission_key] = false;
    }

    return $cache[$permission_key];
}

/**
 * Check if the current user has an admin role.
 */
function is_admin($pdo) {
    $user = get_current_user_data($pdo);
    return $user && $user['role'] === 'admin';
}

/**
 * Check if the current user is an admin or moderator.
 */
function is_moderator($pdo) {
    $user = get_current_user_data($pdo);
    return $user && in_array($user['role'], ['admin', 'moderator']);
}

/**
 * Enforce a minimum permission requirement, auto-registering it and checking access.
 */
function require_permission($pdo, $permission_key, $description = null) {
    if (!isset($_SESSION['user_id'])) {
        header('Location: ' . BASE_PATH . '/user/login.php');
        exit;
    }
   
    if (!has_permission($pdo, $permission_key, $description)) {
        require_once __DIR__ . '/../403.php';
        exit;
    }
   
    $user = get_current_user_data($pdo);
    $current_script = basename($_SERVER['PHP_SELF']);
    if (!empty($user['is_new_user']) && $current_script !== 'onboarding.php' && $current_script !== 'save_onboarding.php') {
        header('Location: ' . BASE_PATH . '/user/onboarding.php');
        exit;
    }
   
    return $user;
}

/**
 * Legacy support wrapper: Enforce minimum role or permissions.
 */
function require_role($pdo, $allowed_roles) {
    if (!isset($_SESSION['user_id'])) {
        header('Location: ' . BASE_PATH . '/user/login.php');
        exit;
    }
    $user = get_current_user_data($pdo);
    if (!$user || !in_array($user['role'], (array)$allowed_roles)) {
        require_once __DIR__ . '/../403.php';
        exit;
    }
   
    $current_script = basename($_SERVER['PHP_SELF']);
    if (!empty($user['is_new_user']) && $current_script !== 'onboarding.php' && $current_script !== 'save_onboarding.php') {
        header('Location: ' . BASE_PATH . '/user/onboarding.php');
        exit;
    }
   
    return $user;
}

/**
 * Get the application/system name dynamically from the database.
 */
function get_system_name($pdo) {
    try {
        $stmt = $pdo->prepare("SELECT setting_value FROM site_settings WHERE setting_key = 'system_name'");
        $stmt->execute();
        $name = $stmt->fetchColumn();
        if ($name) {
            return $name;
        }
    } catch (Exception $e) {}
    return "Parish Records Directory (PRD)";
}

if (!function_exists('generate_csrf_token')) {
    function generate_csrf_token() {
        if (session_status() === PHP_SESSION_NONE) {
            session_start();
        }
        if (empty($_SESSION['csrf_token'])) {
            $_SESSION['csrf_token'] = bin2hex(random_bytes(32));
        }
        return $_SESSION['csrf_token'];
    }
}

if (!function_exists('csrf_field')) {
    function csrf_field() {
        $token = generate_csrf_token();
        return '<input type="hidden" name="csrf_token" value="' . htmlspecialchars($token, ENT_QUOTES, 'UTF-8') . '">';
    }
}

if (!function_exists('verify_csrf_token')) {
    function verify_csrf_token() {
        if (session_status() === PHP_SESSION_NONE) {
            session_start();
        }
        $submitted_token = $_POST['csrf_token'] ?? '';
        if (empty($_SESSION['csrf_token']) || empty($submitted_token) || !hash_equals($_SESSION['csrf_token'], $submitted_token)) {
            http_response_code(403);
            error_log("CSRF token validation failed from IP: " . ($_SERVER['REMOTE_ADDR'] ?? 'Unknown'));
            exit('Security Error: Invalid or missing CSRF token.');
        }
    }
}

if (!function_exists('initialize_action')) {
    function initialize_action($pdo, $allowed_roles = ['user', 'moderator', 'admin'], $required_method = 'POST') {
        if (session_status() === PHP_SESSION_NONE) {
            session_start();
        }
        if ($required_method && $_SERVER['REQUEST_METHOD'] !== $required_method) {
            http_response_code(405);
            exit('Method Not Allowed');
        }
        if ($required_method === 'POST') {
            verify_csrf_token();
        }
        require_role($pdo, $allowed_roles);
        return get_current_user_data($pdo);
    }
}

/**
 * Standard bootstrap for admin pages.
 * Handles session, permission check, flash messages, and returns the current user.
 */
function require_admin_page(PDO $pdo, string $permission_key, string $description = null): array
{
    if (session_status() === PHP_SESSION_NONE) {
        session_start();
    }

    $current_user = require_permission($pdo, $permission_key, $description);

    // Make flash messages available to the view
    $GLOBALS['message'] = $_SESSION['message'] ?? '';
    $GLOBALS['error']   = $_SESSION['error']   ?? '';
    unset($_SESSION['message'], $_SESSION['error']);

    return $current_user;
}
