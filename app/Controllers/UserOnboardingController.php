<?php
/**
 * MIGRATED FILE MAPPING
 * ---------------------
 * Original Old File: user/onboarding.php
 * Migrated Date: 2026-08-05 05:04:14
 */
declare(strict_types=1);

namespace App\Controllers;

use Exception;
use PDO;

class UserOnboardingController
{
    private PDO $pdo;

    public function __construct(PDO $pdo)
    {
        $this->pdo = $pdo;
    }

    public function show(): void
    {
        $basePath = defined('BASE_PATH') ? rtrim((string) BASE_PATH, '/') : '';

        if (!isset($_SESSION['user_id'])) {
            header('Location: ' . $basePath . '/login');
            exit;
        }

        /** @var array{id: int|string, username: string, first_name?: string, surname?: string, is_new_user?: int|string, timezone?: string, date_format?: string, time_format?: string, attribution_display_mode?: string, language?: string|null}|null $currentUser */
        $currentUser = \get_current_user_data($this->pdo);
        if ($currentUser === null) {
            header('Location: ' . $basePath . '/login');
            exit;
        }

        try {
            $pCheck = $this->pdo->prepare(
                "INSERT IGNORE INTO permissions (permission_key, description)
                 VALUES ('access_onboarding', 'Allows accessing the first-time user onboarding setup wizard')"
            );
            $pCheck->execute();
        } catch (Exception $e) {
            // non-fatal
        }

        if (empty($currentUser['is_new_user'])) {
            header('Location: ' . $basePath . '/data-entry');
            exit;
        }

        require_once dirname(__DIR__, 2) . '/includes/user_preferences.php';
        $currentUser = \user_merge_personal_draft($currentUser, 'onboarding_draft');

        /** @var list<string> $onboardingLanguages */
        $onboardingLanguages = \user_available_language_codes();
        $userLanguage = isset($currentUser['language']) && is_string($currentUser['language'])
            ? $currentUser['language'] : '';

        $error = $_SESSION['error'] ?? '';
        unset($_SESSION['error']);

        require_once __DIR__ . '/../Views/user/onboarding.php';
    }
}
