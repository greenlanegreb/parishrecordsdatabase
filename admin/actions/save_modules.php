<?php
// admin/actions/save_modules.php - Handles saving module toggle states
require_once '../../db/db.php';
require_once '../../db/auth_helpers.php';
session_start();
if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    http_response_code(405);
    exit('Method Not Allowed');
}
verify_csrf_token();
$current_user = require_permission($pdo, 'manage_settings', 'Manage global site settings and module feature flags');

$modules = ['moderation', 'volunteers', 'feedback', 'users', 'leaderboard'];
$stmt = $pdo->prepare("INSERT INTO site_settings (setting_key, setting_value) VALUES (?, ?) ON DUPLICATE KEY UPDATE setting_value = ?");

foreach ($modules as $mod) {
    $key = 'module_' . $mod . '_enabled';
    
    // Explicitly check if the checkbox was posted; if not, it's '0'
    if ($mod === 'leaderboard' && !isset($_POST['module_users_enabled'])) {
        $val = '0';
    } else {
        $val = isset($_POST[$key]) ? '1' : '0';
    }
    
    $stmt->execute([$key, $val, $val]);
}

$_SESSION['message'] = "Module feature flags successfully updated!";
$audit = $pdo->prepare("INSERT INTO audit_logs (user_id, action, details, ip_address) VALUES (?, 'UPDATE_MODULES', 'Updated application module feature flags', ?)");
$audit->execute([$current_user['id'], $_SERVER['REMOTE_ADDR']]);

header('Location: ../settings.php#tab-modules');
exit;
