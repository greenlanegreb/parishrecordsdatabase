<?php
declare(strict_types=1);
/**
 * MIGRATED FILE MAPPING
 * ---------------------
 * Original Old File: partials/notices_banner.php
 * Migrated Date: 2026-08-05 06:45:00
 */

if (!isset($pdo) || !($pdo instanceof PDO)) {
    return;
}

if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

$queryGet = $_GET;

// Handle dismiss action in session
if (isset($queryGet['dismiss_notice'])) {
    $dismissId = (int)$queryGet['dismiss_notice'];
    if (!isset($_SESSION['dismissed_notices']) || !is_array($_SESSION['dismissed_notices'])) {
        $_SESSION['dismissed_notices'] = [];
    }
    $_SESSION['dismissed_notices'][$dismissId] = true;
    
    $serverUri = isset($_SERVER['REQUEST_URI']) && is_string($_SERVER['REQUEST_URI']) ? $_SERVER['REQUEST_URI'] : '/index.php';
    $cleanUrl = strtok($serverUri, '?') ?: '/index.php';
    header("Location: " . $cleanUrl);
    exit;
}

// Fetch active notices ordered by display preference
/** @var array<int, array<string, mixed>> $allNotices */
$allNotices = [];
try {
    $noticesStmt = $pdo->query("SELECT * FROM site_notices WHERE is_active = 1 ORDER BY display_order ASC");
    if ($noticesStmt !== false) {
        $allNotices = $noticesStmt->fetchAll(PDO::FETCH_ASSOC);
    }
} catch (Exception $e) {
    $allNotices = [];
}

$userIsLoggedIn = isset($_SESSION['user_id']);
/** @var array{id: int|string, role?: string}|null $currentUserData */
$currentUserData = null;
if ($userIsLoggedIn && function_exists('get_current_user_data')) {
    $currentUserData = get_current_user_data($pdo);
}

foreach ($allNotices as $notice) {
    $noticeId = isset($notice['id']) ? (int)$notice['id'] : 0;
    
    // Skip if dismissed in the current session
    if (isset($_SESSION['dismissed_notices']) && is_array($_SESSION['dismissed_notices']) && isset($_SESSION['dismissed_notices'][$noticeId])) {
        continue;
    }

    $targetRolesRaw = isset($notice['target_roles']) && is_string($notice['target_roles']) ? $notice['target_roles'] : 'everyone';
    $targetRoles = array_map('trim', explode(',', $targetRolesRaw));
    $isVisible = false;

    // Evaluate target audience based on current session context and roles
    if (in_array('everyone', $targetRoles, true)) {
        $isVisible = true;
    } elseif (!$userIsLoggedIn && in_array('public', $targetRoles, true)) {
        $isVisible = true;
    } elseif ($userIsLoggedIn) {
        $userRole = ($currentUserData !== false && $currentUserData !== null && isset($currentUserData['role']) && is_string($currentUserData['role'])) ? $currentUserData['role'] : '';
        
        if (in_array($userRole, $targetRoles, true)) {
            $isVisible = true;
        }
    }

    if (!$isVisible) {
        continue;
    }

    $isDismissible = !empty($notice['is_dismissible']);
    $noticeTitle = isset($notice['title']) && is_string($notice['title']) ? $notice['title'] : '';
    $noticeContent = isset($notice['content']) && is_string($notice['content']) ? $notice['content'] : '';
    ?>
    <div class="alert alert-info border-0 shadow-sm mb-3 position-relative site-notice-banner" data-notice-id="<?= $noticeId ?>" role="alert">
        <?php if ($isDismissible): ?>
            <a href="?dismiss_notice=<?= $noticeId ?>" class="btn-close position-absolute top-0 end-0 m-3" aria-label="<?= htmlspecialchars(__('notices_banner.close_title'), ENT_QUOTES, 'UTF-8') ?>" title="<?= htmlspecialchars(__('notices_banner.close_title'), ENT_QUOTES, 'UTF-8') ?>"></a>
        <?php endif; ?>
        
        <?php if ($noticeTitle !== ''): ?>
            <h4 class="alert-heading h6 fw-bold mb-1"><?= htmlspecialchars($noticeTitle, ENT_QUOTES, 'UTF-8') ?></h4>
        <?php endif; ?>
        
        <div class="mb-0 small" style="line-height: 1.5;"><?= nl2br(htmlspecialchars($noticeContent, ENT_QUOTES, 'UTF-8')) ?></div>
    </div>
    <?php
}
