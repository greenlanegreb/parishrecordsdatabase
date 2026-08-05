<?php
/**
 * MIGRATED FILE MAPPING
 * ---------------------
 * Original Old File: purge_audit_entry.php
 * Migrated Date: 2026-08-05 05:40:13
 */declare(strict_types=1);


namespace App\Controllers;

use Exception;
use PDO;

class UserPurgeAuditEntryActionController
{
    private PDO $pdo;

    public function __construct(PDO $pdo)
    {
        $this->pdo = $pdo;
    }

    public function purge(): void
    {
        if (session_status() === PHP_SESSION_NONE) {
            session_start();
        }

        $serverMethod = isset($_SERVER['REQUEST_METHOD']) && is_string($_SERVER['REQUEST_METHOD']) ? $_SERVER['REQUEST_METHOD'] : 'GET';
        if ($serverMethod !== 'POST') {
            http_response_code(405);
            exit('Method Not Allowed');
        }

        verify_csrf_token();

        // Self-discovers and enforces the permission key in the database matrix
        require_permission($this->pdo, 'purge_audit_entry', 'Allows purging individual audit log entries from record history');

        $post = $_POST;
        $auditId = isset($post['audit_id']) ? (int)$post['audit_id'] : 0;
        $recordId = isset($post['record_id']) ? (int)$post['record_id'] : 0;

        if ($auditId <= 0 || $recordId <= 0) {
            $_SESSION['error'] = "Invalid audit or record specified.";
            header('Location: /index.php');
            exit;
        }

        try {
            $stmt = $this->pdo->prepare("DELETE FROM audit_logs WHERE id = ?");
            $stmt->execute([$auditId]);

            $_SESSION['message'] = "Audit log entry successfully purged.";
        } catch (Exception $e) {
            $_SESSION['error'] = "Failed to purge audit entry: " . $e->getMessage();
        }

        header('Location: /record_history.php?record_id=' . $recordId);
        exit;
    }
}
