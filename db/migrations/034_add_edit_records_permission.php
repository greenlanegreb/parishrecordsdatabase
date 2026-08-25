<?php
declare(strict_types=1);

/**
 * Migration 034: add edit_records permission (direct edit + merge).
 * Additive only. Default: admin role only.
 * Does not alter suggestion queue / moderation module behaviour.
 */
return [
    'version' => 34,
    'up' => function (PDO $pdo): void {
        // Permission
        $pdo->exec("
            INSERT IGNORE INTO permissions (permission_key, description)
            VALUES (
                'edit_records',
                'Edit and merge existing records (direct edit; not the public suggestion queue)'
            )
        ");

        // Map to admin role (role_name = 'admin')
        $permId = (int) $pdo->query("SELECT id FROM permissions WHERE permission_key = 'edit_records' LIMIT 1")->fetchColumn();
        if ($permId > 0) {
            $adminId = (int) $pdo->query("SELECT id FROM roles WHERE LOWER(role_name) = 'admin' LIMIT 1")->fetchColumn();
            if ($adminId > 0) {
                $stmt = $pdo->prepare("INSERT IGNORE INTO role_permissions (role_id, permission_id) VALUES (?, ?)");
                $stmt->execute([$adminId, $permId]);
            }
        }
    },
];
