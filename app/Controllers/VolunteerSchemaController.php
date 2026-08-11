<?php
/**
 * MIGRATED FILE MAPPING
 * ---------------------
 * Original Old File: admin/manage_volunteer_schema.php/admin/actions/save_volunteer_schema.php
 * Migrated Date: 2026-08-05 03:31:26
 */
declare(strict_types=1);

namespace App\Controllers;

use PDO;

class VolunteerSchemaController
{
    private PDO $pdo;

    public function __construct(PDO $pdo)
    {
        $this->pdo = $pdo;
    }

    public function index(): void
    {
        if (!\is_module_enabled($this->pdo, 'volunteers')) {
            http_response_code(403);
            exit('403 Forbidden: The Volunteer Portal module is currently disabled.');
        }

        /** @var array{id: int, username: string} $currentUser */
        $currentUser = require_admin_page($this->pdo, 'manage_volunteers', 'Manage volunteer form schema definitions');

        /** @var array{0: string, 1: string} $timePrefs */
        $timePrefs = get_user_time_prefs($currentUser);
        $userTimezone = $timePrefs[0];
        $fullFormatStr = $timePrefs[1];

        $settingsStmt = $this->pdo->query('SELECT setting_key, setting_value FROM volunteer_form_settings');
        $formSettings = [];
        if ($settingsStmt !== false) {
            while ($row = $settingsStmt->fetch(PDO::FETCH_ASSOC)) {
                if (isset($row['setting_key'], $row['setting_value'])
                    && is_string($row['setting_key'])
                    && is_string($row['setting_value'])
                ) {
                    $formSettings[$row['setting_key']] = $row['setting_value'];
                }
            }
        }
        $formTitle = $formSettings['form_title'] ?? 'Volunteer for Data Entry';
        $formIntro = $formSettings['form_intro']
            ?? 'Interested in helping transcribe and contribute? Let us know a little about yourself and any relevant experience.';

        $get = $_GET;
        /** @var array<string, mixed>|false $editCol */
        $editCol = false;
        if (isset($get['edit_column'])) {
            $editColId = (int) $get['edit_column'];
            $cStmt = $this->pdo->prepare('SELECT * FROM volunteer_columns WHERE id = ?');
            $cStmt->execute([$editColId]);
            $editCol = $cStmt->fetch(PDO::FETCH_ASSOC);
        }

        $stmtColumns = $this->pdo->query(
            'SELECT vc.* FROM volunteer_columns vc ORDER BY vc.sort_order ASC, vc.column_name ASC'
        );
        /** @var array<int, array<string, mixed>> $columns */
        $columns = $stmtColumns !== false ? $stmtColumns->fetchAll(PDO::FETCH_ASSOC) : [];

        foreach ($columns as &$col) {
            $uid = isset($col['created_by']) ? (int) $col['created_by'] : 0;
            if ($uid > 0 && function_exists('format_user_display_name_by_id')) {
                $col['created_by_display'] = format_user_display_name_by_id($this->pdo, $uid, $currentUser);
            } else {
                $col['created_by_display'] = 'System';
            }
        }
        unset($col);

        $message = $_SESSION['message'] ?? '';
        $error = $_SESSION['error'] ?? '';
        unset($_SESSION['message'], $_SESSION['error']);

        require_once __DIR__ . '/../Views/admin/manage_volunteer_schema.php';
    }

