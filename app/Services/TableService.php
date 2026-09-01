<?php
declare(strict_types=1);

namespace App\Services;

use PDO;
use Exception;

class TableService
{
    private PDO $pdo;

    public function __construct(PDO $pdo)
    {
        $this->pdo = $pdo;
    }

    public function createTable(array $post, array $currentUser): int
    {
        $tableName = isset($post['table_name']) && is_string($post['table_name']) ? trim($post['table_name']) : '';
        $description = isset($post['description']) && is_string($post['description']) ? trim($post['description']) : '';

        if ($tableName === '') {
            throw new Exception($this->msg('manage_tables.err_table_name_empty', 'Please enter a table name.'));
        }
        if ($this->tableNameTaken($tableName)) {
            throw new Exception($this->msg('manage_tables.err_table_name_taken', 'There is already a table called %s. Please choose a different name.', $tableName));
        }

        $stmt = $this->pdo->prepare("INSERT INTO dynamic_tables (table_name, description, created_by) VALUES (?, ?, ?)");
        if (!$stmt->execute([$tableName, $description, $currentUser['id']])) {
            throw new Exception("Failed to create table. Table name might already exist.");
        }

        $newTableId = (int)$this->pdo->lastInsertId();

        // Setup dynamic permissions
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

        $ip = isset($_SERVER['REMOTE_ADDR']) && is_string($_SERVER['REMOTE_ADDR']) ? $_SERVER['REMOTE_ADDR'] : '127.0.0.1';
        $audit = $this->pdo->prepare("INSERT INTO audit_logs (user_id, action, details, ip_address) VALUES (?, 'CREATE_TABLE', ?, ?)");
        $audit->execute([$currentUser['id'], "Created table: {$tableName}", $ip]);

        return $newTableId;
    }

    public function updateTable(int $tableId, array $post, array $currentUser): string
    {
        $tableName = isset($post['table_name']) && is_string($post['table_name']) ? trim($post['table_name']) : '';
        $description = isset($post['description']) && is_string($post['description']) ? trim($post['description']) : '';

        if ($tableId <= 0) {
            throw new Exception("Invalid table selected for editing.");
        }
        if ($tableName === '') {
            throw new Exception($this->msg('manage_tables.err_table_name_empty', 'Please enter a table name.'));
        }
        if ($this->tableNameTaken($tableName, $tableId)) {
            throw new Exception($this->msg('manage_tables.err_table_name_taken', 'There is already a table called %s. Please choose a different name.', $tableName));
        }

        $stmt = $this->pdo->prepare("UPDATE dynamic_tables SET table_name = ?, description = ? WHERE id = ?");
        if (!$stmt->execute([$tableName, $description, $tableId])) {
            throw new Exception("Failed to update table metadata.");
        }

        $ip = isset($_SERVER['REMOTE_ADDR']) && is_string($_SERVER['REMOTE_ADDR']) ? $_SERVER['REMOTE_ADDR'] : '127.0.0.1';
        $audit = $this->pdo->prepare("INSERT INTO audit_logs (user_id, action, details, ip_address) VALUES (?, 'UPDATE_TABLE', ?, ?)");
        $audit->execute([$currentUser['id'], "Updated table ID {$tableId} metadata to: {$tableName}", $ip]);

        return "Table metadata successfully updated!";
    }

