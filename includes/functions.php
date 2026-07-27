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
        // If empty or null
        if ($val === null || $val === '') return 'N/A';
        
        // Normalize boolean integer/string to true/false
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

        // Strip any accidental HTML tags/scripts
        $text = strip_tags($text);

        // Normalize smart/curly quotes to straight ASCII quotes
        $search = [
            "\xC2\xAB", // « (left guillemet)
            "\xC2\xBB", // » (right guillemet)
            "\xE2\x80\x98", // ‘ (left single quote)
            "\xE2\x80\x99", // ’ (right single quote)
            "\xE2\x80\x9C", // “ (left double quote)
            "\xE2\x80\x9D", // ” (right double quote)
            "\xE2\x80\x93", // – (en dash)
            "\xE2\x80\x94", // — (em dash)
            "\xE2\x80\xA6"  // … (ellipsis)
        ];
        $replace = [
            '"', '"', "'", "'", '"', '"', '-', '-', '...'
        ];
        $text = str_replace($search, $replace, $text);

        // Normalize non-breaking spaces and invisible control spaces to standard spaces
        $text = preg_replace('/[\x{00A0}\x{200B}]/u', ' ', $text);

        // Trim extra whitespace
        return trim($text);
    }
}

// Helper function to format UTC timestamps according to user timezone and format preference
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

// Helper function to format stored ISO dates (YYYY-MM-DD) into user's preferred format
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

/**
 * Centralized CSV Export Utility
 * Handles dynamic column mapping, search/date filtering, boolean & date formatting,
 * and direct browser output download headers.
 */
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

/**
 * Retrieve a global site setting value by its key.
 */
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

/**
 * Compile the PHP date/time format string based on user preferences.
 */
if (!function_exists('get_user_datetime_format')) {
    function get_user_datetime_format($current_user) {
        $user_date_format = $current_user['date_format'] ?? 'd/m/Y';
        $user_time_format = $current_user['time_format'] ?? '24';
        
        if ($user_time_format === '12') {
            return $user_date_format . ' h:i A';
        } elseif ($user_time_format === '24') {
            return $user_date_format . ' H:i';
        } else {
            return $user_date_format; // Date only
        }
    }
}

/**
 * Evaluates whether a record matches the given text/boolean and date range filters.
 */
if (!function_exists('record_matches_filters')) {
    function record_matches_filters($record_id, $record_values_map, $search_filters, $date_filters) {
        // 1. Evaluate standard text/boolean filters
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
        
        // 2. Evaluate dynamic date range filters
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

/**
 * Generate or retrieve the active session CSRF token.
 */
if (!function_exists('generate_csrf_token')) {
    function generate_csrf_token() {
        if (session_status() === PHP_SESSION_NONE) {
            session_start();
        }
        if (empty($_SESSION['csrf_token'])) {
            $_SESSION['csrf_token'] = bin2hex(random_bytes(32));
        }
        return $_SESSION['csrf_token'];
    }
}

/**
 * Output a hidden input field containing the CSRF token.
 */
if (!function_exists('csrf_field')) {
    function csrf_field() {
        $token = generate_csrf_token();
        return '<input type="hidden" name="csrf_token" value="' . htmlspecialchars($token, ENT_QUOTES, 'UTF-8') . '">';
    }
}

/**
 * Validate an incoming CSRF token against the session.
 */
if (!function_exists('verify_csrf_token')) {
    function verify_csrf_token() {
        if (session_status() === PHP_SESSION_NONE) {
            session_start();
        }
        $submitted_token = $_POST['csrf_token'] ?? '';
        if (empty($_SESSION['csrf_token']) || empty($submitted_token) || !hash_equals($_SESSION['csrf_token'], $submitted_token)) {
            http_response_code(403);
            error_log("CSRF token validation failed from IP: " . ($_SERVER['REMOTE_ADDR'] ?? 'Unknown'));
            exit('Security Error: Invalid or missing CSRF token.');
        }
    }
}
