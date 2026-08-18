<?php
/**
 * Safe to re-run: ADD COLUMN IF NOT EXISTS.
 */
return [
    'version' => 28,
    'description' => 'Choice-list options, multi-select flag, and integer min/max on table_columns',
    'up' => function (PDO $pdo) {
        $pdo->exec("
            ALTER TABLE table_columns
              ADD COLUMN IF NOT EXISTS field_options TEXT NULL,
              ADD COLUMN IF NOT EXISTS allow_multiple TINYINT(1) NOT NULL DEFAULT 0,
              ADD COLUMN IF NOT EXISTS min_value INT NULL DEFAULT NULL,
              ADD COLUMN IF NOT EXISTS max_value INT NULL DEFAULT NULL
        ");
    },
];
