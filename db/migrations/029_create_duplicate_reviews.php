<?php
/**
 * Safe to re-run: CREATE TABLE IF NOT EXISTS.
 * Existing installs get the table via Update database.
 * Fresh installs should use the same CREATE in schema_baseline.sql.
 */
return [
    'version' => 29,
    'description' => 'Create duplicate_reviews table for catch-up queue and merge history',
    'up' => function (PDO $pdo) {
        $pdo->exec("
            CREATE TABLE IF NOT EXISTS duplicate_reviews (
                id INT NOT NULL AUTO_INCREMENT,
                table_id INT NOT NULL,
                record_a_id INT NOT NULL,
                record_b_id INT NOT NULL,
                score_percent INT NOT NULL DEFAULT 0,
                status VARCHAR(20) NOT NULL DEFAULT 'pending',
                reviewed_by INT DEFAULT NULL,
                reviewed_at DATETIME DEFAULT NULL,
                merge_kept_id INT DEFAULT NULL,
                created_at TIMESTAMP NULL DEFAULT CURRENT_TIMESTAMP,
                PRIMARY KEY (id),
                UNIQUE KEY uniq_dup_pair (table_id, record_a_id, record_b_id),
                KEY idx_dup_status (status)
            ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci
        ");
    },
];
