<?php
/**
 * install/index.php — First-time installer wizard for pRD
 *
 * Fresh install (empty DB only):
 *   schema_baseline.sql → seed_baseline.sql → permissions bootstrap
 *   → seed_defaults.php → stamp baseline schema_version (see install_baseline_schema_version)
 *   → run_pending_migrations() for versions above baseline (same as Update database)
 *   → create admin → modules → optional demo packs → lock
 *
 * Baseline SQL is a snapshot through version N; numbered migrations N+1…latest still run once.
 * Does NOT overwrite committed db/db.php — only writes config.local.php.
 */
declare(strict_types=1);

if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

$root = dirname(__DIR__);
$configLocal = $root . '/config.local.php';
$lockFile = $root . '/db/INSTALL_LOCK';
$functionsPath = $root . '/includes/functions.php';

if (is_file($functionsPath)) {
    require_once $functionsPath;
}

if (!function_exists('__')) {
    function __(string $key, array $replace = []): string
    {
        return $key;
    }
}

$serverMethod = isset($_SERVER['REQUEST_METHOD']) && is_string($_SERVER['REQUEST_METHOD'])
    ? $_SERVER['REQUEST_METHOD'] : 'GET';
$step = isset($_POST['step']) ? (int) $_POST['step'] : 1;

/**
 * Apply installer UI language into session. Returns cleaned code.
 */
function install_apply_language(string $code): string
{
    $cleaned = preg_replace('/[^a-zA-Z_]/', '', $code);
    if (!is_string($cleaned) || $cleaned === '') {
        $cleaned = 'en';
    }
    if (function_exists('set_language')) {
        set_language($cleaned);
    } else {
        $_SESSION['lang'] = $cleaned;
    }
    $_SESSION['install_lang_chosen'] = true;
    return $cleaned;
}

// First GET into an unfinished install: start from English (ignore stale session lang from earlier tests)
if (
    $serverMethod === 'GET'
    && empty($_SESSION['install_lang_chosen'])
    && !is_file($lockFile)
) {
    if (function_exists('set_language')) {
        set_language('en');
    } else {
        $_SESSION['lang'] = 'en';
    }
    // Do not set install_lang_chosen yet — only after the user picks (or continues)
}

// Back button
if ($serverMethod === 'POST' && isset($_POST['action']) && $_POST['action'] === 'back') {
    if ($step === 2) {
        $step = 1;
    } elseif ($step === 3) {
        $step = 2;
    } elseif ($step === 5) {
        $step = 2;
    } elseif ($step === 4) {
        $step = 7;
    } elseif ($step === 7) {
        $step = 5;
    }
}

// Language from dropdown
if ($serverMethod === 'POST' && isset($_POST['selected_lang']) && is_string($_POST['selected_lang'])) {
    install_apply_language($_POST['selected_lang']);
}

// onchange on step 1 posts apply_lang_only=1 — stay on language page so it re-renders translated
$langOnly = $serverMethod === 'POST'
    && isset($_POST['apply_lang_only'])
    && (string) $_POST['apply_lang_only'] === '1';

$error = '';
$message = '';

function install_db_is_empty(PDO $pdo): bool
{
    $stmt = $pdo->query('SHOW TABLES');
    $tables = $stmt !== false ? $stmt->fetchAll(PDO::FETCH_COLUMN) : [];
    return is_array($tables) && count($tables) === 0;
}

function install_import_sql(PDO $pdo, string $path): void
{
    if (!is_file($path)) {
        throw new RuntimeException("Missing SQL file: {$path}");
    }
    $sql = file_get_contents($path);
    if ($sql === false) {
        throw new RuntimeException("Could not read SQL file: {$path}");
    }
    $sql = preg_replace('/\/\*![0-9]{5}.*?\*\//s', '', $sql) ?? $sql;
    $sql = preg_replace('/^--.*$/m', '', $sql) ?? $sql;
    $sql = preg_replace('/^\/\*M!.*$/m', '', $sql) ?? $sql;
    $statements = array_filter(array_map('trim', explode(';', $sql)));
    $pdo->exec('SET FOREIGN_KEY_CHECKS=0');
    try {
        foreach ($statements as $statement) {
            if ($statement === '' || preg_match('/^(SET|USE)\s+/i', $statement)) {
                continue;
            }
            $pdo->exec($statement);
        }
    } finally {
        $pdo->exec('SET FOREIGN_KEY_CHECKS=1');
    }
}

function install_latest_schema_version(string $migrationsDir): int
{
    $latest = 0;
    if (is_dir($migrationsDir)) {
        $globFiles = glob($migrationsDir . '/*.php');
        if ($globFiles !== false) {
            foreach ($globFiles as $file) {
                $m = [];
                if (preg_match('/(\d+)_/', basename($file), $m)) {
                    $latest = max($latest, (int) $m[1]);
                }
            }
        }
    }
    return max(1, $latest);
}

/**
 * Schema version embodied by db/schema_baseline.sql.
 * Migrations with version > this number are applied once after import (same runner as Update database).
 * Bump this only when you regenerate schema_baseline.sql to include later migrations.
 */
function install_baseline_schema_version(): int
{
    return 27;
}

/**
 * App URL prefix for nested installs (e.g. /projects/prd-install-test).
 */
function install_detect_base_path(string $projectRoot): string
{
    // Same formula as includes/init.php (and public/404.php)
    $docRoot = rtrim(str_replace('\\', '/', $_SERVER['DOCUMENT_ROOT'] ?? ''), '/');
    $projectRoot = rtrim(str_replace('\\', '/', $projectRoot), '/');
    if ($docRoot !== '') {
        $base = str_replace($docRoot, '', $projectRoot);
        $base = rtrim(str_replace('\\', '/', $base), '/');
        if ($base !== '' && $base !== $projectRoot) {
            return $base;
        }
    }

    // Fallback when DOCUMENT_ROOT does not contain the project (or install script path is clearer)
    $script = isset($_SERVER['SCRIPT_NAME']) && is_string($_SERVER['SCRIPT_NAME'])
        ? str_replace('\\', '/', $_SERVER['SCRIPT_NAME'])
        : '';
    if ($script !== '' && preg_match('#^(.*)/install(?:/index\\.php)?$#', $script, $m)) {
        return rtrim($m[1], '/');
    }
    return '';
}


