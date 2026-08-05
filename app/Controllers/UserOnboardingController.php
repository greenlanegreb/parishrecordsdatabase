<?php
/**
 * MIGRATED FILE MAPPING
 * ---------------------
 * Original Old File: user/onboarding.php/user/actions/save_onboarding.php
 * Migrated Date: 2026-08-05 05:04:14
 */declare(strict_types=1);


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
        if (session_status() === PHP_SESSION_NONE) {
            session_start();
        }

        // Ensure the users module is enabled; otherwise block access to onboarding
        if (!is_module_enabled($this->pdo, 'users')) {
            http_response_code(403);
            exit('403 Forbidden: The User Management module is currently disabled.');
        }

        // Require user authentication (redirects to login if not logged in)
        require_login();
        /** @var array{id: int|string, username: string, first_name?: string, surname?: string, is_new_user?: int|string, timezone?: string, date_format?: string, time_format?: string, attribution_display_mode?: string} $currentUser */
        $currentUser = get_current_user_data($this->pdo);

        // Ensure 'access_onboarding' permission exists in schema for role management UI
        try {
            $pCheck = $this->pdo->prepare("INSERT IGNORE INTO permissions (permission_key, description) VALUES ('access_onboarding', 'Allows accessing the first-time user onboarding setup wizard')");
            $pCheck->execute();
        } catch (Exception $e) {
            // Suppress if table/permission already exists
        }

        // If they are no longer marked as a new user, redirect them to main entry/dashboard
        if (empty($currentUser['is_new_user'])) {
            header('Location: /user/data_entry.php');
            exit;
        }

        $error = $_SESSION['error'] ?? '';
        unset($_SESSION['error']);

        require_once __DIR__ . '/../Views/user/onboarding.php';
    }
}
