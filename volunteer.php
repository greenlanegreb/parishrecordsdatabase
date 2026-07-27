<?php
// volunteer.php - Public Volunteer Interest Submission Form View
session_start();

require_once 'db/db.php';
require_once 'db/auth_helpers.php';
require_once 'includes/functions.php';

// Ensure the volunteers module is enabled; otherwise block access
if (!is_module_enabled($pdo, 'volunteers')) {
    http_response_code(403);
    exit('403 Forbidden: The Volunteer Portal module is currently disabled.');
}

// ------------------------------------------------------------------
// Permission gate – only the guest role controls public access
// ------------------------------------------------------------------
$current_user = function_exists('get_current_user_data') ? get_current_user_data($pdo) : null;

// Only the guest role controls public (unauthenticated) access
$has_guest_permission = guest_has_permission($pdo, 'submit_volunteer');

if (!$current_user && !$has_guest_permission) {
    // Fall back to the normal require_permission (forces login)
    $current_user = require_permission($pdo, 'submit_volunteer', 'Allows submitting volunteer interest and transcription applications');
}

$system_name = get_system_name($pdo);

$message = $_SESSION['message'] ?? '';
$error   = $_SESSION['error']   ?? '';
unset($_SESSION['message'], $_SESSION['error']);
?>
<?php require_once 'partials/header.php'; ?>

<div class="search-box-container volunteer-container" role="region" aria-label="Volunteer Form">
    <h3>Volunteer for Data Entry</h3>
    <p>Interested in helping transcribe and contribute to the <?php echo htmlspecialchars($system_name); ?>? Let us know a little about yourself and any relevant experience.</p>

    <?php if (!empty($error)): ?>
        <p class="alert-danger" role="alert"><strong><?php echo htmlspecialchars($error); ?></strong></p>
    <?php endif; ?>
    <?php if (!empty($message)): ?>
        <p class="alert-success" role="status"><strong><?php echo htmlspecialchars($message); ?></strong></p>
    <?php endif; ?>

    <form method="POST" action="actions/save_volunteer.php">
        <?php echo csrf_field(); ?>
        <!-- Hidden Honeypot Field to trap spam bots -->
        <div class="honeypot-field" aria-hidden="true">
            <label for="website_url">Leave this field blank:</label>
            <input type="text" id="website_url" name="website_url" value="" autocomplete="off" tabindex="-1">
        </div>

        <label for="name">Your Name:</label><br>
        <input type="text" id="name" name="name" required class="volunteer-input" aria-label="Your Name"><br>

        <label for="email">Your Email Address:</label><br>
        <input type="email" id="email" name="email" required class="volunteer-input" aria-label="Your Email Address"><br>

        <label for="experience">Relevant Experience / Why you'd like to help:</label><br>
        <textarea id="experience" name="experience" rows="5" required class="volunteer-textarea" aria-label="Relevant Experience"></textarea><br>

        <button type="submit" class="btn">Submit Volunteer Interest</button>
    </form>
</div>

<?php require_once 'partials/footer.php'; ?>
