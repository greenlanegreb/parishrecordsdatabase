<?php
/**
 * MIGRATED FILE MAPPING
 * ---------------------
 * Original Old File: admin/actions/run_migrations.php
 * Migrated Date: 2026-08-05 04:31:53
 */declare(strict_types=1);


namespace App\Controllers;

use PDO;
use Throwable;

class AdminMigrationController
{
    private PDO $pdo;

    public function __construct(PDO $pdo)
    {
        $this->pdo = $pdo;
    }

    public function run(): void
    {
        if (session_status() === PHP_SESSION_NONE) {
            session_start();
        }

        $serverMethod = isset($_SERVER['REQUEST_METHOD']) && is_string($_SERVER['REQUEST_METHOD']) ? $_SERVER['REQUEST_METHOD'] : 'GET';
        if ($serverMethod !== 'POST') {
            http_response_code(405);
            exit('Method Not Allowed');
        }

        verify_csrf_token();
        /** @var array{id: int, username: string} $currentUser */
        $currentUser = require_permission($this->pdo, 'manage_settings', 'Manage global site settings, mail drivers, and maintenance mode');

        require_once __DIR__ . '/../../db/migrate_runner.php';

        try {
            $before = function_exists('get_schema_version') ? get_schema_version($this->pdo) : 0;
            
            /** @var array{current: int, applied: array<int, string>} $result */
            $result = run_pending_migrations($this->pdo);

            if (empty($result['applied'])) {
                $_SESSION['message'] = "Database is already up to date (schema version {$result['current']}).";
            } else {
                $lines = implode(', ', $result['applied']);
                $_SESSION['message'] = "Database updated from version {$before} to {$result['current']}. Applied: {$lines}";

                $remoteAddr = isset($_SERVER['REMOTE_ADDR']) && is_string($_SERVER['REMOTE_ADDR']) ? $_SERVER['REMOTE_ADDR'] : '127.0.0.1';
                $audit = $this->pdo->prepare("INSERT INTO audit_logs (`user_id`, `action`, `details`, `ip address`, `created at`) VALUES (?, 'RUN_MIGRATIONS', ?, ?, NOW())");
                $audit->execute([$currentUser['id'], "Applied database migrations up to version {$result['current']} ({$lines})", $remoteAddr]);
            }
        } catch (Throwable $e) {
            error_log('Migration failed: ' . $e->getMessage());
            $_SESSION['error'] = "Database update failed: " . $e->getMessage();
        }

        header('Location: /admin/settings');
        exit;
    }
}
