<?php
declare(strict_types=1);

namespace App\Services;

use Carbon\CarbonImmutable;
use DateTimeImmutable;
use PDO;
use Throwable;

class DateSearchService
{
    /**
     * Main entry point used by record_matches_filters() and all search/export paths.
     */
    public static function recordMatchesFilters(
        $recordId,
        array $recordValuesMap,
        array $searchFilters,
        array $dateFilters
    ): bool {
        $prefs = self::getUserDatePrefs();
        $userDateFormat = $prefs['date_format'];
        $userTimezone   = $prefs['timezone'];

        // ---------- Text / general search filters ----------
        if (!empty($searchFilters)) {
            foreach ($searchFilters as $colId => $searchTerm) {
                if (!is_string($searchTerm) || trim($searchTerm) === '') {
                    continue;
                }

                $cellVal = trim($recordValuesMap[$recordId][$colId] ?? '');
                $term    = trim($searchTerm);

                // Detect pure date or datetime cells
                $isDateOnly   = (bool) preg_match('/^\d{4}-\d{2}-\d{2}$/', $cellVal);
                $isDateTime   = (bool) preg_match('/^\d{4}-\d{2}-\d{2}[ T]\d{2}:\d{2}/', $cellVal);

                if ($isDateOnly || $isDateTime) {
                    if (!self::dateCellMatchesSearch($cellVal, $term, $userDateFormat, $userTimezone, $isDateTime)) {
                        return false;
                    }
                    continue;
                }

                // Ordinary text match
                if (stripos($cellVal, $term) === false) {
                    return false;
                }
            }
        }

        // ---------- Date-range filters (the two boxes) ----------
        if (!empty($dateFilters)) {
            foreach ($dateFilters as $colId => $range) {
                if (!is_array($range)) {
                    continue;
                }

                $fromInput = isset($range['from']) && is_string($range['from']) ? trim($range['from']) : '';
                $toInput   = isset($range['to'])   && is_string($range['to'])   ? trim($range['to'])   : '';
                $cellVal   = trim($recordValuesMap[$recordId][$colId] ?? '');

                if (self::isTimeRange($cellVal, $fromInput, $toInput)) {
                    if (!self::timeCellInRange($cellVal, $fromInput, $toInput)) {
                        return false;
                    }
                    continue;
                }

                // Empty cell is only allowed when both sides of the range are empty
                if ($cellVal === '') {
                    if ($fromInput !== '' || $toInput !== '') {
                        return false;
                    }
                    continue;
                }

                $isDateTime = (bool) preg_match('/^\d{4}-\d{2}-\d{2}[ T]\d{2}:\d{2}/', $cellVal);
                $cellDt     = self::parseCellValue($cellVal, $userTimezone, $isDateTime);

                if ($cellDt === null) {
                    if ($fromInput !== '' || $toInput !== '') {
                        return false;
                    }
                    continue;
                }

                // FROM (lower bound) – empty is allowed
                if ($fromInput !== '') {
                    $fromDt = self::parseUserDate($fromInput, $userDateFormat, false, $userTimezone, $isDateTime);
                    if ($fromDt === null) {
                        return false;
                    }
                    if ($fromDt->year === 0) { // day+month only
                        if ($cellDt->format('m-d') < $fromDt->format('m-d')) {
                            return false;
                        }
                    } elseif ($cellDt->lt($fromDt)) {
                        return false;
                    }
                }

                // TO (upper bound) – empty is allowed
                if ($toInput !== '') {
                    $toDt = self::parseUserDate($toInput, $userDateFormat, true, $userTimezone, $isDateTime);
                    if ($toDt === null) {
                        return false;
                    }
                    if ($toDt->year === 0) {
                        if ($cellDt->format('m-d') > $toDt->format('m-d')) {
                            return false;
                        }
                    } elseif ($cellDt->gt($toDt)) {
                        return false;
                    }
                }
            }
        }

        return true;
    }

