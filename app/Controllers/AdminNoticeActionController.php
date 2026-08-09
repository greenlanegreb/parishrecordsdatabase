<?php
/**
 * MIGRATED FILE MAPPING
 * ---------------------
 * Original Old File: admin/actions/save_notice_inline.php
 * Migrated Date: 2026-08-05 04:38:39
 */
declare(strict_types=1);

namespace App\Controllers;

use Exception;
use PDO;

class AdminNoticeActionController
{
    private PDO $pdo;

    public function __construct(PDO $pdo)
    {
        $this->pdo = $pdo;
    }

    public function handle(): void
    {
        $serverMethod = isset($_SERVER['REQUEST_METHOD']) && is_string($_SERVER['REQUEST_METHOD']) ? $_SERVER['REQUEST_METHOD'] : 'GET';
        if ($serverMethod !== 'POST') {
            http_response_code(405);
            exit('Method Not Allowed');
        }

        // Verify CSRF token and enforce dynamic permission check
        verify_csrf_token();
        /** @var array{id: int, username: string} $currentUser */
        $currentUser = require_permission($this->pdo, 'manage_notices', 'Manage site-wide notices and broadcast announcements');

        $post = $_POST;
        $noticeId = isset($post['notice_id']) ? (int)$post['notice_id'] : 0;
        $updateAction = isset($post['update_action']) && is_string($post['update_action']) ? $post['update_action'] : '';
        $remoteAddr = isset($_SERVER['REMOTE_ADDR']) && is_string($_SERVER['REMOTE_ADDR']) ? $_SERVER['REMOTE_ADDR'] : '127.0.0.1';

        try {
            if ($noticeId > 0) {
                if ($updateAction === 'delete') {
                    $stmt = $this->pdo->prepare("DELETE FROM site_notices WHERE id = ?");
                    $stmt->execute([$noticeId]);
                    $_SESSION['message'] = "Notice deleted successfully.";
                    
                    $audit = $this->pdo->prepare("INSERT INTO audit_logs (user_id, action, details, ip_address) VALUES (?, 'DELETE_NOTICE', ?, ?)");
                    $audit->execute([$currentUser['id'], "Deleted notice ID: {$noticeId}", $remoteAddr]);
                } elseif ($updateAction === 'save') {
                    $title = isset($post['title']) && is_string($post['title']) ? trim($post['title']) : '';
                    $content = isset($post['content']) && is_string($post['content']) ? trim($post['content']) : '';
                    
                    // Capture roles array from checkboxes and convert to comma-separated string
                    /** @var array<int, string> $rolesArray */
                    $rolesArray = isset($post['target_roles']) && is_array($post['target_roles']) ? $post['target_roles'] : [];
                    if (in_array('everyone', $rolesArray, true)) {
                        $targetRoles = 'everyone';
                    } else {
                        $targetRoles = !empty($rolesArray) ? implode(',', $rolesArray) : 'public';
                    }

                    $isDismissible = isset($post['is_dismissible']) ? 1 : 0;
                    $isActive = isset($post['is_active']) ? 1 : 0;
                    $displayOrder = isset($post['display_order']) ? (int)$post['display_order'] : 0;

                    if ($title !== '' && $content !== '') {
                        $stmt = $this->pdo->prepare("
                            UPDATE site_notices 
                            SET title = ?, content = ?, target_roles = ?, is_dismissible = ?, is_active = ?, display_order = ? 
                            WHERE id = ?
                        ");
                        $stmt->execute([$title, $content, $targetRoles, $isDismissible, $isActive, $displayOrder, $noticeId]);
                        $_SESSION['message'] = "Notice updated successfully.";
                        
                        $audit = $this->pdo->prepare("INSERT INTO audit_logs (user_id, action, details, ip_address) VALUES (?, 'UPDATE_NOTICE', ?, ?)");
                        $audit->execute([$currentUser['id'], "Updated notice ID: {$noticeId} ('{$title}')", $remoteAddr]);
                    } else {
                        $_SESSION['error'] = "Notice title and content cannot be empty.";
                    }
                }
            } else {
                $_SESSION['error'] = "Invalid notice reference.";
            }
        } catch (Exception $e) {
            $_SESSION['error'] = "Database error: " . $e->getMessage();
        }

        header('Location: ' . BASE_PATH . '/admin/settings');
        exit;
    }
}
