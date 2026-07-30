<?php
// update_database.php - Standalone unauthenticated schema update gateway
require_once 'db/db.php';
require_once 'includes/functions.php'; // Required for get_schema_version, set_schema_version, get_setting
require_once 'db/migrate_runner.php';

if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

// Safely calculate current schema version, falling back if site_settings table is missing
$schema_current = 0;
try {
    $schema_current = function_exists('get_schema_version') ? get_schema_version($pdo) : 0;
} catch (Exception $e) {
    $schema_current = 0; // If table doesn't exist yet, start from scratch
}

// Calculate latest available schema version from migration files
$schema_latest = $schema_current;
$migrations_dir = __DIR__ . '/db/migrations';

if (is_dir($migrations_dir)) {
    foreach (glob($migrations_dir . '/*.php') as $mig_file) {
        if (preg_match('/(\d+)_/', basename($mig_file), $m)) {
            $schema_latest = max($schema_latest, (int) $m[1]);
        }
    }
}

// If no updates are actually pending, block access and send them to login
if ($schema_current >= $schema_latest) {
    header('Location: user/login.php');
    exit;
}

$message = '';
$error = '';
$applied_list = [];

// Handle update execution submission
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    try {
        $result = run_pending_migrations($pdo, $migrations_dir);
        $applied_list = $result['applied'];
        $schema_current = $result['current'];
        
        if (!empty($applied_list)) {
            $message = "Database successfully updated! " . count($applied_list) . " migration(s) applied.";
        } else {
            $message = "Database is already up to date.";
        }
    } catch (Exception $e) {
        $error = "Migration failed: " . $e->getMessage();
    }
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>System Update Required - Parish Records Directory</title>
    <style>
        body { font-family: system-ui, -apple-system, sans-serif; background: #f8f9fa; color: #333; display: flex; justify-content: center; align-items: center; height: 100vh; margin: 0; }
        .update-card { background: #fff; border: 1px solid #dee2e6; border-radius: 8px; padding: 2rem; max-width: 500px; width: 100%; box-shadow: 0 4px 12px rgba(0,0,0,0.05); }
        h2 { margin-top: 0; color: #dc3545; }
        .btn { background: #007bff; color: #fff; border: none; padding: 0.75rem 1.5rem; font-size: 1rem; border-radius: 4px; cursor: pointer; width: 100%; font-weight: bold; margin-top: 1rem; }
        .btn:hover { background: #0056b3; }
        .alert-success { background: #d4edda; color: #155724; padding: 0.75rem; border-radius: 4px; margin-bottom: 1rem; }
        .alert-danger { background: #f8d7da; color: #721c24; padding: 0.75rem; border-radius: 4px; margin-bottom: 1rem; }
        .meta { font-size: 0.9rem; color: #666; margin: 1rem 0; }
    </style>
</head>
<body>
    <div class="update-card">
        <h2>⚠️ System Update Required</h2>
        <p>The application database structure is out of date and requires a schema update before normal operation can resume.</p>
        
        <div class="meta">
            Current Schema Version: <strong><?php echo (int) $schema_current; ?></strong><br>
            Latest Available Version: <strong><?php echo (int) $schema_latest; ?></strong>
        </div>

        <?php if (!empty($error)): ?>
            <div class="alert-danger"><?php echo htmlspecialchars($error); ?></div>
        <?php endif; ?>

        <?php if (!empty($message)): ?>
            <div class="alert-success">
                <?php echo htmlspecialchars($message); ?>
                <?php if ($schema_current >= $schema_latest): ?>
                    <br><br><a href="user/login.php" style="color: #155724; font-weight: bold;">Proceed to Login &rarr;</a>
                <?php endif; ?>
            </div>
        <?php endif; ?>

        <?php if ($schema_current < $schema_latest): ?>
            <form method="POST" onsubmit="return confirm('Have you backed up your database? Click OK to apply pending schema updates.');">
                <button type="submit" class="btn">Update Database Now</button>
            </form>
        <?php endif; ?>
    </div>
</body>
</html>
