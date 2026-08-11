<?php
/**
 * MIGRATED FILE MAPPING
 * ---------------------
 * Original Old File: admin/feedback_dashboard.php
 * Migrated Date: 2026-08-04 09:28:44
 */
declare(strict_types=1);

namespace App\Controllers;

use Exception;
use PDO;

class FeedbackController
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
            exit('403 Forbidden: The Feedback Submissions module is currently disabled.');
        }

        /** @var array{id: int, username: string} $currentUser */
        $currentUser = \require_admin_page($this->pdo, 'manage_feedback', 'Manage feedback tickets and dialogue');

        $timePrefs = \get_user_time_prefs($currentUser);
        $userTimezone = $timePrefs[0];
        $fullFormatStr = $timePrefs[1];

        $systemName = \get_system_name($this->pdo);

        $serverMethod = isset($_SERVER['REQUEST_METHOD']) && is_string($_SERVER['REQUEST_METHOD'])
            ? $_SERVER['REQUEST_METHOD'] : 'GET';
        if ($serverMethod === 'POST') {
            $post = $_POST;
            $action = isset($post['action']) && is_string($post['action']) ? $post['action'] : '';

            if ($action === 'delete_ticket') {
                \verify_csrf_token();
                $ticketId = isset($post['ticket_id']) ? (int) $post['ticket_id'] : 0;

                if ($ticketId > 0) {
                    $this->pdo->prepare('DELETE FROM feedback_ticket_values WHERE ticket_id = ?')->execute([$ticketId]);
                    $this->pdo->prepare('DELETE FROM feedback_ticket_replies WHERE ticket_id = ?')->execute([$ticketId]);
                    $this->pdo->prepare('DELETE FROM feedback_tickets WHERE id = ?')->execute([$ticketId]);

                    $_SESSION['message'] = "Feedback ticket #{$ticketId} has been successfully deleted.";
                    header('Location: ' . BASE_PATH . '/admin/tickets');
                    exit;
                }
            }
        }

        $stmt = $this->pdo->query(
            'SELECT t.* FROM feedback_tickets t ORDER BY t.created_at DESC'
        );
        /** @var array<int, array<string, mixed>> $tickets */
        $tickets = $stmt !== false ? $stmt->fetchAll(PDO::FETCH_ASSOC) : [];

        foreach ($tickets as &$t) {
            $t['submitter_display'] = $this->resolveTicketSubmitterDisplay($t, $currentUser);
        }
        unset($t);

        $message = $_SESSION['message'] ?? '';
        $error = $_SESSION['error'] ?? '';
        unset($_SESSION['message'], $_SESSION['error']);

        require_once __DIR__ . '/../Views/admin/feedback_dashboard.php';
    }

    /**
     * Linked account → central display helper; else form name on the ticket.
     *
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
