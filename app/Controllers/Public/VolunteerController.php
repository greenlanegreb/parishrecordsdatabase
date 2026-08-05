<?php
/**
 * MIGRATED FILE MAPPING
 * ---------------------
 * Original Old File: roote/volunteer.php
 * Migrated Date: 2026-08-05 07:01:07
 */declare(strict_types=1);


namespace App\Controllers\Public;

use PDO;

class VolunteerController
{
    private PDO $pdo;

    public function __construct(PDO $pdo)
    {
        $this->pdo = $pdo;
    }

    public function index(): void
    {
        if (session_status() === PHP_SESSION_NONE) {
            session_start();
        }

        require_once __DIR__ . '/../../../db/auth_helpers.php';
        require_once __DIR__ . '/../../../includes/functions.php';
        require_once __DIR__ . '/../../../includes/security_engine.php';

        if (!is_module_enabled($this->pdo, 'volunteers')) {
            http_response_code(403);
            exit('403 Forbidden: The Volunteer Portal module is currently disabled.');
        }

        /** @var array{id: int|string, username?: string}|null $currentUser */
        $currentUser = function_exists('get_current_user_data') ? get_current_user_data($this->pdo) : null;
        $hasGuestPermission = guest_has_permission($this->pdo, 'submit_volunteer');

        if ($currentUser === null && !$hasGuestPermission) {
            $currentUser = require_permission($this->pdo, 'submit_volunteer', 'Allows submitting volunteer interest and transcription applications');
        }

        $systemName = get_system_name($this->pdo);

        $settingsStmt = $this->pdo->query("SELECT setting_key, setting_value FROM volunteer_form_settings");
        $formSettings = [];
        if ($settingsStmt !== false) {
            while ($row = $settingsStmt->fetch(PDO::FETCH_ASSOC)) {
                $k = isset($row['setting_key']) && is_string($row['setting_key']) ? $row['setting_key'] : '';
                $v = isset($row['setting_value']) && is_string($row['setting_value']) ? $row['setting_value'] : '';
                if ($k !== '') {
                    $formSettings[$k] = $v;
                }
            }
        }
        $formTitle = $formSettings['form_title'] ?? 'Volunteer for Data Entry';
        $formIntro = $formSettings['form_intro'] ?? 'Interested in helping transcribe and contribute? Let us know a little about yourself and any relevant experience.';

        $columnsStmt = $this->pdo->query("SELECT * FROM volunteer_columns ORDER BY sort_order ASC, column_name ASC");
        /** @var array<int, array<string, mixed>> $columns */
        $columns = $columnsStmt !== false ? $columnsStmt->fetchAll(PDO::FETCH_ASSOC) : [];

        $message = isset($_SESSION['message']) && is_string($_SESSION['message']) ? $_SESSION['message'] : '';
        $error = isset($_SESSION['error']) && is_string($_SESSION['error']) ? $_SESSION['error'] : '';
        
        $submittedData = isset($_SESSION['submitted_volunteer_fields']) && is_array($_SESSION['submitted_volunteer_fields']) ? $_SESSION['submitted_volunteer_fields'] : [];
        $submittedFirst = isset($_SESSION['submitted_volunteer_first']) && is_string($_SESSION['submitted_volunteer_first']) ? $_SESSION['submitted_volunteer_first'] : '';
        $submittedSurname = isset($_SESSION['submitted_volunteer_surname']) && is_string($_SESSION['submitted_volunteer_surname']) ? $_SESSION['submitted_volunteer_surname'] : '';
        $submittedEmail = isset($_SESSION['submitted_volunteer_email']) && is_string($_SESSION['submitted_volunteer_email']) ? $_SESSION['submitted_volunteer_email'] : '';

        unset(
            $_SESSION['message'], 
            $_SESSION['error'], 
            $_SESSION['submitted_volunteer_fields'], 
            $_SESSION['submitted_volunteer_first'], 
            $_SESSION['submitted_volunteer_surname'], 
            $_SESSION['submitted_volunteer_email']
        );

        require_once __DIR__ . '/../../Views/volunteer/index.php';
    }
}
