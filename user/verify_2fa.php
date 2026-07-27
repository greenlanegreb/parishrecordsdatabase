<?php
// verify_2fa.php - View for 2FA login challenge
session_start();

if (!isset($_SESSION['pending_2fa_user_id'])) {
    http_response_code(403);
    error_log("Unauthorized direct access attempt to verify_2fa.php from IP: " . $_SERVER['REMOTE_ADDR']);
    header('Location: login.php');
    exit;
}

$error = $_SESSION['error'] ?? '';
unset($_SESSION['error']);
?>

    <?php require_once '../partials/header.php'; ?>

    <div class="search-box-container verify-2fa-container" role="region" aria-label="2FA Verification">
        <h3>Two-Factor Authentication</h3>
        <p style="font-size: 0.95rem; margin-bottom: 1.5rem;">Enter the 6-digit code from your authenticator app, or use an emergency backup recovery code.</p>

        <?php if (!empty($error)): ?>
            <p class="alert-danger" style="margin-bottom: 1rem;"><strong><?php echo htmlspecialchars($error); ?></strong></p>
        <?php endif; ?>

        <form method="POST" action="actions/save_verify_2fa.php">
            <label for="code" style="display: block; font-weight: bold; margin-bottom: 0.5rem;">Verification Code / Backup Code:</label>
            <input type="text" id="code" name="code" required autofocus class="verify-2fa-input" aria-label="Enter authenticator or backup code">
            <button type="submit" class="btn" style="width: 100%;">Verify & Log In</button>
        </form>

        <p style="text-align: center; margin-top: 1.5rem;"><a href="login.php" style="color: var(--text-color); font-size: 0.9rem;">Back to Login</a></p>
    </div>

    <?php require_once '../partials/footer.php'; ?>
