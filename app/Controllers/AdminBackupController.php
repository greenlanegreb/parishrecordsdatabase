<?php
/**
 * MIGRATED FILE MAPPING
 * ---------------------
 * Original Old File: admin/actions/download_database_backup.php
 * Migrated Date: 2026-08-05 04:28:31
 */
declare(strict_types=1);

namespace App\Controllers;

use App\Services\DatabaseDumpService;
use App\Services\ReleaseUpdateService;
use Exception;
use PDO;

class AdminBackupController
{
    private PDO $pdo;

    public function __construct(PDO $pdo)
    {
        $this->pdo = $pdo;
    }

    public function downloadBackup(): void
    {
        $serverMethod = isset($_SERVER['REQUEST_METHOD']) && is_string($_SERVER['REQUEST_METHOD']) ? $_SERVER['REQUEST_METHOD'] : 'GET';
        if ($serverMethod !== 'POST') {
            http_response_code(405);
            exit('Method Not Allowed');
        }

        verify_csrf_token();
        /** @var array{id: int, username: string} $currentUser */
        $currentUser = require_permission($this->pdo, 'manage_settings', 'Manage global site settings, mail drivers, and maintenance mode');

        $releases = new ReleaseUpdateService($this->pdo);
        $pkg = $releases->currentPackage();
        $sha = is_string($pkg['sha']) ? $pkg['sha'] : '';
        $schemaVersion = function_exists('get_schema_version') ? get_schema_version($this->pdo) : 0;
        $dump = new DatabaseDumpService($this->pdo);

        // Private last-good copy on the server (not publicly downloadable).
        try {
            $releases->ensureBackupDir();
            $archiveName = $releases->archiveFilename($sha);
            $dump->writeToFile($releases->backupDir() . '/' . $archiveName, (int) $schemaVersion, $sha);
            $releases->pruneArchives(2);
        } catch (Exception $e) {
            error_log('pRD private backup archive failed: ' . $e->getMessage());
        }

        $short = $sha !== '' ? substr(preg_replace('/[^a-fA-F0-9]/', '', $sha) ?? '', 0, 7) : 'unknown';
        $filename = 'prd-backup-' . gmdate('Y-m-d_Hi') . 'Z-' . ($short !== '' ? $short : 'unknown') . '.sql';

        header('Content-Type: application/sql; charset=utf-8');
        header('Content-Disposition: attachment; filename="' . $filename . '"');
        header('Cache-Control: no-store, no-cache, must-revalidate');

        $out = fopen('php://output', 'w');
        if ($out === false) {
            http_response_code(500);
            exit('Failed to open output stream.');
        }
        $dump->writeToStream($out, (int) $schemaVersion, $sha);
        fclose($out);

        $remoteAddr = isset($_SERVER['REMOTE_ADDR']) && is_string($_SERVER['REMOTE_ADDR']) ? $_SERVER['REMOTE_ADDR'] : '127.0.0.1';
        try {
            $audit = $this->pdo->prepare("INSERT INTO audit_logs (`user_id`, `action`, `details`, `ip_address`, `created_at`) VALUES (?, 'DOWNLOAD_DATABASE_BACKUP', ?, ?, NOW())");
            $audit->execute([$currentUser['id'], "Downloaded full SQL database backup ({$filename})", $remoteAddr]);
        } catch (Exception $e) {
        }

        exit;
    }

    public function downloadArchive(): void
    {
        $serverMethod = isset($_SERVER['REQUEST_METHOD']) && is_string($_SERVER['REQUEST_METHOD']) ? $_SERVER['REQUEST_METHOD'] : 'GET';
        if ($serverMethod !== 'POST') {
            http_response_code(405);
            exit('Method Not Allowed');
        }
        verify_csrf_token();
        require_permission($this->pdo, 'manage_settings', 'Manage global site settings, mail drivers, and maintenance mode');

        $releases = new ReleaseUpdateService($this->pdo);
        $name = isset($_POST['file']) && is_string($_POST['file']) ? $_POST['file'] : '';
        $path = $releases->archivePath($name);
        if ($path === null) {
            $_SESSION['error'] = function_exists('__') && __('updates.archive_missing') !== 'updates.archive_missing'
                ? __('updates.archive_missing')
                : 'That private backup was not found.';
            $base = defined('BASE_PATH') ? rtrim((string) BASE_PATH, '/') : '';
            header('Location: ' . $base . '/admin/settings?tab=core');
            exit;
        }

        header('Content-Type: application/sql; charset=utf-8');
        header('Content-Disposition: attachment; filename="' . basename($path) . '"');
        header('Cache-Control: no-store, no-cache, must-revalidate');
        readfile($path);
        exit;
    }
}
