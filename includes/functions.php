<?php
declare(strict_types=1);
/**
 * MIGRATED FILE MAPPING
 * ---------------------
 * Original Old File: includes/functions.php
 * Migrated Date: 2026-08-04 17:30:00
 */

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
     * @param array{id: int|string, first_name?: string, surname?: string, username?: string, attribution_display_mode?: string}|null $viewerUser
     * @return string
     */
    function format_user_display_name(PDO $pdo, ?array $targetUser, ?array $viewerUser = null): string
    {
        if ($targetUser === null || !isset($targetUser['id'])) {
            return 'User_Anon';
        }

        // Check if viewer has explicit permission or role to view full names
        $canSeeFullNames = false;
        if ($viewerUser !== null && isset($viewerUser['id'])) {
            $viewerId = (int)$viewerUser['id'];
            if (is_admin($pdo, $viewerId) || has_permission($pdo, 'view_user_full_names', $viewerId)) {
                $canSeeFullNames = true;
            } else {
                // Automatically allow built-in/active moderators to see full names when logged in
                $tablesChk = $pdo->query("SELECT id FROM dynamic_tables");
                $tableIds = $tablesChk !== false ? $tablesChk->fetchAll(PDO::FETCH_COLUMN) : [];
                foreach ($tableIds as $tId) {
                    if (has_permission($pdo, 'moderate_table_' . (int)$tId, $viewerId)) {
                        $canSeeFullNames = true;
                        break;
                    }
                }
            }
        }

        $first = isset($targetUser['first_name']) && is_string($targetUser['first_name']) ? trim($targetUser['first_name']) : '';
        $surname = isset($targetUser['surname']) && is_string($targetUser['surname']) ? trim($targetUser['surname']) : '';
        $username = isset($targetUser['username']) && is_string($targetUser['username']) ? trim($targetUser['username']) : 'User';
        $mode = isset($targetUser['attribution_display_mode']) && is_string($targetUser['attribution_display_mode']) ? $targetUser['attribution_display_mode'] : 'initials_random';

        // If viewer has administrative/moderator full name privilege, show Full Name
        if ($canSeeFullNames) {
            if ($first !== '' || $surname !== '') {
                return trim("{$first} {$surname}") . " ({$username})";
            }
            return $username;
        }

        // Profile display preferences
        if ($mode === 'full_name') {
            if ($first !== '' || $surname !== '') {
                return trim("{$first} {$surname}");
            }
            return $username;
        } elseif ($mode === 'volunteers_only') {
            // Volunteers only: Visible as full name to logged-in users, but anonymous initials to guests
            if ($viewerUser !== null && isset($viewerUser['id'])) {
                if ($first !== '' || $surname !== '') {
                    return trim("{$first} {$surname}");
                }
                return $username;
            } else {
                // Public guest view falls back to initials & random number
                $initials = '';
                if ($first !== '') {
                    $initials .= mb_substr($first, 0, 1);
                }
                if ($surname !== '') {
                    $initials .= mb_substr($surname, 0, 1);
                }
                if ($initials === '') {
                    $initials = mb_substr($username, 0, 2);
                }
                
                $randNum = ((int)$targetUser['id'] * 37) % 900 + 100; 
                return strtoupper($initials) . '-' . $randNum;
            }
        } else {
            // Default: initials_random (Anonymous to everyone)
            $initials = '';
            if ($first !== '') {
                $initials .= mb_substr($first, 0, 1);
            }
            if ($surname !== '') {
                $initials .= mb_substr($surname, 0, 1);
            }
            if ($initials === '') {
                $initials = mb_substr($username, 0, 2);
            }
            
            $randNum = ((int)$targetUser['id'] * 37) % 900 + 100; 
            return strtoupper($initials) . '-' . $randNum;
        }
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
        $dt = DateTime::createFromFormat('Y-m-d', $dateStr);
        if ($dt !== false) {
            $phpFormat = str_replace(['d', 'm', 'Y'], ['d', 'm', 'Y'], $formatPref);
            return $dt->format($phpFormat);
        }
        return $dateStr;
    }
}

