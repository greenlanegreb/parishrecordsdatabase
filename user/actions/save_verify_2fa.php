<?php
// user/actions/save_verify_2fa.php - Handles 2FA login challenge verification logic
require_once '../../db/db.php';
session_start();

if (!isset($_SESSION['pending_2fa_user_id'])) {
    http_response_code(403);
    error_log("Unauthorized direct access attempt to save_verify_2fa.php from IP: " . $_SERVER['REMOTE_ADDR']);
    header('Location: ../login.php');
    exit;
}

$user_id = $_SESSION['pending_2fa_user_id'];

// TOTP Verification Helper Functions
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
    $input_code = trim($_POST['code'] ?? '');

    $stmt = $pdo->prepare("SELECT id, username, role, google_2fa_secret, backup_codes FROM users WHERE id = ?");
    $stmt->execute([$user_id]);
    $user = $stmt->fetch();

    if (!$user) {
        http_response_code(403);
        error_log("Invalid pending 2FA user ID: {$user_id} from IP: " . $_SERVER['REMOTE_ADDR']);
        header('Location: ../login.php');
        exit;
    }

    $authenticated = false;

    // 1. Check TOTP app code
    if (verify_google_2fa($user['google_2fa_secret'], $input_code)) {
        $authenticated = true;
    } 
    // 2. Check backup recovery codes
    elseif (!empty($user['backup_codes'])) {
        $stored_hashed_codes = json_decode($user['backup_codes'], true);
        if (is_array($stored_hashed_codes)) {
            foreach ($stored_hashed_codes as $index => $hashed_code) {
                if (password_verify($input_code, $hashed_code)) {
                    $authenticated = true;
                    
                    unset($stored_hashed_codes[$index]);
                    $updated_codes_json = json_encode(array_values($stored_hashed_codes));
                    
                    $upd = $pdo->prepare("UPDATE users SET backup_codes = ? WHERE id = ?");
                    $upd->execute([$updated_codes_json, $user_id]);
                    
                    error_log("Backup recovery code used and burned for user ID: {$user_id} from IP: " . $_SERVER['REMOTE_ADDR']);
                    break;
                }
            }
        }
    }

    if ($authenticated) {
        unset($_SESSION['pending_2fa_user_id']);
        $_SESSION['user_id'] = $user['id'];
        $_SESSION['username'] = $user['username'];
        $_SESSION['role'] = $user['role'];

        $audit = $pdo->prepare("INSERT INTO audit_logs (user_id, action, details, ip_address) VALUES (?, 'LOGIN_SUCCESS_2FA', 'Completed 2FA login challenge', ?)");
        $audit->execute([$user['id'], $_SERVER['REMOTE_ADDR']]);

        header('Location: ../dashboard.php');
        exit;
    } else {
        http_response_code(403);
        error_log("Failed 2FA verification attempt for user ID: {$user_id} from IP: " . $_SERVER['REMOTE_ADDR']);
        $_SESSION['error'] = "Invalid verification code or backup code. Please try again.";
    }
}

header('Location: ../verify_2fa.php');
exit;
