<?php
// db/migrate_runner.php - Apply pending schema migrations
declare(strict_types=1);

/**
 * Load migration files from db/migrations/ (NNN_description.php).
 * Each file returns: ['version' => int, 'description' => string, 'up' => callable]
 *
 * @return array<int, array{version:int,description:string,up:callable,file:string}>
 */
function load_migrations(string $migrationsDir): array {
    $list = [];
    if (!is_dir($migrationsDir)) {
        return $list;
    }
    foreach (glob($migrationsDir . '/*.php') as $file) {
        $base = basename($file);
        if (!preg_match('/^(\d+)_/', $base, $m)) {
            continue;
        }
        $migration = include $file;
        if (!is_array($migration) || !isset($migration['version'], $migration['up']) || !is_callable($migration['up'])) {
            throw new RuntimeException("Invalid migration file: {$base}");
        }
        $version = (int) $migration['version'];
        $list[$version] = [
            'version'     => $version,
            'description' => (string) ($migration['description'] ?? $base),
            'up'          => $migration['up'],
            'file'        => $base,
        ];
    }
    ksort($list, SORT_NUMERIC);
    return $list;
}

/**
 * Run all migrations with version > current schema_version.
 *
 * @return array{applied: list<string>, current: int}
 */
function run_pending_migrations(PDO $pdo, ?string $migrationsDir = null): array {
    $migrationsDir = $migrationsDir ?? (__DIR__ . '/migrations');
    $current = get_schema_version($pdo);
    $all = load_migrations($migrationsDir);
    $applied = [];

    foreach ($all as $version => $migration) {
        if ($version <= $current) {
            continue;
        }
        ($migration['up'])($pdo);
        set_schema_version($pdo, $version);
        $current = $version;
        $applied[] = sprintf(
            'v%d: %s (%s)',
            $version,
            $migration['description'],
            $migration['file']
        );
    }

    return ['applied' => $applied, 'current' => $current];
}
