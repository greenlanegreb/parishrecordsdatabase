<?php
declare(strict_types=1);
/**
 * CLI: clear expired invite / reset tokens.
 * Usage: php db/cron_token_cleanup.php
 */
if (PHP_SAPI !== 'cli') {
    fwrite(STDERR, "This script is for the command line only.\n");
    exit(1);
}

$root = dirname(__DIR__);
$config = $root . '/config.local.php';
if (!is_file($config)) {
    fwrite(STDERR, "config.local.php not found.\n");
    exit(1);
}
require $config;

$autoload = [
    $root . '/includes/init.php',
];
foreach ($autoload as $f) {
    if (is_file($f)) {
        require_once $f;
    }
}

$pdo = $pdo ?? ($GLOBALS['pdo'] ?? null);
if (!$pdo instanceof PDO) {
    if (!defined('DB_HOST') || !defined('DB_NAME') || !defined('DB_USER')) {
        fwrite(STDERR, "Database settings missing.\n");
        exit(1);
    }
    $pass = defined('DB_PASS') ? DB_PASS : '';
    $pdo = new PDO(
        'mysql:host=' . DB_HOST . ';dbname=' . DB_NAME . ';charset=utf8mb4',
        DB_USER,
        $pass,
        [PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION]
    );
}

require_once $root . '/app/Services/TokenCleanupService.php';

try {
    $svc = new \App\Services\TokenCleanupService($pdo);
    $n = $svc->run();
    $details = sprintf(
        'Token maintenance executed successfully. Purged %d expired tokens (%d invite, %d reset) and %d leftover invite tokens on activated accounts.',
        $n['invite'] + $n['reset'],
        $n['invite'],
        $n['reset'],
        $n['stale_invite']
    );
    $svc->audit(null, '127.0.0.1', 'TOKEN_CLEANUP_SUCCESS', $details);
    echo '[SUCCESS] ' . $details . PHP_EOL;
    exit(0);
} catch (Throwable $e) {
    fwrite(STDERR, '[ERROR] Token maintenance failed: ' . $e->getMessage() . PHP_EOL);
    exit(1);
}
