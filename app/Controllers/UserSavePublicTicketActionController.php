<?php
/**
 * MIGRATED FILE MAPPING
 * ---------------------
 * Original Old File: user/actions/save_public_ticket.php
 * Migrated Date: 2026-08-05 05:43:56
 */
declare(strict_types=1);

namespace App\Controllers;

use Exception;
use PDO;

class UserSavePublicTicketActionController
{
    private PDO $pdo;

    public function __construct(PDO $pdo)
    {
        $this->pdo = $pdo;
    }

    public function handle(): void
    {
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

        require_once __DIR__ . '/../../includes/security_engine.php';

        $basePath = defined('BASE_PATH') ? rtrim(BASE_PATH, '/') : '';

        // 1. Run Threat Defense Firewall Check
        $firewallResult = run_form_firewall_check($this->pdo);
        if ($firewallResult !== true) {
            $_SESSION['error'] = is_string($firewallResult) ? $firewallResult : 'Firewall block triggered.';
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

        $post = $_POST;
        $honeypot = isset($post['website_hp']) && is_string($post['website_hp']) ? trim($post['website_hp']) : '';
        if ($honeypot !== '') {
            header('Location: ' . $basePath . '/feedback');
            exit;
        }

        $firstName = isset($post['feedback_first_name']) && is_string($post['feedback_first_name']) ? trim($post['feedback_first_name']) : '';
        $surname = isset($post['feedback_surname']) && is_string($post['feedback_surname']) ? trim($post['feedback_surname']) : '';
        $email = isset($post['feedback_email']) && is_string($post['feedback_email']) ? trim($post['feedback_email']) : '';
        $rawSubject = isset($post['feedback_subject']) && is_string($post['feedback_subject']) ? trim($post['feedback_subject']) : '';
        $subject = ($rawSubject !== '') ? $rawSubject : 'Support Inquiry';
        
        /** @var array<mixed, mixed> $fields */
        $fields = isset($post['fields']) && is_array($post['fields']) ? $post['fields'] : [];
        $userId = isset($_SESSION['user_id']) ? (int)$_SESSION['user_id'] : null;

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
        /** @var array<int|string, array<string, mixed>> $columnsMap */
        $columnsMap = [];
        if ($colsStmt !== false) {
            while ($col = $colsStmt->fetch(PDO::FETCH_ASSOC)) {
                $cId = isset($col['id']) ? (int)$col['id'] : 0;
                $columnsMap[$cId] = $col;
            }
        }

        // Validate custom required fields safely (handling strings and arrays)
        foreach ($columnsMap as $colId => $colMeta) {
            if (!empty($colMeta['is_required'])) {
                $val = $fields[$colId] ?? null;
                $isEmpty = false;
                if ($val === null) {
                    $isEmpty = true;
                } elseif (is_array($val)) {
                    $filtered = array_filter($val, function($v) { return is_string($v) && trim($v) !== ''; });
                    if (empty($filtered)) {
                        $isEmpty = true;
                    }
                } else {
                    if (!is_string($val) || trim($val) === '') {
                        $isEmpty = true;
                    }
                }

                if ($isEmpty) {
                    $colName = isset($colMeta['column_name']) && is_string($colMeta['column_name']) ? $colMeta['column_name'] : 'Field';
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
                            $valStmt->execute([$ticketId, (int)$colId, $valContent]);
                        }
                    }
                }
            }

            $this->pdo->commit();

            // Trigger automated support ticket email template
            require_once __DIR__ . '/../../includes/feedback_mail_engine.php';
            send_feedback_templated_email($this->pdo, $ticketId, 'ticket_received');

            unset($_SESSION['submitted_feedback_first'], $_SESSION['submitted_feedback_surname'], $_SESSION['submitted_feedback_email'], $_SESSION['submitted_feedback_subject'], $_SESSION['submitted_feedback_fields']);
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
