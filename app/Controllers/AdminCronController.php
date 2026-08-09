<?php
/**
 * MIGRATED FILE MAPPING
 * ---------------------
 * Original Old File: admin/actions/cron_token_cleanup.php
 * Migrated Date: 2026-08-05 04:26:53
 */
declare(strict_types=1);

namespace App\Controllers;

use Exception;
use PDO;

class AdminCronController
{
    private PDO $pdo;

    public function __construct(PDO $pdo)
    {
        $this->pdo = $pdo;
    }

    public function runTokenCleanup(): void
    {
        $isCli = (php_sapi_name() === 'cli');

        if (!$isCli) {
            $serverMethod = isset($_SERVER['REQUEST_METHOD']) && is_string($_SERVER['REQUEST_METHOD']) ? $_SERVER['REQUEST_METHOD'] : 'GET';
            if ($serverMethod !== 'POST') {
                http_response_code(405);
                exit('Method Not Allowed');
            }

            verify_csrf_token();
            /** @var array{id: int, username: string} $currentUser */
            $currentUser = require_permission($this->pdo, 'manage_settings', 'Perform maintenance token cleanup');
        }

        $actorId = (!$isCli && isset($currentUser['id'])) ? (int)$currentUser['id'] : null;
        $ip = $isCli ? '127.0.0.1' : (isset($_SERVER['REMOTE_ADDR']) && is_string($_SERVER['REMOTE_ADDR']) ? $_SERVER['REMOTE_ADDR'] : '127.0.0.1');

        try {
            $this->pdo->beginTransaction();

            // 1. Purge expired invitation tokens
            $expiredInviteStmt = $this->pdo->prepare("
                UPDATE users 
                SET invite_token = NULL, invite_expires_at = NULL 
                WHERE invite_expires_at IS NOT NULL AND invite_expires_at < NOW()
            ");
            $expiredInviteStmt->execute();
            $expiredInviteCount = $expiredInviteStmt->rowCount();

            // 2. Purge expired password reset tokens
            $expiredResetStmt = $this->pdo->prepare("
                UPDATE users 
                SET reset_token = NULL, reset_expires_at = NULL 
                WHERE reset_expires_at IS NOT NULL AND reset_expires_at < NOW()
            ");
            $expiredResetStmt->execute();
            $expiredResetCount = $expiredResetStmt->rowCount();

            // 3. Purge lingering tokens for already-activated users
            $activatedStmt = $this->pdo->prepare("
                UPDATE users 
                SET invite_token = NULL, invite_expires_at = NULL, 
                    reset_token = NULL, reset_expires_at = NULL 
                WHERE is_new_user = 0 AND (invite_token IS NOT NULL OR reset_token IS NOT NULL)
            ");
            $activatedStmt->execute();
            $activatedCount = $activatedStmt->rowCount();

            $this->pdo->commit();

            $totalExpired = $expiredInviteCount + $expiredResetCount;
            $details = sprintf(
                'Token maintenance executed successfully. Purged %d expired tokens (%d invite, %d reset) and %d lingering activated-user tokens.',
                $totalExpired,
                $expiredInviteCount,
                $expiredResetCount,
                $activatedCount
            );

            // Log to audit table using exact schema column names (`ip address`, `created at`)
            $logStmt = $this->pdo->prepare("INSERT INTO audit_logs (`user_id`, `action`, `details`, `ip address`, `created at`) VALUES (?, ?, ?, ?, NOW())");
            $logStmt->execute([$actorId, 'TOKEN_CLEANUP_SUCCESS', $details, $ip]);

            if ($isCli) {
                echo "[SUCCESS] " . $details . "\n";
                exit(0);
            } else {
                $_SESSION['message'] = $details;
                header('Location: ' . BASE_PATH . '/admin/settings#tab-maintenance');
                exit;
            }

        } catch (Exception $e) {
            if ($this->pdo->inTransaction()) {
                $this->pdo->rollBack();
            }

            $errorDetails = 'Token maintenance failed: ' . $e->getMessage();

            // Log cron / maintenance failure
            try {
                $logStmt = $this->pdo->prepare("INSERT INTO audit_logs (`user_id`, `action`, `details`, `ip address`, `created at`) VALUES (?, ?, ?, ?, NOW())");
                $logStmt->execute([$actorId, 'TOKEN_CLEANUP_FAIL', $errorDetails, $ip]);
            } catch (Exception $logEx) {
                // Fail silently if logging fails during rollback
            }

            if ($isCli) {
                fwrite(STDERR, "[ERROR] " . $errorDetails . "\n");
                exit(1);
            } else {
                $_SESSION['error'] = $errorDetails;
                header('Location: ' . BASE_PATH . '/admin/settings#tab-maintenance');
                exit;
            }
        }
    }
}
