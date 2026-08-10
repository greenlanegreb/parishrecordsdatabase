<?php
/**
 * MIGRATED FILE MAPPING
 * ---------------------
 * Original Old File: roote/update_database.php
 * Migrated Date: 2026-08-05 06:52:04
 */
declare(strict_types=1);

namespace App\Controllers;

use Exception;
use PDO;

class UpdateDatabaseController
{
    private PDO $pdo;

    public function __construct(PDO $pdo)
    {
        $this->pdo = $pdo;
    }

    private function emergencyFlagPath(): string
    {
        return dirname(__DIR__, 2) . '/db/ALLOW_EMERGENCY_MIGRATE';
    }

    private function canAccessUpdater(bool $emergencyOk): bool
    {
        if ($emergencyOk) {
            return true;
        }
        if (!isset($_SESSION['user_id'])) {
            return false;
        }
        if (function_exists('has_permission')) {
            return has_permission($this->pdo, 'manage_settings');
        }
        return false;
    }

    public function index(): void
    {
        require_once __DIR__ . '/../../includes/functions.php';
        require_once __DIR__ . '/../../db/migrate_runner.php';

        $flagPath    = $this->emergencyFlagPath();
        $emergencyOk = is_file($flagPath);
        $basePath    = defined('BASE_PATH') && is_string(BASE_PATH) ? rtrim(BASE_PATH, '/') : '';

        if (!$this->canAccessUpdater($emergencyOk)) {
            http_response_code(403);
            exit('Forbidden');
        }

        $schemaCurrent = 0;
        try {
            $schemaCurrent = function_exists('get_schema_version') ? get_schema_version($this->pdo) : 0;
        } catch (Exception $e) {
            $schemaCurrent = 0;
        }

        $schemaLatest  = $schemaCurrent;
        $migrationsDir = dirname(__DIR__, 2) . '/db/migrations';

        if (is_dir($migrationsDir)) {
            $globFiles = glob($migrationsDir . '/*.php');
            if ($globFiles !== false) {
                foreach ($globFiles as $migFile) {
                    $m = [];
                    if (preg_match('/(\d+)_/', basename($migFile), $m)) {
                        $schemaLatest = max($schemaLatest, (int) $m[1]);
                    }
                }
            }
        }

        $message     = '';
        $error       = '';
        $appliedList = [];
        $serverMethod = isset($_SERVER['REQUEST_METHOD']) && is_string($_SERVER['REQUEST_METHOD'])
            ? $_SERVER['REQUEST_METHOD'] : 'GET';

        if ($serverMethod === 'POST') {
            $action = isset($_POST['action']) && is_string($_POST['action']) ? $_POST['action'] : 'migrate';

            if (function_exists('verify_csrf_token')) {
                verify_csrf_token();
            }

            if ($action === 'remove_emergency_flag') {
                if (is_file($flagPath) && @unlink($flagPath)) {
                    $message = 'Emergency migration access file has been removed.';
                    $emergencyOk = false;
                } else {
                    $error = 'Could not remove emergency file (missing or not writable).';
                }
            } else {
                // Run migrations
                try {
                    $result = run_pending_migrations($this->pdo, $migrationsDir);
                    $appliedList = isset($result['applied']) && is_array($result['applied']) ? $result['applied'] : [];
                    $schemaCurrent = isset($result['current']) ? (int) $result['current'] : $schemaCurrent;

                    if ($appliedList !== []) {
                        $message = function_exists('__')
                            ? sprintf(__('update_database.msg_success'), count($appliedList))
                            : ('Applied ' . count($appliedList) . ' migration(s).');
                    } else {
                        $message = function_exists('__')
                            ? __('update_database.msg_uptodate')
                            : 'Schema is already up to date.';
                    }

                    // Auto-remove emergency flag after a successful run when up to date
                    if ($schemaCurrent >= $schemaLatest && is_file($flagPath)) {
                        if (@unlink($flagPath)) {
                            $emergencyOk = false;
                            $message .= ' Emergency access file removed.';
                        }
                    }
                } catch (Exception $e) {
                    $error = (function_exists('__') ? __('update_database.err_failed') . ' ' : 'Update failed: ')
                        . $e->getMessage();
                }
            }
        }

        // Up to date: logged-in admins go to settings; emergency visitors stay here to remove flag
        if ($schemaCurrent >= $schemaLatest && $serverMethod === 'GET') {
            if (!$emergencyOk && isset($_SESSION['user_id'])) {
                header('Location: ' . $basePath . '/admin/settings');
                exit;
            }
            if (!$emergencyOk && !isset($_SESSION['user_id'])) {
                header('Location: ' . $basePath . '/login');
                exit;
            }
            // emergencyOk && up to date → fall through to view so they can remove the flag
        }

        $schemaBehind = ($schemaCurrent < $schemaLatest);

        require_once __DIR__ . '/../Views/update_database/index.php';
    }
}
