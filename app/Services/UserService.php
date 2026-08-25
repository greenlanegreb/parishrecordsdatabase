<?php
declare(strict_types=1);

namespace App\Services;

use PDO;
use Exception;

class UserService
{
    private PDO $pdo;

    public function __construct(PDO $pdo)
    {
        $this->pdo = $pdo;
    }

    public function changeRole(int $targetUserId, array $targetUser, int $newRoleId, array $currentUser, string $remoteAddr): string
    {
        $rChk = $this->pdo->prepare("SELECT role_name FROM roles WHERE id = ?");
        $rChk->execute([$newRoleId]);
        /** @var array{role_name: string}|false $roleData */
        $roleData = $rChk->fetch(PDO::FETCH_ASSOC);

        if ($roleData === false) {
            throw new Exception("Selected role does not exist.");
        }

        $upd = $this->pdo->prepare("UPDATE users SET role_id = ? WHERE id = ?");
        $upd->execute([$newRoleId, $targetUserId]);

        $audit = $this->pdo->prepare("INSERT INTO audit_logs (user_id, action, details, ip_address) VALUES (?, 'CHANGE_USER_ROLE', ?, ?)");
        $audit->execute([$currentUser['id'], "Changed role of user {$targetUser['username']} to {$roleData['role_name']}", $remoteAddr]);

        return "The role for '{$targetUser['username']}' has been successfully updated to '{$roleData['role_name']}'.";
    }

    public function overridePoints(int $targetUserId, array $targetUser, int $newPoints, array $currentUser, string $remoteAddr): string
    {
        $upd = $this->pdo->prepare("UPDATE users SET points = ? WHERE id = ?");
        $upd->execute([$newPoints, $targetUserId]);

        $audit = $this->pdo->prepare("INSERT INTO audit_logs (user_id, action, details, ip_address) VALUES (?, 'OVERRIDE_POINTS', ?, ?)");
        $audit->execute([$currentUser['id'], "Overrode score for {$targetUser['username']} to {$newPoints}", $remoteAddr]);

        return "The leadership score has been successfully updated for '{$targetUser['username']}'.";
    }

    public function updateEmail(int $targetUserId, array $targetUser, string $newEmail, array $currentUser, string $remoteAddr): string
    {
        if ($newEmail === '' || !filter_var($newEmail, FILTER_VALIDATE_EMAIL)) {
            throw new Exception("Invalid or empty email address format provided.");
        }

        $chk = $this->pdo->prepare("SELECT id FROM users WHERE email = ? AND id != ?");
        $chk->execute([$newEmail, $targetUserId]);
        if ($chk->fetch()) {
            throw new Exception("That email address is already registered to another user account.");
        }

        $upd = $this->pdo->prepare("UPDATE users SET email = ? WHERE id = ?");
        $upd->execute([$newEmail, $targetUserId]);

        $audit = $this->pdo->prepare("INSERT INTO audit_logs (user_id, action, details, ip_address) VALUES (?, 'UPDATE_USER_EMAIL', ?, ?)");
        $audit->execute([$currentUser['id'], "Changed email address for {$targetUser['username']} to {$newEmail}", $remoteAddr]);

        return "The email address for '{$targetUser['username']}' has been successfully updated to {$newEmail}.";
    }

    public function sendPasswordReset(int $targetUserId, array $currentUser, string $remoteAddr): string
    {

        require_once dirname(__DIR__, 2) . '/db/mail_helper.php';
        $token = bin2hex(random_bytes(32));
        $expires = date('Y-m-d H:i:s', strtotime('+24 hours'));
        
        $upd = $this->pdo->prepare("UPDATE users SET reset_token = ?, reset_expires_at = ? WHERE id = ?");
        $upd->execute([$token, $expires, $targetUserId]);

        $uDet = $this->pdo->prepare("SELECT u.email, u.username, r.role_name FROM users u LEFT JOIN roles r ON u.role_id = r.id WHERE u.id = ?");
        $uDet->execute([$targetUserId]);
        /** @var array{email: string, username: string, role_name?: string}|false $uData */
        $uData = $uDet->fetch(PDO::FETCH_ASSOC);

        if ($uData === false || empty($uData['email'])) {
            throw new Exception("Could not find a valid email address for this user.");
        }

        \send_user_invitation($this->pdo, $uData['email'], $token, [
            'first_name' => $uData['username'],
            'surname'    => '',
            'username'   => $uData['username'],
            'role_name'  => $uData['role_name'] ?? 'User'
        ], 'password_reset');

        $audit = $this->pdo->prepare("INSERT INTO audit_logs (user_id, action, details, ip_address) VALUES (?, 'SEND_PASSWORD_RESET', ?, ?)");
        $audit->execute([$currentUser['id'], "Dispatched password reset link to user: {$uData['username']}", $remoteAddr]);

        return "A password reset email for '{$uData['username']}' has been successfully sent.";
    }

