<?php
// verify_email.php - Validates the 24-hour email verification token
require_once '../db/db.php';
session_start();

$message = '';
$error = '';

$token = $_GET['token'] ?? '';

if (empty($token)) {
    $error = "No verification token provided.";
} else {
    $stmt = $pdo->prepare("SELECT id, email_verified, token_expires_at FROM users WHERE verification_token = ?");
    $stmt->execute([$token]);
    $user = $stmt->fetch();

    if (!$user) {
        $error = "Invalid verification token.";
    } elseif ($user['email_verified']) {
        $message = "Your email has already been verified. You can log in.";
    } else {
        $current_time = date('Y-m-d H:i:s');
        
        if ($current_time > $user['token_expires_at']) {
            $error = "This verification link has expired (exceeded the 24-hour window). Please register again or request a new link.";
        } else {
            $update = $pdo->prepare("UPDATE users SET email_verified = 1, verification_token = NULL, token_expires_at = NULL WHERE id = ?");
            
            if ($update->execute([$user['id']])) {
                $message = "Email successfully verified! Your account is now active. You can proceed to log in.";
            } else {
                $error = "An error occurred while verifying your email. Please try again.";
            }
        }
    }
}
?>

    <?php require_once '../partials/header.php'; ?>

    <div class="search-box-container verify-email-container" role="region" aria-label="Email Verification Status">
        <h3>Email Verification Status</h3>

        <?php if (!empty($error)): ?>
            <p class="alert-danger" style="margin: 1.5rem 0;"><strong><?php echo htmlspecialchars($error); ?></strong></p>
        <?php endif; ?>
        
        <?php if (!empty($message)): ?>
            <p class="alert-success" style="margin: 1.5rem 0;"><strong><?php echo htmlspecialchars($message); ?></strong></p>
            <p><a href="login.php" class="btn">Click here to log in</a></p>
        <?php endif; ?>
    </div>

    <?php require_once '../partials/footer.php'; ?>
