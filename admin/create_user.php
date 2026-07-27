<?php
// admin/create_user.php - Admin interface view for inviting/creating new users securely
require_once '../db/db.php';
require_once '../db/auth_helpers.php';
session_start();

// Enforce strict administrator privileges via central helper
require_role($pdo, ['admin']);

// Pull session flash messages safely
$error = $_SESSION['error'] ?? '';
$message = $_SESSION['message'] ?? '';
unset($_SESSION['error'], $_SESSION['message']);
?>

    <?php require_once '../partials/header.php'; ?>

    <?php if (!empty($error)): ?>
        <p class="alert-danger"><strong><?php echo htmlspecialchars($error); ?></strong></p>
    <?php endif; ?>
    <?php if (!empty($message)): ?>
        <p class="alert-success"><strong><?php echo htmlspecialchars($message); ?></strong></p>
    <?php endif; ?>

    <div class="search-box-container profile-container">
        <h3>New User Invitation Form</h3>
        <p>This will generate a secure 24-hour setup link and email it directly to the user.</p>
        <form method="POST" action="actions/save_user.php">
            <?php echo csrf_field(); ?>
            <label for="username">Username:</label><br>
            <input type="text" id="username" name="username" required class="profile-input"><br>

            <label for="email">Email Address:</label><br>
            <input type="email" id="email" name="email" required class="profile-input"><br>

            <label for="role">User Role:</label><br>
            <select id="role" name="role" class="profile-input suggest-edit-select">
                <option value="user">Standard User</option>
                <option value="moderator">Moderator</option>
                <option value="admin">Administrator</option>
            </select><br>

            <button type="submit" class="btn">Create User & Send Invite</button>
        </form>
    </div>

    <?php require_once '../partials/footer.php'; ?>
