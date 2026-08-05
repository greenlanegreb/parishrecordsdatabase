<?php
/**
 * MIGRATED FILE MAPPING
 * ---------------------
 * Original Old File: user/data_entry.php/user/actions/save_data_entry.php
 * Migrated Date: 2026-08-05 04:50:29
 */declare(strict_types=1);


namespace App\Controllers;

use Exception;
use PDO;

class UserDataEntryActionController
{
    private PDO $pdo;

    public function __construct(PDO $pdo)
    {
        $this->pdo = $pdo;
    }

    public function save(): void
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
        /** @var array{id: int|string, username: string} $currentUser */
        $currentUser = require_permission($this->pdo, 'access_data_entry', 'Allows accessing the core data entry workstation and creating records');

        $post = $_POST;
        $tableId = isset($post['table_id']) ? (int)$post['table_id'] : 1;

        // Enforce table-specific permission check
        $permKey = 'view_table_' . $tableId;
        if ($tableId !== 1 && !has_permission($this->pdo, $permKey)) {
            http_response_code(403);
            exit('Unauthorized table access.');
        }

        $action = isset($post['action']) && is_string($post['action']) ? $post['action'] : '';
        $remoteAddr = isset($_SERVER['REMOTE_ADDR']) && is_string($_SERVER['REMOTE_ADDR']) ? $_SERVER['REMOTE_ADDR'] : '127.0.0.1';

        if ($action === 'insert_record') {
            /** @var array<int, string> $inputFilters */
            $inputFilters = isset($post['filters']) && is_array($post['filters']) ? $post['filters'] : [];
            $confirmedDuplicate = isset($post['confirm_duplicate']) && $post['confirm_duplicate'] === '1';

            // Fetch column metadata specifically for this table
            /** @var array<int, array<string, mixed>> $colsMap */
            $colsMap = [];
            $stmtCols = $this->pdo->prepare("SELECT id, column_name, is_required, data_type FROM table_columns WHERE table_id = ?");
            $stmtCols->execute([$tableId]);
            while ($col = $stmtCols->fetch(PDO::FETCH_ASSOC)) {
                $cId = isset($col['id']) ? (int)$col['id'] : 0;
                $colsMap[$cId] = $col;
            }

            // Save submitted filters to session so form fields persist if validation fails or duplicate warning triggers
            $_SESSION['submitted_filters'] = $inputFilters;

            // Server-side required field check & text sanitization prep
            /** @var array<int, string> $sanitizedInputs */
            $sanitizedInputs = [];
            foreach ($inputFilters as $cid => $val) {
                $isBool = (isset($colsMap[$cid]) && (string)($colsMap[$cid]['data_type'] ?? '') === 'BOOLEAN');
                
                // Handle boolean "0" properly without treating it as empty
                if ($isBool) {
                    $cleanVal = ($val !== '' && $val !== null) ? trim((string)$val) : '';
                } else {
                    $cleanVal = sanitize_incoming_text((string)$val);
                }

                $sanitizedInputs[$cid] = $cleanVal;

                if (isset($colsMap[$cid]) && !empty($colsMap[$cid]['is_required'])) {
                    if ($cleanVal === '') {
                        $colName = isset($colsMap[$cid]['column_name']) && is_string($colsMap[$cid]['column_name']) ? $colsMap[$cid]['column_name'] : 'Field';
                        $_SESSION['error'] = sprintf(__('save_data_entry.err_required_field'), $colName);
                        header('Location: /user/data_entry.php?table_id=' . $tableId);
                        exit;
                    }
                }
            }

            $hasContent = false;
            foreach ($sanitizedInputs as $val) {
                if ($val !== '') { $hasContent = true; break; }
            }

            if ($hasContent) {
                $firstColVal = '';
                $firstColId = 0;
                foreach ($sanitizedInputs as $cid => $cval) {
                    if ($cval !== '') {
                        $firstColId = $cid;
                        $firstColVal = $cval;
                        break;
                    }
                }

                try {
                    $this->pdo->beginTransaction();

                    // Check for duplicates within the same table if not confirmed
                    if (!$confirmedDuplicate && $firstColVal !== '') {
                        $checkStmt = $this->pdo->prepare("
                            SELECT r.id, rv.value_content, u.username 
                            FROM record_values rv
                            JOIN records r ON rv.record_id = r.id
                            LEFT JOIN users u ON r.created_by = u.id
                            WHERE r.table_id = ? AND rv.column_id = ? AND rv.value_content = ?
                            FOR UPDATE
                        ");
                        $checkStmt->execute([$tableId, $firstColId, $firstColVal]);
                        /** @var array<int, array<string, mixed>> $existingMatches */
                        $existingMatches = $checkStmt->fetchAll(PDO::FETCH_ASSOC);

                        if (count($existingMatches) > 0) {
                            $this->pdo->rollBack();
                            $_SESSION['duplicate_warning'] = true;
                            $_SESSION['duplicate_matches'] = $existingMatches;
                            header('Location: /user/data_entry.php?table_id=' . $tableId);
                            exit;
                        }
                    }

                    // Proceed with insertion bound to the active table ID
                    $recStmt = $this->pdo->prepare("INSERT INTO records (table_id, created_by) VALUES (?, ?)");
                    $recStmt->execute([$tableId, $currentUser['id']]);
                    $recordId = (int)$this->pdo->lastInsertId();

                    $auditDetailsParts = [sprintf(__('save_data_entry.audit_created_prefix'), $tableId)];

                    foreach ($sanitizedInputs as $columnId => $valueContent) {
                        if ($valueContent !== '') {
                            $valStmt = $this->pdo->prepare("INSERT INTO record_values (record_id, column_id, value_content) VALUES (?, ?, ?)");
                            $valStmt->execute([$recordId, $columnId, $valueContent]);

                            // Build readable summary for audit details
                            if (isset($colsMap[$columnId])) {
                                $colName = isset($colsMap[$columnId]['column_name']) && is_string($colsMap[$columnId]['column_name']) ? $colsMap[$columnId]['column_name'] : 'Field';
                                $auditDetailsParts[] = "{$colName}: {$valueContent}";
                            }
                        }
                    }

                    // Increment points securely via helper function
                    adjust_user_points($this->pdo, (int)$currentUser['id'], 1);

                    // Enhanced Audit log with initial field values
                    $auditDetails = implode(' | ', $auditDetailsParts);
                    $auditStmt = $this->pdo->prepare("INSERT INTO audit_logs (user_id, action, record_id, details, ip_address) VALUES (?, ?, ?, ?, ?)");
                    $auditStmt->execute([$currentUser['id'], 'INSERT', $recordId, $auditDetails, $remoteAddr]);

                    $this->pdo->commit();
                    // Clear submitted filters on successful save so the form resets for the next entry
                    unset($_SESSION['submitted_filters']);
                    $_SESSION['message'] = __('save_data_entry.msg_success');
                } catch (Exception $e) {
                    if ($this->pdo->inTransaction()) {
                        $this->pdo->rollBack();
                    }
                    $_SESSION['error'] = "Database error: " . $e->getMessage();
                }
            }
        }

        unset($_SESSION['duplicate_warning'], $_SESSION['duplicate_matches']);
        header('Location: /user/data_entry.php?table_id=' . $tableId);
        exit;
    }
}
