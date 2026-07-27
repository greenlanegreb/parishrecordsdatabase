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
        header('Location: /projects/cakebread-database/user/login.php');
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
        header('Location: /projects/cakebread-database/user/onboarding.php');
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
