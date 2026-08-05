<?php
declare(strict_types=1);
/**
 * MIGRATED FILE MAPPING
 * ---------------------
 * Original Old File: db/maintenance_guard.php
 * Migrated Date: 2026-08-05 12:25:00
 */

// db/maintenance_guard.php - Global interceptor for site maintenance mode

/**
 * Check if the site is in maintenance mode and block non-privileged visitors.
 *
 * @param \PDO $pdo
 * @return void
 */
function check_maintenance_mode(\PDO $pdo): void
{
    // Fetch maintenance settings safely
    $settings = [];
    try {
        $stmt = $pdo->query("SELECT setting_key, setting_value FROM site_settings WHERE setting_key IN ('maintenance_mode', 'maintenance_reason', 'maintenance_eta')");
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
        // Fallback if table or keys don't exist yet
        return;
    }

    $isOffline = isset($settings['maintenance_mode']) && $settings['maintenance_mode'] === '1';

    if ($isOffline) {
        // Check if user has permission to manage settings/bypass maintenance mode
        $canBypass = false;
        if (isset($_SESSION['user_id'])) {
            // Ensure auth helpers are available for permission checking
            if (!function_exists('has_permission') && file_exists(__DIR__ . '/auth_helpers.php')) {
                require_once __DIR__ . '/auth_helpers.php';
            }
            if (function_exists('has_permission')) {
                $canBypass = has_permission($pdo, 'manage_settings', 'Manage global site settings and bypass maintenance mode');
            }
        }

        // Allow authorized administrators, admin panel scripts, or login/auth pages to bypass
        $requestUri = isset($_SERVER['REQUEST_URI']) && is_string($_SERVER['REQUEST_URI']) ? $_SERVER['REQUEST_URI'] : '';
        $serverSelf = isset($_SERVER['PHP_SELF']) && is_string($_SERVER['PHP_SELF']) ? $_SERVER['PHP_SELF'] : '';
        $currentScript = basename($serverSelf);
        
        $isAdminArea = (strpos($requestUri, '/admin/') !== false);
        $allowedScripts = ['login.php', 'logout.php', 'authenticate.php'];
        
        if (!$canBypass && !$isAdminArea && !in_array($currentScript, $allowedScripts, true)) {
            $reason = isset($settings['maintenance_reason']) && $settings['maintenance_reason'] !== '' ? $settings['maintenance_reason'] : 'System maintenance is currently underway.';
            $eta = isset($settings['maintenance_eta']) && $settings['maintenance_eta'] !== '' ? $settings['maintenance_eta'] : 'Shortly';
            
            // Use dynamic BASE_PATH constant defined in db.php
            $basePath = defined('BASE_PATH') && is_string(BASE_PATH) ? BASE_PATH : '';
            
            // Render a clean maintenance page with a 503 HTTP status code
            if (!headers_sent()) {
                http_response_code(503);
            }
            ?>
            <!DOCTYPE html>
            <html lang="en">
            <head>
                <meta charset="UTF-8">
                <title>Site Under Maintenance</title>
                <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
            </head>
            <body class="bg-light d-flex align-items-center justify-content-center min-vh-100">
                <div class="container" style="max-width: 550px;">
                    <div class="card border-0 shadow-sm p-5 text-center bg-white" role="alert">
                        <h2 class="h4 fw-bold text-danger mb-3">System Offline for Maintenance</h2>
                        <p class="text-secondary mb-4" style="line-height: 1.5;"><?= htmlspecialchars($reason, ENT_QUOTES, 'UTF-8') ?></p>
                        <div class="alert alert-warning bg-warning bg-opacity-10 border-0 fw-semibold text-dark py-2 mb-4">
                            Expected Return Time: <?= htmlspecialchars($eta, ENT_QUOTES, 'UTF-8') ?>
                        </div>
                        <p class="small text-muted mb-0">Thank you for your patience while we improve your experience.</p>
                    </div>
                </div>
            </body>
            </html>
            <?php
            exit;
        }
    }
}
