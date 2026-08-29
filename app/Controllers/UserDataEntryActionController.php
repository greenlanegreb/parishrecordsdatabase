<?php
/**
 * MIGRATED FILE MAPPING
 * ---------------------
 * Original Old File: user/data_entry.php/user/actions/save_data_entry.php
 * Migrated Date: 2026-08-05 04:50:29
 */
declare(strict_types=1);

namespace App\Controllers;

use App\Services\DuplicateCheckService;
use Exception;
use PDO;

require_once dirname(__DIR__, 2) . '/includes/form_fields.php';

require_once dirname(__DIR__, 2) . '/includes/column_options.php';

class UserDataEntryActionController
{
    private PDO $pdo;

    public function __construct(PDO $pdo)
    {
        $this->pdo = $pdo;
    }

    public function save(): void
    {
        $serverMethod = isset($_SERVER['REQUEST_METHOD']) && is_string($_SERVER['REQUEST_METHOD']) ? $_SERVER['REQUEST_METHOD'] : 'GET';
        if ($serverMethod !== 'POST') {
            http_response_code(405);
            exit('Method Not Allowed');
        }

        verify_csrf_token();
        /** @var array{id: int|string, username: string} $currentUser */
        $currentUser = require_permission($this->pdo, 'access_data_entry', 'Allows accessing the core data entry workstation and creating records');

        $basePath = defined('BASE_PATH') ? rtrim(BASE_PATH, '/') : '';
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
            $stmtCols = $this->pdo->prepare("SELECT id, column_name, is_required, data_type, field_options, allow_multiple, min_value, max_value FROM table_columns WHERE table_id = ?");
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
                $dataType = isset($colsMap[$cid]) ? (string)($colsMap[$cid]['data_type'] ?? '') : '';
                $isBool = ($dataType === 'BOOLEAN');
                $isDate = ($dataType === 'DATE');
                $isSelect = ($dataType === 'SELECT');
                $isInt = ($dataType === 'INT');
                $isLocation = ($dataType === 'LOCATION');
                $colName = isset($colsMap[$cid]['column_name']) && is_string($colsMap[$cid]['column_name']) ? $colsMap[$cid]['column_name'] : 'Field';

                if ($isBool) {
                    $cleanVal = ($val !== '' && $val !== null && !is_array($val)) ? trim((string)$val) : '';
                } elseif ($isDate) {
                    $cleanVal = normalize_incoming_date(is_scalar($val) ? (string)$val : null);
                } elseif ($isSelect) {
                    $cleanVal = flatten_posted_column_value($val);
                    $opts = parse_column_options($colsMap[$cid]['field_options'] ?? '');
                    $multi = !empty($colsMap[$cid]['allow_multiple']);
                    if ($cleanVal !== '' && !column_values_are_allowed($cleanVal, $opts, $multi)) {
                        $_SESSION['error'] = sprintf(__('save_data_entry.err_invalid_choice') !== 'save_data_entry.err_invalid_choice' ? __('save_data_entry.err_invalid_choice') : 'Please choose a listed option for %s.', $colName);
                        remember_field_error($cid, $_SESSION['error']);
                        header('Location: ' . $basePath . '/data-entry?table_id=' . $tableId);
                        exit;
                    }
                } elseif ($isInt) {
                    $cleanVal = is_scalar($val) ? trim((string)$val) : '';
                    if ($cleanVal !== '') {
                        if (!preg_match('/^-?\d+$/', $cleanVal)) {
                            $_SESSION['error'] = sprintf(__('save_data_entry.err_not_number') !== 'save_data_entry.err_not_number' ? __('save_data_entry.err_not_number') : '%s must be a whole number.', $colName);
                            remember_field_error($cid, $_SESSION['error']);
                            header('Location: ' . $basePath . '/data-entry?table_id=' . $tableId);
                            exit;
                        }
                        $n = (int) $cleanVal;
                        $min = $colsMap[$cid]['min_value'] ?? null;
                        $max = $colsMap[$cid]['max_value'] ?? null;
                        if ($min !== null && $min !== '' && $n < (int)$min) {
                            $_SESSION['error'] = sprintf(__('save_data_entry.err_min') !== 'save_data_entry.err_min' ? __('save_data_entry.err_min') : '%s is below the minimum.', $colName);
                            remember_field_error($cid, $_SESSION['error']);
                            header('Location: ' . $basePath . '/data-entry?table_id=' . $tableId);
                            exit;
                        }
                        if ($max !== null && $max !== '' && $n > (int)$max) {
                            $_SESSION['error'] = sprintf(__('save_data_entry.err_max') !== 'save_data_entry.err_max' ? __('save_data_entry.err_max') : '%s is above the maximum.', $colName);
                            remember_field_error($cid, $_SESSION['error']);
                            header('Location: ' . $basePath . '/data-entry?table_id=' . $tableId);
                            exit;
                        }
                    }
                } elseif ($isLocation) {
                    $postedLoc = is_array($val) ? $val : [];
                    $locData = \App\Services\LocationValueService::fromPosted($postedLoc);
                    if ($locData === null) {
                        $cleanVal = '';
                    } elseif (!\App\Services\LocationValueService::isComplete($locData)) {
                        $_SESSION['error'] = sprintf(__('save_data_entry.err_location') !== 'save_data_entry.err_location' ? __('save_data_entry.err_location') : 'Choose a place from the list and add a title and short text for %s.', $colName);
                        remember_field_error($cid, $_SESSION['error']);
                        header('Location: ' . $basePath . '/data-entry?table_id=' . $tableId);
                        exit;
                    } else {
                        $cleanVal = \App\Services\LocationValueService::encode($locData);
                    }
                } else {
                    $cleanVal = sanitize_incoming_text(is_scalar($val) ? (string)$val : flatten_posted_column_value($val));
                }

                $sanitizedInputs[$cid] = $cleanVal;

                if (isset($colsMap[$cid]) && !empty($colsMap[$cid]['is_required'])) {
                    if ($cleanVal === '') {
                        $_SESSION['error'] = sprintf(__('save_data_entry.err_required_field'), $colName);
                        remember_field_error($cid, $_SESSION['error']);
                        header('Location: ' . $basePath . '/data-entry?table_id=' . $tableId);
                        exit;
                    }
                }
            }

