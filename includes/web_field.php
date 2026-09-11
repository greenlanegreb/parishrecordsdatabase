<?php
declare(strict_types=1);

/**
 * EMAIL / URL dynamic columns — validate on write, safe links on display.
 */
function prd_normalize_email(string $raw): string
{
    $v = trim($raw);
    $v = preg_replace('/\s+/', '', $v) ?? $v;
    return $v;
}

function prd_valid_email(string $raw): bool
{
    $v = prd_normalize_email($raw);
    if ($v === '' || strlen($v) > 254) {
        return false;
    }
    return filter_var($v, FILTER_VALIDATE_EMAIL) !== false;
}

function prd_normalize_url(string $raw): string
{
    $v = trim($raw);
    if ($v === '') {
        return '';
    }
    if (!preg_match('#^[a-z][a-z0-9+.-]*:#i', $v)) {
        $v = 'https://' . $v;
    }
    return $v;
}

function prd_valid_url(string $raw): bool
{
    $v = prd_normalize_url($raw);
    if ($v === '' || strlen($v) > 2000) {
        return false;
    }
    if (filter_var($v, FILTER_VALIDATE_URL) === false) {
        return false;
    }
    $p = parse_url($v);
    if (!is_array($p)) {
        return false;
    }
    $scheme = strtolower((string) ($p['scheme'] ?? ''));
    if ($scheme !== 'http' && $scheme !== 'https') {
        return false;
    }
    $host = (string) ($p['host'] ?? '');
    if ($host === '' || str_contains($host, ' ') || isset($p['user']) || isset($p['pass'])) {
        return false;
    }
    return true;
}

/**
 * @param array<string, mixed> $column
 */
function prd_url_opens_new_tab(array $column): bool
{
    $raw = $column['field_options'] ?? '';
    if (is_string($raw) && $raw !== '' && ($raw[0] ?? '') === '{') {
        $j = json_decode($raw, true);
        if (is_array($j) && !empty($j['new_tab'])) {
            return true;
        }
    }
    return false;
}

/**
 * Safe HTML for a table cell. Escaped. Empty string if invalid.
 */
function prd_render_web_cell(string $dataType, string $value, array $column = [], bool $viewerLoggedIn = false): string
{
    $type = strtoupper($dataType);
    if ($type === 'EMAIL') {
        $email = prd_normalize_email($value);
        if (!prd_valid_email($email)) {
            return htmlspecialchars($value, ENT_QUOTES, 'UTF-8');
        }
        $safe = htmlspecialchars($email, ENT_QUOTES, 'UTF-8');
        if ($viewerLoggedIn) {
            return '<a href="mailto:' . $safe . '">' . $safe . '</a>';
        }
        $token = htmlspecialchars(rtrim(strtr(base64_encode($email), '+/', '-_'), '='), ENT_QUOTES, 'UTF-8');
        $labelPlain = preg_replace('/@/', ' [at] ', $email) ?? $email;
        $label = htmlspecialchars($labelPlain, ENT_QUOTES, 'UTF-8');
        $aria = htmlspecialchars('Email ' . $labelPlain . '. Opens your email program.', ENT_QUOTES, 'UTF-8');
        return '<button type="button" class="btn btn-link p-0 align-baseline prd-email-btn" data-e="' . $token . '" aria-label="' . $aria . '">' . $label . '</button>';
    }
    if ($type === 'URL') {
        $url = prd_normalize_url($value);
        if (!prd_valid_url($url)) {
            return htmlspecialchars($value, ENT_QUOTES, 'UTF-8');
        }
        $safe = htmlspecialchars($url, ENT_QUOTES, 'UTF-8');
        $blank = prd_url_opens_new_tab($column)
            ? ' target="_blank" rel="noopener noreferrer"'
            : '';
        $ariaBlank = $blank !== ''
            ? ' aria-label="' . htmlspecialchars((string) $url . ' (' . ((__('web_field.new_tab') !== 'web_field.new_tab') ? __('web_field.new_tab') : 'opens in a new tab') . ')', ENT_QUOTES, 'UTF-8') . '"'
            : '';
        return '<a class="prd-url-link" href="' . $safe . '"' . $blank . $ariaBlank . '>' . $safe . '</a>';
    }
    return htmlspecialchars($value, ENT_QUOTES, 'UTF-8');
}
