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
