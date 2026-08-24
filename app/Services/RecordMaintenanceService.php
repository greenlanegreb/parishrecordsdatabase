<?php
declare(strict_types=1);

namespace App\Services;

use Exception;
use PDO;

/**
 * Admin record maintenance: delete (edit/merge later).
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
            'table_id' => $tableId,
            'record_id' => $recordId,
            'created_by' => isset($row['created_by']) ? (int) $row['created_by'] : 0,
        ];
    }
}
