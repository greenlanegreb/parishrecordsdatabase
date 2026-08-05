<?php
declare(strict_types=1);
/**
 * MIGRATED FILE MAPPING
 * ---------------------
 * Original Old File: partials/nav.php
 * Migrated Date: 2026-08-05 06:25:00
 */

if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

$authHelperPath = __DIR__ . '/../db/auth_helpers.php';
if (file_exists($authHelperPath)) {
    require_once $authHelperPath;
}
$functionsPath = __DIR__ . '/../includes/functions.php';
if (file_exists($functionsPath)) {
    require_once $functionsPath;
}

// High-contrast toggle
$queryGet = $_GET;
if (isset($queryGet['contrast']) && $queryGet['contrast'] === 'toggle') {
    $_SESSION['high_contrast'] = !($_SESSION['high_contrast'] ?? false);
    $redirectUrl = strtok($_SERVER['REQUEST_URI'] ?? '/index.php', '?') ?: '/index.php';
    header('Location: ' . $redirectUrl);
    exit;
}

// Language switch (?lang=en / ?lang=cy …)
if (isset($queryGet['lang']) && is_string($queryGet['lang']) && function_exists('set_language')) {
    $requested = preg_replace('/[^a-zA-Z_]/', '', $queryGet['lang']);
    $langFile = __DIR__ . '/../lang/' . $requested . '.php';
    if ($requested !== '' && is_file($langFile)) {
        set_language($requested);
    }
    $redirectUrl = strtok($_SERVER['REQUEST_URI'] ?? '/index.php', '?') ?: '/index.php';
    header('Location: ' . $redirectUrl);
    exit;
}

$isHighContrast = isset($_SESSION['high_contrast']) && $_SESSION['high_contrast'];
/** @var array{id: int|string, username: string, first_name?: string, points?: int|string}|null $currentUser */
$currentUser = (function_exists('get_current_user_data') && isset($pdo) && $pdo instanceof PDO) ? get_current_user_data($pdo) : null;
$isLoggedIn = ($currentUser !== null || isset($_SESSION['user_id']));

// Comprehensive flag & human-readable label dictionary for all your language files
$languageMeta = [
    'ar'         => ['name' => 'العربية (Arabic)', 'flag' => '🇸🇦'],
    'br'         => ['name' => 'Brezhoneg (Breton)', 'flag' => '🏴󠁥󠁳󠁢󲁧'],
    'cy'         => ['name' => 'Cymraeg (Welsh)', 'flag' => '🏴󠁧󠁢󠁷󠁬󠁳󠁿'],
    'da'         => ['name' => 'Dansk (Danish)', 'flag' => '🇩🇰'],
    'de'         => ['name' => 'Deutsch (German)', 'flag' => '🇩🇪'],
    'en'         => ['name' => 'English', 'flag' => '🇬🇧'],
    'en_AU'      => ['name' => 'English (Australia)', 'flag' => '🇦🇺'],
    'en_US'      => ['name' => 'English (United States)', 'flag' => '🇺🇸'],
    'en_GB_chav' => ['name' => 'English (UK Slang)', 'flag' => '🇬🇧'],
    'es'         => ['name' => 'Español (Spanish)', 'flag' => '🇪🇸'],
    'fa'         => ['name' => 'فارسی (Persian)', 'flag' => '🇮🇷'],
    'fr'         => ['name' => 'Français (French)', 'flag' => '🇫🇷'],
    'ga'         => ['name' => 'Gaeilge (Irish)', 'flag' => '🇮🇪'],
    'gd'         => ['name' => 'Gàidhlig (Scottish Gaelic)', 'flag' => '🏴󠁧󠁢󠁳󠁣󠁴󠁿'],
    'gv'         => ['name' => 'Gaelg (Manx)', 'flag' => '🇮🇲'],
    'it'         => ['name' => 'Italiano (Italian)', 'flag' => '🇮🇹'],
    'ja'         => ['name' => '日本語 (Japanese)', 'flag' => '🇯🇵'],
    'kw'         => ['name' => 'Kernewek (Cornish)', 'flag' => '🏴󠁰󠁲󠁧󠁢󠁣󠁷󠁿'],
    'ln'         => ['name' => 'Lingala', 'flag' => '🇨🇩'],
    'pl'         => ['name' => 'Polski (Polish)', 'flag' => '🇵🇱'],
    'prs'        => ['name' => 'Dari (Afghan Persian)', 'flag' => '🇦🇫'],
    'ps'         => ['name' => 'پښتو (Pashto)', 'flag' => '🇦🇫'],
    'pt'         => ['name' => 'Português (Portuguese)', 'flag' => '🇵🇹'],
    'pt_BR'      => ['name' => 'Português (Brazil)', 'flag' => '🇧🇷'],
    'rn'         => ['name' => 'Kirundi', 'flag' => '🇧🇮'],
    'ru'         => ['name' => 'Русский (Russian)', 'flag' => '🇷🇺'],
    'so'         => ['name' => 'Soomaaliga (Somali)', 'flag' => '🇸🇴'],
    'uk'         => ['name' => 'Українська (Ukrainian)', 'flag' => '🇺🇦'],
    'zh_CN'      => ['name' => '中文 (Chinese Simplified)', 'flag' => '🇨🇳'],
    'zh_TW'      => ['name' => '中文 (Chinese Traditional)', 'flag' => '🇹🇼'],
];

