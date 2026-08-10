<?php
return [
    'version' => 26,
    'description' => 'Add preferred_username to volunteer_submissions',
    'up' => function (PDO $pdo) {
        $pdo->exec("
            ALTER TABLE volunteer_submissions
            ADD COLUMN preferred_username VARCHAR(100) NULL DEFAULT NULL
            AFTER email
        ");
    },
];
