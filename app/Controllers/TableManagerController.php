<?php
declare(strict_types=1);

namespace App\Controllers;

use App\Services\TableService;
use Exception;
use PDO;

class TableManagerController
{
    private PDO $pdo;

    public function __construct(PDO $pdo)
    {
        $this->pdo = $pdo;
    }

    public function index(): void
    {
        /** @var array{id: int, username: string, timezone?: string} $currentUser */
        $currentUser = require_user_admin(
            $this->pdo,
            'manage_tables',
            'Manage dynamic database tables and column schema definitions'
        );

        [$userTimezone, $fullFormatStr] = get_user_time_prefs($currentUser);

        $tablesStmt = $this->pdo->query('SELECT * FROM dynamic_tables ORDER BY id ASC');
        /** @var array<int, array<string, mixed>> $tables */
        $tables = $tablesStmt !== false ? $tablesStmt->fetchAll(PDO::FETCH_ASSOC) : [];

        $get = $_GET;
        $activeTableId = isset($get['table_id'])
            ? (int) $get['table_id']
            : ($tables !== [] ? (int) $tables[0]['id'] : 1);

        $activeTableInfo = null;
        foreach ($tables as $t) {
            if (isset($t['id']) && (int) $t['id'] === $activeTableId) {
                $activeTableInfo = $t;
                break;
            }
        }

        $editTable = null;
        if (isset($get['edit_table'])) {
            $stmt = $this->pdo->prepare('SELECT * FROM dynamic_tables WHERE id = ?');
            $stmt->execute([(int) $get['edit_table']]);
            $fetched = $stmt->fetch(PDO::FETCH_ASSOC);
            if ($fetched !== false) {
                $editTable = $fetched;
            }
        }

        $editCol = null;
        if (isset($get['edit_column'])) {
            $cStmt = $this->pdo->prepare('SELECT * FROM table_columns WHERE id = ?');
            $cStmt->execute([(int) $get['edit_column']]);
            $fetchedCol = $cStmt->fetch(PDO::FETCH_ASSOC);
            if ($fetchedCol !== false) {
                $editCol = $fetchedCol;
                if (isset($editCol['table_id'])) {
                    $activeTableId = (int) $editCol['table_id'];
                }
            }
        }

        $colsStmt = $this->pdo->prepare(
            'SELECT * FROM table_columns WHERE table_id = ? ORDER BY sort_order ASC, id ASC'
        );
        $colsStmt->execute([$activeTableId]);
        /** @var array<int, array<string, mixed>> $columns */
        $columns = $colsStmt->fetchAll(PDO::FETCH_ASSOC);

        foreach ($columns as &$col) {
            $uid = isset($col['created_by']) ? (int) $col['created_by'] : 0;
            if ($uid > 0 && function_exists('format_user_display_name_by_id')) {
                $col['created_by_display'] = format_user_display_name_by_id($this->pdo, $uid, $currentUser);
            } else {
                $col['created_by_display'] = 'System';
            }
        }
        unset($col);

        $message = $GLOBALS['message'] ?? '';
        $error = $GLOBALS['error'] ?? '';
        $basePath = defined('BASE_PATH') && is_string(BASE_PATH) ? rtrim(BASE_PATH, '/') : '';

        require_once __DIR__ . '/../Views/admin/manage_tables.php';
    }

    public function store(): void
    {
        if (($_SERVER['REQUEST_METHOD'] ?? 'GET') !== 'POST') {
            http_response_code(405);
            exit('Method Not Allowed');
        }

        verify_csrf_token();

        /** @var array{id: int, username: string} $currentUser */
        $currentUser = require_user_permission(
            $this->pdo,
            'manage_tables',
            'Manage dynamic database tables and column schema definitions'
        );

        $post = $_POST;
        $action = isset($post['action']) && is_string($post['action']) ? $post['action'] : 'create';
        $tableId = isset($post['table_id']) ? (int) $post['table_id'] : 1;

        $tableService = new TableService($this->pdo);

        try {
            switch ($action) {
                case 'create_table':
                    $newTableId = $tableService->createTable($post, $currentUser);
                    flash_success('Custom table successfully created with table-scoped view and moderation permissions!');
                    redirect('/admin/tables?table_id=' . $newTableId);

                case 'update_table':
                    flash_success($tableService->updateTable($tableId, $post, $currentUser));
                    redirect('/admin/tables?table_id=' . $tableId);

                case 'delete_table':
                    flash_success($tableService->deleteTable($tableId, $currentUser));
                    redirect('/admin/tables');

                case 'create':
                    flash_success($tableService->saveColumn($action, $tableId, $post, $currentUser));
                    $addAnother = isset($post['after_save']) && $post['after_save'] === 'add_another';
                    redirect('/admin/tables?table_id=' . $tableId . ($addAnother ? '&add_column=1' : ''));

                case 'update':
                    flash_success($tableService->saveColumn($action, $tableId, $post, $currentUser));
                    break;

                case 'update_order_batch':
                    $sortOrders = isset($post['sort_orders']) && is_array($post['sort_orders'])
                        ? $post['sort_orders'] : [];
                    $tableService->updateSortOrders($sortOrders);
                    flash_success('All column sort orders successfully updated!');
                    break;

                case 'delete':
                    $columnId = isset($post['column_id']) ? (int) $post['column_id'] : 0;
                    flash_success($tableService->deleteColumn($columnId, $currentUser));
                    break;

                default:
                    throw new Exception('Invalid action requested.');
            }
        } catch (Exception $e) {
            flash_error($e->getMessage());
        }

        redirect('/admin/tables?table_id=' . $tableId);
    }
}
