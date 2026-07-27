<?php
// db/maintenance_guard.php - Global interceptor for site maintenance mode

function check_maintenance_mode($pdo) {
    // Fetch maintenance settings safely
    try {
        $stmt = $pdo->query("SELECT setting_key, setting_value FROM site_settings WHERE setting_key IN ('maintenance_mode', 'maintenance_reason', 'maintenance_eta')");
        $settings = [];
        while ($row = $stmt->fetch(PDO::FETCH_ASSOC)) {
            $settings[$row['setting_key']] = $row['setting_value'];
        }
    } catch (PDOException $e) {
        // Fallback if table or keys don't exist yet
        return;
    }

    $is_offline = isset($settings['maintenance_mode']) && $settings['maintenance_mode'] === '1';

    if ($is_offline) {
        // Check if user is logged in and has administrator privileges to bypass
        $is_admin = false;
        if (isset($_SESSION['user_id'])) {
            $user_stmt = $pdo->prepare("SELECT role FROM users WHERE id = ?");
            $user_stmt->execute([$_SESSION['user_id']]);
            $user = $user_stmt->fetch();
            if ($user && $user['role'] === 'admin') {
                $is_admin = true;
            }
        }

        // Allow administrators, admin panel scripts, or login/auth pages to bypass
        $request_uri = $_SERVER['REQUEST_URI'] ?? '';
        $current_script = basename($_SERVER['PHP_SELF']);
        
        $is_admin_area = (strpos($request_uri, '/admin/') !== false);
        $allowed_scripts = ['login.php', 'logout.php', 'authenticate.php'];
        
        if (!$is_admin && !$is_admin_area && !in_array($current_script, $allowed_scripts)) {
            $reason = $settings['maintenance_reason'] ?? 'System maintenance is currently underway.';
            $eta = $settings['maintenance_eta'] ?? 'Shortly';
            
            // Use dynamic BASE_PATH constant defined in db.php
            $base_path = defined('BASE_PATH') ? BASE_PATH : '';
            
            // Render a clean maintenance page with a 503 HTTP status code
            http_response_code(503);
            ?>
            <!DOCTYPE html>
            <html lang="en">
            <head>
                <meta charset="UTF-8">
                <title>Site Under Maintenance</title>
                <link rel="stylesheet" href="<?php echo $base_path; ?>/assets/css/style.css">
            </head>
            <body style="display: flex; justify-content: center; align-items: center; height: 100vh; background: #f8f9fa; font-family: sans-serif; margin: 0;">
                <div style="background: white; padding: 2.5rem; border-radius: 8px; box-shadow: 0 4px 12px rgba(0,0,0,0.1); max-width: 500px; text-align: center;">
                    <h2 style="color: #dc3545; margin-top: 0;">System Offline for Maintenance</h2>
                    <p style="color: #555; line-height: 1.5; font-size: 1rem;"><?php echo htmlspecialchars($reason); ?></p>
                    <p style="background: #e9ecef; padding: 0.75rem; border-radius: 4px; font-weight: bold; color: #333; margin: 1.5rem 0;">Expected Return Time: <?php echo htmlspecialchars($eta); ?></p>
                    <p style="font-size: 0.85rem; color: #888; margin-bottom: 0;">Thank you for your patience while we improve your experience.</p>
                </div>
            </body>
            </html>
            <?php
            exit;
        }
    }
}
