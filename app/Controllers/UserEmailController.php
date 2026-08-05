<?php
/**
 * MIGRATED FILE MAPPING
 * ---------------------
 * Original Old File: admin/manage_user_emails.php/admin/actions/save_user_email_template.php
 * Migrated Date: 2026-08-05 03:22:22
 */declare(strict_types=1);


namespace App\Controllers;

use PDO;

class UserEmailController
{
    private PDO $pdo;

    public function __construct(PDO $pdo)
    {
        $this->pdo = $pdo;
    }

    public function index(): void
    {
        // 1. Module check
        $moduleCheck = $this->pdo->prepare("SELECT is_enabled FROM modules WHERE module_name = ?");
        $moduleCheck->execute(['users']);
        if (!$moduleCheck->fetchColumn()) {
            http_response_code(403);
            exit('403 Forbidden: The User Management module is currently disabled.');
        }

        if (session_status() === PHP_SESSION_NONE) {
            session_start();
        }

        // 2. Admin authorization
        /** @var array{id: int, username: string} $currentUser */
        $currentUser = require_admin_page($this->pdo, 'manage_users', 'Manage user email templates');

        $message = $_SESSION['message'] ?? '';
        $error = $_SESSION['error'] ?? '';
        unset($_SESSION['message'], $_SESSION['error']);

        // Determine active template trigger (default to 'user_invitation')
        $get = $_GET;
        $triggerEvent = isset($get['trigger_event']) && is_string($get['trigger_event']) ? $get['trigger_event'] : 'user_invitation';
        if (!in_array($triggerEvent, ['user_invitation', 'password_reset'], true)) {
            $triggerEvent = 'user_invitation';
        }

        // Fetch template row
        $stmt = $this->pdo->prepare("SELECT * FROM user_email_templates WHERE trigger_event = ?");
        $stmt->execute([$triggerEvent]);
        /** @var array<string, mixed>|false $template */
        $template = $stmt->fetch(PDO::FETCH_ASSOC);

        $subject = $template && isset($template['subject']) && is_string($template['subject']) ? $template['subject'] : '';
        $body = $template && isset($template['body']) && is_string($template['body']) ? $template['body'] : '';
        $templateName = $template && isset($template['template_name']) && is_string($template['template_name']) ? $template['template_name'] : 'User Template';

        require_once __DIR__ . '/../Views/admin/manage_user_emails.php';
    }

    public function store(): void
    {
        if (session_status() === PHP_SESSION_NONE) {
            session_start();
        }

        $moduleCheck = $this->pdo->prepare("SELECT is_enabled FROM modules WHERE module_name = ?");
        $moduleCheck->execute(['users']);
        if (!$moduleCheck->fetchColumn()) {
            http_response_code(403);
            exit('403 Forbidden: The User Management module is currently disabled.');
        }

        $serverMethod = isset($_SERVER['REQUEST_METHOD']) && is_string($_SERVER['REQUEST_METHOD']) ? $_SERVER['REQUEST_METHOD'] : 'GET';
        if ($serverMethod !== 'POST') {
            http_response_code(405);
            exit('Method Not Allowed');
        }

        verify_csrf_token();
        /** @var array{id: int, username: string} $currentUser */
        $currentUser = require_permission($this->pdo, 'manage_users', 'Manage user email templates');

        $post = $_POST;
        $subject = isset($post['subject']) && is_string($post['subject']) ? trim($post['subject']) : '';
        $body = isset($post['body']) && is_string($post['body']) ? trim($post['body']) : '';
        $triggerEvent = isset($post['trigger_event']) && is_string($post['trigger_event']) ? trim($post['trigger_event']) : 'user_invitation';

        if ($subject === '' || $body === '') {
            $_SESSION['error'] = "Subject and body fields cannot be blank.";
            header('Location: /admin/users/emails?trigger_event=' . urlencode($triggerEvent));
            exit;
        }

        // Support updating either invitation or password reset templates dynamically based on trigger event
        if (!in_array($triggerEvent, ['user_invitation', 'password_reset'], true)) {
            $triggerEvent = 'user_invitation';
        }

        $stmt = $this->pdo->prepare("UPDATE user_email_templates SET subject = ?, body = ? WHERE trigger_event = ?");
        if ($stmt->execute([$subject, $body, $triggerEvent])) {
            $_SESSION['message'] = "User email template updated successfully.";
            $remoteAddr = isset($_SERVER['REMOTE_ADDR']) && is_string($_SERVER['REMOTE_ADDR']) ? $_SERVER['REMOTE_ADDR'] : '127.0.0.1';
            $audit = $this->pdo->prepare("INSERT INTO audit_logs (user_id, action, details, ip_address) VALUES (?, 'UPDATE_USER_TEMPLATE', ?, ?)");
            $audit->execute([$currentUser['id'], "Updated user email template layout for: {$triggerEvent}", $remoteAddr]);
        } else {
            $_SESSION['error'] = "Failed to update the template database record.";
        }

        header('Location: /admin/users/emails?trigger_event=' . urlencode($triggerEvent));
        exit;
    }
}
