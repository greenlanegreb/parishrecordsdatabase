<?php
/**
 * MIGRATED FILE MAPPING
 * ---------------------
 * Original Old File: admin/actions/save_maintenance.php
 * Migrated Date: 2026-08-05 04:35:30
 */
declare(strict_types=1);

namespace App\Controllers;

use Exception;
use PDO;

class AdminMaintenanceController
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

        // Verify CSRF token and enforce permission check
        verify_csrf_token();
        /** @var array{id: int, username: string} $currentUser */
        $currentUser = require_permission($this->pdo, 'manage_settings', 'Manage global site settings, mail drivers, and maintenance mode');

        $post = $_POST;
        $maintenanceMode = isset($post['maintenance_mode']) ? '1' : '0';
        $maintenanceReason = isset($post['maintenance_reason']) && is_string($post['maintenance_reason']) ? trim($post['maintenance_reason']) : 'Scheduled system maintenance and database updates.';
        $maintenanceEta = isset($post['maintenance_eta']) && is_string($post['maintenance_eta']) ? trim($post['maintenance_eta']) : 'Shortly';
        $remoteAddr = isset($_SERVER['REMOTE_ADDR']) && is_string($_SERVER['REMOTE_ADDR']) ? $_SERVER['REMOTE_ADDR'] : '127.0.0.1';

        try {
            if ($maintenanceReason !== '' && $maintenanceEta !== '') {
                $stmt = $this->pdo->prepare("INSERT INTO site_settings (setting_key, setting_value) VALUES (?, ?) ON DUPLICATE KEY UPDATE setting_value = ?");
                
                $stmt->execute(['maintenance_mode', $maintenanceMode, $maintenanceMode]);
                $stmt->execute(['maintenance_reason', $maintenanceReason, $maintenanceReason]);
                $stmt->execute(['maintenance_eta', $maintenanceEta, $maintenanceEta]);

                $_SESSION['message'] = "Maintenance settings updated successfully.";
                
                $audit = $this->pdo->prepare("INSERT INTO audit_logs (user_id, action, details, ip_address) VALUES (?, 'UPDATE_MAINTENANCE', ?, ?)");
                $audit->execute([$currentUser['id'], "Updated maintenance mode state to: {$maintenanceMode}", $remoteAddr]);
            } else {
                $_SESSION['error'] = "Maintenance reason and ETA cannot be empty.";
            }
        } catch (Exception $e) {
            $_SESSION['error'] = "Database error: " . $e->getMessage();
        }

        header('Location: ' . BASE_PATH . '/admin/settings#tab-maintenance');
        exit;
    }
}