// Automatically scan the lang directory so NO languages are missed
$navLanguages = [];
$langDir = __DIR__ . '/../lang';
if (is_dir($langDir)) {
    $globFiles = glob($langDir . '/*.php');
    if ($globFiles !== false) {
        foreach ($globFiles as $file) {
            $code = basename($file, '.php');
            if (preg_match('/^[a-zA-Z_\-]+$/', $code)) {
                $navLanguages[$code] = $languageMeta[$code]['name'] ?? ucwords(str_replace('_', ' ', $code));
            }
        }
    }
    ksort($navLanguages);
}
$activeLang = function_exists('get_active_language') ? get_active_language() : 'en';

// ------------------------------------------------------------------
// Module toggles
// ------------------------------------------------------------------
$modModeration  = (isset($pdo) && $pdo instanceof PDO) ? is_module_enabled($pdo, 'moderation') : true;
$modVolunteers  = (isset($pdo) && $pdo instanceof PDO) ? is_module_enabled($pdo, 'volunteers') : true;
$modFeedback    = (isset($pdo) && $pdo instanceof PDO) ? is_module_enabled($pdo, 'feedback') : true;
$modUsers       = (isset($pdo) && $pdo instanceof PDO) ? is_module_enabled($pdo, 'users') : true;
$modLeaderboard = (isset($pdo) && $pdo instanceof PDO) ? is_module_enabled($pdo, 'leaderboard') : true;

// Public button visibility
$canPublicSearch     = $isLoggedIn || (isset($pdo) && $pdo instanceof PDO && guest_has_permission($pdo, 'view_as_guest'));
$canPublicVolunteer  = $isLoggedIn || (isset($pdo) && $pdo instanceof PDO && $modVolunteers && guest_has_permission($pdo, 'submit_volunteer'));
$canPublicFeedback   = $isLoggedIn || (isset($pdo) && $pdo instanceof PDO && $modFeedback && guest_has_permission($pdo, 'submit_feedback'));
$canPublicLeaderboard = $isLoggedIn || (isset($pdo) && $pdo instanceof PDO && $modLeaderboard && guest_has_permission($pdo, 'view_leaderboard'));

// ------------------------------------------------------------------
// Logged-in capability checks
// ------------------------------------------------------------------
$canModerate = false;
if (isset($pdo) && $pdo instanceof PDO && $modModeration) {
    if (is_admin($pdo) || has_permission($pdo, 'moderate_table_1')) {
        $canModerate = true;
    } else {
        $tablesChk = $pdo->query("SELECT id FROM dynamic_tables");
        $tableIds = $tablesChk !== false ? $tablesChk->fetchAll(PDO::FETCH_COLUMN) : [];
        foreach ($tableIds as $tId) {
            if (has_permission($pdo, 'moderate_table_' . (int)$tId)) {
                $canModerate = true;
                break;
            }
        }
    }
}
$canManageUsers = (isset($pdo) && $pdo instanceof PDO) && $modUsers && has_permission($pdo, 'manage_users', 'Manage user accounts');
$canManageCols  = (isset($pdo) && $pdo instanceof PDO) && has_permission($pdo, 'manage_columns', 'Configure table columns');
$canManageVols  = (isset($pdo) && $pdo instanceof PDO) && $modVolunteers && has_permission($pdo, 'manage_volunteers', 'Manage volunteers');
$canManageFeed  = (isset($pdo) && $pdo instanceof PDO) && $modFeedback && has_permission($pdo, 'manage_feedback', 'Manage feedback');
$canManageSets  = (isset($pdo) && $pdo instanceof PDO) && has_permission($pdo, 'manage_settings', 'Manage global settings');

