<?php
// register.php - User registration view
session_start();

$message = $_SESSION['message'] ?? '';
$error = $_SESSION['error'] ?? '';
unset($_SESSION['message'], $_SESSION['error']);
?>

    <?php require_once '../partials/header.php'; ?>

    <div class="search-box-container register-container" role="region" aria-label="User Registration">
        <h3>Register New Account</h3>

        <?php if (!empty($error)): ?>
            <p class="alert-danger"><strong><?php echo htmlspecialchars($error); ?></strong></p>
        <?php endif; ?>
        <?php if (!empty($message)): ?>
            <p class="alert-success"><strong><?php echo htmlspecialchars($message); ?></strong></p>
        <?php endif; ?>
        
        <form method="POST" action="actions/save_register.php">
            <label for="username">Username:</label><br>
            <input type="text" id="username" name="username" required class="register-input"><br>

            <label for="email">Email Address:</label><br>
            <input type="email" id="email" name="email" required class="register-input"><br>

            <label for="password">Password:</label><br>
            <input type="password" id="password" name="password" required class="register-input"><br>

            <button type="submit" class="btn">Register</button>
        </form>
    </div>

    <?php require_once '../partials/footer.php'; ?>
