<?php
// admin/create_user.php - Admin interface view for inviting/creating new users securely
require_once '../db/db.php';
require_once '../db/auth_helpers.php';
session_start();

// Enforce dynamic permission-based access control
require_permission($pdo, 'manage_users', 'Manage user accounts, roles, and status');

// Pull session flash messages safely
$error = $_SESSION['error'] ?? '';
$message = $_SESSION['message'] ?? '';
unset($_SESSION['error'], $_SESSION['message']);

// Fetch all available roles dynamically from the database
$roles_list = $pdo->query("SELECT id, role_name FROM roles ORDER BY id ASC")->fetchAll(PDO::FETCH_ASSOC);
?>

    <?php require_once '../partials/header.php'; ?>

    <?php if (!empty($error)): ?>
        <p class="alert-danger" role="alert"><strong><?php echo htmlspecialchars($error); ?></strong></p>
    <?php endif; ?>
    <?php if (!empty($message)): ?>
        <p class="alert-success" role="status"><strong><?php echo htmlspecialchars($message); ?></strong></p>
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

            <label for="role_id">User Role:</label><br>
            <select id="role_id" name="role_id" class="profile-input suggest-edit-select">
                <?php foreach ($roles_list as $r): ?>
                    <option value="<?php echo $r['id']; ?>" <?php echo ($r['role_name'] === 'user') ? 'selected' : ''; ?>>
                        <?php echo htmlspecialchars(ucwords($r['role_name'])); ?>
                    </option>
                <?php endforeach; ?>
            </select><br>

            <button type="submit" class="btn">Create User & Send Invite</button>
        </form>
    </div>

    <?php require_once '../partials/footer.php'; ?>