if (!function_exists('generate_csv_export')) {
    function generate_csv_export(PDO $pdo, string $filenamePrefix = 'psd-export'): void
    {
        $userDateFormat = 'd/m/Y';
        if (isset($_SESSION['user_id'])) {
            $currentUser = get_current_user_data($pdo);
            if ($currentUser !== false && $currentUser !== null && isset($currentUser['date_format']) && is_string($currentUser['date_format'])) {
                $userDateFormat = $currentUser['date_format'];
            }
        }
        $colsStmt = $pdo->query("SELECT * FROM table_columns ORDER BY id ASC");
        /** @var array<int, array<string, mixed>> $columns */
        $columns = $colsStmt !== false ? $colsStmt->fetchAll(PDO::FETCH_ASSOC) : [];

        $queryGet = $_GET;
        /** @var array<mixed, mixed> $searchFilters */
        $searchFilters = isset($queryGet['filters']) && is_array($queryGet['filters']) ? $queryGet['filters'] : [];
        /** @var array<mixed, mixed> $dateFilters */
        $dateFilters = isset($queryGet['date_filters']) && is_array($queryGet['date_filters']) ? $queryGet['date_filters'] : [];

        $recordsStmt = $pdo->query("SELECT r.id, r.created_at, u.username FROM records r LEFT JOIN users u ON r.created_by = u.id ORDER BY r.id DESC");
        /** @var array<int, array<string, mixed>> $records */
        $records = $recordsStmt !== false ? $recordsStmt->fetchAll(PDO::FETCH_ASSOC) : [];

        $valuesStmt = $pdo->query("SELECT record_id, column_id, value_content FROM record_values");
        /** @var array<int, array<string, mixed>> $rawValues */
        $rawValues = $valuesStmt !== false ? $valuesStmt->fetchAll(PDO::FETCH_ASSOC) : [];
        
        /** @var array<int|string, array<int|string, string>> $recordValues */
        $recordValues = [];
        foreach ($rawValues as $val) {
            $recId = isset($val['record_id']) ? $val['record_id'] : 0;
            $colId = isset($val['column_id']) ? $val['column_id'] : 0;
            $valCont = isset($val['value_content']) && is_string($val['value_content']) ? $val['value_content'] : '';
            $recordValues[$recId][$colId] = $valCont;
        }

        header('Content-Type: text/csv; charset=utf-8');
        header('Content-Disposition: attachment; filename="' . $filenamePrefix . '-' . date('Y-m-d') . '.csv"');
        header('Cache-Control: no-store, no-cache, must-revalidate');

        $output = fopen('php://output', 'w');
        if ($output === false) {
            http_response_code(500);
            exit('Failed to open output stream.');
        }

        /** @var array<int, string> $headerRow */
        $headerRow = ['Record ID'];
        foreach ($columns as $col) {
            $colName = isset($col['column_name']) && is_string($col['column_name']) ? $col['column_name'] : '';
            $headerRow[] = $colName;
        }
        $headerRow[] = 'Created By';
        $headerRow[] = 'Date Added';
        fputcsv($output, $headerRow);

        foreach ($records as $rec) {
            $recId = isset($rec['id']) ? (int)$rec['id'] : 0;
            $match = true;
            if (!empty($searchFilters)) {
                foreach ($searchFilters as $colId => $searchTerm) {
                    if (is_string($searchTerm) && trim($searchTerm) !== '') {
                        $cellVal = $recordValues[$recId][$colId] ?? '';
                        if (stripos($cellVal, trim($searchTerm)) === false) {
                            $match = false;
                            break;
                        }
                    }
                }
            }
            if ($match && !empty($dateFilters)) {
                foreach ($dateFilters as $colId => $range) {
                    if (!is_array($range)) {
                        continue;
                    }
                    $fromInput = isset($range['from']) && is_string($range['from']) ? trim($range['from']) : '';
                    $toInput = isset($range['to']) && is_string($range['to']) ? trim($range['to']) : '';
                    $cellVal = trim($recordValues[$recId][$colId] ?? '');

                    if ($cellVal !== '') {
                        $cellTs = strtotime($cellVal);
                        if ($fromInput !== '') {
                            $fromTs = false;
                            $m = [];
                            if (preg_match('/^(\d{1,2})\/(\d{1,2})\/(\d{4})$/', $fromInput, $m)) {
                                $fromTs = strtotime("{$m[3]}-{$m[2]}-{$m[1]}");
                            } else {
                                $fromTs = strtotime($fromInput);
                            }
                            if ($fromTs !== false && $cellTs !== false) {
                                if ($cellTs < $fromTs) {
                                    $match = false;
                                    break;
                                }
                            } else {
                                $cleanFrom = str_replace(['/', '-'], '', $fromInput);
                                $cleanCell = str_replace('-', '', $cellVal);
                                if (stripos($cellVal, $fromInput) === false && stripos($cleanCell, $cleanFrom) === false) {
                                    $match = false;
                                    break;
                                }
                            }
                        }
                        if ($toInput !== '') {
                            $toTs = false;
                            $m = [];
                            if (preg_match('/^(\d{1,2})\/(\d{1,2})\/(\d{4})$/', $toInput, $m)) {
                                $toTs = strtotime("{$m[3]}-{$m[2]}-{$m[1]}");
                            } else {
                                $toTs = strtotime($toInput);
                            }
                            if ($toTs !== false && $cellTs !== false) {
                                if ($cellTs > $toTs) {
                                    $match = false;
                                    break;
                                }
                            } else {
                                $cleanTo = str_replace(['/', '-'], '', $toInput);
                                $cleanCell = str_replace('-', '', $cellVal);
                                if (stripos($cellVal, $toInput) === false && stripos($cleanCell, $cleanTo) === false) {
                                    $match = false;
                                    break;
                                }
                            }
                        }
                    } elseif ($fromInput !== '' || $toInput !== '') {
                        $match = false;
                        break;
                    }
                }
            }
            if ($match) {
                $row = ['#' . $recId];
                foreach ($columns as $col) {
                    $cId = isset($col['id']) ? $col['id'] : 0;
                    $rawVal = $recordValues[$recId][$cId] ?? '';
                    $dataType = isset($col['data_type']) && is_string($col['data_type']) ? $col['data_type'] : '';
                    $boolFormat = isset($col['boolean_display_format']) && is_string($col['boolean_display_format']) ? $col['boolean_display_format'] : 'yes_no';

                    if ($dataType === 'BOOLEAN') {
                        $row[] = format_boolean_value($rawVal, $boolFormat);
                    } elseif ($dataType === 'DATE') {
                        $row[] = format_display_date($rawVal, $userDateFormat);
                    } else {
                        $row[] = $rawVal;
                    }
                }
                $recUsername = isset($rec['username']) && is_string($rec['username']) ? $rec['username'] : 'User_Anon';
                $recCreatedAt = isset($rec['created_at']) && is_string($rec['created_at']) ? $rec['created_at'] : '';

                $row[] = $recUsername;
                $row[] = $recCreatedAt;
                fputcsv($output, $row);
            }
        }
        fclose($output);
        exit;
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

if (!function_exists('get_user_datetime_format')) {
    /**
     * @param array{date_format?: string, time_format?: string} $currentUser
     * @return string
     */
    function get_user_datetime_format(array $currentUser): string
    {
        $userDateFormat = isset($currentUser['date_format']) && is_string($currentUser['date_format']) ? $currentUser['date_format'] : 'd/m/Y';
        $userTimeFormat = isset($currentUser['time_format']) && is_string($currentUser['time_format']) ? $currentUser['time_format'] : '24';
      
        if ($userTimeFormat === '12') {
            return $userDateFormat . ' h:i A';
        } elseif ($userTimeFormat === '24') {
            return $userDateFormat . ' H:i';
        } else {
            return $userDateFormat;
        }
    }
}

if (!function_exists('record_matches_filters')) {
    /**
     * @param int|string $recordId
     * @param array<int|string, array<int|string, string>> $recordValuesMap
     * @param array<mixed, mixed> $searchFilters
     * @param array<mixed, mixed> $dateFilters
     * @return bool
     */
    function record_matches_filters($recordId, array $recordValuesMap, array $searchFilters, array $dateFilters): bool
    {
        if (!empty($searchFilters)) {
            foreach ($searchFilters as $colId => $searchTerm) {
                if (is_string($searchTerm) && trim($searchTerm) !== '') {
                    $cellVal = $recordValuesMap[$recordId][$colId] ?? '';
                    if (stripos($cellVal, trim($searchTerm)) === false) {
                        return false;
                    }
                }
            }
        }
      
        if (!empty($dateFilters)) {
            foreach ($dateFilters as $colId => $range) {
                if (!is_array($range)) {
                    continue;
                }
                $fromInput = isset($range['from']) && is_string($range['from']) ? trim($range['from']) : '';
                $toInput = isset($range['to']) && is_string($range['to']) ? trim($range['to']) : '';
                $cellVal = trim($recordValuesMap[$recordId][$colId] ?? '');
              
                if ($cellVal === '') {
                    if ($fromInput !== '' || $toInput !== '') {
                        return false;
                    }
                    continue;
                }

                $cellTs = strtotime($cellVal);

                if ($fromInput !== '') {
                    $fromTs = false;
                    $m = [];
                    if (preg_match('/^(\d{1,2})\/(\d{1,2})\/(\d{4})$/', $fromInput, $m)) {
                        $fromTs = strtotime("{$m[3]}-{$m[2]}-{$m[1]}");
                    } else {
                        $fromTs = strtotime($fromInput);
                    }

                    if ($fromTs !== false && $cellTs !== false) {
                        if ($cellTs < $fromTs) {
                            return false;
                        }
                    } else {
                        $cleanFrom = str_replace(['/', '-'], '', $fromInput);
                        $cleanCell = str_replace('-', '', $cellVal);
                        if (stripos($cellVal, $fromInput) === false && stripos($cleanCell, $cleanFrom) === false) {
                            return false;
                        }
                    }
                }

                if ($toInput !== '') {
                    $toTs = false;
                    $m = [];
                    if (preg_match('/^(\d{1,2})\/(\d{1,2})\/(\d{4})$/', $toInput, $m)) {
                        $toTs = strtotime("{$m[3]}-{$m[2]}-{$m[1]}");
                    } else {
                        $toTs = strtotime($toInput);
                    }

                    if ($toTs !== false && $cellTs !== false) {
                        if ($cellTs > $toTs) {
                            return false;
                        }
                    } else {
                        $cleanTo = str_replace(['/', '-'], '', $toInput);
                        $cleanCell = str_replace('-', '', $cellVal);
                        if (stripos($cellVal, $toInput) === false && stripos($cleanCell, $cleanTo) === false) {
                            return false;
                        }
                    }
                }
            }
        }
      
        return true;
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

if (!function_exists('get_user_time_prefs')) {
    /**
     * @param array{timezone?: string, date_format?: string, time_format?: string} $currentUser
     * @return array{0: string, 1: string}
     */
    function get_user_time_prefs(array $currentUser): array
    {
        $tz = isset($currentUser['timezone']) && is_string($currentUser['timezone']) ? $currentUser['timezone'] : 'UTC';
        return [
            $tz,
            get_user_datetime_format($currentUser),
        ];
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

if (!function_exists('has_exceeded_username_check_limit')) {
    function has_exceeded_username_check_limit(PDO $pdo): bool
    {
        $ip = isset($_SERVER['REMOTE_ADDR']) && is_string($_SERVER['REMOTE_ADDR']) ? $_SERVER['REMOTE_ADDR'] : '0.0.0.0';
        try {
            $stmt = $pdo->prepare("SELECT COUNT(*) FROM audit_logs WHERE ip_address = ? AND action = 'USERNAME_CHECK_ATTEMPT' AND created_at >= (NOW() - INTERVAL 24 HOUR)");
            $stmt->execute([$ip]);
            $count = $stmt->fetchColumn();
            return (int)$count >= 3;
        } catch (Exception $e) {
            return false;
        }
    }
}

if (!function_exists('log_username_check_attempt')) {
    function log_username_check_attempt(PDO $pdo): void
    {
        $ip = isset($_SERVER['REMOTE_ADDR']) && is_string($_SERVER['REMOTE_ADDR']) ? $_SERVER['REMOTE_ADDR'] : '0.0.0.0';
        try {
            $stmt = $pdo->prepare("INSERT INTO audit_logs (action, details, ip_address) VALUES ('USERNAME_CHECK_ATTEMPT', 'Checked username availability during user invite', ?)");
            $stmt->execute([$ip]);
        } catch (Exception $e) {
            // Suppress log failure exceptions
        }
    }
}
