<?php
// includes/functions.php - Global utility and helper functions
if (!function_exists('obscure_name_ajax')) {
    function obscure_name_ajax($name) {
        if (empty($name)) return 'User_Anon';
        $len = strlen($name);
        if ($len <= 2) return $name[0] . '*';
        return substr($name, 0, 2) . str_repeat('*', max(1, $len - 2));
    }
}

if (!function_exists('format_user_display_name')) {
    function format_user_display_name($pdo, $target_user, $viewer_user = null) {
        if (!$target_user || !isset($target_user['id'])) {
            return 'User_Anon';
        }

        // Check if viewer has explicit permission or role to view full names
        $can_see_full_names = false;
        if ($viewer_user) {
            if (is_admin($pdo, $viewer_user['id']) || has_permission($pdo, 'view_user_full_names', $viewer_user['id'])) {
                $can_see_full_names = true;
            } else {
                // Automatically allow built-in/active moderators to see full names when logged in
                $tables_chk = $pdo->query("SELECT id FROM dynamic_tables")->fetchAll(PDO::FETCH_COLUMN);
                foreach ($tables_chk as $t_id) {
                    if (has_permission($pdo, 'moderate_table_' . $t_id, $viewer_user['id'])) {
                        $can_see_full_names = true;
                        break;
                    }
                }
            }
        }

        $first = trim($target_user['first_name'] ?? '');
        $surname = trim($target_user['surname'] ?? '');
        $username = trim($target_user['username'] ?? 'User');
        $mode = $target_user['attribution_display_mode'] ?? 'initials_random';

        // If viewer has administrative/moderator full name privilege, show Full Name
        if ($can_see_full_names) {
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
            if ($viewer_user !== null && !empty($viewer_user['id'])) {
                if ($first !== '' || $surname !== '') {
                    return trim("{$first} {$surname}");
                }
                return $username;
            } else {
                // Public guest view falls back to initials & random number
                $initials = '';
                if ($first !== '') $initials .= mb_substr($first, 0, 1);
                if ($surname !== '') $initials .= mb_substr($surname, 0, 1);
                if ($initials === '') $initials = mb_substr($username, 0, 2);
                
                $rand_num = (intval($target_user['id']) * 37) % 900 + 100; 
                return strtoupper($initials) . '-' . $rand_num;
            }
        } else {
            // Default: initials_random (Anonymous to everyone)
            $initials = '';
            if ($first !== '') $initials .= mb_substr($first, 0, 1);
            if ($surname !== '') $initials .= mb_substr($surname, 0, 1);
            if ($initials === '') $initials = mb_substr($username, 0, 2);
            
            $rand_num = (intval($target_user['id']) * 37) % 900 + 100; 
            return strtoupper($initials) . '-' . $rand_num;
        }
    }
}

if (!function_exists('format_boolean_value')) {
    function format_boolean_value($val, $format) {
        if ($val === null || $val === '') return 'N/A';
      
        $is_true = filter_var($val, FILTER_VALIDATE_BOOLEAN);
        switch ($format) {
            case 'true_false':
                return $is_true ? 'True' : 'False';
            case 'tick_cross':
                return $is_true ? '✔' : '✘';
            case 'yes_no':
            default:
                return $is_true ? 'Yes' : 'No';
        }
    }
}

if (!function_exists('sanitize_incoming_text')) {
    function sanitize_incoming_text($text) {
        if (empty($text)) return '';
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
        $text = preg_replace('/[\x{00A0}\x{200B}]/u', ' ', $text);
        return trim($text);
    }
}

if (!function_exists('format_user_time')) {
    function format_user_time($utc_timestamp, $timezone_str, $format_str) {
        if (empty($utc_timestamp)) return 'N/A';
        try {
            $dt = new DateTime($utc_timestamp, new DateTimeZone('UTC'));
            $dt->setTimezone(new DateTimeZone($timezone_str));
            return $dt->format($format_str);
        } catch (Exception $e) {
            return $utc_timestamp;
        }
    }
}

if (!function_exists('format_display_date')) {
    function format_display_date($date_str, $format_pref) {
        if (empty($date_str)) return '';
        $dt = DateTime::createFromFormat('Y-m-d', $date_str);
        if ($dt !== false) {
            $php_format = str_replace(['d', 'm', 'Y'], ['d', 'm', 'Y'], $format_pref);
            return $dt->format($php_format);
        }
        return $date_str;
    }
}

