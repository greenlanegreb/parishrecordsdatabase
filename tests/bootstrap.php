<?php
// tests/bootstrap.php - PHPUnit bootstrap for PRD permission smoke tests
declare(strict_types=1);

$autoload = dirname(__DIR__) . '/vendor/autoload.php';
if (!is_file($autoload)) {
    fwrite(STDERR, "Run: php composer.phar install\n");
    exit(1);
}
require_once $autoload;

$_SERVER['REQUEST_URI'] = '/phpunit';
$_SERVER['REQUEST_METHOD'] = 'GET';
$_SERVER['REMOTE_ADDR'] = '127.0.0.1';

// Prefer isolated test DB over application db.php
$testDb = __DIR__ . '/db.php';
if (is_file($testDb)) {
    require_once $testDb;
} else {
    $appDb = dirname(__DIR__) . '/db/db.php';
    if (!is_file($appDb)) {
        fwrite(STDERR, "Missing tests/db.php and db/db.php\n");
        exit(1);
    }
    fwrite(STDERR, "WARNING: using application db.php (not ideal). Prefer tests/db.php\n");
    require_once $appDb;
}

require_once dirname(__DIR__) . '/db/auth_helpers.php';
require_once dirname(__DIR__) . '/includes/functions.php';

if (!isset($pdo) || !($pdo instanceof PDO)) {
    fwrite(STDERR, "Bootstrap failed: \$pdo not available\n");
    exit(1);
}

// Ensure PHPUnit test classes can see the connection
$GLOBALS['pdo'] = $pdo;
