<?php
// db/migrations/001_baseline.php
// Marks current production-shaped schema as version 1.
// Does not recreate tables — existing installs only get a version stamp.

return [
    'version'     => 1,
    'description' => 'Baseline: record schema_version for existing installs',
    'up'          => function (PDO $pdo) {
        // Ensure site_settings can store the version (table already exists on PRD)
        if (get_schema_version($pdo) < 1) {
            set_schema_version($pdo, 1);
        }
    },
];
