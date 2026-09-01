<?php
declare(strict_types=1);

namespace App\Controllers;

use PDO;

/**
 * Read-only full record. Same table-view rule as search / data-entry lists.
 */
class RecordViewController
{
    private PDO $pdo;

    public function __construct(PDO $pdo)
    {
        $this->pdo = $pdo;
    }

    public function show(int|string $id = 0): void
    {
        if (is_file(dirname(__DIR__, 2) . '/includes/form_fields.php')) {
            require_once dirname(__DIR__, 2) . '/includes/form_fields.php';
        }

        $basePath = defined('BASE_PATH') ? rtrim((string) BASE_PATH, '/') : '';
        $recordId = (int) $id;
        if ($recordId < 1) {
            http_response_code(404);
            echo 'Record not found.';
            return;
        }

        $stmt = $this->pdo->prepare(
            'SELECT r.id, r.table_id, r.created_by, r.created_at, dt.table_name,
                    u.username, u.first_name, u.surname, u.attribution_display_mode
             FROM records r
             INNER JOIN dynamic_tables dt ON dt.id = r.table_id
             LEFT JOIN users u ON u.id = r.created_by
             WHERE r.id = ?'
        );
        $stmt->execute([$recordId]);
        $record = $stmt->fetch(PDO::FETCH_ASSOC);
        if ($record === false) {
            http_response_code(404);
            echo 'Record not found.';
            return;
        }

        $tableId = (int) ($record['table_id'] ?? 0);
        $currentUser = function_exists('get_current_user_data') ? get_current_user_data($this->pdo) : null;
        if ($tableId > 0 && function_exists('user_can_view_table')
            && !user_can_view_table($this->pdo, $tableId, is_array($currentUser) ? $currentUser : null)) {
            http_response_code(403);
            echo 'You cannot view this record.';
            return;
        }

        $colsStmt = $this->pdo->prepare(
            'SELECT * FROM table_columns WHERE table_id = ? ORDER BY sort_order ASC, id ASC'
        );
        $colsStmt->execute([$tableId]);
        $columns = $colsStmt->fetchAll(PDO::FETCH_ASSOC);
        $visHelper = dirname(__DIR__, 2) . '/includes/column_visibility.php';
        if (is_file($visHelper)) {
            require_once $visHelper;
        }
        if (function_exists('column_shown_on_record')) {
            $columns = array_values(array_filter($columns, 'column_shown_on_record'));
        }

        $valStmt = $this->pdo->prepare(
            'SELECT column_id, value_content FROM record_values WHERE record_id = ?'
        );
        $valStmt->execute([$recordId]);
        $values = [];
        while ($row = $valStmt->fetch(PDO::FETCH_ASSOC)) {
            $values[(int) $row['column_id']] = (string) ($row['value_content'] ?? '');
        }

        $creatorId = (int) ($record['created_by'] ?? 0);
        if ($creatorId > 0) {
            $creator = [
                'id' => $creatorId,
                'username' => isset($record['username']) && is_string($record['username']) ? $record['username'] : '',
                'first_name' => isset($record['first_name']) && is_string($record['first_name']) ? $record['first_name'] : '',
                'surname' => isset($record['surname']) && is_string($record['surname']) ? $record['surname'] : '',
                'attribution_display_mode' => isset($record['attribution_display_mode']) && is_string($record['attribution_display_mode'])
                    ? $record['attribution_display_mode']
                    : 'initials_random',
            ];
            $createdByLabel = function_exists('format_user_display_name')
                ? format_user_display_name($this->pdo, $creator, is_array($currentUser) ? $currentUser : null)
                : 'Contributor';
        } else {
            $createdByLabel = 'System';
        }

        $userTimezone = 'UTC';
        $fullFormatStr = 'd/m/Y H:i';
        $userDateFormat = null;
        $userTimePref = null;
        if (is_array($currentUser)) {
            if (!empty($currentUser['timezone']) && is_string($currentUser['timezone'])) {
                $userTimezone = $currentUser['timezone'];
            }
            if (!empty($currentUser['date_format']) && is_string($currentUser['date_format'])) {
                $userDateFormat = $currentUser['date_format'];
            }
            if (!empty($currentUser['time_format']) && is_string($currentUser['time_format'])) {
                $userTimePref = $currentUser['time_format'];
            }
        }
        $createdAtRaw = isset($record['created_at']) && is_string($record['created_at']) ? $record['created_at'] : '';
        $createdAtLabel = function_exists('format_user_time')
            ? format_user_time($createdAtRaw, $userTimezone, $fullFormatStr)
            : $createdAtRaw;

        $canSuggestEdit = function_exists('can_suggest_edit') && can_suggest_edit($this->pdo);
        $canEditRecords = function_exists('has_permission') && has_permission($this->pdo, 'edit_records');
        $canDeleteRecords = function_exists('has_permission') && has_permission($this->pdo, 'delete_records');
        $canExport = function_exists('has_permission') && has_permission($this->pdo, 'export_data');

        $systemName = function_exists('get_system_name') ? get_system_name($this->pdo) : 'pRD';
        $returnUrl = isset($_GET['return']) && is_string($_GET['return']) ? $_GET['return'] : ($basePath . '/');
        $csrfToken = function_exists('generate_csrf_token') ? generate_csrf_token() : '';

        require dirname(__DIR__) . '/Views/user/record_view.php';
    }
}
