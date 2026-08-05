<?php

return [
    'version'     => 5,
    'description' => 'Rename leaderboard_display_mode to attribution_display_mode for unified user attribution privacy settings',
    'up'          => function (PDO $pdo) {
        $chk = $pdo->query("SHOW COLUMNS FROM users LIKE 'leaderboard_display_mode'");
        if ($chk->fetch()) {
            $pdo->exec("ALTER TABLE users CHANGE COLUMN leaderboard_display_mode attribution_display_mode VARCHAR(50) DEFAULT 'initials_random'");
        }
    },
];
