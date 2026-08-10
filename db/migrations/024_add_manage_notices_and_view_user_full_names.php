<?php
// 024_add_manage_notices_and_view_user_full_names.php
// Adds manage_notices + view_user_full_names if missing, and grants them to admin (role_id = 1)
return [
    'version' => 24,
    'description' => 'Add manage_notices and view_user_full_names permissions; grant to admin role',
    'up' => function (PDO $pdo) {
        $insertPerm = $pdo->prepare(
            'INSERT IGNORE INTO permissions (permission_key, description) VALUES (?, ?)'
        );

        $insertPerm->execute([
            'manage_notices',
            'Manage PRD Front Page Notices',
        ]);
        $insertPerm->execute([
            'view_user_full_names',
            'Controls Visibility of Volunteers Full Names Being Seen Instead of Obscured',
        ]);

        // Grant both to admin role (id = 1) if not already mapped
        $pdo->exec("
            INSERT IGNORE INTO role_permissions (role_id, permission_id)
            SELECT 1, id FROM permissions
            WHERE permission_key IN ('manage_notices', 'view_user_full_names')
        ");
    },
];
