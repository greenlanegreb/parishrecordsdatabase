<?php
/**
 * MIGRATED FILE MAPPING
 * ---------------------
 * Original Old File: admin/view_ticket.php/admin/actions/save_ticket_reply.php
 * Migrated Date: 2026-08-05 03:53:11
 */declare(strict_types=1);


namespace App\Controllers;

use Exception;
use PDO;

class AdminTicketController
{
    private PDO $pdo;

    public function __construct(PDO $pdo)
    {
        $this->pdo = $pdo;
    }

    public function show(int $ticketId): void
    {
        if (session_status() === PHP_SESSION_NONE) {
            session_start();
        }

        if (!is_module_enabled($this->pdo, 'feedback')) {
            http_response_code(403);
            exit('403 Forbidden: The Feedback module is currently disabled.');
        }

        /** @var array{id: int, username: string, timezone?: string} $currentUser */
        $currentUser = require_admin_page($this->pdo, 'manage_feedback', 'View and reply to feedback tickets');

        $message = $_SESSION['message'] ?? '';
        $error = $_SESSION['error'] ?? '';
        unset($_SESSION['message'], $_SESSION['error']);

        [$userTimezone, $fullFormatStr] = get_user_time_prefs($currentUser);
        $systemName = get_system_name($this->pdo);

        $stmt = $this->pdo->prepare("SELECT t.*, u.username FROM feedback_tickets t LEFT JOIN users u ON t.user_id = u.id WHERE t.id = ?");
        $stmt->execute([$ticketId]);
        /** @array{id: int, subject?: string, name: string, email: string, created_at: string, status: string, user_id?: int, username?: string}|false $ticket */
        $ticket = $stmt->fetch(PDO::FETCH_ASSOC);

        if ($ticket === false) {
            header('Location: /admin/feedback_dashboard.php');
            exit;
        }

        // Fetch dynamic custom field responses associated with this ticket
        $dynStmt = $this->pdo->prepare("
            SELECT fc.column_name, ftv.value_content 
            FROM feedback_ticket_values ftv
            JOIN feedback_columns fc ON ftv.column_id = fc.id
            WHERE ftv.ticket_id = ?
            ORDER BY fc.sort_order ASC
        ");
        $dynStmt->execute([$ticketId]);
        /** @array<int, array<string, mixed>> $dynValues */
        $dynValues = $dynStmt->fetchAll(PDO::FETCH_ASSOC);

        $repliesStmt = $this->pdo->prepare("SELECT r.*, u.username FROM feedback_ticket_replies r LEFT JOIN users u ON r.user_id = u.id WHERE r.ticket_id = ? ORDER BY r.created_at ASC");
        $repliesStmt->execute([$ticketId]);
        /** @array<int, array<string, mixed>> $thread */
        $thread = $repliesStmt->fetchAll(PDO::FETCH_ASSOC);

        require_once __DIR__ . '/../Views/admin/view_ticket.php';
    }

    public function handleAction(): void
    {
        if (session_status() === PHP_SESSION_NONE) {
            session_start();
        }

        if (!is_module_enabled($this->pdo, 'feedback')) {
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
        $currentUser = require_permission($this->pdo, 'manage_feedback', 'Manage feedback replies');

        $post = $_POST;
        $action = isset($post['action']) && is_string($post['action']) ? $post['action'] : '';
        $ticketId = isset($post['ticket_id']) ? (int)$post['ticket_id'] : 0;

        if ($ticketId <= 0) {
            header('Location: /admin/feedback_dashboard.php');
            exit;
        }

        $tStmt = $this->pdo->prepare("SELECT * FROM feedback_tickets WHERE id = ?");
        $tStmt->execute([$ticketId]);
        /** @array{id: int, name: string, email: string}|false $ticket */
        $ticket = $tStmt->fetch(PDO::FETCH_ASSOC);

        if ($ticket === false) {
            header('Location: /admin/feedback_dashboard.php');
            exit;
        }

        $systemName = get_system_name($this->pdo);

        try {
            if ($action === 'update_status') {
                $status = isset($post['status']) && is_string($post['status']) ? trim($post['status']) : 'Pending';
                $this->pdo->prepare("UPDATE feedback_tickets SET status = ? WHERE id = ?")->execute([$status, $ticketId]);
                $_SESSION['message'] = "Ticket status updated to {$status}.";
            } elseif ($action === 'post_reply') {
                $message = isset($post['reply_message']) && is_string($post['reply_message']) ? trim($post['reply_message']) : '';
                if ($message !== '') {
                    $stmt = $this->pdo->prepare("INSERT INTO feedback_ticket_replies (ticket_id, user_id, message, is_admin_reply) VALUES (?, ?, ?, 1)");
                    $stmt->execute([$ticketId, $currentUser['id'], $message]);

                    // Send email notification to submitter
                    $to = $ticket['email'];
                    $subject = "[{$systemName}] Update on Ticket #{$ticketId}";
                    $body = "Hello {$ticket['name']},\n\nAn administrator has posted a reply to your support ticket (#{$ticketId}):\n\n{$message}\n\nBest regards,\n{$systemName}";
                    
                    $serverHost = isset($_SERVER['HTTP_HOST']) && is_string($_SERVER['HTTP_HOST']) ? $_SERVER['HTTP_HOST'] : 'localhost';
                    $parsedHost = parse_url('http://' . $serverHost, PHP_URL_HOST) ?: 'localhost';
                    $headers = "From: no-reply@{$parsedHost}\r\n" .
                               "Reply-To: no-reply@{$parsedHost}\r\n" .
                               "X-Mailer: PHP/" . phpversion();
                    
                    @mail($to, $subject, $body, $headers);
                    $_SESSION['message'] = "Reply sent and email notification dispatched successfully.";
                }
            }
        } catch (Exception $e) {
            $_SESSION['error'] = "Database error: " . $e->getMessage();
        }

        header('Location: /admin/view_ticket.php?id=' . $ticketId);
        exit;
    }
}
