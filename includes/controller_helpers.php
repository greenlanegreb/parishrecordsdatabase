<?php
declare(strict_types=1);

/**
 * Simple controller helpers go here.
 */

function require_module(PDO $pdo, string $moduleKey): void
{
    if (!is_module_enabled($pdo, $moduleKey)) {
        http_response_code(403);
        exit('403 Forbidden: This module is currently disabled.');
    }
}

/**
 * Require a permission and return the current user.
 *
 * @return array{id: int, username: string, ...}
 */
function require_user_permission(PDO $pdo, string $permission, string $description = ''): array
{
    return require_permission($pdo, $permission, $description);
}

/**
 * Require admin page access and return the current user.
 *
 * @return array{id: int, username: string, ...}
 */
function require_user_admin(PDO $pdo, string $permission, string $description = ''): array
{
    return require_admin_page($pdo, $permission, $description);
}

function flash_success(string $message): void
{
    $_SESSION['message'] = $message;
}

function flash_error(string $message): void
{
    $_SESSION['error'] = $message;
}

function redirect(string $path): never
{
    $base = defined('BASE_PATH') ? rtrim((string) BASE_PATH, '/') : '';
    header('Location: ' . $base . $path);
    exit;
}

function audit(PDO $pdo, int $userId, string $action, string $details, ?string $ip = null): void
{
    $ip = $ip ?? (isset($_SERVER['REMOTE_ADDR']) && is_string($_SERVER['REMOTE_ADDR'])
        ? $_SERVER['REMOTE_ADDR']
        : '127.0.0.1');

    $stmt = $pdo->prepare(
        'INSERT INTO audit_logs (user_id, action, details, ip_address) VALUES (?, ?, ?, ?)'
    );
    $stmt->execute([$userId, $action, $details, $ip]);
}

/**
 * Public form gate: CSRF + firewall + CAPTCHA + honeypot.
 * On failure redirects to $redirectPath and exits.
 *
 * @param list<string> $honeypotFields Field names that must be empty (spam traps)
 */
function require_public_form_security(
    PDO $pdo,
    string $redirectPath,
    array $honeypotFields = ['website_hp', 'website_url']
): void {
    verify_csrf_token();

    $securityEngine = __DIR__ . '/security_engine.php';
    if (is_file($securityEngine)) {
        require_once $securityEngine;
    }

    $fail = static function (string $error = '') use ($redirectPath): void {
        if ($error !== '') {
            $_SESSION['error'] = $error;
        }
        redirect($redirectPath);
    };

    if (function_exists('run_form_firewall_check')) {
        $fw = run_form_firewall_check($pdo);
        if ($fw !== true) {
            $fail(is_string($fw) ? $fw : 'Firewall block triggered.');
        }
    }

    if (function_exists('verify_form_captcha')) {
        $cap = verify_form_captcha($pdo);
        if ($cap !== true) {
            $fail(is_string($cap) ? $cap : 'CAPTCHA verification failed.');
        }
    }

    foreach ($honeypotFields as $field) {
        if (!is_string($field) || $field === '') {
            continue;
        }
        $v = $_POST[$field] ?? '';
        if (is_string($v) && trim($v) !== '') {
            // Silent redirect — do not teach bots what failed
            $fail('');
        }
    }
}

/**
 * After login / onboarding / 2FA: first useful page the user is allowed to open.
 * Prefer data entry for contributors; otherwise home (read) or profile — avoids 403
 * when a need-to-know reader has no access_data_entry.
 */
