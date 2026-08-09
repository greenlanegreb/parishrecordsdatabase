<?php
/**
 * MIGRATED FILE MAPPING
 * ---------------------
 * Original Old File: roote/feedback.php & user/actions/save_public_ticket.php
 * Migrated Date: 2026-08-05 06:58:18
 */
declare(strict_types=1);

namespace App\Controllers\Public;

use PDO;
use Exception;

class FeedbackController
{
    private PDO $pdo;

    public function __construct(PDO $pdo)
    {
        $this->pdo = $pdo;
    }

    public function index(): void
    {
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

    public function store(): void
    {
        $basePath = defined('BASE_PATH') ? rtrim(BASE_PATH, '/') : '';

        if (!is_module_enabled($this->pdo, 'feedback')) {
            http_response_code(403);
            exit('403 Forbidden');
        }

        $serverMethod = isset($_SERVER['REQUEST_METHOD']) && is_string($_SERVER['REQUEST_METHOD']) ? $_SERVER['REQUEST_METHOD'] : 'GET';
        if ($serverMethod !== 'POST') {
            http_response_code(405);
            exit('Method Not Allowed');
        }

        verify_csrf_token();

        // 1. Run Threat Defense Firewall Check
        $firewallResult = run_form_firewall_check($this->pdo);
        if ($firewallResult !== true) {
            $_SESSION['error'] = is_string($firewallResult) ? $firewallResult : 'Firewall check failed.';
            header('Location: ' . $basePath . '/feedback');
            exit;
        }

        // 2. Run CAPTCHA Verification Check
        $captchaResult = verify_form_captcha($this->pdo);
        if ($captchaResult !== true) {
            $_SESSION['error'] = is_string($captchaResult) ? $captchaResult : 'CAPTCHA verification failed.';
            header('Location: ' . $basePath . '/feedback');
            exit;
        }

        if (!empty($_POST['website_hp'])) {
            header('Location: ' . $basePath . '/feedback');
            exit;
        }

        $post = $_POST;
        $firstName = isset($post['feedback_first_name']) && is_string($post['feedback_first_name']) ? trim($post['feedback_first_name']) : '';
        $surname = isset($post['feedback_surname']) && is_string($post['feedback_surname']) ? trim($post['feedback_surname']) : '';
        $email = isset($post['feedback_email']) && is_string($post['feedback_email']) ? trim($post['feedback_email']) : '';
        $subject = isset($post['feedback_subject']) && is_string($post['feedback_subject']) ? trim($post['feedback_subject']) : 'Support Inquiry';
        $fields = isset($post['fields']) && is_array($post['fields']) ? $post['fields'] : [];
        $userId = isset($_SESSION['user_id']) ? intval($_SESSION['user_id']) : null;

        $_SESSION['submitted_feedback_first'] = $firstName;
        $_SESSION['submitted_feedback_surname'] = $surname;
        $_SESSION['submitted_feedback_email'] = $email;
        $_SESSION['submitted_feedback_subject'] = $subject;
        $_SESSION['submitted_feedback_fields'] = $fields;

        if ($firstName === '' || $surname === '' || $email === '' || $subject === '') {
            $_SESSION['error'] = "First name, surname, email address, and subject are mandatory fields.";
            header('Location: ' . $basePath . '/feedback');
            exit;
        }
        if (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
            $_SESSION['error'] = "Invalid email format.";
            header('Location: ' . $basePath . '/feedback');
            exit;
        }

        $colsStmt = $this->pdo->query("SELECT * FROM feedback_columns");
        $columnsMap = [];
        if ($colsStmt !== false) {
            while ($col = $colsStmt->fetch(PDO::FETCH_ASSOC)) {
                $columnsMap[$col['id']] = $col;
            }
        }

        // Validate custom required fields safely
        foreach ($columnsMap as $colId => $colMeta) {
            if (!empty($colMeta['is_required'])) {
                $val = $fields[$colId] ?? null;
                $isEmpty = false;
                if ($val === null) {
                    $isEmpty = true;
                } elseif (is_array($val)) {
                    $filtered = array_filter($val, function($v) { return is_string($v) && trim($v) !== ''; });
                    if (empty($filtered)) $isEmpty = true;
                } else {
                    if (!is_string($val) || trim($val) === '') $isEmpty = true;
                }

                if ($isEmpty) {
                    $colName = isset($colMeta['column_name']) ? (string)$colMeta['column_name'] : 'Field';
                    $_SESSION['error'] = "The field '{$colName}' is mandatory.";
                    header('Location: ' . $basePath . '/feedback');
                    exit;
                }
            }
        }

        try {
            $this->pdo->beginTransaction();

            $stmt = $this->pdo->prepare("INSERT INTO feedback_tickets (user_id, first_name, surname, email, subject, status) VALUES (?, ?, ?, ?, ?, 'Pending')");
            $stmt->execute([$userId, $firstName, $surname, $email, $subject]);
            $ticketId = (int)$this->pdo->lastInsertId();

            if (!empty($fields)) {
                $valStmt = $this->pdo->prepare("INSERT INTO feedback_ticket_values (ticket_id, column_id, value_content) VALUES (?, ?, ?)");
                foreach ($fields as $colId => $val) {
                    if ($val !== '' && $val !== [] && $val !== null) {
                        if (is_array($val)) {
                            $val = array_filter($val, function($v) { return is_string($v) && trim($v) !== ''; });
                        }
                        if (!empty($val)) {
                            $valContent = is_array($val) ? sanitize_incoming_text(implode(', ', $val)) : sanitize_incoming_text((string)$val);
                            $valStmt->execute([$ticketId, intval($colId), $valContent]);
                        }
                    }
                }
            }

            $this->pdo->commit();

            // Trigger automated support ticket email template
            require_once __DIR__ . '/../../../includes/feedback_mail_engine.php';
            send_feedback_templated_email($this->pdo, $ticketId, 'ticket_received');

            unset(
                $_SESSION['submitted_feedback_first'], 
                $_SESSION['submitted_feedback_surname'], 
                $_SESSION['submitted_feedback_email'], 
                $_SESSION['submitted_feedback_subject'], 
                $_SESSION['submitted_feedback_fields']
            );
            $_SESSION['message'] = "Your support ticket (#{$ticketId}) has been successfully submitted!";
        } catch (Exception $e) {
            if ($this->pdo->inTransaction()) {
                $this->pdo->rollBack();
            }
            $_SESSION['error'] = "An error occurred while saving your ticket. Please try again.";
        }

        header('Location: ' . $basePath . '/feedback');
        exit;
    }
}
