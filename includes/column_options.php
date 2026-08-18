<?php
declare(strict_types=1);

/**
 * Helpers for SELECT / multi-select column options and integer bounds.
 */

/**
 * @return list<string>
 */
function parse_column_options(mixed $raw): array
{
    if (!is_string($raw) || trim($raw) === '') {
        return [];
    }
    $parts = preg_split('/\r\n|\r|\n/', $raw) ?: [];
    $out = [];
    foreach ($parts as $part) {
        $opt = trim((string) $part);
        if ($opt !== '' && !in_array($opt, $out, true)) {
            $out[] = $opt;
        }
    }
    return $out;
}

function flatten_posted_column_value(mixed $val): string
{
    if (is_array($val)) {
        $bits = [];
        foreach ($val as $item) {
            if (!is_scalar($item)) {
                continue;
            }
            $t = trim((string) $item);
            if ($t !== '') {
                $bits[] = $t;
            }
        }
        return implode(', ', $bits);
    }
    if (is_scalar($val)) {
        return trim((string) $val);
    }
    return '';
}

/**
 * @param list<string> $options
 */
function column_values_are_allowed(string $stored, array $options, bool $multiple): bool
{
    if ($stored === '' || $options === []) {
        return $stored === '';
    }
    $chosen = $multiple
        ? array_map('trim', explode(',', $stored))
        : [$stored];
    foreach ($chosen as $item) {
        if ($item === '') {
            continue;
        }
        if (!in_array($item, $options, true)) {
            return false;
        }
    }
    return true;
}

/**
 * @return list<string>
 */
function explode_stored_column_values(string $stored): array
{
    if ($stored === '') {
        return [];
    }
    $parts = array_map('trim', explode(',', $stored));
    $out = [];
    foreach ($parts as $p) {
        if ($p !== '') {
            $out[] = $p;
        }
    }
    return $out;
}
