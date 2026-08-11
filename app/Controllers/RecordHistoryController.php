<?php
/**
 * MIGRATED FILE MAPPING
 * ---------------------
 * Original Old File: roote/record_history.php
 * Migrated Date: 2026-08-05 06:48:40
 */
declare(strict_types=1);

namespace App\Controllers;

use PDO;

class RecordHistoryController
{
    private PDO $pdo;

    public function __construct(PDO $pdo)
    {
        $this->pdo = $pdo;
    }

    public function index(): void
    {
        $queryGet = $_GET;
        $recordId = isset($queryGet['record_id']) ? (int) $queryGet['record_id'] : 0;
        if ($recordId <= 0) {
            exit(__('record_history.exit_no_record'));
        }

        // Ensure record exists and find its table parent
        $recStmt = $this->pdo->prepare(
            'SELECT r.*, dt.table_name
             FROM records r
             JOIN dynamic_tables dt ON r.table_id = dt.id
             WHERE r.id = ?'
        );
        $recStmt->execute([$recordId]);
        /** @var array{id: int|string, table_id: int|string, table_name: string}|false $record */
        $record = $recStmt->fetch(PDO::FETCH_ASSOC);

        if ($record === false) {
            exit(__('record_history.exit_not_found'));
        }

        $tableId = (int) $record['table_id'];

        // Access control: Ensure user/guest has permission to view this table
        /** @var array{id: int|string, timezone?: string, date_format?: string, time_format?: string}|null $currentUser */
        $currentUser = function_exists('get_current_user_data') ? get_current_user_data($this->pdo) : null;
        $hasPublicPermission = guest_has_permission($this->pdo, 'view_as_guest');
        $permKey = 'view_table_' . $tableId;

        if ($tableId !== 1 && $currentUser === null && !$hasPublicPermission) {
            $basePath = defined('BASE_PATH') && is_string(BASE_PATH) ? rtrim(BASE_PATH, '/') : '';
            header('Location: ' . $basePath . '/login');
            exit;
        }
        if ($tableId !== 1 && $currentUser !== null && !has_permission($this->pdo, $permKey)) {
            require_once __DIR__ . '/../../public/403.php';
            exit;
        }

        // Check if current user has permission to purge individual audit log entries
        $canPurgeAudit = $currentUser !== null && has_permission(
            $this->pdo,
            'purge_audit_entry',
            'Allows purging individual audit log entries from record history'
        );

        // User timezone + datetime format preferences (profile when logged in; site default for guests)
        $siteDateFormat = function_exists('get_setting')
            ? get_setting($this->pdo, 'default_date_format', 'd/m/Y')
            : 'd/m/Y';
        if ($siteDateFormat === '') {
            $siteDateFormat = 'd/m/Y';
        }

        if ($currentUser !== null) {
            $userDateFormat = (
                isset($currentUser['date_format']) && is_string($currentUser['date_format']) && $currentUser['date_format'] !== ''
            ) ? $currentUser['date_format'] : $siteDateFormat;

            $userTimezone = (
                isset($currentUser['timezone']) && is_string($currentUser['timezone']) && $currentUser['timezone'] !== ''
            ) ? $currentUser['timezone'] : 'UTC';

            $fullFormatStr = function_exists('get_user_datetime_format')
                ? get_user_datetime_format($currentUser)
                : ($userDateFormat . ' H:i');
        } else {
            $userDateFormat = $siteDateFormat;
            $userTimezone = 'UTC';
            $fullFormatStr = $userDateFormat . ' H:i';
        }

        // Flash messages
        $message = isset($_SESSION['message']) && is_string($_SESSION['message']) ? $_SESSION['message'] : '';
        $error = isset($_SESSION['error']) && is_string($_SESSION['error']) ? $_SESSION['error'] : '';
        unset($_SESSION['message'], $_SESSION['error']);

        // Fetch audit logs + contributor profile fields for attribution
        $historyStmt = $this->pdo->prepare("
            SELECT
                al.*,
                u.username,
                u.first_name,
                u.surname,
                u.attribution_display_mode,
                es.column_name AS sug_column,
                es.proposed_value AS sug_value,
                es.reasoning AS sug_reasoning,
                es.status AS sug_status
            FROM audit_logs al
            LEFT JOIN users u ON al.user_id = u.id
            LEFT JOIN edit_suggestions es
                ON al.record_id = es.record_id
               AND (
                    al.details LIKE CONCAT('%suggestion ID: ', es.id, '%')
                 OR al.details LIKE CONCAT('%column: ', es.column_name, '%')
               )
            WHERE al.record_id = ?
            ORDER BY al.created_at DESC
        ");
        $historyStmt->execute([$recordId]);
        /** @var array<int, array<string, mixed>> $historyLogs */
        $historyLogs = $historyStmt->fetchAll(PDO::FETCH_ASSOC);

        // Public-safe contributor labels (honours attribution_display_mode)
        foreach ($historyLogs as &$log) {
            $uid = isset($log['user_id']) ? (int) $log['user_id'] : 0;
            if ($uid < 1) {
                $log['contributor_display'] = 'System';
                continue;
            }
            $actor = [
                'id' => $uid,
                'username' => isset($log['username']) && is_string($log['username']) ? $log['username'] : '',
                'first_name' => isset($log['first_name']) && is_string($log['first_name']) ? $log['first_name'] : '',
                'surname' => isset($log['surname']) && is_string($log['surname']) ? $log['surname'] : '',
                'attribution_display_mode' => isset($log['attribution_display_mode']) && is_string($log['attribution_display_mode'])
                    ? $log['attribution_display_mode']
                    : 'initials_random',
            ];
            $log['contributor_display'] = function_exists('format_user_display_name')
                ? format_user_display_name($this->pdo, $actor, $currentUser)
                : 'Contributor';
        }
        unset($log);

        // Fetch current live values for this record to show context
        $valsStmt = $this->pdo->prepare("
            SELECT tc.column_name, tc.data_type, tc.boolean_display_format, rv.value_content
            FROM table_columns tc
            LEFT JOIN record_values rv ON rv.column_id = tc.id AND rv.record_id = ?
            WHERE tc.table_id = ?
            ORDER BY tc.sort_order ASC
        ");
        $valsStmt->execute([$recordId, $tableId]);
        /** @var array<int, array<string, mixed>> $currentValues */
        $currentValues = $valsStmt->fetchAll(PDO::FETCH_ASSOC);

        $serverRef = isset($_SERVER['HTTP_REFERER']) && is_string($_SERVER['HTTP_REFERER'])
            ? $_SERVER['HTTP_REFERER'] : '';

        // Ensure referer is safe and doesn't point back to the history page itself
        if ($serverRef !== '' && strpos($serverRef, 'record_history') === false) {
            $returnUrl = $serverRef;
        } else {
            $returnUrl = 'data-entry?table_id=' . $tableId;
        }

        // Pass variables to View
        require_once __DIR__ . '/../Views/record_history/index.php';
    }
}
