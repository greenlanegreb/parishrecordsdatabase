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
function require_user_permission(PDO $pdo, string $permission, string $description = ''): array
{
    return require_permission($pdo, $permission, $description);
}
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
            $fail('');
        }
    }
}
function post_login_destination_path(PDO $pdo): string
{
    $base = defined('BASE_PATH') && is_string(BASE_PATH) ? rtrim(BASE_PATH, '/') : '';
    if (function_exists('has_permission') && has_permission($pdo, 'access_data_entry')) {
        return $base . '/data-entry';
    }
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
        }
    }
    if (function_exists('has_permission') && has_permission($pdo, 'view_as_guest')) {
        return $base !== '' ? $base . '/' : '/';
    }
    if (function_exists('has_permission') && has_permission($pdo, 'access_profile')) {
        return $base . '/profile';
    }
    return $base !== '' ? $base . '/' : '/';
}
function role_display_name(string $roleName): string
{
    $key = strtolower(trim($roleName));
    $map = [
        'user' => function_exists('__') && __('role.label_user') !== 'role.label_user'
            ? __('role.label_user') : 'Data Entry User',
        'guest' => function_exists('__') && __('role.label_guest') !== 'role.label_guest'
            ? __('role.label_guest') : 'Public Visitor',
        'admin' => function_exists('__') && __('role.label_admin') !== 'role.label_admin'
            ? __('role.label_admin') : 'Administrator',
        'moderator' => function_exists('__') && __('role.label_moderator') !== 'role.label_moderator'
            ? __('role.label_moderator') : 'Moderator',
    ];
    $out = $map[$key] ?? str_replace('_', ' ', $roleName);
    if ($out === '') {
        $out = $roleName;
    }
    return function_exists('prd_title_case') ? prd_title_case($out) : $out;
}
function permission_display_label(string $permissionKey): string
{
    $key = strtolower(trim($permissionKey));
    $map = [
        'view_as_guest' => function_exists('__') && __('perm.label_view_as_guest') !== 'perm.label_view_as_guest'
            ? __('perm.label_view_as_guest') : 'Public Catalogue (Home Page Search)',
        'access_data_entry' => function_exists('__') && __('perm.label_access_data_entry') !== 'perm.label_access_data_entry'
            ? __('perm.label_access_data_entry') : 'Data Entry Workstation',
        'access_onboarding' => function_exists('__') && __('perm.label_access_onboarding') !== 'perm.label_access_onboarding'
            ? __('perm.label_access_onboarding') : 'First-Time Setup Wizard',
        'access_profile' => function_exists('__') && __('perm.label_access_profile') !== 'perm.label_access_profile'
            ? __('perm.label_access_profile') : 'Own Profile Settings',
        'edit_records' => function_exists('__') && __('perm.label_edit_records') !== 'perm.label_edit_records'
            ? __('perm.label_edit_records') : 'Direct Edit and Merge Records',
        'delete_records' => function_exists('__') && __('perm.label_delete_records') !== 'perm.label_delete_records'
            ? __('perm.label_delete_records') : 'Direct Delete Records',
        'access_suggest_edit' => function_exists('__') && __('perm.label_access_suggest_edit') !== 'perm.label_access_suggest_edit'
            ? __('perm.label_access_suggest_edit') : 'Suggest an Edit',
        'moderate_suggestions' => function_exists('__') && __('perm.label_moderate_suggestions') !== 'perm.label_moderate_suggestions'
            ? __('perm.label_moderate_suggestions') : 'Review Suggestion Queue',
    ];
    $out = $map[$key] ?? ucwords(str_replace('_', ' ', $permissionKey));
    return function_exists('prd_title_case') ? prd_title_case($out) : $out;
}
