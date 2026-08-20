<?php
declare(strict_types=1);

namespace App\Controllers;

use PDO;

class PrintRecordsController
{
    private PDO $pdo;

    public function __construct(PDO $pdo)
    {
        $this->pdo = $pdo;
    }

    public function index(): void
    {
        $helper = dirname(__DIR__, 2) . '/includes/column_visibility.php';
        if (is_file($helper)) {
            require_once $helper;
        }

        $tableId = isset($_GET['table_id']) ? (int) $_GET['table_id'] : 0;
        if ($tableId < 1) {
            http_response_code(400);
            exit('Please choose a table.');
        }

        $currentUser = function_exists('get_current_user_data') ? get_current_user_data($this->pdo) : null;
        $canView = ($currentUser !== null && function_exists('has_permission') && has_permission($this->pdo, 'view_table_' . $tableId))
            || (function_exists('guest_has_permission') && guest_has_permission($this->pdo, 'view_as_guest'));
        $canExport = ($currentUser !== null && function_exists('has_permission') && has_permission($this->pdo, 'export_data'))
            || (function_exists('guest_has_permission') && guest_has_permission($this->pdo, 'export_data'));
        if (!$canView || !$canExport) {
            require_once dirname(__DIR__, 2) . '/public/403.php';
            exit;
        }

        $tStmt = $this->pdo->prepare('SELECT table_name FROM dynamic_tables WHERE id = ?');
        $tStmt->execute([$tableId]);
        $tableName = (string) ($tStmt->fetchColumn() ?: '');

        $colsStmt = $this->pdo->prepare(
            'SELECT * FROM table_columns WHERE table_id = ? ORDER BY sort_order ASC, column_name ASC'
        );
        $colsStmt->execute([$tableId]);
        $allColumns = $colsStmt->fetchAll(PDO::FETCH_ASSOC);
        $columns = function_exists('resolve_visible_columns')
            ? resolve_visible_columns($allColumns, $tableId)
            : $allColumns;

        $recordsStmt = $this->pdo->prepare(
            'SELECT r.id, r.created_at, r.created_by, u.username
             FROM records r
             LEFT JOIN users u ON r.created_by = u.id
             WHERE r.table_id = ?
             ORDER BY r.id DESC'
        );
        $recordsStmt->execute([$tableId]);
        $records = $recordsStmt->fetchAll(PDO::FETCH_ASSOC);

        $valuesStmt = $this->pdo->prepare(
            'SELECT rv.record_id, rv.column_id, rv.value_content
             FROM record_values rv
             JOIN records r ON rv.record_id = r.id
             WHERE r.table_id = ?'
        );
        $valuesStmt->execute([$tableId]);
        $recordValues = [];
        while ($row = $valuesStmt->fetch(PDO::FETCH_ASSOC)) {
            $recordValues[(int) $row['record_id']][(int) $row['column_id']] = (string) ($row['value_content'] ?? '');
        }

        $searchFilters = isset($_GET['filters']) && is_array($_GET['filters']) ? $_GET['filters'] : [];
        $dateFilters = isset($_GET['date_filters']) && is_array($_GET['date_filters']) ? $_GET['date_filters'] : [];

        $userDateFormat = 'd/m/Y';
        if (is_array($currentUser) && isset($currentUser['date_format']) && is_string($currentUser['date_format'])) {
            $userDateFormat = $currentUser['date_format'];
        }

        $systemName = function_exists('get_system_name') ? get_system_name($this->pdo) : 'pRD';
        $printedAt = date('Y-m-d H:i');
        $basePath = defined('BASE_PATH') ? rtrim((string) BASE_PATH, '/') : '';
        require_once __DIR__ . '/../Views/print/records.php';
    }
}
