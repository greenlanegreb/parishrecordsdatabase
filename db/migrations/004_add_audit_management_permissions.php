<?php

return [
    'version'     => 4,
    'description' => 'Add audit trail management and purging permissions',
    'up'          => function (PDO $pdo) {
        // 1. Safely insert purge_audit_entry permission if it doesn't exist
        $stmt1 = $pdo->prepare("SELECT id FROM permissions WHERE permission_key = ?");
        $stmt1->execute(['purge_audit_entry']);
        if (!$stmt1->fetch()) {
            $insert1 = $pdo->prepare("INSERT INTO permissions (permission_key, description) VALUES (?, ?)");
            $insert1->execute(['purge_audit_entry', 'Allows purging individual audit log entries from record history']);
        }

        // 2. Safely insert manage_audit_logs permission if it doesn't exist
        $stmt2 = $pdo->prepare("SELECT id FROM permissions WHERE permission_key = ?");
        $stmt2->execute(['manage_audit_logs']);
        if (!$stmt2->fetch()) {
            $insert2 = $pdo->prepare("INSERT INTO permissions (permission_key, description) VALUES (?, ?)");
            $insert2->execute(['manage_audit_logs', 'Allows viewing and managing the global system-wide audit log trail']);
        }

        // 3. Automatically map these permissions to the Administrator role (role_id = 1)
        $pdo->exec("
            INSERT IGNORE INTO role_permissions (role_id, permission_id)
            SELECT 1, id FROM permissions WHERE permission_key IN ('purge_audit_entry', 'manage_audit_logs')
        ");
    },
];