$baseUrl = defined('BASE_PATH') && is_string(BASE_PATH) ? rtrim(BASE_PATH, '/') : '';
$serverScript = isset($_SERVER['SCRIPT_NAME']) && is_string($_SERVER['SCRIPT_NAME']) ? $_SERVER['SCRIPT_NAME'] : '';
$currentScript = basename($serverScript);
$systemName = (function_exists('get_system_name') && isset($pdo) && $pdo instanceof PDO) ? get_system_name($pdo) : 'Parish Records Directory (PRD)';
$sessionUsername = isset($_SESSION['username']) && is_string($_SESSION['username']) ? $_SESSION['username'] : 'User';
$displayUsername = ($currentUser !== null && isset($currentUser['username']) && is_string($currentUser['username'])) ? $currentUser['username'] : $sessionUsername;
$displayIdentifier = ($currentUser !== null && isset($currentUser['first_name']) && is_string($currentUser['first_name']) && $currentUser['first_name'] !== '') ? $currentUser['first_name'] : $displayUsername;
$userPoints = ($currentUser !== null && isset($currentUser['points'])) ? (int)$currentUser['points'] : 0;
?>

<!-- Top Accessibility & Language Bar -->
<div class="bg-dark text-white py-1 px-3 small border-bottom">
    <div class="container d-flex justify-content-between align-items-center flex-wrap gap-2">
        <!-- High-Contrast Toggle -->
        <div>
            <a href="?contrast=toggle" class="text-white text-decoration-none d-inline-flex align-items-center gap-1" role="button" aria-label="<?= htmlspecialchars(__('nav.high_contrast'), ENT_QUOTES, 'UTF-8') ?>">
                <span aria-hidden="true">👁️</span> <span><?= htmlspecialchars($isHighContrast ? __('nav.low_contrast') : __('nav.high_contrast'), ENT_QUOTES, 'UTF-8') ?></span>
            </a>
        </div>

        <!-- Accessible Language Picker with Flags -->
        <?php if (count($navLanguages) > 1): ?>
            <div class="d-inline-flex align-items-center gap-2">
                <label for="site-lang-select" class="text-white-50 mb-0">
                    <span aria-hidden="true">🌐</span> <span class="visually-hidden">Language Selector:</span>
                </label>
                <select id="site-lang-select" 
                        onchange="if(this.value) window.location.href='?lang=' + encodeURIComponent(this.value);" 
                        class="form-select form-select-sm bg-dark text-white border-secondary py-0 px-2"
                        style="width: auto; font-size: 0.85rem;"
                        aria-label="Select Application Language">
                    <?php foreach ($navLanguages as $code => $label): ?>
                        <?php 
                            $flag = $languageMeta[$code]['flag'] ?? '🏳️';
                            $displayText = $flag . ' ' . $label;
                        ?>
                        <option value="<?= htmlspecialchars($code, ENT_QUOTES, 'UTF-8') ?>" <?= ($code === $activeLang) ? 'selected' : '' ?> class="bg-dark text-white">
                            <?= htmlspecialchars($displayText, ENT_QUOTES, 'UTF-8') ?>
                        </option>
                    <?php endforeach; ?>
                </select>
            </div>
        <?php endif; ?>
    </div>
</div>

