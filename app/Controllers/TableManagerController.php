<?php
/**
 * MIGRATED FILE MAPPING
 * ---------------------
 * Original Old File: admin/manage_tables.php/admin/actions/save_manage_tables.php
 * Migrated Date: 2026-08-05 03:17:32
 */
declare(strict_types=1);

namespace App\Controllers;

use App\Services\TableService;
use PDO;
use Exception;

class TableManagerController
{
    private PDO $pdo;

    public function __construct(PDO $pdo)
    {
        $this->pdo = $pdo;
    }

    public function index(): void
    {
        /** @var array{id: int, username: string} $currentUser */
        $currentUser = require_admin_page($this->pdo, 'manage_tables', 'Manage dynamic database tables and column schema definitions');
        
        /** @var array{0: string, 1: string} $timePrefs */
        $timePrefs = get_user_time_prefs($currentUser);
        $userTimezone = $timePrefs[0];
        $fullFormatStr = $timePrefs[1];

        // Fetch all existing dynamic tables
        $tablesStmt = $this->pdo->query("SELECT * FROM dynamic_tables ORDER BY id ASC");
        /** @var array<int, array<string, mixed>> $tables */
        $tables = $tablesStmt !== false ? $tablesStmt->fetchAll(PDO::FETCH_ASSOC) : [];

        $get = $_GET;
        $activeTableId = isset($get['table_id']) ? (int)$get['table_id'] : ($tables !== [] ? (int)$tables[0]['id'] : 1);

        // Find active table details
        /** @var array<string, mixed>|null $activeTableInfo */
        $activeTableInfo = null;
        foreach ($tables as $t) {
            if (isset($t['id']) && (int)$t['id'] === $activeTableId) {
                $activeTableInfo = $t;
                break;
            }
        }

        // Check if we are editing an existing table definition
        /** @var array<string, mixed>|null $editTable */
        $editTable = null;
        if (isset($get['edit_table'])) {
            $editId = (int)$get['edit_table'];
            $stmt = $this->pdo->prepare("SELECT * FROM dynamic_tables WHERE id = ?");
            $stmt->execute([$editId]);
            $fetchedTable = $stmt->fetch(PDO::FETCH_ASSOC);
            if ($fetchedTable !== false) {
                $editTable = $fetchedTable;
            }
        }

        // Check if we are editing an existing column schema within the active table
        /** @var array<string, mixed>|null $editCol */
        $editCol = null;
        if (isset($get['edit_column'])) {
            $editColId = (int)$get['edit_column'];
            $cStmt = $this->pdo->prepare("SELECT * FROM table_columns WHERE id = ?");
            $cStmt->execute([$editColId]);
            $fetchedCol = $cStmt->fetch(PDO::FETCH_ASSOC);
            if ($fetchedCol !== false) {
                $editCol = $fetchedCol;
                if (isset($editCol['table_id'])) {
                    $activeTableId = (int)$editCol['table_id'];
                }
            }
        }

        // Fetch columns for the currently active table
        $columnsStmt = $this->pdo->prepare("SELECT tc.*, u.username FROM table_columns tc JOIN users u ON tc.created_by = u.id WHERE tc.table_id = ? ORDER BY tc.sort_order ASC, tc.column_name ASC");
        $columnsStmt->execute([$activeTableId]);
        /** @var array<int, array<string, mixed>> $columns */
        $columns = $columnsStmt->fetchAll(PDO::FETCH_ASSOC);

        $message = $_SESSION['message'] ?? '';
        $error = $_SESSION['error'] ?? '';
        unset($_SESSION['message'], $_SESSION['error']);

        require_once __DIR__ . '/../Views/admin/manage_tables.php';
    }

    public function store(): void
    {
        $serverMethod = isset($_SERVER['REQUEST_METHOD']) && is_string($_SERVER['REQUEST_METHOD']) ? $_SERVER['REQUEST_METHOD'] : 'GET';
        if ($serverMethod !== 'POST') {
            http_response_code(405);
            exit('Method Not Allowed');
        }

        verify_csrf_token();
        /** @var array{id: int, username: string} $currentUser */
        $currentUser = require_permission($this->pdo, 'manage_tables', 'Manage dynamic database tables and column schema definitions');

        $basePath = defined('BASE_PATH') ? rtrim(BASE_PATH, '/') : '';
        $post = $_POST;
        $action = isset($post['action']) && is_string($post['action']) ? $post['action'] : 'create';
        $tableId = isset($post['table_id']) ? (int)$post['table_id'] : 1;

        $tableService = new TableService($this->pdo);

        try {
            switch ($action) {
                case 'create_table':
                    $newTableId = $tableService->createTable($post, $currentUser);
                    $_SESSION['message'] = "Custom table successfully created with table-scoped view and moderation permissions!";
                    header('Location: ' . $basePath . '/admin/tables?table_id=' . $newTableId);
                    exit;

                case 'update_table':
                    $_SESSION['message'] = $tableService->updateTable($tableId, $post, $currentUser);
                    header('Location: ' . $basePath . '/admin/tables?table_id=' . $tableId);
                    exit;

                case 'delete_table':
                    $_SESSION['message'] = $tableService->deleteTable($tableId, $currentUser);
                    header('Location: ' . $basePath . '/admin/tables');
                    exit;

                case 'create':
                case 'update':
                    $_SESSION['message'] = $tableService->saveColumn($action, $tableId, $post, $currentUser);
                    break;

                case 'update_order_batch':
                    $sortOrders = isset($post['sort_orders']) && is_array($post['sort_orders']) ? $post['sort_orders'] : [];
                    $tableService->updateSortOrders($sortOrders);
                    $_SESSION['message'] = "All column sort orders successfully updated!";
                    break;

                case 'delete':
                    $columnId = isset($post['column_id']) ? (int)$post['column_id'] : 0;
                    $_SESSION['message'] = $tableService->deleteColumn($columnId, $currentUser);
                    break;

                default:
                    throw new Exception("Invalid action requested.");
            }
        } catch (Exception $e) {
            $_SESSION['error'] = $e->getMessage();
        }

        header('Location: ' . $basePath . '/admin/tables?table_id=' . $tableId);
        exit;
    }
}
