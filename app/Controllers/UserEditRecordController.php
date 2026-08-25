<?php
declare(strict_types=1);

namespace App\Controllers;

use PDO;

/**
 * Direct record edit (permission: edit_records). Independent of moderation module.
 */
class UserEditRecordController
{
    private PDO $pdo;

    public function __construct(PDO $pdo)
    {
        $this->pdo = $pdo;
    }

    public function show(int|string $id = 0): void
    {
        if (is_file(dirname(__DIR__, 2) . '/includes/form_fields.php')) {
            require_once dirname(__DIR__, 2) . '/includes/form_fields.php';
        }
        if (is_file(dirname(__DIR__, 2) . '/includes/column_options.php')) {
            require_once dirname(__DIR__, 2) . '/includes/column_options.php';
        }

        /** @var array{id: int|string, username?: string, date_format?: string} $currentUser */
        $currentUser = require_permission(
            $this->pdo,
            'edit_records',
            'Edit and merge existing records (direct edit; not the public suggestion queue)'
        );

        $basePath = defined('BASE_PATH') ? rtrim((string) BASE_PATH, '/') : '';
        $recordId = (int) $id;
        if ($recordId < 1) {
            $_SESSION['error'] = 'No record specified.';
            header('Location: ' . $basePath . '/data-entry');
            exit;
        }

        $stmt = $this->pdo->prepare(
            'SELECT r.id, r.table_id, r.created_by, r.created_at, dt.table_name
             FROM records r
             INNER JOIN dynamic_tables dt ON dt.id = r.table_id
             WHERE r.id = ?'
        );
        $stmt->execute([$recordId]);
        $record = $stmt->fetch(PDO::FETCH_ASSOC);
        if ($record === false) {
            $_SESSION['error'] = 'That record was not found.';
            header('Location: ' . $basePath . '/data-entry');
            exit;
        }

        $tableId = (int) ($record['table_id'] ?? 0);
        if ($tableId > 0 && function_exists('user_can_view_table')
            && !user_can_view_table($this->pdo, $tableId, $currentUser)) {
            $_SESSION['error'] = 'You are not allowed to edit records in this table.';
            header('Location: ' . $basePath . '/data-entry');
            exit;
        }

        $colsStmt = $this->pdo->prepare(
            'SELECT * FROM table_columns WHERE table_id = ? ORDER BY sort_order ASC, id ASC'
        );
        $colsStmt->execute([$tableId]);
        /** @var array<int, array<string, mixed>> $columns */
        $columns = $colsStmt->fetchAll(PDO::FETCH_ASSOC);

        $valStmt = $this->pdo->prepare(
            'SELECT column_id, value_content FROM record_values WHERE record_id = ?'
        );
        $valStmt->execute([$recordId]);
        /** @var array<int, string> $values */
        $values = [];
        while ($row = $valStmt->fetch(PDO::FETCH_ASSOC)) {
            $cid = isset($row['column_id']) ? (int) $row['column_id'] : 0;
            if ($cid > 0) {
                $values[$cid] = isset($row['value_content']) && is_string($row['value_content'])
                    ? $row['value_content'] : '';
            }
        }

        // Restore draft after validation failure
        if (isset($_SESSION['edit_record_draft']) && is_array($_SESSION['edit_record_draft'])
            && (int) ($_SESSION['edit_record_draft']['record_id'] ?? 0) === $recordId) {
            $draftFields = $_SESSION['edit_record_draft']['fields'] ?? [];
            if (is_array($draftFields)) {
                foreach ($draftFields as $k => $v) {
                    $cid = (int) $k;
                    if ($cid < 1) {
                        continue;
                    }
                    if (is_array($v)) {
                        // LOCATION (and similar) kept as array for the form; encode for simple display slots
                        $values[$cid] = $v;
                    } elseif (is_scalar($v)) {
                        $values[$cid] = (string) $v;
                    }
                }
            }
            unset($_SESSION['edit_record_draft']);
        }

        $returnUrl = '/data-entry';
        if (isset($_GET['return']) && is_string($_GET['return'])) {
            $r = trim($_GET['return']);
            if ($r !== '' && !preg_match('#^https?://#i', $r)) {
                $returnUrl = $r;
            }
        }
        if ($basePath !== '' && str_starts_with($returnUrl, '/') && !str_starts_with($returnUrl, $basePath)) {
            $returnUrl = $basePath . $returnUrl;
        }

        $message = isset($_SESSION['message']) && is_string($_SESSION['message']) ? $_SESSION['message'] : '';
        $error = isset($_SESSION['error']) && is_string($_SESSION['error']) ? $_SESSION['error'] : '';
        $fieldErrors = function_exists('consume_field_errors') ? consume_field_errors() : [];
        if ($fieldErrors === [] && isset($_SESSION['field_errors']) && is_array($_SESSION['field_errors'])) {
            $fieldErrors = $_SESSION['field_errors'];
            unset($_SESSION['field_errors']);
        }
        unset($_SESSION['message'], $_SESSION['error']);

        $systemName = function_exists('get_system_name') ? get_system_name($this->pdo) : 'pRD';

        require_once __DIR__ . '/../Views/user/edit_record.php';
    }
}
