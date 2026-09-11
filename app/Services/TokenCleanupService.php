<?php
declare(strict_types=1);

namespace App\Services;

use PDO;

class TokenCleanupService
{
    public function __construct(private PDO $pdo)
    {
    }

    /**
     * @return array{invite:int,reset:int,stale_invite:int}
     */
    public function run(): array
    {
        $invite = $this->pdo->prepare(
            'UPDATE users SET invite_token = NULL, invite_expires_at = NULL
             WHERE invite_expires_at IS NOT NULL AND invite_expires_at < NOW()'
        );
        $invite->execute();
        $inviteN = $invite->rowCount();

        $reset = $this->pdo->prepare(
            'UPDATE users SET reset_token = NULL, reset_expires_at = NULL
             WHERE reset_expires_at IS NOT NULL AND reset_expires_at < NOW()'
        );
        $reset->execute();
        $resetN = $reset->rowCount();

        // Invites left on accounts that already finished setup — not live reset tokens
        $stale = $this->pdo->prepare(
            'UPDATE users SET invite_token = NULL, invite_expires_at = NULL
             WHERE is_new_user = 0 AND invite_token IS NOT NULL'
        );
        $stale->execute();
        $staleN = $stale->rowCount();

        return ['invite' => $inviteN, 'reset' => $resetN, 'stale_invite' => $staleN];
    }

    public function audit(?int $actorId, string $ip, string $action, string $details): void
    {
        $stmt = $this->pdo->prepare(
            'INSERT INTO audit_logs (user_id, action, details, ip_address, created_at) VALUES (?, ?, ?, ?, NOW())'
        );
        $stmt->execute([$actorId, $action, $details, $ip]);
    }
}
