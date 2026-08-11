<?php
/**
 * MIGRATED FILE MAPPING
 * ---------------------
 * Original Old File: admin/view_ticket.php/admin/actions/save_ticket_reply.php
 * Migrated Date: 2026-08-05 03:53:11
 */
declare(strict_types=1);

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

    public function index(): void
    {
        if (!\is_module_enabled($this->pdo, 'feedback')) {
            http_response_code(403);
            exit('403 Forbidden: The Feedback module is currently disabled.');
        }

        /** @var array{id: int, username: string, timezone?: string} $currentUser */
        $currentUser = \require_admin_page($this->pdo, 'manage_feedback', 'Manage feedback and support tickets');

        $message = $_SESSION['message'] ?? '';
        $error = $_SESSION['error'] ?? '';
        unset($_SESSION['message'], $_SESSION['error']);

        [$userTimezone, $fullFormatStr] = \get_user_time_prefs($currentUser);
        $systemName = \get_system_name($this->pdo);

        $stmt = $this->pdo->query(
            'SELECT t.* FROM feedback_tickets t ORDER BY t.created_at DESC'
        );
        /** @var array<int, array<string, mixed>> $tickets */
        $tickets = $stmt !== false ? $stmt->fetchAll(PDO::FETCH_ASSOC) : [];

        foreach ($tickets as &$t) {
            $t['submitter_display'] = $this->resolveTicketSubmitterDisplay($t, $currentUser);
        }
        unset($t);

        require_once __DIR__ . '/../Views/admin/feedback_dashboard.php';
    }

    public function show(int|string $ticketId): void
    {
        if (!\is_module_enabled($this->pdo, 'feedback')) {
            http_response_code(403);
            exit('403 Forbidden: The Feedback module is currently disabled.');
        }

        $ticketId = (int) $ticketId;
        $basePath = defined('BASE_PATH') ? rtrim(BASE_PATH, '/') : '';

        /** @var array{id: int, username: string, timezone?: string} $currentUser */
        $currentUser = \require_admin_page($this->pdo, 'manage_feedback', 'View and reply to feedback tickets');

        $message = $_SESSION['message'] ?? '';
        $error = $_SESSION['error'] ?? '';
        unset($_SESSION['message'], $_SESSION['error']);

        [$userTimezone, $fullFormatStr] = \get_user_time_prefs($currentUser);
        $systemName = \get_system_name($this->pdo);

        $stmt = $this->pdo->prepare('SELECT t.* FROM feedback_tickets t WHERE t.id = ?');
        $stmt->execute([$ticketId]);
        /** @var array<string, mixed>|false $ticket */
        $ticket = $stmt->fetch(PDO::FETCH_ASSOC);

        if ($ticket === false) {
            header('Location: ' . BASE_PATH . '/admin/tickets');
            exit;
        }

        $ticket['submitter_display'] = $this->resolveTicketSubmitterDisplay($ticket, $currentUser);

        $dynStmt = $this->pdo->prepare("
            SELECT fc.column_name, ftv.value_content
            FROM feedback_ticket_values ftv
            JOIN feedback_columns fc ON ftv.column_id = fc.id
            WHERE ftv.ticket_id = ?
            ORDER BY fc.sort_order ASC
        ");
        $dynStmt->execute([$ticketId]);
        /** @var array<int, array<string, mixed>> $dynValues */
        $dynValues = $dynStmt->fetchAll(PDO::FETCH_ASSOC);

        $repliesStmt = $this->pdo->prepare(
            'SELECT r.* FROM feedback_ticket_replies r WHERE r.ticket_id = ? ORDER BY r.created_at ASC'
        );
        $repliesStmt->execute([$ticketId]);
        /** @var array<int, array<string, mixed>> $thread */
        $thread = $repliesStmt->fetchAll(PDO::FETCH_ASSOC);

        foreach ($thread as &$rep) {
            $rid = isset($rep['user_id']) ? (int) $rep['user_id'] : 0;
            if ($rid > 0 && function_exists('format_user_display_name_by_id')) {
                $rep['reply_display_name'] = format_user_display_name_by_id($this->pdo, $rid, $currentUser);
            } else {
                $rep['reply_display_name'] = 'Staff';
            }
        }
        unset($rep);

        require_once __DIR__ . '/../Views/admin/view_ticket.php';
    }

    public function handleAction(): void
    {
        if (!\is_module_enabled($this->pdo, 'feedback')) {
            http_response_code(403);
            exit('403 Forbidden');
        }

        $serverMethod = isset($_SERVER['REQUEST_METHOD']) && is_string($_SERVER['REQUEST_METHOD'])
            ? $_SERVER['REQUEST_METHOD'] : 'GET';
        if ($serverMethod !== 'POST') {
            http_response_code(405);
            exit('Method Not Allowed');
        }

        \verify_csrf_token();
        /** @var array{id: int, username: string} $currentUser */
        $currentUser = \require_permission($this->pdo, 'manage_feedback', 'Manage feedback replies');

        $basePath = defined('BASE_PATH') ? rtrim(BASE_PATH, '/') : '';
        $post = $_POST;
        $action = isset($post['action']) && is_string($post['action']) ? $post['action'] : '';
        $ticketId = isset($post['ticket_id']) ? (int) $post['ticket_id'] : 0;

        if ($ticketId <= 0) {
            header('Location: ' . BASE_PATH . '/admin/tickets');
            exit;
        }

        $tStmt = $this->pdo->prepare('SELECT * FROM feedback_tickets WHERE id = ?');
        $tStmt->execute([$ticketId]);
        /** @var array{id: int, name?: string, email?: string, first_name?: string, surname?: string}|false $ticket */
        $ticket = $tStmt->fetch(PDO::FETCH_ASSOC);

        if ($ticket === false) {
            header('Location: ' . BASE_PATH . '/admin/tickets');
            exit;
        }

        $systemName = \get_system_name($this->pdo);
        $redirectUrl = BASE_PATH . '/admin/tickets/' . $ticketId;

        try {
            if ($action === 'delete_ticket') {
                $this->pdo->prepare('DELETE FROM feedback_ticket_replies WHERE ticket_id = ?')->execute([$ticketId]);
                $this->pdo->prepare('DELETE FROM feedback_ticket_values WHERE ticket_id = ?')->execute([$ticketId]);
                $this->pdo->prepare('DELETE FROM feedback_tickets WHERE id = ?')->execute([$ticketId]);
                $_SESSION['message'] = "Support ticket #{$ticketId} deleted successfully.";
                $redirectUrl = BASE_PATH . '/admin/tickets';
            } elseif ($action === 'update_status') {
                $status = isset($post['status']) && is_string($post['status']) ? trim($post['status']) : 'Pending';
                $this->pdo->prepare('UPDATE feedback_tickets SET status = ? WHERE id = ?')->execute([$status, $ticketId]);
                $_SESSION['message'] = "Ticket status updated to {$status}.";
            } elseif ($action === 'post_reply') {
                $message = isset($post['reply_message']) && is_string($post['reply_message'])
                    ? trim($post['reply_message']) : '';
                if ($message !== '') {
                    $stmt = $this->pdo->prepare(
                        'INSERT INTO feedback_ticket_replies (ticket_id, user_id, message, is_admin_reply) VALUES (?, ?, ?, 1)'
                    );
                    $stmt->execute([$ticketId, $currentUser['id'], $message]);

                    $to = isset($ticket['email']) && is_string($ticket['email']) ? $ticket['email'] : '';
                    $helloName = $this->resolveTicketSubmitterDisplay($ticket, $currentUser);
                    $subject = "[{$systemName}] Update on Ticket #{$ticketId}";
                    $body = "Hello {$helloName},\n\nAn administrator has posted a reply to your support ticket (#{$ticketId}):\n\n{$message}\n\nBest regards,\n{$systemName}";

                    $serverHost = isset($_SERVER['HTTP_HOST']) && is_string($_SERVER['HTTP_HOST'])
                        ? $_SERVER['HTTP_HOST'] : 'localhost';
                    $parsedHost = parse_url('http://' . $serverHost, PHP_URL_HOST) ?: 'localhost';
                    $headers = "From: no-reply@{$parsedHost}\r\n" .
                               "Reply-To: no-reply@{$parsedHost}\r\n" .
                               'X-Mailer: PHP/' . phpversion();

                    if ($to !== '') {
                        @mail($to, $subject, $body, $headers);
                    }
                    $_SESSION['message'] = 'Reply sent and email notification dispatched successfully.';
                }
            }
        } catch (Exception $e) {
            $_SESSION['error'] = 'Database error: ' . $e->getMessage();
        }

        header('Location: ' . $redirectUrl);
        exit;
    }

    /**
     * @param array<string, mixed> $ticket
     * @param array{id?: int|string}|null $viewer
     */
    private function resolveTicketSubmitterDisplay(array $ticket, ?array $viewer): string
    {
        $uid = isset($ticket['user_id']) ? (int) $ticket['user_id'] : 0;
        if ($uid > 0 && function_exists('format_user_display_name_by_id')) {
            return format_user_display_name_by_id($this->pdo, $uid, $viewer);
        }

        $first = isset($ticket['first_name']) && is_string($ticket['first_name']) ? trim($ticket['first_name']) : '';
        $surname = isset($ticket['surname']) && is_string($ticket['surname']) ? trim($ticket['surname']) : '';
        $full = trim($first . ' ' . $surname);
        if ($full !== '') {
            return $full;
        }
        if (isset($ticket['name']) && is_string($ticket['name']) && trim($ticket['name']) !== '') {
            return trim($ticket['name']);
        }
        return 'Anonymous';
    }
}
