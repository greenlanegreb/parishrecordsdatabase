<?php
/**
 * MIGRATED FILE MAPPING
 * ---------------------
 * Original Old File: user/actions/save_public_volunteer.php
 * Migrated Date: 2026-08-05 05:46:11
 */
declare(strict_types=1);

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

    /**
     * Allocate a username unique against users and pending/open volunteer preferred names.
     */
    private function allocateUniqueUsername(string $firstName, string $surname): string
    {
        $cleanedFirst = preg_replace('/[^a-zA-Z]/', '', $firstName) ?? '';
        $cleanedSurname = preg_replace('/[^a-zA-Z]/', '', $surname) ?? '';
        $base = strtolower(substr($cleanedFirst, 0, 1) . $cleanedSurname);
        if ($base === '') {
            $base = 'user';
        }

        $username = $base;
        $counter = 1;
        while ($this->usernameIsTaken($username)) {
            $username = $base . $counter;
            $counter++;
        }
        return $username;
    }

    /**
     * Taken if already a user OR claimed on a non-rejected volunteer application.
     */
    private function usernameIsTaken(string $username): bool
    {
        $chkUser = $this->pdo->prepare('SELECT id FROM users WHERE username = ? LIMIT 1');
        $chkUser->execute([$username]);
        if ($chkUser->fetch()) {
            return true;
        }

        $chkVol = $this->pdo->prepare(
            "SELECT id FROM volunteer_submissions
             WHERE preferred_username = ?
               AND (status IS NULL OR status NOT IN ('Rejected', 'Declined', 'Accepted'))
             LIMIT 1"
        );
        $chkVol->execute([$username]);
        return (bool) $chkVol->fetch();
    }

    /**
     * AJAX: check preferred username availability (rate-limited).
     */
    public function checkUsername(): void
    {
        header('Content-Type: application/json; charset=utf-8');

        if (!is_module_enabled($this->pdo, 'volunteers')) {
            http_response_code(403);
            echo json_encode(['ok' => false, 'message' => 'Forbidden']);
            return;
        }

        if (($_SERVER['REQUEST_METHOD'] ?? '') !== 'POST') {
            http_response_code(405);
            echo json_encode(['ok' => false, 'message' => 'Method not allowed']);
            return;
        }

        verify_csrf_token();

        $raw = isset($_POST['username']) && is_string($_POST['username']) ? trim($_POST['username']) : '';
        if ($raw === '') {
            echo json_encode(['ok' => true, 'available' => false, 'message' => 'Enter a username to check.']);
            return;
        }

        $sanitized = preg_replace('/[^a-zA-Z0-9_\-]/', '', $raw) ?? '';
        if ($sanitized === '' || $sanitized !== $raw) {
            echo json_encode([
                'ok' => true,
                'available' => false,
                'message' => 'Use only letters, numbers, underscore, and hyphen.',
            ]);
            return;
        }

        log_username_check_attempt($this->pdo);

        if (has_exceeded_username_check_limit($this->pdo)) {
            echo json_encode([
                'ok' => true,
                'available' => false,
                'limited' => true,
                'message' => 'Check limit reached (max 3 per 24 hours). A username will be allocated on submit.',
            ]);
            return;
        }

        if ($this->usernameIsTaken($sanitized)) {
            echo json_encode([
                'ok' => true,
                'available' => false,
                'message' => 'That username is not available.',
            ]);
            return;
        }

        echo json_encode([
            'ok' => true,
            'available' => true,
            'message' => 'Username is available.',
        ]);
    }

    public function handle(): void
    {
        if (!is_module_enabled($this->pdo, 'volunteers')) {
            http_response_code(403);
            exit('403 Forbidden');
        }

        $serverMethod = isset($_SERVER['REQUEST_METHOD']) && is_string($_SERVER['REQUEST_METHOD'])
            ? $_SERVER['REQUEST_METHOD'] : 'GET';
        if ($serverMethod !== 'POST') {
            http_response_code(405);
            exit('Method Not Allowed');
        }

        verify_csrf_token();
        require_once __DIR__ . '/../../includes/security_engine.php';
        $basePath = defined('BASE_PATH') ? rtrim(BASE_PATH, '/') : '';

        $firewallResult = run_form_firewall_check($this->pdo);
        if ($firewallResult !== true) {
            $_SESSION['error'] = is_string($firewallResult) ? $firewallResult : 'Firewall block triggered.';
            header('Location: ' . $basePath . '/volunteer');
            exit;
        }

        $captchaResult = verify_form_captcha($this->pdo);
        if ($captchaResult !== true) {
            $_SESSION['error'] = is_string($captchaResult) ? $captchaResult : 'CAPTCHA verification failed.';
            header('Location: ' . $basePath . '/volunteer');
            exit;
        }

        $post = $_POST;
        $honeypot = isset($post['website_url']) && is_string($post['website_url']) ? trim($post['website_url']) : '';
        if ($honeypot !== '') {
            $_SESSION['error'] = 'Spam detection triggered.';
            header('Location: ' . $basePath . '/volunteer');
            exit;
        }

        $firstName = isset($post['volunteer_first_name']) && is_string($post['volunteer_first_name']) ? trim($post['volunteer_first_name']) : '';
        $surname = isset($post['volunteer_surname']) && is_string($post['volunteer_surname']) ? trim($post['volunteer_surname']) : '';
        $email = isset($post['volunteer_email']) && is_string($post['volunteer_email']) ? trim($post['volunteer_email']) : '';
        $autoUsername = !empty($post['auto_username']);
        $requestedUsername = isset($post['preferred_username']) && is_string($post['preferred_username'])
            ? trim($post['preferred_username']) : '';

        /** @var array<mixed, mixed> $fields */
        $fields = isset($post['fields']) && is_array($post['fields']) ? $post['fields'] : [];

        $_SESSION['submitted_volunteer_first'] = $firstName;
        $_SESSION['submitted_volunteer_surname'] = $surname;
        $_SESSION['submitted_volunteer_email'] = $email;
        $_SESSION['submitted_volunteer_username'] = $requestedUsername;
        $_SESSION['submitted_volunteer_fields'] = $fields;

        if ($firstName === '' || $surname === '' || $email === '') {
            $_SESSION['error'] = 'First name, surname, and email address are required fields.';
            header('Location: ' . $basePath . '/volunteer');
            exit;
        }

        if (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
            $_SESSION['error'] = 'Please provide a valid email address.';
            header('Location: ' . $basePath . '/volunteer');
            exit;
        }

        $finalUsername = '';
        $usernameNote = '';

        if ($autoUsername || $requestedUsername === '') {
            $finalUsername = $this->allocateUniqueUsername($firstName, $surname);
            $usernameNote = $autoUsername
                ? "A username was allocated for you: {$finalUsername}."
                : "No username requested; allocated: {$finalUsername}.";
        } else {
            log_username_check_attempt($this->pdo);

            if (has_exceeded_username_check_limit($this->pdo)) {
                $finalUsername = $this->allocateUniqueUsername($firstName, $surname);
                $usernameNote = "Username check limit reached (max 3 per 24 hours from your network). Allocated: {$finalUsername}.";
            } else {
                $sanitized = preg_replace('/[^a-zA-Z0-9_\-]/', '', $requestedUsername) ?? '';
                if ($sanitized === '') {
                    $finalUsername = $this->allocateUniqueUsername($firstName, $surname);
                    $usernameNote = "Invalid username characters; allocated: {$finalUsername}.";
                } elseif ($this->usernameIsTaken($sanitized)) {
                    $finalUsername = $this->allocateUniqueUsername($firstName, $surname);
                    $usernameNote = "The username '{$sanitized}' is not available. Allocated: {$finalUsername}.";
                } else {
                    $finalUsername = $sanitized;
                    $usernameNote = "Username reserved for your application: {$finalUsername}.";
                }
            }
        }

        $currentUser = function_exists('get_current_user_data') ? get_current_user_data($this->pdo) : null;
        $userId = ($currentUser !== false && $currentUser !== null && isset($currentUser['id'])) ? $currentUser['id'] : null;

        $this->pdo->beginTransaction();
        try {
            $stmt = $this->pdo->prepare(
                'INSERT INTO volunteer_submissions (first_name, surname, email, preferred_username, created_by)
                 VALUES (?, ?, ?, ?, ?)'
            );
            $stmt->execute([$firstName, $surname, $email, $finalUsername, $userId]);
            $submissionId = (int) $this->pdo->lastInsertId();

            if (!empty($fields)) {
                $valStmt = $this->pdo->prepare(
                    'INSERT INTO volunteer_submission_values (submission_id, column_id, value_content) VALUES (?, ?, ?)'
                );
                foreach ($fields as $colId => $val) {
                    if ($val !== '' && $val !== [] && $val !== null) {
                        if (is_array($val)) {
                            $val = array_filter($val, static function ($v) {
                                return is_string($v) && trim($v) !== '';
                            });
                        }
                        if (!empty($val)) {
                            $valContent = is_array($val)
                                ? sanitize_incoming_text(implode(', ', $val))
                                : sanitize_incoming_text((string) $val);
                            $valStmt->execute([$submissionId, (int) $colId, $valContent]);
                        }
                    }
                }
            }

            $this->pdo->commit();

            require_once __DIR__ . '/../../includes/volunteer_mail_engine.php';
            send_volunteer_templated_email($this->pdo, $submissionId, 'submission_received');

            unset(
                $_SESSION['submitted_volunteer_first'],
                $_SESSION['submitted_volunteer_surname'],
                $_SESSION['submitted_volunteer_email'],
                $_SESSION['submitted_volunteer_username'],
                $_SESSION['submitted_volunteer_fields']
            );

            $_SESSION['message'] = "Thank you! Your volunteer interest has been successfully submitted. {$usernameNote}";
        } catch (Exception $e) {
            if ($this->pdo->inTransaction()) {
                $this->pdo->rollBack();
            }
            $_SESSION['error'] = 'An error occurred while saving your submission. Please try again.';
        }

        header('Location: ' . $basePath . '/volunteer');
        exit;
    }
}
