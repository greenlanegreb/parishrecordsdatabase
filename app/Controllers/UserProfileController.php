<?php
/**
 * MIGRATED FILE MAPPING
 * ---------------------
 * Original Old File: user/profile.php/user/actions/save_profile.php
 * Migrated Date: 2026-08-05 05:10:12
 */declare(strict_types=1);


namespace App\Controllers;

use PDO;

class UserProfileController
{
    private PDO $pdo;

    public function __construct(PDO $pdo)
    {
        $this->pdo = $pdo;
    }

    public function show(): void
    {
        if (session_status() === PHP_SESSION_NONE) {
            session_start();
        }

        require_permission($this->pdo, 'access_profile', 'Allows viewing and managing personal user profile and security settings');
        /** @var array{id: int|string, username: string, email: string, email_verified?: int|string, first_name?: string, surname?: string, timezone?: string, date_format?: string, time_format?: string, attribution_display_mode?: string, language?: string, two_fa_enabled?: int|string} $currentUser */
        $currentUser = get_current_user_data($this->pdo);

        $systemName = get_system_name($this->pdo);
        $systemSlug = strtolower(preg_replace('/[^a-zA-Z0-9]+/', '-', $systemName) ?? 'app');

        $message = $_SESSION['message'] ?? '';
        $error = $_SESSION['error'] ?? '';
        unset($_SESSION['message'], $_SESSION['error']);

        /** @var array<int, string> $profileLanguages */
        $profileLanguages = [];
        $langDir = __DIR__ . '/../lang';
        if (is_dir($langDir)) {
            $langFiles = glob($langDir . '/*.php');
            if ($langFiles !== false) {
                foreach ($langFiles as $file) {
                    $code = basename($file, '.php');
                    if (preg_match('/^[a-z_]+$/', $code)) {
                        $profileLanguages[] = $code;
                    }
                }
            }
            sort($profileLanguages);
        }
        if (!in_array('en', $profileLanguages, true)) {
            array_unshift($profileLanguages, 'en');
        }
        $userLanguage = isset($currentUser['language']) && is_string($currentUser['language']) ? $currentUser['language'] : '';

        $queryGet = $_GET;
        if (isset($queryGet['action']) && $queryGet['action'] === 'download_new_codes') {
            if (!empty($_SESSION['new_raw_backup_codes']) && is_array($_SESSION['new_raw_backup_codes'])) {
                /** @var array<int, string> $codesToDownload */
                $codesToDownload = $_SESSION['new_raw_backup_codes'];
                unset($_SESSION['new_raw_backup_codes']);

                header('Content-Type: text/plain; charset=utf-8');
                header('Content-Disposition: attachment; filename="' . $systemSlug . '-backup-codes.txt"');
                header('Cache-Control: no-store, no-cache, must-revalidate');
                echo strtoupper($systemName) . " - NEW EMERGENCY BACKUP CODES\n";
                echo str_repeat("=", strlen($systemName) + 33) . "\n\n";
                echo "Keep these codes in a secure place. Each code can only be used once:\n\n";
                foreach ($codesToDownload as $code) {
                    echo " - " . $code . "\n";
                }
                exit;
            }
        }

        require_once __DIR__ . '/../Views/user/profile.php';
    }
}