function install_write_config_local(string $path, string $host, string $db, string $user, string $pass): void
{
    $hostE = var_export($host, true);
    $dbE = var_export($db, true);
    $userE = var_export($user, true);
    $passE = var_export($pass, true);
    // Do NOT define BASE_PATH here — includes/init.php calculates it from DOCUMENT_ROOT
    // vs project root (same pattern as public/404.php). Defining '' would block that.
    $content = <<<PHP
<?php
// config.local.php - Generated by installer. Do not commit.
// Secrets only. BASE_PATH is set by includes/init.php when the app boots.
declare(strict_types=1);
\$host = {$hostE};
\$db = {$dbE};
\$user = {$userE};
\$pass = {$passE};
\$charset = 'utf8mb4';
\$dsn = "mysql:host=\$host;dbname=\$db;charset=\$charset";
\$options = [
    PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION,
    PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
    PDO::ATTR_EMULATE_PREPARES => false,
];
\$log_dir = __DIR__ . '/logs';
if (!is_dir(\$log_dir)) {
    @mkdir(\$log_dir, 0755, true);
}
try {
    \$pdo = new PDO(\$dsn, \$user, \$pass, \$options);
    \$pdo->exec("SET time_zone = '+00:00';");
    if (is_file(__DIR__ . '/db/maintenance_guard.php')) {
        require_once __DIR__ . '/db/maintenance_guard.php';
        if (function_exists('check_maintenance_mode')) {
            check_maintenance_mode(\$pdo);
        }
    }
} catch (PDOException \$e) {
    throw new PDOException('Database Connection Failed: ' . \$e->getMessage(), (int) \$e->getCode());
}
PHP;
    if (@file_put_contents($path, $content) === false) {
        $last = error_get_last();
        $detail = isset($last['message']) && is_string($last['message']) ? $last['message'] : 'unknown error';
        throw new RuntimeException('Could not write config.local.php: ' . $detail);
    }
}

function install_bootstrap_permissions(PDO $pdo): void
{
    $registryPath = dirname(__DIR__) . '/db/permissions_registry.php';
    if (!is_file($registryPath)) {
        throw new RuntimeException("Missing permissions registry: {$registryPath}");
    }
    /** @var array{permissions?: array<string, string>, default_roles?: array<string, array<int, string>>} $registry */
    $registry = include $registryPath;
    $permissions = isset($registry['permissions']) && is_array($registry['permissions']) ? $registry['permissions'] : [];
    $defaultRoles = isset($registry['default_roles']) && is_array($registry['default_roles']) ? $registry['default_roles'] : [];

    $insPerm = $pdo->prepare('INSERT IGNORE INTO permissions (permission_key, description) VALUES (?, ?)');
    foreach ($permissions as $key => $desc) {
        $insPerm->execute([$key, $desc]);
    }
    foreach ($defaultRoles as $roleName => $permKeys) {
        $stmt = $pdo->prepare('SELECT id FROM roles WHERE role_name = ?');
        $stmt->execute([$roleName]);
        $roleId = $stmt->fetchColumn();
        if (!$roleId) {
            continue;
        }
        foreach ($permKeys as $key) {
            $pStmt = $pdo->prepare('SELECT id FROM permissions WHERE permission_key = ?');
            $pStmt->execute([$key]);
            $permId = $pStmt->fetchColumn();
            if ($permId) {
                $mapStmt = $pdo->prepare('INSERT IGNORE INTO role_permissions (role_id, permission_id) VALUES (?, ?)');
                $mapStmt->execute([$roleId, $permId]);
            }
        }
    }
}

function install_seed_defaults(PDO $pdo, string $root): void
{
    $path = $root . '/db/seed_defaults.php';
    if (!is_file($path)) {
        return;
    }
    require_once $path;
    if (function_exists('seed_application_defaults')) {
        seed_application_defaults($pdo);
    }
}


function install_save_modules(PDO $pdo): void
{
    $modules = ['moderation', 'volunteers', 'feedback', 'users', 'leaderboard', 'maps'];
    $usersOn = isset($_POST['module_users_enabled']);
    $stmt = $pdo->prepare(
        "INSERT INTO site_settings (setting_key, setting_value) VALUES (?, ?)
         ON DUPLICATE KEY UPDATE setting_value = VALUES(setting_value)"
    );
    foreach ($modules as $mod) {
        $key = 'module_' . $mod . '_enabled';
        $val = '0';
        if ($mod === 'leaderboard') {
            $val = ($usersOn && isset($_POST[$key])) ? '1' : '0';
        } elseif (isset($_POST[$key])) {
            $val = '1';
        }
        $stmt->execute([$key, $val]);
    }
}

/**
 * After baseline import: record baseline version, then apply any newer migrations.
 *
 * @return array{applied: list<string>, current: int}
 */
function install_stamp_and_migrate(PDO $pdo, string $root): array
{
    $baseline = install_baseline_schema_version();
    if (function_exists('set_schema_version')) {
        set_schema_version($pdo, $baseline);
    } else {
        $stmt = $pdo->prepare(
            "INSERT INTO site_settings (setting_key, setting_value) VALUES ('schema_version', ?)
             ON DUPLICATE KEY UPDATE setting_value = ?"
        );
        $stmt->execute([(string) $baseline, (string) $baseline]);
    }

    $runner = $root . '/db/migrate_runner.php';
    if (!is_file($runner)) {
        throw new RuntimeException('Missing db/migrate_runner.php — cannot apply post-baseline migrations.');
    }
    require_once $runner;
    if (!function_exists('run_pending_migrations')) {
        throw new RuntimeException('run_pending_migrations() not available.');
    }
    // Need get_schema_version / set_schema_version from functions.php (already loaded when present)
    return run_pending_migrations($pdo, $root . '/db/migrations');
}

/** @deprecated use install_stamp_and_migrate — kept name avoided; callers updated */
function install_stamp_schema_version(PDO $pdo, string $root): void
{
    install_stamp_and_migrate($pdo, $root);
}

function install_load_pdo_from_config(string $configLocal): PDO
{
    if (!is_file($configLocal)) {
        throw new RuntimeException('No config.local.php found.');
    }
    require $configLocal;
    if (!isset($pdo) || !($pdo instanceof PDO)) {
        throw new RuntimeException('Database connection not available.');
    }
    return $pdo;
}


/**
 * Best-effort removal of the install folder. Cannot always delete the running script.
 *
 * @return array{ok: bool, message: string}
 */
function install_try_remove_install_dir(string $projectRoot): array
{
    $dir = rtrim($projectRoot, '/') . '/install';
    if (!is_dir($dir)) {
        return ['ok' => true, 'message' => (__('install.remove_already_gone') !== 'install.remove_already_gone')
            ? __('install.remove_already_gone')
            : 'The install folder is already gone.'];
    }
    $failed = [];
    $it = new RecursiveIteratorIterator(
        new RecursiveDirectoryIterator($dir, FilesystemIterator::SKIP_DOTS),
        RecursiveIteratorIterator::CHILD_FIRST
    );
    foreach ($it as $item) {
        $path = $item->getPathname();
        if ($item->isDir()) {
            if (!@rmdir($path)) {
                $failed[] = $path;
            }
        } else {
            if (!@unlink($path)) {
                $failed[] = $path;
            }
        }
    }
    if (!@rmdir($dir)) {
        $failed[] = $dir;
    }
    if ($failed === []) {
        return ['ok' => true, 'message' => (__('install.remove_ok') !== 'install.remove_ok')
            ? __('install.remove_ok')
            : 'The install folder has been removed.'];
    }
    return ['ok' => false, 'message' => (__('install.remove_failed') !== 'install.remove_failed')
        ? __('install.remove_failed')
        : 'pRD could not delete the install folder automatically (the web server often cannot delete the file it is running). In your hosting file manager or FTP, open the pRD folder and delete or rename the install directory.'];
}

