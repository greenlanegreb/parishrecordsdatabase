<?php
// user/actions/save_verify_2fa.php - Handles 2FA login challenge verification logic
require_once '../../db/db.php';
require_once '../../db/2fa_helpers.php';
session_start();
if (!isset($_SESSION['pending_2fa_user_id'])) {
    http_response_code(403);
    error_log("Unauthorized direct access attempt to save_verify_2fa.php from IP: " . $_SERVER['REMOTE_ADDR']);
    header('Location: ../login.php');
    exit;
}
$user_id = $_SESSION['pending_2fa_user_id'];
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
        header('Location: ../data_entry.php');
        exit;
    } else {
        http_response_code(403);
        error_log("Failed 2FA verification attempt for user ID: {$user_id} from IP: " . $_SERVER['REMOTE_ADDR']);
        $_SESSION['error'] = "Invalid verification code or backup code. Please try again.";
    }
}
header('Location: ../verify_2fa.php');
exit;
