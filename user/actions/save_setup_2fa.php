<?php
// user/actions/save_setup_2fa.php - Handles 2FA verification and activation logic
require_once '../../db/db.php';
require_once '../../db/auth_helpers.php';
require_once '../../db/2fa_helpers.php';

$current_user = initialize_action($pdo, ['user', 'moderator', 'admin'], 'POST');
$user_id = $current_user['id'];
$secret = $_SESSION['temp_2fa_secret'] ?? '';

$entered_code = trim($_POST['code'] ?? '');

if (!empty($secret) && verify_google_2fa($secret, $entered_code)) {
    $hashed_codes_json = $_SESSION['temp_hashed_backup_codes'] ?? '[]';

    $upd = $pdo->prepare("UPDATE users SET google_2fa_secret = ?, backup_codes = ?, two_fa_enabled = 1 WHERE id = ?");
    if ($upd->execute([$secret, $hashed_codes_json, $user_id])) {
        unset($_SESSION['temp_2fa_secret'], $_SESSION['temp_raw_backup_codes'], $_SESSION['temp_hashed_backup_codes']);
        $_SESSION['message'] = "Two-Factor Authentication successfully enabled!";
        header('Location: ../profile.php');
        exit;
    } else {
        http_response_code(403);
        error_log("Database error saving 2FA setup for user ID: {$user_id} from IP: " . $_SERVER['REMOTE_ADDR']);
        $_SESSION['error'] = "Failed to activate 2FA in the database.";
    }
} else {
    http_response_code(403);
    error_log("Failed 2FA activation confirmation attempt for user ID: {$user_id} from IP: " . $_SERVER['REMOTE_ADDR']);
    $_SESSION['error'] = "Invalid 2FA code. Please ensure your authenticator app is synced and try again.";
}

header('Location: ../setup_2fa.php');
exit;