function post_login_destination_path(PDO $pdo): string
{
    $base = defined('BASE_PATH') && is_string(BASE_PATH) ? rtrim(BASE_PATH, '/') : '';

    if (function_exists('has_permission') && has_permission($pdo, 'access_data_entry')) {
        return $base . '/data-entry';
    }

    // Any dynamic table this user may read → home / catalogue
    if (function_exists('user_can_view_table')) {
        try {
            $stmt = $pdo->query('SELECT id FROM dynamic_tables ORDER BY id ASC');
            if ($stmt !== false) {
                while ($row = $stmt->fetch(PDO::FETCH_ASSOC)) {
                    $tid = isset($row['id']) ? (int) $row['id'] : 0;
                    if ($tid > 0 && user_can_view_table($pdo, $tid)) {
                        return $base !== '' ? $base . '/' : '/';
                    }
                }
            }
        } catch (Throwable $e) {
            // ignore schema issues during early setup
        }
    }

    if (function_exists('has_permission') && has_permission($pdo, 'view_as_guest')) {
        return $base !== '' ? $base . '/' : '/';
    }

    if (function_exists('has_permission') && has_permission($pdo, 'access_profile')) {
        return $base . '/profile';
    }

    // Last resort: home (may still show a guided empty/403 state rather than data-entry)
    return $base !== '' ? $base . '/' : '/';
}

/**
 * Friendly label for a role_name (machine key unchanged in the database).
 */
function role_display_name(string $roleName): string
{
    $key = strtolower(trim($roleName));
    $map = [
        'user' => function_exists('__') && __('role.label_user') !== 'role.label_user'
            ? __('role.label_user')
            : 'Data entry user',
        'guest' => function_exists('__') && __('role.label_guest') !== 'role.label_guest'
            ? __('role.label_guest')
            : 'Public visitor',
        'admin' => function_exists('__') && __('role.label_admin') !== 'role.label_admin'
            ? __('role.label_admin')
            : 'Administrator',
        'moderator' => function_exists('__') && __('role.label_moderator') !== 'role.label_moderator'
            ? __('role.label_moderator')
            : 'Moderator',
    ];
    if (isset($map[$key])) {
        return $map[$key];
    }
    // Custom roles: humanise underscores
    $pretty = str_replace('_', ' ', $roleName);
    return $pretty !== '' ? $pretty : $roleName;
}

/**
 * Friendly matrix label for a permission_key (key unchanged in the database).
 */
function permission_display_label(string $permissionKey): string
{
    $key = strtolower(trim($permissionKey));
    $map = [
        'view_as_guest' => function_exists('__') && __('perm.label_view_as_guest') !== 'perm.label_view_as_guest'
            ? __('perm.label_view_as_guest')
            : 'Public catalogue (home page search)',
        'access_data_entry' => function_exists('__') && __('perm.label_access_data_entry') !== 'perm.label_access_data_entry'
            ? __('perm.label_access_data_entry')
            : 'Data entry workstation',
        'access_onboarding' => function_exists('__') && __('perm.label_access_onboarding') !== 'perm.label_access_onboarding'
            ? __('perm.label_access_onboarding')
            : 'First-time setup wizard',
        'access_profile' => function_exists('__') && __('perm.label_access_profile') !== 'perm.label_access_profile'
            ? __('perm.label_access_profile')
            : 'Own profile settings',
        'edit_records' => function_exists('__') && __('perm.label_edit_records') !== 'perm.label_edit_records'
            ? __('perm.label_edit_records')
            : 'Direct edit and merge records',
        'delete_records' => function_exists('__') && __('perm.label_delete_records') !== 'perm.label_delete_records'
            ? __('perm.label_delete_records')
            : 'Direct delete records',
        'access_suggest_edit' => function_exists('__') && __('perm.label_access_suggest_edit') !== 'perm.label_access_suggest_edit'
            ? __('perm.label_access_suggest_edit')
            : 'Suggest an edit',
        'moderate_suggestions' => function_exists('__') && __('perm.label_moderate_suggestions') !== 'perm.label_moderate_suggestions'
            ? __('perm.label_moderate_suggestions')
            : 'Review suggestion queue',
    ];
    if (isset($map[$key])) {
        return $map[$key];
    }
    return ucwords(str_replace('_', ' ', $permissionKey));
}
