<?php
declare(strict_types=1);

namespace App\Services;

use PDO;

/**
 * Same-table duplicate check: score the whole filled row, warn + override.
 */
class DuplicateCheckService
{
    private const MIN_PERCENT = 25;
    private const MAX_CARDS = 8;

    private PDO $pdo;

    public function __construct(PDO $pdo)
    {
        $this->pdo = $pdo;
    }

    /**
     * @param array<int|string, string> $valuesByColId
     * @param array<int, array<string, mixed>> $colsMap
     * @return list<array<string, mixed>>
     */
    public function findMatches(int $tableId, array $valuesByColId, array $colsMap, string $picky = 'similar', int $excludeRecordId = 0): array
    {
        $picky = $picky === 'exact' ? 'exact' : 'similar';
        $criteria = $this->filledCriteria($valuesByColId, $colsMap);
        if ($criteria === []) {
            return [];
        }

        $recStmt = $this->pdo->prepare(
            'SELECT r.id, u.username
             FROM records r
             LEFT JOIN users u ON r.created_by = u.id
             WHERE r.table_id = ?'
        );
        $recStmt->execute([$tableId]);
        /** @var list<array<string, mixed>> $records */
        $records = $recStmt->fetchAll(PDO::FETCH_ASSOC);
        if ($records === []) {
            return [];
        }

        $ids = [];
        foreach ($records as $row) {
            $ids[] = (int) $row['id'];
        }
        $placeholders = implode(',', array_fill(0, count($ids), '?'));
        $valStmt = $this->pdo->prepare(
            "SELECT record_id, column_id, value_content
             FROM record_values
             WHERE record_id IN ({$placeholders})"
        );
        $valStmt->execute($ids);
        /** @var array<int, array<int, string>> $byRecord */
        $byRecord = [];
        while ($row = $valStmt->fetch(PDO::FETCH_ASSOC)) {
            $rid = isset($row['record_id']) ? (int) $row['record_id'] : 0;
            $cid = isset($row['column_id']) ? (int) $row['column_id'] : 0;
            $byRecord[$rid][$cid] = isset($row['value_content']) && is_string($row['value_content'])
                ? $row['value_content'] : '';
        }

        $matches = [];
        foreach ($records as $row) {
            $rid = isset($row['id']) ? (int) $row['id'] : 0;
            if ($excludeRecordId > 0 && $rid === $excludeRecordId) {
                continue;
            }
            $stored = $byRecord[$rid] ?? [];
            $scored = $this->scoreRecord($stored, $criteria, $colsMap, $picky);
            if ($scored['percent'] < self::MIN_PERCENT) {
                continue;
            }
            $scored['id'] = $rid;
            $scored['username'] = isset($row['username']) && is_string($row['username']) ? $row['username'] : '';
            $scored['value_content'] = $scored['overview'][0]['value'] ?? '';
            $matches[] = $scored;
        }

        usort($matches, static function (array $a, array $b): int {
            return ($b['percent'] <=> $a['percent']) ?: ($a['id'] <=> $b['id']);
        });

        return array_slice($matches, 0, self::MAX_CARDS);
    }

    /**
     * @param array<int|string, string> $valuesByColId
     * @param array<int, array<string, mixed>> $colsMap
     * @return array<int, array{type: string, value: string, label: string}>
     */
    private function filledCriteria(array $valuesByColId, array $colsMap): array
    {
        $filled = [];
        foreach ($valuesByColId as $cid => $raw) {
            $cid = (int) $cid;
            if (!isset($colsMap[$cid])) {
                continue;
            }
            $type = isset($colsMap[$cid]['data_type']) && is_string($colsMap[$cid]['data_type'])
                ? $colsMap[$cid]['data_type'] : 'VARCHAR';
            if (!in_array($type, ['VARCHAR', 'TEXT', 'SELECT', 'INT', 'DATE'], true)) {
                continue;
            }
            $norm = $this->normalize($raw, $type);
            if ($norm === '') {
                continue;
            }
            $label = isset($colsMap[$cid]['column_name']) && is_string($colsMap[$cid]['column_name'])
                ? $colsMap[$cid]['column_name'] : 'Field';
            $filled[$cid] = ['type' => $type, 'value' => $norm, 'label' => $label];
        }
        return $filled;
    }

