<?php
declare(strict_types=1);
/**
 * MIGRATED FILE MAPPING
 * ---------------------
 * Original Old File: db/2fa_helpers.php
 * Migrated Date: 2026-08-05 11:35:00
 */

// db/2fa_helpers.php - Centralized Base32 decoding, TOTP math, and Google Authenticator verification helpers

if (!function_exists('base32_decode')) {
    /**
     * Decode a base32-encoded secret key string.
     *
     * @param string $input
     * @return string|false
     */
    function base32_decode(string $input)
    {
        $map = 'ABCDEFGHIJKLMNOPQRSTUVWXYZ234567';
        $input = strtoupper($input);
        $val = 0; 
        $bits = 0; 
        $output = '';
        
        $len = strlen($input);
        for ($i = 0; $i < $len; $i++) {
            $char = $input[$i];
            if ($char === '=') {
                break;
            }
            $pos = strpos($map, $char);
            if ($pos === false) {
                return false;
            }
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
    /**
     * Calculate a TOTP code given a decoded secret and time slice.
     *
     * @param string $key
     * @param int $timeSlice
     * @return string
     */
    function calculate_totp(string $key, int $timeSlice): string
    {
        $time = pack('N*', 0) . pack('N*', $timeSlice);
        $hash = hash_hmac('sha1', $time, $key, true);
        $lastByte = substr($hash, -1);
        $offset = ord($lastByte !== false ? $lastByte : "\0") & 0x0F;
        
        $hashBytes = function_exists('unpack') ? unpack('C*', $hash) : [];
        $h0 = isset($hashBytes[$offset + 1]) ? (int)$hashBytes[$offset + 1] : 0;
        $h1 = isset($hashBytes[$offset + 2]) ? (int)$hashBytes[$offset + 2] : 0;
        $h2 = isset($hashBytes[$offset + 3]) ? (int)$hashBytes[$offset + 3] : 0;
        $h3 = isset($hashBytes[$offset + 4]) ? (int)$hashBytes[$offset + 4] : 0;

        $code = (
            (($h0 & 0x7F) << 24) |
            (($h1 & 0xFF) << 16) |
            (($h2 & 0xFF) << 8) |
            ($h3 & 0xFF)
        ) % 1000000;

        return str_pad((string)$code, 6, '0', STR_PAD_LEFT);
    }
}

if (!function_exists('verify_google_2fa')) {
    /**
     * Verify a Google Authenticator TOTP code against a secret key.
     *
     * @param string $secret
     * @param string|int $code
     * @param int $discrepancy
     * @return bool
     */
    function verify_google_2fa(string $secret, $code, int $discrepancy = 1): bool
    {
        $decodedSecret = base32_decode($secret);
        if ($decodedSecret === false) {
            return false;
        }
        
        $codeStr = str_pad((string)$code, 6, '0', STR_PAD_LEFT);
        $currentTime = (int)floor(time() / 30);
        
        for ($i = -$discrepancy; $i <= $discrepancy; $i++) {
            $calculatedCode = calculate_totp($decodedSecret, $currentTime + $i);
            if (hash_equals($calculatedCode, $codeStr)) {
                return true;
            }
        }
        return false;
    }
}
