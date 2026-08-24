<?php
declare(strict_types=1);
return [
    'version' => 33,
    'description' => 'Permission delete_records for admin; independent of moderation module',
    'up' => static function (PDO $pdo): void {
        $pdo->exec(
            "INSERT IGNORE INTO permissions (permission_key, description) VALUES
             ('delete_records', 'Delete individual records from dynamic tables')"
        );

        // Grant to admin role only (by role_name)
        $roleStmt = $pdo->query("SELECT id FROM roles WHERE LOWER(role_name) = 'admin' LIMIT 1");
        $adminId = $roleStmt !== false ? (int) $roleStmt->fetchColumn() : 0;
        if ($adminId < 1) {
            return;
        }
        $permStmt = $pdo->prepare("SELECT id FROM permissions WHERE permission_key = 'delete_records' LIMIT 1");
        $permStmt->execute();
        $permId = (int) $permStmt->fetchColumn();
        if ($permId < 1) {
            return;
        }
        $map = $pdo->prepare(
            'INSERT IGNORE INTO role_permissions (role_id, permission_id) VALUES (?, ?)'
        );
        $map->execute([$adminId, $permId]);
    },
];
