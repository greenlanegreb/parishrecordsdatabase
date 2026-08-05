<?php
/**
 * MIGRATED FILE MAPPING
 * ---------------------
 * Original Old File: roote/update_database.php
 * Migrated Date: 2026-08-05 06:52:04
 */declare(strict_types=1);


namespace App\Controllers;

use PDO;
use Exception;

class UpdateDatabaseController
{
    private PDO $pdo;

    public function __construct(PDO $pdo)
    {
        $this->pdo = $pdo;
    }

    public function index(): void
    {
        if (session_status() === PHP_SESSION_NONE) {
            session_start();
        }

        require_once __DIR__ . '/../../includes/functions.php';
        require_once __DIR__ . '/../../db/migrate_runner.php';

        // Safely calculate current schema version, falling back if site_settings table is missing
        $schemaCurrent = 0;
        try {
            $schemaCurrent = function_exists('get_schema_version') ? get_schema_version($this->pdo) : 0;
        } catch (Exception $e) {
            $schemaCurrent = 0; // If table doesn't exist yet, start from scratch
        }

        // Calculate latest available schema version from migration files
        $schemaLatest = $schemaCurrent;
        $migrationsDir = __DIR__ . '/../../db/migrations';

        if (is_dir($migrationsDir)) {
            $globFiles = glob($migrationsDir . '/*.php');
            if ($globFiles !== false) {
                foreach ($globFiles as $migFile) {
                    $m = [];
                    if (preg_match('/(\d+)_/', basename($migFile), $m)) {
                        $schemaLatest = max($schemaLatest, (int)$m[1]);
                    }
                }
            }
        }

        // If no updates are actually pending, block access and send them to login
        if ($schemaCurrent >= $schemaLatest) {
            $base = defined('BASE_PATH') && is_string(BASE_PATH) ? rtrim(BASE_PATH, '/') : '';
            header('Location: ' . $base . '/user/login.php');
            exit;
        }

        $message = '';
        $error = '';
        $appliedList = [];

        // Handle update execution submission
        $serverMethod = isset($_SERVER['REQUEST_METHOD']) && is_string($_SERVER['REQUEST_METHOD']) ? $_SERVER['REQUEST_METHOD'] : 'GET';
        if ($serverMethod === 'POST') {
            try {
                $result = run_pending_migrations($this->pdo, $migrationsDir);
                $appliedList = isset($result['applied']) && is_array($result['applied']) ? $result['applied'] : [];
                $schemaCurrent = isset($result['current']) ? (int)$result['current'] : $schemaCurrent;
                
                if (!empty($appliedList)) {
                    $message = sprintf(__('update_database.msg_success'), count($appliedList));
                } else {
                    $message = __('update_database.msg_uptodate');
                }
            } catch (Exception $e) {
                $error = __('update_database.err_failed') . ' ' . $e->getMessage();
            }
        }

        // Pass variables to View
        require_once __DIR__ . '/../Views/update_database/index.php';
    }
}
