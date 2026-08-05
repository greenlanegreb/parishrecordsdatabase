<?php
/**
 * MIGRATED FILE MAPPING
 * ---------------------
 * Original Old File: admin/manage_volunteer_emails.php/admin/actions/save_volunteer_email_template.php
 * Migrated Date: 2026-08-05 03:27:14
 */declare(strict_types=1);


namespace App\Controllers;

use PDO;

class VolunteerEmailController
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
        $moduleCheck->execute(['volunteers']);
        if (!$moduleCheck->fetchColumn()) {
            http_response_code(403);
            exit('403 Forbidden: The Volunteer Portal module is currently disabled.');
        }

        if (session_status() === PHP_SESSION_NONE) {
            session_start();
        }

        // 2. Admin authorization
        /** @var array{id: int, username: string} $currentUser */
        $currentUser = require_admin_page($this->pdo, 'manage_volunteers', 'Manage volunteer email templates and triggers');

        // Fetch all templates
        $stmtTemplates = $this->pdo->query("SELECT * FROM volunteer_email_templates ORDER BY id ASC");
        /** @var array<int, array<string, mixed>> $templates */
        $templates = $stmtTemplates !== false ? $stmtTemplates->fetchAll(PDO::FETCH_ASSOC) : [];

        // Fetch custom schema columns
        $stmtColumns = $this->pdo->query("SELECT column_name FROM volunteer_columns ORDER BY sort_order ASC");
        /** @var array<int, string> $columns */
        $columns = $stmtColumns !== false ? $stmtColumns->fetchAll(PDO::FETCH_COLUMN) : [];

        $message = $_SESSION['message'] ?? '';
        $error = $_SESSION['error'] ?? '';
        unset($_SESSION['message'], $_SESSION['error']);

        require_once __DIR__ . '/../Views/admin/manage_volunteer_emails.php';
    }

    public function store(): void
    {
        if (session_status() === PHP_SESSION_NONE) {
            session_start();
        }

        $moduleCheck = $this->pdo->prepare("SELECT is_enabled FROM modules WHERE module_name = ?");
        $moduleCheck->execute(['volunteers']);
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
        /** @var array{id: int, username: string} $currentUser */
        $currentUser = require_permission($this->pdo, 'manage_volunteers', 'Manage volunteer email templates');

        $post = $_POST;
        $templateId = isset($post['template_id']) ? (int)$post['template_id'] : 0;
        $subject = isset($post['subject']) && is_string($post['subject']) ? trim($post['subject']) : '';
        $body = isset($post['body']) && is_string($post['body']) ? trim($post['body']) : '';

        if ($templateId > 0 && $subject !== '' && $body !== '') {
            $stmt = $this->pdo->prepare("UPDATE volunteer_email_templates SET subject = ?, body = ? WHERE id = ?");
            $stmt->execute([$subject, $body, $templateId]);
            
            $_SESSION['message'] = "Email template updated successfully.";
            $remoteAddr = isset($_SERVER['REMOTE_ADDR']) && is_string($_SERVER['REMOTE_ADDR']) ? $_SERVER['REMOTE_ADDR'] : '127.0.0.1';
            $audit = $this->pdo->prepare("INSERT INTO audit_logs (user_id, action, details, ip_address) VALUES (?, 'UPDATE_VOLUNTEER_TEMPLATE', ?, ?)");
            $audit->execute([$currentUser['id'], "Updated volunteer email template ID: {$templateId}", $remoteAddr]);
        } else {
            $_SESSION['error'] = "Subject and body fields cannot be empty.";
        }

        header('Location: /admin/volunteers/emails');
        exit;
    }
}
