<?php
/**
 * MIGRATED FILE MAPPING
 * ---------------------
 * Original Old File: user/setup_2fa.php/user/actions/save_setup_2fa.php
 * Migrated Date: 2026-08-05 05:22:20
 */
declare(strict_types=1);

namespace App\Controllers;

use PDO;

class UserSetup2faController
{
    private PDO $pdo;

    public function __construct(PDO $pdo)
    {
        $this->pdo = $pdo;
    }

    public function show(): void
    {
        // Ensure user is logged in (bypassing the old permission check wall)
        /** @var array{id: int|string, username: string, two_fa_enabled?: int|string}|null $user */
        $user = get_current_user_data($this->pdo);

        $basePath = defined('BASE_PATH') ? rtrim(BASE_PATH, '/') : '';

        if ($user === null) {
            header('Location: ' . $basePath . '/login');
            exit;
        }

        if (!empty($user['two_fa_enabled'])) {
            header('Location: ' . $basePath . '/profile');
            exit;
        }

        if (!isset($_SESSION['temp_2fa_secret']) || !is_string($_SESSION['temp_2fa_secret'])) {
            $_SESSION['temp_2fa_secret'] = $this->generateBase32Secret();
            
            /** @var array<int, string> $rawCodes */
            $rawCodes = [];
            /** @var array<int, string> $hashedCodes */
            $hashedCodes = [];
            for ($i = 0; $i < 5; $i++) {
                $code = strtoupper(bin2hex(random_bytes(3)));
                $formattedCode = substr($code, 0, 3) . '-' . substr($code, 3, 3);
                $rawCodes[] = $formattedCode;
                $hashedCodes[] = password_hash($formattedCode, PASSWORD_DEFAULT);
            }
            $_SESSION['temp_raw_backup_codes'] = $rawCodes;
            $_SESSION['temp_hashed_backup_codes'] = json_encode($hashedCodes);
        }

        $secret = $_SESSION['temp_2fa_secret'];
        /** @var array<int, string> $rawBackupCodes */
        $rawBackupCodes = isset($_SESSION['temp_raw_backup_codes']) && is_array($_SESSION['temp_raw_backup_codes']) ? $_SESSION['temp_raw_backup_codes'] : [];

        $queryGet = $_GET;
        // Handle direct download of backup codes as a .txt file
        if (isset($queryGet['action']) && $queryGet['action'] === 'download_codes') {
            if (!empty($rawBackupCodes)) {
                header('Content-Type: text/plain; charset=utf-8');
                header('Content-Disposition: attachment; filename="cakebread-database-backup-codes.txt"');
                header('Cache-Control: no-store, no-cache, must-revalidate');
                echo "CAKEBREAD DATABASE - 2FA EMERGENCY BACKUP CODES\n";
                echo "================================================\n\n";
                echo "Keep these codes in a secure place. Each code can be used once\n";
                echo "if you lose access to your authenticator app:\n\n";
                foreach ($rawBackupCodes as $code) {
                    echo " - " . $code . "\n";
                }
                exit;
            }
        }

        $error = $_SESSION['error'] ?? '';
        unset($_SESSION['error']);

        $issuer = "CakebreadDatabase";
        $accountName = urlencode($user['username']);
        $otpauthUrl = "otpauth://totp/{$issuer}:{$accountName}?secret={$secret}&issuer={$issuer}";
        $qrCodeUrl = "https://api.qrserver.com/v1/create-qr-code/?size=200x200&data=" . urlencode($otpauthUrl);

        require_once __DIR__ . '/../Views/user/setup_2fa.php';
    }

    private function generateBase32Secret(int $length = 16): string
    {
        $map = 'ABCDEFGHIJKLMNOPQRSTUVWXYZ234567';
        $secret = '';
        for ($i = 0; $i < $length; $i++) {
            $secret .= $map[random_int(0, 31)];
        }
        return $secret;
    }
}
