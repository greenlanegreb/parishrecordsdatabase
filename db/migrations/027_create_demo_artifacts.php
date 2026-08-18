<?php
/**
 * Safe to re-run: CREATE TABLE IF NOT EXISTS.
 * Existing installs get the table via Update database.
 * Fresh installs also get it from schema_baseline.sql (same definition).
 */
return [
    'version' => 27,
    'description' => 'Create demo_artifacts table for removable demo packs',
    'up' => function (PDO $pdo) {
        $pdo->exec("
            CREATE TABLE IF NOT EXISTS demo_artifacts (
                id INT UNSIGNED NOT NULL AUTO_INCREMENT PRIMARY KEY,
                pack_slug VARCHAR(64) NOT NULL,
                artifact_type VARCHAR(32) NOT NULL,
                ref_id INT UNSIGNED NOT NULL,
                created_at TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP,
                INDEX idx_demo_pack (pack_slug),
                INDEX idx_demo_type_ref (artifact_type, ref_id)
            ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci
        ");
    },
];
