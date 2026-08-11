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
