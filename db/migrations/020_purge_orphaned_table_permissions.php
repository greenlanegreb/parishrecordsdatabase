<?php
// db/migrations/020_purge_orphaned_table_permissions.php

return [
    'version'     => 20,
    'description' => 'Purge orphaned table permissions, remove edit_records, introduce export_data respecting admin settings',
    'up'          => function (PDO $pdo) {
        $pdo->beginTransaction();
        try {
            // 1. Purge orphaned table-scoped permissions and their role mappings left behind by previously deleted dynamic tables
            $tablesStmt = $pdo->query("SELECT id FROM dynamic_tables");
            $validTableIds = $tablesStmt->fetchAll(PDO::FETCH_COLUMN);
            $validTableIds = array_map('intval', $validTableIds);

            $permStmt = $pdo->query("SELECT id, permission_key FROM permissions WHERE permission_key LIKE 'view_table_%' OR permission_key LIKE 'moderate_table_%'");
            $allDynamicPerms = $permStmt->fetchAll(PDO::FETCH_ASSOC);

            $orphanedPermIds = [];

            foreach ($allDynamicPerms as $perm) {
                $key = $perm['permission_key'];
                if (preg_match('/_(?:table_)?(\d+)$/', $key, $matches)) {
                    $tableId = (int) $matches[1];
                    if (!in_array($tableId, $validTableIds, true)) {
                        $orphanedPermIds[] = (int) $perm['id'];
                    }
                }
            }

            if (!empty($orphanedPermIds)) {
                $placeholders = implode(',', array_fill(0, count($orphanedPermIds), '?'));
                $delMappings = $pdo->prepare("DELETE FROM role_permissions WHERE permission_id IN ({$placeholders})");
                $delMappings->execute($orphanedPermIds);

                $delPermissions = $pdo->prepare("DELETE FROM permissions WHERE id IN ({$placeholders})");
                $delPermissions->execute($orphanedPermIds);
            }

            // 2. Delete obsolete 'edit_records' permission and its role mappings
            $editPermStmt = $pdo->prepare("SELECT id FROM permissions WHERE permission_key = 'edit_records'");
            $editPermStmt->execute();
            $editPermId = $editPermStmt->fetchColumn();

            if ($editPermId) {
                $delJunction = $pdo->prepare("DELETE FROM role_permissions WHERE permission_id = ?");
                $delJunction->execute([$editPermId]);

                $delPerm = $pdo->prepare("DELETE FROM permissions WHERE id = ?");
                $delPerm->execute([$editPermId]);
            }

            // 3. Ensure 'export_data' exists in the permissions master table
            $exportCheck = $pdo->prepare("SELECT id FROM permissions WHERE permission_key = 'export_data'");
            $exportCheck->execute();
            $exportPermId = $exportCheck->fetchColumn();

            if (!$exportPermId) {
                $insExport = $pdo->prepare("INSERT INTO permissions (permission_key, description) VALUES (?, ?)");
                $insExport->execute(['export_data', 'Export data from records table']);
                $exportPermId = $pdo->lastInsertId();
            }

            // 4. Map 'export_data' to default roles, respecting admin toggles stored in site_settings
            $settingsStmt = $pdo->prepare("SELECT setting_value FROM site_settings WHERE setting_key = ?");
            
            $settingsStmt->execute(['allow_guest_export']);
            $guestExportSetting = $settingsStmt->fetchColumn();
            $allowGuestExport = ($guestExportSetting !== false && $guestExportSetting !== '0' && $guestExportSetting !== 'false');

            $settingsStmt->execute(['allow_user_export']);
            $userExportSetting = $settingsStmt->fetchColumn();
            $allowUserExport = ($userExportSetting !== false && $userExportSetting !== '0' && $userExportSetting !== 'false');

            $rolesMap = [
                'admin'     => true,
                'moderator' => $allowUserExport,
                'user'      => $allowUserExport,
                'guest'     => $allowGuestExport,
            ];

            $roleStmt = $pdo->prepare("SELECT id, role_name FROM roles");
            $roleStmt->execute();
            $roles = $roleStmt->fetchAll(PDO::FETCH_ASSOC);

            $assignStmt = $pdo->prepare("INSERT IGNORE INTO role_permissions (role_id, permission_id) VALUES (?, ?)");
            $removeStmt = $pdo->prepare("DELETE FROM role_permissions WHERE role_id = ? AND permission_id = ?");

            foreach ($roles as $role) {
                $roleName = strtolower(trim($role['role_name']));
                if (isset($rolesMap[$roleName])) {
                    if ($rolesMap[$roleName]) {
                        $assignStmt->execute([$role['id'], $exportPermId]);
                    } else {
                        $removeStmt->execute([$role['id'], $exportPermId]);
                    }
                }
            }

            $pdo->commit();
        } catch (Exception $e) {
            $pdo->rollBack();
            throw $e;
        }
    },
];
