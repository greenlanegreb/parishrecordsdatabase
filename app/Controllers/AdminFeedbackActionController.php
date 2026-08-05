<?php
/**
 * MIGRATED FILE MAPPING
 * ---------------------
 * Original Old File: admin/actions/save_feedback.php
 * Migrated Date: 2026-08-05 04:33:57
 */declare(strict_types=1);


namespace App\Controllers;

use Exception;
use PDO;

class AdminFeedbackActionController
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

        // Ensure the feedback module is enabled; otherwise block action execution
        if (!is_module_enabled($this->pdo, 'feedback')) {
            http_response_code(403);
            exit('403 Forbidden: The Feedback Submissions module is currently disabled.');
        }

        $serverMethod = isset($_SERVER['REQUEST_METHOD']) && is_string($_SERVER['REQUEST_METHOD']) ? $_SERVER['REQUEST_METHOD'] : 'GET';
        if ($serverMethod !== 'POST') {
            http_response_code(405);
            exit('Method Not Allowed');
        }

        // Verify CSRF token and enforce permission check
        verify_csrf_token();
        /** @var array{id: int, username: string} $currentUser */
        $currentUser = require_permission($this->pdo, 'manage_feedback', 'Manage and moderate public feedback and submissions');

        $post = $_POST;
        $action = isset($post['action']) && is_string($post['action']) ? $post['action'] : '';
        $remoteAddr = isset($_SERVER['REMOTE_ADDR']) && is_string($_SERVER['REMOTE_ADDR']) ? $_SERVER['REMOTE_ADDR'] : '127.0.0.1';

        try {
            if ($action === 'update_feedback') {
                $feedbackId = isset($post['feedback_id']) ? (int)$post['feedback_id'] : 0;
                $status = isset($post['status']) && is_string($post['status']) ? trim($post['status']) : 'Pending';
                $adminNotes = isset($post['admin_notes']) && is_string($post['admin_notes']) ? trim($post['admin_notes']) : '';

                $allowedStatuses = ['Pending', 'Completed', 'Revised from Proposal', 'Rejected'];
                if (!in_array($status, $allowedStatuses, true)) {
                    $status = 'Pending';
                }

                if ($feedbackId > 0) {
                    $updateStmt = $this->pdo->prepare("UPDATE feedback SET status = ?, admin_notes = ? WHERE id = ?");
                    if ($updateStmt->execute([$status, $adminNotes, $feedbackId])) {
                        $_SESSION['message'] = "Feedback entry #{$feedbackId} has been successfully updated.";
                        
                        $audit = $this->pdo->prepare("INSERT INTO audit_logs (user_id, action, details, ip_address) VALUES (?, 'UPDATE_FEEDBACK', ?, ?)");
                        $audit->execute([$currentUser['id'], "Updated feedback entry #{$feedbackId} status to {$status}", $remoteAddr]);
                    } else {
                        $_SESSION['error'] = "Failed to update feedback entry.";
                    }
                }
            } elseif ($action === 'delete_feedback') {
                $feedbackId = isset($post['feedback_id']) ? (int)$post['feedback_id'] : 0;
                if ($feedbackId > 0) {
                    $delStmt = $this->pdo->prepare("DELETE FROM feedback WHERE id = ?");
                    if ($delStmt->execute([$feedbackId])) {
                        $_SESSION['message'] = "Feedback entry #{$feedbackId} has been successfully deleted.";
                        
                        $audit = $this->pdo->prepare("INSERT INTO audit_logs (user_id, action, details, ip_address) VALUES (?, 'DELETE_FEEDBACK', ?, ?)");
                        $audit->execute([$currentUser['id'], "Deleted feedback entry #{$feedbackId}", $remoteAddr]);
                    } else {
                        $_SESSION['error'] = "Failed to delete feedback entry.";
                    }
                }
            }
        } catch (Exception $e) {
            $_SESSION['error'] = "Database error: " . $e->getMessage();
        }

        header('Location: /admin/feedback_dashboard.php');
        exit;
    }
}
