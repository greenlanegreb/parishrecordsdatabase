<?php
declare(strict_types=1);

/**
 * Sort record rows the same way catalogue search does.
 *
 * @param array<int, array<string, mixed>> $records
 * @param array<int|string, array<int|string, string>> $recordValues
 * @return array<int, array<string, mixed>>
 */
function prd_sort_record_rows(array $records, array $recordValues, string $sortCol, string $sortDir): array
{
    $dir = strtoupper($sortDir) === 'ASC' ? 'ASC' : 'DESC';
    $value = static function (array $rec) use ($sortCol, $recordValues): string {
        $rid = (int) ($rec['id'] ?? 0);
        if ($sortCol === 'date' || $sortCol === 'created_at') {
            return (string) ($rec['created_at'] ?? '');
        }
        if ($sortCol === 'created_by') {
            $name = trim((string) ($rec['first_name'] ?? '') . ' ' . (string) ($rec['surname'] ?? ''));
            return strtolower($name !== '' ? $name : (string) ($rec['username'] ?? ''));
        }
        if (str_starts_with($sortCol, 'col_')) {
            $cid = (int) substr($sortCol, 4);
            $raw = '';
            if (isset($recordValues[$rid][$cid]) && is_string($recordValues[$rid][$cid])) {
                $raw = $recordValues[$rid][$cid];
            } elseif (isset($recordValues[(string) $rid][$cid]) && is_string($recordValues[(string) $rid][$cid])) {
                $raw = $recordValues[(string) $rid][$cid];
            }
            $trim = trim($raw);
            if ($trim !== '' && isset($trim[0]) && $trim[0] === '{' && class_exists(\App\Services\LocationValueService::class)) {
                $raw = \App\Services\LocationValueService::formatDisplay($trim);
            }
            return strtolower($raw);
        }
        return str_pad((string) $rid, 12, '0', STR_PAD_LEFT);
    };
    usort($records, static function (array $a, array $b) use ($value, $dir): int {
        $cmp = strnatcasecmp($value($a), $value($b));
        return $dir === 'DESC' ? -$cmp : $cmp;
    });
    return $records;
}