    /**
     * @param array<int, string> $stored
     * @param array<int, array{type: string, value: string, label: string}> $criteria
     * @param array<int, array<string, mixed>> $colsMap
     * @return array{percent: int, bucket: int, overview: list<array<string, mixed>>}
     */
    private function scoreRecord(array $stored, array $criteria, array $colsMap, string $picky): array
    {
        $points = 0.0;
        $overview = [];
        foreach ($criteria as $cid => $item) {
            $haveRaw = $stored[$cid] ?? '';
            $have = $this->normalize($haveRaw, $item['type']);
            $fieldScore = $this->fieldScore($item['value'], $have, $item['type'], $picky);
            $points += $fieldScore;
            $overview[] = [
                'label' => $item['label'],
                'value' => is_string($haveRaw) ? $haveRaw : '',
                'close' => $fieldScore >= 0.7 && $fieldScore < 1.0,
            ];
        }
        $rawPct = (int) round(100 * ($points / max(count($criteria), 1)));
        if ($rawPct > 100) {
            $rawPct = 100;
        }
        $bucket = 25 * (int) round($rawPct / 25);
        if ($bucket < 25 && $rawPct >= self::MIN_PERCENT) {
            $bucket = 25;
        }
        if ($bucket > 100) {
            $bucket = 100;
        }
        return [
            'percent' => $rawPct,
            'bucket' => $bucket,
            'overview' => $overview,
        ];
    }

    private function fieldScore(string $newVal, string $oldVal, string $type, string $picky): float
    {
        if ($newVal === '' || $oldVal === '') {
            return 0.0;
        }
        if ($newVal === $oldVal) {
            return 1.0;
        }
        if ($picky === 'exact') {
            return 0.0;
        }
        if ($type === 'INT' || $type === 'DATE') {
            return 0.0;
        }
        if ($type === 'SELECT') {
            $a = array_filter(explode(',', $newVal));
            $b = array_filter(explode(',', $oldVal));
            if ($a === [] || $b === []) {
                return 0.0;
            }
            $overlap = count(array_intersect($a, $b));
            $union = count(array_unique(array_merge($a, $b)));
            return $union > 0 ? $overlap / $union : 0.0;
        }
        $len = max(strlen($newVal), strlen($oldVal));
        if ($len <= 255) {
            $dist = levenshtein($newVal, $oldVal);
            if ($dist >= 0 && $dist <= 2 && $len >= 4) {
                return 0.85;
            }
        }
        $pct = 0.0;
        similar_text($newVal, $oldVal, $pct);
        if ($pct >= 80.0) {
            return 0.75;
        }
        return 0.0;
    }

    private function normalize(string $value, string $type): string
    {
        $value = trim($value);
        if ($value === '') {
            return '';
        }
        if ($type === 'SELECT') {
            $parts = array_map('trim', explode(',', $value));
            $parts = array_values(array_filter($parts, static fn(string $p): bool => $p !== ''));
            $lower = array_map(static fn(string $p): string => function_exists('mb_strtolower')
                ? mb_strtolower($p, 'UTF-8') : strtolower($p), $parts);
            sort($lower, SORT_STRING);
            return implode(',', $lower);
        }
        if ($type === 'INT') {
            return preg_match('/^-?\d+$/', $value) === 1 ? (string) (int) $value : $value;
        }
        $collapsed = preg_replace('/\s+/u', ' ', $value);
        $value = is_string($collapsed) ? $collapsed : $value;
        return function_exists('mb_strtolower') ? mb_strtolower($value, 'UTF-8') : strtolower($value);
    }
}
