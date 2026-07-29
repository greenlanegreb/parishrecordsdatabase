<?php
// db/migrations/002_add_reasoning_to_edit_suggestions.php

return [
    'version'     => 2,
    'description' => 'Add reasoning column to edit_suggestions table for user evidence and rationale',
    'up'          => function (PDO $pdo) {
        // Idempotent check: safely add the column if it doesn't already exist
        $stmt = $pdo->query("SHOW COLUMNS FROM edit_suggestions LIKE 'reasoning'");
        if (!$stmt->fetch()) {
            $pdo->exec("
                ALTER TABLE edit_suggestions
                ADD COLUMN reasoning TEXT NULL AFTER proposed_value
            ");
        }
    },
];
