<?php
// db/2fa_helpers.php - Centralized Base32 decoding, TOTP math, and Google Authenticator verification helpers

if (!function_exists('base32_decode')) {
    function base32_decode($input) {
        $map = 'ABCDEFGHIJKLMNOPQRSTUVWXYZ234567';
        $input = strtoupper($input);
        $val = 0; $bits = 0; $output = '';
        for ($i = 0; $i < strlen($input); $i++) {
            $char = $input[$i];
            if ($char === '=') break;
            $pos = strpos($map, $char);
            if ($pos === false) return false;
            $val = ($val << 5) | $pos;
            $bits += 5;
            if ($bits >= 8) {
                $bits -= 8;
                $output .= chr(($val >> $bits) & 0xFF);
            }
        }
        return $output;
    }
}

if (!function_exists('calculate_totp')) {
    function calculate_totp($key, $timeSlice) {
        $time = pack('N*', 0) . pack('N*', $timeSlice);
        $hash = hash_hmac('sha1', $time, $key, true);
        $offset = ord(substr($hash, -1)) & 0x0F;
        $code = (
            ((ord($hash[$offset]) & 0x7F) << 24) |
            ((ord($hash[$offset + 1]) & 0xFF) << 16) |
            ((ord($hash[$offset + 2]) & 0xFF) << 8) |
            (ord($hash[$offset + 3]) & 0xFF)
        ) % 1000000;
        return str_pad($code, 6, '0', STR_PAD_LEFT);
    }
}

if (!function_exists('verify_google_2fa')) {
    function verify_google_2fa($secret, $code, $discrepancy = 1) {
        $decodedSecret = base32_decode($secret);
        if ($decodedSecret === false) return false;
        $currentTime = floor(time() / 30);
        for ($i = -$discrepancy; $i <= $discrepancy; $i++) {
            $calculatedCode = calculate_totp($decodedSecret, $currentTime + $i);
            if (hash_equals($calculatedCode, str_pad($code, 6, '0', STR_PAD_LEFT))) {
                return true;
            }
        }
        return false;
    }
}
