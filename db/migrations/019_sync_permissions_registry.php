<?php
// db/migrations/019_sync_permissions_registry.php

return [
    'version'     => 19,
    'description' => 'Sync permissions registry, insert missing permissions non-destructively, and remove redundant keys',
    'up'          => function (PDO $pdo) {
        $registryPath = __DIR__ . '/../permissions_registry.php';
        if (!is_file($registryPath)) {
            throw new RuntimeException("Missing permissions registry file at: {$registryPath}");
        }

        $registry = include $registryPath;
        $permissions = $registry['permissions'] ?? [];
        $defaultRoles = $registry['default_roles'] ?? [];

        // 1. Insert any new or missing permissions non-destructively
        $insPerm = $pdo->prepare('INSERT IGNORE INTO permissions (permission_key, description) VALUES (?, ?)');
        foreach ($permissions as $key => $desc) {
            $insPerm->execute([$key, $desc]);
        }

        // 2. Synchronize default role-permission assignments non-destructively
        foreach ($defaultRoles as $roleName => $permKeys) {
            $stmt = $pdo->prepare("SELECT id FROM roles WHERE role_name = ?");
            $stmt->execute([$roleName]);
            $roleId = $stmt->fetchColumn();

            if ($roleId) {
                foreach ($permKeys as $key) {
                    $pStmt = $pdo->prepare("SELECT id FROM permissions WHERE permission_key = ?");
                    $pStmt->execute([$key]);
                    $permId = $pStmt->fetchColumn();

                    if ($permId) {
                        $mapStmt = $pdo->prepare("INSERT IGNORE INTO role_permissions (role_id, permission_id) VALUES (?, ?)");
                        $mapStmt->execute([$roleId, $permId]);
                    }
                }
            }
        }

        // 3. Purge redundant/deprecated permission keys safely and non-destructively
        $redundantKeys = ['manage_moderation', 'moderate_submissions'];
        foreach ($redundantKeys as $redundantKey) {
            $pStmt = $pdo->prepare("SELECT id FROM permissions WHERE permission_key = ?");
            $pStmt->execute([$redundantKey]);
            $permIds = $pStmt->fetchAll(PDO::FETCH_COLUMN);

            if (!empty($permIds)) {
                $placeholders = implode(',', array_fill(0, count($permIds), '?'));
                
                // Remove from role mappings first
                $delMap = $pdo->prepare("DELETE FROM role_permissions WHERE permission_id IN ({$placeholders})");
                $delMap->execute($permIds);

                // Remove from permissions table
                $delPerm = $pdo->prepare("DELETE FROM permissions WHERE permission_key = ?");
                $delPerm->execute([$redundantKey]);
            }
        }
    },
];