    /**
     * Parse any user-typed date (or partial) into a CarbonImmutable.
     * Accepts: 01.09.1955, 01-09-1955, 01/09/1955, 1955-09-01, 09/1955, 02/2026, 1955, etc.
     */
    public static function parseUserDate(
        string $input,
        string $format,
        bool $isEndOfRange = false,
        string $timezone = 'UTC',
        bool $isDateTime = false
    ): ?CarbonImmutable {
        $input = trim($input);
        if ($input === '' || strlen($input) < 2) {
            return null;
        }

        // Normalise separators
        $normalized = str_replace(['.', '-', ' '], '/', $input);
        $format     = str_replace(['.', '-', ' '], '/', $format);

        // 1. Exact user format
        try {
            $native = DateTimeImmutable::createFromFormat('!' . $format, $normalized);
            if ($native !== false) {
                $dt = CarbonImmutable::instance($native)->setTimezone($isDateTime ? $timezone : 'UTC');
                return $isEndOfRange ? $dt->endOfDay() : $dt->startOfDay();
            }
        } catch (Throwable $e) {
            // continue
        }

        // 2. ISO / logical order (very common when pasting)
        foreach (['Y/m/d', 'Y-m-d', 'Y/m', 'Y'] as $iso) {
            try {
                $native = DateTimeImmutable::createFromFormat('!' . $iso, $normalized);
                if ($native !== false) {
                    $dt = CarbonImmutable::instance($native);
                    if ($isEndOfRange) {
                        if ($iso === 'Y') {
                            return $dt->endOfYear();
                        }
                        if ($iso === 'Y/m') {
                            return $dt->endOfMonth();
                        }
                        return $dt->endOfDay();
                    }
                    return $dt->startOfDay();
                }
            } catch (Throwable $e) {
                // continue
            }
        }

        // 3. Year only
        if (preg_match('/^\d{4}$/', $input)) {
            $year = (int) $input;
            return $isEndOfRange
                ? CarbonImmutable::create($year, 12, 31, 23, 59, 59)
                : CarbonImmutable::create($year, 1, 1, 0, 0, 0);
        }

        // 4. Month + Year (09/1955 or 9/1955)
        if (preg_match('/^(\d{1,2})\/(\d{4})$/', $normalized, $m)) {
            $month = (int) $m[1];
            $year  = (int) $m[2];
            if ($month < 1 || $month > 12) {
                return null;
            }
            $dt = CarbonImmutable::create($year, $month, 1, 0, 0, 0);
            return $isEndOfRange ? $dt->endOfMonth() : $dt->startOfMonth();
        }

        // 5. Day + Month only (01/09) → any year (year 0 marker)
        if (preg_match('/^(\d{1,2})\/(\d{1,2})$/', $normalized, $m)) {
            $day   = (int) $m[1];
            $month = (int) $m[2];
            if ($day < 1 || $day > 31 || $month < 1 || $month > 12) {
                return null;
            }
            return CarbonImmutable::create(0, $month, $day, 0, 0, 0);
        }

        // 6. Final aggressive fallbacks
        $fallbacks = [
            'd/m/Y', 'd/m/y', 'j/n/Y', 'j/n/y',
            'd/m', 'j/n',
            'm/Y', 'n/Y',
            'd.m.Y', 'd.m.y', 'd.m',
        ];
        foreach ($fallbacks as $f) {
            try {
                $native = DateTimeImmutable::createFromFormat('!' . $f, $normalized);
                if ($native !== false) {
                    $dt = CarbonImmutable::instance($native);
                    // No year in format → force year 0
                    if (strpos($f, 'Y') === false && strpos($f, 'y') === false) {
                        $dt = $dt->setDate(0, (int) $dt->month, (int) $dt->day);
                    }
                    return $isEndOfRange ? $dt->endOfDay() : $dt->startOfDay();
                }
            } catch (Throwable $e) {
                // continue
            }
        }

        return null;
    }

