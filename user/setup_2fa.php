<?php
// setup_2fa.php - Wizard view to generate a secret, show QR code, and activate 2FA
require_once '../db/db.php';
require_once '../db/auth_helpers.php';
session_start();

// Enforce dynamic permission check for 2FA setup (automatically registers 'setup_2fa' if new)
require_permission($pdo, 'setup_2fa', 'Allows setting up and configuring Google Authenticator 2FA');
$user = get_current_user_data($pdo);

if ($user['two_fa_enabled']) {
    header('Location: profile.php');
    exit;
}

function generate_base32_secret($length = 16) {
    $map = 'ABCDEFGHIJKLMNOPQRSTUVWXYZ234567';
    $secret = '';
    for ($i = 0; $i < $length; $i++) {
        $secret .= $map[random_int(0, 31)];
    }
    return $secret;
}

if (!isset($_SESSION['temp_2fa_secret'])) {
    $_SESSION['temp_2fa_secret'] = generate_base32_secret();
    
    $raw_codes = [];
    $hashed_codes = [];
    for ($i = 0; $i < 5; $i++) {
        $code = strtoupper(bin2hex(random_bytes(3)));
        $formatted_code = substr($code, 0, 3) . '-' . substr($code, 3, 3);
        $raw_codes[] = $formatted_code;
        $hashed_codes[] = password_hash($formatted_code, PASSWORD_DEFAULT);
    }
    $_SESSION['temp_raw_backup_codes'] = $raw_codes;
    $_SESSION['temp_hashed_backup_codes'] = json_encode($hashed_codes);
}

$secret = $_SESSION['temp_2fa_secret'];
$raw_backup_codes = $_SESSION['temp_raw_backup_codes'];

// Handle direct download of backup codes as a .txt file
if (isset($_GET['action']) && $_GET['action'] === 'download_codes') {
    if (!empty($raw_backup_codes)) {
        header('Content-Type: text/plain; charset=utf-8');
        header('Content-Disposition: attachment; filename="cakebread-database-backup-codes.txt"');
        echo "CAKEBREAD DATABASE - 2FA EMERGENCY BACKUP CODES\n";
        echo "================================================\n\n";
        echo "Keep these codes in a secure place. Each code can be used once\n";
        echo "if you lose access to your authenticator app:\n\n";
        foreach ($raw_backup_codes as $code) {
            echo " - " . $code . "\n";
        }
        exit;
    }
}

$error = $_SESSION['error'] ?? '';
unset($_SESSION['error']);

$issuer = "CakebreadDatabase";
$accountName = urlencode($user['username']);
$otpauthUrl = "otpauth://totp/{$issuer}:{$accountName}?secret={$secret}&issuer={$issuer}";
$qrCodeUrl = "https://api.qrserver.com/v1/create-qr-code/?size=200x200&data=" . urlencode($otpauthUrl);
?>

    <?php require_once '../partials/header.php'; ?>

    <div class="search-box-container setup-2fa-container" role="region" aria-label="<?php echo htmlspecialchars(__('setup_2fa.aria_region')); ?>">
        <h3 style="text-align: center;"><?php echo htmlspecialchars(__('setup_2fa.heading')); ?></h3>
        <p style="text-align: center;"><?php echo htmlspecialchars(__('setup_2fa.subheading')); ?></p>

        <?php if (!empty($error)): ?>
            <p class="alert-danger" style="text-align: center; margin: 1rem 0;"><strong><?php echo htmlspecialchars($error); ?></strong></p>
        <?php endif; ?>

        <!-- QR Code Display -->
        <div class="setup-2fa-qr-wrapper">
            <div class="setup-2fa-qr-inner">
                <img src="<?php echo htmlspecialchars($qrCodeUrl); ?>" alt="<?php echo htmlspecialchars(__('setup_2fa.qr_alt')); ?>" width="200" height="200">
            </div>
            <p style="font-size: 0.9rem; margin-top: 0.5rem;">
                <?php echo htmlspecialchars(__('setup_2fa.manual_prompt')); ?><br>
                <code class="setup-2fa-secret-code"><?php echo htmlspecialchars($secret); ?></code>
            </p>
        </div>

        <hr style="border: 0.0625rem solid var(--border-color); margin: 1.5rem 0;">

        <!-- Backup Recovery Codes Box -->
        <div class="backup-codes-box">
            <h4 style="margin-top: 0; color: var(--danger-color);"><?php echo htmlspecialchars(__('setup_2fa.backup_heading')); ?></h4>
            <p style="font-size: 0.9rem;"><?php echo __('setup_2fa.backup_desc'); ?></p>
            <ul class="backup-codes-list">
                <?php foreach ($raw_backup_codes as $rc): ?>
                    <li><?php echo htmlspecialchars($rc); ?></li>
                <?php endforeach; ?>
            </ul>
            <a href="setup_2fa.php?action=download_codes" class="btn btn-secondary" style="font-size: 0.9rem; text-decoration: none; display: inline-block;"><?php echo htmlspecialchars(__('setup_2fa.download_btn')); ?></a>
        </div>

        <form method="POST" action="actions/save_setup_2fa.php">
            <?php echo csrf_field(); ?>
            <label for="code" style="display: block; font-weight: bold; margin-bottom: 0.5rem;"><?php echo htmlspecialchars(__('setup_2fa.code_label')); ?></label>
            <input type="text" id="code" name="code" pattern="[0-9]{6}" maxlength="6" required autofocus class="setup-2fa-input" aria-label="<?php echo htmlspecialchars(__('setup_2fa.aria_code_input')); ?>">
            <button type="submit" class="btn" style="width: 100%;"><?php echo htmlspecialchars(__('setup_2fa.submit_btn')); ?></button>
        </form>
        
        <p style="text-align: center; margin-top: 1.5rem;"><a href="profile.php" style="color: var(--text-color); text-decoration: underline;"><?php echo htmlspecialchars(__('setup_2fa.cancel_link')); ?></a></p>
    </div>

    <?php require_once '../partials/footer.php'; ?>
