<?php
declare(strict_types=1);

namespace App\Controllers;

use App\Services\DatabaseDumpService;
use App\Services\ReleaseUpdateService;
use PDO;
use Throwable;

class AdminSoftwareUpdateController
{
    private PDO $pdo;

    public function __construct(PDO $pdo)
    {
        $this->pdo = $pdo;
    }

    public function apply(): void
    {
        if (($_SERVER['REQUEST_METHOD'] ?? '') !== 'POST') {
            http_response_code(405);
            exit('Method Not Allowed');
        }
        verify_csrf_token();
        $currentUser = require_permission($this->pdo, 'manage_settings', 'Manage global site settings, mail drivers, and maintenance mode');
        $base = defined('BASE_PATH') ? rtrim((string) BASE_PATH, '/') : '';

        $releases = new ReleaseUpdateService($this->pdo);
        $ref = isset($_POST['update_ref']) && is_string($_POST['update_ref'])
            ? $releases->sanitizeRef($_POST['update_ref'])
            : $releases->defaultRef();

        $schemaVersion = function_exists('get_schema_version') ? (int) get_schema_version($this->pdo) : 0;
        $pkg = $releases->currentPackage();
        $oldSha = is_string($pkg['sha']) ? $pkg['sha'] : '';
        $force = isset($_POST['force_apply']) && (string) $_POST['force_apply'] === '1';

        $look = $releases->lookupRemote($ref);
        if ($look['ok'] && $oldSha !== '' && hash_equals(strtolower($oldSha), strtolower($look['sha'])) && !$force) {
            $_SESSION['message'] = function_exists('__') && __('updates.already_current') !== 'updates.already_current'
                ? __('updates.already_current')
                : 'Files already match this update channel. Nothing was changed.';
            header('Location: ' . $base . '/admin/settings?tab=core');
            exit;
        }

        try {
            $releases->ensureBackupDir();
            $dump = new DatabaseDumpService($this->pdo);
            $archiveName = $releases->archiveFilename($oldSha !== '' ? $oldSha : 'preupdate');
            $dump->writeToFile($releases->backupDir() . '/' . $archiveName, $schemaVersion, $oldSha);
            $releases->pruneArchives(2);
        } catch (Throwable $e) {
            error_log('pRD pre-update archive failed: ' . $e->getMessage());
            $_SESSION['error'] = function_exists('__') && __('updates.archive_failed') !== 'updates.archive_failed'
                ? __('updates.archive_failed')
                : 'Could not write the private database copy. File update was not started.';
            header('Location: ' . $base . '/admin/settings?tab=core');
            exit;
        }

        $result = $releases->applyPackage($ref);
        $remoteAddr = isset($_SERVER['REMOTE_ADDR']) && is_string($_SERVER['REMOTE_ADDR']) ? $_SERVER['REMOTE_ADDR'] : '127.0.0.1';
        try {
            $audit = $this->pdo->prepare("INSERT INTO audit_logs (`user_id`, `action`, `details`, `ip_address`, `created_at`) VALUES (?, ?, ?, ?, NOW())");
            $audit->execute([
                $currentUser['id'],
                $result['ok'] ? 'APPLY_FILE_PACKAGE' : 'APPLY_FILE_PACKAGE_FAILED',
                $result['message'] . ' ref=' . $ref . ' sha=' . $result['sha'] . ' files=' . $result['files'],
                $remoteAddr,
            ]);
        } catch (Throwable $e) {
        }

        if ($result['ok']) {
            $access = dirname(__DIR__, 2) . '/includes/prd_update_access.php';
            if (is_file($access)) {
                require_once $access;
            }
            $msg = (function_exists('__') && __('updates.files_ok') !== 'updates.files_ok'
                ? __('updates.files_ok')
                : 'File package applied.')
                . ' ' . substr($result['sha'], 0, 7) . '.';
            $runner = dirname(__DIR__, 2) . '/db/migrate_runner.php';
            if (is_file($runner)) {
                require_once $runner;
            }
            if (function_exists('prd_mark_pending_migrate')) {
                prd_mark_pending_migrate();
            }
            if (function_exists('run_pending_migrations')) {
                try {
                    $migrated = run_pending_migrations($this->pdo);
                    if (function_exists('prd_clear_pending_migrate')) {
                        prd_clear_pending_migrate();
                    }
                    if (!empty($migrated['applied'])) {
                        $msg .= ' ' . (function_exists('__') && __('updates.db_ran') !== 'updates.db_ran'
                            ? __('updates.db_ran')
                            : 'The database was updated as well.');
                    }
                } catch (Throwable $e) {
                    error_log('pRD auto-migrate after file package: ' . $e->getMessage());
                    $msg .= ' ' . (function_exists('__') && __('updates.db_pending') !== 'updates.db_pending'
                        ? __('updates.db_pending')
                        : 'If the site asks you to update the database next, open that page. You do not need to create any extra files.');
                }
            }
            $_SESSION['message'] = $msg;
        } else {
            $_SESSION['error'] = $result['message'];
        }

        header('Location: ' . $base . '/admin/settings?tab=core');
        exit;
    }

    public function saveChannel(): void
    {
        if (($_SERVER['REQUEST_METHOD'] ?? '') !== 'POST') {
            http_response_code(405);
            exit('Method Not Allowed');
        }
        verify_csrf_token();
        require_permission($this->pdo, 'manage_settings', 'Manage global site settings, mail drivers, and maintenance mode');
        $releases = new ReleaseUpdateService($this->pdo);
        $ref = isset($_POST['update_ref']) && is_string($_POST['update_ref'])
            ? $releases->sanitizeRef($_POST['update_ref'])
            : 'main';
        $releases->setChannel($ref);
        $_SESSION['message'] = function_exists('__') && __('updates.channel_saved') !== 'updates.channel_saved'
            ? __('updates.channel_saved')
            : 'Update channel saved.';
        $base = defined('BASE_PATH') ? rtrim((string) BASE_PATH, '/') : '';
        header('Location: ' . $base . '/admin/settings?tab=core');
        exit;
    }
}
