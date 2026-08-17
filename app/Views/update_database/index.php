<?php
/**
 * MIGRATED FILE MAPPING
 * ---------------------
 * Original Old File: roote/update_database.php
 * Migrated Date: 2026-08-05 06:52:42
 */
declare(strict_types=1);
/** Translate with fallback when lang key is missing. @return string */
$__t = static function (string $key, string $fallback = ''): string {
    $v = function_exists('__') ? (string) __($key) : $key;
    if ($v !== $key && $v !== '') {
        return $v;
    }
    return $fallback !== '' ? $fallback : $key;
};


/**
 * @var int $schemaCurrent
 * @var int $schemaLatest
 * @var string $error
 * @var string $message
 * @var bool $emergencyOk
 */
$emergencyOk = $emergencyOk ?? false;

require_once ROOT_PATH . '/partials/header.php';
$basePath = defined('BASE_PATH') ? rtrim(BASE_PATH, '/') : '';
?>
<div class="container my-5" style="max-width: 600px;">
    <div class="card border-0 shadow-sm p-4 bg-white">
        <h2 class="h4 fw-bold text-danger mb-2"><?= htmlspecialchars($__t('update_database.heading', 'Database Update Required'), ENT_QUOTES, 'UTF-8') ?></h2>
        <p class="text-muted small mb-3"><?= htmlspecialchars($__t('update_database.subheading', 'Review schema status and apply pending database migrations safely.'), ENT_QUOTES, 'UTF-8') ?></p>

        <div class="alert alert-light border small py-2 mb-4">
            <?= htmlspecialchars($__t('update_database.current_version', 'Current Schema Version:'), ENT_QUOTES, 'UTF-8') ?>
            <strong class="text-dark"><?= (int) $schemaCurrent ?></strong><br>
            <?= htmlspecialchars($__t('update_database.latest_version', 'Latest Available Version:'), ENT_QUOTES, 'UTF-8') ?>
            <strong class="text-dark"><?= (int) $schemaLatest ?></strong>
        </div>

        <?php if ($emergencyOk): ?>
            <div class="alert alert-warning small mb-3">
                Emergency migration access is active (<code>db/ALLOW_EMERGENCY_MIGRATE</code>).
                Run any pending updates, then remove this file so the updater is not open without login.
            </div>
        <?php endif; ?>

        <?php if ($error !== ''): ?>
            <div class="alert alert-danger shadow-sm mb-3" role="alert">
                <?= htmlspecialchars($error, ENT_QUOTES, 'UTF-8') ?>
            </div>
        <?php endif; ?>

        <?php if ($message !== ''): ?>
            <div class="alert alert-success shadow-sm mb-3" role="alert">
                <div><?= htmlspecialchars($message, ENT_QUOTES, 'UTF-8') ?></div>
                <?php if ($schemaCurrent >= $schemaLatest): ?>
                    <div class="mt-2">
                        <a href="<?= htmlspecialchars($basePath, ENT_QUOTES, 'UTF-8') ?>/login"
                           class="alert-link fw-bold text-success text-decoration-none">
                            <?= htmlspecialchars($__t('update_database.proceed_login', 'Proceed to Login'), ENT_QUOTES, 'UTF-8') ?> &rarr;
                        </a>
                    </div>
                <?php endif; ?>
            </div>
        <?php endif; ?>

        <?php if ($schemaCurrent < $schemaLatest): ?>
            <form method="POST"
                  action="<?= htmlspecialchars($basePath, ENT_QUOTES, 'UTF-8') ?>/update-database"
                  onsubmit="return confirm('<?= htmlspecialchars($__t('update_database.confirm_prompt', 'Are you sure you want to run database updates?'), ENT_QUOTES, 'UTF-8') ?>');">
                <?= function_exists('csrf_field') ? csrf_field() : '' ?>
                <input type="hidden" name="action" value="migrate">
                <button type="submit" class="btn btn-primary w-100 fw-bold py-2">
                    <?= htmlspecialchars($__t('update_database.update_btn', 'Run Database Updates'), ENT_QUOTES, 'UTF-8') ?>
                </button>
            </form>
            <p class="text-muted small mt-2 mb-0">
               <?= htmlspecialchars($__t('update_database.backup_notice', 'Take a database backup before running updates when you can.'), ENT_QUOTES, 'UTF-8') ?>
            </p>
        <?php endif; ?>

        <?php if ($emergencyOk): ?>
            <hr class="my-4">
            <form method="POST" action="<?= htmlspecialchars($basePath, ENT_QUOTES, 'UTF-8') ?>/update-database">
                <?= function_exists('csrf_field') ? csrf_field() : '' ?>
                <input type="hidden" name="action" value="remove_emergency_flag">
                <button type="submit" class="btn btn-outline-secondary btn-sm w-100">
                   <?= htmlspecialchars($__t('update_database.remove_emergency_file', 'Remove emergency access file'), ENT_QUOTES, 'UTF-8') ?>
                </button>
            </form>
        <?php endif; ?>
    </div>
</div>
<?php require_once ROOT_PATH . '/partials/footer.php'; ?>
