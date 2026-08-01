<?php
// admin/actions/cron_token_cleanup.php - Automated & Manual Token Maintenance Cron Script

// Allow execution from CLI or authenticated admin session
$is_cli = (php_sapi_name() === 'cli');

if (!$is_cli) {
    require_once '../../db/db.php';
    require_once '../../db/auth_helpers.php';
    require_once '../../includes/functions.php';
    session_start();
    verify_csrf_token();
    $current_user = require_permission($pdo, 'manage_settings', 'Perform maintenance token cleanup');
} else {
    require_once __DIR__ . '/../../db/db.php';
    require_once __DIR__ . '/../../includes/functions.php';
}

try {
    $pdo->beginTransaction();

    // Purge expired tokens
    $expiredStmt = $pdo->prepare("
        UPDATE users 
        SET verification_token = NULL, token_expires_at = NULL 
        WHERE token_expires_at IS NOT NULL AND token_expires_at < NOW()
    ");
    $expiredStmt->execute();
    $expiredCount = $expiredStmt->rowCount();

    // Purge tokens for already-activated users
    $activatedStmt = $pdo->prepare("
        UPDATE users 
        SET verification_token = NULL, token_expires_at = NULL 
        WHERE is_new_user = 0 AND verification_token IS NOT NULL
    ");
    $activatedStmt->execute();
    $activatedCount = $activatedStmt->rowCount();

    $pdo->commit();

    $details = sprintf('Token maintenance executed successfully. Purged %d expired tokens and %d lingering activated-user tokens.', $expiredCount, $activatedCount);
    
    // Log to audit table
    $actor_id = $is_cli ? null : ($current_user['id'] ?? null);
    $ip = $is_cli ? '127.0.0.1' : ($_SERVER['REMOTE_ADDR'] ?? '127.0.0.1');
    
    $logStmt = $pdo->prepare("INSERT INTO audit_logs (`user_id`, `action`, `details`, `ip address`, `created at`) VALUES (?, ?, ?, ?, NOW())");
    $logStmt->execute([$actor_id, 'TOKEN_CLEANUP_SUCCESS', $details, $ip]);

    if ($is_cli) {
        echo "[SUCCESS] " . $details . "\n";
        exit(0);
    } else {
        $_SESSION['message'] = $details;
        header('Location: ../settings.php#tab-maintenance');
        exit;
    }

} catch (Exception $e) {
    if ($pdo->inTransaction()) {
        $pdo->rollBack();
    }
    
    $errorDetails = 'Token maintenance failed: ' . $e->getMessage();
    
    // Log cron / maintenance failure
    try {
        $actor_id = $is_cli ? null : ($current_user['id'] ?? null);
        $ip = $is_cli ? '127.0.0.1' : ($_SERVER['REMOTE_ADDR'] ?? '127.0.0.1');
        $logStmt = $pdo->prepare("INSERT INTO audit_logs (`user_id`, `action`, `details`, `ip address`, `created at`) VALUES (?, ?, ?, ?, NOW())");
        $logStmt->execute([$actor_id, 'TOKEN_CLEANUP_FAIL', $errorDetails, $ip]);
    } catch (Exception $logEx) {}

    if ($is_cli) {
        fwrite(STDERR, "[ERROR] " . $errorDetails . "\n");
        exit(1);
    } else {
        $_SESSION['error'] = $errorDetails;
        header('Location: ../settings.php#tab-maintenance');
        exit;
    }
}
