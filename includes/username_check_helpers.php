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

/**
 * True if this username may be given to a new account (not live, not retired).
 */
if (!function_exists('is_username_available')) {
    function is_username_available(PDO $pdo, string $username, ?int $exceptUserId = null): bool
    {
        $username = trim($username);
        if ($username === '') {
            return false;
        }
        try {
            if ($exceptUserId !== null && $exceptUserId > 0) {
                $stmt = $pdo->prepare('SELECT id FROM users WHERE username = ? AND id != ? LIMIT 1');
                $stmt->execute([$username, $exceptUserId]);
            } else {
                $stmt = $pdo->prepare('SELECT id FROM users WHERE username = ? LIMIT 1');
                $stmt->execute([$username]);
            }
            if ($stmt->fetch() !== false) {
                return false;
            }
            $retired = $pdo->prepare('SELECT id FROM retired_usernames WHERE username = ? LIMIT 1');
            $retired->execute([$username]);
            if ($retired->fetch() !== false) {
                return false;
            }
        } catch (\Throwable $e) {
            // Table missing before migration 036: fall back to live users only
            $stmt = $pdo->prepare('SELECT id FROM users WHERE username = ? LIMIT 1');
            $stmt->execute([$username]);
            return $stmt->fetch() === false;
        }
        return true;
    }
}

/**
 * Record a username so it can never be issued again after delete.
 */
if (!function_exists('retire_username')) {
    function retire_username(PDO $pdo, string $username, ?int $formerUserId = null): void
    {
        $username = trim($username);
        if ($username === '') {
            return;
        }
        try {
            $stmt = $pdo->prepare(
                'INSERT IGNORE INTO retired_usernames (username, former_user_id) VALUES (?, ?)'
            );
            $stmt->execute([$username, $formerUserId]);
        } catch (\Throwable $e) {
            // non-fatal if migration not applied yet
        }
    }
}

/**
 * Build a unique username from first + surname (never reuses live or retired names).
 */
if (!function_exists('allocate_unique_username')) {
    function allocate_unique_username(PDO $pdo, string $firstName, string $surname): string
    {
        $cleanedFirst = preg_replace('/[^a-zA-Z]/', '', $firstName) ?? '';
        $cleanedSurname = preg_replace('/[^a-zA-Z]/', '', $surname) ?? '';
        $base = strtolower(substr($cleanedFirst, 0, 1) . $cleanedSurname);
        if ($base === '') {
            $base = 'user';
        }
        $username = $base;
        $counter = 1;
        while (!is_username_available($pdo, $username)) {
            $username = $base . $counter;
            $counter++;
            if ($counter > 10000) {
                $username = $base . bin2hex(random_bytes(3));
                break;
            }
        }
        return $username;
    }
}