function install_show_complete_page(?array $removeResult = null): void

{
    $basePath = install_detect_base_path(dirname(__DIR__));
    ?>
<!DOCTYPE html>
<html lang="<?= htmlspecialchars(function_exists('get_active_language') ? get_active_language() : 'en', ENT_QUOTES, 'UTF-8') ?>">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title><?= htmlspecialchars(__('install.complete_title'), ENT_QUOTES, 'UTF-8') ?></title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
</head>
<body class="bg-light d-flex align-items-center justify-content-center min-vh-100">
    <div class="container" style="max-width: 600px;">
        <div class="card border-0 shadow-sm p-5 text-center bg-white">
            <h1 class="h4 fw-bold text-success mb-3"><?= htmlspecialchars(__('install.complete_heading'), ENT_QUOTES, 'UTF-8') ?></h1>
            <p class="text-secondary small mb-4"><?= htmlspecialchars(__('install.complete_desc'), ENT_QUOTES, 'UTF-8') ?></p>
            <div class="mb-4">
                <a href="<?= htmlspecialchars($basePath . '/login', ENT_QUOTES, 'UTF-8') ?>" class="btn btn-primary btn-sm px-3 fw-bold text-decoration-none me-2"><?= htmlspecialchars(__('install.login_link'), ENT_QUOTES, 'UTF-8') ?></a>
                <a href="<?= htmlspecialchars($basePath . '/', ENT_QUOTES, 'UTF-8') ?>" class="btn btn-outline-secondary btn-sm px-3 text-decoration-none"><?= htmlspecialchars(__('install.home_link'), ENT_QUOTES, 'UTF-8') ?></a>
            </div>
            <?php if (is_array($removeResult)): ?>
                <div class="alert <?= !empty($removeResult['ok']) ? 'alert-success' : 'alert-warning' ?> small text-start" role="status">
                    <?= htmlspecialchars((string) ($removeResult['message'] ?? ''), ENT_QUOTES, 'UTF-8') ?>
                </div>
            <?php endif; ?>
            <?php
            $installDirStillThere = is_dir(dirname(__DIR__) . '/install');
            ?>
            <?php if ($installDirStillThere): ?>
                <form method="post" class="mb-3">
                    <input type="hidden" name="action" value="remove_installer">
                    <button type="submit" class="btn btn-outline-danger btn-sm">
                        <?= htmlspecialchars((__('install.remove_folder_btn') !== 'install.remove_folder_btn') ? __('install.remove_folder_btn') : 'Remove the install folder', ENT_QUOTES, 'UTF-8') ?>
                    </button>
                </form>
                <p class="small text-muted mb-0 text-start"><?= htmlspecialchars((__('install.delete_folder_hint') !== 'install.delete_folder_hint') ? __('install.delete_folder_hint') : 'For safety, delete or rename the install folder when you are done. If the button cannot remove it, use your host file manager or FTP: open the pRD project folder and delete the install directory.', ENT_QUOTES, 'UTF-8') ?></p>
            <?php else: ?>
                <p class="small text-success mb-0"><?= htmlspecialchars((__('install.remove_ok') !== 'install.remove_ok') ? __('install.remove_ok') : 'The install folder has been removed.', ENT_QUOTES, 'UTF-8') ?></p>
            <?php endif; ?>
        </div>
    </div>
</body>
</html>
    <?php
    exit;
}

function install_get_language_flag(string $code): string
{
    $map = [
        'en' => '🇬🇧', 'fr' => '🇫🇷', 'es' => '🇪🇸', 'de' => '🇩🇪', 'it' => '🇮🇹',
        'nl' => '🇳🇱', 'pt' => '🇵🇹', 'pl' => '🇵🇱', 'cy' => '🏴󠁧󠁢󠁷󠁬󠁳󠁿', 'gd' => '🏴󠁧󠁢󠁳󠁣󠁴󠁿', 'ga' => '🇮🇪',
    ];
    return $map[strtolower(substr($code, 0, 2))] ?? '🌐';
}

// Write probe
$probeOk = false;
$probeError = '';
$probeFile = $root . '/.install_write_probe';
if (@file_put_contents($probeFile, 'ok') !== false) {
    $probeOk = true;
    @unlink($probeFile);
} else {
    $last = error_get_last();
    $probeError = isset($last['message']) && is_string($last['message']) ? $last['message'] : 'write failed';
}

// Lock / resume
if (is_file($lockFile) && $serverMethod === 'POST' && isset($_POST['action']) && $_POST['action'] === 'remove_installer') {
    $removeResult = install_try_remove_install_dir($root);
    install_show_complete_page($removeResult);
}
if (is_file($lockFile)) {
    install_show_complete_page();
}

if (is_file($configLocal) && $serverMethod !== 'POST' && (!isset($_POST['action']) || $_POST['action'] !== 'back')) {
    try {
        $pdo = install_load_pdo_from_config($configLocal);
        $userCount = (int) $pdo->query('SELECT COUNT(*) FROM users')->fetchColumn();
        if ($userCount > 0) {
            install_bootstrap_permissions($pdo);
            install_seed_defaults($pdo, $root);
            $_SESSION['install_db_ok'] = true;
            $step = 4;
            $message = __('install.msg_db_ready');
        } else {
            $_SESSION['install_db_ok'] = true;
            $step = 5;
            $message = __('install.msg_db_ready');
        }
    } catch (Throwable $e) {
        $error = __('install.err_config_load') . ' ' . $e->getMessage();
    }
}

