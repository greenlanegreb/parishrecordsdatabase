<?php
declare(strict_types=1);

/**
 * Date / time display and input helpers.
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

        $dt = false;
        foreach (['Y-m-d', 'd/m/Y', 'd.m.Y', 'Y/m/d', 'Y.m.d', 'd-m-Y', 'Y-m', 'm/Y', 'm.Y', 'Y'] as $fmt) {
            $parsed = DateTime::createFromFormat($fmt, trim($dateStr));
            if ($parsed !== false) {
                $dt = $parsed;
                break;
            }
        }

        if ($dt === false && @strtotime($dateStr) !== false) {
            $dt = new DateTime($dateStr);
        }

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

            return $dt->format($formatPref);
        }

        return $dateStr;
    }
}

if (!function_exists('get_user_datetime_format')) {
    /**
     * @param array{date_format?: string, time_format?: string} $currentUser
     */
    function get_user_datetime_format(array $currentUser): string
    {
        $userDateFormat = isset($currentUser['date_format']) && is_string($currentUser['date_format'])
            ? $currentUser['date_format'] : 'd/m/Y';
        $userTimeFormat = isset($currentUser['time_format']) && is_string($currentUser['time_format'])
            ? $currentUser['time_format'] : '24';

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
     * @return array{0: string, 1: string}
     */
    function get_user_time_prefs(array $currentUser): array
    {
        $tz = isset($currentUser['timezone']) && is_string($currentUser['timezone'])
            ? $currentUser['timezone'] : 'UTC';
        return [
            $tz,
            get_user_datetime_format($currentUser),
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
