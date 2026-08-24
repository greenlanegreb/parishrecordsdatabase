<?php
/**
 * MIGRATED FILE MAPPING
 * ---------------------
 * Original Old File: admin/actions/save_role.php
 * Migrated Date: 2026-08-05 04:41:55
 */
declare(strict_types=1);

namespace App\Controllers;

use Exception;
use PDO;

class AdminRoleActionController
{
    private PDO $pdo;

    public function __construct(PDO $pdo)
    {
        $this->pdo = $pdo;
    }

    public function handle(): void
    {
        $serverMethod = isset($_SERVER['REQUEST_METHOD']) && is_string($_SERVER['REQUEST_METHOD']) ? $_SERVER['REQUEST_METHOD'] : 'GET';
        if ($serverMethod !== 'POST') {
            http_response_code(405);
            exit('Method Not Allowed');
        }

        verify_csrf_token();
        /** @var array{id: int, username: string} $currentUser */
        $currentUser = require_permission($this->pdo, 'manage_settings', 'Manage global site settings, mail drivers, and maintenance mode');

        $post = $_POST;
        $action = isset($post['action']) && is_string($post['action']) ? $post['action'] : 'create_role';
        $remoteAddr = isset($_SERVER['REMOTE_ADDR']) && is_string($_SERVER['REMOTE_ADDR']) ? $_SERVER['REMOTE_ADDR'] : '127.0.0.1';

        // HANDLE ROLE DELETION
        if (isset($post['delete_role_id'])) {
            $roleIdToDelete = (int)$post['delete_role_id'];

            // Fetch role info
            $rChk = $this->pdo->prepare("SELECT role_name FROM roles WHERE id = ?");
            $rChk->execute([$roleIdToDelete]);
            /** @var array{role_name: string}|false $roleToDelete */
            $roleToDelete = $rChk->fetch(PDO::FETCH_ASSOC);

            if ($roleToDelete === false) {
                $_SESSION['error'] = "Role not found.";
            } elseif (in_array($roleToDelete['role_name'], ['admin', 'moderator', 'user', 'guest'], true)) {
                $_SESSION['error'] = "Core system roles cannot be deleted.";
            } else {
                // Find default 'user' role ID to reassign orphaned users safely
                $defaultR = $this->pdo->query("SELECT id FROM roles WHERE role_name = 'user' LIMIT 1");
                $fallbackCol = $defaultR !== false ? $defaultR->fetch(PDO::FETCH_COLUMN) : false;
                $fallbackRoleId = ($fallbackCol !== false && $fallbackCol !== null) ? (int)$fallbackCol : 2; // Default fallback ID 2

                $this->pdo->beginTransaction();
                try {
                    // Reassign any users currently assigned to this deleted role over to the default user role
                    $reassign = $this->pdo->prepare("UPDATE users SET role_id = ? WHERE role_id = ?");
                    $reassign->execute([$fallbackRoleId, $roleIdToDelete]);

                    // Delete role permission mappings
                    $delPerms = $this->pdo->prepare("DELETE FROM role_permissions WHERE role_id = ?");
                    $delPerms->execute([$roleIdToDelete]);

                    // Delete the role itself
                    $delRole = $this->pdo->prepare("DELETE FROM roles WHERE id = ?");
                    $delRole->execute([$roleIdToDelete]);

                    $this->pdo->commit();
                    $_SESSION['message'] = "Role '{$roleToDelete['role_name']}' successfully deleted and associated users safely reassigned.";

                    $audit = $this->pdo->prepare("INSERT INTO audit_logs (user_id, action, details, ip_address) VALUES (?, 'DELETE_ROLE', ?, ?)");
                    $audit->execute([$currentUser['id'], "Deleted custom role: {$roleToDelete['role_name']}", $remoteAddr]);
                } catch (Exception $e) {
                    if ($this->pdo->inTransaction()) {
                        $this->pdo->rollBack();
                    }
                    $_SESSION['error'] = "Failed to delete role: " . $e->getMessage();
                }
            }
            header('Location: ' . BASE_PATH . '/admin/settings?tab=permissions');
            exit;
        }

        // HANDLE ROLE CREATION OR UPDATING
        $rawRoleName = isset($post['role_name']) && is_string($post['role_name']) ? strtolower(trim($post['role_name'])) : '';
        $description = isset($post['description']) && is_string($post['description']) ? trim($post['description']) : '';
        $roleName = preg_replace('/[^a-z0-9_]/', '_', $rawRoleName) ?? '';

        if ($roleName === '') {
            $_SESSION['error'] = "Role name cannot be empty.";
            header('Location: ' . BASE_PATH . '/admin/settings?tab=permissions');
            exit;
        }

        try {
            if ($action === 'update_role') {
                $roleId = isset($post['role_id']) ? (int)$post['role_id'] : 0;

                // Check name collision
                $chk = $this->pdo->prepare("SELECT id FROM roles WHERE role_name = ? AND id != ?");
                $chk->execute([$roleName, $roleId]);
                if ($chk->fetch()) {
                    $_SESSION['error'] = "Another role with that name already exists.";
                } else {
                    $upd = $this->pdo->prepare("UPDATE roles SET role_name = ?, description = ? WHERE id = ?");
                    if ($upd->execute([$roleName, $description, $roleId])) {
                        $_SESSION['message'] = "Role '{$roleName}' successfully updated!";

                        $audit = $this->pdo->prepare("INSERT INTO audit_logs (user_id, action, details, ip_address) VALUES (?, 'UPDATE_ROLE', ?, ?)");
                        $audit->execute([$currentUser['id'], "Updated role ID {$roleId} to name: {$roleName}", $remoteAddr]);
                    } else {
                        $_SESSION['error'] = "Failed to update role.";
                    }
                }
            } else {
                // Create new role
                $chk = $this->pdo->prepare("SELECT id FROM roles WHERE role_name = ?");
                $chk->execute([$roleName]);
                if ($chk->fetch()) {
                    $_SESSION['error'] = "A role with that name already exists.";
                } else {
                    $ins = $this->pdo->prepare("INSERT INTO roles (role_name, description) VALUES (?, ?)");
                    if ($ins->execute([$roleName, $description])) {
                        $newRoleId = (int)$this->pdo->lastInsertId();

                        // Default: allow directory viewing (guest-style master switch key)
                        $permStmt = $this->pdo->prepare("SELECT id FROM permissions WHERE permission_key = 'view_as_guest'");
                        $permStmt->execute();
                        $viewPermId = $permStmt->fetchColumn();

                        if ($viewPermId !== false && $viewPermId !== null) {
                            $mapStmt = $this->pdo->prepare("INSERT INTO role_permissions (role_id, permission_id) VALUES (?, ?)");
                            $mapStmt->execute([$newRoleId, (int)$viewPermId]);
                        }
                        $_SESSION['message'] = "Custom role '{$roleName}' successfully created!";

                        $audit = $this->pdo->prepare("INSERT INTO audit_logs (user_id, action, details, ip_address) VALUES (?, 'CREATE_ROLE', ?, ?)");
                        $audit->execute([$currentUser['id'], "Created new role: {$roleName}", $remoteAddr]);
                    } else {
                        $_SESSION['error'] = "Failed to create role.";
                    }
                }
            }
        } catch (Exception $e) {
            $_SESSION['error'] = "Database error: " . $e->getMessage();
        }

        header('Location: ' . BASE_PATH . '/admin/settings?tab=permissions');
        exit;
    }
}
