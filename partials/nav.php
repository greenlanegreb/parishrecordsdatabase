<?php
// partials/nav.php - Centralized header navigation, permissions, and system name
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

// Include centralized authentication helpers if available (which also ensures db.php/BASE_PATH is loaded)
$auth_helper_path = __DIR__ . '/../db/auth_helpers.php';
if (file_exists($auth_helper_path)) {
    require_once $auth_helper_path;
}

// Handle contrast toggle request via query string
if (isset($_GET['contrast']) && $_GET['contrast'] === 'toggle') {
    $_SESSION['high_contrast'] = !($_SESSION['high_contrast'] ?? false);
    $redirect_url = strtok($_SERVER['REQUEST_URI'], '?');
    header('Location: ' . $redirect_url);
    exit;
}

$is_high_contrast = $_SESSION['high_contrast'] ?? false;

// Use centralized helper functions if defined
$current_user = (function_exists('get_current_user_data') && isset($pdo)) ? get_current_user_data($pdo) : null;
$is_logged_in = ($current_user !== null || isset($_SESSION['user_id']));

// Dynamic capability evaluations using the central permission framework
$can_moderate = false;
if (isset($pdo)) {
    if (is_admin($pdo) || has_permission($pdo, 'moderate_table_1')) {
        $can_moderate = true;
    } else {
        $tables_chk = $pdo->query("SELECT id FROM dynamic_tables")->fetchAll(PDO::FETCH_COLUMN);
        foreach ($tables_chk as $t_id) {
            if (has_permission($pdo, 'moderate_table_' . $t_id)) {
                $can_moderate = true;
                break;
            }
        }
    }
}

$can_invite       = isset($pdo) && has_permission($pdo, 'invite_users', 'Create and invite new users to the platform');
$can_manage_users = isset($pdo) && has_permission($pdo, 'manage_users', 'Manage user accounts, roles, and status');
$can_manage_cols  = isset($pdo) && has_permission($pdo, 'manage_columns', 'Configure database table columns and types');
$can_manage_vols  = isset($pdo) && has_permission($pdo, 'manage_volunteers', 'Manage and review volunteer applications and submissions');
$can_manage_feed  = isset($pdo) && has_permission($pdo, 'manage_feedback', 'Review and manage user feedback submissions');
$can_manage_sets  = isset($pdo) && has_permission($pdo, 'manage_settings', 'Manage global site settings, mail drivers, and maintenance mode');

// Use dynamic BASE_PATH constant defined in db.php
$base_url = defined('BASE_PATH') ? BASE_PATH : '';
$current_script = basename($_SERVER['SCRIPT_NAME']);

// Dynamic system name retrieved from site_settings table
$system_name = (function_exists('get_system_name') && isset($pdo)) ? get_system_name($pdo) : "Parish Records Directory (PRD)";
$display_username = $current_user['username'] ?? ($_SESSION['username'] ?? 'User');
?>

<!-- Flush Top-Left High Contrast Button -->
<div class="contrast-toggle-wrapper">
    <a href="?contrast=toggle" class="btn contrast-toggle-btn" aria-label="Toggle High Contrast Mode">
        <?php echo $is_high_contrast ? 'Low Contrast' : 'High Contrast'; ?>
    </a>
</div>

<!-- Header Row with Increased Bottom Margin for Vertical Spacing -->
<div class="header-bar header-bar-flex" role="banner">
    <h1>
        <?php echo htmlspecialchars($system_name); ?>
    </h1>

    <!-- Top-Right Menu Line with Linked First Name, Star Score, Leaderboard Link & Log Out -->
    <?php if ($is_logged_in): ?>
        <?php 
            $display_identifier = !empty($current_user['first_name']) ? $current_user['first_name'] : $display_username;
            $user_points = $current_user['points'] ?? 0;
        ?>
        <div class="top-right-account-menu" aria-label="User Account Menu" style="display: flex; align-items: center; gap: 1rem;">
            <span class="top-right-welcome">
                Welcome, <a href="<?php echo $base_url; ?>/user/profile.php" style="color: inherit; <?php echo ($current_script === 'profile.php') ? 'font-weight: bold; text-decoration: underline;' : ''; ?>" aria-label="Go to User Profile"><?php echo htmlspecialchars($display_identifier); ?></a>
                <span class="gamification-badge" style="margin-left: 0.75rem; font-weight: bold;" title="View Leaderboard">
                    <a href="<?php echo $base_url; ?>/leaderboard.php" style="text-decoration: none; color: inherit;" aria-label="Leaderboard Score">
                        ⭐ <span style="text-decoration: underline;"><?php echo intval($user_points); ?></span>
                    </a>
                </span>
            </span>
            <a href="<?php echo $base_url; ?>/user/logout.php" class="btn btn-danger btn-discreet" aria-label="Log Out">Log Out</a>
        </div>
    <?php endif; ?>