    public function resendInvite(int $targetUserId, array $currentUser, string $remoteAddr): string
    {

        require_once dirname(__DIR__, 2) . '/db/mail_helper.php';
        $token = bin2hex(random_bytes(32));
        $expires = date('Y-m-d H:i:s', strtotime('+24 hours'));
        
        $upd = $this->pdo->prepare("UPDATE users SET invite_token = ?, invite_expires_at = ? WHERE id = ?");
        $upd->execute([$token, $expires, $targetUserId]);

        $uDet = $this->pdo->prepare("SELECT u.email, u.username, r.role_name FROM users u LEFT JOIN roles r ON u.role_id = r.id WHERE u.id = ?");
        $uDet->execute([$targetUserId]);
        /** @var array{email: string, username: string, role_name?: string}|false $uData */
        $uData = $uDet->fetch(PDO::FETCH_ASSOC);

        if ($uData === false || empty($uData['email'])) {
            throw new Exception("Could not find a valid email address for this user.");
        }

        \send_user_invitation($this->pdo, $uData['email'], $token, [
            'first_name' => $uData['username'],
            'surname'    => '',
            'username'   => $uData['username'],
            'role_name'  => $uData['role_name'] ?? 'User'
        ]);

        $audit = $this->pdo->prepare("INSERT INTO audit_logs (user_id, action, details, ip_address) VALUES (?, 'RESEND_INVITE', ?, ?)");
        $audit->execute([$currentUser['id'], "Dispatched invitation email to user: {$uData['username']}", $remoteAddr]);

        return "'{$uData['username']}' has been successfully reinvited.";
    }

    public function suspendUser(int $targetUserId, array $targetUser, array $currentUser, string $remoteAddr): string
    {
        if ($targetUserId === (int)$currentUser['id']) {
            throw new Exception("You cannot suspend your own administrative account.");
        }

        $upd = $this->pdo->prepare("UPDATE users SET is_active = 0 WHERE id = ?");
        $upd->execute([$targetUserId]);

        $audit = $this->pdo->prepare("INSERT INTO audit_logs (user_id, action, details, ip_address) VALUES (?, 'SUSPEND_USER', ?, ?)");
        $audit->execute([$currentUser['id'], "Suspended user account: {$targetUser['username']}", $remoteAddr]);

        return "'{$targetUser['username']}' has been successfully suspended.";
    }

    public function unsuspendUser(int $targetUserId, array $targetUser, array $currentUser, string $remoteAddr): string
    {
        $upd = $this->pdo->prepare("UPDATE users SET is_active = 1 WHERE id = ?");
        $upd->execute([$targetUserId]);

        $audit = $this->pdo->prepare("INSERT INTO audit_logs (user_id, action, details, ip_address) VALUES (?, 'REACTIVATE_USER', ?, ?)");
        $audit->execute([$currentUser['id'], "Reactivated user account: {$targetUser['username']}", $remoteAddr]);

        return "'{$targetUser['username']}' has been successfully reactivated.";
    }

    public function deleteUser(int $targetUserId, array $targetUser, array $currentUser, string $remoteAddr): string
    {
        if ($targetUserId === (int)$currentUser['id']) {
            throw new Exception("You cannot delete your own active administrative account.");
        }

        $uname = isset($targetUser['username']) && is_string($targetUser['username'])
            ? $targetUser['username'] : '';

        // Never re-issue this login name (audits / "who was jfairby?")
        $helper = dirname(__DIR__, 2) . '/includes/username_check_helpers.php';
        if (is_file($helper)) {
            require_once $helper;
        }
        if ($uname !== '' && function_exists('retire_username')) {
            retire_username($this->pdo, $uname, $targetUserId);
        }

        $del = $this->pdo->prepare("DELETE FROM users WHERE id = ?");
        $del->execute([$targetUserId]);

        $audit = $this->pdo->prepare("INSERT INTO audit_logs (user_id, action, details, ip_address) VALUES (?, 'DELETE_USER', ?, ?)");
        $audit->execute([$currentUser['id'], "Permanently deleted user account: {$uname}", $remoteAddr]);

        return "'{$uname}' has been successfully deleted. That username will not be offered again.";
    }

    public function reset2fa(int $targetUserId, array $targetUser, array $currentUser, string $remoteAddr): string
    {
        $upd = $this->pdo->prepare("UPDATE users SET two_fa_enabled = 0, google_2fa_secret = NULL WHERE id = ?");
        $upd->execute([$targetUserId]);

        $audit = $this->pdo->prepare("INSERT INTO audit_logs (user_id, action, details, ip_address) VALUES (?, 'RESET_2FA', ?, ?)");
        $audit->execute([$currentUser['id'], "Reset 2FA for user account: {$targetUser['username']}", $remoteAddr]);

        return "Two-factor authentication has been successfully reset for '{$targetUser['username']}' .";
    }
}
