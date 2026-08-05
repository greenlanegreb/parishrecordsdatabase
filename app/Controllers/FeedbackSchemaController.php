<?php
/**
 * MIGRATED FILE MAPPING
 * ---------------------
 * Original Old File: admin/manage_feedback_schema.php/admin/actions/save_feedback_schema.php
 * Migrated Date: 2026-08-05 03:13:45
 */declare(strict_types=1);


namespace App\Controllers;

use PDO;

class FeedbackSchemaController
{
    private PDO $pdo;

    public function __construct(PDO $pdo)
    {
        $this->pdo = $pdo;
    }

    public function index(): void
    {
        $moduleCheck = $this->pdo->prepare("SELECT is_enabled FROM modules WHERE module_name = ?");
        $moduleCheck->execute(['feedback']);
        if (!$moduleCheck->fetchColumn()) {
            http_response_code(403);
            exit('403 Forbidden: The Feedback Submissions module is currently disabled.');
        }

        if (session_status() === PHP_SESSION_NONE) {
            session_start();
        }

        /** @var array{id: int, username: string} $currentUser */
        $currentUser = require_admin_page($this->pdo, 'manage_feedback', 'Manage feedback ticket schema definitions');
        
        /** @var array{0: string, 1: string} $timePrefs */
        $timePrefs = get_user_time_prefs($currentUser);
        $userTimezone = $timePrefs[0];
        $fullFormatStr = $timePrefs[1];

        // Fetch form title & intro settings
        $settingsStmt = $this->pdo->query("SELECT setting_key, setting_value FROM feedback_form_settings");
        $formSettings = [];
        if ($settingsStmt !== false) {
            while ($row = $settingsStmt->fetch(PDO::FETCH_ASSOC)) {
                if (isset($row['setting_key'], $row['setting_value']) && is_string($row['setting_key']) && is_string($row['setting_value'])) {
                    $formSettings[$row['setting_key']] = $row['setting_value'];
                }
            }
        }
        $formTitle = $formSettings['form_title'] ?? 'Submit Support Ticket or Feedback';
        $formIntro = $formSettings['form_intro'] ?? 'Fill out the form below to open a ticket with our team.';

        $get = $_GET;
        /** @var array<string, mixed>|false $editCol */
        $editCol = false;
        if (isset($get['edit_column'])) {
            $editColId = (int)$get['edit_column'];
            $cStmt = $this->pdo->prepare("SELECT * FROM feedback_columns WHERE id = ?");
            $cStmt->execute([$editColId]);
            $editCol = $cStmt->fetch(PDO::FETCH_ASSOC);
        }

        $stmtColumns = $this->pdo->query("SELECT fc.*, u.username FROM feedback_columns fc LEFT JOIN users u ON fc.created_by = u.id ORDER BY fc.sort_order ASC, fc.column_name ASC");
        /** @var array<int, array<string, mixed>> $columns */
        $columns = $stmtColumns !== false ? $stmtColumns->fetchAll(PDO::FETCH_ASSOC) : [];

        $message = $_SESSION['message'] ?? '';
        $error = $_SESSION['error'] ?? '';
        unset($_SESSION['message'], $_SESSION['error']);

        require_once __DIR__ . '/../Views/admin/manage_feedback_schema.php';
    }