    public function store(): void
    {
        if (!\is_module_enabled($this->pdo, 'volunteers')) {
            http_response_code(403);
            exit('403 Forbidden');
        }

        $serverMethod = isset($_SERVER['REQUEST_METHOD']) && is_string($_SERVER['REQUEST_METHOD'])
            ? $_SERVER['REQUEST_METHOD'] : 'GET';
        if ($serverMethod !== 'POST') {
            http_response_code(405);
            exit('Method Not Allowed');
        }

        verify_csrf_token();
        /** @var array{id: int, username: string} $currentUser */
        $currentUser = require_permission($this->pdo, 'manage_volunteers', 'Manage volunteer schema definitions');

        $basePath = defined('BASE_PATH') ? rtrim(BASE_PATH, '/') : '';
        $post = $_POST;
        $action = isset($post['action']) && is_string($post['action']) ? $post['action'] : '';

        if ($action === 'create' || $action === 'update') {
            $columnName = isset($post['column_name']) && is_string($post['column_name']) ? trim($post['column_name']) : '';
            $dataType = isset($post['data_type']) && is_string($post['data_type']) ? trim($post['data_type']) : 'VARCHAR';
            $fieldSubtype = isset($post['field_subtype']) && is_string($post['field_subtype']) ? trim($post['field_subtype']) : '';
            $rawOptions = isset($post['field_options']) && is_string($post['field_options']) ? trim($post['field_options']) : '';

            $splitOptions = preg_split('/[\r\n]+|,/', $rawOptions);
            $mappedOptions = is_array($splitOptions) ? array_map('trim', $splitOptions) : [];
            $filteredOptions = array_filter($mappedOptions, static fn($val) => $val !== '');
            $fieldOptions = implode(', ', $filteredOptions);

            $allowMultiple = isset($post['allow_multiple']) ? 1 : 0;
            $maxLength = !empty($post['max_length']) ? (int) $post['max_length'] : null;
            $isRequired = isset($post['is_required']) ? 1 : 0;
            $booleanFormat = ($dataType === 'BOOLEAN')
                ? (isset($post['boolean_display_format']) && is_string($post['boolean_display_format'])
                    ? trim($post['boolean_display_format']) : 'yes_no')
                : null;

            if ($columnName !== '') {
                if ($action === 'create') {
                    $ordStmt = $this->pdo->query('SELECT COALESCE(MAX(sort_order), 0) + 1 FROM volunteer_columns');
                    $ord = $ordStmt !== false ? (int) $ordStmt->fetchColumn() : 1;
                    $stmt = $this->pdo->prepare(
                        'INSERT INTO volunteer_columns (column_name, data_type, field_subtype, field_options, allow_multiple, max_length, boolean_display_format, sort_order, is_required, created_by) VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?)'
                    );
                    $stmt->execute([
                        $columnName, $dataType, $fieldSubtype, $fieldOptions, $allowMultiple,
                        $maxLength, $booleanFormat, $ord, $isRequired, $currentUser['id'],
                    ]);
                    $_SESSION['message'] = "Volunteer field '{$columnName}' created successfully.";
                } else {
                    $colId = isset($post['column_id']) ? (int) $post['column_id'] : 0;
                    if ($colId > 0) {
                        $stmt = $this->pdo->prepare(
                            'UPDATE volunteer_columns SET column_name = ?, data_type = ?, field_subtype = ?, field_options = ?, allow_multiple = ?, max_length = ?, boolean_display_format = ?, is_required = ? WHERE id = ?'
                        );
                        $stmt->execute([
                            $columnName, $dataType, $fieldSubtype, $fieldOptions, $allowMultiple,
                            $maxLength, $booleanFormat, $isRequired, $colId,
                        ]);
                        $_SESSION['message'] = 'Volunteer field updated successfully.';
                    }
                }
            }
        } elseif ($action === 'delete') {
            $colId = isset($post['column_id']) ? (int) $post['column_id'] : 0;
            if ($colId > 0) {
                $this->pdo->prepare('DELETE FROM volunteer_submission_values WHERE column_id = ?')->execute([$colId]);
                $this->pdo->prepare('DELETE FROM volunteer_columns WHERE id = ?')->execute([$colId]);
                $_SESSION['message'] = 'Volunteer field deleted.';
            }
        } elseif ($action === 'update_order_batch') {
            $sortOrders = isset($post['sort_orders']) && is_array($post['sort_orders']) ? $post['sort_orders'] : [];
            $stmt = $this->pdo->prepare('UPDATE volunteer_columns SET sort_order = ? WHERE id = ?');
            $this->pdo->beginTransaction();
            foreach ($sortOrders as $id => $order) {
                $stmt->execute([(int) $order, (int) $id]);
            }
            $this->pdo->commit();
            exit;
        } elseif ($action === 'update_settings') {
            $formTitle = isset($post['form_title']) && is_string($post['form_title'])
                ? trim($post['form_title']) : 'Volunteer for Data Entry';
            $formIntro = isset($post['form_intro']) && is_string($post['form_intro'])
                ? trim($post['form_intro']) : '';
            $stmt = $this->pdo->prepare(
                "INSERT INTO volunteer_form_settings (setting_key, setting_value) VALUES ('form_title', ?), ('form_intro', ?)
                 ON DUPLICATE KEY UPDATE setting_value = VALUES(setting_value)"
            );
            $stmt->execute([$formTitle, $formIntro]);
            $_SESSION['message'] = 'Form presentation settings updated successfully.';
        }

        header('Location: ' . $basePath . '/admin/volunteers/schema');
        exit;
    }
}
