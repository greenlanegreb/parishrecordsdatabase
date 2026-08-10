<?php
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
        if (($_SERVER['REQUEST_METHOD'] ?? 'GET') !== 'POST') {
            http_response_code(405);
            exit('Method Not Allowed');
        }

        verify_csrf_token();

        /** @var array{id: int, username: string} $currentUser */
        $currentUser = require_user_permission(
            $this->pdo,
            'manage_notices',
            'Manage site-wide notices and broadcast announcements'
        );

        $post         = $_POST;
        $noticeId     = isset($post['notice_id']) ? (int) $post['notice_id'] : 0;
        $updateAction = isset($post['update_action']) && is_string($post['update_action']) ? $post['update_action'] : '';
        $remoteAddr   = isset($_SERVER['REMOTE_ADDR']) && is_string($_SERVER['REMOTE_ADDR'])
            ? $_SERVER['REMOTE_ADDR'] : '127.0.0.1';

        try {
            // ----- CREATE -----
            if ($updateAction === 'create') {
                $title   = isset($post['title']) && is_string($post['title']) ? trim($post['title']) : '';
                $content = isset($post['content']) && is_string($post['content']) ? trim($post['content']) : '';

                $rolesArray = isset($post['target_roles']) && is_array($post['target_roles']) ? $post['target_roles'] : [];
                if (in_array('everyone', $rolesArray, true) || $rolesArray === []) {
                    $targetRoles = 'everyone';
                } else {
                    $targetRoles = implode(',', array_map('strval', $rolesArray));
                }

                $isDismissible = isset($post['is_dismissible']) ? 1 : 0;
                $isActive      = isset($post['is_active']) ? 1 : 0;
                // Default new notices to active if checkbox omitted
                if (!isset($post['is_active']) && !isset($post['is_active_present'])) {
                    $isActive = 1;
                }
                $displayOrder = isset($post['display_order']) ? (int) $post['display_order'] : 0;

                if ($title === '' || $content === '') {
                    flash_error('Notice title and content cannot be empty.');
                } else {
                    $stmt = $this->pdo->prepare(
                        "INSERT INTO site_notices
                         (title, content, target_roles, is_dismissible, is_active, display_order)
                         VALUES (?, ?, ?, ?, ?, ?)"
                    );
                    $stmt->execute([$title, $content, $targetRoles, $isDismissible, $isActive, $displayOrder]);
                    flash_success('Notice created successfully.');
                    audit($this->pdo, (int) $currentUser['id'], 'CREATE_NOTICE', "Created notice '{$title}'", $remoteAddr);
                }

            // ----- DELETE -----
            } elseif ($noticeId > 0 && $updateAction === 'delete') {
                $stmt = $this->pdo->prepare("DELETE FROM site_notices WHERE id = ?");
                $stmt->execute([$noticeId]);
                flash_success('Notice deleted successfully.');
                audit($this->pdo, (int) $currentUser['id'], 'DELETE_NOTICE', "Deleted notice ID: {$noticeId}", $remoteAddr);

            // ----- UPDATE -----
            } elseif ($noticeId > 0 && $updateAction === 'save') {
                $title   = isset($post['title']) && is_string($post['title']) ? trim($post['title']) : '';
                $content = isset($post['content']) && is_string($post['content']) ? trim($post['content']) : '';

                $rolesArray = isset($post['target_roles']) && is_array($post['target_roles']) ? $post['target_roles'] : [];
                if (in_array('everyone', $rolesArray, true) || $rolesArray === []) {
                    $targetRoles = 'everyone';
                } else {
                    $targetRoles = implode(',', array_map('strval', $rolesArray));
                }

                $isDismissible = isset($post['is_dismissible']) ? 1 : 0;
                $isActive      = isset($post['is_active']) ? 1 : 0;
                $displayOrder  = isset($post['display_order']) ? (int) $post['display_order'] : 0;

                if ($title === '' || $content === '') {
                    flash_error('Notice title and content cannot be empty.');
                } else {
                    $stmt = $this->pdo->prepare(
                        "UPDATE site_notices
                         SET title = ?, content = ?, target_roles = ?, is_dismissible = ?, is_active = ?, display_order = ?
                         WHERE id = ?"
                    );
                    $stmt->execute([$title, $content, $targetRoles, $isDismissible, $isActive, $displayOrder, $noticeId]);
                    flash_success('Notice updated successfully.');
                    audit($this->pdo, (int) $currentUser['id'], 'UPDATE_NOTICE', "Updated notice ID: {$noticeId} ('{$title}')", $remoteAddr);
                }
            } else {
                flash_error('Invalid notice action.');
            }
        } catch (Exception $e) {
            flash_error('Database error: ' . $e->getMessage());
        }

        redirect('/admin/settings#tab-notices');
    }
}
