<?php
declare(strict_types=1);
require_once __DIR__ . '/data_entry_helpers.php';
require_once __DIR__ . '/export_helpers.php';
require_once __DIR__ . '/datetime_helpers.php';
require_once __DIR__ . '/username_check_helpers.php';
use App\Services\DateSearchService;
if (!function_exists('obscure_name_ajax')) {
    function obscure_name_ajax(?string $name): string
    {
        if (empty($name)) {
            return 'User_Anon';
        }
        $len = strlen($name);
        if ($len <= 2) {
            return $name[0] . '*';
        }
        return substr($name, 0, 2) . str_repeat('*', max(1, $len - 2));
    }
}
if (!function_exists('format_user_display_name')) {
    /**
     * @param PDO $pdo
     * @param array{id: int|string, first_name?: string, surname?: string, username?: string, attribution_display_mode?: string}|null $targetUser
     * @param array{id?: int|string}|null $viewerUser
     * @return string
     */
    function format_user_display_name(PDO $pdo, ?array $targetUser, ?array $viewerUser = null): string
    {
        if ($targetUser === null || !isset($targetUser['id'])) {
            return 'Contributor';
        }
        $first = isset($targetUser['first_name']) && is_string($targetUser['first_name'])
            ? trim($targetUser['first_name']) : '';
        $surname = isset($targetUser['surname']) && is_string($targetUser['surname'])
            ? trim($targetUser['surname']) : '';
        $username = isset($targetUser['username']) && is_string($targetUser['username'])
            ? trim($targetUser['username']) : '';
        $mode = isset($targetUser['attribution_display_mode']) && is_string($targetUser['attribution_display_mode'])
            ? $targetUser['attribution_display_mode']
            : 'initials_random';
        if ($mode === 'public') {
            $mode = 'full_name';
        }
        if ($mode === 'anonymous') {
            $mode = 'initials_random';
        }
        $fullName = trim($first . ' ' . $surname);
        $initials = '';
        if ($first !== '') {
            $initials .= mb_substr($first, 0, 1);
        }
        if ($surname !== '') {
            $initials .= mb_substr($surname, 0, 1);
        }
        if ($initials === '') {
            $initials = $username !== '' ? mb_substr($username, 0, 2) : 'C';
        }
        $anonymousLabel = strtoupper($initials) . '-' . ((((int) $targetUser['id']) * 37) % 900 + 100);
        $viewerLoggedIn = $viewerUser !== null && isset($viewerUser['id']);
        // Admin or view_user_full_names: always full name
        if ($viewerLoggedIn) {
            $forceFull = false;
            if (function_exists('is_admin') && is_admin($pdo)) {
                $forceFull = true;
            } elseif (function_exists('has_permission') && has_permission($pdo, 'view_user_full_names')) {
                $forceFull = true;
            }
            if ($forceFull) {
                return $fullName !== '' ? $fullName : $anonymousLabel;
            }
        }
        // Public
        if ($mode === 'full_name') {
            return $fullName !== '' ? $fullName : $anonymousLabel;
        }
        // Volunteers only: full name for logged-in volunteers / mods / admins (admin already handled above)
        if ($mode === 'volunteers_only') {
            if (!$viewerLoggedIn) {
                return $anonymousLabel;
            }
            // Any logged-in user counts as volunteer audience for this mode
            return $fullName !== '' ? $fullName : $anonymousLabel;
        }
        // Anonymous
        return $anonymousLabel;
    }
}
if (!function_exists('format_user_display_name_by_id')) {
    /**
     * Load a user by id and format their display name (same rules as format_user_display_name).
     *
     * @param PDO $pdo
     * @param int|null $userId
     * @param array{id?: int|string}|null $viewerUser
     * @return string
     */
    function format_user_display_name_by_id(PDO $pdo, ?int $userId, ?array $viewerUser = null): string
    {
        if ($userId === null || $userId < 1) {
            return 'System';
        }
        $stmt = $pdo->prepare(
            'SELECT id, username, first_name, surname, attribution_display_mode
             FROM users WHERE id = ? LIMIT 1'
        );
        $stmt->execute([$userId]);
        /** @var array{id: int|string, username?: string, first_name?: string, surname?: string, attribution_display_mode?: string}|false $row */
        $row = $stmt->fetch(PDO::FETCH_ASSOC);
        if ($row === false) {
            return 'Contributor';
        }
        return format_user_display_name($pdo, $row, $viewerUser);
    }
}
if (!function_exists('format_boolean_value')) {
    function format_boolean_value(mixed $val, string $format): string
    {
        if ($val === null || $val === '') {
            return 'N/A';
        }
        $isTrue = filter_var($val, FILTER_VALIDATE_BOOLEAN);
        switch ($format) {
            case 'male_female':
                return $isTrue ? 'Male' : 'Female';
            case 'true_false':
                return $isTrue ? 'True' : 'False';
            case 'tick_cross':
                return $isTrue ? '✔' : '✘';
            case 'yes_no':
            default:
                return $isTrue ? 'Yes' : 'No';
        }
    }
}
if (!function_exists('sanitize_incoming_text')) {
    function sanitize_incoming_text(?string $text): string
    {
        if ($text === null || $text === '') {
            return '';
        }
        $text = strip_tags($text);
        $search = [
            "\xC2\xAB", "\xC2\xBB",
            "\xE2\x80\x98", "\xE2\x80\x99",
            "\xE2\x80\x9C", "\xE2\x80\x9D",
            "\xE2\x80\x93", "\xE2\x80\x94",
            "\xE2\x80\xA6"
        ];
        $replace = [
            '"', '"', "'", "'", '"', '"', '-', '-', '...'
        ];
        $text = str_replace($search, $replace, $text);
        $cleanText = preg_replace('/[\x{00A0}\x{200B}]/u', ' ', $text);
        return trim($cleanText !== null ? $cleanText : $text);
    }
}
if (!function_exists('get_setting')) {
    function get_setting(PDO $pdo, string $key, string $default = ''): string
    {
        try {
            $stmt = $pdo->prepare("SELECT setting_value FROM site_settings WHERE setting_key = ?");
            $stmt->execute([$key]);
            $val = $stmt->fetchColumn();
            return ($val !== false && $val !== null && is_string($val)) ? $val : $default;
        } catch (Exception $e) {
            return $default;
        }
    }
}
if (!function_exists('is_module_enabled')) {
    function is_module_enabled(PDO $pdo, string $moduleKey): bool
    {
        /** @var array<string, bool> $moduleCache */
        static $moduleCache = [];
        if (array_key_exists($moduleKey, $moduleCache)) {
            return $moduleCache[$moduleKey];
        }
        try {
            $stmt = $pdo->prepare("SELECT setting_value FROM site_settings WHERE setting_key = ?");
            $stmt->execute(['module_' . $moduleKey . '_enabled']);
            $val = $stmt->fetchColumn();
        
            if ($moduleKey === 'leaderboard') {
                $usersEnabled = is_module_enabled($pdo, 'users');
                if (!$usersEnabled) {
                    return false;
                }
            }
        
            $enabled = ($val === false || $val === null) ? true : ((int)$val === 1);
            $moduleCache[$moduleKey] = $enabled;
            return $enabled;
        } catch (Exception $e) {
            $moduleCache[$moduleKey] = true;
            return true;
        }
    }
}
if (!function_exists('get_active_language')) {
    function get_active_language(): string
    {
        if (session_status() === PHP_SESSION_NONE) {
            session_start();
        }
        if (!empty($_SESSION['lang']) && is_string($_SESSION['lang'])) {
            $cleaned = preg_replace('/[^a-zA-Z_]/', '', $_SESSION['lang']);
            if ($cleaned !== null && $cleaned !== '') {
                return $cleaned;
            }
        }
        /** @var PDO|null $pdo */
        $pdo = $GLOBALS['pdo'] ?? null;
        if ($pdo instanceof PDO && function_exists('get_current_user_data')) {
            $user = get_current_user_data($pdo);
            if ($user !== false && $user !== null && isset($user['language']) && is_string($user['language'])) {
                $cleaned = preg_replace('/[^a-zA-Z_]/', '', $user['language']);
                if ($cleaned !== null && $cleaned !== '') {
                    return $cleaned;
                }
            }
        }
        if ($pdo instanceof PDO && function_exists('get_setting')) {
            $site = get_setting($pdo, 'default_language', 'en');
            if ($site !== '') {
                $cleaned = preg_replace('/[^a-zA-Z_]/', '', $site);
                if ($cleaned !== null && $cleaned !== '') {
                    return $cleaned;
                }
            }
        }
        return 'en';
    }
}
if (!function_exists('__')) {
    /**
     * @param string $key
     * @param array<string, scalar> $replace
     * @return string
     */
    function __(string $key, array $replace = []): string
    {
        /** @var array<string, string>|null $catalogue */
        static $catalogue = null;
        static $loadedLang = null;
        $lang = get_active_language();
        if ($catalogue === null || $loadedLang !== $lang) {
            $safeRegex = preg_replace('/[^a-zA-Z_]/', '', $lang);
            $safe = ($safeRegex !== null && $safeRegex !== '') ? $safeRegex : 'en';
        
            // Look for exact file path match first
            $path = __DIR__ . '/../lang/' . $safe . '.php';
            if (!is_file($path)) {
                // Fallback to base language code if compound file doesn't exist
                $baseLang = explode('_', $safe)[0];
                $path = __DIR__ . '/../lang/' . $baseLang . '.php';
                $safe = $baseLang;
            }
            if (!is_file($path)) {
                $path = __DIR__ . '/../lang/en.php';
                $safe = 'en';
            }
            $rawCatalogue = is_file($path) ? include $path : [];
            $catalogue = is_array($rawCatalogue) ? $rawCatalogue : [];
            $loadedLang = $lang;
        }
        $text = isset($catalogue[$key]) && is_string($catalogue[$key]) ? $catalogue[$key] : $key;
        foreach ($replace as $k => $v) {
            $text = str_replace(':' . $k, (string)$v, $text);
        }
        return $text;
    }
}
if (!function_exists('set_language')) {
    function set_language(string $code): void
    {
        if (session_status() === PHP_SESSION_NONE) {
            session_start();
        }
        $cleaned = preg_replace('/[^a-zA-Z_]/', '', $code);
        $_SESSION['lang'] = ($cleaned !== null && $cleaned !== '') ? $cleaned : 'en';
    }
}
if (!function_exists('get_schema_version')) {
    function get_schema_version(PDO $pdo): int
    {
        $val = get_setting($pdo, 'schema_version', '0');
        return max(0, (int)$val);
    }
}
if (!function_exists('set_schema_version')) {
    function set_schema_version(PDO $pdo, int $version): void
    {
        $version = max(0, $version);
        $stmt = $pdo->prepare(
            "INSERT INTO site_settings (setting_key, setting_value) VALUES ('schema_version', ?)
             ON DUPLICATE KEY UPDATE setting_value = ?"
        );
        $stmt->execute([(string)$version, (string)$version]);
    }
}
if (!function_exists('adjust_user_points')) {
    function adjust_user_points(PDO $pdo, int $userId, int $amount): void
    {
        if ($userId <= 0 || $amount === 0) {
            return;
        }
        if ($amount > 0) {
            $stmt = $pdo->prepare("UPDATE users SET points = points + ? WHERE id = ?");
            $stmt->execute([$amount, $userId]);
        } else {
            $absAmount = abs($amount);
            $stmt = $pdo->prepare("UPDATE users SET points = GREATEST(0, points - ?) WHERE id = ?");
            $stmt->execute([$absAmount, $userId]);
        }
    }
}
function get_flash(string $key): string {
    // Check $GLOBALS first (in case require_admin_page already ran)
    if (isset($GLOBALS[$key])) {
        $val = $GLOBALS[$key];
        unset($GLOBALS[$key]); // consume it
        return $val;
    }
  
    // Fall back to session directly
    if (isset($_SESSION[$key])) {
        $val = $_SESSION[$key];
        unset($_SESSION[$key]); // consume it
        return $val;
    }
  
    return '';
}