            $hasContent = false;
            foreach ($sanitizedInputs as $val) {
                if ($val !== '') { $hasContent = true; break; }
            }

            if ($hasContent) {
                try {
                    $dupMode = function_exists('get_setting')
                        ? get_setting($this->pdo, 'duplicate_mode', 'warn') : 'warn';
                    $dupPicky = function_exists('get_setting')
                        ? get_setting($this->pdo, 'duplicate_picky', 'similar') : 'similar';
                    if (!in_array($dupMode, ['off', 'warn', 'block', 'flag'], true)) {
                        $dupMode = 'warn';
                    }

                    if ($dupMode !== 'off' && !($confirmedDuplicate && $dupMode !== 'block')) {
                        $dupes = (new DuplicateCheckService($this->pdo))->findMatches(
                            $tableId,
                            $sanitizedInputs,
                            $colsMap,
                            $dupPicky === 'exact' ? 'exact' : 'similar'
                        );
                        if ($dupes !== []) {
                            $_SESSION['duplicate_warning'] = true;
                            $_SESSION['duplicate_matches'] = $dupes;
                            $_SESSION['duplicate_mode'] = $dupMode;
                            header('Location: ' . $basePath . '/data-entry?table_id=' . $tableId);
                            exit;
                        }
                    }

                    if ($confirmedDuplicate && $dupMode === 'flag' && function_exists('audit')) {
                        $remote = isset($_SERVER['REMOTE_ADDR']) && is_string($_SERVER['REMOTE_ADDR'])
                            ? $_SERVER['REMOTE_ADDR'] : '127.0.0.1';
                        audit(
                            $this->pdo,
                            (int) $currentUser['id'],
                            'DUPLICATE_SAVED',
                            'A similar record was saved after a warning for table ' . $tableId,
                            $remote
                        );
                    }

                    $this->pdo->beginTransaction();

                    // Proceed with insertion bound to the active table ID
                    $recStmt = $this->pdo->prepare("INSERT INTO records (table_id, created_by) VALUES (?, ?)");
                    $recStmt->execute([$tableId, $currentUser['id']]);
                    $recordId = (int)$this->pdo->lastInsertId();

                    $auditDetailsParts = [sprintf(__('save_data_entry.audit_created_prefix'), $tableId)];

                    foreach ($sanitizedInputs as $columnId => $valueContent) {
                        if ($valueContent !== '') {
                            $valStmt = $this->pdo->prepare("INSERT INTO record_values (record_id, column_id, value_content) VALUES (?, ?, ?)");
                            $valStmt->execute([$recordId, $columnId, $valueContent]);
                            if (isset($colsMap[$columnId]['data_type']) && $colsMap[$columnId]['data_type'] === 'LOCATION') {
                                $pin = \App\Services\LocationValueService::decode($valueContent);
                                if (\App\Services\LocationValueService::isComplete($pin)) {
                                    try {
                                        \App\Services\LocationValueService::upsertPin($this->pdo, $tableId, $recordId, (int) $columnId, $pin);
                                    } catch (\Throwable $e) {
                                        // Pin write must not block the record save
                                    }
                                }
                            }

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
        header('Location: ' . $basePath . '/data-entry?table_id=' . $tableId);
        exit;
    }
}