if (!function_exists('generate_csv_export')) {
    function generate_csv_export($pdo, $filename_prefix = 'psd-export') {
        $user_date_format = 'd/m/Y';
        if (isset($_SESSION['user_id'])) {
            $current_user = get_current_user_data($pdo);
            if ($current_user) {
                $user_date_format = $current_user['date_format'] ?? 'd/m/Y';
            }
        }
        $cols_stmt = $pdo->query("SELECT * FROM table_columns ORDER BY id ASC");
        $columns = $cols_stmt->fetchAll();
        $search_filters = $_GET['filters'] ?? [];
        $date_filters = $_GET['date_filters'] ?? [];
        $records_stmt = $pdo->query("SELECT r.id, r.created_at, u.username FROM records r LEFT JOIN users u ON r.created_by = u.id ORDER BY r.id DESC");
        $records = $records_stmt->fetchAll();
        $values_stmt = $pdo->query("SELECT record_id, column_id, value_content FROM record_values");
        $raw_values = $values_stmt->fetchAll();
        $record_values = [];
        foreach ($raw_values as $val) {
            $record_values[$val['record_id']][$val['column_id']] = $val['value_content'];
        }
        header('Content-Type: text/csv; charset=utf-8');
        header('Content-Disposition: attachment; filename="' . $filename_prefix . '-' . date('Y-m-d') . '.csv"');
        $output = fopen('php://output', 'w');
        $header_row = ['Record ID'];
        foreach ($columns as $col) {
            $header_row[] = $col['column_name'];
        }
        $header_row[] = 'Created By';
        $header_row[] = 'Date Added';
        fputcsv($output, $header_row);
        foreach ($records as $rec) {
            $match = true;
            if (!empty($search_filters)) {
                foreach ($search_filters as $col_id => $search_term) {
                    if (!empty(trim($search_term))) {
                        $cell_val = $record_values[$rec['id']][$col_id] ?? '';
                        if (stripos($cell_val, trim($search_term)) === false) {
                            $match = false;
                            break;
                        }
                    }
                }
            }
            if ($match && !empty($date_filters)) {
                foreach ($date_filters as $col_id => $range) {
                    $from_input = trim($range['from'] ?? '');
                    $to_input = trim($range['to'] ?? '');
                    $cell_val = trim($record_values[$rec['id']][$col_id] ?? '');
                    if (!empty($cell_val)) {
                        $cell_ts = strtotime($cell_val);
                        if (!empty($from_input)) {
                            $from_ts = false;
                            if (preg_match('/^(\d{1,2})\/(\d{1,2})\/(\d{4})$/', $from_input, $m)) {
                                $from_ts = strtotime("{$m[3]}-{$m[2]}-{$m[1]}");
                            } else {
                                $from_ts = strtotime($from_input);
                            }
                            if ($from_ts && $cell_ts) {
                                if ($cell_ts < $from_ts) { $match = false; break; }
                            } else {
                                $clean_from = str_replace(['/', '-'], '', $from_input);
                                $clean_cell = str_replace('-', '', $cell_val);
                                if (stripos($cell_val, $from_input) === false && stripos($clean_cell, $clean_from) === false) {
                                    $match = false; break;
                                }
                            }
                        }
                        if (!empty($to_input)) {
                            $to_ts = false;
                            if (preg_match('/^(\d{1,2})\/(\d{1,2})\/(\d{4})$/', $to_input, $m)) {
                                $to_ts = strtotime("{$m[3]}-{$m[2]}-{$m[1]}");
                            } else {
                                $to_ts = strtotime($to_input);
                            }
                            if ($to_ts && $cell_ts) {
                                if ($cell_ts > $to_ts) { $match = false; break; }
                            } else {
                                $clean_to = str_replace(['/', '-'], '', $to_input);
                                $clean_cell = str_replace('-', '', $cell_val);
                                if (stripos($cell_val, $to_input) === false && stripos($clean_cell, $clean_to) === false) {
                                    $match = false; break;
                                }
                            }
                        }
                    } elseif (!empty($from_input) || !empty($to_input)) {
                        $match = false;
                        break;
                    }
                }
            }
            if ($match) {
                $row = ['#' . $rec['id']];
                foreach ($columns as $col) {
                    $raw_val = $record_values[$rec['id']][$col['id']] ?? '';
                    if (($col['data_type'] ?? '') === 'BOOLEAN') {
                        $row[] = format_boolean_value($raw_val, $col['boolean_display_format'] ?? 'yes_no');
                    } elseif (($col['data_type'] ?? '') === 'DATE') {
                        $row[] = format_display_date($raw_val, $user_date_format);
                    } else {
                        $row[] = $raw_val;
                    }
                }
                $row[] = $rec['username'] ?? 'User_Anon';
                $row[] = $rec['created_at'];
                fputcsv($output, $row);
            }
        }
        fclose($output);
        exit;
    }
}

