<?php
declare(strict_types=1);
/**
 * Main navigation + accessibility / language bar.
 */
$initPath = dirname(__DIR__) . '/includes/init.php';
if (file_exists($initPath)) {
    require_once $initPath;
}

$queryGet = $_GET;

// High-contrast toggle is handled once in partials/header.php (avoid double-flip).

// Language switch
if (isset($queryGet['lang']) && is_string($queryGet['lang']) && function_exists('set_language')) {
    $requested = preg_replace('/[^a-zA-Z_]/', '', $queryGet['lang']) ?? '';
    $langFile = __DIR__ . '/../lang/' . $requested . '.php';
    if ($requested !== '' && is_file($langFile)) {
        set_language($requested);
    }
    $redirectUrl = strtok($_SERVER['REQUEST_URI'] ?? '/', '?') ?: '/';
    header('Location: ' . $redirectUrl);
    exit;
}

$isHighContrast = isset($_SESSION['high_contrast']) && $_SESSION['high_contrast'];

/** @var array{id: int|string, username: string, first_name?: string, points?: int|string}|null $currentUser */
$currentUser = (function_exists('get_current_user_data') && isset($pdo) && $pdo instanceof PDO)
    ? get_current_user_data($pdo) : null;
$isLoggedIn = ($currentUser !== null || isset($_SESSION['user_id']));

// Optional flags for common languages; others still appear by scanned name
$languageMeta = [
    'ar'     => ['name' => 'العربية', 'flag' => '🇸🇦'],
    'br'     => ['name' => 'Brezhoneg', 'flag' => '🏴󠁧󠁢󠁳󠁣󠁴󠁿'], // or appropriate flag
    'da'     => ['name' => 'Dansk', 'flag' => '🇩🇰'],
    'de'     => ['name' => 'Deutsch', 'flag' => '🇩🇪'],
    'en'     => ['name' => 'English', 'flag' => '🇬🇧'],
    'en_AU'  => ['name' => 'English (AU)', 'flag' => '🇦🇺'],
    'en_GB_chav' => ['name' => 'English (Chav)', 'flag' => '🇬🇧'],
    'en_US'  => ['name' => 'English (US)', 'flag' => '🇺🇸'],
    'es'     => ['name' => 'Español', 'flag' => '🇪🇸'],
    'fa'     => ['name' => 'فارسی', 'flag' => '🇮🇷'],
    'fr'     => ['name' => 'Français', 'flag' => '🇫🇷'],
    'ga'     => ['name' => 'Gaeilge', 'flag' => '🇮🇪'],
    'gd'     => ['name' => 'Gàidhlig', 'flag' => '🏴󠁧󠁢󠁳󠁣󠁴󠁿'],
    'gv'     => ['name' => 'Gaelg', 'flag' => '🇮🇲'],
    'it'     => ['name' => 'Italiano', 'flag' => '🇮🇹'],
    'ja'     => ['name' => '日本語', 'flag' => '🇯🇵'],
    'kw'     => ['name' => 'Kernewek', 'flag' => '🏴󠁧󠁢󠁧󠁿'],
    'ln'     => ['name' => 'Lingála', 'flag' => '🇨🇩'],
    'pl'     => ['name' => 'Polski', 'flag' => '🇵🇱'],
    'prs'    => ['name' => 'Dari', 'flag' => '🇦🇫'],
    'ps'     => ['name' => 'پښتو', 'flag' => '🇦🇫'],
    'pt'     => ['name' => 'Português', 'flag' => '🇵🇹'],
    'pt_BR'  => ['name' => 'Português (BR)', 'flag' => '🇧🇷'],
    'rn'     => ['name' => 'Kirundi', 'flag' => '🇧🇮'],
    'ru'     => ['name' => 'Русский', 'flag' => '🇷🇺'],
    'so'     => ['name' => 'Soomaali', 'flag' => '🇸🇴'],
    'uk'     => ['name' => 'Українська', 'flag' => '🇺🇦'],
    'cy'     => ['name' => 'Cymraeg', 'flag' => '🏴󠁧󠁢󠁷󠁬󠁳󠁿'],
    'zh_CN'  => ['name' => '中文 (简体)', 'flag' => '🇨🇳'],
    'zh_TW'  => ['name' => '中文 (繁體)', 'flag' => '🇹🇼'],
];

