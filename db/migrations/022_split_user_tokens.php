<?php
// db/migrations/022_split_user_tokens.php

return [
    'version'     => 22,
    'description' => 'Split verification_token into invite_token/invite_expires_at and reset_token/reset_expires_at',
    'up'          => function (PDO $pdo) {
        $pdo->beginTransaction();
        try {
            // 1. Add new invitation token columns
            $pdo->exec("ALTER TABLE users ADD COLUMN invite_token varchar(64) DEFAULT NULL AFTER email_verified");
            $pdo->exec("ALTER TABLE users ADD COLUMN invite_expires_at datetime DEFAULT NULL AFTER invite_token");

            // 2. Add new password reset token columns
            $pdo->exec("ALTER TABLE users ADD COLUMN reset_token varchar(64) DEFAULT NULL AFTER invite_expires_at");
            $pdo->exec("ALTER TABLE users ADD COLUMN reset_expires_at datetime DEFAULT NULL AFTER reset_token");

            // 3. Drop old unified columns
            $pdo->exec("ALTER TABLE users DROP COLUMN verification_token");
            $pdo->exec("ALTER TABLE users DROP COLUMN token_expires_at");

            $pdo->commit();
        } catch (Exception $e) {
            $pdo->rollBack();
            throw $e;
        }
    },
];
