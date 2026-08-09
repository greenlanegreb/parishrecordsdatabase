<?php
/**
 * MIGRATED FILE MAPPING
 * ---------------------
 * Original Old File: admin/actions/save_modules.php
 * Migrated Date: 2026-08-05 04:37:06
 */
declare(strict_types=1);

namespace App\Controllers;

use Exception;
use PDO;

class AdminModulesController
{
    private PDO $pdo;

    public function __construct(PDO $pdo)
    {
        $this->pdo = $pdo;
    }

    public function save(): void
    {
        $serverMethod = isset($_SERVER['REQUEST_METHOD']) && is_string($_SERVER['REQUEST_METHOD']) ? $_SERVER['REQUEST_METHOD'] : 'GET';
        if ($serverMethod !== 'POST') {
            http_response_code(405);
            exit('Method Not Allowed');
        }

        \verify_csrf_token();
        /** @var array{id: int, username: string} $currentUser */
        $currentUser = \require_permission($this->pdo, 'manage_settings', 'Manage global site settings and module feature flags');

        $post = $_POST;
        $remoteAddr = isset($_SERVER['REMOTE_ADDR']) && is_string($_SERVER['REMOTE_ADDR']) ? $_SERVER['REMOTE_ADDR'] : '127.0.0.1';

        try {
            $modules = ['moderation', 'volunteers', 'feedback', 'users', 'leaderboard'];
            $stmt = $this->pdo->prepare("INSERT INTO site_settings (setting_key, setting_value) VALUES (?, ?) ON DUPLICATE KEY UPDATE setting_value = ?");

            foreach ($modules as $mod) {
                $key = 'module_' . $mod . '_enabled';
                
                // Explicitly check if the checkbox was posted; if not, it's '0'
                if ($mod === 'leaderboard' && !isset($post['module_users_enabled'])) {
                    $val = '0';
                } else {
                    $val = isset($post[$key]) ? '1' : '0';
                }
                
                $stmt->execute([$key, $val, $val]);
            }

            $_SESSION['message'] = "Module feature flags successfully updated!";
            $audit = $this->pdo->prepare("INSERT INTO audit_logs (user_id, action, details, ip_address) VALUES (?, 'UPDATE_MODULES', 'Updated application module feature flags', ?)");
            $audit->execute([$currentUser['id'], $remoteAddr]);

        } catch (Exception $e) {
            $_SESSION['error'] = "Failed to update module feature flags: " . $e->getMessage();
        }

        header('Location: ' . BASE_PATH . '/admin/settings#tab-modules');
        exit;
    }
}
