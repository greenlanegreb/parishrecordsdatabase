<?php
declare(strict_types=1);
/**
 * Shared personal-preference helpers for onboarding + profile (DRY).
 * Loaded from controllers; safe to require_once.
 */

if (!function_exists('user_available_language_codes')) {
    /**
     * @return list<string>
     */
    function user_available_language_codes(): array
    {
        $codes = [];
        $langDir = dirname(__DIR__) . '/lang';
        if (is_dir($langDir)) {
            $files = glob($langDir . '/*.php');
            if ($files !== false) {
                foreach ($files as $file) {
                    $code = basename($file, '.php');
                    if (preg_match('/^[a-z_]+$/', $code)) {
                        $codes[] = $code;
                    }
                }
            }
        }
        sort($codes);
        if (!in_array('en', $codes, true)) {
            array_unshift($codes, 'en');
        }
        return $codes;
    }
}

if (!function_exists('user_normalize_personal_details')) {
    /**
     * Normalize personal-detail fields from request-like input.
     *
     * @param array<string, mixed> $input
     * @return array{
     *   first_name: string,
     *   surname: string,
     *   attribution_display_mode: string,
     *   timezone: string,
     *   date_format: string,
     *   time_format: string,
     *   language: string,
     *   language_db: string|null
     * }
     */
    function user_normalize_personal_details(array $input): array
    {
        $firstName = isset($input['first_name']) && is_string($input['first_name']) ? trim($input['first_name']) : '';
        $surname = isset($input['surname']) && is_string($input['surname']) ? trim($input['surname']) : '';
        $displayMode = isset($input['attribution_display_mode']) && is_string($input['attribution_display_mode'])
            ? trim($input['attribution_display_mode']) : 'initials_random';
        $timezone = isset($input['timezone']) && is_string($input['timezone']) ? trim($input['timezone']) : 'UTC';
        $dateFormat = isset($input['date_format']) && is_string($input['date_format']) ? trim($input['date_format']) : 'd/m/Y';
        $timeFormat = isset($input['time_format']) && is_string($input['time_format']) ? trim($input['time_format']) : '24';

        $rawLang = isset($input['language']) && is_string($input['language']) ? strtolower(trim($input['language'])) : '';
        $language = preg_replace('/[^a-z_]/', '', $rawLang) ?? '';

        $allowedModes = ['full_name', 'volunteers_only', 'initials_random'];
        if (!in_array($displayMode, $allowedModes, true)) {
            $displayMode = 'initials_random';
        }

        if (!in_array($timezone, timezone_identifiers_list(), true)) {
            $timezone = 'UTC';
        }

        $allowedDateFormats = ['d/m/Y', 'd/m/y', 'd.m.Y', 'm/d/Y', 'l j F Y'];
        if (!in_array($dateFormat, $allowedDateFormats, true)) {
            $dateFormat = 'd/m/Y';
        }

        $allowedTimeFormats = ['12', '24', 'none'];
        if (!in_array($timeFormat, $allowedTimeFormats, true)) {
            $timeFormat = '24';
        }

        if ($language !== '') {
            $langFile = dirname(__DIR__) . '/lang/' . $language . '.php';
            if (!is_file($langFile)) {
                $language = '';
            }
        }

        return [
            'first_name' => $firstName,
            'surname' => $surname,
            'attribution_display_mode' => $displayMode,
            'timezone' => $timezone,
            'date_format' => $dateFormat,
            'time_format' => $timeFormat,
            'language' => $language,
            'language_db' => ($language === '') ? null : $language,
        ];
    }
}

if (!function_exists('user_validate_personal_details_required')) {
    /**
     * @param array<string, mixed> $normalized from user_normalize_personal_details
     * @return string error message or '' if ok
     */
    function user_validate_personal_details_required(array $normalized): string
    {
        $first = isset($normalized['first_name']) ? trim((string) $normalized['first_name']) : '';
        $sur = isset($normalized['surname']) ? trim((string) $normalized['surname']) : '';
        if ($first === '' || $sur === '') {
            return function_exists('__')
                ? __('onboarding.err_names_required')
                : 'First name and surname are required.';
        }
        return '';
    }
}

if (!function_exists('user_apply_ui_language')) {
    /**
     * Apply language for the rest of this session (and thus immediate UI).
     * Empty string = use site default (clear personal session override).
     */
    function user_apply_ui_language(string $language, ?PDO $pdo = null): void
    {
        if ($language !== '') {
            if (function_exists('set_language')) {
                set_language($language);
            } else {
                $_SESSION['lang'] = $language;
            }
            return;
        }

        // Site default
        $site = 'en';
        if ($pdo instanceof PDO && function_exists('get_setting')) {
            $site = (string) get_setting($pdo, 'default_language', 'en');
            $site = preg_replace('/[^a-zA-Z_]/', '', $site) ?? 'en';
            if ($site === '') {
                $site = 'en';
            }
        }
        if (function_exists('set_language')) {
            set_language($site);
        } else {
            $_SESSION['lang'] = $site;
        }
    }
}

if (!function_exists('user_save_personal_details')) {
    /**
     * Persist personal details for a user. Does not touch password/2FA.
     *
     * @param array<string, mixed> $normalized from user_normalize_personal_details
     */
    function user_save_personal_details(PDO $pdo, int|string $userId, array $normalized, bool $clearNewUser = false): bool
    {
        if ($clearNewUser) {
            $sql = 'UPDATE users SET first_name = ?, surname = ?, attribution_display_mode = ?, timezone = ?, date_format = ?, time_format = ?, language = ?, is_new_user = 0 WHERE id = ?';
        } else {
            $sql = 'UPDATE users SET first_name = ?, surname = ?, attribution_display_mode = ?, timezone = ?, date_format = ?, time_format = ?, language = ? WHERE id = ?';
        }
        $stmt = $pdo->prepare($sql);
        return $stmt->execute([
            $normalized['first_name'],
            $normalized['surname'],
            $normalized['attribution_display_mode'],
            $normalized['timezone'],
            $normalized['date_format'],
            $normalized['time_format'],
            $normalized['language_db'],
            $userId,
        ]);
    }
}

if (!function_exists('user_store_personal_draft')) {
    /**
     * @param array<string, mixed> $normalized
     */
    function user_store_personal_draft(string $key, array $normalized): void
    {
        $_SESSION[$key] = [
            'first_name' => $normalized['first_name'],
            'surname' => $normalized['surname'],
            'attribution_display_mode' => $normalized['attribution_display_mode'],
            'timezone' => $normalized['timezone'],
            'date_format' => $normalized['date_format'],
            'time_format' => $normalized['time_format'],
            'language' => $normalized['language'],
        ];
    }
}

if (!function_exists('user_merge_personal_draft')) {
    /**
     * Merge session draft over user row for form display.
     *
     * @param array<string, mixed> $user
     * @return array<string, mixed>
     */
    function user_merge_personal_draft(array $user, string $key): array
    {
        if (!isset($_SESSION[$key]) || !is_array($_SESSION[$key])) {
            return $user;
        }
        $d = $_SESSION[$key];
        foreach (['first_name', 'surname', 'attribution_display_mode', 'timezone', 'date_format', 'time_format', 'language'] as $f) {
            if (array_key_exists($f, $d)) {
                $user[$f] = $d[$f];
            }
        }
        return $user;
    }
}
