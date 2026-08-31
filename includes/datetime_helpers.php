<?php
declare(strict_types=1);

/**
 * Date / time display and input helpers.
 * Site defaults (default_timezone, default_date_format, default_time_format)
 * apply when the viewer has no personal preference (or is a guest).
 */

if (!function_exists('format_user_time')) {
    function format_user_time(?string $utcTimestamp, string $timezoneStr, string $formatStr): string
    {
        if (empty($utcTimestamp)) {
            return 'N/A';
        }
        try {
            $dt = new DateTime($utcTimestamp, new DateTimeZone('UTC'));
            $dt->setTimezone(new DateTimeZone($timezoneStr));
            return $dt->format($formatStr);
        } catch (Exception $e) {
            return $utcTimestamp;
        }
    }
}

if (!function_exists('format_display_date')) {
    function format_display_date(?string $dateStr, string $formatPref): string
    {
        if (empty($dateStr)) {
            return '';
        }

        $trimmed = trim($dateStr);
        if (preg_match('/^(\d{4}-\d{2}-\d{2})[ T].+/', $trimmed, $m)) {
            $trimmed = $m[1];
        }

        $dt = false;
        $tryFmts = ['Y-m-d', $formatPref, 'd/m/Y', 'm/d/Y', 'd.m.Y', 'Y/m/d', 'Y.m.d', 'd-m-Y', 'Y-m', 'm/Y', 'm.Y', 'Y'];
        $seen = [];
        foreach ($tryFmts as $fmt) {
            if ($fmt === '' || isset($seen[$fmt])) {
                continue;
            }
            $seen[$fmt] = true;
            $parsed = DateTime::createFromFormat('!' . $fmt, $trimmed);
            if ($parsed !== false) {
                $errors = DateTime::getLastErrors();
                if ($errors === false || (($errors['warning_count'] ?? 0) === 0 && ($errors['error_count'] ?? 0) === 0)) {
                    $dt = $parsed;
                    break;
                }
            }
        }

        if ($dt === false && @strtotime($trimmed) !== false) {
            $dt = new DateTime($trimmed);
        }
        $dateStr = $trimmed;

        if ($dt !== false) {
            $trimmed = trim($dateStr);
            $length = strlen($trimmed);

            if ($length === 4 && ctype_digit($trimmed)) {
                return $dt->format('Y');
            }

            if ($length === 7) {
                if (strpos($formatPref, '.') !== false) {
                    return $dt->format('m.Y');
                }
                if (strpos($formatPref, '-') !== false) {
                    return $dt->format('Y-m');
                }
            }

            $out = $dt->format($formatPref);
            if (strpbrk($formatPref, 'HisAa') === false) {
                $out = preg_replace('/\s+\d{1,2}:\d{2}(:\d{2})?(\s*[AaPp][Mm])?$/', '', $out) ?? $out;
            }
            return $out;
        }

        return $dateStr;
    }
}

if (!function_exists('resolve_pdo_for_settings')) {
    function resolve_pdo_for_settings(?PDO $pdo = null): ?PDO
    {
        if ($pdo instanceof PDO) {
            return $pdo;
        }
        if (isset($GLOBALS['pdo']) && $GLOBALS['pdo'] instanceof PDO) {
            return $GLOBALS['pdo'];
        }
        return null;
    }
}

/**
 * @return array{0: string, 1: string, 2: string} [timezone, date_format, time_format]
 */
if (!function_exists('get_site_datetime_defaults')) {
    function get_site_datetime_defaults(?PDO $pdo = null): array
    {
        $tz = 'UTC';
        $date = 'd/m/Y';
        $time = '24';

        $pdo = resolve_pdo_for_settings($pdo);
        if ($pdo instanceof PDO && function_exists('get_setting')) {
            $tzRaw = get_setting($pdo, 'default_timezone', 'UTC');
            $dateRaw = get_setting($pdo, 'default_date_format', 'd/m/Y');
            $timeRaw = get_setting($pdo, 'default_time_format', '24');
            if ($tzRaw !== '') {
                $tz = $tzRaw;
            }
            if ($dateRaw !== '') {
                $date = $dateRaw;
            }
            if ($timeRaw !== '') {
                $time = $timeRaw;
            }
        }

        if (!in_array($tz, timezone_identifiers_list(), true)) {
            $tz = 'UTC';
        }

        $allowedDates = ['d/m/Y', 'd.m.Y', 'Y-m-d', 'm/d/Y', 'd-m-Y'];
        if (!in_array($date, $allowedDates, true)) {
            $date = 'd/m/Y';
        }

        if ($time !== '12' && $time !== '24') {
            $time = '24';
        }

        return [$tz, $date, $time];
    }
}

if (!function_exists('get_user_datetime_format')) {
    /**
     * @param array{date_format?: string, time_format?: string} $currentUser
     */
    function get_user_datetime_format(array $currentUser, ?PDO $pdo = null): string
    {
        [, $siteDate, $siteTime] = get_site_datetime_defaults($pdo);

        $userDateFormat = (isset($currentUser['date_format']) && is_string($currentUser['date_format']) && $currentUser['date_format'] !== '')
            ? $currentUser['date_format']
            : $siteDate;
        $userTimeFormat = (isset($currentUser['time_format']) && is_string($currentUser['time_format']) && $currentUser['time_format'] !== '')
            ? $currentUser['time_format']
            : $siteTime;

        if ($userTimeFormat === '12') {
            return $userDateFormat . ' h:i A';
        }
        if ($userTimeFormat === '24') {
            return $userDateFormat . ' H:i';
        }
        return $userDateFormat;
    }
}

