<?php
// login.php - Secure Login View
session_start();
require_once '../db/auth_helpers.php';

// If the user is already authenticated, redirect them away from the login page
if (isset($_SESSION['user_id'])) {
    header('Location: ../data_entry.php'); // Matches where authenticate.php sends logged-in users
    exit;
}

$error = $_SESSION['error'] ?? '';
$message = $_SESSION['message'] ?? '';
unset($_SESSION['error'], $_SESSION['message']);
?>

    <?php require_once '../partials/header.php'; ?>

    <div class="search-box-container login-container" role="region" aria-label="User Login">
        <h3>User Login</h3>
        
        <?php if (!empty($error)): ?>
            <p class="alert-danger" role="alert"><strong><?php echo htmlspecialchars($error); ?></strong></p>
        <?php endif; ?>
        <?php if (!empty($message)): ?>
            <p class="alert-success" role="status"><strong><?php echo htmlspecialchars($message); ?></strong></p>
        <?php endif; ?>

        <form method="POST" action="actions/authenticate.php">
            <?php echo csrf_field(); ?>
            <label for="username">Username or Email:</label><br>
            <input type="text" id="username" name="username" required class="login-input" style="margin-bottom: 1rem;"><br>

            <label for="password">Password:</label><br>
            <input type="password" id="password" name="password" required class="login-input" style="margin-bottom: 1.5rem;"><br>

            <div style="display: flex; justify-content: space-between; align-items: center; flex-wrap: wrap; gap: 1rem;">
                <button type="submit" class="btn">Log In</button>
                <a href="forgot_password.php" style="font-size: 0.9rem; color: var(--primary-color, #007bff); text-decoration: underline;">Forgot Password?</a>
            </div>
        </form>
    </div>

    <?php require_once '../partials/footer.php'; ?>
