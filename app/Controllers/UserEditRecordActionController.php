<?php
declare(strict_types=1);

namespace App\Controllers;

use App\Services\LocationValueService;
use App\Services\RecordMaintenanceService;
use Exception;
use PDO;

/**
 * Save direct record edit. Same field rules as data entry / suggest-edit.
 */
class UserEditRecordActionController
{
    private PDO $pdo;

    public function __construct(PDO $pdo)
    {
        $this->pdo = $pdo;
    }

    public function save(int|string $id = 0): void
    {
        if (is_file(dirname(__DIR__, 2) . '/includes/form_fields.php')) {
            require_once dirname(__DIR__, 2) . '/includes/form_fields.php';
        }
        if (is_file(dirname(__DIR__, 2) . '/includes/column_options.php')) {
            require_once dirname(__DIR__, 2) . '/includes/column_options.php';
        }

        if (($_SERVER['REQUEST_METHOD'] ?? '') !== 'POST') {
            http_response_code(405);
            exit('Method Not Allowed');
        }

        verify_csrf_token();

        /** @var array{id: int|string, username?: string} $currentUser */
        $currentUser = require_permission(
            $this->pdo,
            'edit_records',
            'Edit and merge existing records (direct edit; not the public suggestion queue)'
        );

        $basePath = defined('BASE_PATH') ? rtrim((string) BASE_PATH, '/') : '';
        $recordId = (int) $id;
        $post = $_POST;

        $returnUrl = isset($post['return_url']) && is_string($post['return_url']) ? trim($post['return_url']) : '';
        if ($returnUrl === '' || preg_match('#^https?://#i', $returnUrl)) {
            $returnUrl = $basePath . '/data-entry';
        } elseif ($basePath !== '' && str_starts_with($returnUrl, '/') && !str_starts_with($returnUrl, $basePath)) {
            $returnUrl = $basePath . $returnUrl;
        }

        $editFormUrl = $basePath . '/records/' . $recordId . '/edit?return=' . rawurlencode(
            $returnUrl === $basePath . '/data-entry' ? '/data-entry' : $returnUrl
        );

        $redirectEdit = static function () use ($editFormUrl): void {
            header('Location: ' . $editFormUrl);
            exit;
        };

        try {
            if ($recordId < 1) {
                throw new Exception('No record specified.');
            }

            $stmt = $this->pdo->prepare('SELECT id, table_id FROM records WHERE id = ?');
            $stmt->execute([$recordId]);
            $record = $stmt->fetch(PDO::FETCH_ASSOC);
            if ($record === false) {
                throw new Exception('That record was not found.');
            }
            $tableId = (int) ($record['table_id'] ?? 0);
            if ($tableId > 0 && function_exists('user_can_view_table')
                && !user_can_view_table($this->pdo, $tableId, $currentUser)) {
                throw new Exception('You are not allowed to edit records in this table.');
            }

            $colsStmt = $this->pdo->prepare('SELECT * FROM table_columns WHERE table_id = ?');
            $colsStmt->execute([$tableId]);
            /** @var array<int, array<string, mixed>> $colsMap */
            $colsMap = [];
            while ($col = $colsStmt->fetch(PDO::FETCH_ASSOC)) {
                $cid = isset($col['id']) ? (int) $col['id'] : 0;
                if ($cid > 0) {
                    $colsMap[$cid] = $col;
                }
            }

            $fields = isset($post['fields']) && is_array($post['fields']) ? $post['fields'] : [];
            /** @var array<int, string> $cleanValues */
            $cleanValues = [];

            foreach ($colsMap as $cid => $col) {
                $dataType = isset($col['data_type']) && is_string($col['data_type']) ? $col['data_type'] : '';
                $colName = isset($col['column_name']) && is_string($col['column_name']) ? $col['column_name'] : 'Field';
                $isRequired = !empty($col['is_required']);
                $val = $fields[$cid] ?? ($fields[(string) $cid] ?? null);

                $isBool = ($dataType === 'BOOLEAN');
                $isDate = ($dataType === 'DATE');
                $isTime = ($dataType === 'TIME');
                $isSelect = ($dataType === 'SELECT');
                $isInt = ($dataType === 'INT');
                $isLocation = ($dataType === 'LOCATION');

                if ($isBool) {
                    $cleanVal = ($val !== '' && $val !== null && !is_array($val)) ? trim((string) $val) : '';
                } elseif ($isDate) {
                    $cleanVal = function_exists('normalize_incoming_date')
                        ? normalize_incoming_date(is_scalar($val) ? (string) $val : null)
                        : (is_scalar($val) ? trim((string) $val) : '');
                } elseif ($isTime) {
                    $cleanVal = function_exists('normalize_incoming_time')
                        ? normalize_incoming_time(is_scalar($val) ? (string) $val : null)
                        : (is_scalar($val) ? trim((string) $val) : '');
                } elseif ($isSelect) {
                    $cleanVal = function_exists('flatten_posted_column_value')
                        ? flatten_posted_column_value($val)
                        : (is_scalar($val) ? trim((string) $val) : '');
                    $opts = function_exists('parse_column_options')
                        ? parse_column_options($col['field_options'] ?? '')
                        : [];
                    $multi = !empty($col['allow_multiple']);
                    if ($cleanVal !== '' && function_exists('column_values_are_allowed')
                        && !column_values_are_allowed($cleanVal, $opts, $multi)) {
                        $msg = sprintf(
                            __('save_data_entry.err_invalid_choice') !== 'save_data_entry.err_invalid_choice'
                                ? __('save_data_entry.err_invalid_choice')
                                : 'Please choose a listed option for %s.',
                            $colName
                        );
                        if (function_exists('remember_field_error')) {
                            remember_field_error($cid, $msg);
                        }
                        $_SESSION['error'] = $msg;
                        $_SESSION['edit_record_draft'] = ['record_id' => $recordId, 'fields' => $fields];
                        $redirectEdit();
                    }
                } elseif ($isInt) {
                    $cleanVal = is_scalar($val) ? trim((string) $val) : '';
                    if ($cleanVal !== '') {
                        if (!preg_match('/^-?\d+$/', $cleanVal)) {
                            $msg = sprintf(
                                __('save_data_entry.err_not_number') !== 'save_data_entry.err_not_number'
                                    ? __('save_data_entry.err_not_number')
                                    : '%s must be a whole number.',
                                $colName
                            );
                            if (function_exists('remember_field_error')) {
                                remember_field_error($cid, $msg);
                            }
                            $_SESSION['error'] = $msg;
                            $_SESSION['edit_record_draft'] = ['record_id' => $recordId, 'fields' => $fields];
                            $redirectEdit();
                        }
                        $intVal = (int) $cleanVal;
                        if (isset($col['min_value']) && $col['min_value'] !== null && $col['min_value'] !== ''
                            && $intVal < (int) $col['min_value']) {
                            $msg = sprintf(
                                __('save_data_entry.err_min') !== 'save_data_entry.err_min'
                                    ? __('save_data_entry.err_min')
                                    : '%s is below the minimum.',
                                $colName
                            );
                            if (function_exists('remember_field_error')) {
                                remember_field_error($cid, $msg);
                            }
                            $_SESSION['error'] = $msg;
                            $_SESSION['edit_record_draft'] = ['record_id' => $recordId, 'fields' => $fields];
                            $redirectEdit();
                        }
                        if (isset($col['max_value']) && $col['max_value'] !== null && $col['max_value'] !== ''
                            && $intVal > (int) $col['max_value']) {
                            $msg = sprintf(
                                __('save_data_entry.err_max') !== 'save_data_entry.err_max'
                                    ? __('save_data_entry.err_max')
                                    : '%s is above the maximum.',
                                $colName
                            );
                            if (function_exists('remember_field_error')) {
                                remember_field_error($cid, $msg);
                            }
                            $_SESSION['error'] = $msg;
                            $_SESSION['edit_record_draft'] = ['record_id' => $recordId, 'fields' => $fields];
                            $redirectEdit();
                        }
                    }
                } elseif ($isLocation) {
                    $postedLoc = is_array($val) ? $val : [];
                    $locData = LocationValueService::fromPosted($postedLoc);
                    if ($locData === null) {
                        $cleanVal = '';
                    } elseif (!LocationValueService::isComplete($locData)) {
                        $msg = sprintf(
                            __('save_data_entry.err_location') !== 'save_data_entry.err_location'
                                ? __('save_data_entry.err_location')
                                : 'Choose a place from the list and add a title and short text for %s.',
                            $colName
                        );
                        if (function_exists('remember_field_error')) {
                            remember_field_error($cid, $msg);
                        }
                        $_SESSION['error'] = $msg;
                        $_SESSION['edit_record_draft'] = ['record_id' => $recordId, 'fields' => $fields];
                        $redirectEdit();
                    } else {
                        $cleanVal = LocationValueService::encode($locData);
                    }
                } else {
                    $cleanVal = function_exists('sanitize_incoming_text')
                        ? sanitize_incoming_text(is_scalar($val) ? (string) $val : '')
                        : (is_scalar($val) ? trim((string) $val) : '');
                }

                if ($isRequired && $cleanVal === '') {
                    $msg = sprintf(
                        __('save_data_entry.err_required') !== 'save_data_entry.err_required'
                            ? __('save_data_entry.err_required')
                            : '%s is required.',
                        $colName
                    );
                    if (function_exists('remember_field_error')) {
                        remember_field_error($cid, $msg);
                    }
                    $_SESSION['error'] = $msg;
                    $_SESSION['edit_record_draft'] = ['record_id' => $recordId, 'fields' => $fields];
                    $redirectEdit();
                }

                $cleanValues[$cid] = $cleanVal;
            }

            $remoteAddr = isset($_SERVER['REMOTE_ADDR']) && is_string($_SERVER['REMOTE_ADDR'])
                ? $_SERVER['REMOTE_ADDR'] : '127.0.0.1';

            (new RecordMaintenanceService($this->pdo))->updateRecordValues(
                $recordId,
                $tableId,
                $cleanValues,
                $colsMap,
                (int) $currentUser['id'],
                $remoteAddr
            );

            $_SESSION['message'] = __('data_entry.edit_record_saved') !== 'data_entry.edit_record_saved'
                ? __('data_entry.edit_record_saved')
                : 'The record was updated.';
            header('Location: ' . $returnUrl);
            exit;
        } catch (Exception $e) {
            $_SESSION['error'] = $e->getMessage();
            if ($recordId > 0) {
                header('Location: ' . $editFormUrl);
            } else {
                header('Location: ' . $returnUrl);
            }
            exit;
        }
    }
}