    public function store(): void
    {
        if (session_status() === PHP_SESSION_NONE) {
            session_start();
        }

        $moduleCheck = $this->pdo->prepare("SELECT is_enabled FROM modules WHERE module_name = ?");
        $moduleCheck->execute(['feedback']);
        if (!$moduleCheck->fetchColumn()) {
            http_response_code(403);
            exit('403 Forbidden');
        }

        $serverMethod = isset($_SERVER['REQUEST_METHOD']) && is_string($_SERVER['REQUEST_METHOD']) ? $_SERVER['REQUEST_METHOD'] : 'GET';
        if ($serverMethod !== 'POST') {
            http_response_code(405);
            exit('Method Not Allowed');
        }

        verify_csrf_token();
        /** @var array{id: int, username: string} $currentUser */
        $currentUser = require_permission($this->pdo, 'manage_feedback', 'Manage feedback schema definitions');

        $post = $_POST;
        $action = isset($post['action']) && is_string($post['action']) ? $post['action'] : '';

        if ($action === 'create' || $action === 'update') {
            $columnName = isset($post['column_name']) && is_string($post['column_name']) ? trim($post['column_name']) : '';
            $dataType = isset($post['data_type']) && is_string($post['data_type']) ? trim($post['data_type']) : 'VARCHAR';
            $fieldSubtype = isset($post['field_subtype']) && is_string($post['field_subtype']) ? trim($post['field_subtype']) : '';
            $fieldOptions = isset($post['field_options']) && is_string($post['field_options']) ? trim($post['field_options']) : '';
            $allowMultiple = isset($post['allow_multiple']) ? 1 : 0;
            $maxLength = !empty($post['max_length']) ? (int)$post['max_length'] : null;
            $isRequired = isset($post['is_required']) ? 1 : 0;
            $booleanFormat = ($dataType === 'BOOLEAN') ? (isset($post['boolean_display_format']) && is_string($post['boolean_display_format']) ? trim($post['boolean_display_format']) : 'yes_no') : null;

            if ($columnName !== '') {
                if ($action === 'create') {
                    $ordStmt = $this->pdo->query("SELECT COALESCE(MAX(sort_order), 0) + 1 FROM feedback_columns");
                    $ord = $ordStmt !== false ? (int)$ordStmt->fetchColumn() : 1;

                    $stmt = $this->pdo->prepare("INSERT INTO feedback_columns (column_name, data_type, field_subtype, field_options, allow_multiple, max_length, boolean_display_format, sort_order, is_required, created_by) VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?)");
                    $stmt->execute([$columnName, $dataType, $fieldSubtype, $fieldOptions, $allowMultiple, $maxLength, $booleanFormat, $ord, $isRequired, $currentUser['id']]);

                    $_SESSION['message'] = "Ticket field '{$columnName}' created successfully.";
                } else {
                    $colId = isset($post['column_id']) ? (int)$post['column_id'] : 0;
                    if ($colId > 0) {
                        $stmt = $this->pdo->prepare("UPDATE feedback_columns SET column_name = ?, data_type = ?, field_subtype = ?, field_options = ?, allow_multiple = ?, max_length = ?, boolean_display_format = ?, is_required = ? WHERE id = ?");
                        $stmt->execute([$columnName, $dataType, $fieldSubtype, $fieldOptions, $allowMultiple, $maxLength, $booleanFormat, $isRequired, $colId]);

                        $_SESSION['message'] = "Ticket field updated successfully.";
                    }
                }
            }
        } elseif ($action === 'delete') {
            $colId = isset($post['column_id']) ? (int)$post['column_id'] : 0;
            if ($colId > 0) {
                $this->pdo->prepare("DELETE FROM feedback_ticket_values WHERE column_id = ?")->execute([$colId]);
                $this->pdo->prepare("DELETE FROM feedback_columns WHERE id = ?")->execute([$colId]);
                $_SESSION['message'] = "Ticket field deleted.";
            }
        } elseif ($action === 'update_order_batch') {
            $sortOrders = isset($post['sort_orders']) && is_array($post['sort_orders']) ? $post['sort_orders'] : [];
            $stmt = $this->pdo->prepare("UPDATE feedback_columns SET sort_order = ? WHERE id = ?");
            $this->pdo->beginTransaction();
            foreach ($sortOrders as $id => $order) {
                $stmt->execute([(int)$order, (int)$id]);
            }
            $this->pdo->commit();
            exit;
        } elseif ($action === 'update_settings') {
            $formTitle = isset($post['form_title']) && is_string($post['form_title']) ? trim($post['form_title']) : 'Submit Support Ticket or Feedback';
            $formIntro = isset($post['form_intro']) && is_string($post['form_intro']) ? trim($post['form_intro']) : '';

            $stmt = $this->pdo->prepare("INSERT INTO feedback_form_settings (setting_key, setting_value) VALUES ('form_title', ?), ('form_intro', ?) ON DUPLICATE KEY UPDATE setting_value = VALUES(setting_value)");
            $stmt->execute([$formTitle, $formIntro]);

            $_SESSION['message'] = "Feedback form presentation settings updated successfully.";
        }

        header('Location: /admin/feedback/schema');
        exit;
    }
}
