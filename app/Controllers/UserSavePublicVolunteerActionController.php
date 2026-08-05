<?php
/**
 * MIGRATED FILE MAPPING
 * ---------------------
 * Original Old File: user/actions/save_public_volunteer.php
 * Migrated Date: 2026-08-05 05:46:11
 */declare(strict_types=1);


namespace App\Controllers;

use Exception;
use PDO;

class UserSavePublicVolunteerActionController
{
    private PDO $pdo;

    public function __construct(PDO $pdo)
    {
        $this->pdo = $pdo;
    }

    public function handle(): void
    {
        if (session_status() === PHP_SESSION_NONE) {
            session_start();
        }

        if (!is_module_enabled($this->pdo, 'volunteers')) {
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

        // 1. Run Threat Defense Firewall Check
        $firewallResult = run_form_firewall_check($this->pdo);
        if ($firewallResult !== true) {
            $_SESSION['error'] = is_string($firewallResult) ? $firewallResult : 'Firewall block triggered.';
            header('Location: /volunteer.php');
            exit;
        }

        // 2. Run CAPTCHA Verification Check
        $captchaResult = verify_form_captcha($this->pdo);
        if ($captchaResult !== true) {
            $_SESSION['error'] = is_string($captchaResult) ? $captchaResult : 'CAPTCHA verification failed.';
            header('Location: /volunteer.php');
            exit;
        }

        $post = $_POST;
        $honeypot = isset($post['website_url']) && is_string($post['website_url']) ? trim($post['website_url']) : '';
        if ($honeypot !== '') {
            $_SESSION['error'] = 'Spam detection triggered.';
            header('Location: /volunteer.php');
            exit;
        }

        $firstName = isset($post['volunteer_first_name']) && is_string($post['volunteer_first_name']) ? trim($post['volunteer_first_name']) : '';
        $surname = isset($post['volunteer_surname']) && is_string($post['volunteer_surname']) ? trim($post['volunteer_surname']) : '';
        $email = isset($post['volunteer_email']) && is_string($post['volunteer_email']) ? trim($post['volunteer_email']) : '';
        
        /** @var array<mixed, mixed> $fields */
        $fields = isset($post['fields']) && is_array($post['fields']) ? $post['fields'] : [];

        $_SESSION['submitted_volunteer_first'] = $firstName;
        $_SESSION['submitted_volunteer_surname'] = $surname;
        $_SESSION['submitted_volunteer_email'] = $email;
        $_SESSION['submitted_volunteer_fields'] = $fields;

        if ($firstName === '' || $surname === '' || $email === '') {
            $_SESSION['error'] = "First name, surname, and email address are required fields.";
            header('Location: /volunteer.php');
            exit;
        }
        if (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
            $_SESSION['error'] = "Please provide a valid email address.";
            header('Location: /volunteer.php');
            exit;
        }

        $currentUser = function_exists('get_current_user_data') ? get_current_user_data($this->pdo) : null;
        $userId = ($currentUser !== false && $currentUser !== null && isset($currentUser['id'])) ? $currentUser['id'] : null;

        $this->pdo->beginTransaction();
        try {
            $stmt = $this->pdo->prepare("INSERT INTO volunteer_submissions (first_name, surname, email, created_by) VALUES (?, ?, ?, ?)");
            $stmt->execute([$firstName, $surname, $email, $userId]);
            $submissionId = (int)$this->pdo->lastInsertId();

            if (!empty($fields)) {
                $valStmt = $this->pdo->prepare("INSERT INTO volunteer_submission_values (submission_id, column_id, value_content) VALUES (?, ?, ?)");

                foreach ($fields as $colId => $val) {
                    if ($val !== '' && $val !== [] && $val !== null) {
                        if (is_array($val)) {
                            $val = array_filter($val, function($v) { return is_string($v) && trim($v) !== ''; });
                        }
                        if (!empty($val)) {
                            $valContent = is_array($val) ? sanitize_incoming_text(implode(', ', $val)) : sanitize_incoming_text((string)$val);
                            $valStmt->execute([$submissionId, (int)$colId, $valContent]);
                        }
                    }
                }
            }

            $this->pdo->commit();
            
            // Trigger automated email template
            require_once __DIR__ . '/../../includes/volunteer_mail_engine.php';
            send_volunteer_templated_email($this->pdo, $submissionId, 'submission_received');

            unset($_SESSION['submitted_volunteer_first'], $_SESSION['submitted_volunteer_surname'], $_SESSION['submitted_volunteer_email'], $_SESSION['submitted_volunteer_fields']);
            $_SESSION['message'] = "Thank you! Your volunteer interest has been successfully submitted.";
        } catch (Exception $e) {
            if ($this->pdo->inTransaction()) {
                $this->pdo->rollBack();
            }
            $_SESSION['error'] = "An error occurred while saving your submission. Please try again.";
        }

        header('Location: /volunteer.php');
        exit;
    }
}
