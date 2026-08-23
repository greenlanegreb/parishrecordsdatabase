<?php
declare(strict_types=1);

/**
 * Guest = unauthenticated visitor. Only these keys may be ticked for that role.
 *
 * @return list<string>
 */

/**
 * Any of these lets someone open Settings; each tab is still checked on its own.
 *
 * @return list<string>
 */
function settings_entry_permission_keys(): array
{
    return [
        'manage_settings',
        'manage_audit_logs',
        'view_error_logs',
        'manage_notices',
        'purge_audit_entry',
    ];
}

function user_can_open_settings(PDO $pdo): bool
{
    foreach (settings_entry_permission_keys() as $key) {
        if (function_exists('has_permission') && has_permission($pdo, $key)) {
            return true;
        }
    }
    return false;
}

function guest_allowed_permission_keys(): array
{
    return [
        'view_as_guest',
        'export_data',
        'view_leaderboard',
        'access_suggest_edit',
        'submit_feedback',
        'submit_volunteer',
    ];
}

/**
 * Guest may also be given view_table_N as tables are created (not moderate_table_N).
 */
function guest_may_hold_permission(string $key): bool
{
    if (in_array($key, guest_allowed_permission_keys(), true)) {
        return true;
    }
    return preg_match('/^view_table_[0-9]+$/', $key) === 1;
}

/**
 * Child key => parent key. Dynamic table pairs: moderate_table_N → view_table_N.
 *
 * @return array<string, string>
 */
function permission_dependencies(): array
{
    return [
        'manage_columns' => 'manage_tables',
    ];
}

function permission_parent_key(string $key): ?string
{
    $fixed = permission_dependencies();
    if (isset($fixed[$key])) {
        return $fixed[$key];
    }
    if (preg_match('/^moderate_table_(\d+)$/', $key, $m) === 1) {
        return 'view_table_' . $m[1];
    }
    return null;
}

function is_guest_role_name(string $name): bool
{
    return strtolower(trim($name)) === 'guest';
}

/**
 * Remove guest-role mappings that are not on the public-visitor allow-list.
 */
function prune_guest_role_permissions(PDO $pdo): void
{
    $roleStmt = $pdo->query("SELECT id FROM roles WHERE LOWER(role_name) = 'guest' LIMIT 1");
    $roleId = $roleStmt !== false ? (int) $roleStmt->fetchColumn() : 0;
    if ($roleId < 1) {
        return;
    }
    $allowed = guest_allowed_permission_keys();
    $in = implode(',', array_fill(0, count($allowed), '?'));
    $sql = "DELETE rp FROM role_permissions rp
            INNER JOIN permissions p ON p.id = rp.permission_id
            WHERE rp.role_id = ?
              AND p.permission_key NOT IN ({$in})
              AND p.permission_key NOT REGEXP '^view_table_[0-9]+$'";
    $stmt = $pdo->prepare($sql);
    $stmt->execute(array_merge([$roleId], $allowed));
}
