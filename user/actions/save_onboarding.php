<?php
// user/actions/save_onboarding.php - Saves wizard preferences and completes onboarding
require_once '../../db/db.php';
require_once '../../db/auth_helpers.php';

// Enforce dynamic permission check replacing hardcoded roles (automatically registers 'access_onboarding' if new)
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}
if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    http_response_code(405);
    exit('Method Not Allowed');
}
verify_csrf_token();
$current_user = require_permission($pdo, 'access_onboarding', 'Allows accessing the first-time user onboarding setup wizard');
$user_id = $current_user['id'];

$first_name = trim($_POST['first_name'] ?? '');
$surname = trim($_POST['surname'] ?? '');
$display_mode = trim($_POST['leaderboard_display_mode'] ?? 'initials_random');
$timezone = trim($_POST['timezone'] ?? 'UTC');
$date_format = trim($_POST['date_format'] ?? 'd/m/Y');
$time_format = trim($_POST['time_format'] ?? '24');

// Validation checks mirroring save_profile.php
$allowed_modes = ['full_name', 'volunteers_only', 'initials_random'];
if (!in_array($display_mode, $allowed_modes)) $display_mode = 'initials_random';

$valid_timezones = timezone_identifiers_list();
if (!in_array($timezone, $valid_timezones)) $timezone = 'UTC';

$allowed_date_formats = ['d/m/Y', 'd/m/y', 'd.m.Y', 'm/d/Y', 'l j F Y'];
if (!in_array($date_format, $allowed_date_formats)) $date_format = 'd/m/Y';

$allowed_time_formats = ['12', '24', 'none'];
if (!in_array($time_format, $allowed_time_formats)) $time_format = '24';

// Update preferences AND set is_new_user to 0
$stmt = $pdo->prepare("UPDATE users SET first_name = ?, surname = ?, leaderboard_display_mode = ?, timezone = ?, date_format = ?, time_format = ?, is_new_user = 0 WHERE id = ?");
if ($stmt->execute([$first_name, $surname, $display_mode, $timezone, $date_format, $time_format, $user_id])) {
    $_SESSION['message'] = "Welcome aboard! Your preferences have been saved.";
    header('Location: ../data_entry.php');
    exit;
} else {
    $_SESSION['error'] = "Failed to save onboarding preferences. Please try again.";
    header('Location: ../onboarding.php');
    exit;
}
