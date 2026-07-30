<?php
// set_password.php - View for setting new password via secure token
require_once '../db/db.php';
require_once '../db/auth_helpers.php';
require_once '../includes/functions.php';
session_start();

$token = $_GET['token'] ?? '';

if (empty($token)) {
    exit("Invalid or missing setup token.");
}

$stmt = $pdo->prepare("SELECT id, username FROM users WHERE verification_token = ? AND token_expires_at > NOW()");
$stmt->execute([$token]);
$user = $stmt->fetch();

if (!$user) {
    exit("This password setup link is invalid or has expired.");
}

$message = $_SESSION['message'] ?? '';
$error = $_SESSION['error'] ?? '';
unset($_SESSION['message'], $_SESSION['error']);
?>

    <?php require_once '../partials/header.php'; ?>

    <?php if (!empty($error)): ?>
        <p class="alert-danger"><strong><?php echo htmlspecialchars($error); ?></strong></p>
    <?php endif; ?>
    <?php if (!empty($message)): ?>
        <p class="alert-success"><strong><?php echo htmlspecialchars($message); ?></strong></p>
        <p><a href="login.php" class="btn">Proceed to Login</a></p>
    <?php else: ?>
        <div class="search-box-container password-setup-container" role="region" aria-label="Password Setup">
            <h3 style="margin-bottom: 0.5rem;">Please Set Your Password for <?php echo htmlspecialchars($user['username']); ?></h3>
            <p style="color: #555; margin-bottom: 1.5rem; font-size: 0.95rem;">
                Welcome to your new account, <strong><?php echo htmlspecialchars($user['username']); ?></strong>! Please choose your password below.
            </p>

            <form method="POST" action="actions/save_password.php">
                <?php echo csrf_field(); ?>
                <input type="hidden" name="token" value="<?php echo htmlspecialchars($token); ?>">
                
                <label for="password">New Password (minimum 8 characters):</label><br>
                <input type="password" id="password" name="password" required class="password-setup-input" style="width: 100%; padding: 0.6rem; margin-top: 0.4rem; margin-bottom: 1rem;"><br>

                <label for="confirm_password">Confirm Password:</label><br>
                <input type="password" id="confirm_password" name="confirm_password" required class="password-setup-input" style="width: 100%; padding: 0.6rem; margin-top: 0.4rem; margin-bottom: 1rem;"><br>

                <div style="margin-bottom: 1.5rem;">
                    <label style="cursor: pointer; font-size: 0.9rem; display: inline-flex; align-items: center; gap: 0.5rem;">
                        <input type="checkbox" id="show_passwords" onclick="togglePasswordVisibility()" style="transform: scale(1.1); cursor: pointer;">
                        <span>Show password</span>
                    </label>
                </div>

                <button type="submit" class="btn" style="margin-top: 0.5rem;">Save Password</button>
            </form>
        </div>

        <script>
        function togglePasswordVisibility() {
            const pwd = document.getElementById('password');
            const confirmPwd = document.getElementById('confirm_password');
            const type = document.getElementById('show_passwords').checked ? 'text' : 'password';
            pwd.type = type;
            confirmPwd.type = type;
        }
        </script>
    <?php endif; ?>

    <?php require_once '../partials/footer.php'; ?>
