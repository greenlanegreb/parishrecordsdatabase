<?php
declare(strict_types=1);

/**
 * Tokens in session / GET: numeric column ids, plus created_by and created_at.
 *
 * @return list<string>|null  null = nothing saved yet (show everything)
 */
function column_visibility_tokens(int $tableId): ?array
{
    if (isset($_GET['cols']) && is_array($_GET['cols'])) {
        $tokens = [];
        foreach ($_GET['cols'] as $raw) {
            if (!is_string($raw) && !is_int($raw)) {
                continue;
            }
            $token = trim((string) $raw);
            if ($token !== '') {
                $tokens[] = $token;
            }
        }
        if (!isset($_SESSION['visible_cols']) || !is_array($_SESSION['visible_cols'])) {
            $_SESSION['visible_cols'] = [];
        }
        $_SESSION['visible_cols'][$tableId] = $tokens;
        return $tokens;
    }
    if (isset($_SESSION['visible_cols'][$tableId]) && is_array($_SESSION['visible_cols'][$tableId])) {
        $tokens = [];
        foreach ($_SESSION['visible_cols'][$tableId] as $raw) {
            $tokens[] = (string) $raw;
        }
        return $tokens;
    }
    return null;
}

function column_shown_on_list(array $col): bool
{
    return !array_key_exists('show_in_list', $col) || (int) $col['show_in_list'] === 1;
}

function column_shown_on_record(array $col): bool
{
    return !array_key_exists('show_in_record', $col) || (int) $col['show_in_record'] === 1;
}

/**
 * @param array<int, array<string, mixed>> $columns
 * @return array<int, array<string, mixed>>
 */
function resolve_visible_columns(array $columns, int $tableId): array
{
    $list = [];
    foreach ($columns as $col) {
        if (column_shown_on_list($col)) {
            $list[] = $col;
        }
    }
    $columns = $list;
    $allIds = [];
    foreach ($columns as $col) {
        $cid = isset($col['id']) ? (int) $col['id'] : 0;
        if ($cid > 0) {
            $allIds[] = $cid;
        }
    }
    $tokens = column_visibility_tokens($tableId);
    if ($tokens === null) {
        return apply_column_display_order($columns);
    }
    $allowed = [];
    foreach ($tokens as $token) {
        if (ctype_digit($token)) {
            $allowed[] = (int) $token;
        }
    }
    $allowed = array_values(array_intersect($allowed, $allIds));
    if ($allowed === []) {
        return [];
    }
    $visible = [];
    foreach ($columns as $col) {
        $cid = isset($col['id']) ? (int) $col['id'] : 0;
        if (in_array($cid, $allowed, true)) {
            $visible[] = $col;
        }
    }
    return apply_column_display_order($visible);
}

/**
 * Honour dragged column order from col_order[] (export / print / list).
 *
 * @param array<int, array<string, mixed>> $columns
 * @param list<mixed>|null $orderIds
 * @return array<int, array<string, mixed>>
 */
function apply_column_display_order(array $columns, ?array $orderIds = null): array
{
    if ($orderIds === null && isset($_GET['col_order']) && is_array($_GET['col_order'])) {
        $orderIds = $_GET['col_order'];
    }
    if (!is_array($orderIds) || $orderIds === []) {
        return $columns;
    }
    $byId = [];
    foreach ($columns as $col) {
        $cid = isset($col['id']) ? (int) $col['id'] : 0;
        if ($cid > 0) {
            $byId[$cid] = $col;
        }
    }
    $out = [];
    foreach ($orderIds as $raw) {
        $id = (int) $raw;
        if (isset($byId[$id])) {
            $out[] = $byId[$id];
            unset($byId[$id]);
        }
    }
    foreach ($byId as $col) {
        $out[] = $col;
    }
    return $out;
}

/**
 * @param array<int, array<string, mixed>> $columns
 * @return list<int>
 */
function visible_column_ids(array $columns): array
{
    $ids = [];
    foreach ($columns as $col) {
        $cid = isset($col['id']) ? (int) $col['id'] : 0;
        if ($cid > 0) {
            $ids[] = $cid;
        }
    }
    return $ids;
}

function show_created_by_column(int $tableId): bool
{
    $tokens = column_visibility_tokens($tableId);
    return $tokens === null || in_array('created_by', $tokens, true);
}

function show_created_at_column(int $tableId): bool
{
    $tokens = column_visibility_tokens($tableId);
    return $tokens === null || in_array('created_at', $tokens, true);
}
