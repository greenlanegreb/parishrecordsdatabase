<?php
// register.php - User registration view
session_start();
require_once '../db/db.php';
require_once '../db/auth_helpers.php';
require_once '../includes/functions.php';

// Ensure the users module is enabled; otherwise block access to registration
if (!is_module_enabled($pdo, 'users')) {
    http_response_code(403);
    exit('403 Forbidden: The User Management module is currently disabled.');
}

$message = $_SESSION['message'] ?? '';
$error = $_SESSION['error'] ?? '';
unset($_SESSION['message'], $_SESSION['error']);
?>

    <?php require_once '../partials/header.php'; ?>

    <div class="search-box-container register-container" role="region" aria-label="<?php echo htmlspecialchars(__('register.aria_region')); ?>">
        <h3><?php echo htmlspecialchars(__('register.heading')); ?></h3>

        <?php if (!empty($error)): ?>
            <p class="alert-danger"><strong><?php echo htmlspecialchars($error); ?></strong></p>
        <?php endif; ?>
        <?php if (!empty($message)): ?>
            <p class="alert-success"><strong><?php echo htmlspecialchars($message); ?></strong></p>
        <?php endif; ?>
        
        <form method="POST" action="actions/save_register.php">
            <?php echo csrf_field(); ?>
            <label for="username"><?php echo htmlspecialchars(__('register.username_label')); ?></label><br>
            <input type="text" id="username" name="username" required class="register-input"><br>

            <label for="email"><?php echo htmlspecialchars(__('forgot_password.email_label')); ?></label><br>
            <input type="email" id="email" name="email" required class="register-input"><br>

            <label for="password"><?php echo htmlspecialchars(__('login.password_label')); ?></label><br>
            <input type="password" id="password" name="password" required class="register-input"><br>

            <button type="submit" class="btn"><?php echo htmlspecialchars(__('register.submit_btn')); ?></button>
        </form>
    </div>

    <?php require_once '../partials/footer.php'; ?>