// POST handling (skip advance when only applying language on step 1)
if (
    $serverMethod === 'POST'
    && (!$langOnly)
    && (!isset($_POST['action']) || $_POST['action'] !== 'back')
) {
    try {
        if ($step === 1) {
            $step = 2;
        } elseif ($step === 2) {
            $step = 3;
        } elseif ($step === 3) {
            if (!$probeOk) {
                throw new RuntimeException(__('install.err_write_permission') . ($probeError !== '' ? ' ' . __('install.detail_prefix') . ' ' . $probeError : ''));
            }

            if (!empty($_SESSION['install_db_ok']) && is_file($configLocal)) {
                $step = 5;
                $message = __('install.msg_db_ready');
            } else {
                $host = trim(isset($_POST['db_host']) && is_string($_POST['db_host']) ? $_POST['db_host'] : '127.0.0.1');
                $name = trim(isset($_POST['db_name']) && is_string($_POST['db_name']) ? $_POST['db_name'] : '');
                $user = trim(isset($_POST['db_user']) && is_string($_POST['db_user']) ? $_POST['db_user'] : '');
                $pass = isset($_POST['db_pass']) && is_string($_POST['db_pass']) ? $_POST['db_pass'] : '';

                $_SESSION['install_db'] = ['host' => $host, 'name' => $name, 'user' => $user, 'pass' => $pass];

                if ($name === '' || $user === '') {
                    throw new RuntimeException(__('install.err_db_required'));
                }

                $dsn = "mysql:host={$host};dbname={$name};charset=utf8mb4";
                $pdo = new PDO($dsn, $user, $pass, [PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION]);
                $pdo->exec("SET time_zone = '+00:00';");

                if (!install_db_is_empty($pdo)) {
                    throw new RuntimeException(__('install.err_db_not_empty'));
                }

                install_write_config_local($configLocal, $host, $name, $user, $pass);

                install_import_sql($pdo, $root . '/db/schema_baseline.sql');
                install_import_sql($pdo, $root . '/db/seed_baseline.sql');
                install_bootstrap_permissions($pdo);
                install_seed_defaults($pdo, $root);
                install_stamp_schema_version($pdo, $root);

                $installLang = isset($_SESSION['lang']) && is_string($_SESSION['lang']) && $_SESSION['lang'] !== ''
                    ? preg_replace('/[^a-zA-Z_]/', '', $_SESSION['lang'])
                    : (function_exists('get_active_language') ? get_active_language() : 'en');
                if (!is_string($installLang) || $installLang === '') {
                    $installLang = 'en';
                }
                $langStmt = $pdo->prepare(
                    "INSERT INTO site_settings (setting_key, setting_value) VALUES ('default_language', ?)
                     ON DUPLICATE KEY UPDATE setting_value = VALUES(setting_value)"
                );
                $langStmt->execute([$installLang]);

                $_SESSION['install_db_ok'] = true;
                $step = 5;
                $message = __('install.msg_schema_imported');
            }
        } elseif ($step === 5) {
            if (empty($_SESSION['install_db_ok']) || !is_file($configLocal)) {
                throw new RuntimeException(__('install.err_complete_db_first'));
            }
            $pdo = install_load_pdo_from_config($configLocal);

            $username = trim(isset($_POST['admin_username']) && is_string($_POST['admin_username']) ? $_POST['admin_username'] : '');
            $email = trim(isset($_POST['admin_email']) && is_string($_POST['admin_email']) ? $_POST['admin_email'] : '');
            $password = isset($_POST['admin_password']) && is_string($_POST['admin_password']) ? $_POST['admin_password'] : '';
            $confirm = isset($_POST['admin_password_confirm']) && is_string($_POST['admin_password_confirm']) ? $_POST['admin_password_confirm'] : '';

            if ($username === '' || $email === '' || $password === '') {
                throw new RuntimeException(__('install.err_admin_required'));
            }
            // No spaces; letters, numbers, underscore, hyphen, dot — matches typical app expectations
            if (!preg_match('/^[A-Za-z0-9][A-Za-z0-9._-]{2,49}$/', $username)) {
                throw new RuntimeException(__('install.err_invalid_username'));
            }
            if (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
                throw new RuntimeException(__('install.err_invalid_email'));
            }
            if (strlen($password) < 8) {
                throw new RuntimeException(__('install.err_password_length'));
            }
            if ($password !== $confirm) {
                throw new RuntimeException(__('install.err_passwords_match'));
            }

            $hash = password_hash($password, PASSWORD_DEFAULT);
            try {
                $stmt = $pdo->prepare(
                    'INSERT INTO users (username, email, password_hash, role_id, email_verified, is_active, is_new_user)
                     VALUES (?, ?, ?, 1, 1, 1, 1)'
                );
                $stmt->execute([$username, $email, $hash]);
            } catch (PDOException $e) {
                $stmt = $pdo->prepare(
                    'INSERT INTO users (username, email, password_hash, role_id) VALUES (?, ?, ?, 1)'
                );
                $stmt->execute([$username, $email, $hash]);
            }

            $check = $pdo->prepare('SELECT COUNT(*) FROM users WHERE username = ?');
            $check->execute([$username]);
            if ((int) $check->fetchColumn() < 1) {
                throw new RuntimeException(__('install.err_admin_save_failed'));
            }

            install_bootstrap_permissions($pdo);
            install_seed_defaults($pdo, $root);

            $idStmt = $pdo->prepare('SELECT id FROM users WHERE username = ? LIMIT 1');
            $idStmt->execute([$username]);
            $_SESSION['install_admin_id'] = (int) $idStmt->fetchColumn();
            $step = 7;
            $message = function_exists('__') ? __('install.msg_admin_created') : 'Administrator account created. You can choose which features to turn on.';
        } elseif ($step === 7) {
            if (empty($_SESSION['install_db_ok']) || !is_file($configLocal)) {
                throw new RuntimeException(__('install.err_complete_db_first'));
            }
            $pdo = install_load_pdo_from_config($configLocal);
            install_save_modules($pdo);
            $step = 4;
            $message = function_exists('__') ? __('install.msg_modules_saved') : 'Your feature choices have been saved. You can add optional demo packs, or skip.';
        } elseif ($step === 4) {
            if (empty($_SESSION['install_db_ok']) || !is_file($configLocal)) {
                throw new RuntimeException(__('install.err_complete_db_first'));
            }
            $pdo = install_load_pdo_from_config($configLocal);
            $skipDemo = isset($_POST['demo_skip']) && (string) $_POST['demo_skip'] === '1';
            if (!$skipDemo) {
                $slugs = isset($_POST['packs']) && is_array($_POST['packs']) ? $_POST['packs'] : [];
                $clean = [];
                foreach ($slugs as $s) {
                    if (is_string($s) && $s !== '') {
                        $clean[] = $s;
                    }
                }
                if ($clean !== []) {
                    $withData = isset($_POST['with_data']) && (string) $_POST['with_data'] === '1';
                    $adminId = isset($_SESSION['install_admin_id']) ? (int) $_SESSION['install_admin_id'] : 0;
                    if ($adminId < 1) {
                        $aid = $pdo->query('SELECT id FROM users ORDER BY id ASC LIMIT 1');
                        $adminId = $aid !== false ? (int) $aid->fetchColumn() : 0;
                    }
                    $svcPath = $root . '/app/Services/DemoPackService.php';
                    $tblPath = $root . '/app/Services/TableService.php';
                    if (!is_file($svcPath) || !is_file($tblPath)) {
                        throw new RuntimeException('Demo pack service is not installed.');
                    }
                    require_once $tblPath;
                    require_once $svcPath;
                    $service = new \App\Services\DemoPackService($pdo);
                    $service->installPacks($clean, $withData, ['id' => $adminId]);
                }
            }

            if (!is_dir($root . '/db')) {
                @mkdir($root . '/db', 0755, true);
            }
            file_put_contents($lockFile, 'Installed ' . gmdate('c') . "\n");
            unset($_SESSION['install_db_ok'], $_SESSION['install_db'], $_SESSION['install_admin_id']);
            $step = 6;
            $message = __('install.msg_installation_complete');
        }
    } catch (Throwable $e) {
        $error = $e->getMessage();
        if ($step === 3 && is_file($configLocal) && empty($_SESSION['install_db_ok'])) {
            @unlink($configLocal);
        }
        if ($step === 5) {
            $_SESSION['install_db_ok'] = true;
        }
    }
}

