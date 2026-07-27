<?php
// admin/actions/save_user_management.php - Handles admin user management actions (2FA reset, suspend, reactivate, points override)
require_once '../../db/db.php';
require_once '../../db/auth_helpers.php';
session_start();

// Enforce strict administrator privileges via central helper
$current_user = require_role($pdo, 'admin');

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $action = $_POST['action'] ?? '';
    $target_user_id = intval($_POST['target_user_id'] ?? 0);

    if ($target_user_id === intval($current_user['id']) && in_array($action, ['suspend', 'reset_2fa'])) {
        $_SESSION['error'] = "You cannot perform this administrative action on your own active account.";
    } elseif ($target_user_id > 0) {
        if ($action === 'reset_2fa') {
            $reset_stmt = $pdo->prepare("UPDATE users SET two_fa_enabled = 0, google_2fa_secret = NULL WHERE id = ?");
            if ($reset_stmt->execute([$target_user_id])) {
                $_SESSION['message'] = "Successfully reset 2FA for user ID {$target_user_id}.";
                
                $audit = $pdo->prepare("INSERT INTO audit_logs (user_id, action, details, ip_address) VALUES (?, 'ADMIN_RESET_2FA', ?, ?)");
                $audit->execute([$current_user['id'], "Reset 2FA for user ID: {$target_user_id}", $_SERVER['REMOTE_ADDR']]);
            } else {
                $_SESSION['error'] = "Failed to reset 2FA.";
            }
        } elseif ($action === 'override_points') {
            $new_points = max(0, intval($_POST['new_points'] ?? 0));
            $pts_stmt = $pdo->prepare("UPDATE users SET points = ? WHERE id = ?");
            if ($pts_stmt->execute([$new_points, $target_user_id])) {
                $_SESSION['message'] = "Leaderboard score for user ID {$target_user_id} updated to {$new_points} points.";
                
                $audit = $pdo->prepare("INSERT INTO audit_logs (user_id, action, details, ip_address) VALUES (?, 'ADMIN_OVERRIDE_POINTS', ?, ?)");
                $audit->execute([$current_user['id'], "Overrode leaderboard score to {$new_points} for user ID: {$target_user_id}", $_SERVER['REMOTE_ADDR']]);
            } else {
                $_SESSION['error'] = "Failed to update leaderboard score.";
            }
        } elseif ($action === 'suspend') {
            $sus_stmt = $pdo->prepare("UPDATE users SET is_active = 0 WHERE id = ?");
            if ($sus_stmt->execute([$target_user_id])) {
                $_SESSION['message'] = "User ID {$target_user_id} has been suspended.";
                
                $audit = $pdo->prepare("INSERT INTO audit_logs (user_id, action, details, ip_address) VALUES (?, 'ADMIN_SUSPEND_USER', ?, ?)");
                $audit->execute([$current_user['id'], "Suspended user ID: {$target_user_id}", $_SERVER['REMOTE_ADDR']]);
            } else {
                $_SESSION['error'] = "Failed to suspend user account.";
            }
        } elseif ($action === 'unsuspend') {
            $unsus_stmt = $pdo->prepare("UPDATE users SET is_active = 1 WHERE id = ?");
            if ($unsus_stmt->execute([$target_user_id])) {
                $_SESSION['message'] = "User ID {$target_user_id} has been reactivated.";
                
                $audit = $pdo->prepare("INSERT INTO audit_logs (user_id, action, details, ip_address) VALUES (?, 'ADMIN_REACTIVATE_USER', ?, ?)");
                $audit->execute([$current_user['id'], "Reactivated user ID: {$target_user_id}", $_SERVER['REMOTE_ADDR']]);
            } else {
                $_SESSION['error'] = "Failed to reactivate user account.";
            }
        }
    }
}

header('Location: ../users.php');
exit;
