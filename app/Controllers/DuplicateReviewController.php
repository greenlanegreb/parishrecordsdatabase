<?php
declare(strict_types=1);

namespace App\Controllers;

use App\Services\DuplicateReviewService;
use Exception;
use PDO;

class DuplicateReviewController
{
    private PDO $pdo;
    private DuplicateReviewService $reviews;

    public function __construct(PDO $pdo)
    {
        $this->pdo = $pdo;
        $this->reviews = new DuplicateReviewService($pdo);
    }

    public function index(): void
    {
        $currentUser = $this->gate();
        $basePath = defined('BASE_PATH') ? rtrim((string) BASE_PATH, '/') : '';
        header('Location: ' . $basePath . '/admin/moderation?tab=similar');
        exit;
    }

    public function scan(): void
    {
        $currentUser = $this->gate();
        if (($_SERVER['REQUEST_METHOD'] ?? '') !== 'POST') {
            http_response_code(405);
            exit('Method Not Allowed');
        }
        verify_csrf_token();
        $tableId = isset($_POST['table_id']) ? (int) $_POST['table_id'] : 0;
        $picky = function_exists('get_setting') ? get_setting($this->pdo, 'duplicate_picky', 'similar') : 'similar';
        try {
            if ($tableId < 1) {
                throw new Exception('Please choose a table to scan.');
            }
            $result = $this->reviews->scanTable($tableId, $picky === 'exact' ? 'exact' : 'similar');
            $_SESSION['message'] = sprintf(
                __('dup_queue.scan_done') !== 'dup_queue.scan_done'
                    ? __('dup_queue.scan_done')
                    : 'Scan finished. %s similar pairs found, %s new items added to the list.',
                (string) $result['found'],
                (string) $result['queued']
            );
        } catch (Exception $e) {
            $_SESSION['error'] = $e->getMessage();
        }
        $this->goBack();
    }

    public function dismiss(): void
    {
        $currentUser = $this->gate();
        if (($_SERVER['REQUEST_METHOD'] ?? '') !== 'POST') {
            http_response_code(405);
            exit('Method Not Allowed');
        }
        verify_csrf_token();
        $id = isset($_POST['review_id']) ? (int) $_POST['review_id'] : 0;
        $this->reviews->dismiss($id, (int) $currentUser['id']);
        $_SESSION['message'] = __('dup_queue.dismissed') !== 'dup_queue.dismissed'
            ? __('dup_queue.dismissed') : 'That pair was marked as not a duplicate.';
        $this->goBack();
    }

    public function mergeForm(): void
    {
        $currentUser = $this->gate();
        $id = isset($_GET['id']) ? (int) $_GET['id'] : 0;
        $review = $this->reviews->findReview($id);
        if ($review === null || ($review['status'] ?? '') !== 'pending') {
            $_SESSION['error'] = __('dup_queue.gone') !== 'dup_queue.gone' ? __('dup_queue.gone') : 'That review is no longer waiting.';
            $this->goBack();
        }
        $fields = $this->reviews->compareValues(
            (int) $review['table_id'],
            (int) $review['record_a_id'],
            (int) $review['record_b_id']
        );
        $basePath = defined('BASE_PATH') ? rtrim((string) BASE_PATH, '/') : '';
        require_once __DIR__ . '/../Views/admin/duplicate_merge.php';
    }

    public function mergeSave(): void
    {
        $currentUser = $this->gate();
        if (($_SERVER['REQUEST_METHOD'] ?? '') !== 'POST') {
            http_response_code(405);
            exit('Method Not Allowed');
        }
        verify_csrf_token();
        $id = isset($_POST['review_id']) ? (int) $_POST['review_id'] : 0;
        $keep = isset($_POST['keep_record_id']) ? (int) $_POST['keep_record_id'] : 0;
        $keepByColumn = [];
        if (isset($_POST['keep_col']) && is_array($_POST['keep_col'])) {
            foreach ($_POST['keep_col'] as $cid => $side) {
                $keepByColumn[(int) $cid] = $side === 'b' ? 'b' : 'a';
            }
        }
        $ip = isset($_SERVER['REMOTE_ADDR']) && is_string($_SERVER['REMOTE_ADDR']) ? $_SERVER['REMOTE_ADDR'] : '127.0.0.1';
        try {
            $this->reviews->merge($id, $keep, $keepByColumn, $currentUser, $ip);
            $_SESSION['message'] = __('dup_queue.merged') !== 'dup_queue.merged'
                ? __('dup_queue.merged') : 'Those two records are now one. The change is on the remaining record’s history.';
        } catch (Exception $e) {
            $_SESSION['error'] = $e->getMessage();
        }
        $this->goBack();
    }

    /**
     * Gate for all duplicate-review / merge actions.
     * edit_records alone is enough (works with moderation module on or off).
     * Falls back to moderate_suggestions for users who only have the original permission.
     *
     * @return array{id: int|string, username: string}
     */
    private function gate(): array
    {
        if (function_exists('has_permission') && has_permission($this->pdo, 'edit_records')) {
            if (function_exists('require_user_permission')) {
                return require_user_permission(
                    $this->pdo,
                    'edit_records',
                    'Review similar records and merge duplicates'
                );
            }
            return require_permission(
                $this->pdo,
                'edit_records',
                'Review similar records and merge duplicates'
            );
        }

        // Fall back to original moderate_suggestions path
        if (function_exists('require_user_permission')) {
            return require_user_permission(
                $this->pdo,
                'moderate_suggestions',
                'Review similar records and merge duplicates'
            );
        }
        return require_admin_page(
            $this->pdo,
            'moderate_suggestions',
            'Review similar records and merge duplicates'
        );
    }

    /**
     * @return list<array{id: int, table_name: string}>
     */
    private function tables(): array
    {
        $stmt = $this->pdo->query('SELECT id, table_name FROM dynamic_tables ORDER BY table_name ASC');
        return $stmt !== false ? $stmt->fetchAll(PDO::FETCH_ASSOC) : [];
    }

    private function goBack(): void
    {
        $basePath = defined('BASE_PATH') ? rtrim((string) BASE_PATH, '/') : '';
        header('Location: ' . $basePath . '/admin/moderation?tab=similar');
        exit;
    }
}
