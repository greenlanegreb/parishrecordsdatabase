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
            try {
                $this->pdo->prepare('DELETE FROM map_pins WHERE record_id = ?')->execute([$recordId]);
            } catch (\Throwable $e) {
            }
            try {
                $this->pdo->prepare('DELETE FROM edit_suggestions WHERE record_id = ?')->execute([$recordId]);
            } catch (\Throwable $e) {
            }
            try {
                $this->pdo->prepare(
                    'DELETE FROM duplicate_reviews WHERE record_a_id = ? OR record_b_id = ? OR merge_kept_id = ?'
                )->execute([$recordId, $recordId, $recordId]);
            } catch (\Throwable $e) {
            }
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
            'table_id' => $tableId,
            'record_id' => $recordId,
            'created_by' => isset($row['created_by']) ? (int) $row['created_by'] : 0,
        ];
    }

    /**
     * Replace field values for one record. Values must already be validated by the controller
     * (same rules as data entry). Syncs map_pins for LOCATION columns.
     *
     * @param array<int, string> $cleanValues column_id => value_content (empty string = clear)
     * @param array<int, array<string, mixed>> $colsMap
     */
    public function updateRecordValues(
        int $recordId,
        int $tableId,
        array $cleanValues,
        array $colsMap,
        int $actorUserId,
        string $remoteAddr
    ): void {
        if ($recordId < 1 || $tableId < 1) {
            throw new Exception('Invalid record.');
        }

        $nameStmt = $this->pdo->prepare('SELECT table_name FROM dynamic_tables WHERE id = ?');
        $nameStmt->execute([$tableId]);
        $tableName = (string) ($nameStmt->fetchColumn() ?: $tableId);

        $this->pdo->beginTransaction();
        try {
            $del = $this->pdo->prepare('DELETE FROM record_values WHERE record_id = ? AND column_id = ?');
            $ins = $this->pdo->prepare(
                'INSERT INTO record_values (record_id, column_id, value_content) VALUES (?, ?, ?)'
            );

            foreach ($cleanValues as $colId => $val) {
                $colId = (int) $colId;
                $del->execute([$recordId, $colId]);
                $content = is_string($val) ? $val : '';
                if ($content !== '') {
                    $ins->execute([$recordId, $colId, $content]);
                }

                $type = isset($colsMap[$colId]['data_type']) ? (string) $colsMap[$colId]['data_type'] : '';
                if ($type === 'LOCATION') {
                    try {
                        if ($content === '') {
                            LocationValueService::deletePin($this->pdo, $recordId, $colId);
                        } else {
                            $pin = LocationValueService::decode($content);
                            if ($pin !== null && LocationValueService::isComplete($pin)) {
                                LocationValueService::upsertPin($this->pdo, $tableId, $recordId, $colId, $pin);
                            }
                        }
                    } catch (\Throwable $e) {
                    }
                }
            }

            $audit = $this->pdo->prepare(
                'INSERT INTO audit_logs (user_id, action, details, ip_address, created_at)
                 VALUES (?, ?, ?, ?, NOW())'
            );
            $audit->execute([
                $actorUserId,
                'EDIT_RECORD',
                "Direct-edited record #{$recordId} (table {$tableId}: {$tableName})",
                $remoteAddr,
            ]);

            $this->pdo->commit();
        } catch (\Throwable $e) {
            if ($this->pdo->inTransaction()) {
                $this->pdo->rollBack();
            }
            throw new Exception('Could not save the record: ' . $e->getMessage());
        }
    }
}
