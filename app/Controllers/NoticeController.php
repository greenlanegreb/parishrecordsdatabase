<?php
/**
 * MIGRATED FILE MAPPING
 * ---------------------
 * Original Old File: admin/notices.php
 * Migrated Date: 2026-08-05 03:40:44
 */declare(strict_types=1);


namespace App\Controllers;

use PDO;

class NoticeController
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

        /** @var array{id: int, username: string} $currentUser */
        $currentUser = require_admin_page($this->pdo, 'manage_notices', 'Manage site-wide notices and broadcast announcements');

        $message = $_SESSION['message'] ?? '';
        $error = $_SESSION['error'] ?? '';
        unset($_SESSION['message'], $_SESSION['error']);

        $stmtNotices = $this->pdo->query("SELECT * FROM site_notices ORDER BY display_order ASC, id DESC");
        /** @var array<int, array<string, mixed>> $notices */
        $notices = $stmtNotices !== false ? $stmtNotices->fetchAll(PDO::FETCH_ASSOC) : [];

        require_once __DIR__ . '/../Views/admin/notices.php';
    }

    public function store(): void
    {
        if (session_status() === PHP_SESSION_NONE) {
            session_start();
        }

        $serverMethod = isset($_SERVER['REQUEST_METHOD']) && is_string($_SERVER['REQUEST_METHOD']) ? $_SERVER['REQUEST_METHOD'] : 'GET';
        if ($serverMethod !== 'POST') {
            http_response_code(405);
            exit('Method Not Allowed');
        }

        verify_csrf_token();
        /** @var array{id: int, username: string} $currentUser */
        $currentUser = require_permission($this->pdo, 'manage_notices', 'Manage site-wide notices and broadcast announcements');

        $post = $_POST;
        $action = isset($post['action']) && is_string($post['action']) ? $post['action'] : '';

        if ($action === 'create') {
            $title = isset($post['title']) && is_string($post['title']) ? trim($post['title']) : '';
            $content = isset($post['content']) && is_string($post['content']) ? trim($post['content']) : '';
            $isDismissible = isset($post['is_dismissible']) ? 1 : 0;
            
            $rawRoles = isset($post['target_roles']) && is_array($post['target_roles']) ? $post['target_roles'] : ['everyone'];
            $sanitizedRoles = array_map(static fn($r) => is_string($r) ? trim($r) : '', $rawRoles);
            $targetRoles = implode(',', array_filter($sanitizedRoles));
            if ($targetRoles === '') {
                $targetRoles = 'everyone';
            }

            $displayOrder = isset($post['display_order']) ? (int)$post['display_order'] : 0;

            if ($title === '' || $content === '') {
                $_SESSION['error'] = __('notices.error_blank');
            } else {
                $stmt = $this->pdo->prepare("INSERT INTO site_notices (title, content, target_roles, is_dismissible, display_order) VALUES (?, ?, ?, ?, ?)");
                $stmt->execute([$title, $content, $targetRoles, $isDismissible, $displayOrder]);
                $_SESSION['message'] = __('notices.msg_created');
            }
        } elseif ($action === 'delete') {
            $id = isset($post['notice_id']) ? (int)$post['notice_id'] : 0;
            if ($id > 0) {
                $stmt = $this->pdo->prepare("DELETE FROM site_notices WHERE id = ?");
                $stmt->execute([$id]);
                $_SESSION['message'] = __('notices.msg_deleted');
            }
        }

        header('Location: /admin/notices');
        exit;
    }
}
