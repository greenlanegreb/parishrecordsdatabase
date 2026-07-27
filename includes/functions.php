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
                    $from = trim($range['from'] ?? '');
                    $to = trim($range['to'] ?? '');
                    $cell_val = $record_values[$rec['id']][$col_id] ?? '';
                    if (!empty($cell_val)) {
                        if (!empty($from) && $cell_val < $from) { $match = false; break; }
                        if (!empty($to) && $cell_val > $to) { $match = false; break; }
                    } elseif (!empty($from) || !empty($to)) {
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
                $from = trim($range['from'] ?? '');
                $to = trim($range['to'] ?? '');
                $cell_val = $record_values_map[$record_id][$col_id] ?? '';
               
                if (!empty($cell_val)) {
                    if (!empty($from) && $cell_val < $from) { return false; }
                    if (!empty($to) && $cell_val > $to) { return false; }
                } elseif (!empty($from) || !empty($to)) {
                    return false;
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
