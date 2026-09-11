<?php
declare(strict_types=1);

/**
 * @return array{
 *   php:string,
 *   script:string,
 *   command:string,
 *   crontab:string,
 *   script_ok:bool,
 *   php_ok:bool,
 *   exec_ok:bool,
 *   notes:list<string>
 * }
 */
function prd_cron_detect(string $projectRoot): array
{
    $notes = [];
    $php = defined('PHP_BINARY') && PHP_BINARY !== '' ? PHP_BINARY : '/usr/bin/php';
    if (!is_file($php)) {
        $which = '';
        if (function_exists('exec')) {
            $out = [];
            @exec('command -v php 2>/dev/null', $out);
            $which = isset($out[0]) && is_string($out[0]) ? trim($out[0]) : '';
        }
        if ($which !== '' && is_file($which)) {
            $php = $which;
            $notes[] = 'php_path_fallback';
        }
    }
    $phpOk = is_file($php) && is_executable($php);

    $script = rtrim($projectRoot, '/') . '/db/cron_token_cleanup.php';
    $scriptOk = is_file($script) && is_readable($script);

    $execOk = false;
    if ($phpOk && function_exists('exec') && !in_array('exec', array_map('trim', explode(',', (string) ini_get('disable_functions'))), true)) {
        $out = [];
        $code = 1;
        @exec(escapeshellarg($php) . ' -v 2>/dev/null', $out, $code);
        $execOk = ($code === 0);
    } else {
        $notes[] = 'exec_disabled';
    }

    $cmd = $php . ' ' . $script;
    $crontab = '0 3 * * * ' . $cmd . ' >/dev/null 2>&1';

    return [
        'php' => $php,
        'script' => $script,
        'command' => $cmd,
        'crontab' => $crontab,
        'script_ok' => $scriptOk,
        'php_ok' => $phpOk,
        'exec_ok' => $execOk,
        'notes' => $notes,
    ];
}
