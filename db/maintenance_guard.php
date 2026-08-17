<?php
declare(strict_types=1);
/**
 * db/maintenance_guard.php — Global interceptor for site maintenance mode.
 *
 * Called early after PDO is available (config.local / db bootstrap).
 * Not a controller: cross-cutting gate, same layer as session/auth helpers.
 *
 * Bypass when:
 * - Logged-in user has manage_settings, or
 * - Request is an auth-related front-controller route (/login, /logout, …), or
 * - Request is under /admin (so settings can be reached once logged in), or
 * - Emergency migrator flag is present and path is /update-database
 */

/**
 * Normalize request path relative to BASE_PATH (no query string).
 */
function maintenance_request_path(): string
{
    $uri = isset($_SERVER['REQUEST_URI']) && is_string($_SERVER['REQUEST_URI'])
        ? $_SERVER['REQUEST_URI'] : '/';
    $q = strpos($uri, '?');
    if ($q !== false) {
        $uri = substr($uri, 0, $q);
    }
    $uri = rawurldecode($uri);
    $uri = str_replace('\\', '/', $uri);

    $base = defined('BASE_PATH') && is_string(BASE_PATH) ? rtrim(BASE_PATH, '/') : '';
    if ($base !== '' && ($uri === $base || str_starts_with($uri, $base . '/'))) {
        $uri = substr($uri, strlen($base)) ?: '/';
    }
    if ($uri === '' || $uri === false) {
        $uri = '/';
    }
    // Strip trailing slash except root
    if ($uri !== '/' && str_ends_with($uri, '/')) {
        $uri = rtrim($uri, '/');
    }
    return $uri;
}

/**
 * Routes that must stay reachable while the site is offline (login recovery).
 *
 * @return list<string>
 */
function maintenance_allowed_path_prefixes(): array
{
    return [
        '/login',
        '/logout',
        '/register',
        '/forgot-password',
        '/user/set-password',
        '/user/verify-2fa',
        '/user/verify-email',
        '/setup-2fa',
        // Legacy script names if anything still hits them directly
        '/login.php',
        '/logout.php',
        '/authenticate.php',
    ];
}

/**
 * True if this request should not be blocked by maintenance mode.
 */
function maintenance_path_is_allowed(string $path): bool
{
    foreach (maintenance_allowed_path_prefixes() as $prefix) {
        if ($path === $prefix || str_starts_with($path, $prefix . '/')) {
            return true;
        }
    }
    // Admin UI (once authenticated, manage_settings users can turn maintenance off)
    if ($path === '/admin' || str_starts_with($path, '/admin/')) {
        return true;
    }
    // Emergency migrator (same flag as UpdateDatabaseController)
    if ($path === '/update-database' || str_starts_with($path, '/update-database/')) {
        // Same flag as UpdateDatabaseController (db/ALLOW_EMERGENCY_MIGRATE)
        if (is_file(__DIR__ . '/ALLOW_EMERGENCY_MIGRATE')) {
            return true;
        }
    }
    return false;
}

/**
 * Check if the site is in maintenance mode and block non-privileged visitors.
 *
 * @param \PDO $pdo
 */
function check_maintenance_mode(\PDO $pdo): void
{
    $settings = [];
    try {
        $stmt = $pdo->query(
            "SELECT setting_key, setting_value FROM site_settings
             WHERE setting_key IN ('maintenance_mode', 'maintenance_reason', 'maintenance_eta')"
        );
        if ($stmt !== false) {
            while ($row = $stmt->fetch(\PDO::FETCH_ASSOC)) {
                $k = isset($row['setting_key']) && is_string($row['setting_key']) ? $row['setting_key'] : '';
                $v = isset($row['setting_value']) && is_string($row['setting_value']) ? $row['setting_value'] : '';
                if ($k !== '') {
                    $settings[$k] = $v;
                }
            }
        }
    } catch (\PDOException $e) {
        // Schema not ready — do not block install / early boot
        return;
    }

    $isOffline = isset($settings['maintenance_mode']) && $settings['maintenance_mode'] === '1';
    if (!$isOffline) {
        return;
    }

    $canBypass = false;
    if (isset($_SESSION['user_id'])) {
        if (!function_exists('has_permission') && is_file(__DIR__ . '/auth_helpers.php')) {
            require_once __DIR__ . '/auth_helpers.php';
        }
        if (function_exists('has_permission')) {
            $canBypass = has_permission(
                $pdo,
                'manage_settings',
                'Manage global site settings and bypass maintenance mode'
            );
        }
    }

    $path = maintenance_request_path();
    if ($canBypass || maintenance_path_is_allowed($path)) {
        return;
    }

    $reason = (isset($settings['maintenance_reason']) && $settings['maintenance_reason'] !== '')
        ? $settings['maintenance_reason']
        : 'System maintenance is currently underway.';
    $eta = (isset($settings['maintenance_eta']) && $settings['maintenance_eta'] !== '')
        ? $settings['maintenance_eta']
        : 'Shortly';

    if (!headers_sent()) {
        http_response_code(503);
        header('Retry-After: 3600');
    }
    ?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>Site Under Maintenance</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
</head>
<body class="bg-light d-flex align-items-center justify-content-center min-vh-100">
    <div class="container" style="max-width: 550px;">
        <div class="card border-0 shadow-sm p-5 text-center bg-white" role="alert">
            <h1 class="h4 fw-bold text-danger mb-3">System Offline for Maintenance</h1>
            <p class="text-secondary mb-4" style="line-height: 1.5;"><?= htmlspecialchars($reason, ENT_QUOTES, 'UTF-8') ?></p>
            <div class="alert alert-warning bg-warning bg-opacity-10 border-0 fw-semibold text-dark py-2 mb-4">
                Expected return: <?= htmlspecialchars($eta, ENT_QUOTES, 'UTF-8') ?>
            </div>
            <p class="small text-muted mb-0">Thank you for your patience.</p>
        </div>
    </div>
</body>
</html>
    <?php
    exit;
}
