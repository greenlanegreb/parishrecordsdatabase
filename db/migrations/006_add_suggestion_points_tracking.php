<?php

return [
    'version'     => 6,
    'description' => 'Add points_awarded tracking flag to edit_suggestions for bulletproof gamification auditing',
    'up'          => function (PDO $pdo) {
        $chk = $pdo->query("SHOW COLUMNS FROM edit_suggestions LIKE 'points_awarded'");
        if (!$chk->fetch()) {
            $pdo->exec("ALTER TABLE edit_suggestions ADD COLUMN points_awarded TINYINT(1) DEFAULT 0 AFTER status");
        }
    },
];