if (!function_exists('get_user_time_prefs')) {
    /**
     * @param array{timezone?: string, date_format?: string, time_format?: string} $currentUser
     * @return array{0: string, 1: string} [timezone, full datetime format string]
     */
    function get_user_time_prefs(array $currentUser, ?PDO $pdo = null): array
    {
        [$siteTz] = get_site_datetime_defaults($pdo);

        $tz = (isset($currentUser['timezone']) && is_string($currentUser['timezone']) && $currentUser['timezone'] !== '')
            ? $currentUser['timezone']
            : $siteTz;

        if (!in_array($tz, timezone_identifiers_list(), true)) {
            $tz = $siteTz;
        }

        return [
            $tz,
            get_user_datetime_format($currentUser, $pdo),
        ];
    }
}

if (!function_exists('normalize_incoming_date')) {
    function normalize_incoming_date(?string $val): string
    {
        $valStr = trim((string) $val);
        if ($valStr === '') {
            return '';
        }

        $cleanVal = $valStr;

        if (preg_match('/^(\d{4})$/', $valStr)) {
            $cleanVal = $valStr;
        } elseif (preg_match('/^(\d{4})[\/\-](\d{1,2})$/', $valStr, $m)) {
            $cleanVal = "{$m[1]}-" . str_pad($m[2], 2, '0', STR_PAD_LEFT);
        } else {
            foreach (['d/m/Y', 'd.m.Y', 'Y-m-d', 'd-m-Y', 'Y/m/d', 'm/d/Y'] as $fmt) {
                $dt = DateTime::createFromFormat($fmt, $valStr);
                if ($dt !== false) {
                    $cleanVal = $dt->format('Y-m-d');
                    break;
                }
            }
        }

        return $cleanVal;
    }
}

if (!function_exists('get_date_placeholder')) {
    function get_date_placeholder(?string $dateFormat): string
    {
        $userFmt = $dateFormat ?: 'd/m/Y';

        if ($userFmt === 'd/m/Y' || $userFmt === 'd/m/y' || $userFmt === 'd-m-Y' || $userFmt === 'd-m-y') {
            return 'DD/MM/YYYY (e.g. 25/05/1955)';
        }
        if ($userFmt === 'd.m.Y' || $userFmt === 'd.m.y') {
            return 'DD.MM.YYYY (e.g. 25.05.1955)';
        }
        if ($userFmt === 'm/d/Y' || $userFmt === 'm/d/y' || $userFmt === 'm-d-Y') {
            return 'MM/DD/YYYY (e.g. 05/25/1955)';
        }
        if ($userFmt === 'Y-m-d' || $userFmt === 'Y/m/d') {
            return 'YYYY-MM-DD (e.g. 1955-05-25)';
        }

        return 'DD/MM/YYYY (e.g. 25/05/1955)';
    }
}


if (!function_exists('resolve_viewer_time_format')) {
    /**
     * @param array<string, mixed> $currentUser
     */
    function resolve_viewer_time_format(array $currentUser = [], ?PDO $pdo = null): string
    {
        [, , $siteTime] = array_pad(get_site_datetime_defaults($pdo), 3, '24');
        $pref = isset($currentUser['time_format']) && is_string($currentUser['time_format']) && $currentUser['time_format'] !== ''
            ? $currentUser['time_format']
            : (string) $siteTime;
        return $pref === '12' ? '12' : '24';
    }
}

if (!function_exists('format_display_time')) {
    function format_display_time(?string $timeStr, string $timePref = '24'): string
    {
        $t = trim((string) $timeStr);
        if ($t === '') {
            return '';
        }
        $dt = DateTime::createFromFormat('H:i:s', $t)
            ?: DateTime::createFromFormat('H:i', $t)
            ?: DateTime::createFromFormat('g:i A', $t)
            ?: DateTime::createFromFormat('g:i a', $t)
            ?: DateTime::createFromFormat('h:i A', $t);
        if ($dt === false && preg_match('/^(\d{1,2}):(\d{2})(?::(\d{2}))?\s*([AaPp][Mm])?$/', $t, $m)) {
            $h = (int) $m[1];
            $i = (int) $m[2];
            $ampm = $m[4] ?? '';
            if ($ampm !== '') {
                $ampm = strtoupper($ampm);
                if ($ampm === 'PM' && $h < 12) {
                    $h += 12;
                }
                if ($ampm === 'AM' && $h === 12) {
                    $h = 0;
                }
            }
            if ($h >= 0 && $h <= 23 && $i >= 0 && $i <= 59) {
                $dt = DateTime::createFromFormat('H:i', sprintf('%02d:%02d', $h, $i));
            }
        }
        if ($dt === false) {
            return $t;
        }
        return $timePref === '12' ? $dt->format('g:i A') : $dt->format('H:i');
    }
}

if (!function_exists('normalize_incoming_time')) {
    function normalize_incoming_time(?string $val): string
    {
        $shown = format_display_time($val, '24');
        if ($shown === '' || !preg_match('/^\d{2}:\d{2}$/', $shown)) {
            $t = trim((string) $val);
            return $t;
        }
        return $shown . ':00';
    }
}

if (!function_exists('get_time_placeholder')) {
    function get_time_placeholder(?string $timePref): string
    {
        return ($timePref === '12') ? '2:30 PM' : '14:30';
    }
}
