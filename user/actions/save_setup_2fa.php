<?php
// user/actions/save_setup_2fa.php - Handles 2FA verification and activation logic
require_once '../../db/db.php';
require_once '../../db/auth_helpers.php';
session_start();

// Enforce standard user/moderator/admin authentication via central helper
require_role($pdo, ['user', 'moderator', 'admin']);
$current_user = get_current_user_data($pdo);
$user_id = $current_user['id'];

$secret = $_SESSION['temp_2fa_secret'] ?? '';

// Helper functions for verification check
function base32_decode($input) {
    $map = 'ABCDEFGHIJKLMNOPQRSTUVWXYZ234567';
    $input = strtoupper($input);
    $val = 0; $bits = 0; $output = '';
    for ($i = 0; $i < strlen($input); $i++) {
        $char = $input[$i];
        if ($char === '=') break;
        $pos = strpos($map, $char);
        if ($pos === false) return false;
        $val = ($val << 5) | $pos;
        $bits += 5;
        if ($bits >= 8) {
            $bits -= 8;
            $output .= chr(($val >> $bits) & 0xFF);
        }
    }
    return $output;
}

function calculate_totp($key, $timeSlice) {
    $time = pack('N*', 0) . pack('N*', $timeSlice);
    $hash = hash_hmac('sha1', $time, $key, true);
    $offset = ord(substr($hash, -1)) & 0x0F;
    $code = (
        ((ord($hash[$offset]) & 0x7F) << 24) |
        ((ord($hash[$offset + 1]) & 0xFF) << 16) |
        ((ord($hash[$offset + 2]) & 0xFF) << 8) |
        (ord($hash[$offset + 3]) & 0xFF)
    ) % 1000000;
    return str_pad($code, 6, '0', STR_PAD_LEFT);
}

function verify_google_2fa($secret, $code, $discrepancy = 1) {
    $decodedSecret = base32_decode($secret);
    if ($decodedSecret === false) return false;
    $currentTime = floor(time() / 30);
    for ($i = -$discrepancy; $i <= $discrepancy; $i++) {
        $calculatedCode = calculate_totp($decodedSecret, $currentTime + $i);
        if (hash_equals($calculatedCode, str_pad($code, 6, '0', STR_PAD_LEFT))) {
            return true;
        }
    }
    return false;
}

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
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
}

header('Location: ../setup_2fa.php');
exit;