<!-- Main Bootstrap 5 Navbar -->
<nav class="navbar navbar-expand-lg navbar-light bg-white shadow-sm sticky-top" aria-label="Main Navigation">
    <div class="container">
        <!-- Brand Title -->
        <a class="navbar-brand fw-bold text-primary" href="<?= htmlspecialchars($baseUrl, ENT_QUOTES, 'UTF-8') ?>/index.php">
            <?= htmlspecialchars($systemName, ENT_QUOTES, 'UTF-8') ?>
        </a>

        <!-- Mobile Toggle Button -->
        <button class="navbar-toggler border-0" type="button" data-bs-toggle="collapse" data-bs-target="#mainNavbarContent" aria-controls="mainNavbarContent" aria-expanded="false" aria-label="Toggle navigation">
            <span class="navbar-toggler-icon"></span>
        </button>

        <!-- Navbar Links & Account Actions -->
        <div class="collapse navbar-collapse" id="mainNavbarContent">
            <ul class="navbar-nav me-auto mb-2 mb-lg-0 align-items-lg-center gap-lg-1">
                <?php if ($canPublicSearch): ?>
                    <li class="nav-item">
                        <a href="<?= htmlspecialchars($baseUrl, ENT_QUOTES, 'UTF-8') ?>/index.php" class="nav-link <?= ($currentScript === 'index.php') ? 'active fw-bold text-primary' : '' ?>">
                            <?= htmlspecialchars(__('nav.search'), ENT_QUOTES, 'UTF-8') ?>
                        </a>
                    </li>
                <?php endif; ?>
                
                <?php if (!$isLoggedIn && $canPublicVolunteer): ?>
                    <li class="nav-item">
                        <a href="<?= htmlspecialchars($baseUrl, ENT_QUOTES, 'UTF-8') ?>/volunteer.php" class="nav-link <?= ($currentScript === 'volunteer.php') ? 'active fw-bold text-primary' : '' ?>">
                            <?= htmlspecialchars(__('nav.volunteer'), ENT_QUOTES, 'UTF-8') ?>
                        </a>
                    </li>
                <?php endif; ?>
                
                <?php if (!$isLoggedIn && $canPublicFeedback): ?>
                    <li class="nav-item">
                        <a href="<?= htmlspecialchars($baseUrl, ENT_QUOTES, 'UTF-8') ?>/feedback.php" class="nav-link <?= ($currentScript === 'feedback.php') ? 'active fw-bold text-primary' : '' ?>">
                            <?= htmlspecialchars(__('nav.feedback'), ENT_QUOTES, 'UTF-8') ?>
                        </a>
                    </li>
                <?php endif; ?>
                
                <?php if (!$isLoggedIn && $canPublicLeaderboard): ?>
                    <li class="nav-item">
                        <a href="<?= htmlspecialchars($baseUrl, ENT_QUOTES, 'UTF-8') ?>/leaderboard.php" class="nav-link <?= ($currentScript === 'leaderboard.php') ? 'active fw-bold text-primary' : '' ?>">
                            <?= htmlspecialchars(__('nav.leaderboard'), ENT_QUOTES, 'UTF-8') ?>
                        </a>
                    </li>
                <?php endif; ?>

                <?php if ($isLoggedIn): ?>
                    <li class="nav-item">
                        <a href="<?= htmlspecialchars($baseUrl, ENT_QUOTES, 'UTF-8') ?>/user/data_entry.php" class="nav-link <?= ($currentScript === 'data_entry.php') ? 'active fw-bold text-primary' : '' ?>">
                            <?= htmlspecialchars(__('nav.data_entry'), ENT_QUOTES, 'UTF-8') ?>
                        </a>
                    </li>
                    <?php if ($canModerate): ?>
                        <li class="nav-item">
                            <a href="<?= htmlspecialchars($baseUrl, ENT_QUOTES, 'UTF-8') ?>/admin/moderate.php" class="nav-link <?= ($currentScript === 'moderate.php') ? 'active fw-bold text-primary' : '' ?>">
                                <?= htmlspecialchars(__('nav.moderation'), ENT_QUOTES, 'UTF-8') ?>
                            </a>
                        </li>
                    <?php endif; ?>
                    <?php if ($canManageUsers): ?>
                        <li class="nav-item">
                            <a href="<?= htmlspecialchars($baseUrl, ENT_QUOTES, 'UTF-8') ?>/admin/users.php" class="nav-link <?= ($currentScript === 'users.php') ? 'active fw-bold text-primary' : '' ?>">
                                <?= htmlspecialchars(__('nav.manage_users'), ENT_QUOTES, 'UTF-8') ?>
                            </a>
                        </li>
                    <?php endif; ?>
                    <?php if ($canManageCols): ?>
                        <li class="nav-item">
                            <a href="<?= htmlspecialchars($baseUrl, ENT_QUOTES, 'UTF-8') ?>/admin/manage_tables.php" class="nav-link <?= ($currentScript === 'manage_tables.php') ? 'active fw-bold text-primary' : '' ?>">
                                <?= htmlspecialchars(__('nav.manage_tables'), ENT_QUOTES, 'UTF-8') ?>
                            </a>
                        </li>
                    <?php endif; ?>
                    <?php if ($modVolunteers && $canManageVols): ?>
                        <li class="nav-item">
                            <a href="<?= htmlspecialchars($baseUrl, ENT_QUOTES, 'UTF-8') ?>/admin/volunteer_dashboard.php" class="nav-link <?= ($currentScript === 'volunteer_dashboard.php') ? 'active fw-bold text-primary' : '' ?>">
                                <?= htmlspecialchars(__('nav.volunteer_dashboard'), ENT_QUOTES, 'UTF-8') ?>
                            </a>
                        </li>
                    <?php endif; ?>
                    <?php if ($modFeedback && $canManageFeed): ?>
                        <li class="nav-item">
                            <a href="<?= htmlspecialchars($baseUrl, ENT_QUOTES, 'UTF-8') ?>/admin/feedback_dashboard.php" class="nav-link <?= ($currentScript === 'feedback_dashboard.php') ? 'active fw-bold text-primary' : '' ?>">
                                <?= htmlspecialchars(__('nav.feedback_dashboard'), ENT_QUOTES, 'UTF-8') ?>
                            </a>
                        </li>
                    <?php endif; ?>
                    <?php if ($canManageSets): ?>
                        <li class="nav-item">
                            <a href="<?= htmlspecialchars($baseUrl, ENT_QUOTES, 'UTF-8') ?>/admin/settings.php" class="nav-link <?= ($currentScript === 'settings.php') ? 'active fw-bold text-primary' : '' ?>">
                                <?= htmlspecialchars(__('nav.settings'), ENT_QUOTES, 'UTF-8') ?>
                            </a>
                        </li>
                    <?php endif; ?>
                <?php endif; ?>
            </ul>

            <!-- Right-Side Account / Auth Controls -->
            <ul class="navbar-nav ms-auto align-items-lg-center gap-2 mt-3 mt-lg-0">
                <?php if ($isLoggedIn): ?>
                    <li class="nav-item d-flex align-items-center gap-2">
                        <span class="navbar-text small text-secondary">
                            <?= htmlspecialchars(__('nav.welcome'), ENT_QUOTES, 'UTF-8') ?>
                            <a href="<?= htmlspecialchars($baseUrl, ENT_QUOTES, 'UTF-8') ?>/user/profile.php" class="fw-bold text-dark text-decoration-none <?= ($currentScript === 'profile.php') ? 'text-decoration-underline' : '' ?>" aria-label="Go to User Profile">
                                <?= htmlspecialchars($displayIdentifier, ENT_QUOTES, 'UTF-8') ?>
                            </a>
                        </span>
                        <?php if ($modLeaderboard): ?>
                            <a href="<?= htmlspecialchars($baseUrl, ENT_QUOTES, 'UTF-8') ?>/leaderboard.php" class="badge bg-warning text-dark text-decoration-none px-2 py-1" aria-label="<?= htmlspecialchars(__('nav.leaderboard_score'), ENT_QUOTES, 'UTF-8') ?>: <?= $userPoints ?> points">
                                ⭐ <span class="fw-bold"><?= $userPoints ?></span>
                            </a>
                        <?php endif; ?>
                    </li>
                    <li class="nav-item">
                        <a href="<?= htmlspecialchars($baseUrl, ENT_QUOTES, 'UTF-8') ?>/user/logout.php" class="btn btn-sm btn-outline-danger" role="button" aria-label="<?= htmlspecialchars(__('nav.logout'), ENT_QUOTES, 'UTF-8') ?>">
                            <?= htmlspecialchars(__('nav.logout'), ENT_QUOTES, 'UTF-8') ?>
                        </a>
                    </li>
                <?php else: ?>
                    <li class="nav-item">
                        <a href="<?= htmlspecialchars($baseUrl, ENT_QUOTES, 'UTF-8') ?>/user/login.php" class="btn btn-sm btn-primary px-3 <?= ($currentScript === 'login.php') ? 'active' : '' ?>">
                            <?= htmlspecialchars(__('nav.login'), ENT_QUOTES, 'UTF-8') ?>
                        </a>
                    </li>
                <?php endif; ?>

                <?php if ($isLoggedIn && $modFeedback): ?>
                    <li class="nav-item">
                        <a href="<?= htmlspecialchars($baseUrl, ENT_QUOTES, 'UTF-8') ?>/feedback.php" class="btn btn-sm btn-outline-secondary <?= ($currentScript === 'feedback.php') ? 'active' : '' ?>">
                            <?= htmlspecialchars(__('nav.feedback'), ENT_QUOTES, 'UTF-8') ?>
                        </a>
                    </li>
                <?php endif; ?>
            </ul>
        </div>
    </div>
</nav>
