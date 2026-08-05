<?php
/**
 * MIGRATED FILE MAPPING
 * ---------------------
 * Original Old File: admin/manage_feedback_emails.php/admin/actions/save_feedback_email_template.php
 * Migrated Date: 2026-08-04 09:32:53
 */declare(strict_types=1);


namespace App\Controllers;

use PDO;

class FeedbackEmailController
{
    private PDO $pdo;

    public function __construct(PDO $pdo)
    {
        $this->pdo = $pdo;
    }

    public function index(): void
    {
        $moduleCheck = $this->pdo->prepare("SELECT is_enabled FROM modules WHERE module_name = ?");
        $moduleCheck->execute(['feedback']);
        if (!$moduleCheck->fetchColumn()) {
            http_response_code(403);
            exit('403 Forbidden: The Feedback module is currently disabled.');
        }

        if (session_status() === PHP_SESSION_NONE) {
            session_start();
        }

        /** @var array{id: int, username: string} $currentUser */
        $currentUser = require_admin_page($this->pdo, 'manage_feedback', 'Manage feedback email templates and triggers');

        $stmtTemplates = $this->pdo->query("SELECT * FROM feedback_email_templates ORDER BY id ASC");
        /** @array<int, array<string, mixed>> $templates */
        $templates = $stmtTemplates !== false ? $stmtTemplates->fetchAll(PDO::FETCH_ASSOC) : [];

        $stmtColumns = $this->pdo->query("SELECT column_name FROM feedback_columns ORDER BY sort_order ASC");
        /** @array<int, string> $columns */
        $columns = $stmtColumns !== false ? $stmtColumns->fetchAll(PDO::FETCH_COLUMN) : [];

        $message = $_SESSION['message'] ?? '';
        $error = $_SESSION['error'] ?? '';
        unset($_SESSION['message'], $_SESSION['error']);

        require_once __DIR__ . '/../Views/admin/manage_feedback_emails.php';
    }

    public function store(): void
    {
        if (session_status() === PHP_SESSION_NONE) {
            session_start();
        }

        $moduleCheck = $this->pdo->prepare("SELECT is_enabled FROM modules WHERE module_name = ?");
        $moduleCheck->execute(['feedback']);
        if (!$moduleCheck->fetchColumn()) {
            http_response_code(403);
            exit('403 Forbidden');
        }

        $serverMethod = isset($_SERVER['REQUEST_METHOD']) && is_string($_SERVER['REQUEST_METHOD']) ? $_SERVER['REQUEST_METHOD'] : 'GET';
        if ($serverMethod !== 'POST') {
            http_response_code(405);
            exit('Method Not Allowed');
        }

        verify_csrf_token();
        require_permission($this->pdo, 'manage_feedback', 'Manage feedback email templates');

        $post = $_POST;
        $templateId = isset($post['template_id']) ? (int)$post['template_id'] : 0;
        $subject = isset($post['subject']) && is_string($post['subject']) ? trim($post['subject']) : '';
        $body = isset($post['body']) && is_string($post['body']) ? trim($post['body']) : '';

        if ($templateId > 0 && $subject !== '' && $body !== '') {
            $stmt = $this->pdo->prepare("UPDATE feedback_email_templates SET subject = ?, body = ? WHERE id = ?");
            $stmt->execute([$subject, $body, $templateId]);
            
            $_SESSION['message'] = "Feedback email template updated successfully.";
        } else {
            $_SESSION['error'] = "Subject and body fields cannot be empty.";
        }

        header('Location: /admin/feedback/emails');
        exit;
    }
}
