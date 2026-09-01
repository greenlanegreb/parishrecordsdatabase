<?php
/**
 * MIGRATED FILE MAPPING
 * ---------------------
 * Original Old File: admin/actions/save_permissions.php
 * Migrated Date: 2026-08-05 04:40:25
 */
declare(strict_types=1);

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

        $graph = dirname(__DIR__, 2) . '/includes/permission_graph.php';
        if (is_file($graph)) {
            require_once $graph;
        }

        $guestRoleId = 0;
        $guestStmt = $this->pdo->query("SELECT id FROM roles WHERE LOWER(role_name) = 'guest' LIMIT 1");
        if ($guestStmt !== false) {
            $guestRoleId = (int) $guestStmt->fetchColumn();
        }
        $guestAllowed = function_exists('guest_allowed_permission_keys') ? guest_allowed_permission_keys() : [];
        $keyById = [];
        $idByKey = [];
        $keysStmt = $this->pdo->query('SELECT id, permission_key FROM permissions');
        if ($keysStmt !== false) {
            foreach ($keysStmt->fetchAll(PDO::FETCH_ASSOC) as $row) {
                $id = (int) $row['id'];
                $key = (string) $row['permission_key'];
                $keyById[$id] = $key;
                $idByKey[$key] = $id;
            }
        }

        try {
            $this->pdo->beginTransaction();

            // Clear existing mappings to rebuild cleanly based on checkbox state
            $this->pdo->exec("DELETE FROM role_permissions");

            if (!empty($submittedMatrix)) {
                $insertStmt = $this->pdo->prepare("INSERT INTO role_permissions (role_id, permission_id) VALUES (?, ?)");

                foreach ($submittedMatrix as $roleId => $permissionIds) {
                    if (!is_array($permissionIds)) {
                        continue;
                    }
                    $roleId = (int) $roleId;
                    $accepted = [];
                    foreach ($permissionIds as $permissionId => $val) {
                        if ((string) $val !== '1') {
                            continue;
                        }
                        $permissionId = (int) $permissionId;
                        $key = $keyById[$permissionId] ?? '';
                        if ($key === '') {
                            continue;
                        }
                        if ($guestRoleId > 0 && $roleId === $guestRoleId) {
                            $ok = function_exists('guest_may_hold_permission')
                                ? guest_may_hold_permission($key)
                                : in_array($key, $guestAllowed, true);
                            if (!$ok) {
                                continue;
                            }
                        }
                        $accepted[$key] = $permissionId;
                    }
                    foreach ($accepted as $key => $permissionId) {
                        $parent = function_exists('permission_parent_key') ? permission_parent_key($key) : null;
                        if ($parent !== null && !isset($accepted[$parent]) && isset($idByKey[$parent])) {
                            $accepted[$parent] = $idByKey[$parent];
                        }
                    }
                    foreach ($accepted as $permissionId) {
                        $insertStmt->execute([$roleId, (int) $permissionId]);
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

        $xhr = isset($_SERVER['HTTP_X_REQUESTED_WITH']) && is_string($_SERVER['HTTP_X_REQUESTED_WITH'])
            && strtolower($_SERVER['HTTP_X_REQUESTED_WITH']) === 'xmlhttprequest';
        if ($xhr) {
            header('Content-Type: application/json; charset=utf-8');
            $ok = empty($_SESSION['error']);
            echo json_encode(['ok' => $ok, 'error' => $_SESSION['error'] ?? null]);
            unset($_SESSION['message'], $_SESSION['error']);
            exit;
        }
        header('Location: ' . BASE_PATH . '/admin/settings?tab=permissions');
        exit;
    }
}
