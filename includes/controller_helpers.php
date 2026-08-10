<?php
declare(strict_types=1);

/**
 * Simple controller helpers – keep them boring and predictable.
 * These wrap existing functions so controllers stay clean.
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
 * Uses the existing require_permission() so behaviour stays identical.
 *
 * @return array{id: int, username: string, ...}
 */
function require_user_permission(PDO $pdo, string $permission, string $description = ''): array
{
    return require_permission($pdo, $permission, $description);
}

/**
 * Require admin page access and return the current user.
 * Uses the existing require_admin_page().
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
        "INSERT INTO audit_logs (user_id, action, details, ip_address) VALUES (?, ?, ?, ?)"
    );
    $stmt->execute([$userId, $action, $details, $ip]);
}
