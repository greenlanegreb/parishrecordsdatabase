<?php
// user/actions/save_profile.php - Handles profile security settings and updates
require_once '../../db/db.php';
require_once '../../db/auth_helpers.php';
require_once '../../db/mail_helper.php';

// Enforce dynamic permission check replacing hardcoded roles (automatically registers 'access_profile' if new)
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}
if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    http_response_code(405);
    exit('Method Not Allowed');
}
verify_csrf_token();
$current_user = require_permission($pdo, 'access_profile', 'Allows viewing and managing personal user profile and security settings');
$user_id = $current_user['id'];
$action = $_POST['action'] ?? '';

// 0. Handle Personal Details Update Request
if ($action === 'update_personal_details') {
    $first_name = trim($_POST['first_name'] ?? '');
    $surname = trim($_POST['surname'] ?? '');
    $display_mode = trim($_POST['leaderboard_display_mode'] ?? 'initials_random');
    $timezone = trim($_POST['timezone'] ?? 'UTC');
    $date_format = trim($_POST['date_format'] ?? 'd/m/Y');
    $time_format = trim($_POST['time_format'] ?? '24');

    // Validate that allowed display mode value is safe and permitted
    $allowed_modes = ['full_name', 'volunteers_only', 'initials_random'];
    if (!in_array($display_mode, $allowed_modes)) {
        $display_mode = 'initials_random';
    }

    // Validate that the timezone identifier is real/supported by PHP
    $valid_timezones = timezone_identifiers_list();
    if (!in_array($timezone, $valid_timezones)) {
        $timezone = 'UTC';
    }

    // Validate that the date format string matches permitted safe options
    $allowed_date_formats = ['d/m/Y', 'd/m/y', 'd.m.Y', 'm/d/Y', 'l j F Y'];
    if (!in_array($date_format, $allowed_date_formats)) {
        $date_format = 'd/m/Y';
    }

    // Validate that the time format string matches permitted safe options
    $allowed_time_formats = ['12', '24', 'none'];
    if (!in_array($time_format, $allowed_time_formats)) {
        $time_format = '24';
    }

    $upd_name = $pdo->prepare("UPDATE users SET first_name = ?, surname = ?, leaderboard_display_mode = ?, timezone = ?, date_format = ?, time_format = ? WHERE id = ?");
    if ($upd_name->execute([$first_name, $surname, $display_mode, $timezone, $date_format, $time_format, $user_id])) {
        $_SESSION['message'] = "Personal details, timezone, and format settings updated successfully!";
    } else {
        http_response_code(403);
        error_log("Database error during personal details update for user ID: {$user_id} from IP: " . $_SERVER['REMOTE_ADDR']);
        $_SESSION['error'] = "Failed to update personal details.";
    }
}
// 1. Handle Email Update Request
elseif ($action === 'update_email') {
    $new_email = trim($_POST['email'] ?? '');
    if (empty($new_email) || !filter_var($new_email, FILTER_VALIDATE_EMAIL)) {
        http_response_code(403);
        error_log("Failed email update attempt (invalid format) for user ID: {$user_id} from IP: " . $_SERVER['REMOTE_ADDR']);
        $_SESSION['error'] = "Please provide a valid email address.";
    } elseif ($new_email === $current_user['email']) {
        $_SESSION['error'] = "The new email address matches your current email.";
    } else {
        $chk = $pdo->prepare("SELECT id FROM users WHERE email = ? AND id != ?");
        $chk->execute([$new_email, $user_id]);
        if ($chk->fetch()) {
            http_response_code(403);
            error_log("Failed email update attempt (email already registered) for user ID: {$user_id} from IP: " . $_SERVER['REMOTE_ADDR']);
            $_SESSION['error'] = "That email address is already registered to another account.";
        } else {
            $token = bin2hex(random_bytes(32));
            $expires = date('Y-m-d H:i:s', strtotime('+24 hours'));
            $upd = $pdo->prepare("UPDATE users SET email = ?, email_verified = 0, verification_token = ?, token_expires_at = ? WHERE id = ?");
            if ($upd->execute([$new_email, $token, $expires, $user_id])) {
                if (send_user_invitation($pdo, $new_email, $token)) {
                    $_SESSION['message'] = "Email updated successfully! A verification link has been sent to your new address.";
                } else {
                    http_response_code(403);
                    error_log("Database email update succeeded but mail dispatch failed for user ID: {$user_id} from IP: " . $_SERVER['REMOTE_ADDR']);
                    $_SESSION['error'] = "Email updated in database, but failed to dispatch the verification message.";
                }
            } else {
                http_response_code(403);
                error_log("Database error during email update for user ID: {$user_id} from IP: " . $_SERVER['REMOTE_ADDR']);
                $_SESSION['error'] = "Failed to update email address.";
            }
        }
    }
} 
// 2. Handle Password Update Request
elseif ($action === 'update_password') {
    $current_password = $_POST['current_password'] ?? '';
    $new_password = $_POST['new_password'] ?? '';
    $confirm_password = $_POST['confirm_password'] ?? '';

    if (empty($current_password) || empty($new_password) || empty($confirm_password)) {
        $_SESSION['error'] = "All password fields are required.";
    } elseif (!password_verify($current_password, $current_user['password_hash'])) {
        http_response_code(403);
        error_log("Failed password change attempt (incorrect current password) for user ID: {$user_id} from IP: " . $_SERVER['REMOTE_ADDR']);
        $_SESSION['error'] = "Your current password was incorrect.";
    } elseif ($new_password !== $confirm_password) {
        $_SESSION['error'] = "The new passwords do not match.";
    } elseif (strlen($new_password) < 8) {
        $_SESSION['error'] = "New password must be at least 8 characters long.";
    } else {
        $new_hash = password_hash($new_password, PASSWORD_DEFAULT);
        $upd_pwd = $pdo->prepare("UPDATE users SET password_hash = ? WHERE id = ?");
        if ($upd_pwd->execute([$new_hash, $user_id])) {
            $_SESSION['message'] = "Password updated successfully!";
        } else {
            http_response_code(403);
            error_log("Database error during password update for user ID: {$user_id} from IP: " . $_SERVER['REMOTE_ADDR']);
            $_SESSION['error'] = "Failed to update password.";
        }
    }
} 
// 3. Handle Generate New Backup Codes Request
elseif ($action === 'generate_backup_codes') {
    if (!$current_user['two_fa_enabled']) {
        $_SESSION['error'] = "You must enable 2FA before generating backup codes.";
    } else {
        $raw_codes = [];
        $hashed_codes = [];
        for ($i = 0; $i < 5; $i++) {
            $code = strtoupper(bin2hex(random_bytes(3)));
            $formatted_code = substr($code, 0, 3) . '-' . substr($code, 3, 3);
            $raw_codes[] = $formatted_code;
            $hashed_codes[] = password_hash($formatted_code, PASSWORD_DEFAULT);
        }
        
        $hashed_codes_json = json_encode($hashed_codes);
        $upd_codes = $pdo->prepare("UPDATE users SET backup_codes = ? WHERE id = ?");
        if ($upd_codes->execute([$hashed_codes_json, $user_id])) {
            $_SESSION['new_raw_backup_codes'] = $raw_codes;
            $_SESSION['message'] = "New backup codes generated successfully! Please download and save them immediately.";
        } else {
            http_response_code(403);
            error_log("Database error generating backup codes for user ID: {$user_id} from IP: " . $_SERVER['REMOTE_ADDR']);
            $_SESSION['error'] = "Failed to generate new backup codes.";
        }
    }
} 
// 4. Handle 2FA Setup Initiation
elseif ($action === 'setup_2fa') {
    header('Location: ../setup_2fa.php');
    exit;
}

header('Location: ../profile.php');
exit;