    public function deleteTable(int $tableId, array $currentUser): string
    {
        if ($tableId <= 1) {
            throw new Exception("The default root table cannot be deleted.");
        }

        $tStmt = $this->pdo->prepare("SELECT table_name FROM dynamic_tables WHERE id = ?");
        $tStmt->execute([$tableId]);
        $tableInfo = $tStmt->fetch(PDO::FETCH_ASSOC);

        if ($tableInfo === false) {
            throw new Exception("Table not found.");
        }

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

            if ($pIds !== []) {
                $placeholders = implode(',', array_fill(0, count($pIds), '?'));
                $delRp = $this->pdo->prepare("DELETE FROM role_permissions WHERE permission_id IN ({$placeholders})");
                $delRp->execute($pIds);
                $delP = $this->pdo->prepare("DELETE FROM permissions WHERE id IN ({$placeholders})");
                $delP->execute($pIds);
            }

            $delTbl = $this->pdo->prepare("DELETE FROM dynamic_tables WHERE id = ?");
            $delTbl->execute([$tableId]);

            $this->pdo->commit();

            $ip = isset($_SERVER['REMOTE_ADDR']) && is_string($_SERVER['REMOTE_ADDR']) ? $_SERVER['REMOTE_ADDR'] : '127.0.0.1';
            $audit = $this->pdo->prepare("INSERT INTO audit_logs (user_id, action, details, ip_address) VALUES (?, 'DELETE_TABLE', ?, ?)");
            $audit->execute([$currentUser['id'], "Deleted table ID {$tableId}: {$tableName}", $ip]);

            return "Table '{$tableName}' and all its associated data and permissions were successfully deleted.";
        } catch (Exception $e) {
            $this->pdo->rollBack();
            throw new Exception("Failed to delete table safely.");
        }
    }

    public function saveColumn(string $action, int $tableId, array $post, array $currentUser): string
    {
        $columnName = isset($post['column_name']) && is_string($post['column_name']) ? trim($post['column_name']) : '';
        $dataType = isset($post['data_type']) && is_string($post['data_type']) ? trim($post['data_type']) : 'VARCHAR';
        $maxLength = isset($post['max_length']) && $post['max_length'] !== '' ? (int)$post['max_length'] : null;
        $isRequired = isset($post['is_required']) ? 1 : 0;
        $excludeFromPublicSearch = isset($post['exclude_from_public_search']) ? 1 : 0;
        $showInList = isset($post['show_in_list']) ? 1 : 0;
        $showInRecord = isset($post['show_in_record']) ? 1 : 0;

        $allowedTypes = ['VARCHAR', 'TEXT', 'INT', 'BOOLEAN', 'DATE', 'TIME', 'SELECT', 'LOCATION'];
        if (!in_array($dataType, $allowedTypes, true)) {
            $dataType = 'VARCHAR';
        }

        $booleanDisplayFormat = ($dataType === 'BOOLEAN') ? (isset($post['boolean_display_format']) && is_string($post['boolean_display_format']) ? trim($post['boolean_display_format']) : 'yes_no') : null;
        $dateSearchBehavior = ($dataType === 'DATE') ? (isset($post['date_search_behavior']) && is_string($post['date_search_behavior']) ? trim($post['date_search_behavior']) : 'manual_only') : null;

        $fieldOptions = null;
        $allowMultiple = 0;
        if ($dataType === 'SELECT') {
            $rawOpts = isset($post['field_options']) && is_string($post['field_options']) ? $post['field_options'] : '';
            if (!function_exists('parse_column_options')) {
                $helper = dirname(__DIR__, 2) . '/includes/column_options.php';
                if (is_file($helper)) {
                    require_once $helper;
                }
            }
            $opts = function_exists('parse_column_options') ? parse_column_options($rawOpts) : [];
            if ($opts === []) {
                throw new Exception('Choice lists need at least one option (one per line).');
            }
            $fieldOptions = implode("
", $opts);
            $allowMultiple = isset($post['allow_multiple']) ? 1 : 0;
        }

        $minValue = null;
        $maxValue = null;
        if ($dataType === 'INT') {
            if (isset($post['min_value']) && is_string($post['min_value']) && $post['min_value'] !== '') {
                $minValue = (int) $post['min_value'];
            }
            if (isset($post['max_value']) && is_string($post['max_value']) && $post['max_value'] !== '') {
                $maxValue = (int) $post['max_value'];
            }
            if ($minValue !== null && $maxValue !== null && $minValue > $maxValue) {
                throw new Exception('Minimum cannot be greater than maximum.');
            }
        }

        if ($columnName === '') {
            throw new Exception($this->msg('manage_tables.err_col_name_empty', 'Please enter a column name.'));
        }
        $excludeColId = ($action === 'create') ? 0 : (isset($post['column_id']) ? (int) $post['column_id'] : 0);
        if ($this->columnNameTaken($tableId, $columnName, $excludeColId)) {
            throw new Exception($this->msg('manage_tables.err_col_name_taken', 'This table already has a column called %s. Please choose a different name.', $columnName));
        }

        $ip = isset($_SERVER['REMOTE_ADDR']) && is_string($_SERVER['REMOTE_ADDR']) ? $_SERVER['REMOTE_ADDR'] : '127.0.0.1';

        if ($action === 'create') {
            $orderStmt = $this->pdo->prepare("SELECT COALESCE(MAX(sort_order), 0) + 1 FROM table_columns WHERE table_id = ?");
            $orderStmt->execute([$tableId]);
            $nextSortOrder = (int)$orderStmt->fetchColumn();

            $stmt = $this->pdo->prepare("INSERT INTO table_columns (table_id, column_name, data_type, max_length, boolean_display_format, date_search_behavior, sort_order, is_required, exclude_from_public_search, created_by, field_options, allow_multiple, min_value, max_value, show_in_list, show_in_record) VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?)");
            if (!$stmt->execute([$tableId, $columnName, $dataType, $maxLength, $booleanDisplayFormat, $dateSearchBehavior, $nextSortOrder, $isRequired, $excludeFromPublicSearch, $currentUser['id'], $fieldOptions, $allowMultiple, $minValue, $maxValue, $showInList, $showInRecord])) {
                throw new Exception("Failed to create column.");
            }

            $audit = $this->pdo->prepare("INSERT INTO audit_logs (user_id, action, details, ip_address) VALUES (?, 'CREATE_COLUMN', ?, ?)");
            $audit->execute([$currentUser['id'], "Created column '{$columnName}' in table ID {$tableId}", $ip]);

            return "Dynamic column '{$columnName}' successfully created!";
        } else {
            $columnId = isset($post['column_id']) ? (int)$post['column_id'] : 0;
            if ($columnId <= 0) {
                throw new Exception("Invalid column selected for update.");
            }

            $stmt = $this->pdo->prepare("UPDATE table_columns SET column_name = ?, data_type = ?, max_length = ?, boolean_display_format = ?, date_search_behavior = ?, is_required = ?, exclude_from_public_search = ?, field_options = ?, allow_multiple = ?, min_value = ?, max_value = ?, show_in_list = ?, show_in_record = ? WHERE id = ?");
            if (!$stmt->execute([$columnName, $dataType, $maxLength, $booleanDisplayFormat, $dateSearchBehavior, $isRequired, $excludeFromPublicSearch, $fieldOptions, $allowMultiple, $minValue, $maxValue, $showInList, $showInRecord, $columnId])) {
                throw new Exception("Failed to update column.");
            }

            $audit = $this->pdo->prepare("INSERT INTO audit_logs (user_id, action, details, ip_address) VALUES (?, 'UPDATE_COLUMN', ?, ?)");
            $audit->execute([$currentUser['id'], "Updated column ID {$columnId}: {$columnName}", $ip]);

            return "Dynamic column '{$columnName}' successfully updated!";
        }
    }

    public function updateSortOrders(array $sortOrders): void
    {
        if ($sortOrders === []) {
            throw new Exception("No sort order data received.");
        }

        $stmt = $this->pdo->prepare("UPDATE table_columns SET sort_order = ? WHERE id = ?");
        $this->pdo->beginTransaction();
        try {
            foreach ($sortOrders as $colId => $orderVal) {
                $stmt->execute([(int)$orderVal, (int)$colId]);
            }
            $this->pdo->commit();
        } catch (Exception $e) {
            $this->pdo->rollBack();
            throw new Exception("Failed to update column sort orders.");
        }
    }

    public function deleteColumn(int $columnId, array $currentUser): string
    {
        if ($columnId <= 0) {
            throw new Exception("Invalid column selected for deletion.");
        }

        $cStmt = $this->pdo->prepare("SELECT column_name FROM table_columns WHERE id = ?");
        $cStmt->execute([$columnId]);
        $colInfo = $cStmt->fetch(PDO::FETCH_ASSOC);

        if ($colInfo === false) {
            throw new Exception("Column not found.");
        }

        $colName = isset($colInfo['column_name']) && is_string($colInfo['column_name']) ? $colInfo['column_name'] : '';
        
        $delVals = $this->pdo->prepare("DELETE FROM record_values WHERE column_id = ?");
        $delVals->execute([$columnId]);
        
        $delCol = $this->pdo->prepare("DELETE FROM table_columns WHERE id = ?");
        $delCol->execute([$columnId]);

        $ip = isset($_SERVER['REMOTE_ADDR']) && is_string($_SERVER['REMOTE_ADDR']) ? $_SERVER['REMOTE_ADDR'] : '127.0.0.1';
        $audit = $this->pdo->prepare("INSERT INTO audit_logs (user_id, action, details, ip_address) VALUES (?, 'DELETE_COLUMN', ?, ?)");
        $audit->execute([$currentUser['id'], "Deleted column ID {$columnId}: {$colName}", $ip]);

        return "Column '{$colName}' and its associated data entries were successfully deleted.";
    }

    private function tableNameTaken(string $name, int $exceptId = 0): bool
    {
        $sql = 'SELECT id FROM dynamic_tables WHERE LOWER(table_name) = LOWER(?)';
        $params = [$name];
        if ($exceptId > 0) {
            $sql .= ' AND id <> ?';
            $params[] = $exceptId;
        }
        $stmt = $this->pdo->prepare($sql . ' LIMIT 1');
        $stmt->execute($params);
        return $stmt->fetchColumn() !== false;
    }

    private function columnNameTaken(int $tableId, string $name, int $exceptId = 0): bool
    {
        $sql = 'SELECT id FROM table_columns WHERE table_id = ? AND LOWER(column_name) = LOWER(?)';
        $params = [$tableId, $name];
        if ($exceptId > 0) {
            $sql .= ' AND id <> ?';
            $params[] = $exceptId;
        }
        $stmt = $this->pdo->prepare($sql . ' LIMIT 1');
        $stmt->execute($params);
        return $stmt->fetchColumn() !== false;
    }

    private function msg(string $key, string $fallback, string $name = ''): string
    {
        $text = (function_exists('__') && __($key) !== $key) ? __($key) : $fallback;
        if ($name !== '' && str_contains($text, '%s')) {
            return sprintf($text, $name);
        }
        return $text;
    }

}
