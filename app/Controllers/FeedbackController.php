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
        // 1. Module check using global helper
        if (!\is_module_enabled($this->pdo, 'feedback')) {
            http_response_code(403);
            exit('403 Forbidden: The Feedback Submissions module is currently disabled.');
        }

        // 2. Admin bootstrap & preferences
        /** @var array{id: int, username: string} $currentUser */
        $currentUser = \require_admin_page($this->pdo, 'manage_feedback', 'Manage feedback tickets and dialogue');
        
        $timePrefs = \get_user_time_prefs($currentUser);
        $userTimezone = $timePrefs[0];
        $fullFormatStr = $timePrefs[1];
        
        $systemName = \get_system_name($this->pdo);

        // 3. Handle ticket deletion action if triggered via POST
        $serverMethod = isset($_SERVER['REQUEST_METHOD']) && is_string($_SERVER['REQUEST_METHOD']) ? $_SERVER['REQUEST_METHOD'] : 'GET';
        if ($serverMethod === 'POST') {
            $post = $_POST;
            $action = isset($post['action']) && is_string($post['action']) ? $post['action'] : '';
            
            if ($action === 'delete_ticket') {
                \verify_csrf_token();
                $ticketId = isset($post['ticket_id']) ? (int)$post['ticket_id'] : 0;
                
                if ($ticketId > 0) {
                    $this->pdo->prepare("DELETE FROM feedback_ticket_values WHERE ticket_id = ?")->execute([$ticketId]);
                    $this->pdo->prepare("DELETE FROM feedback_ticket_replies WHERE ticket_id = ?")->execute([$ticketId]);
                    $this->pdo->prepare("DELETE FROM feedback_tickets WHERE id = ?")->execute([$ticketId]);
                    
                    $_SESSION['message'] = "Feedback ticket #{$ticketId} has been successfully deleted.";
                    header('Location: ' . BASE_PATH . '/admin/tickets');
                    exit;
                }
            }
        }

        // 4. Fetch all tickets
        $stmt = $this->pdo->query("SELECT t.*, u.username FROM feedback_tickets t LEFT JOIN users u ON t.user_id = u.id ORDER BY t.created_at DESC");
        /** @var array<int, array<string, mixed>> $tickets */
        $tickets = $stmt !== false ? $stmt->fetchAll(PDO::FETCH_ASSOC) : [];

        $message = $_SESSION['message'] ?? '';
        $error = $_SESSION['error'] ?? '';
        unset($_SESSION['message'], $_SESSION['error']);

        // Render View Template
        require_once __DIR__ . '/../Views/admin/feedback_dashboard.php';
    }
}