$phpOk = version_compare(PHP_VERSION, '8.0.0', '>=');
$pdoOk = extension_loaded('pdo_mysql');
$logsDir = $root . '/logs';
$logsOk = is_dir($logsDir) ? is_writable($logsDir) : is_writable($root);
$reqsOk = $phpOk && $pdoOk && $logsOk && $probeOk;
$showDbForm = ($step === 3);

$savedDb = isset($_SESSION['install_db']) && is_array($_SESSION['install_db']) ? $_SESSION['install_db'] : [];
$dbHostVal = isset($savedDb['host']) && is_string($savedDb['host']) ? $savedDb['host'] : '127.0.0.1';
$dbNameVal = isset($savedDb['name']) && is_string($savedDb['name']) ? $savedDb['name'] : '';
$dbUserVal = isset($savedDb['user']) && is_string($savedDb['user']) ? $savedDb['user'] : '';
$dbPassVal = isset($savedDb['pass']) && is_string($savedDb['pass']) ? $savedDb['pass'] : '';
$dbFieldsLocked = !empty($_SESSION['install_db_ok']);

$availableLanguages = [];
$langDir = $root . '/lang';
if (is_dir($langDir)) {
    $globLang = glob($langDir . '/*.php');
    if ($globLang !== false) {
        foreach ($globLang as $langFile) {
            $code = basename($langFile, '.php');
            $availableLanguages[$code] = ucwords(str_replace(['_', '-'], ' ', $code));
        }
    }
}
if ($availableLanguages === []) {
    $availableLanguages = ['en' => 'English'];
}
asort($availableLanguages);
$currentActiveLang = function_exists('get_active_language') ? get_active_language() : (isset($_SESSION['lang']) && is_string($_SESSION['lang']) ? $_SESSION['lang'] : 'en');
$basePath = install_detect_base_path($root);

/**
 * Translate and escape for HTML text nodes.
 * Decodes entities first so lang files that store &amp; do not show as literal "&amp;".
 */
$t = static function (string $key) : string {
    $raw = __($key);
    $decoded = html_entity_decode($raw, ENT_QUOTES | ENT_HTML5, 'UTF-8');
    return htmlspecialchars($decoded, ENT_QUOTES, 'UTF-8');
};
$closeLabel = (__('install.close_alert') !== 'install.close_alert') ? __('install.close_alert') : 'Close';
?>
<!DOCTYPE html>
<html lang="<?= htmlspecialchars($currentActiveLang, ENT_QUOTES, 'UTF-8') ?>">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title><?= htmlspecialchars(__('install.page_title'), ENT_QUOTES, 'UTF-8') ?></title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
</head>

