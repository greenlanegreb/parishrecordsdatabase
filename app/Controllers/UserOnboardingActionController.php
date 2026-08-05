<?php
/**
 * MIGRATED FILE MAPPING
 * ---------------------
 * Original Old File: user/onboarding.php/user/actions/save_onboarding.php
 * Migrated Date: 2026-08-05 05:05:06
 */declare(strict_types=1);


namespace App\Controllers;

use PDO;

class UserOnboardingActionController
{
    private PDO $pdo;

    public function __construct(PDO $pdo)
    {
        $this->pdo = $pdo;
    }

    public function save(): void
    {
        if (session_status() === PHP_SESSION_NONE) {
            session_start();
        }

        if (!is_module_enabled($this->pdo, 'users')) {
            http_response_code(403);
            exit('403 Forbidden: The User Management module is currently disabled.');
        }

        $serverMethod = isset($_SERVER['REQUEST_METHOD']) && is_string($_SERVER['REQUEST_METHOD']) ? $_SERVER['REQUEST_METHOD'] : 'GET';
        if ($serverMethod !== 'POST') {
            http_response_code(405);
            exit('Method Not Allowed');
        }

        verify_csrf_token();
        require_login();
        /** @var array{id: int|string, username: string} $currentUser */
        $currentUser = get_current_user_data($this->pdo);
        $userId = $currentUser['id'];

        $post = $_POST;
        $firstName = isset($post['first_name']) && is_string($post['first_name']) ? trim($post['first_name']) : '';
        $surname = isset($post['surname']) && is_string($post['surname']) ? trim($post['surname']) : '';
        $displayMode = isset($post['attribution_display_mode']) && is_string($post['attribution_display_mode']) ? trim($post['attribution_display_mode']) : 'initials_random';
        $timezone = isset($post['timezone']) && is_string($post['timezone']) ? trim($post['timezone']) : 'UTC';
        $dateFormat = isset($post['date_format']) && is_string($post['date_format']) ? trim($post['date_format']) : 'd/m/Y';
        $timeFormat = isset($post['time_format']) && is_string($post['time_format']) ? trim($post['time_format']) : '24';

        $allowedModes = ['full_name', 'volunteers_only', 'initials_random'];
        if (!in_array($displayMode, $allowedModes, true)) {
            $displayMode = 'initials_random';
        }

        $validTimezones = timezone_identifiers_list();
        if (!in_array($timezone, $validTimezones, true)) {
            $timezone = 'UTC';
        }

        $allowedDateFormats = ['d/m/Y', 'd/m/y', 'd.m.Y', 'm/d/Y', 'l j F Y'];
        if (!in_array($dateFormat, $allowedDateFormats, true)) {
            $dateFormat = 'd/m/Y';
        }

        $allowedTimeFormats = ['12', '24', 'none'];
        if (!in_array($timeFormat, $allowedTimeFormats, true)) {
            $timeFormat = '24';
        }

        $stmt = $this->pdo->prepare("UPDATE users SET first_name = ?, surname = ?, attribution_display_mode = ?, timezone = ?, date_format = ?, time_format = ?, is_new_user = 0 WHERE id = ?");
        if ($stmt->execute([$firstName, $surname, $displayMode, $timezone, $dateFormat, $timeFormat, $userId])) {
            $_SESSION['message'] = "Welcome aboard! Your preferences have been saved.";
            
            if (function_exists('has_permission') && has_permission($this->pdo, 'manage_settings')) {
                header('Location: /admin/settings.php');
            } else {
                header('Location: /user/data_entry.php');
            }
            exit;
        } else {
            $_SESSION['error'] = "Failed to save onboarding preferences. Please try again.";
            header('Location: /user/onboarding.php');
            exit;
        }
    }
}