$navLanguages = [];
$langDir = __DIR__ . '/../lang';
if (is_dir($langDir)) {
    $globFiles = glob($langDir . '/*.php');
    if ($globFiles !== false) {
        foreach ($globFiles as $file) {
            $code = basename($file, '.php');
            if (preg_match('/^[a-zA-Z_]+$/', $code)) {
                $navLanguages[$code] = $languageMeta[$code]['name'] ?? ucwords(str_replace('_', ' ', $code));
            }
        }
    }
    ksort($navLanguages);
}
$activeLang = function_exists('get_active_language') ? get_active_language() : 'en';

$pdoOk = isset($pdo) && $pdo instanceof PDO;

$modModeration = $pdoOk ? is_module_enabled($pdo, 'moderation') : true;
$modVolunteers = $pdoOk ? is_module_enabled($pdo, 'volunteers') : true;
$modFeedback = $pdoOk ? is_module_enabled($pdo, 'feedback') : true;
$modUsers = $pdoOk ? is_module_enabled($pdo, 'users') : true;
$modLeaderboard = $pdoOk ? is_module_enabled($pdo, 'leaderboard') : true;

$canPublicSearch = $isLoggedIn || ($pdoOk && guest_has_permission($pdo, 'view_as_guest'));
$canPublicVolunteer = !$isLoggedIn && $pdoOk && $modVolunteers && guest_has_permission($pdo, 'submit_volunteer');
$canPublicFeedback = !$isLoggedIn && $pdoOk && $modFeedback && guest_has_permission($pdo, 'submit_feedback');
$canPublicLeaderboard = !$isLoggedIn && $pdoOk && $modLeaderboard && guest_has_permission($pdo, 'view_leaderboard');
$canDataEntry = $isLoggedIn && $pdoOk && function_exists('has_permission') && has_permission($pdo, 'access_data_entry');
$canManageDuplicates = $isLoggedIn && $pdoOk && function_exists('has_permission') && has_permission($pdo, 'edit_records');

// Keep moderate_table_1 special-case + dynamic tables + moderate_suggestions
$canModerate = false;
if ($pdoOk && $modModeration) {
    if (is_admin($pdo) || has_permission($pdo, 'moderate_table_1') || has_permission($pdo, 'moderate_suggestions')) {
        $canModerate = true;
    } else {
        $tablesChk = $pdo->query('SELECT id FROM dynamic_tables');
        $tableIds = $tablesChk !== false ? $tablesChk->fetchAll(PDO::FETCH_COLUMN) : [];
        foreach ($tableIds as $tId) {
            if (has_permission($pdo, 'moderate_table_' . (int) $tId)) {
                $canModerate = true;
                break;
            }
        }
    }
}

$canManageUsers = $pdoOk && $modUsers && has_permission($pdo, 'manage_users');
$canManageCols = $pdoOk && has_permission($pdo, 'manage_columns');
$canManageVols = $pdoOk && $modVolunteers && has_permission($pdo, 'manage_volunteers');
$canManageFeed = $pdoOk && $modFeedback && has_permission($pdo, 'manage_feedback');
$canManageSets = $pdoOk && (
    has_permission($pdo, 'manage_settings')
    || has_permission($pdo, 'manage_audit_logs')
    || has_permission($pdo, 'view_error_logs')
    || has_permission($pdo, 'manage_notices')
    || has_permission($pdo, 'purge_audit_entry')
);
$showAdminMenu = $canManageUsers || $canManageCols || $canManageVols || $canManageFeed || $canManageSets;

$baseUrl = defined('BASE_PATH') && is_string(BASE_PATH) ? rtrim(BASE_PATH, '/') : '';
$requestUri = parse_url($_SERVER['REQUEST_URI'] ?? '/', PHP_URL_PATH) ?? '/';
$currentRoute = str_replace($baseUrl, '', (string) $requestUri);
if ($currentRoute === '' || $currentRoute === false) {
    $currentRoute = '/';
}

$systemName = ($pdoOk && function_exists('get_system_name'))
    ? get_system_name($pdo) : 'Parish Records Directory (PRD)';

$sessionUsername = isset($_SESSION['username']) && is_string($_SESSION['username'])
    ? $_SESSION['username'] : 'User';
$displayUsername = ($currentUser !== null && isset($currentUser['username']) && is_string($currentUser['username']))
    ? $currentUser['username'] : $sessionUsername;
$displayIdentifier = ($currentUser !== null && isset($currentUser['first_name']) && is_string($currentUser['first_name']) && $currentUser['first_name'] !== '')
    ? $currentUser['first_name'] : $displayUsername;
$userPoints = ($currentUser !== null && isset($currentUser['points'])) ? (int) $currentUser['points'] : 0;

