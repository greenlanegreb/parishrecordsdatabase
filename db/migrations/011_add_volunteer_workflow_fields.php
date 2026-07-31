<?php

return [
    'version'     => 11,
    'description' => 'Add interview status, scheduling, notes, and acceptance workflow to volunteer_submissions',
    'up'          => function (PDO $pdo) {
        $pdo->exec("
            ALTER TABLE volunteer_submissions 
            ADD COLUMN status VARCHAR(50) DEFAULT 'Pending Review' AFTER created_at,
            ADD COLUMN interview_date DATETIME NULL AFTER status,
            ADD COLUMN interview_notes TEXT NULL AFTER interview_date
        ");
    },
];
