<?php
declare(strict_types=1);
/**
 * MIGRATED FILE MAPPING
 * ---------------------
 * Original Old File: db/scripts/migrate.php
 * Migrated Date: 2026-08-05 13:00:00
 */

// db/scripts/migrate.php - CLI: apply pending database migrations
// Usage (from project root):  php db/scripts/migrate.php
// Or:  php db/scripts/migrate.php --path=/path/to/project

$root = dirname(__DIR__, 2);
if (isset($argv) && is_array($argv)) {
    foreach ($argv as $arg) {
        if (is_string($arg) && str_starts_with($arg, '--path=')) {
            $root = rtrim(substr($arg, 7), '/');
        }
    }
}

require_once $root . '/db/db.php';
require_once $root . '/includes/functions.php';
require_once $root . '/db/migrate_runner.php';

if (!isset($pdo) || !($pdo instanceof PDO)) {
    fwrite(STDERR, "No database connection (\$pdo).\n");
    exit(1);
}

$before = function_exists('get_schema_version') ? get_schema_version($pdo) : 0;
echo "Current schema_version: {$before}\n";

try {
    /** @var array{applied: array<int, string>, current: int} $result */
    $result = run_pending_migrations($pdo);
} catch (\Throwable $e) {
    fwrite(STDERR, "Migration failed: " . $e->getMessage() . "\n");
    exit(1);
}

$appliedList = isset($result['applied']) && is_array($result['applied']) ? $result['applied'] : [];
$currentVersion = isset($result['current']) ? (int)$result['current'] : 0;

if (empty($appliedList)) {
    echo "Already up to date (version {$currentVersion}).\n";
} else {
    echo "Applied:\n";
    foreach ($appliedList as $line) {
        if (is_string($line)) {
            echo "  - {$line}\n";
        }
    }
    echo "Now at schema_version: {$currentVersion}\n";
}

exit(0);
