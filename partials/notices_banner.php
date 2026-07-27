<?php
// partials/notices_banner.php - Dynamic banner module renderer
if (!isset($pdo)) {
    return;
}

// Determine user role context
$user_is_logged_in = isset($current_user) && $current_user !== null;
$current_role = $user_is_logged_in ? $current_user['role'] : 'public';

// Fetch active notices
$notices_stmt = $pdo->query("SELECT * FROM site_notices WHERE is_active = 1 ORDER BY display_order ASC");
$all_notices = $notices_stmt->fetchAll();

foreach ($all_notices as $notice) {
    $roles = explode(',', $notice['target_roles']);
    
    // Check role eligibility
    $is_targeted = in_array('everyone', $roles) || 
                   in_array($current_role, $roles) || 
                   (!$user_is_logged_in && in_array('public', $roles));

    if (!$is_targeted) {
        continue;
    }

    $notice_id = $notice['id'];
    // Check if dismissed in the current session
    if (isset($_SESSION['dismissed_notices'][$notice_id])) {
        continue;
    }
    ?>
    <div class="site-notice-banner" data-notice-id="<?php echo $notice_id; ?>" style="background: #f8f9fa; border-left: 4px solid var(--primary-color, #007bff); padding: 1rem; margin-bottom: 1rem; border-radius: 4px; position: relative; box-shadow: 0 1px 3px rgba(0,0,0,0.05);">
        <?php if ($notice['is_dismissible']): ?>
            <a href="?dismiss_notice=<?php echo $notice_id; ?>" style="position: absolute; top: 0.5rem; right: 0.75rem; text-decoration: none; font-weight: bold; color: #6c757d; font-size: 1.1rem;" title="Close notice">&times;</a>
        <?php endif; ?>
        <h4 style="margin: 0 0 0.3rem 0; font-size: 1rem; color: #333;"><?php echo htmlspecialchars($notice['title']); ?></h4>
        <div style="font-size: 0.95rem; color: #555;"><?php echo nl2br($notice['content']); ?></div>
    </div>
    <?php
}

// Handle dismiss action in session
if (isset($_GET['dismiss_notice'])) {
    $dismiss_id = intval($_GET['dismiss_notice']);
    $_SESSION['dismissed_notices'][$dismiss_id] = true;
    
    // Clean URL parameters to avoid keeping dismiss_notice in the query string
    $clean_url = strtok($_SERVER['REQUEST_URI'], '?');
    header("Location: " . $clean_url);
    exit;
}