</div>

<!-- Main Navigation Bar -->
<nav class="nav-menu-container nav-menu-flex" aria-label="Main Navigation">
    <?php if ($is_logged_in || ($current_script !== 'index.php')): ?>
        <a href="<?php echo $base_url; ?>/index.php" class="btn btn-secondary <?php echo ($current_script === 'index.php') ? 'btn-active' : ''; ?>">Search</a>
    <?php endif; ?>

    <!-- Volunteer & Feedback Order for Logged-Out Visitors -->
    <?php if (!$is_logged_in): ?>
        <a href="<?php echo $base_url; ?>/volunteer.php" class="btn btn-secondary <?php echo ($current_script === 'volunteer.php') ? 'btn-active' : ''; ?>">Volunteer</a>
        <a href="<?php echo $base_url; ?>/feedback.php" class="btn btn-secondary <?php echo ($current_script === 'feedback.php') ? 'btn-active' : ''; ?>">Feedback</a>
    <?php endif; ?>

    <?php if ($is_logged_in): ?>
        <a href="<?php echo $base_url; ?>/user/data_entry.php" class="btn <?php echo ($current_script === 'data_entry.php') ? 'btn-active' : ''; ?>">Data Entry</a>

        <?php if ($can_moderate): ?>
            <a href="<?php echo $base_url; ?>/admin/moderate.php" class="btn btn-secondary <?php echo ($current_script === 'moderate.php') ? 'btn-active' : ''; ?>">Moderation</a>
        <?php endif; ?>

        <?php if ($can_invite): ?>
            <a href="<?php echo $base_url; ?>/admin/create_user.php" class="btn btn-success <?php echo ($current_script === 'create_user.php') ? 'btn-active' : ''; ?>">Invite User</a>
        <?php endif; ?>

        <?php if ($can_manage_users): ?>
            <a href="<?php echo $base_url; ?>/admin/users.php" class="btn btn-secondary <?php echo ($current_script === 'users.php') ? 'btn-active' : ''; ?>">Manage Users</a>
        <?php endif; ?>

        <?php if ($can_manage_cols): ?>
            <a href="<?php echo $base_url; ?>/admin/manage_tables.php" class="btn btn-secondary <?php echo ($current_script === 'manage_tables.php') ? 'btn-active' : ''; ?>">Manage Tables</a>
        <?php endif; ?>

        <?php if ($can_manage_vols): ?>
            <a href="<?php echo $base_url; ?>/admin/volunteer_dashboard.php" class="btn btn-secondary <?php echo ($current_script === 'volunteer_dashboard.php') ? 'btn-active' : ''; ?>">Volunteer Dashboard</a>
        <?php endif; ?>

        <?php if ($can_manage_feed): ?>
            <a href="<?php echo $base_url; ?>/admin/feedback_dashboard.php" class="btn btn-secondary <?php echo ($current_script === 'feedback_dashboard.php') ? 'btn-active' : ''; ?>">Feedback Dashboard</a>
        <?php endif; ?>

        <?php if ($can_manage_sets): ?>
            <a href="<?php echo $base_url; ?>/admin/settings.php" class="btn btn-secondary <?php echo ($current_script === 'settings.php') ? 'btn-active' : ''; ?>">Site Settings</a>
        <?php endif; ?>
    <?php endif; ?>

    <!-- Login Button for Logged-Out Users -->
    <?php if (!$is_logged_in): ?>
        <a href="<?php echo $base_url; ?>/user/login.php" class="btn <?php echo ($current_script === 'login.php') ? 'btn-active' : ''; ?> nav-push-right">Login</a>
    <?php endif; ?>

    <!-- Feedback Button for Logged-In Users / Admins pushed to the far right end -->
    <?php if ($is_logged_in): ?>
        <a href="<?php echo $base_url; ?>/feedback.php" class="btn btn-secondary <?php echo ($current_script === 'feedback.php') ? 'btn-active' : ''; ?> nav-push-right">Feedback</a>
    <?php endif; ?>
</nav>

<hr style="border: 0.0625rem solid var(--border-color); margin-top: 1rem; margin-bottom: 1.5rem;">