$navActive = static function (string $route) use ($currentRoute): string {
    return ($currentRoute === $route) ? 'active fw-bold text-primary' : '';
};
?>
<!-- Top Accessibility & Language Bar -->
<div class="bg-dark text-white py-1 px-3 small border-bottom">
    <div class="container d-flex justify-content-between align-items-center flex-wrap gap-2">
        <div>
            <a href="?contrast=toggle" class="text-white text-decoration-none d-inline-flex align-items-center gap-1" role="button"
               aria-label="<?= htmlspecialchars(__('nav.high_contrast'), ENT_QUOTES, 'UTF-8') ?>">
                <span aria-hidden="true">👁️</span>
                <span><?= htmlspecialchars($isHighContrast ? __('nav.low_contrast') : __('nav.high_contrast'), ENT_QUOTES, 'UTF-8') ?></span>
            </a>
        </div>
        <?php if (count($navLanguages) > 1): ?>
            <div class="d-inline-flex align-items-center gap-2">
                <label for="site-lang-select" class="text-white-50 mb-0">
                    <span aria-hidden="true">🌐</span>
                    <span class="visually-hidden"><?= htmlspecialchars(__('nav.language_label'), ENT_QUOTES, 'UTF-8') ?></span>
                </label>
                <select id="site-lang-select"
                        onchange="if(this.value) window.location.href='?lang=' + encodeURIComponent(this.value);"
                        class="form-select form-select-sm bg-dark text-white border-secondary py-0 px-2"
                        style="width: auto; font-size: 0.85rem;"
                        aria-label="<?= htmlspecialchars(__('nav.select_language'), ENT_QUOTES, 'UTF-8') ?>"
                    <?php foreach ($navLanguages as $code => $label): ?>
                        <?php $flag = $languageMeta[$code]['flag'] ?? '🌐'; ?>
                        <option value="<?= htmlspecialchars($code, ENT_QUOTES, 'UTF-8') ?>"
                            <?= ($code === $activeLang) ? 'selected' : '' ?> class="bg-dark text-white">
                            <?= htmlspecialchars(trim($flag . ' ' . $label), ENT_QUOTES, 'UTF-8') ?>
                        </option>
                    <?php endforeach; ?>
                </select>
            </div>
        <?php endif; ?>
    </div>
</div>