    /**
     * Compare a stored cell value against a user search term.
     */
    public static function dateCellMatchesSearch(
        string $cellVal,
        string $searchTerm,
        string $userDateFormat,
        string $userTimezone = 'UTC',
        bool $isDateTime = false
    ): bool {
        $userDt = self::parseUserDate($searchTerm, $userDateFormat, false, $userTimezone, $isDateTime);
        if ($userDt === null) {
            return false;
        }

        $cellDt = self::parseCellValue($cellVal, $userTimezone, $isDateTime);
        if ($cellDt === null) {
            return false;
        }

        // Day + month only
        if ($userDt->year === 0) {
            return $cellDt->format('m-d') === $userDt->format('m-d');
        }

        // Full date (ignore time component for matching)
        return $cellDt->toDateString() === $userDt->toDateString();
    }

    /**
     * Turn a DB cell value into a Carbon instance (applies user TZ for timestamps).
     */
    private static function parseCellValue(string $cellVal, string $userTimezone, bool $isDateTime): ?CarbonImmutable
    {
        try {
            if ($isDateTime) {
                // System timestamp – stored as UTC, convert to user TZ for comparison
                return CarbonImmutable::parse($cellVal, 'UTC')->setTimezone($userTimezone);
            }
            // Event date – pure calendar date, no TZ shift
            $native = DateTimeImmutable::createFromFormat('!Y-m-d', substr($cellVal, 0, 10));
            return $native ? CarbonImmutable::instance($native) : null;
        } catch (Throwable $e) {
            return null;
        }
    }

    /**
     * Safe user preference getter (works for guests and logged-in users).
     */
    public static function getUserDatePrefs(): array
    {
        $dateFormat = 'd/m/Y';
        $timezone   = 'UTC';

        $pdo = $GLOBALS['pdo'] ?? null;
        if ($pdo instanceof PDO && isset($_SESSION['user_id']) && function_exists('get_current_user_data')) {
            $user = get_current_user_data($pdo);
            if (is_array($user)) {
                if (!empty($user['date_format']) && is_string($user['date_format'])) {
                    $dateFormat = $user['date_format'];
                }
                if (!empty($user['timezone']) && is_string($user['timezone'])) {
                    $timezone = $user['timezone'];
                }
            }
        }

        return [
            'date_format' => $dateFormat,
            'timezone'    => $timezone,
        ];
    }

    private static function isTimeRange(string $cell, string $from, string $to): bool
    {
        return self::looksLikeTime($cell) || self::looksLikeTime($from) || self::looksLikeTime($to);
    }

    private static function looksLikeTime(string $v): bool
    {
        $v = trim($v);
        if ($v === '') {
            return false;
        }
        if (preg_match('/^\d{1,2}:\d{2}(:\d{2})?(\s*[AaPp][Mm])?$/', $v)) {
            return true;
        }
        return function_exists('normalize_incoming_time') && preg_match('/^\d{2}:\d{2}:\d{2}$/', normalize_incoming_time($v));
    }

    private static function timeToMinutes(string $v): ?int
    {
        $norm = function_exists('normalize_incoming_time') ? normalize_incoming_time($v) : $v;
        if (!preg_match('/^(\d{2}):(\d{2})/', $norm, $m)) {
            return null;
        }
        $h = (int) $m[1];
        $i = (int) $m[2];
        if ($h > 23 || $i > 59) {
            return null;
        }
        return ($h * 60) + $i;
    }

    private static function timeCellInRange(string $cell, string $from, string $to): bool
    {
        if ($cell === '') {
            return ($from === '' && $to === '');
        }
        $cellMin = self::timeToMinutes($cell);
        if ($cellMin === null) {
            return ($from === '' && $to === '');
        }
        if ($from !== '') {
            $fromMin = self::timeToMinutes($from);
            if ($fromMin === null || $cellMin < $fromMin) {
                return false;
            }
        }
        if ($to !== '') {
            $toMin = self::timeToMinutes($to);
            if ($toMin === null || $cellMin > $toMin) {
                return false;
            }
        }
        return true;
    }
}
