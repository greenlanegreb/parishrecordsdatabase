<?php
/**
 * MIGRATED FILE MAPPING
 * ---------------------
 * Original Old File: admin/manage_tables.php/admin/actions/save_manage_tables.php
 * Migrated Date: 2026-08-05 03:17:32
 */declare(strict_types=1);


namespace App\Controllers;

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
        if (session_status() === PHP_SESSION_NONE) {
            session_start();
        }

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
        $activeTableId = isset($get['table_id']) ? (int)$get['table_id'] : (!empty($tables) ? (int)$tables[0]['id'] : 1);

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
        if (session_status() === PHP_SESSION_NONE) {
            session_start();
        }

        $serverMethod = isset($_SERVER['REQUEST_METHOD']) && is_string($_SERVER['REQUEST_METHOD']) ? $_SERVER['REQUEST_METHOD'] : 'GET';
        if ($serverMethod !== 'POST') {
            http_response_code(405);
            exit('Method Not Allowed');
        }

        verify_csrf_token();
        /** @var array{id: int, username: string} $currentUser */
        $currentUser = require_permission($this->pdo, 'manage_tables', 'Manage dynamic database tables and column schema definitions');

        $post = $_POST;
        $action = isset($post['action']) && is_string($post['action']) ? $post['action'] : 'create';
        $tableId = isset($post['table_id']) ? (int)$post['table_id'] : 1;

        // 1. HANDLE TABLE CREATION
        if ($action === 'create_table') {
            $tableName = isset($post['table_name']) && is_string($post['table_name']) ? trim($post['table_name']) : '';
            $description = isset($post['description']) && is_string($post['description']) ? trim($post['description']) : '';

            if ($tableName === '') {
                $_SESSION['error'] = "Table name cannot be empty.";
            } else {
                $stmt = $this->pdo->prepare("INSERT INTO dynamic_tables (table_name, description, created_by) VALUES (?, ?, ?)");
                if ($stmt->execute([$tableName, $description, $currentUser['id']])) {
                    $newTableId = (int)$this->pdo->lastInsertId();

                    $viewPermKey = 'view_table_' . $newTableId;
                    $viewPermDesc = 'Allows viewing and searching records in table: ' . $tableName;
                    $modPermKey = 'moderate_table_' . $newTableId;
                    $modPermDesc = 'Allows reviewing and moderating suggestions in table: ' . $tableName;

                    try {
                        $pStmt = $this->pdo->prepare("INSERT INTO permissions (permission_key, description) VALUES (?, ?) ON DUPLICATE KEY UPDATE description = COALESCE(VALUES(description), description)");
                        $pStmt->execute([$viewPermKey, $viewPermDesc]);
                        $pStmt->execute([$modPermKey, $modPermDesc]);

                        $getP = $this->pdo->prepare("SELECT id, permission_key FROM permissions WHERE permission_key IN (?, ?)");
                        $getP->execute([$viewPermKey, $modPermKey]);
                        $perms = $getP->fetchAll(PDO::FETCH_ASSOC);

                        foreach ($perms as $p) {
                            $pId = isset($p['id']) ? (int)$p['id'] : 0;
                            $pKey = isset($p['permission_key']) && is_string($p['permission_key']) ? $p['permission_key'] : '';

                            if (str_starts_with($pKey, 'view_table_')) {
                                $rStmt = $this->pdo->prepare("
                                    SELECT DISTINCT r.id 
                                    FROM roles r
                                    LEFT JOIN role_permissions rp ON r.id = rp.role_id
                                    LEFT JOIN permissions perm ON rp.permission_id = perm.id
                                    WHERE LOWER(r.role_name) IN ('admin', 'moderator')
                                        OR perm.permission_key = 'view_as_guest'
                                ");
                            } else {
                                $rStmt = $this->pdo->prepare("
                                    SELECT DISTINCT r.id 
                                    FROM roles r
                                    WHERE LOWER(r.role_name) IN ('admin', 'moderator')
                                ");
                            }
                            $rStmt->execute();
                            /** @var array<int, int> $roles */
                            $roles = $rStmt->fetchAll(PDO::FETCH_COLUMN);

                            $mapStmt = $this->pdo->prepare("INSERT IGNORE INTO role_permissions (role_id, permission_id) VALUES (?, ?)");
                            foreach ($roles as $rId) {
                                $mapStmt->execute([$rId, $pId]);
                            }
                        }
                    } catch (Exception $e) {
                        // Non-blocking fallback
                    }

                    $_SESSION['message'] = "Custom table '{$tableName}' successfully created with table-scoped view and moderation permissions!";
                    $audit = $this->pdo->prepare("INSERT INTO audit_logs (user_id, action, details, ip_address) VALUES (?, 'CREATE_TABLE', ?, ?)");
                    $audit->execute([$currentUser['id'], "Created table: {$tableName}", isset($_SERVER['REMOTE_ADDR']) && is_string($_SERVER['REMOTE_ADDR']) ? $_SERVER['REMOTE_ADDR'] : '127.0.0.1']);

                    header('Location: /admin/tables?table_id=' . $newTableId);
                    exit;
                } else {
                    $_SESSION['error'] = "Failed to create table. Table name might already exist.";
                }
            }
        }
        // 1b. HANDLE TABLE UPDATE
        elseif ($action === 'update_table') {
            $tableName = isset($post['table_name']) && is_string($post['table_name']) ? trim($post['table_name']) : '';
            $description = isset($post['description']) && is_string($post['description']) ? trim($post['description']) : '';

            if ($tableId <= 0) {
                $_SESSION['error'] = "Invalid table selected for editing.";
            } elseif ($tableName === '') {
                $_SESSION['error'] = "Table name cannot be empty.";
            } else {
                $stmt = $this->pdo->prepare("UPDATE dynamic_tables SET table_name = ?, description = ? WHERE id = ?");
                if ($stmt->execute([$tableName, $description, $tableId])) {
                    $_SESSION['message'] = "Table metadata successfully updated!";
                    $audit = $this->pdo->prepare("INSERT INTO audit_logs (user_id, action, details, ip_address) VALUES (?, 'UPDATE_TABLE', ?, ?)");
                    $audit->execute([$currentUser['id'], "Updated table ID {$tableId} metadata to: {$tableName}", isset($_SERVER['REMOTE_ADDR']) && is_string($_SERVER['REMOTE_ADDR']) ? $_SERVER['REMOTE_ADDR'] : '127.0.0.1']);
                } else {
                    $_SESSION['error'] = "Failed to update table metadata.";
                }
            }
            header('Location: /admin/tables?table_id=' . $tableId);
            exit;
        }
        // 2. HANDLE TABLE DELETION
        elseif ($action === 'delete_table') {
            if ($tableId <= 1) {
                $_SESSION['error'] = "The default root table cannot be deleted.";
            } else {
                $tStmt = $this->pdo->prepare("SELECT table_name FROM dynamic_tables WHERE id = ?");
                $tStmt->execute([$tableId]);
                $tableInfo = $tStmt->fetch(PDO::FETCH_ASSOC);
                if ($tableInfo !== false) {
                    $tableName = isset($tableInfo['table_name']) && is_string($tableInfo['table_name']) ? $tableInfo['table_name'] : '';
                    $this->pdo->beginTransaction();
                    try {
                        $delVals = $this->pdo->prepare("DELETE rv FROM record_values rv JOIN table_columns tc ON rv.column_id = tc.id WHERE tc.table_id = ?");
                        $delVals->execute([$tableId]);
                        $delRecs = $this->pdo->prepare("DELETE FROM records WHERE table_id = ?");
                        $delRecs->execute([$tableId]);
                        $delCols = $this->pdo->prepare("DELETE FROM table_columns WHERE table_id = ?");
                        $delCols->execute([$tableId]);

                        $viewPermKey = 'view_table_' . $tableId;
                        $modPermKey = 'moderate_table_' . $tableId;

                        $pSel = $this->pdo->prepare("SELECT id FROM permissions WHERE permission_key IN (?, ?)");
                        $pSel->execute([$viewPermKey, $modPermKey]);
                        /** @var array<int, int> $pIds */
                        $pIds = $pSel->fetchAll(PDO::FETCH_COLUMN);

                        if (!empty($pIds)) {
                            $placeholders = implode(',', array_fill(0, count($pIds), '?'));
                            $delRp = $this->pdo->prepare("DELETE FROM role_permissions WHERE permission_id IN ({$placeholders})");
                            $delRp->execute($pIds);
                            $delP = $this->pdo->prepare("DELETE FROM permissions WHERE id IN ({$placeholders})");
                            $delP->execute($pIds);
                        }

                        $delTbl = $this->pdo->prepare("DELETE FROM dynamic_tables WHERE id = ?");
                        $delTbl->execute([$tableId]);

                        $this->pdo->commit();
                        $_SESSION['message'] = "Table '{$tableName}' and all its associated data and permissions were successfully deleted.";
                        $audit = $this->pdo->prepare("INSERT INTO audit_logs (user_id, action, details, ip_address) VALUES (?, 'DELETE_TABLE', ?, ?)");
                        $audit->execute([$currentUser['id'], "Deleted table ID {$tableId}: {$tableName}", isset($_SERVER['REMOTE_ADDR']) && is_string($_SERVER['REMOTE_ADDR']) ? $_SERVER['REMOTE_ADDR'] : '127.0.0.1']);
                    } catch (Exception $e) {
                        $this->pdo->rollBack();
                        $_SESSION['error'] = "Failed to delete table safely.";
                    }
                }
            }
            header('Location: /admin/tables');
            exit;
        }
        // 3. HANDLE COLUMN CREATION / UPDATING
        elseif ($action === 'create' || $action === 'update') {
            $columnName = isset($post['column_name']) && is_string($post['column_name']) ? trim($post['column_name']) : '';
            $dataType = isset($post['data_type']) && is_string($post['data_type']) ? trim($post['data_type']) : 'VARCHAR';
            $maxLength = !empty($post['max_length']) ? (int)$post['max_length'] : null;
            $isRequired = isset($post['is_required']) ? 1 : 0;
            $excludeFromPublicSearch = isset($post['exclude_from_public_search']) ? 1 : 0;

            $booleanDisplayFormat = ($dataType === 'BOOLEAN') ? (isset($post['boolean_display_format']) && is_string($post['boolean_display_format']) ? trim($post['boolean_display_format']) : 'yes_no') : null;
            $dateSearchBehavior = ($dataType === 'DATE') ? (isset($post['date_search_behavior']) && is_string($post['date_search_behavior']) ? trim($post['date_search_behavior']) : 'manual_only') : null;

            if ($columnName === '') {
                $_SESSION['error'] = "Column name cannot be empty.";
            } else {
                if ($action === 'create') {
                    $orderStmt = $this->pdo->prepare("SELECT COALESCE(MAX(sort_order), 0) + 1 FROM table_columns WHERE table_id = ?");
                    $orderStmt->execute([$tableId]);
                    $nextSortOrder = (int)$orderStmt->fetchColumn();

                    $stmt = $this->pdo->prepare("INSERT INTO table_columns (table_id, column_name, data_type, max_length, boolean_display_format, date_search_behavior, sort_order, is_required, exclude_from_public_search, created_by) VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?)");
                    if ($stmt->execute([$tableId, $columnName, $dataType, $maxLength, $booleanDisplayFormat, $dateSearchBehavior, $nextSortOrder, $isRequired, $excludeFromPublicSearch, $currentUser['id']])) {
                        $_SESSION['message'] = "Dynamic column '{$columnName}' successfully created!";
                        $audit = $this->pdo->prepare("INSERT INTO audit_logs (user_id, action, details, ip_address) VALUES (?, 'CREATE_COLUMN', ?, ?)");
                        $audit->execute([$currentUser['id'], "Created column '{$columnName}' in table ID {$tableId}", isset($_SERVER['REMOTE_ADDR']) && is_string($_SERVER['REMOTE_ADDR']) ? $_SERVER['REMOTE_ADDR'] : '127.0.0.1']);
                    } else {
                        $_SESSION['error'] = "Failed to create column.";
                    }
                } elseif ($action === 'update') {
                    $columnId = isset($post['column_id']) ? (int)$post['column_id'] : 0;
                    if ($columnId > 0) {
                        $stmt = $this->pdo->prepare("UPDATE table_columns SET column_name = ?, data_type = ?, max_length = ?, boolean_display_format = ?, date_search_behavior = ?, is_required = ?, exclude_from_public_search = ? WHERE id = ?");
                        if ($stmt->execute([$columnName, $dataType, $maxLength, $booleanDisplayFormat, $dateSearchBehavior, $isRequired, $excludeFromPublicSearch, $columnId])) {
                            $_SESSION['message'] = "Dynamic column '{$columnName}' successfully updated!";
                            $audit = $this->pdo->prepare("INSERT INTO audit_logs (user_id, action, details, ip_address) VALUES (?, 'UPDATE_COLUMN', ?, ?)");
                            $audit->execute([$currentUser['id'], "Updated column ID {$columnId}: {$columnName}", isset($_SERVER['REMOTE_ADDR']) && is_string($_SERVER['REMOTE_ADDR']) ? $_SERVER['REMOTE_ADDR'] : '127.0.0.1']);
                        } else {
                            $_SESSION['error'] = "Failed to update column.";
                        }
                    }
                }
            }
        }
        // 4. HANDLE BATCH SORT ORDER UPDATES
        elseif ($action === 'update_order_batch') {
            $sortOrders = isset($post['sort_orders']) && is_array($post['sort_orders']) ? $post['sort_orders'] : [];
            if (!empty($sortOrders)) {
                $stmt = $this->pdo->prepare("UPDATE table_columns SET sort_order = ? WHERE id = ?");
                $this->pdo->beginTransaction();
                try {
                    foreach ($sortOrders as $colId => $orderVal) {
                        $stmt->execute([(int)$orderVal, (int)$colId]);
                    }
                    $this->pdo->commit();
                    $_SESSION['message'] = "All column sort orders successfully updated!";
                } catch (Exception $e) {
                    $this->pdo->rollBack();
                    $_SESSION['error'] = "Failed to update column sort orders.";
                }
            } else {
                $_SESSION['error'] = "No sort order data received.";
            }

            header('Location: /admin/tables?table_id=' . $tableId);
            exit;
        }
        // 5. HANDLE COLUMN DELETION
        elseif ($action === 'delete') {
            $columnId = isset($post['column_id']) ? (int)$post['column_id'] : 0;
            if ($columnId > 0) {
                $cStmt = $this->pdo->prepare("SELECT column_name FROM table_columns WHERE id = ?");
                $cStmt->execute([$columnId]);
                $colInfo = $cStmt->fetch(PDO::FETCH_ASSOC);
                if ($colInfo !== false) {
                    $colName = isset($colInfo['column_name']) && is_string($colInfo['column_name']) ? $colInfo['column_name'] : '';
                    $delVals = $this->pdo->prepare("DELETE FROM record_values WHERE column_id = ?");
                    $delVals->execute([$columnId]);
                    $delCol = $this->pdo->prepare("DELETE FROM table_columns WHERE id = ?");
                    $delCol->execute([$columnId]);

                    $_SESSION['message'] = "Column '{$colName}' and its associated data entries were successfully deleted.";
                    $audit = $this->pdo->prepare("INSERT INTO audit_logs (user_id, action, details, ip_address) VALUES (?, 'DELETE_COLUMN', ?, ?)");
                    $audit->execute([$currentUser['id'], "Deleted column ID {$columnId}: {$colName}", isset($_SERVER['REMOTE_ADDR']) && is_string($_SERVER['REMOTE_ADDR']) ? $_SERVER['REMOTE_ADDR'] : '127.0.0.1']);
                }
            }
        }

        header('Location: /admin/tables?table_id=' . $tableId);
        exit;
    }
}