<nav class="navbar navbar-expand-lg navbar-light bg-white shadow-sm sticky-top" aria-label="Main Navigation">
    <div class="container">
        <a class="navbar-brand fw-bold text-primary" href="<?= htmlspecialchars($baseUrl, ENT_QUOTES, 'UTF-8') ?>/">
            <?= htmlspecialchars($systemName, ENT_QUOTES, 'UTF-8') ?>
        </a>

        <button class="navbar-toggler border-0" type="button" data-bs-toggle="collapse"
                data-bs-target="#mainNavbarContent" aria-controls="mainNavbarContent"
                aria-expanded="false" aria-label="<?= htmlspecialchars(__('nav.toggle_navigation'), ENT_QUOTES, 'UTF-8') ?>">
            <span class="navbar-toggler-icon"></span>
        </button>

        <div class="collapse navbar-collapse" id="mainNavbarContent">
            <ul class="navbar-nav me-auto mb-2 mb-lg-0 align-items-lg-center gap-lg-1">
                <?php if ($canPublicSearch): ?>
                    <li class="nav-item">
                        <a href="<?= htmlspecialchars($baseUrl, ENT_QUOTES, 'UTF-8') ?>/"
                           class="nav-link <?= ($currentRoute === '/' || $currentRoute === '/index.php') ? 'active fw-bold text-primary' : '' ?>">
                            <?= htmlspecialchars(__('nav.search'), ENT_QUOTES, 'UTF-8') ?>
                        </a>
                    </li>
                <?php endif; ?>

                <?php if ($canPublicVolunteer): ?>
                    <li class="nav-item">
                        <a href="<?= htmlspecialchars($baseUrl, ENT_QUOTES, 'UTF-8') ?>/volunteer"
                           class="nav-link <?= $navActive('/volunteer') ?>">
                            <?= htmlspecialchars(__('nav.volunteer'), ENT_QUOTES, 'UTF-8') ?>
                        </a>
                    </li>
                <?php endif; ?>

                <?php if ($canPublicFeedback): ?>
                    <li class="nav-item">
                        <a href="<?= htmlspecialchars($baseUrl, ENT_QUOTES, 'UTF-8') ?>/feedback"
                           class="nav-link <?= $navActive('/feedback') ?>">
                            <?= htmlspecialchars(__('nav.feedback'), ENT_QUOTES, 'UTF-8') ?>
                        </a>
                    </li>
                <?php endif; ?>

                <?php if ($canPublicLeaderboard): ?>
                    <li class="nav-item">
                        <a href="<?= htmlspecialchars($baseUrl, ENT_QUOTES, 'UTF-8') ?>/leaderboard"
                           class="nav-link <?= $navActive('/leaderboard') ?>">
                            <?= htmlspecialchars(__('nav.leaderboard'), ENT_QUOTES, 'UTF-8') ?>
                        </a>
                    </li>
                <?php endif; ?>

                <?php if ($isLoggedIn): ?>
                    <?php if ($canDataEntry): ?>
                    <li class="nav-item">
                        <a href="<?= htmlspecialchars($baseUrl, ENT_QUOTES, 'UTF-8') ?>/data-entry"
                           class="nav-link <?= $navActive('/data-entry') ?>">
                            <?= htmlspecialchars(__('nav.data_entry'), ENT_QUOTES, 'UTF-8') ?>
                        </a>
                    </li>
                    <?php endif; ?>

                    <?php if ($canManageDuplicates && !$canModerate): ?>
                        <li class="nav-item">
                            <a href="<?= htmlspecialchars($baseUrl, ENT_QUOTES, 'UTF-8') ?>/admin/duplicates"
                               class="nav-link <?= str_starts_with($currentRoute, '/admin/duplicates') ? 'active fw-bold text-primary' : '' ?>">
                                <?= htmlspecialchars(__('nav.similar_records') !== 'nav.similar_records' ? __('nav.similar_records') : 'Similar records', ENT_QUOTES, 'UTF-8') ?>
                            </a>
                        </li>
                    <?php endif; ?>
                    <?php if ($canModerate): ?>
                        <li class="nav-item">
                            <a href="<?= htmlspecialchars($baseUrl, ENT_QUOTES, 'UTF-8') ?>/admin/moderation"
                               class="nav-link <?= str_starts_with($currentRoute, '/admin/moderation') ? 'active fw-bold text-primary' : '' ?>">
                                <?= htmlspecialchars(__('nav.moderation'), ENT_QUOTES, 'UTF-8') ?>
                            </a>
                        </li>
                    <?php endif; ?>

                    <?php if ($showAdminMenu): ?>
                        <li class="nav-item dropdown">
                            <a class="nav-link dropdown-toggle <?= (str_starts_with($currentRoute, '/admin') && !str_starts_with($currentRoute, '/admin/moderation') && !str_starts_with($currentRoute, '/admin/gh-feedback')) ? 'active fw-bold text-primary' : '' ?>"
                               href="#" id="adminNavDropdown" role="button"
                               data-bs-toggle="dropdown" aria-expanded="false">
                                <?= htmlspecialchars(__('nav.admin'), ENT_QUOTES, 'UTF-8') ?>
                            </a>
                            <ul class="dropdown-menu" aria-labelledby="adminNavDropdown">
                                <?php if ($canManageUsers): ?>
                                    <li>
                                        <a class="dropdown-item <?= str_starts_with($currentRoute, '/admin/users') ? 'active' : '' ?>"
                                           href="<?= htmlspecialchars($baseUrl, ENT_QUOTES, 'UTF-8') ?>/admin/users">
                                            <?= htmlspecialchars(__('nav.manage_users'), ENT_QUOTES, 'UTF-8') ?>
                                        </a>
                                    </li>
                                <?php endif; ?>
                                <?php if ($canManageCols): ?>
                                    <li>
                                        <a class="dropdown-item <?= str_starts_with($currentRoute, '/admin/tables') ? 'active' : '' ?>"
                                           href="<?= htmlspecialchars($baseUrl, ENT_QUOTES, 'UTF-8') ?>/admin/tables">
                                            <?= htmlspecialchars(__('nav.manage_tables'), ENT_QUOTES, 'UTF-8') ?>
                                        </a>
                                    </li>
                                <?php endif; ?>
                                <?php if ($canManageVols): ?>
                                    <li>
                                        <a class="dropdown-item <?= str_starts_with($currentRoute, '/admin/volunteers') ? 'active' : '' ?>"
                                           href="<?= htmlspecialchars($baseUrl, ENT_QUOTES, 'UTF-8') ?>/admin/volunteers">
                                            <?= htmlspecialchars(__('nav.volunteer_dashboard'), ENT_QUOTES, 'UTF-8') ?>
                                        </a>
                                    </li>
                                <?php endif; ?>
                                <?php if ($canManageFeed): ?>
                                    <li>
                                        <a class="dropdown-item <?= str_starts_with($currentRoute, '/admin/tickets') ? 'active' : '' ?>"
                                           href="<?= htmlspecialchars($baseUrl, ENT_QUOTES, 'UTF-8') ?>/admin/tickets">
                                            <?= htmlspecialchars(__('nav.feedback_dashboard'), ENT_QUOTES, 'UTF-8') ?>
                                        </a>
                                    </li>
                                <?php endif; ?>
                                <?php if ($canManageSets): ?>
                                    <li><hr class="dropdown-divider"></li>
                                    <li>
                                        <a class="dropdown-item <?= ($currentRoute === '/admin' || str_starts_with($currentRoute, '/admin/settings')) ? 'active' : '' ?>"
                                           href="<?= htmlspecialchars($baseUrl, ENT_QUOTES, 'UTF-8') ?>/admin">
                                            <?= htmlspecialchars(__('nav.settings'), ENT_QUOTES, 'UTF-8') ?>
                                        </a>
                                    </li>
                                <?php endif; ?>
                            </ul>
                        </li>
                    <?php endif; ?>
                <?php endif; ?>
            </ul>

            <ul class="navbar-nav ms-auto align-items-lg-center gap-2 mt-3 mt-lg-0">
                <?php if ($isLoggedIn): ?>
                    <li class="nav-item d-flex align-items-center gap-2">
                        <span class="navbar-text small text-secondary">
                            <?= htmlspecialchars(__('nav.welcome'), ENT_QUOTES, 'UTF-8') ?>
                            <a href="<?= htmlspecialchars($baseUrl, ENT_QUOTES, 'UTF-8') ?>/profile"
                               class="fw-bold text-dark text-decoration-none <?= $navActive('/profile') ?>"
                               aria-label="<?= htmlspecialchars(__('nav.profile'), ENT_QUOTES, 'UTF-8') ?>">
                                <?= htmlspecialchars($displayIdentifier, ENT_QUOTES, 'UTF-8') ?>
                            </a>
                        </span>
                        <?php if ($modLeaderboard): ?>
                            <a href="<?= htmlspecialchars($baseUrl, ENT_QUOTES, 'UTF-8') ?>/leaderboard"
                               class="badge bg-warning text-dark text-decoration-none px-2 py-1"
                               aria-label="<?= htmlspecialchars(__('nav.leaderboard_score'), ENT_QUOTES, 'UTF-8') ?>: <?= $userPoints ?>">
                                ⭐ <span class="fw-bold"><?= $userPoints ?></span>
                            </a>
                        <?php endif; ?>
                    </li>
                    <li class="nav-item">
                        <a href="<?= htmlspecialchars($baseUrl, ENT_QUOTES, 'UTF-8') ?>/logout"
                           class="btn btn-sm btn-outline-danger" role="button">
                            <?= htmlspecialchars(__('nav.logout'), ENT_QUOTES, 'UTF-8') ?>
                        </a>
                    </li>
                    <?php if ($canManageSets): ?>
                        <li class="nav-item">
                            <a href="<?= htmlspecialchars($baseUrl, ENT_QUOTES, 'UTF-8') ?>/admin/gh-feedback"
                               class="btn btn-sm btn-outline-primary bg-white text-primary<?= $currentRoute === '/admin/gh-feedback' ? ' fw-bold' : '' ?>">
                                <?= htmlspecialchars(__('nav.feedback'), ENT_QUOTES, 'UTF-8') ?>
                            </a>
                        </li>
                    <?php elseif ($modFeedback): ?>
                        <li class="nav-item">
                            <a href="<?= htmlspecialchars($baseUrl, ENT_QUOTES, 'UTF-8') ?>/feedback"
                               class="btn btn-sm btn-outline-primary bg-white text-primary<?= $currentRoute === '/feedback' ? ' fw-bold' : '' ?>">
                                <?= htmlspecialchars(__('nav.feedback'), ENT_QUOTES, 'UTF-8') ?>
                            </a>
                        </li>
                    <?php endif; ?>
                <?php else: ?>
                    <li class="nav-item">
                        <a href="<?= htmlspecialchars($baseUrl, ENT_QUOTES, 'UTF-8') ?>/login"
                           class="btn btn-sm btn-primary px-3 <?= $navActive('/login') ?>">
                            <?= htmlspecialchars(__('nav.login'), ENT_QUOTES, 'UTF-8') ?>
                        </a>
                    </li>
                <?php endif; ?>
            </ul>
        </div>
    </div>
</nav>
