<?php
// set_password.php - View for setting new password via secure token
require_once '../db/db.php';
session_start();

$token = $_GET['token'] ?? '';

if (empty($token)) {
    exit("Invalid or missing setup token.");
}

$stmt = $pdo->prepare("SELECT id, username FROM users WHERE reset_token = ? AND reset_expires > NOW()");
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
            <h3>Define Your New Password for <?php echo htmlspecialchars($user['username']); ?></h3>
            <form method="POST" action="actions/save_password.php">
                <input type="hidden" name="token" value="<?php echo htmlspecialchars($token); ?>">
                
                <label for="password">New Password (minimum 8 characters):</label><br>
                <input type="password" id="password" name="password" required class="password-setup-input"><br>

                <label for="confirm_password">Confirm Password:</label><br>
                <input type="password" id="confirm_password" name="confirm_password" required class="password-setup-input"><br>

                <button type="submit" class="btn">Save Password</button>
            </form>
        </div>
    <?php endif; ?>

    <?php require_once '../partials/footer.php'; ?>
