<?php
declare(strict_types=1);

namespace App\Controllers;

use App\Services\RecordMaintenanceService;
use Exception;
use PDO;

/**
 * Delete a single dynamic record. Requires delete_records. Works with moderation on or off.
 */
class UserDeleteRecordActionController
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

        /** @var array{id: int|string, username?: string} $currentUser */
        $currentUser = require_permission(
            $this->pdo,
            'delete_records',
            'Delete individual records from dynamic tables'
        );

        $basePath = defined('BASE_PATH') ? rtrim((string) BASE_PATH, '/') : '';
        $post = $_POST;
        $recordId = isset($post['record_id']) ? (int) $post['record_id'] : 0;
        $returnUrl = isset($post['return_url']) && is_string($post['return_url']) ? trim($post['return_url']) : '';
        if ($returnUrl === '' || preg_match('#^https?://#i', $returnUrl)) {
            $returnUrl = $basePath . '/data-entry';
        } elseif ($basePath !== '' && str_starts_with($returnUrl, '/') && !str_starts_with($returnUrl, $basePath)) {
            $returnUrl = $basePath . $returnUrl;
        }

        $remoteAddr = isset($_SERVER['REMOTE_ADDR']) && is_string($_SERVER['REMOTE_ADDR'])
            ? $_SERVER['REMOTE_ADDR']
            : '127.0.0.1';

        try {
            if ($recordId < 1) {
                throw new Exception('No record specified.');
            }

            // Confirm the user can see this table (integrity: no delete of hidden tables)
            $tStmt = $this->pdo->prepare(
                'SELECT r.id, r.table_id FROM records r WHERE r.id = ?'
            );
            $tStmt->execute([$recordId]);
            $rec = $tStmt->fetch(PDO::FETCH_ASSOC);
            if ($rec === false) {
                throw new Exception('That record was not found.');
            }
            $tableId = (int) ($rec['table_id'] ?? 0);
            if ($tableId > 0 && function_exists('user_can_view_table')
                && !user_can_view_table($this->pdo, $tableId, $currentUser)) {
                throw new Exception('You are not allowed to delete records in this table.');
            }

            $info = (new RecordMaintenanceService($this->pdo))->deleteRecord($recordId);

            // Reverse the +1 awarded on create (same helper as data entry)
            $creatorId = (int) ($info['created_by'] ?? 0);
            if ($creatorId > 0 && function_exists('adjust_user_points')) {
                adjust_user_points($this->pdo, $creatorId, -1);
            }

            if (function_exists('audit')) {
                audit(
                    $this->pdo,
                    (int) $currentUser['id'],
                    'DELETE_RECORD',
                    'Deleted record #' . $info['record_id'] . ' from table_id ' . $info['table_id']
                        . ($creatorId > 0 ? ' (points adjusted for user #' . $creatorId . ')' : ''),
                    $remoteAddr
                );
            }

            $_SESSION['message'] = function_exists('__') && __('data_entry.record_deleted') !== 'data_entry.record_deleted'
                ? __('data_entry.record_deleted')
                : 'The record was deleted.';
        } catch (Exception $e) {
            $_SESSION['error'] = $e->getMessage();
        }

        header('Location: ' . $returnUrl);
        exit;
    }
}
