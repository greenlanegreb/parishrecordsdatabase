<?php
declare(strict_types=1);

/**
 * Migration 035: clearer permission help text; default onboarding+profile on non-guest roles;
 * does not change security model or wipe custom matrix ticks beyond adding missing defaults.
 */
return [
    'version' => 35,
    'description' => 'Refresh permission descriptions; ensure onboarding and profile on non-guest roles',
    'up' => static function (PDO $pdo): void {
        $descriptions = [
            'view_as_guest' => 'Public (not logged in): view and search records on the home page',
            'edit_records' => 'Direct edit of records and merge of similar records (not the suggestion queue; independent of Moderation module)',
            'delete_records' => 'Direct delete of individual records (permanent; independent of Moderation module)',
            'access_onboarding' => 'First-time setup wizard after invite or first login',
            'access_profile' => 'View and manage own profile and personal settings',
            'access_data_entry' => 'Add and work with records in the data entry workstation',
            'access_suggest_edit' => 'Submit suggested changes for review (uses Moderation queue when that module is on)',
            'moderate_suggestions' => 'Approve, reject, or override suggested edits in the Moderation queue',
        ];

        $upd = $pdo->prepare(
            'UPDATE permissions SET description = ? WHERE permission_key = ?'
        );
        $ins = $pdo->prepare(
            'INSERT IGNORE INTO permissions (permission_key, description) VALUES (?, ?)'
        );
        foreach ($descriptions as $key => $desc) {
            $ins->execute([$key, $desc]);
            $upd->execute([$desc, $key]);
        }

        // Backfill onboarding + profile for every role except guest
        $keys = ['access_onboarding', 'access_profile'];
        $roleStmt = $pdo->query(
            "SELECT id, role_name FROM roles WHERE LOWER(role_name) <> 'guest'"
        );
        if ($roleStmt === false) {
            return;
        }
        $permStmt = $pdo->prepare(
            'SELECT id FROM permissions WHERE permission_key = ? LIMIT 1'
        );
        $mapStmt = $pdo->prepare(
            'INSERT IGNORE INTO role_permissions (role_id, permission_id) VALUES (?, ?)'
        );
        $permIds = [];
        foreach ($keys as $key) {
            $permStmt->execute([$key]);
            $pid = $permStmt->fetchColumn();
            if ($pid !== false && $pid !== null) {
                $permIds[] = (int) $pid;
            }
        }
        while ($role = $roleStmt->fetch(PDO::FETCH_ASSOC)) {
            $rid = (int) ($role['id'] ?? 0);
            if ($rid < 1) {
                continue;
            }
            foreach ($permIds as $pid) {
                $mapStmt->execute([$rid, $pid]);
            }
        }
    },
];
