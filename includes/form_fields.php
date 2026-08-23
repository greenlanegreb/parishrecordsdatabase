<?php
declare(strict_types=1);

/**
 * Field-level form errors for screen readers (aria-invalid / aria-describedby).
 *
 * @param array<string|int, string> $errors
 */
function field_has_error(array $errors, string|int $key): bool
{
    return isset($errors[$key]) && is_string($errors[$key]) && $errors[$key] !== '';
}

/**
 * @param array<string|int, string> $errors
 */
function field_invalid_attr(array $errors, string|int $key, string $describedById): string
{
    if (!field_has_error($errors, $key)) {
        return '';
    }
    return ' aria-invalid="true" aria-describedby="' . htmlspecialchars($describedById, ENT_QUOTES, 'UTF-8') . '"';
}

/**
 * @param array<string|int, string> $errors
 */
function field_error_html(array $errors, string|int $key, string $describedById): string
{
    if (!field_has_error($errors, $key)) {
        return '';
    }
    $msg = htmlspecialchars((string) $errors[$key], ENT_QUOTES, 'UTF-8');
    return '<div class="invalid-feedback d-block" id="' . htmlspecialchars($describedById, ENT_QUOTES, 'UTF-8') . '" role="alert">' . $msg . '</div>';
}

function remember_field_error(string|int $key, string $message): void
{
    if (!isset($_SESSION['field_errors']) || !is_array($_SESSION['field_errors'])) {
        $_SESSION['field_errors'] = [];
    }
    $_SESSION['field_errors'][$key] = $message;
    $_SESSION['error'] = $message;
}

/**
 * @return array<string|int, string>
 */
function consume_field_errors(): array
{
    $errors = $_SESSION['field_errors'] ?? [];
    unset($_SESSION['field_errors']);
    if (!is_array($errors)) {
        return [];
    }
    $out = [];
    foreach ($errors as $k => $v) {
        if (is_string($v) && $v !== '') {
            $out[$k] = $v;
        }
    }
    return $out;
}
