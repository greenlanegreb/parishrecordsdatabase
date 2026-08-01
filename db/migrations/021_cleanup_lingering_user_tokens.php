<?php
// db/migrations/021_cleanup_lingering_user_tokens.php

return [
    'version'     => 21,
    'description' => 'Cleanup lingering verification tokens for expired links and already-activated users',
    'up'          => function (PDO $pdo) {
        $pdo->beginTransaction();
        try {
            // 1. Clear tokens where the expiration date has already passed
            $expiredStmt = $pdo->prepare("
                UPDATE users 
                SET verification_token = NULL, token_expires_at = NULL 
                WHERE token_expires_at IS NOT NULL AND token_expires_at < NOW()
            ");
            $expiredStmt->execute();
            $expiredCount = $expiredStmt->rowCount();

            // 2. Clear lingering tokens for users who have already finished setup / activated their account (is_new_user = 0)
            $activatedStmt = $pdo->prepare("
                UPDATE users 
                SET verification_token = NULL, token_expires_at = NULL 
                WHERE is_new_user = 0 AND verification_token IS NOT NULL
            ");
            $activatedStmt->execute();
            $activatedCount = $activatedStmt->rowCount();

            // 3. Log this maintenance cleanup matching the exact audit_logs schema (action, details, ip address, created at)
            $tableCheck = $pdo->query("SHOW TABLES LIKE 'audit_logs'");
            if ($tableCheck->rowCount() > 0) {
                $logStmt = $pdo->prepare("
                    INSERT INTO audit_logs (`action`, `details`, `ip address`, `created at`) 
                    VALUES (?, ?, ?, NOW())
                ");
                $logStmt->execute([
                    'MIGRATION_CLEANUP', 
                    sprintf('Migration 21 purged %d expired tokens and %d lingering activated-user tokens.', $expiredCount, $activatedCount),
                    '127.0.0.1'
                ]);
            }

            $pdo->commit();
        } catch (Exception $e) {
            $pdo->rollBack();
            throw $e;
        }
    },
];
