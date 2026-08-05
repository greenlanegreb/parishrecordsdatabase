<?php
/**
 * MIGRATED FILE MAPPING
 * ---------------------
 * Original Old File: admin/actions/save_permissions.php
 * Migrated Date: 2026-08-05 04:40:25
 */declare(strict_types=1);


namespace App\Controllers;

use Exception;
use PDO;

class AdminPermissionsActionController
{
    private PDO $pdo;

    public function __construct(PDO $pdo)
    {
        $this->pdo = $pdo;
    }

    public function save(): void
    {
        if (session_status() === PHP_SESSION_NONE) {
            session_start();
        }

        $serverMethod = isset($_SERVER['REQUEST_METHOD']) && is_string($_SERVER['REQUEST_METHOD']) ? $_SERVER['REQUEST_METHOD'] : 'GET';
        if ($serverMethod !== 'POST') {
            http_response_code(405);
            exit('Method Not Allowed');
        }

        // Verify CSRF token and enforce dynamic permission check
        verify_csrf_token();
        /** @var array{id: int, username: string} $currentUser */
        $currentUser = require_permission($this->pdo, 'manage_settings', 'Manage global site settings, mail drivers, and maintenance mode');

        $post = $_POST;
        /** @var array<mixed, mixed> $submittedMatrix */
        $submittedMatrix = isset($post['permissions']) && is_array($post['permissions']) ? $post['permissions'] : []; // Format: role_permissions[role_id][permission_id] = 1
        $remoteAddr = isset($_SERVER['REMOTE_ADDR']) && is_string($_SERVER['REMOTE_ADDR']) ? $_SERVER['REMOTE_ADDR'] : '127.0.0.1';

        try {
            $this->pdo->beginTransaction();

            // Clear existing mappings to rebuild cleanly based on checkbox state
            $this->pdo->exec("DELETE FROM role_permissions");

            if (!empty($submittedMatrix)) {
                $insertStmt = $this->pdo->prepare("INSERT INTO role_permissions (role_id, permission_id) VALUES (?, ?)");
                
                foreach ($submittedMatrix as $roleId => $permissionIds) {
                    if (is_array($permissionIds)) {
                        foreach ($permissionIds as $permissionId => $val) {
                            if ((string)$val === '1') {
                                $insertStmt->execute([(int)$roleId, (int)$permissionId]);
                            }
                        }
                    }
                }
            }

            $this->pdo->commit();
            $_SESSION['message'] = "Role permissions matrix successfully updated!";
            
            $audit = $this->pdo->prepare("INSERT INTO audit_logs (user_id, action, details, ip_address) VALUES (?, 'UPDATE_PERMISSIONS', ?, ?)");
            $audit->execute([$currentUser['id'], "Updated dynamic role permission matrix", $remoteAddr]);

        } catch (Exception $e) {
            if ($this->pdo->inTransaction()) {
                $this->pdo->rollBack();
            }
            $_SESSION['error'] = "Failed to update permissions: " . $e->getMessage();
        }

        header('Location: /admin/settings#tab-permissions');
        exit;
    }
}
