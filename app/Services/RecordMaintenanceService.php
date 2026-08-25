<?php
declare(strict_types=1);

namespace App\Services;

use Exception;
use PDO;

/**
 * Admin record maintenance: delete + direct edit.
 * Independent of the moderation module.
 */
class RecordMaintenanceService
{
    private PDO $pdo;

    public function __construct(PDO $pdo)
    {
        $this->pdo = $pdo;
    }

    /**
     * Hard-delete one record and dependent rows. Audit is caller's job after success.
     *
     * @return array{table_id: int, record_id: int, created_by: int}
     */
    public function deleteRecord(int $recordId): array
    {
        if ($recordId < 1) {
            throw new Exception('Invalid record.');
        }
        $stmt = $this->pdo->prepare('SELECT id, table_id, created_by FROM records WHERE id = ?');
        $stmt->execute([$recordId]);
        $row = $stmt->fetch(PDO::FETCH_ASSOC);
        if ($row === false) {
            throw new Exception('That record was not found (it may already have been deleted).');
        }
        $tableId = (int) ($row['table_id'] ?? 0);
        $this->pdo->beginTransaction();
        try {
            // Map pins for this record (maps module)
            try {
                $this->pdo->prepare('DELETE FROM map_pins WHERE record_id = ?')->execute([$recordId]);
            } catch (\Throwable $e) {
                // Table may not exist on older installs
            }
            // Pending / historical suggestions for this record
            try {
                $this->pdo->prepare('DELETE FROM edit_suggestions WHERE record_id = ?')->execute([$recordId]);
            } catch (\Throwable $e) {
            }
            // Duplicate review queue rows that mention this record
            try {
                $this->pdo->prepare(
                    'DELETE FROM duplicate_reviews WHERE record_a_id = ? OR record_b_id = ? OR merge_kept_id = ?'
                )->execute([$recordId, $recordId, $recordId]);
            } catch (\Throwable $e) {
            }
            // Values (also cascade if FK exists)
            $this->pdo->prepare('DELETE FROM record_values WHERE record_id = ?')->execute([$recordId]);
            $this->pdo->prepare('DELETE FROM records WHERE id = ?')->execute([$recordId]);
            $this->pdo->commit();
        } catch (\Throwable $e) {
            if ($this->pdo->inTransaction()) {
                $this->pdo->rollBack();
            }
            throw new Exception('Could not delete the record: ' . $e->getMessage());
        }
        return [
            'table_id'   => $tableId,
            'record_id'  => $recordId,
            'created_by' => isset($row['created_by']) ? (int) $row['created_by'] : 0,
        ];
    }

