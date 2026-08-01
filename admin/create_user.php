<?php
// admin/create_user.php - Admin interface view for inviting/creating new users securely
require_once '../db/db.php';
require_once '../db/auth_helpers.php';
require_once '../includes/functions.php';

// Ensure the users module is enabled; otherwise block direct access
if (!is_module_enabled($pdo, 'users')) {
    http_response_code(403);
    exit('403 Forbidden: The User Management module is currently disabled.');
}

// Standard admin bootstrap (permission + flash messages)
$current_user = require_admin_page($pdo, 'invite_users', 'Create and invite new users');
$message = $GLOBALS['message'] ?? '';
$error   = $GLOBALS['error']   ?? '';

// Catch pre-filled data from volunteer portal bridge if present
$prefill_email = trim($_GET['email'] ?? '');
$prefill_first = trim($_GET['first_name'] ?? '');
$prefill_surname = trim($_GET['surname'] ?? '');
$volunteer_id  = intval($_GET['volunteer_id'] ?? 0);

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

<div class="search-box-container profile-container" style="max-width: 600px; margin: 2rem auto;">
    <h3><?php echo htmlspecialchars(__('create_user.heading')); ?></h3>
    <p><?php echo htmlspecialchars(__('create_user.subheading')); ?></p>
    <form method="POST" action="actions/save_user.php">
        <?php echo csrf_field(); ?>
        <?php if ($volunteer_id > 0): ?>
            <input type="hidden" name="volunteer_id" value="<?php echo $volunteer_id; ?>">
        <?php endif; ?>

        <!-- First Name & Surname -->
        <div style="display: flex; gap: 1rem; margin-bottom: 1rem;">
            <div style="flex: 1;">
                <label for="first_name"><strong><?php echo htmlspecialchars(__('create_user.first_name')); ?></strong></label><br>
                <input type="text" id="first_name" name="first_name" value="<?php echo htmlspecialchars($prefill_first); ?>" class="profile-input" style="width:100%; padding:0.4rem;">
            </div>
            <div style="flex: 1;">
                <label for="surname"><strong><?php echo htmlspecialchars(__('create_user.surname')); ?></strong></label><br>
                <input type="text" id="surname" name="surname" value="<?php echo htmlspecialchars($prefill_surname); ?>" class="profile-input" style="width:100%; padding:0.4rem;">
            </div>
        </div>

        <div style="margin-bottom: 1rem;">
            <label for="username"><strong><?php echo htmlspecialchars(__('create_user.username_label')); ?></strong></label><br>
            <input type="text" id="username" name="username" placeholder="<?php echo htmlspecialchars(__('create_user.username_placeholder')); ?>" class="profile-input" style="width:100%; padding:0.4rem;">
            <small style="color:#666;"><?php echo htmlspecialchars(__('create_user.username_help')); ?></small>
        </div>
        
        <div style="margin-bottom: 1rem;">
            <label for="email"><strong><?php echo htmlspecialchars(__('create_user.email_label')); ?></strong> <span style="color:red;">*</span></label><br>
            <input type="email" id="email" name="email" value="<?php echo htmlspecialchars($prefill_email); ?>" required class="profile-input" style="width:100%; padding:0.4rem;">
        </div>
        
        <div style="margin-bottom: 1.5rem;">
            <label for="role_id"><strong><?php echo htmlspecialchars(__('create_user.role_label')); ?></strong></label><br>
            <select id="role_id" name="role_id" class="profile-input suggest-edit-select" style="width:100%; padding:0.4rem;">
                <?php foreach ($roles_list as $r): ?>
                    <option value="<?php echo $r['id']; ?>" <?php echo ($r['role_name'] === 'user') ? 'selected' : ''; ?>>
                        <?php echo htmlspecialchars(ucwords($r['role_name'])); ?>
                    </option>
                <?php endforeach; ?>
            </select>
        </div>
        
        <button type="submit" class="btn"><?php echo htmlspecialchars(__('create_user.submit_btn')); ?></button>
    </form>
</div>

<?php require_once '../partials/footer.php'; ?>
