<?php

return [
    'version'     => 8,
    'description' => 'Add max_length column to feedback_columns table for ticket form field length limits',
    'up'          => function (PDO $pdo) {
        $chk = $pdo->query("SHOW COLUMNS FROM feedback_columns LIKE 'max_length'");
        if (!$chk->fetch()) {
            $pdo->exec("ALTER TABLE feedback_columns ADD COLUMN max_length INT NULL AFTER data_type");
        }
    },
];