    /**
     * Direct edit of an existing record (permission: edit_records).
     * Updates record_values only. Writes EDIT_RECORD audit.
     * Does not change points. Does not touch the suggestion queue.
     *
     * @param array{id: int|string, username?: string} $actor
     * @param array<string, mixed> $post  Raw $_POST (expects fields[column_id] = value)
     * @throws Exception
     */
    public function updateRecord(int $recordId, array $post, array $actor): void
    {
        $remoteAddr = isset($_SERVER['REMOTE_ADDR']) && is_string($_SERVER['REMOTE_ADDR'])
            ? $_SERVER['REMOTE_ADDR'] : '127.0.0.1';

        if ($recordId < 1) {
            throw new Exception('Invalid record.');
        }

        $recStmt = $this->pdo->prepare("
            SELECT r.id, r.table_id, r.created_by, t.table_name
            FROM records r
            JOIN dynamic_tables t ON t.id = r.table_id
            WHERE r.id = ?
        ");
        $recStmt->execute([$recordId]);
        $record = $recStmt->fetch(PDO::FETCH_ASSOC);
        if ($record === false) {
            throw new Exception('Record not found.');
        }
        $tableId   = (int) $record['table_id'];
        $tableName = (string) ($record['table_name'] ?? '');

        $colsStmt = $this->pdo->prepare("
            SELECT * FROM table_columns
            WHERE table_id = ?
            ORDER BY sort_order ASC, id ASC
        ");
        $colsStmt->execute([$tableId]);
        $columns = $colsStmt->fetchAll(PDO::FETCH_ASSOC) ?: [];
        if ($columns === []) {
            throw new Exception('No columns defined for this table.');
        }

        $fields = isset($post['fields']) && is_array($post['fields']) ? $post['fields'] : [];

        $errors = [];
        $cleanValues = []; // column_id => string|null

        foreach ($columns as $col) {
            $colId    = (int) ($col['id'] ?? 0);
            $colName  = (string) ($col['column_name'] ?? 'Field');
            $type     = strtoupper((string) ($col['data_type'] ?? 'TEXT'));
            $required = !empty($col['is_required']);
            $rawIn = $fields[$colId] ?? null;

            // Location: keep structured array (do not implode as multi-select)
            if ($type === 'LOCATION') {
                if (is_array($rawIn)) {
                    $locData = \App\Services\LocationValueService::fromPosted($rawIn);
                    if ($locData === null) {
                        if ($required) {
                            $errors[] = "The field '{$colName}' is required.";
                        }
                        $cleanValues[$colId] = null;
                        continue;
                    }
                    if (!\App\Services\LocationValueService::isComplete($locData)) {
                        $errors[] = "Choose a place and add a title and short text for '{$colName}'.";
                        continue;
                    }
                    $cleanValues[$colId] = \App\Services\LocationValueService::encode($locData);
                    continue;
                }
                // Plain string (legacy / incomplete UI): store as text only if non-empty
                if (is_string($rawIn) && trim($rawIn) !== '') {
                    $cleanValues[$colId] = function_exists('sanitize_incoming_text')
                        ? sanitize_incoming_text(trim($rawIn))
                        : trim($rawIn);
                    continue;
                }
                if ($required) {
                    $errors[] = "The field '{$colName}' is required.";
                }
                $cleanValues[$colId] = null;
                continue;
            }

            $raw = $rawIn;

            // Normalise multi-select arrays → comma string or null
            if (is_array($raw)) {
                $raw = array_filter(array_map(static function ($v) {
                    return is_string($v) ? trim($v) : '';
                }, $raw), static fn($v) => $v !== '');
                $raw = $raw === [] ? null : implode(', ', $raw);
            } elseif (is_string($raw)) {
                $raw = trim($raw);
                if ($raw === '') {
                    $raw = null;
                }
            } else {
                $raw = null;
            }

            if ($required && $raw === null) {
                $errors[] = "The field '{$colName}' is required.";
                continue;
            }

            if ($raw === null) {
                $cleanValues[$colId] = null;
                continue;
            }

            if ($type === 'INT') {
                if (filter_var($raw, FILTER_VALIDATE_INT) === false) {
                    $errors[] = "'{$colName}' must be a whole number.";
                    continue;
                }
                $intVal = (int) $raw;
                if (isset($col['min_value']) && $col['min_value'] !== '' && $col['min_value'] !== null) {
                    if ($intVal < (int) $col['min_value']) {
                        $errors[] = "'{$colName}' must be at least " . $col['min_value'] . '.';
                        continue;
                    }
                }
                if (isset($col['max_value']) && $col['max_value'] !== '' && $col['max_value'] !== null) {
                    if ($intVal > (int) $col['max_value']) {
                        $errors[] = "'{$colName}' must be at most " . $col['max_value'] . '.';
                        continue;
                    }
                }
                $cleanValues[$colId] = (string) $intVal;
            } elseif ($type === 'BOOLEAN') {
                $cleanValues[$colId] = in_array($raw, ['1', '0', 1, 0, true, false], true)
                    ? ((string) ((int) (bool) $raw === 1 || $raw === '1' || $raw === 1 ? 1 : 0))
                    : '0';
            } elseif ($type === 'SELECT') {
                $opts = array_filter(array_map('trim', preg_split('/\r\n|\r|\n/', (string) ($col['field_options'] ?? ''))));
                $chosen = array_map('trim', explode(',', $raw));
                $ok = true;
                foreach ($chosen as $c) {
                    if ($c !== '' && !in_array($c, $opts, true)) {
                        $ok = false;
                        break;
                    }
                }
                if (!$ok) {
                    $errors[] = "'{$colName}' contains an invalid choice.";
                    continue;
                }
                $cleanValues[$colId] = function_exists('sanitize_incoming_text')
                    ? sanitize_incoming_text($raw)
                    : $raw;
            } else {
                // TEXT, DATE, LOCATION, etc.
                $cleanValues[$colId] = function_exists('sanitize_incoming_text')
                    ? sanitize_incoming_text($raw)
                    : $raw;
            }
        }

        if ($errors !== []) {
            throw new Exception(implode(' ', $errors));
        }

        $this->pdo->beginTransaction();
        try {
            $del = $this->pdo->prepare('DELETE FROM record_values WHERE record_id = ? AND column_id = ?');
            $ins = $this->pdo->prepare('INSERT INTO record_values (record_id, column_id, value_content) VALUES (?, ?, ?)');

            foreach ($cleanValues as $colId => $val) {
                $del->execute([$recordId, $colId]);
                if ($val !== null && $val !== '') {
                    $ins->execute([$recordId, $colId, $val]);
                }
            }

            foreach ($columns as $col) {
                if (strtoupper((string) ($col['data_type'] ?? '')) !== 'LOCATION') {
                    continue;
                }
                $cid = (int) ($col['id'] ?? 0);
                $stored = $cleanValues[$cid] ?? null;
                \App\Services\LocationValueService::syncPinFromStoredValue(
                    $this->pdo,
                    $tableId,
                    $recordId,
                    $cid,
                    is_string($stored) ? $stored : null
                );
            }

            $audit = $this->pdo->prepare("
                INSERT INTO audit_logs (user_id, action, details, ip_address, created_at)
                VALUES (?, 'EDIT_RECORD', ?, ?, NOW())
            ");
            $audit->execute([
                (int) $actor['id'],
                "Direct-edited record #{$recordId} (table {$tableId}: {$tableName})",
                $remoteAddr,
            ]);

            $this->pdo->commit();
        } catch (Exception $e) {
            if ($this->pdo->inTransaction()) {
                $this->pdo->rollBack();
            }
            throw $e;
        }
    }
}
