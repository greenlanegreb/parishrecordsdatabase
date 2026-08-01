<?php
// forgot_password.php - Request password reset view
session_start();
require_once '../db/auth_helpers.php';

$error = $_SESSION['error'] ?? '';
$message = $_SESSION['message'] ?? '';
unset($_SESSION['error'], $_SESSION['message']);
?>

    <?php require_once '../partials/header.php'; ?>

    <div class="search-box-container login-container" role="region" aria-label="<?php echo htmlspecialchars(__('forgot_password.aria_region')); ?>">
        <h3><?php echo htmlspecialchars(__('forgot_password.heading')); ?></h3>
        <p style="font-size: 0.95rem; color: #666; margin-bottom: 1.5rem;"><?php echo htmlspecialchars(__('forgot_password.subheading')); ?></p>

        <?php if (!empty($error)): ?>
            <p class="alert-danger" role="alert"><strong><?php echo htmlspecialchars($error); ?></strong></p>
        <?php endif; ?>
        <?php if (!empty($message)): ?>
            <p class="alert-success" role="status"><strong><?php echo htmlspecialchars($message); ?></strong></p>
        <?php endif; ?>

        <form method="POST" action="actions/save_forgot_password.php">
            <?php echo csrf_field(); ?>
            <label for="email"><?php echo htmlspecialchars(__('forgot_password.email_label')); ?></label><br>
            <input type="email" id="email" name="email" required class="login-input" autocomplete="email"><br>

            <button type="submit" class="btn" style="width: 100%; margin-top: 0.5rem;"><?php echo htmlspecialchars(__('forgot_password.submit_btn')); ?></button>
        </form>

        <p style="text-align: center; margin-top: 1.5rem;"><a href="login.php" style="color: var(--text-color); text-decoration: underline; font-size: 0.9rem;"><?php echo htmlspecialchars(__('forgot_password.back_login_link')); ?></a></p>
    </div>

    <?php require_once '../partials/footer.php'; ?>
