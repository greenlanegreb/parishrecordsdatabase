<?php
/**
 * MIGRATED FILE MAPPING
 * ---------------------
 * Original Old File: user/profile.php/user/actions/save_profile.php
 * Migrated Date: 2026-08-05 05:10:12
 */
declare(strict_types=1);

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
        \require_permission($this->pdo, 'access_profile', 'Allows viewing and managing personal user profile and security settings');
        /** @var array{id: int|string, username: string, email: string, email_verified?: int|string, first_name?: string, surname?: string, timezone?: string, date_format?: string, time_format?: string, attribution_display_mode?: string, language?: string, two_fa_enabled?: int|string} $currentUser */
        $currentUser = \get_current_user_data($this->pdo);

        require_once dirname(__DIR__, 2) . '/includes/user_preferences.php';
        $currentUser = \user_merge_personal_draft($currentUser, 'profile_personal_draft');

        $systemName = \get_system_name($this->pdo);
        $systemSlug = strtolower(preg_replace('/[^a-zA-Z0-9]+/', '-', $systemName) ?? 'app');

        $message = $_SESSION['message'] ?? '';
        $error = $_SESSION['error'] ?? '';
        $fieldErrors = $_SESSION['field_errors'] ?? [];
        unset($_SESSION['message'], $_SESSION['error'], $_SESSION['field_errors']);

        $profileLanguages = \user_available_language_codes();
        $userLanguage = isset($currentUser['language']) && is_string($currentUser['language'])
            ? $currentUser['language'] : '';

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
                echo str_repeat('=', strlen($systemName) + 33) . "\n\n";
                echo "Keep these codes in a secure place. Each code can only be used once:\n\n";
                foreach ($codesToDownload as $code) {
                    echo ' - ' . $code . "\n";
                }
                exit;
            }
        }

        require_once __DIR__ . '/../Views/user/profile.php';
    }
}
