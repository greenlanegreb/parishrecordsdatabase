<?php
// feedback.php - Public Feedback Submission Form View
session_start();

require_once 'db/db.php';
require_once 'db/auth_helpers.php';
require_once 'includes/functions.php';

// Ensure the feedback module is enabled; otherwise block access
if (!is_module_enabled($pdo, 'feedback')) {
    http_response_code(403);
    exit('403 Forbidden: The Feedback Submissions module is currently disabled.');
}

// Enforce dynamic permission check (automatically registers 'submit_feedback' if new)
$permission_key = 'submit_feedback';
$permission_desc = 'Allows submitting public feedback and inquiries';

// Ensure the permission exists in the database first using require_permission's underlying registration or direct check
$p_check = $pdo->prepare("SELECT id FROM permissions WHERE permission_key = ?");
$p_check->execute([$permission_key]);
$perm_id = $p_check->fetchColumn();

if (!$perm_id) {
    $ins_p = $pdo->prepare("INSERT IGNORE INTO permissions (permission_key, description) VALUES (?, ?)");
    $ins_p->execute([$permission_key, $permission_desc]);
}

$current_user = function_exists('get_current_user_data') ? get_current_user_data($pdo) : null;

$has_guest_permission = false;
if ($perm_id) {
    $gp_stmt = $pdo->prepare("
        SELECT COUNT(*) FROM role_permissions rp
        JOIN roles r ON rp.role_id = r.id
        WHERE rp.permission_id = ? AND LOWER(r.role_name) IN ('guest', 'public', 'visitor')
    ");
    $gp_stmt->execute([$perm_id]);
    $has_guest_permission = ($gp_stmt->fetchColumn() > 0);
}

if (!$current_user && !$has_guest_permission) {
    // If not logged in and guests don't have permission, require permission (which will prompt login/error)
    $current_user = require_permission($pdo, $permission_key, $permission_desc);
}

$system_name = get_system_name($pdo);

$message = $_SESSION['message'] ?? '';
$error = $_SESSION['error'] ?? '';
unset($_SESSION['message'], $_SESSION['error']);
?>

<?php require_once 'partials/header.php'; ?>

<div class="search-box-container feedback-container" role="region" aria-label="Feedback Form">
    <h3>Send Us Your Feedback</h3>
    <p>We value your thoughts, suggestions, or reports regarding the <?php echo htmlspecialchars($system_name); ?>.</p>

    <?php if (!empty($error)): ?>
        <p class="alert-danger" role="alert"><strong><?php echo htmlspecialchars($error); ?></strong></p>
    <?php endif; ?>
    <?php if (!empty($message)): ?>
        <p class="alert-success" role="status"><strong><?php echo htmlspecialchars($message); ?></strong></p>
    <?php endif; ?>

    <form method="POST" action="actions/save_feedback.php">
        <?php echo csrf_field(); ?>
        <!-- Hidden Honeypot Field to trap spam bots -->
        <div class="honeypot-field" aria-hidden="true">
            <label for="website_url">Leave this field blank:</label>
            <input type="text" id="website_url" name="website_url" value="" autocomplete="off" tabindex="-1">
        </div>

        <label for="name">Your Name:</label><br>
        <input type="text" id="name" name="name" required class="feedback-input" aria-label="Your Name"><br>

        <label for="email">Your Email Address:</label><br>
        <input type="email" id="email" name="email" required class="feedback-input" aria-label="Your Email Address"><br>

        <label for="message">Your Message / Feedback:</label><br>
        <textarea id="message" name="message" rows="5" required class="feedback-textarea" aria-label="Your Message"></textarea><br>

        <button type="submit" class="btn">Submit Feedback</button>
    </form>
</div>

<?php require_once 'partials/footer.php'; ?>
