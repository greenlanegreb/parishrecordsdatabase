<?php
/**
 * MIGRATED FILE MAPPING
 * ---------------------
 * Original Old File: admin/users.php/admin/actions/save_user_management.php
 * Migrated Date: 2026-08-05 03:50:05
 */declare(strict_types=1);


namespace App\Controllers;

use Exception;
use PDO;

class AdminUsersController
{
    private PDO $pdo;

    public function __construct(PDO $pdo)
    {
        $this->pdo = $pdo;
    }

    public function index(): void
    {
        if (session_status() === PHP_SESSION_NONE) {
            session_start();
        }

        // Ensure the users module is enabled; otherwise block direct access
        if (!is_module_enabled($this->pdo, 'users')) {
            http_response_code(403);
            exit('403 Forbidden: The User Management module is currently disabled.');
        }

        /** @var array{id: int, username: string, timezone?: string} $currentUser */
        $currentUser = require_admin_page($this->pdo, 'manage_users', 'Manage user accounts, roles, and status');

        $message = $_SESSION['message'] ?? '';
        $error = $_SESSION['error'] ?? '';
        unset($_SESSION['message'], $_SESSION['error']);

        // Catch pre-filled data from volunteer portal bridge if present
        $queryGet = $_GET;
        $prefillEmail = isset($queryGet['email']) && is_string($queryGet['email']) ? trim($queryGet['email']) : '';
        $prefillFirst = isset($queryGet['first_name']) && is_string($queryGet['first_name']) ? trim($queryGet['first_name']) : '';
        $prefillSurname = isset($queryGet['surname']) && is_string($queryGet['surname']) ? trim($queryGet['surname']) : '';
        $volunteerId = isset($queryGet['volunteer_id']) ? (int)$queryGet['volunteer_id'] : 0;

        // Determine the first admin user ID dynamically
        $firstAdminId = 1;
        try {
            $faStmt = $this->pdo->query("
                SELECT u.id FROM users u
                JOIN roles r ON u.role_id = r.id
                WHERE LOWER(r.role_name) = 'admin'
                ORDER BY u.created_at ASC, u.id ASC
                LIMIT 1
            ");
            $faId = $faStmt !== false ? $faStmt->fetchColumn() : false;
            if ($faId !== false && $faId !== null) {
                $firstAdminId = (int)$faId;
            }
        } catch (Exception $e) {
            // Fallback to 1
        }

        // Fetch users with their dynamic role names
        $usersStmt = $this->pdo->query("
            SELECT u.id, u.username, u.email, u.points, u.email_verified, u.two_fa_enabled, u.is_active, u.created_at, u.role_id, r.role_name
            FROM users u
            LEFT JOIN roles r ON u.role_id = r.id
            ORDER BY u.created_at DESC
        ");
        /** @var array<int, array<string, mixed>> $users */
        $users = $usersStmt !== false ? $usersStmt->fetchAll(PDO::FETCH_ASSOC) : [];

        // Fetch all available roles for dropdowns
        $rolesStmt = $this->pdo->query("SELECT id, role_name FROM roles ORDER BY id ASC");
        /** @var array<int, array<string, mixed>> $rolesList */
        $rolesList = $rolesStmt !== false ? $rolesStmt->fetchAll(PDO::FETCH_ASSOC) : [];

        $userTimezone = isset($currentUser['timezone']) && is_string($currentUser['timezone']) ? $currentUser['timezone'] : 'UTC';
        $fullFormatStr = get_user_datetime_format($currentUser);

        require_once __DIR__ . '/../Views/admin/users.php';
    }

    public function handleAction(): void
    {
        if (session_status() === PHP_SESSION_NONE) {
            session_start();
        }

        if (!is_module_enabled($this->pdo, 'users')) {
            http_response_code(403);
            exit('403 Forbidden: The User Management module is currently disabled.');
        }

        $serverMethod = isset($_SERVER['REQUEST_METHOD']) && is_string($_SERVER['REQUEST_METHOD']) ? $_SERVER['REQUEST_METHOD'] : 'GET';
        if ($serverMethod !== 'POST') {
            http_response_code(405);
            exit('Method Not Allowed');
        }

        verify_csrf_token();
        /** @var array{id: int, username: string} $currentUser */
        $currentUser = require_permission($this->pdo, 'manage_users', 'Manage user accounts, roles, and status');

        $post = $_POST;
        $action = isset($post['action']) && is_string($post['action']) ? $post['action'] : '';
        $targetUserId = isset($post['target_user_id']) ? (int)$post['target_user_id'] : 0;

        if ($targetUserId <= 0) {
            $_SESSION['error'] = "Invalid target user specified.";
            header('Location: /admin/users');
            exit;
        }

        // Fetch target user details for logging
        $targetStmt = $this->pdo->prepare("SELECT id, username FROM users WHERE id = ?");
        $targetStmt->execute([$targetUserId]);
        /** @array{id: int, username: string}|false $targetUser */
        $targetUser = $targetStmt->fetch(PDO::FETCH_ASSOC);

        if ($targetUser === false) {
            $_SESSION['error'] = "User not found.";
            header('Location: /admin/users');
            exit;
        }

        // Determine the first admin user ID dynamically to protect them server-side
        $firstAdminId = 1;
        try {
            $faStmt = $this->pdo->query("
                SELECT u.id FROM users u
                JOIN roles r ON u.role_id = r.id
                WHERE LOWER(r.role_name) = 'admin'
                ORDER BY u.created_at ASC, u.id ASC
                LIMIT 1
            ");
            $faId = $faStmt !== false ? $faStmt->fetchColumn() : false;
            if ($faId !== false && $faId !== null) {
                $firstAdminId = (int)$faId;
            }
        } catch (Exception $e) {
            // Fallback safely to ID 1
        }

        $isTargetFirstAdmin = ($targetUserId === $firstAdminId);
        if ($isTargetFirstAdmin && in_array($action, ['change_role', 'suspend', 'delete'], true)) {
            http_response_code(403);
            $_SESSION['error'] = "Security Error: The primary system administrator account cannot be modified or deleted.";
            header('Location: /admin/users');
            exit;
        }

        $remoteAddr = isset($_SERVER['REMOTE_ADDR']) && is_string($_SERVER['REMOTE_ADDR']) ? $_SERVER['REMOTE_ADDR'] : '127.0.0.1';

        try {
            switch ($action) {
                case 'change_role':
                    $newRoleId = isset($post['new_role_id']) ? (int)$post['new_role_id'] : 0;

                    $rChk = $this->pdo->prepare("SELECT role_name FROM roles WHERE id = ?");
                    $rChk->execute([$newRoleId]);
                    /** @array{role_name: string}|false $roleData */
                    $roleData = $rChk->fetch(PDO::FETCH_ASSOC);

                    if ($roleData === false) {
                        $_SESSION['error'] = "Selected role does not exist.";
                        break;
                    }

                    $upd = $this->pdo->prepare("UPDATE users SET role_id = ? WHERE id = ?");
                    $upd->execute([$newRoleId, $targetUserId]);

                    $_SESSION['message'] = "Role for '{$targetUser['username']}' successfully updated to '{$roleData['role_name']}'.";

                    $audit = $this->pdo->prepare("INSERT INTO audit_logs (user_id, action, details, ip_address) VALUES (?, 'CHANGE_USER_ROLE', ?, ?)");
                    $audit->execute([$currentUser['id'], "Changed role of user {$targetUser['username']} to {$roleData['role_name']}", $remoteAddr]);
                    break;

                case 'override_points':
                    $newPoints = isset($post['new_points']) ? (int)$post['new_points'] : 0;

                    $upd = $this->pdo->prepare("UPDATE users SET points = ? WHERE id = ?");
                    $upd->execute([$newPoints, $targetUserId]);

                    $_SESSION['message'] = "Score for '{$targetUser['username']}' updated to {$newPoints} points.";

                    $audit = $this->pdo->prepare("INSERT INTO audit_logs (user_id, action, details, ip_address) VALUES (?, 'OVERRIDE_POINTS', ?, ?)");
                    $audit->execute([$currentUser['id'], "Overrode score for {$targetUser['username']} to {$newPoints}", $remoteAddr]);
                    break;

                case 'update_email':
                    $newEmail = isset($post['new_email']) && is_string($post['new_email']) ? trim($post['new_email']) : '';
                    if ($newEmail === '' || !filter_var($newEmail, FILTER_VALIDATE_EMAIL)) {
                        $_SESSION['error'] = "Invalid or empty email address format provided.";
                        break;
                    }

                    $chk = $this->pdo->prepare("SELECT id FROM users WHERE email = ? AND id != ?");
                    $chk->execute([$newEmail, $targetUserId]);
                    if ($chk->fetch()) {
                        $_SESSION['error'] = "That email address is already registered to another user account.";
                        break;
                    }

                    $upd = $this->pdo->prepare("UPDATE users SET email = ? WHERE id = ?");
                    $upd->execute([$newEmail, $targetUserId]);

                    $_SESSION['message'] = "Email address for '{$targetUser['username']}' updated to {$newEmail}.";

                    $audit = $this->pdo->prepare("INSERT INTO audit_logs (user_id, action, details, ip_address) VALUES (?, 'UPDATE_USER_EMAIL', ?, ?)");
                    $audit->execute([$currentUser['id'], "Changed email address for {$targetUser['username']} to {$newEmail}", $remoteAddr]);
                    break;

                case 'send_password_reset':
                    $token = bin2hex(random_bytes(32));
                    $expires = date('Y-m-d H:i:s', strtotime('+24 hours'));
                    
                    $upd = $this->pdo->prepare("UPDATE users SET reset_token = ?, reset_expires_at = ? WHERE id = ?");
                    $upd->execute([$token, $expires, $targetUserId]);

                    $uDet = $this->pdo->prepare("SELECT u.email, u.username, r.role_name FROM users u LEFT JOIN roles r ON u.role_id = r.id WHERE u.id = ?");
                    $uDet->execute([$targetUserId]);
                    /** @array{email: string, username: string, role_name?: string}|false $uData */
                    $uData = $uDet->fetch(PDO::FETCH_ASSOC);

                    if ($uData !== false && !empty($uData['email'])) {
                        send_user_invitation($this->pdo, $uData['email'], $token, [
                            'first_name' => $uData['username'],
                            'surname'    => '',
                            'username'   => $uData['username'],
                            'role_name'  => $uData['role_name'] ?? 'User'
                        ], 'password_reset');

                        $_SESSION['message'] = "Password reset link successfully dispatched to '{$uData['username']}'.";

                        $audit = $this->pdo->prepare("INSERT INTO audit_logs (user_id, action, details, ip_address) VALUES (?, 'SEND_PASSWORD_RESET', ?, ?)");
                        $audit->execute([$currentUser['id'], "Dispatched password reset link to user: {$uData['username']}", $remoteAddr]);
                    } else {
                        $_SESSION['error'] = "Could not find a valid email address for this user.";
                    }
                    break;

                case 'resend_invite':
                    $token = bin2hex(random_bytes(32));
                    $expires = date('Y-m-d H:i:s', strtotime('+24 hours'));
                    
                    $upd = $this->pdo->prepare("UPDATE users SET invite_token = ?, invite_expires_at = ? WHERE id = ?");
                    $upd->execute([$token, $expires, $targetUserId]);

                    $uDet = $this->pdo->prepare("SELECT u.email, u.username, r.role_name FROM users u LEFT JOIN roles r ON u.role_id = r.id WHERE u.id = ?");
                    $uDet->execute([$targetUserId]);
                    /** @array{email: string, username: string, role_name?: string}|false $uData */
                    $uData = $uDet->fetch(PDO::FETCH_ASSOC);

                    if ($uData !== false && !empty($uData['email'])) {
                        send_user_invitation($this->pdo, $uData['email'], $token, [
                            'first_name' => $uData['username'],
                            'surname'    => '',
                            'username'   => $uData['username'],
                            'role_name'  => $uData['role_name'] ?? 'User'
                        ]);

                        $_SESSION['message'] = "Invitation email successfully dispatched to '{$uData['username']}'.";

                        $audit = $this->pdo->prepare("INSERT INTO audit_logs (user_id, action, details, ip_address) VALUES (?, 'RESEND_INVITE', ?, ?)");
                        $audit->execute([$currentUser['id'], "Dispatched invitation email to user: {$uData['username']}", $remoteAddr]);
                    } else {
                        $_SESSION['error'] = "Could not find a valid email address for this user.";
                    }
                    break;

                case 'suspend':
                    if ($targetUserId === (int)$currentUser['id']) {
                        $_SESSION['error'] = "You cannot suspend your own administrative account.";
                        break;
                    }

                    $upd = $this->pdo->prepare("UPDATE users SET is_active = 0 WHERE id = ?");
                    $upd->execute([$targetUserId]);

                    $_SESSION['message'] = "User '{$targetUser['username']}' has been suspended.";

                    $audit = $this->pdo->prepare("INSERT INTO audit_logs (user_id, action, details, ip_address) VALUES (?, 'SUSPEND_USER', ?, ?)");
                    $audit->execute([$currentUser['id'], "Suspended user account: {$targetUser['username']}", $remoteAddr]);
                    break;

                case 'unsuspend':
                    $upd = $this->pdo->prepare("UPDATE users SET is_active = 1 WHERE id = ?");
                    $upd->execute([$targetUserId]);

                    $_SESSION['message'] = "User '{$targetUser['username']}' has been reactivated.";

                    $audit = $this->pdo->prepare("INSERT INTO audit_logs (user_id, action, details, ip_address) VALUES (?, 'REACTIVATE_USER', ?, ?)");
                    $audit->execute([$currentUser['id'], "Reactivated user account: {$targetUser['username']}", $remoteAddr]);
                    break;

                case 'delete':
                    if ($targetUserId === (int)$currentUser['id']) {
                        $_SESSION['error'] = "You cannot delete your own active administrative account.";
                        break;
                    }

                    $del = $this->pdo->prepare("DELETE FROM users WHERE id = ?");
                    $del->execute([$targetUserId]);

                    $_SESSION['message'] = "User '{$targetUser['username']}' has been permanently deleted.";

                    $audit = $this->pdo->prepare("INSERT INTO audit_logs (user_id, action, details, ip_address) VALUES (?, 'DELETE_USER', ?, ?)");
                    $audit->execute([$currentUser['id'], "Permanently deleted user account: {$targetUser['username']}", $remoteAddr]);
                    break;

                case 'reset_2fa':
                    $upd = $this->pdo->prepare("UPDATE users SET two_fa_enabled = 0, google_2fa_secret = NULL WHERE id = ?");
                    $upd->execute([$targetUserId]);

                    $_SESSION['message'] = "Two-factor authentication has been reset for '{$targetUser['username']}'.";

                    $audit = $this->pdo->prepare("INSERT INTO audit_logs (user_id, action, details, ip_address) VALUES (?, 'RESET_2FA', ?, ?)");
                    $audit->execute([$currentUser['id'], "Reset 2FA for user account: {$targetUser['username']}", $remoteAddr]);
                    break;

                default:
                    $_SESSION['error'] = "Unknown management action requested.";
                    break;
            }
        } catch (Exception $e) {
            $_SESSION['error'] = "Database error: " . $e->getMessage();
        }

        header('Location: /admin/users');
        exit;
    }
}
