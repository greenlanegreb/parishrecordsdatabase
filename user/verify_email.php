<?php
// verify_email.php - Validates the 24-hour email verification token
require_once '../db/db.php';
session_start();

$message = '';
$error = '';

$token = $_GET['token'] ?? '';

if (empty($token)) {
    $error = __('verify_email.err_no_token');
} else {
    $stmt = $pdo->prepare("SELECT id, email_verified, token_expires_at FROM users WHERE verification_token = ?");
    $stmt->execute([$token]);
    $user = $stmt->fetch();

    if (!$user) {
        $error = __('verify_email.err_invalid_token');
    } elseif ($user['email_verified']) {
        $message = __('verify_email.msg_already_verified');
    } else {
        $current_time = date('Y-m-d H:i:s');
        
        if ($current_time > $user['token_expires_at']) {
            $error = __('verify_email.err_expired_token');
        } else {
            $update = $pdo->prepare("UPDATE users SET email_verified = 1, verification_token = NULL, token_expires_at = NULL WHERE id = ?");
            
            if ($update->execute([$user['id']])) {
                $message = __('verify_email.msg_success');
            } else {
                $error = __('verify_email.err_update_failed');
            }
        }
    }
}
?>

    <?php require_once '../partials/header.php'; ?>

    <div class="search-box-container verify-email-container" role="region" aria-label="<?php echo htmlspecialchars(__('verify_email.aria_region')); ?>">
        <h3><?php echo htmlspecialchars(__('verify_email.heading')); ?></h3>

        <?php if (!empty($error)): ?>
            <p class="alert-danger" style="margin: 1.5rem 0;"><strong><?php echo htmlspecialchars($error); ?></strong></p>
        <?php endif; ?>
        
        <?php if (!empty($message)): ?>
            <p class="alert-success" style="margin: 1.5rem 0;"><strong><?php echo htmlspecialchars($message); ?></strong></p>
            <p><a href="login.php" class="btn"><?php echo htmlspecialchars(__('verify_email.login_btn')); ?></a></p>
        <?php endif; ?>
    </div>

    <?php require_once '../partials/footer.php'; ?>
