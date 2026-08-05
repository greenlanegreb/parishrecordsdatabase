<?php
/**
 * MIGRATED FILE MAPPING
 * ---------------------
 * Original Old File: roote/feedback.php
 * Migrated Date: 2026-08-05 06:58:18
 */declare(strict_types=1);


namespace App\Controllers\Public;

use PDO;

class FeedbackController
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

        if (!is_module_enabled($this->pdo, 'feedback')) {
            require_once __DIR__ . '/../../../403.php';
            exit;
        }

        $systemName = get_system_name($this->pdo);

        $settingsStmt = $this->pdo->query("SELECT setting_key, setting_value FROM feedback_form_settings");
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
        $formTitle = $formSettings['form_title'] ?? 'Submit Support Ticket or Feedback';
        $formIntro = $formSettings['form_intro'] ?? 'Fill out the form below to open a ticket with our team.';

        $columnsStmt = $this->pdo->query("SELECT * FROM feedback_columns ORDER BY sort_order ASC, column_name ASC");
        /** @var array<int, array<string, mixed>> $columns */
        $columns = $columnsStmt !== false ? $columnsStmt->fetchAll(PDO::FETCH_ASSOC) : [];

        $message = isset($_SESSION['message']) && is_string($_SESSION['message']) ? $_SESSION['message'] : '';
        $error = isset($_SESSION['error']) && is_string($_SESSION['error']) ? $_SESSION['error'] : '';
        
        $submittedData = isset($_SESSION['submitted_feedback_fields']) && is_array($_SESSION['submitted_feedback_fields']) ? $_SESSION['submitted_feedback_fields'] : [];
        $submittedFirst = isset($_SESSION['submitted_feedback_first']) && is_string($_SESSION['submitted_feedback_first']) ? $_SESSION['submitted_feedback_first'] : '';
        $submittedSurname = isset($_SESSION['submitted_feedback_surname']) && is_string($_SESSION['submitted_feedback_surname']) ? $_SESSION['submitted_feedback_surname'] : '';
        $submittedEmail = isset($_SESSION['submitted_feedback_email']) && is_string($_SESSION['submitted_feedback_email']) ? $_SESSION['submitted_feedback_email'] : '';
        $submittedSubject = isset($_SESSION['submitted_feedback_subject']) && is_string($_SESSION['submitted_feedback_subject']) ? $_SESSION['submitted_feedback_subject'] : '';

        unset(
            $_SESSION['message'], 
            $_SESSION['error'], 
            $_SESSION['submitted_feedback_fields'], 
            $_SESSION['submitted_feedback_first'], 
            $_SESSION['submitted_feedback_surname'], 
            $_SESSION['submitted_feedback_email'], 
            $_SESSION['submitted_feedback_subject']
        );

        require_once __DIR__ . '/../../Views/feedback/index.php';
    }
}
