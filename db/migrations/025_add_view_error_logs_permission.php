<?php
// 025_add_view_error_logs_permission.php
// Adds view_error_logs if missing, and grants it to admin (role_id = 1)
return [
    'version' => 25,
    'description' => 'Add view_error_logs permission; grant to admin role',
    'up' => function (PDO $pdo) {
        $insertPerm = $pdo->prepare(
            'INSERT IGNORE INTO permissions (permission_key, description) VALUES (?, ?)'
        );
        $insertPerm->execute([
            'view_error_logs',
            'View and look up system error log entries by reference ID',
        ]);

        $pdo->exec("
            INSERT IGNORE INTO role_permissions (role_id, permission_id)
            SELECT 1, id FROM permissions
            WHERE permission_key = 'view_error_logs'
        ");
    },
];
