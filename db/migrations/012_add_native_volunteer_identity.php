<?php

return [
    'version'     => 12,
    'description' => 'Add native name and email columns to volunteer_submissions for reliable user invitations',
    'up'          => function (PDO $pdo) {
        $pdo->exec("
            ALTER TABLE volunteer_submissions 
            ADD COLUMN name VARCHAR(255) NOT NULL DEFAULT 'Anonymous Volunteer' AFTER id,
            ADD COLUMN email VARCHAR(255) NOT NULL DEFAULT '' AFTER name
        ");
    },
];
