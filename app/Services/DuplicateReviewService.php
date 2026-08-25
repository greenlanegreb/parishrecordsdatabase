<?php
declare(strict_types=1);

namespace App\Services;

use Exception;
use PDO;

class DuplicateReviewService
{
    private PDO $pdo;
    private DuplicateCheckService $checker;

    public function __construct(PDO $pdo)
    {
        $this->pdo = $pdo;
        $this->checker = new DuplicateCheckService($pdo);
    }

    /**
     * Compare existing rows in one table and queue new pending pairs.
     *
     * @return array{found: int, queued: int}
     */
    public function scanTable(int $tableId, string $picky = 'similar'): array
    {
        $colsStmt = $this->pdo->prepare('SELECT * FROM table_columns WHERE table_id = ?');
        $colsStmt->execute([$tableId]);
        $colsMap = [];
        foreach ($colsStmt->fetchAll(PDO::FETCH_ASSOC) as $col) {
            $cid = isset($col['id']) ? (int) $col['id'] : 0;
            if ($cid > 0) {
                $colsMap[$cid] = $col;
            }
        }
        if ($colsMap === []) {
            return ['found' => 0, 'queued' => 0];
        }

        $recStmt = $this->pdo->prepare('SELECT id FROM records WHERE table_id = ? ORDER BY id ASC');
        $recStmt->execute([$tableId]);
        $ids = array_map('intval', $recStmt->fetchAll(PDO::FETCH_COLUMN));
        if (count($ids) < 2) {
            return ['found' => 0, 'queued' => 0];
        }

        $placeholders = implode(',', array_fill(0, count($ids), '?'));
        $valStmt = $this->pdo->prepare(
            "SELECT record_id, column_id, value_content FROM record_values WHERE record_id IN ({$placeholders})"
        );
        $valStmt->execute($ids);
        /** @var array<int, array<int, string>> $byRecord */
        $byRecord = [];
        while ($row = $valStmt->fetch(PDO::FETCH_ASSOC)) {
            $rid = (int) $row['record_id'];
            $cid = (int) $row['column_id'];
            $byRecord[$rid][$cid] = isset($row['value_content']) && is_string($row['value_content'])
                ? $row['value_content'] : '';
        }

        $ins = $this->pdo->prepare(
            'INSERT IGNORE INTO duplicate_reviews (table_id, record_a_id, record_b_id, score_percent, status)
             VALUES (?, ?, ?, ?, \'pending\')'
        );

        $found = 0;
        $queued = 0;
        $count = count($ids);
        for ($i = 0; $i < $count; $i++) {
            $a = $ids[$i];
            $valuesA = $byRecord[$a] ?? [];
            if ($valuesA === []) {
                continue;
            }
            $matches = $this->checker->findMatches($tableId, $valuesA, $colsMap, $picky, $a);
            foreach ($matches as $match) {
                $b = isset($match['id']) ? (int) $match['id'] : 0;
                if ($b <= $a) {
                    continue;
                }
                $found++;
                $score = isset($match['percent']) ? (int) $match['percent'] : 0;
                $ins->execute([$tableId, $a, $b, $score]);
                if ($ins->rowCount() > 0) {
                    $queued++;
                }
            }
        }

        return ['found' => $found, 'queued' => $queued];
    }

    /**
     * @return list<array<string, mixed>>
     */
    public function pendingQueue(): array
    {
        $stmt = $this->pdo->query(
            "SELECT dr.*, dt.table_name
             FROM duplicate_reviews dr
             LEFT JOIN dynamic_tables dt ON dt.id = dr.table_id
             WHERE dr.status = 'pending'
             ORDER BY dr.score_percent DESC, dr.id DESC"
        );
        return $stmt !== false ? $stmt->fetchAll(PDO::FETCH_ASSOC) : [];
    }

    /**
     * @return array<string, mixed>|null
     */
    public function findReview(int $id): ?array
    {
        $stmt = $this->pdo->prepare(
            'SELECT dr.*, dt.table_name
             FROM duplicate_reviews dr
             LEFT JOIN dynamic_tables dt ON dt.id = dr.table_id
             WHERE dr.id = ?'
        );
        $stmt->execute([$id]);
        $row = $stmt->fetch(PDO::FETCH_ASSOC);
        return $row !== false ? $row : null;
    }

    public function dismiss(int $id, int $userId): void
    {
        $stmt = $this->pdo->prepare(
            "UPDATE duplicate_reviews
             SET status = 'dismissed', reviewed_by = ?, reviewed_at = NOW()
             WHERE id = ? AND status = 'pending'"
        );
        $stmt->execute([$userId, $id]);
    }

    /**
     * Keep one record, copy chosen field values onto it, remove the other, write record history.
     *
     * @param array<int, string> $keepByColumn  column_id => 'a'|'b'
     */
    public function merge(int $reviewId, int $keepRecordId, array $keepByColumn, array $currentUser, string $ip): void
    {
        $review = $this->findReview($reviewId);
        if ($review === null || ($review['status'] ?? '') !== 'pending') {
            throw new Exception('That review is no longer waiting.');
        }
        $a = (int) $review['record_a_id'];
        $b = (int) $review['record_b_id'];
        if ($keepRecordId !== $a && $keepRecordId !== $b) {
            throw new Exception('Please choose which record to keep.');
        }
        $dropId = $keepRecordId === $a ? $b : $a;

        $colsStmt = $this->pdo->prepare('SELECT id, column_name FROM table_columns WHERE table_id = ?');
        $colsStmt->execute([(int) $review['table_id']]);
        $colNames = [];
        foreach ($colsStmt->fetchAll(PDO::FETCH_ASSOC) as $col) {
            $colNames[(int) $col['id']] = (string) $col['column_name'];
        }

        $load = $this->pdo->prepare('SELECT column_id, value_content FROM record_values WHERE record_id = ?');
        $vals = [];
        foreach ([$a, $b] as $rid) {
            $load->execute([$rid]);
            while ($row = $load->fetch(PDO::FETCH_ASSOC)) {
                $vals[$rid][(int) $row['column_id']] = (string) ($row['value_content'] ?? '');
            }
        }

        $this->pdo->beginTransaction();
        try {
            $checkVal = $this->pdo->prepare('SELECT id FROM record_values WHERE record_id = ? AND column_id = ?');
            $updVal = $this->pdo->prepare('UPDATE record_values SET value_content = ? WHERE record_id = ? AND column_id = ?');
            $insVal = $this->pdo->prepare('INSERT INTO record_values (record_id, column_id, value_content) VALUES (?, ?, ?)');
            $summary = [];
            foreach ($colNames as $cid => $name) {
                $side = isset($keepByColumn[$cid]) && $keepByColumn[$cid] === 'b' ? 'b' : 'a';
                $sourceId = $side === 'a' ? $a : $b;
                $value = $vals[$sourceId][$cid] ?? '';
                $checkVal->execute([$keepRecordId, $cid]);
                if ($checkVal->fetch()) {
                    $updVal->execute([$value, $keepRecordId, $cid]);
                } else {
                    $insVal->execute([$keepRecordId, $cid, $value]);
                }
                $summary[] = $name . ': ' . ($value !== '' ? $value : '(empty)');
            }

            $this->pdo->prepare('DELETE FROM record_values WHERE record_id = ?')->execute([$dropId]);
            $this->pdo->prepare('DELETE FROM edit_suggestions WHERE record_id = ?')->execute([$dropId]);
            $this->pdo->prepare('DELETE FROM records WHERE id = ?')->execute([$dropId]);

            $this->pdo->prepare(
                "UPDATE duplicate_reviews
                 SET status = 'merged', reviewed_by = ?, reviewed_at = NOW(), merge_kept_id = ?
                 WHERE id = ?"
            )->execute([(int) $currentUser['id'], $keepRecordId, $reviewId]);

            $this->pdo->prepare(
                "UPDATE duplicate_reviews
                 SET status = 'dismissed', reviewed_by = ?, reviewed_at = NOW()
                 WHERE status = 'pending' AND (record_a_id = ? OR record_b_id = ?)"
            )->execute([(int) $currentUser['id'], $dropId, $dropId]);

            $details = 'Merged record #' . $dropId . ' into #' . $keepRecordId . '. Kept values: ' . implode('; ', $summary);
            $hist = $this->pdo->prepare(
                'INSERT INTO audit_logs (user_id, action, record_id, details, ip_address)
                 VALUES (?, ?, ?, ?, ?)'
            );
            $hist->execute([
                (int) $currentUser['id'],
                'MERGE_RECORD',
                $keepRecordId,
                $details,
                $ip,
            ]);

            $this->pdo->commit();
        } catch (Exception $e) {
            $this->pdo->rollBack();
            throw $e;
        }
    }

    /**
     * @return list<array{id: int, column_name: string, value_a: string, value_b: string}>
     */
    public function compareValues(int $tableId, int $recordA, int $recordB): array
    {
        if ($tableId < 1 || $recordA < 1 || $recordB < 1) {
            return [];
        }

        // Only core columns — avoid failing on installs missing optional column attributes
        // boolean_display_format drives Male/Female vs Yes/No etc (same as data entry)
        $colsStmt = $this->pdo->prepare(
            'SELECT id, column_name, data_type, boolean_display_format
             FROM table_columns
             WHERE table_id = ?
             ORDER BY id ASC'
        );
        $colsStmt->execute([$tableId]);
        $cols = $colsStmt->fetchAll(PDO::FETCH_ASSOC);
        if (!is_array($cols) || $cols === []) {
            return [];
        }

        $colIds = [];
        foreach ($cols as $col) {
            $colIds[] = (int) $col['id'];
        }
        $placeholders = implode(',', array_fill(0, count($colIds), '?'));

        $sql = 'SELECT record_id, column_id, value_content
                FROM record_values
                WHERE record_id IN (?, ?)
                  AND column_id IN (' . $placeholders . ')';
        $valStmt = $this->pdo->prepare($sql);
        $params = array_merge([$recordA, $recordB], $colIds);
        $valStmt->execute($params);

        $byRecord = [
            $recordA => [],
            $recordB => [],
        ];
        $rows = $valStmt->fetchAll(PDO::FETCH_ASSOC);
        if (is_array($rows)) {
            foreach ($rows as $vr) {
                $rid = (int) ($vr['record_id'] ?? 0);
                $cid = (int) ($vr['column_id'] ?? 0);
                if ($rid !== $recordA && $rid !== $recordB) {
                    continue;
                }
                // Preserve "0"; only missing/null becomes empty string
                if (!array_key_exists('value_content', $vr) || $vr['value_content'] === null) {
                    $byRecord[$rid][$cid] = '';
                } else {
                    $byRecord[$rid][$cid] = (string) $vr['value_content'];
                }
            }
        }

        $out = [];
        foreach ($cols as $col) {
            $cid = (int) $col['id'];
            $a = $byRecord[$recordA][$cid] ?? '';
            $b = $byRecord[$recordB][$cid] ?? '';
            $dataType = isset($col['data_type']) && is_string($col['data_type'])
                ? strtoupper(trim($col['data_type']))
                : 'VARCHAR';
            $boolFmt = isset($col['boolean_display_format']) && is_string($col['boolean_display_format'])
                && $col['boolean_display_format'] !== ''
                ? $col['boolean_display_format']
                : 'yes_no';
            $out[] = [
                'id' => $cid,
                'column_name' => (string) ($col['column_name'] ?? ''),
                'data_type' => $dataType,
                'value_a' => $a,
                'value_b' => $b,
                'display_a' => $this->formatCompareValue($a, $dataType, $boolFmt),
                'display_b' => $this->formatCompareValue($b, $dataType, $boolFmt),
            ];
        }
        return $out;
    }

    /**
     * Soft display helper — must never throw (queue falls back to record numbers if compare fails).
     */
    private function formatCompareValue(string $raw, string $dataType, string $boolFormat = 'yes_no'): string
    {
        if ($raw === '') {
            return '';
        }
        try {
            if ($dataType === 'BOOLEAN' && function_exists('format_boolean_value')) {
                $formatted = format_boolean_value($raw, $boolFormat !== '' ? $boolFormat : 'yes_no');
                if (is_string($formatted) && $formatted !== '') {
                    return $formatted;
                }
            }
            if ($dataType === 'DATE' && function_exists('format_display_date')) {
                $formatted = format_display_date($raw, null);
                if (is_string($formatted) && $formatted !== '') {
                    return $formatted;
                }
            }
            if (($dataType === 'LOCATION' || ($raw[0] ?? '') === '{') && str_starts_with(ltrim($raw), '{')) {
                $d = json_decode($raw, true);
                if (is_array($d)) {
                    $title = trim((string) ($d['title'] ?? ''));
                    $label = trim((string) ($d['label'] ?? $d['place'] ?? ''));
                    $joined = trim($title . ($title !== '' && $label !== '' ? ' — ' : '') . $label);
                    if ($joined !== '') {
                        return $joined;
                    }
                }
            }
        } catch (\Throwable $e) {
            // fall through to raw
        }
        return $raw;
    }

    public function enrichQueueRow(array $row): array
    {
        $tableId = isset($row['table_id']) ? (int) $row['table_id'] : 0;
        $a = isset($row['record_a_id']) ? (int) $row['record_a_id'] : 0;
        $b = isset($row['record_b_id']) ? (int) $row['record_b_id'] : 0;
        if ($tableId < 1 || $a < 1 || $b < 1) {
            $row['field_compare'] = [];
            return $row;
        }
        try {
            $row['field_compare'] = $this->compareValues($tableId, $a, $b);
        } catch (\Throwable $e) {
            // Still show column headers if possible is better than silent empty —
            // but never break the queue page
            $row['field_compare'] = [];
            if (function_exists('error_log')) {
                @error_log('DuplicateReviewService::enrichQueueRow: ' . $e->getMessage());
            }
        }
        return $row;
    }

}
