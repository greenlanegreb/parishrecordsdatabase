<?php
/**
 * MIGRATED FILE MAPPING
 * ---------------------
 * Original Old File: admin/volunteer_dashboard.php/admin/actions/save_volunteer.php
 * Migrated Date: 2026-08-05 03:57:29
 */
declare(strict_types=1);

namespace App\Controllers;

use Exception;
use PDO;

class AdminVolunteerController
{
    private PDO $pdo;

    public function __construct(PDO $pdo)
    {
        $this->pdo = $pdo;
    }

    public function index(): void
    {
        if (!\is_module_enabled($this->pdo, 'volunteers')) {
            http_response_code(403);
            exit('403 Forbidden: The Volunteer Portal module is currently disabled.');
        }

        /** @var array{id: int, username: string, timezone?: string, date_format?: string} $currentUser */
        $currentUser = \require_admin_page($this->pdo, 'manage_volunteers', 'Manage and review volunteer applications and workflow');

        $message = $_SESSION['message'] ?? '';
        $error = $_SESSION['error'] ?? '';
        unset($_SESSION['message'], $_SESSION['error']);

        [$userTimezone, $fullFormatStr] = \get_user_time_prefs($currentUser);
        $systemName = \get_system_name($this->pdo);

        // Fetch schema columns
        $colsStmt = $this->pdo->query("SELECT * FROM volunteer_columns ORDER BY sort_order ASC, column_name ASC");
        /** @array<int, array<string, mixed>> $columns */
        $columns = $colsStmt !== false ? $colsStmt->fetchAll(PDO::FETCH_ASSOC) : [];

        // Fetch submissions
        $subsStmt = $this->pdo->query("SELECT vs.*, u.username FROM volunteer_submissions vs LEFT JOIN users u ON vs.created_by = u.id ORDER BY vs.created_at DESC");
        /** @array<int, array<string, mixed>> $submissions */
        $submissions = $subsStmt !== false ? $subsStmt->fetchAll(PDO::FETCH_ASSOC) : [];

        // Fetch submission values map
        $valsStmt = $this->pdo->query("SELECT submission_id, column_id, value_content FROM volunteer_submission_values");
        /** @array<int, array<string, mixed>> $rawValues */
        $rawValues = $valsStmt !== false ? $valsStmt->fetchAll(PDO::FETCH_ASSOC) : [];
        
        /** @array<int, array<int, string>> $submissionValues */
        $submissionValues = [];
        foreach ($rawValues as $val) {
            $subId = isset($val['submission_id']) ? (int)$val['submission_id'] : 0;
            $colId = isset($val['column_id']) ? (int)$val['column_id'] : 0;
            $vContent = isset($val['value_content']) && is_string($val['value_content']) ? $val['value_content'] : '';
            $submissionValues[$subId][$colId] = $vContent;
        }

        require_once __DIR__ . '/../Views/admin/volunteer_dashboard.php';
    }

    public function handleAction(): void
    {
        if (!\is_module_enabled($this->pdo, 'volunteers')) {
            http_response_code(403);
            exit('403 Forbidden: The Volunteer Portal module is currently disabled.');
        }

        $serverMethod = isset($_SERVER['REQUEST_METHOD']) && is_string($_SERVER['REQUEST_METHOD']) ? $_SERVER['REQUEST_METHOD'] : 'GET';
        if ($serverMethod !== 'POST') {
            http_response_code(405);
            exit('Method Not Allowed');
        }

        \verify_csrf_token();
        /** @var array{id: int, username: string} $currentUser */
        $currentUser = \require_permission($this->pdo, 'manage_volunteers', 'Manage and review volunteer applications and submissions');

        $basePath = defined('BASE_PATH') ? rtrim(BASE_PATH, '/') : '';
        $post = $_POST;
        $action = isset($post['action']) && is_string($post['action']) ? $post['action'] : '';
        $remoteAddr = isset($_SERVER['REMOTE_ADDR']) && is_string($_SERVER['REMOTE_ADDR']) ? $_SERVER['REMOTE_ADDR'] : '127.0.0.1';

        try {
            if ($action === 'delete_volunteer') {
                $volunteerId = isset($post['volunteer_id']) ? (int)$post['volunteer_id'] : 0;
                if ($volunteerId > 0) {
                    $delStmt = $this->pdo->prepare("DELETE FROM volunteer_submissions WHERE id = ?");
                    if ($delStmt->execute([$volunteerId])) {
                        $_SESSION['message'] = "Volunteer entry #{$volunteerId} has been successfully deleted.";
                        
                        $audit = $this->pdo->prepare("INSERT INTO audit_logs (user_id, action, details, ip_address) VALUES (?, 'DELETE_VOLUNTEER', ?, ?)");
                        $audit->execute([$currentUser['id'], "Deleted volunteer entry ID #{$volunteerId}", $remoteAddr]);
                    } else {
                        $_SESSION['error'] = "Failed to delete volunteer entry.";
                    }
                }
            } elseif ($action === 'update_interview') {
                $volunteerId = isset($post['volunteer_id']) ? (int)$post['volunteer_id'] : 0;
                $status = isset($post['status']) && is_string($post['status']) ? trim($post['status']) : 'Pending Review';
                $interviewDate = !empty($post['interview_date']) && is_string($post['interview_date']) ? $post['interview_date'] : null;
                $interviewNotes = isset($post['interview_notes']) && is_string($post['interview_notes']) ? trim($post['interview_notes']) : '';

                if ($volunteerId > 0) {
                    $stmt = $this->pdo->prepare("UPDATE volunteer_submissions SET status = ?, interview_date = ?, interview_notes = ? WHERE id = ?");
                    if ($stmt->execute([$status, $interviewDate, $interviewNotes, $volunteerId])) {
                        $_SESSION['message'] = "Interview details and status updated successfully for submission #{$volunteerId}.";
                        
                        // Trigger workflow email templates based on status change
                        require_once __DIR__ . '/../../includes/volunteer_mail_engine.php';
                        if ($status === 'Chat Scheduled') {
                            send_volunteer_templated_email($this->pdo, $volunteerId, 'chat_scheduled');
                        } elseif ($status === 'Accepted') {
                            send_volunteer_templated_email($this->pdo, $volunteerId, 'application_accepted');
                        }
                    } else {
                        $_SESSION['error'] = "Failed to update interview details.";
                    }
                }
            }
        } catch (Exception $e) {
            $_SESSION['error'] = "Database error: " . $e->getMessage();
        }

        header('Location: ' . BASE_PATH . '/admin/volunteers');
        exit;
    }
}
