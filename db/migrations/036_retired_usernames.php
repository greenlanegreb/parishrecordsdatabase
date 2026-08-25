<?php
declare(strict_types=1);

/**
 * Never re-issue a username after permanent delete (audit clarity).
 * Safe to re-run: CREATE TABLE IF NOT EXISTS.
 */
return [
    'version' => 36,
    'description' => 'Retire usernames on account delete so they are never reused',
    'up' => static function (PDO $pdo): void {
        $pdo->exec("
            CREATE TABLE IF NOT EXISTS retired_usernames (
                id INT NOT NULL AUTO_INCREMENT,
                username VARCHAR(50) NOT NULL,
                former_user_id INT DEFAULT NULL,
                retired_at TIMESTAMP NULL DEFAULT CURRENT_TIMESTAMP,
                PRIMARY KEY (id),
                UNIQUE KEY uniq_retired_username (username),
                KEY idx_retired_former (former_user_id)
            ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci
        ");
    },
];