if (!function_exists('get_setting')) {
    function get_setting($pdo, $key, $default = '') {
        try {
            $stmt = $pdo->prepare("SELECT setting_value FROM site_settings WHERE setting_key = ?");
            $stmt->execute([$key]);
            $val = $stmt->fetchColumn();
            return ($val !== false && $val !== null) ? $val : $default;
        } catch (Exception $e) {
            return $default;
        }
    }
}

if (!function_exists('get_user_datetime_format')) {
    function get_user_datetime_format($current_user) {
        $user_date_format = $current_user['date_format'] ?? 'd/m/Y';
        $user_time_format = $current_user['time_format'] ?? '24';
      
        if ($user_time_format === '12') {
            return $user_date_format . ' h:i A';
        } elseif ($user_time_format === '24') {
            return $user_date_format . ' H:i';
        } else {
            return $user_date_format;
        }
    }
}

if (!function_exists('record_matches_filters')) {
    function record_matches_filters($record_id, $record_values_map, $search_filters, $date_filters) {
        if (!empty($search_filters)) {
            foreach ($search_filters as $col_id => $search_term) {
                if (!empty(trim($search_term))) {
                    $cell_val = $record_values_map[$record_id][$col_id] ?? '';
                    if (stripos($cell_val, trim($search_term)) === false) {
                        return false;
                    }
                }
            }
        }
      
        if (!empty($date_filters)) {
            foreach ($date_filters as $col_id => $range) {
                $from_input = trim($range['from'] ?? '');
                $to_input = trim($range['to'] ?? '');
                $cell_val = trim($record_values_map[$record_id][$col_id] ?? '');
              
                if (empty($cell_val)) {
                    if (!empty($from_input) || !empty($to_input)) return false;
                    continue;
                }

                $cell_ts = strtotime($cell_val);

                if (!empty($from_input)) {
                    $from_ts = false;
                    if (preg_match('/^(\d{1,2})\/(\d{1,2})\/(\d{4})$/', $from_input, $m)) {
                        $from_ts = strtotime("{$m[3]}-{$m[2]}-{$m[1]}");
                    } else {
                        $from_ts = strtotime($from_input);
                    }

                    if ($from_ts && $cell_ts) {
                        if ($cell_ts < $from_ts) return false;
                    } else {
                        $clean_from = str_replace(['/', '-'], '', $from_input);
                        $clean_cell = str_replace('-', '', $cell_val);
                        if (stripos($cell_val, $from_input) === false && stripos($clean_cell, $clean_from) === false) {
                            return false;
                        }
                    }
                }

                if (!empty($to_input)) {
                    $to_ts = false;
                    if (preg_match('/^(\d{1,2})\/(\d{1,2})\/(\d{4})$/', $to_input, $m)) {
                        $to_ts = strtotime("{$m[3]}-{$m[2]}-{$m[1]}");
                    } else {
                        $to_ts = strtotime($to_input);
                    }

                    if ($to_ts && $cell_ts) {
                        if ($cell_ts > $to_ts) return false;
                    } else {
                        $clean_to = str_replace(['/', '-'], '', $to_input);
                        $clean_cell = str_replace('-', '', $cell_val);
                        if (stripos($cell_val, $to_input) === false && stripos($clean_cell, $clean_to) === false) {
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
    function is_module_enabled($pdo, $module_key) {
        static $module_cache = [];
        if (array_key_exists($module_key, $module_cache)) {
            return $module_cache[$module_key];
        }
        try {
            $stmt = $pdo->prepare("SELECT setting_value FROM site_settings WHERE setting_key = ?");
            $stmt->execute(['module_' . $module_key . '_enabled']);
            $val = $stmt->fetchColumn();
          
            if ($module_key === 'leaderboard') {
                $users_enabled = is_module_enabled($pdo, 'users');
                if (!$users_enabled) {
                    return false;
                }
            }
          
            $enabled = ($val === false || $val === null) ? true : (intval($val) === 1);
            $module_cache[$module_key] = $enabled;
            return $enabled;
        } catch (Exception $e) {
            $module_cache[$module_key] = true;
            return true;
        }
    }
}

if (!function_exists('get_user_time_prefs')) {
    function get_user_time_prefs(array $current_user): array {
        return [
            $current_user['timezone'] ?? 'UTC',
            get_user_datetime_format($current_user),
        ];
    }
}

if (!function_exists('get_active_language')) {
    function get_active_language(): string {
        if (session_status() === PHP_SESSION_NONE) {
            session_start();
        }

        if (!empty($_SESSION['lang'])) {
            return preg_replace('/[^a-z_]/', '', strtolower($_SESSION['lang']));
        }

        $pdo = $GLOBALS['pdo'] ?? null;

        if ($pdo instanceof PDO && function_exists('get_current_user_data')) {
            $user = get_current_user_data($pdo);
            if (!empty($user['language'])) {
                return preg_replace('/[^a-z_]/', '', strtolower($user['language']));
            }
        }

        if ($pdo instanceof PDO && function_exists('get_setting')) {
            $site = get_setting($pdo, 'default_language', 'en');
            if (!empty($site)) {
                return preg_replace('/[^a-z_]/', '', strtolower($site));
            }
        }

        return 'en';
    }
}

if (!function_exists('__')) {
    function __(string $key, array $replace = []): string {
        static $catalogue = null;
        static $loaded_lang = null;

        $lang = get_active_language();

        if ($catalogue === null || $loaded_lang !== $lang) {
            $safe = preg_replace('/[^a-z_]/', '', $lang) ?: 'en';
            $path = __DIR__ . '/../lang/' . $safe . '.php';
            if (!is_file($path)) {
                $path = __DIR__ . '/../lang/en.php';
                $safe = 'en';
            }
            $catalogue = is_file($path) ? include $path : [];
            if (!is_array($catalogue)) {
                $catalogue = [];
            }
            $loaded_lang = $safe;
        }

        $text = $catalogue[$key] ?? $key;

        foreach ($replace as $k => $v) {
            $text = str_replace(':' . $k, (string) $v, $text);
        }

        return $text;
    }
}

if (!function_exists('set_language')) {
    function set_language(string $code): void {
        if (session_status() === PHP_SESSION_NONE) {
            session_start();
        }
        $_SESSION['lang'] = preg_replace('/[^a-z_]/', '', strtolower($code)) ?: 'en';
    }
}

if (!function_exists('get_schema_version')) {
    function get_schema_version(PDO $pdo): int {
        $val = get_setting($pdo, 'schema_version', '0');
        return max(0, (int) $val);
    }
}

if (!function_exists('set_schema_version')) {
    function set_schema_version(PDO $pdo, int $version): void {
        $version = max(0, $version);
        $stmt = $pdo->prepare(
            "INSERT INTO site_settings (setting_key, setting_value) VALUES ('schema_version', ?)
             ON DUPLICATE KEY UPDATE setting_value = ?"
        );
        $stmt->execute([(string) $version, (string) $version]);
    }
}

if (!function_exists('adjust_user_points')) {
    function adjust_user_points(PDO $pdo, int $user_id, int $amount): void {
        if ($user_id <= 0 || $amount === 0) return;
        if ($amount > 0) {
            $stmt = $pdo->prepare("UPDATE users SET points = points + ? WHERE id = ?");
            $stmt->execute([$amount, $user_id]);
        } else {
            $abs_amount = abs($amount);
            $stmt = $pdo->prepare("UPDATE users SET points = GREATEST(0, points - ?) WHERE id = ?");
            $stmt->execute([$abs_amount, $user_id]);
        }
    }
}

if (!function_exists('has_exceeded_username_check_limit')) {
    function has_exceeded_username_check_limit(PDO $pdo) {
        $ip = $_SERVER['REMOTE_ADDR'] ?? '0.0.0.0';
        try {
            $stmt = $pdo->prepare("SELECT COUNT(*) FROM audit_logs WHERE ip_address = ? AND action = 'USERNAME_CHECK_ATTEMPT' AND created_at >= (NOW() - INTERVAL 24 HOUR)");
            $stmt->execute([$ip]);
            return $stmt->fetchColumn() >= 3;
        } catch (Exception $e) {
            return false;
        }
    }
}

if (!function_exists('log_username_check_attempt')) {
    function log_username_check_attempt(PDO $pdo) {
        $ip = $_SERVER['REMOTE_ADDR'] ?? '0.0.0.0';
        try {
            $stmt = $pdo->prepare("INSERT INTO audit_logs (action, details, ip_address) VALUES ('USERNAME_CHECK_ATTEMPT', 'Checked username availability during user invite', ?)");
            $stmt->execute([$ip]);
        } catch (Exception $e) {}
    }
}
