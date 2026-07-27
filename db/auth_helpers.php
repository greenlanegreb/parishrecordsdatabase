<?php
// db/auth_helpers.php - Centralized Role and Permission Management

if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

/**
 * Fetch current user data from the database if logged in.
 */
function get_current_user_data($pdo) {
    if (!isset($_SESSION['user_id'])) {
        return null;
    }
    
    $stmt = $pdo->prepare("SELECT id, username, first_name, surname, email, role, points, two_fa_enabled, email_verified, leaderboard_display_mode, timezone, date_format, time_format, is_new_user FROM users WHERE id = ?");
    $stmt->execute([$_SESSION['user_id']]);
    return $stmt->fetch(PDO::FETCH_ASSOC);
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
 * Enforce a minimum role requirement or display the custom 403 forbidden screen.
 */
function require_role($pdo, $allowed_roles) {
    if (!isset($_SESSION['user_id'])) {
        header('Location: ' . BASE_PATH . '/user/login.php');
        exit;
    }

    $user = get_current_user_data($pdo);
    if (!$user || !in_array($user['role'], (array)$allowed_roles)) {
        // Include and trigger the custom 403 error view
        require_once __DIR__ . '/../403.php';
        exit;
    }
    
    // Check if user is new and needs to complete onboarding (exclude onboarding scripts to prevent redirect loops)
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
    } catch (Exception $e) {
        // Fallback if table is missing or query fails
    }
    return "Parish Records Directory (PRD)";
}

/**
 * Generate or retrieve the active session CSRF token.
 */
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

/**
 * Output a hidden input field containing the CSRF token.
 */
if (!function_exists('csrf_field')) {
    function csrf_field() {
        $token = generate_csrf_token();
        return '<input type="hidden" name="csrf_token" value="' . htmlspecialchars($token, ENT_QUOTES, 'UTF-8') . '">';
    }
}

/**
 * Validate an incoming CSRF token against the session.
 */
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

        // Automatically verify CSRF tokens for all state-changing POST requests
        if ($required_method === 'POST') {
            verify_csrf_token();
        }

        require_role($pdo, $allowed_roles);
        return get_current_user_data($pdo);
    }
}