<body class="bg-light d-flex align-items-center justify-content-center min-vh-100 py-5">
    <div class="container" style="max-width: 600px;">
        <div class="card border-0 shadow-sm p-4 bg-white" role="region" aria-labelledby="installStepHeading">
            <h1 class="visually-hidden"><?= htmlspecialchars(__('install.page_title'), ENT_QUOTES, 'UTF-8') ?></h1>
            <?php if ($error !== ''): ?>
                <div class="alert alert-danger alert-dismissible fade show shadow-sm small" role="alert">
                    <strong><?= htmlspecialchars($error, ENT_QUOTES, 'UTF-8') ?></strong>
                    <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="<?= htmlspecialchars($closeLabel, ENT_QUOTES, 'UTF-8') ?>"></button>
                </div>
            <?php endif; ?>
            <?php if ($message !== '' && $step !== 6): ?>
                <div class="alert alert-success alert-dismissible fade show shadow-sm small" role="status">
                    <strong><?= htmlspecialchars($message, ENT_QUOTES, 'UTF-8') ?></strong>
                    <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="<?= htmlspecialchars($closeLabel, ENT_QUOTES, 'UTF-8') ?>"></button>
                </div>
            <?php endif; ?>

            <?php if ($step === 6): ?>
                <h2 class="h5 fw-bold text-success mb-2" id="installStepHeading"><?= htmlspecialchars(__('install.done_heading'), ENT_QUOTES, 'UTF-8') ?></h2>
                <p class="text-secondary small mb-3"><?= htmlspecialchars(__('install.done_message'), ENT_QUOTES, 'UTF-8') ?></p>
                <p class="small mb-0">
                    <a href="<?= htmlspecialchars($basePath . '/login', ENT_QUOTES, 'UTF-8') ?>" class="text-decoration-none fw-bold"><?= htmlspecialchars(__('install.login_link'), ENT_QUOTES, 'UTF-8') ?></a>
                    ·
                    <a href="<?= htmlspecialchars($basePath !== '' ? $basePath . '/' : '../', ENT_QUOTES, 'UTF-8') ?>" class="text-decoration-none text-muted"><?= htmlspecialchars(__('install.home_link'), ENT_QUOTES, 'UTF-8') ?></a>
                </p>
                <p class="small text-muted mt-3 mb-0"><em><?= __('install.delete_folder_hint') ?></em></p>

            <?php elseif ($step === 4): ?>
                <h2 class="h5 fw-bold text-dark mb-1" id="installStepHeading"><?= htmlspecialchars(__('install.demo_heading') !== 'install.demo_heading' ? __('install.demo_heading') : 'Optional demo packs', ENT_QUOTES, 'UTF-8') ?></h2>
                <p class="text-muted small mb-3" id="installDemoHelp"><?= htmlspecialchars(__('install.demo_help') !== 'install.demo_help' ? __('install.demo_help') : 'You can add starter tables for parish registers and/or a book library. Choose tables and columns only, or include a few made-up sample rows. You can skip this and add or remove packs later in Settings. Removing a pack later also deletes any extra rows you added in those demo tables.', ENT_QUOTES, 'UTF-8') ?></p>
                <form method="post" aria-labelledby="installStepHeading" aria-describedby="installDemoHelp">
                    <input type="hidden" name="step" value="4">
                    <fieldset class="mb-3">
                        <legend class="form-label fw-bold small"><?= htmlspecialchars(__('install.demo_choose') !== 'install.demo_choose' ? __('install.demo_choose') : 'Which packs', ENT_QUOTES, 'UTF-8') ?></legend>
                        <div class="form-check mb-2">
                            <input class="form-check-input" type="checkbox" name="packs[]" value="parish" id="install_pack_parish" aria-describedby="install_pack_parish_desc">
                            <label class="form-check-label" for="install_pack_parish">
                                <strong><?= htmlspecialchars(__('install.demo_parish') !== 'install.demo_parish' ? __('install.demo_parish') : 'Parish registers', ENT_QUOTES, 'UTF-8') ?></strong>
                                <span class="d-block small text-muted" id="install_pack_parish_desc"><?= htmlspecialchars(__('install.demo_parish_desc') !== 'install.demo_parish_desc' ? __('install.demo_parish_desc') : 'Baptisms, marriages, and burials — the workflow pRD was first built for.', ENT_QUOTES, 'UTF-8') ?></span>
                            </label>
                        </div>
                        <div class="form-check mb-2">
                            <input class="form-check-input" type="checkbox" name="packs[]" value="library" id="install_pack_library" aria-describedby="install_pack_library_desc">
                            <label class="form-check-label" for="install_pack_library">
                                <strong><?= htmlspecialchars(__('install.demo_library') !== 'install.demo_library' ? __('install.demo_library') : 'Book library', ENT_QUOTES, 'UTF-8') ?></strong>
                                <span class="d-block small text-muted" id="install_pack_library_desc"><?= htmlspecialchars(__('install.demo_library_desc') !== 'install.demo_library_desc' ? __('install.demo_library_desc') : 'A simple catalogue of titles, authors, and shelf location.', ENT_QUOTES, 'UTF-8') ?></span>
                            </label>
                        </div>
                    </fieldset>
                    <fieldset class="mb-3">
                        <legend class="form-label fw-bold small"><?= htmlspecialchars(__('install.demo_what') !== 'install.demo_what' ? __('install.demo_what') : 'What to add', ENT_QUOTES, 'UTF-8') ?></legend>
                        <div class="form-check">
                            <input class="form-check-input" type="radio" name="with_data" id="installDemoSchemaOnly" value="0" checked>
                            <label class="form-check-label" for="installDemoSchemaOnly"><?= htmlspecialchars(__('install.demo_schema_only') !== 'install.demo_schema_only' ? __('install.demo_schema_only') : 'Tables and columns only (no sample rows)', ENT_QUOTES, 'UTF-8') ?></label>
                        </div>
                        <div class="form-check">
                            <input class="form-check-input" type="radio" name="with_data" id="installDemoSchemaData" value="1">
                            <label class="form-check-label" for="installDemoSchemaData"><?= htmlspecialchars(__('install.demo_schema_data') !== 'install.demo_schema_data' ? __('install.demo_schema_data') : 'Tables, columns, and sample data', ENT_QUOTES, 'UTF-8') ?></label>
                        </div>
                    </fieldset>
                    <div class="d-flex justify-content-between align-items-center flex-wrap gap-2">
                        <button type="submit" name="demo_skip" value="1" class="btn btn-outline-secondary btn-sm px-3"><?= htmlspecialchars(__('install.demo_skip') !== 'install.demo_skip' ? __('install.demo_skip') : 'Skip for now', ENT_QUOTES, 'UTF-8') ?></button>
                        <button type="submit" class="btn btn-primary btn-sm px-4 fw-bold"><?= htmlspecialchars(__('install.demo_continue') !== 'install.demo_continue' ? __('install.demo_continue') : 'Add selected packs and finish', ENT_QUOTES, 'UTF-8') ?></button>
                    </div>
                </form>


            <?php elseif ($step === 7): ?>
                <h2 class="h5 fw-bold text-dark mb-1" id="installStepHeading"><?= htmlspecialchars(__('install.modules_heading') !== 'install.modules_heading' ? __('install.modules_heading') : 'Which parts of pRD would you like to use?', ENT_QUOTES, 'UTF-8') ?></h2>
                <p class="text-muted small mb-3" id="installModulesHelp"><?= htmlspecialchars(__('install.modules_help') !== 'install.modules_help' ? __('install.modules_help') : 'Tick the features you want on day one. You can change this later under Admin → Settings.', ENT_QUOTES, 'UTF-8') ?></p>
                <form method="post" aria-labelledby="installStepHeading" aria-describedby="installModulesHelp">
                    <input type="hidden" name="step" value="7">
                    <fieldset class="border-0 p-0 mb-3">
                        <legend class="visually-hidden"><?= htmlspecialchars(__('install.modules_heading') !== 'install.modules_heading' ? __('install.modules_heading') : 'Which parts of pRD would you like to use?', ENT_QUOTES, 'UTF-8') ?></legend>
                        <?php
                        $modList = [
                            ['users', 'install.mod_users', 'User accounts', 'install.mod_users_desc', 'Let people register and sign in.'],
                            ['moderation', 'install.mod_moderation', 'Moderation', 'install.mod_moderation_desc', 'Review suggested edits before they change a record.'],
                            ['feedback', 'install.mod_feedback', 'Feedback and tickets', 'install.mod_feedback_desc', 'A public form for questions and support tickets.'],
                            ['volunteers', 'install.mod_volunteers', 'Volunteer interest', 'install.mod_volunteers_desc', 'A public form for people who want to help.'],
                            ['leaderboard', 'install.mod_leaderboard', 'Leaderboard', 'install.mod_leaderboard_desc', 'Optional points table. Needs user accounts to be on.'],
                            ['maps', 'install.mod_maps', 'Maps', 'install.mod_maps_desc', 'Optional. When on, you can add location fields and open a map for each table later — nothing is set up at install. Change this anytime under Admin → Settings.'],
                        ];
                        foreach ($modList as $mod):
                            $id = 'install_mod_' . $mod[0];
                            $name = 'module_' . $mod[0] . '_enabled';
                            $label = (__($mod[1]) !== $mod[1]) ? __($mod[1]) : $mod[2];
                            $desc = (__($mod[3]) !== $mod[3]) ? __($mod[3]) : $mod[4];
                        ?>
                        <div class="form-check mb-2">
                            <input class="form-check-input" type="checkbox" name="<?= htmlspecialchars($name, ENT_QUOTES, 'UTF-8') ?>" value="1" id="<?= htmlspecialchars($id, ENT_QUOTES, 'UTF-8') ?>" <?= 'checked' ?> <?= $mod[0] === 'leaderboard' ? 'data-needs-users="1"' : '' ?>>
                            <label class="form-check-label" for="<?= htmlspecialchars($id, ENT_QUOTES, 'UTF-8') ?>">
                                <span class="fw-bold"><?= htmlspecialchars($label, ENT_QUOTES, 'UTF-8') ?></span>
                                <span class="d-block small text-muted"><?= htmlspecialchars($desc, ENT_QUOTES, 'UTF-8') ?></span>
                            </label>
                        </div>
                        <?php endforeach; ?>
                    </fieldset>
                    <p class="small text-muted" id="installModulesLater"><?= htmlspecialchars(__('install.modules_later') !== 'install.modules_later' ? __('install.modules_later') : 'You can turn any of these on or off later in Admin → Settings.', ENT_QUOTES, 'UTF-8') ?></p>
                    <div class="d-flex justify-content-between">
                        <button type="submit" name="action" value="back" class="btn btn-outline-secondary btn-sm"><?= htmlspecialchars(__('install.back_btn'), ENT_QUOTES, 'UTF-8') ?></button>
                        <button type="submit" class="btn btn-primary btn-sm px-4 fw-bold"><?= htmlspecialchars(__('install.modules_continue') !== 'install.modules_continue' ? __('install.modules_continue') : 'Save and continue', ENT_QUOTES, 'UTF-8') ?></button>
                    </div>
                </form>
                <script>
                (function () {
                    var users = document.getElementById('install_mod_users');
                    var board = document.getElementById('install_mod_leaderboard');
                    if (!users || !board) return;
                    function sync() {
                        board.disabled = !users.checked;
                        if (!users.checked) board.checked = false;
                    }
                    users.addEventListener('change', sync);
                    sync();
                })();
                </script>

            <?php elseif ($step === 5): ?>
                <h2 class="h5 fw-bold text-dark mb-1" id="installStepHeading"><?= htmlspecialchars(__('install.admin_heading'), ENT_QUOTES, 'UTF-8') ?></h2>
                <p class="text-muted small mb-3" id="installAdminHelp"><?= __('install.admin_subheading') ?></p>
                <form method="post" aria-labelledby="installStepHeading" aria-describedby="installAdminHelp">
                    <input type="hidden" name="step" value="5">
                    <div class="mb-3">
                        <label for="admin_username" class="form-label small fw-bold"><?= htmlspecialchars(__('install.admin_username_label'), ENT_QUOTES, 'UTF-8') ?></label>
                        <input id="admin_username" name="admin_username" required autocomplete="username" class="form-control form-control-sm" pattern="[A-Za-z0-9][A-Za-z0-9._\-]{2,49}" title="3–50 characters: letters, numbers, dot, underscore, hyphen; no spaces">
                    </div>
                    <div class="mb-3">
                        <label for="admin_email" class="form-label small fw-bold"><?= htmlspecialchars(__('install.admin_email_label'), ENT_QUOTES, 'UTF-8') ?></label>
                        <input id="admin_email" name="admin_email" type="email" required autocomplete="email" class="form-control form-control-sm">
                    </div>
                    <div class="mb-3">
                        <label for="admin_password" class="form-label small fw-bold"><?= htmlspecialchars(__('install.admin_password_label'), ENT_QUOTES, 'UTF-8') ?></label>
                        <div class="input-group input-group-sm">
                            <input id="admin_password" name="admin_password" type="password" required autocomplete="new-password" class="form-control">
                            <button class="btn btn-outline-secondary" type="button" onclick="togglePw('admin_password', this)" aria-pressed="false" aria-controls="admin_password" aria-label="<?= htmlspecialchars(__('install.show_password'), ENT_QUOTES, 'UTF-8') ?>"><?= htmlspecialchars(__('install.show_password'), ENT_QUOTES, 'UTF-8') ?></button>
                        </div>
                    </div>
                    <div class="mb-4">
                        <label for="admin_password_confirm" class="form-label small fw-bold"><?= htmlspecialchars(__('install.admin_confirm_password_label'), ENT_QUOTES, 'UTF-8') ?></label>
                        <div class="input-group input-group-sm">
                            <input id="admin_password_confirm" name="admin_password_confirm" type="password" required autocomplete="new-password" class="form-control">
                            <button class="btn btn-outline-secondary" type="button" onclick="togglePw('admin_password_confirm', this)" aria-pressed="false" aria-controls="admin_password_confirm" aria-label="<?= htmlspecialchars(__('install.show_password'), ENT_QUOTES, 'UTF-8') ?>"><?= htmlspecialchars(__('install.show_password'), ENT_QUOTES, 'UTF-8') ?></button>
                        </div>
                    </div>
                    <div class="d-flex justify-content-between align-items-center">
                        <button type="submit" name="action" value="back" formnovalidate class="btn btn-outline-secondary btn-sm px-3">&larr; <?= htmlspecialchars(__('install.back_btn'), ENT_QUOTES, 'UTF-8') ?></button>
                        <button type="submit" class="btn btn-primary btn-sm px-4 fw-bold"><?= htmlspecialchars(__('install.finish_btn'), ENT_QUOTES, 'UTF-8') ?></button>
                    </div>
                </form>

            <?php elseif ($step === 3 || $showDbForm): ?>
                <h2 class="h5 fw-bold text-dark mb-1" id="installStepHeading"><?= htmlspecialchars(__('install.db_heading'), ENT_QUOTES, 'UTF-8') ?></h2>
                <p class="text-muted small mb-3" id="installDbHelp"><?= __('install.db_hint') ?></p>
                <form method="post" aria-labelledby="installStepHeading" aria-describedby="installDbHelp">
                    <input type="hidden" name="step" value="3">
                    <div class="mb-3">
                        <label for="db_host" class="form-label small fw-bold"><?= htmlspecialchars(__('install.db_host_label'), ENT_QUOTES, 'UTF-8') ?></label>
                        <input id="db_host" name="db_host" value="<?= htmlspecialchars($dbHostVal, ENT_QUOTES, 'UTF-8') ?>" required class="form-control form-control-sm" <?= $dbFieldsLocked ? 'readonly' : '' ?>>
                    </div>
                    <div class="mb-3">
                        <label for="db_name" class="form-label small fw-bold"><?= htmlspecialchars(__('install.db_name_label'), ENT_QUOTES, 'UTF-8') ?></label>
                        <input id="db_name" name="db_name" value="<?= htmlspecialchars($dbNameVal, ENT_QUOTES, 'UTF-8') ?>" required class="form-control form-control-sm" <?= $dbFieldsLocked ? 'readonly' : '' ?>>
                    </div>
                    <div class="mb-3">
                        <label for="db_user" class="form-label small fw-bold"><?= htmlspecialchars(__('install.db_user_label'), ENT_QUOTES, 'UTF-8') ?></label>
                        <input id="db_user" name="db_user" value="<?= htmlspecialchars($dbUserVal, ENT_QUOTES, 'UTF-8') ?>" required autocomplete="off" class="form-control form-control-sm" <?= $dbFieldsLocked ? 'readonly' : '' ?>>
                    </div>
                    <div class="mb-4">
                        <label for="db_pass" class="form-label small fw-bold"><?= htmlspecialchars(__('install.db_pass_label'), ENT_QUOTES, 'UTF-8') ?></label>
                        <div class="input-group input-group-sm">
                            <input id="db_pass" name="db_pass" type="password" value="<?= htmlspecialchars($dbPassVal, ENT_QUOTES, 'UTF-8') ?>" autocomplete="new-password" class="form-control" <?= $dbFieldsLocked ? 'readonly' : '' ?>>
                            <button class="btn btn-outline-secondary" type="button" onclick="togglePw('db_pass', this)" aria-pressed="false" aria-controls="db_pass" aria-label="<?= htmlspecialchars(__('install.show_password'), ENT_QUOTES, 'UTF-8') ?>"><?= htmlspecialchars(__('install.show_password'), ENT_QUOTES, 'UTF-8') ?></button>
                        </div>
                    </div>
                    <div class="d-flex justify-content-between align-items-center">
                        <button type="submit" name="action" value="back" formnovalidate class="btn btn-outline-secondary btn-sm px-3">&larr; <?= htmlspecialchars(__('install.back_btn'), ENT_QUOTES, 'UTF-8') ?></button>
                        <button type="submit" class="btn btn-primary btn-sm px-4 fw-bold"><?= $t('install.db_submit_btn') ?></button>
                    </div>
                </form>

            <?php elseif ($step === 2): ?>
                <h2 class="h5 fw-bold text-dark mb-3" id="installStepHeading"><?= htmlspecialchars(__('install.req_heading'), ENT_QUOTES, 'UTF-8') ?></h2>
                <ul class="list-group list-group-flush mb-4 small">
                    <li class="list-group-item d-flex justify-content-between align-items-center <?= $phpOk ? 'text-success' : 'text-danger' ?>">
                        <span><?= htmlspecialchars(sprintf(__('install.req_php'), PHP_VERSION), ENT_QUOTES, 'UTF-8') ?></span>
                        <span><?= $phpOk ? '✓' : '✗' ?><span class="visually-hidden"> <?= $phpOk ? htmlspecialchars(__('install.req_pass') !== 'install.req_pass' ? __('install.req_pass') : 'Passed', ENT_QUOTES, 'UTF-8') : htmlspecialchars(__('install.req_fail') !== 'install.req_fail' ? __('install.req_fail') : 'Failed', ENT_QUOTES, 'UTF-8') ?></span></span>
                    </li>
                    <li class="list-group-item d-flex justify-content-between align-items-center <?= $pdoOk ? 'text-success' : 'text-danger' ?>">
                        <span><?= htmlspecialchars(__('install.req_pdo'), ENT_QUOTES, 'UTF-8') ?></span>
                        <span><?= $pdoOk ? '✓' : '✗' ?></span>
                    </li>
                    <li class="list-group-item d-flex justify-content-between align-items-center <?= $logsOk ? 'text-success' : 'text-danger' ?>">
                        <span><?= htmlspecialchars(__('install.req_logs'), ENT_QUOTES, 'UTF-8') ?></span>
                        <span><?= $logsOk ? '✓' : '✗' ?></span>
                    </li>
                    <li class="list-group-item d-flex justify-content-between align-items-center <?= $probeOk ? 'text-success' : 'text-danger' ?>">
                        <div>
                            <span><?= htmlspecialchars(__('install.req_probe'), ENT_QUOTES, 'UTF-8') ?></span>
                            <?php if (!$probeOk && $probeError !== ''): ?>
                                <div class="text-muted small"><?= htmlspecialchars($probeError, ENT_QUOTES, 'UTF-8') ?></div>
                            <?php endif; ?>
                        </div>
                        <span><?= $probeOk ? '✓' : '✗' ?></span>
                    </li>
                </ul>
                <form method="post" aria-labelledby="installStepHeading">
                    <input type="hidden" name="step" value="2">
                    <div class="d-flex justify-content-between align-items-center">
                        <button type="submit" name="action" value="back" formnovalidate class="btn btn-outline-secondary btn-sm px-3">&larr; <?= htmlspecialchars(__('install.back_btn'), ENT_QUOTES, 'UTF-8') ?></button>
                        <button type="submit" class="btn btn-primary btn-sm px-4 fw-bold" <?= $reqsOk ? '' : 'disabled' ?>><?= htmlspecialchars(__('install.continue_btn'), ENT_QUOTES, 'UTF-8') ?></button>
                    </div>
                </form>
                <?php if (!$reqsOk): ?>
                    <div class="alert alert-warning small mt-3 mb-0"><?= htmlspecialchars(__('install.req_fail_msg'), ENT_QUOTES, 'UTF-8') ?></div>
                <?php endif; ?>

            <?php else: ?>
                <h2 class="h5 fw-bold text-dark mb-1" id="installStepHeading"><?= htmlspecialchars(__('install.heading'), ENT_QUOTES, 'UTF-8') ?></h2>
                <p class="text-muted small mb-3"><?= __('install.subheading') ?></p>
                <form method="post" id="install-lang-form" aria-labelledby="installStepHeading">
                    <input type="hidden" name="step" value="1">
                    <input type="hidden" name="apply_lang_only" id="apply_lang_only" value="0">
                    <div class="mb-4">
                        <label for="selected_lang" class="form-label small fw-bold"><?= htmlspecialchars(__('install.lang_label'), ENT_QUOTES, 'UTF-8') ?></label>
                        <select id="selected_lang" name="selected_lang" class="form-select form-select-sm" onchange="document.getElementById('apply_lang_only').value='1'; this.form.submit();">
                            <?php foreach ($availableLanguages as $code => $label): ?>
                                <option value="<?= htmlspecialchars($code, ENT_QUOTES, 'UTF-8') ?>" <?= ($code === $currentActiveLang) ? 'selected' : '' ?>>
                                    <?= install_get_language_flag($code) ?> <?= htmlspecialchars($label, ENT_QUOTES, 'UTF-8') ?> (<?= htmlspecialchars($code, ENT_QUOTES, 'UTF-8') ?>)
                                </option>
                            <?php endforeach; ?>
                        </select>
                    </div>
                    <button type="submit" class="btn btn-primary btn-sm px-4 fw-bold" onclick="document.getElementById('apply_lang_only').value='0';"><?= htmlspecialchars(__('install.continue_btn'), ENT_QUOTES, 'UTF-8') ?></button>
                </form>
            <?php endif; ?>
        </div>
    </div>
    <script>
    function togglePw(id, btn) {
        var el = document.getElementById(id);
        if (!el) return;
        var showLabel = <?= json_encode(__('install.show_password'), JSON_UNESCAPED_UNICODE) ?>;
        var hideLabel = <?= json_encode(__('install.hide_password'), JSON_UNESCAPED_UNICODE) ?>;
        if (el.type === 'password') {
            el.type = 'text';
            btn.textContent = hideLabel;
            btn.setAttribute('aria-label', hideLabel);
            btn.setAttribute('aria-pressed', 'true');
        } else {
            el.type = 'password';
            btn.textContent = showLabel;
            btn.setAttribute('aria-label', showLabel);
            btn.setAttribute('aria-pressed', 'false');
        }
    }
    </script>
</body>
</html>
