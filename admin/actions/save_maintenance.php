<?php
// admin/actions/save_maintenance.php - Processes system maintenance mode settings updates
require_once '../../db/db.php';
require_once '../../db/auth_helpers.php';
session_start();

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    http_response_code(405);
    exit('Method Not Allowed');
}

// Verify CSRF token and enforce permission check
verify_csrf_token();
$current_user = require_permission($pdo, 'manage_settings', 'Manage global site settings, mail drivers, and maintenance mode');

$maintenance_mode = isset($_POST['maintenance_mode']) ? '1' : '0';
$maintenance_reason = trim($_POST['maintenance_reason'] ?? 'Scheduled system maintenance and database updates.');
$maintenance_eta = trim($_POST['maintenance_eta'] ?? 'Shortly');

if (!empty($maintenance_reason) && !empty($maintenance_eta)) {
    $stmt = $pdo->prepare("INSERT INTO site_settings (setting_key, setting_value) VALUES (?, ?) ON DUPLICATE KEY UPDATE setting_value = ?");
    
    $stmt->execute(['maintenance_mode', $maintenance_mode, $maintenance_mode]);
    $stmt->execute(['maintenance_reason', $maintenance_reason, $maintenance_reason]);
    $stmt->execute(['maintenance_eta', $maintenance_eta, $maintenance_eta]);

    $_SESSION['message'] = "Maintenance settings updated successfully.";
    
    $audit = $pdo->prepare("INSERT INTO audit_logs (user_id, action, details, ip_address) VALUES (?, 'UPDATE_MAINTENANCE', ?, ?)");
    $audit->execute([$current_user['id'], "Updated maintenance mode state to: {$maintenance_mode}", $_SERVER['REMOTE_ADDR']]);
} else {
    $_SESSION['error'] = "Maintenance reason and ETA cannot be empty.";
}

header('Location: ../settings.php');
exit;
