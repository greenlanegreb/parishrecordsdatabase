<?php
// db/scripts/migrate.php - CLI: apply pending database migrations
// Usage (from project root):  php db/scripts/migrate.php
// Or:  php db/scripts/migrate.php --path=/path/to/project

declare(strict_types=1);

$root = dirname(__DIR__, 2);
if (isset($argv)) {
    foreach ($argv as $arg) {
        if (str_starts_with($arg, '--path=')) {
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

$before = get_schema_version($pdo);
echo "Current schema_version: {$before}\n";

try {
    $result = run_pending_migrations($pdo);
} catch (Throwable $e) {
    fwrite(STDERR, "Migration failed: " . $e->getMessage() . "\n");
    exit(1);
}

if (empty($result['applied'])) {
    echo "Already up to date (version {$result['current']}).\n";
} else {
    echo "Applied:\n";
    foreach ($result['applied'] as $line) {
        echo "  - {$line}\n";
    }
    echo "Now at schema_version: {$result['current']}\n";
}

exit(0);
