<?php
// 023_rename_view_public_to_view_as_guest.php
// Renames permission_key view_public → view_as_guest (clearer: unauthenticated directory access)

return [
    'version' => 23,
    'description' => 'Rename view_public permission key to view_as_guest',
    'up' => function (PDO $pdo) {
        // If both exist, move role links from old → new then drop old
        $old = $pdo->prepare("SELECT id FROM permissions WHERE permission_key = 'view_public'");
        $old->execute();
        $oldId = $old->fetchColumn();

        $new = $pdo->prepare("SELECT id FROM permissions WHERE permission_key = 'view_as_guest'");
        $new->execute();
        $newId = $new->fetchColumn();

        if ($oldId && !$newId) {
            $pdo->prepare("
                UPDATE permissions
                SET permission_key = 'view_as_guest',
                    description = 'Allows guests (not logged in) to view and search records in the public directory'
                WHERE id = ?
            ")->execute([$oldId]);
        } elseif ($oldId && $newId && (int)$oldId !== (int)$newId) {
            $pdo->prepare("
                INSERT IGNORE INTO role_permissions (role_id, permission_id)
                SELECT role_id, ? FROM role_permissions WHERE permission_id = ?
            ")->execute([$newId, $oldId]);
            $pdo->prepare("DELETE FROM role_permissions WHERE permission_id = ?")->execute([$oldId]);
            $pdo->prepare("DELETE FROM permissions WHERE id = ?")->execute([$oldId]);
            $pdo->prepare("
                UPDATE permissions
                SET description = 'Allows guests (not logged in) to view and search records in the public directory'
                WHERE id = ?
            ")->execute([$newId]);
        } elseif (!$oldId && !$newId) {
            $pdo->prepare("
                INSERT INTO permissions (permission_key, description)
                VALUES (
                    'view_as_guest',
                    'Allows guests (not logged in) to view and search records in the public directory'
                )
            ")->execute();
        }
    },
];
