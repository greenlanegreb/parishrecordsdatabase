<?php
declare(strict_types=1);

/**
 * Break-glass access to /update-database without Admin having to create files by hand.
 */
function prd_root_path(): string
{
    if (defined('ROOT_PATH') && is_string(ROOT_PATH) && ROOT_PATH !== '') {
        return rtrim(ROOT_PATH, '/');
    }
    return dirname(__DIR__);
}

function prd_pending_migrate_file(): string
{
    return prd_root_path() . '/storage/prd_pending_migrate';
}

function prd_manual_emergency_file(): string
{
    return prd_root_path() . '/db/ALLOW_EMERGENCY_MIGRATE';
}

function prd_emergency_migrate_allowed(): bool
{
    return is_file(prd_pending_migrate_file()) || is_file(prd_manual_emergency_file());
}

function prd_mark_pending_migrate(): void
{
    $dir = dirname(prd_pending_migrate_file());
    if (!is_dir($dir)) {
        @mkdir($dir, 0750, true);
    }
    @file_put_contents(prd_pending_migrate_file(), gmdate('c') . "\n");
}

function prd_clear_pending_migrate(): void
{
    $f = prd_pending_migrate_file();
    if (is_file($f)) {
        @unlink($f);
    }
}
