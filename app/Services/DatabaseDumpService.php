<?php
declare(strict_types=1);

namespace App\Services;

use PDO;
use RuntimeException;

/**
 * Shared SQL dump writer used by the download button and the private last-good archive.
 */
class DatabaseDumpService
{
    public function __construct(private PDO $pdo)
    {
    }

    public function writeToStream($out, int $schemaVersion, string $packageSha = ''): void
    {
        if (!is_resource($out) && !($out instanceof \SplFileObject)) {
            throw new RuntimeException('Invalid dump stream');
        }
        $write = static function ($out, string $chunk): void {
            if (is_resource($out)) {
                fwrite($out, $chunk);
                return;
            }
            $out->fwrite($chunk);
        };

        $write($out, "-- pRD database backup\n");
        $write($out, '-- Generated: ' . gmdate('Y-m-d H:i:s') . " UTC\n");
        $write($out, '-- Schema version: ' . $schemaVersion . "\n");
        if ($packageSha !== '') {
            $write($out, '-- Package commit: ' . $packageSha . "\n");
        }
        $write($out, "\nSET NAMES utf8mb4;\nSET FOREIGN_KEY_CHECKS=0;\n\n");

        $tablesStmt = $this->pdo->query('SHOW TABLES');
        $tables = $tablesStmt !== false ? $tablesStmt->fetchAll(PDO::FETCH_COLUMN) : [];

        foreach ($tables as $table) {
            if (!is_string($table) || $table === '') {
                continue;
            }
            $escapedTable = str_replace('`', '``', $table);
            $createStmt = $this->pdo->query("SHOW CREATE TABLE `{$escapedTable}`");
            $create = $createStmt !== false ? $createStmt->fetch(PDO::FETCH_NUM) : false;
            if ($create !== false && isset($create[1]) && is_string($create[1])) {
                $write($out, "DROP TABLE IF EXISTS `{$escapedTable}`;\n");
                $write($out, $create[1] . ";\n\n");
            }
            $rowsStmt = $this->pdo->query("SELECT * FROM `{$escapedTable}`");
            if ($rowsStmt === false) {
                continue;
            }
            while ($row = $rowsStmt->fetch(PDO::FETCH_ASSOC)) {
                $cols = [];
                $vals = [];
                foreach ($row as $col => $val) {
                    $cols[] = '`' . str_replace('`', '``', (string) $col) . '`';
                    if ($val === null) {
                        $vals[] = 'NULL';
                    } else {
                        $quoted = $this->pdo->quote((string) $val);
                        $vals[] = $quoted !== false ? $quoted : 'NULL';
                    }
                }
                $write(
                    $out,
                    'INSERT INTO `' . $escapedTable . '` (' . implode(', ', $cols) . ') VALUES (' . implode(', ', $vals) . ");\n"
                );
            }
            $write($out, "\n");
        }

        $write($out, "SET FOREIGN_KEY_CHECKS=1;\n");
    }

    public function writeToFile(string $path, int $schemaVersion, string $packageSha = ''): void
    {
        $dir = dirname($path);
        if (!is_dir($dir) && !@mkdir($dir, 0750, true) && !is_dir($dir)) {
            throw new RuntimeException('Could not create backup folder');
        }
        $fh = fopen($path, 'wb');
        if ($fh === false) {
            throw new RuntimeException('Could not write backup file');
        }
        try {
            $this->writeToStream($fh, $schemaVersion, $packageSha);
        } finally {
            fclose($fh);
        }
        @chmod($path, 0640);
    }
}
