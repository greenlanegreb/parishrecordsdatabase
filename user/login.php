<?php
// login.php - Secure Login View
session_start();

$error = $_SESSION['error'] ?? '';
unset($_SESSION['error']);
?>

    <?php require_once '../partials/header.php'; ?>

    <div class="search-box-container login-container" role="region" aria-label="User Login">
        <h3>User Login</h3>
        
        <?php if (!empty($error)): ?>
            <p class="alert-danger"><strong><?php echo htmlspecialchars($error); ?></strong></p>
        <?php endif; ?>

        <form method="POST" action="actions/authenticate.php">
            <label for="username">Username or Email:</label><br>
            <input type="text" id="username" name="username" required class="login-input"><br>

            <label for="password">Password:</label><br>
            <input type="password" id="password" name="password" required class="login-input"><br>

            <button type="submit" class="btn">Log In</button>
        </form>
    </div>

    <?php require_once '../partials/footer.php'; ?>
