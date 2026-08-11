<?php
declare(strict_types=1);

/**
 * Public volunteer username-check rate limiting (enumeration protection only).
 * Not used for normal form submit or admin user create uniqueness.
 */

if (!function_exists('has_exceeded_username_check_limit')) {
    function has_exceeded_username_check_limit(PDO $pdo): bool
    {
        $ip = isset($_SERVER['REMOTE_ADDR']) && is_string($_SERVER['REMOTE_ADDR'])
            ? $_SERVER['REMOTE_ADDR'] : '0.0.0.0';
        try {
            $stmt = $pdo->prepare(
                "SELECT COUNT(*) FROM audit_logs
                 WHERE ip_address = ?
                   AND action = 'USERNAME_CHECK_ATTEMPT'
                   AND created_at >= (NOW() - INTERVAL 24 HOUR)"
            );
            $stmt->execute([$ip]);
            return (int) $stmt->fetchColumn() >= 3;
        } catch (Exception $e) {
            return false;
        }
    }
}

if (!function_exists('log_username_check_attempt')) {
    function log_username_check_attempt(PDO $pdo): void
    {
        $ip = isset($_SERVER['REMOTE_ADDR']) && is_string($_SERVER['REMOTE_ADDR'])
            ? $_SERVER['REMOTE_ADDR'] : '0.0.0.0';
        try {
            $stmt = $pdo->prepare(
                "INSERT INTO audit_logs (user_id, action, details, ip_address)
                 VALUES (NULL, 'USERNAME_CHECK_ATTEMPT', ?, ?)"
            );
            $stmt->execute(['Volunteer public username availability check', $ip]);
        } catch (Exception $e) {
            // non-fatal
        }
    }
}
