<?php
// admin/actions/save_settings.php - Processes global site settings updates
require_once '../../db/db.php';
require_once '../../db/auth_helpers.php';
session_start();

// Enforce admin-only access
require_role($pdo, ['admin']);

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $system_name = trim($_POST['system_name'] ?? '');

    if (!empty($system_name)) {
        $stmt = $pdo->prepare("INSERT INTO site_settings (setting_key, setting_value) VALUES ('system_name', ?) ON DUPLICATE KEY UPDATE setting_value = ?");
        $stmt->execute([$system_name, $system_name]);

        $_SESSION['message'] = "Site settings updated successfully.";
    } else {
        $_SESSION['error'] = "System name cannot be empty.";
    }
}

header('Location: ../settings.php');
exit;
?>
