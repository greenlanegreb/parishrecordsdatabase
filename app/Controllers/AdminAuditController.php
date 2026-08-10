<?php
/**
 * MIGRATED FILE MAPPING
 * ---------------------
 * Original Old File: admin/actions/purge_audit_logs.php
 * Migrated Date: 2026-08-05 04:29:45
 */
declare(strict_types=1);

namespace App\Controllers;

use Exception;
use PDO;

class AdminAuditController
{
    private PDO $pdo;

    public function __construct(PDO $pdo)
    {
        $this->pdo = $pdo;
    }

    public function purge(): void
    {
        $serverMethod = isset($_SERVER['REQUEST_METHOD']) && is_string($_SERVER['REQUEST_METHOD']) ? $_SERVER['REQUEST_METHOD'] : 'GET';
        if ($serverMethod !== 'POST') {
            http_response_code(405);
            exit('Method Not Allowed');
        }

        verify_csrf_token();
        /** @var array{id: int, username: string} $currentUser */
        $currentUser = require_permission(
           $this->pdo,
           'manage_audit_logs',
           'Allows viewing and managing the global system-wide audit logs'
           );
        $post = $_POST;
        $purgeType = isset($post['purge_type']) && is_string($post['purge_type']) ? trim($post['purge_type']) : '';
        $remoteAddr = isset($_SERVER['REMOTE_ADDR']) && is_string($_SERVER['REMOTE_ADDR']) ? $_SERVER['REMOTE_ADDR'] : '127.0.0.1';

        try {
            if ($purgeType === 'all') {
                $this->pdo->exec("DELETE FROM audit_logs");
                $_SESSION['message'] = "The entire audit log has been successfully cleared.";

                // Log the purge action itself
                $audit = $this->pdo->prepare("INSERT INTO audit_logs (`user_id`, `action`, `details`, `ip address`, `created at`) VALUES (?, 'PURGE_AUDIT_LOGS', 'Cleared the entire audit log', ?, NOW())");
                $audit->execute([$currentUser['id'], $remoteAddr]);
            } elseif ($purgeType === 'records_only') {
                $stmt = $this->pdo->prepare("DELETE FROM audit_logs WHERE action IN ('INSERT', 'PURGE_RECORD', 'EDIT_SUGGESTION', 'APPROVE_SUGGESTION', 'REJECT_SUGGESTION')");
                $stmt->execute();
                $_SESSION['message'] = "All records-related audit logs have been successfully cleared.";

                $audit = $this->pdo->prepare("INSERT INTO audit_logs (`user_id`, `action`, `details`, `ip address`, `created at`) VALUES (?, 'PURGE_AUDIT_LOGS', 'Cleared records-related audit logs', ?, NOW())");
                $audit->execute([$currentUser['id'], $remoteAddr]);
            } elseif ($purgeType !== '') {
                $stmt = $this->pdo->prepare("DELETE FROM audit_logs WHERE action = ?");
                $stmt->execute([$purgeType]);
                $_SESSION['message'] = "Audit logs for action type '{$purgeType}' have been successfully cleared.";

                $audit = $this->pdo->prepare("INSERT INTO audit_logs (`user_id`, `action`, `details`, `ip address`, `created at`) VALUES (?, 'PURGE_AUDIT_LOGS', ?, ?, NOW())");
                $audit->execute([$currentUser['id'], "Cleared audit logs for action type: {$purgeType}", $remoteAddr]);
            } else {
                $_SESSION['error'] = "Invalid purge action specified.";
            }
        } catch (Exception $e) {
            $_SESSION['error'] = "Failed to clear audit logs: " . $e->getMessage();
        }

        // Use BASE_PATH to ensure subfolder redirects work seamlessly
        $redirectUrl = BASE_PATH . '/admin/settings#tab-audit';
        header('Location: ' . $redirectUrl);
        exit;
    }
}
