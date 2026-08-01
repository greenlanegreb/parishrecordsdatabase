<?php
// set_password.php - View for setting new password via secure token
require_once '../db/db.php';
require_once '../db/auth_helpers.php';
require_once '../includes/functions.php';
session_start();

$token = $_GET['token'] ?? '';

if (empty($token)) {
    exit(__('set_password.exit_invalid_token'));
}

$stmt = $pdo->prepare("SELECT id, username FROM users WHERE verification_token = ? AND token_expires_at > NOW()");
$stmt->execute([$token]);
$user = $stmt->fetch();

if (!$user) {
    exit(__('set_password.exit_expired_token'));
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
        <p><a href="login.php" class="btn"><?php echo htmlspecialchars(__('set_password.proceed_login_btn')); ?></a></p>
    <?php else: ?>
        <div class="search-box-container password-setup-container" role="region" aria-label="<?php echo htmlspecialchars(__('set_password.aria_region')); ?>">
            <h3 style="margin-bottom: 0.5rem;"><?php echo htmlspecialchars(sprintf(__('set_password.heading_format'), $user['username'])); ?></h3>
            <p style="color: #555; margin-bottom: 1.5rem; font-size: 0.95rem;">
                <?php echo htmlspecialchars(sprintf(__('set_password.subheading_format'), $user['username'])); ?>
            </p>

            <form method="POST" action="actions/save_password.php">
                <?php echo csrf_field(); ?>
                <input type="hidden" name="token" value="<?php echo htmlspecialchars($token); ?>">
                
                <label for="password"><?php echo htmlspecialchars(__('set_password.new_password_label')); ?></label><br>
                <input type="password" id="password" name="password" required class="password-setup-input" style="width: 100%; padding: 0.6rem; margin-top: 0.4rem; margin-bottom: 1rem;"><br>

                <label for="confirm_password"><?php echo htmlspecialchars(__('set_password.confirm_password_label')); ?></label><br>
                <input type="password" id="confirm_password" name="confirm_password" required class="password-setup-input" style="width: 100%; padding: 0.6rem; margin-top: 0.4rem; margin-bottom: 1rem;"><br>

                <div style="margin-bottom: 1.5rem;">
                    <label style="cursor: pointer; font-size: 0.9rem; display: inline-flex; align-items: center; gap: 0.5rem;">
                        <input type="checkbox" id="show_passwords" onclick="togglePasswordVisibility()" style="transform: scale(1.1); cursor: pointer;">
                        <span><?php echo htmlspecialchars(__('set_password.show_password_label')); ?></span>
                    </label>
                </div>

                <button type="submit" class="btn" style="margin-top: 0.5rem;"><?php echo htmlspecialchars(__('set_password.save_password_btn')); ?></button>
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
