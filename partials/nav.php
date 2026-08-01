<?php
// Temporary debug test
if (isset($_GET['lang'])) {
    var_dump($_GET['lang']); // See what the server actually catches
}

// partials/nav.php - Centralized header navigation, permissions, and system name
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}
$auth_helper_path = __DIR__ . '/../db/auth_helpers.php';
if (file_exists($auth_helper_path)) {
    require_once $auth_helper_path;
}
$functions_path = __DIR__ . '/../includes/functions.php';
if (file_exists($functions_path)) {
    require_once $functions_path;
}

// High-contrast toggle
if (isset($_GET['contrast']) && $_GET['contrast'] === 'toggle') {
    $_SESSION['high_contrast'] = !($_SESSION['high_contrast'] ?? false);
    $redirect_url = strtok($_SERVER['REQUEST_URI'], '?');
    header('Location: ' . $redirect_url);
    exit;
}

// Language switch (?lang=en / ?lang=cy …)
if (isset($_GET['lang']) && function_exists('set_language')) {
    $requested = preg_replace('/[^a-zA-Z_]/', '', $_GET['lang']);
    $lang_file = __DIR__ . '/../lang/' . $requested . '.php';
    if ($requested !== '' && is_file($lang_file)) {
        set_language($requested);
    }
    $redirect_url = strtok($_SERVER['REQUEST_URI'], '?');
    header('Location: ' . $redirect_url);
    exit;
}

$is_high_contrast = $_SESSION['high_contrast'] ?? false;
$current_user = (function_exists('get_current_user_data') && isset($pdo)) ? get_current_user_data($pdo) : null;
$is_logged_in = ($current_user !== null || isset($_SESSION['user_id']));

// Comprehensive flag & human-readable label dictionary for all your language files
$language_meta = [
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
$nav_languages = [];
$lang_dir = __DIR__ . '/../lang';
if (is_dir($lang_dir)) {
    foreach (glob($lang_dir . '/*.php') as $file) {
        $code = basename($file, '.php');
        if (preg_match('/^[a-zA-Z_\-]+$/', $code)) {
            $nav_languages[$code] = $language_meta[$code]['name'] ?? ucwords(str_replace('_', ' ', $code));
        }
    }
    ksort($nav_languages);
}
$active_lang = function_exists('get_active_language') ? get_active_language() : 'en';

// ------------------------------------------------------------------
// Module toggles
// ------------------------------------------------------------------
$mod_moderation  = isset($pdo) ? is_module_enabled($pdo, 'moderation') : true;
$mod_volunteers  = isset($pdo) ? is_module_enabled($pdo, 'volunteers') : true;
$mod_feedback    = isset($pdo) ? is_module_enabled($pdo, 'feedback') : true;
$mod_users       = isset($pdo) ? is_module_enabled($pdo, 'users') : true;
$mod_leaderboard = isset($pdo) ? is_module_enabled($pdo, 'leaderboard') : true;

// Public button visibility
$can_public_search      = $is_logged_in || (isset($pdo) && guest_has_permission($pdo, 'view_public'));
$can_public_volunteer   = $is_logged_in || (isset($pdo) && $mod_volunteers && guest_has_permission($pdo, 'submit_volunteer'));
$can_public_feedback    = $is_logged_in || (isset($pdo) && $mod_feedback && guest_has_permission($pdo, 'submit_feedback'));
$can_public_leaderboard = $is_logged_in || (isset($pdo) && $mod_leaderboard && guest_has_permission($pdo, 'view_leaderboard'));

// ------------------------------------------------------------------
// Logged-in capability checks
// ------------------------------------------------------------------
$can_moderate = false;
if (isset($pdo) && $mod_moderation) {
    if (is_admin($pdo) || has_permission($pdo, 'moderate_table_1')) {
        $can_moderate = true;
    } else {
        $tables_chk = $pdo->query("SELECT id FROM dynamic_tables")->fetchAll(PDO::FETCH_COLUMN);
        foreach ($tables_chk as $t_id) {
            if (has_permission($pdo, 'moderate_table_' . $t_id)) {
                $can_moderate = true;
                break;
            }
        }
    }
}
$can_manage_users = isset($pdo) && $mod_users && has_permission($pdo, 'manage_users', 'Manage user accounts');
$can_manage_cols  = isset($pdo) && has_permission($pdo, 'manage_columns', 'Configure table columns');
$can_manage_vols  = isset($pdo) && $mod_volunteers && has_permission($pdo, 'manage_volunteers', 'Manage volunteers');
$can_manage_feed  = isset($pdo) && $mod_feedback && has_permission($pdo, 'manage_feedback', 'Manage feedback');
$can_manage_sets  = isset($pdo) && has_permission($pdo, 'manage_settings', 'Manage global settings');

$base_url = defined('BASE_PATH') ? BASE_PATH : '';
$current_script = basename($_SERVER['SCRIPT_NAME']);
$system_name = (function_exists('get_system_name') && isset($pdo)) ? get_system_name($pdo) : 'Parish Records Directory (PRD)';
$display_username = $current_user['username'] ?? ($_SESSION['username'] ?? 'User');

// Only apply full size for exact standard 'en'; all other variants (including chav, US, AU) get compact sizing
$is_translated_lang = ($active_lang !== 'en');
?>

<?php if ($is_translated_lang): ?>
<style>
/* Automatically scale down font size and padding slightly for non-standard English languages to keep them on one line */
.nav-menu-container > a.btn {
    font-size: 0.85rem;
    padding-left: 0.5rem;
    padding-right: 0.5rem;
}
</style>
<?php endif; ?>

<!-- Accessibility & Controls Bar (Far Left, Spaced Out Downwards) -->
<aside aria-label="Accessibility and Language Settings" style="display: flex; justify-content: flex-start; align-items: center; gap: 1.25rem; margin-bottom: 2rem; flex-wrap: wrap;">
    <!-- High-Contrast Toggle Button -->
    <a href="?contrast=toggle" class="btn contrast-toggle-btn" role="button" aria-label="<?php echo htmlspecialchars(__('nav.high_contrast')); ?>">
        <span aria-hidden="true">👁️</span> <?php echo htmlspecialchars($is_high_contrast ? __('nav.low_contrast') : __('nav.high_contrast')); ?>
    </a>

    <!-- Accessible Language Picker with Flags -->
    <?php if (count($nav_languages) > 1): ?>
        <div class="lang-switcher-dropdown" style="display: inline-flex; align-items: center; gap: 0.5rem;">
            <label for="site-lang-select" style="font-size: 0.9rem; font-weight: bold; color: inherit;">
                <span aria-hidden="true">🌐</span> <span class="sr-only">Language Selector:</span>
            </label>
            <select id="site-lang-select" 
                    onchange="if(this.value) window.location.href='?lang=' + encodeURIComponent(this.value);" 
                    style="padding: 0.3rem 0.6rem; font-size: 0.9rem; border-radius: 4px; border: 1px solid var(--border-color); background: var(--bg-color, #fff); color: inherit; cursor: pointer;"
                    aria-label="Select Application Language">
                <?php foreach ($nav_languages as $code => $label): ?>
                    <?php 
                        $flag = $language_meta[$code]['flag'] ?? '🏳️';
                        $display_text = $flag . ' ' . $label;
                    ?>
                    <option value="<?php echo htmlspecialchars($code); ?>" <?php echo ($code === $active_lang) ? 'selected' : ''; ?>>
                        <?php echo htmlspecialchars($display_text); ?>
                    </option>
                <?php endforeach; ?>
            </select>
        </div>
    <?php endif; ?>
</aside>

<div class="header-bar header-bar-flex" role="banner">
    <h1><?php echo htmlspecialchars($system_name); ?></h1>
    <?php if ($is_logged_in): ?>
        <?php
            $display_identifier = !empty($current_user['first_name']) ? $current_user['first_name'] : $display_username;
            $user_points = $current_user['points'] ?? 0;
        ?>
        <div class="top-right-account-menu" role="region" aria-label="User Account Menu" style="display:flex;align-items:center;gap:1rem;">
            <span class="top-right-welcome">
                <?php echo htmlspecialchars(__('nav.welcome')); ?>
                <a href="<?php echo $base_url; ?>/user/profile.php"
                   style="color:inherit;<?php echo ($current_script === 'profile.php') ? 'font-weight:bold;text-decoration:underline;' : ''; ?>"
                   aria-label="Go to User Profile">
                    <?php echo htmlspecialchars($display_identifier); ?>
                </a>
                <?php if ($mod_leaderboard): ?>
                    <span class="gamification-badge" style="margin-left:0.75rem;font-weight:bold;">
                        <a href="<?php echo $base_url; ?>/leaderboard.php"
                           style="text-decoration:none;color:inherit;"
                           aria-label="<?php echo htmlspecialchars(__('nav.leaderboard_score')); ?>: <?php echo intval($user_points); ?> points">
                            ⭐ <span style="text-decoration:underline;"><?php echo intval($user_points); ?></span>
                        </a>
                    </span>
                <?php endif; ?>
            </span>
            <a href="<?php echo $base_url; ?>/user/logout.php" class="btn btn-danger btn-discreet" role="button" aria-label="<?php echo htmlspecialchars(__('nav.logout')); ?>">
                <?php echo htmlspecialchars(__('nav.logout')); ?>
            </a>
        </div>
    <?php endif; ?>
</div>

<nav class="nav-menu-container nav-menu-flex" aria-label="Main Navigation">
    <?php if ($can_public_search): ?>
        <a href="<?php echo $base_url; ?>/index.php"
           class="btn btn-secondary <?php echo ($current_script === 'index.php') ? 'btn-active' : ''; ?>">
            <?php echo htmlspecialchars(__('nav.search')); ?>
        </a>
    <?php endif; ?>
    <?php if (!$is_logged_in && $can_public_volunteer): ?>
        <a href="<?php echo $base_url; ?>/volunteer.php"
           class="btn btn-secondary <?php echo ($current_script === 'volunteer.php') ? 'btn-active' : ''; ?>">
            <?php echo htmlspecialchars(__('nav.volunteer')); ?>
        </a>
    <?php endif; ?>
    <?php if (!$is_logged_in && $can_public_feedback): ?>
        <a href="<?php echo $base_url; ?>/feedback.php"
           class="btn btn-secondary <?php echo ($current_script === 'feedback.php') ? 'btn-active' : ''; ?>">
            <?php echo htmlspecialchars(__('nav.feedback')); ?>
        </a>
    <?php endif; ?>
    <?php if (!$is_logged_in && $can_public_leaderboard): ?>
        <a href="<?php echo $base_url; ?>/leaderboard.php"
           class="btn btn-secondary <?php echo ($current_script === 'leaderboard.php') ? 'btn-active' : ''; ?>">
            <?php echo htmlspecialchars(__('nav.leaderboard')); ?>
        </a>
    <?php endif; ?>
    <?php if ($is_logged_in): ?>
        <a href="<?php echo $base_url; ?>/user/data_entry.php"
           class="btn <?php echo ($current_script === 'data_entry.php') ? 'btn-active' : ''; ?>">
            <?php echo htmlspecialchars(__('nav.data_entry')); ?>
        </a>
        <?php if ($can_moderate): ?>
            <a href="<?php echo $base_url; ?>/admin/moderate.php"
               class="btn btn-secondary <?php echo ($current_script === 'moderate.php') ? 'btn-active' : ''; ?>">
                <?php echo htmlspecialchars(__('nav.moderation')); ?>
            </a>
        <?php endif; ?>
        <?php if ($can_manage_users): ?>
            <a href="<?php echo $base_url; ?>/admin/users.php"
               class="btn btn-secondary <?php echo ($current_script === 'users.php') ? 'btn-active' : ''; ?>">
                <?php echo htmlspecialchars(__('nav.manage_users')); ?>
            </a>
        <?php endif; ?>
        <?php if ($can_manage_cols): ?>
            <a href="<?php echo $base_url; ?>/admin/manage_tables.php"
               class="btn btn-secondary <?php echo ($current_script === 'manage_tables.php') ? 'btn-active' : ''; ?>">
                <?php echo htmlspecialchars(__('nav.manage_tables')); ?>
            </a>
        <?php endif; ?>
        <?php if ($mod_volunteers && $can_manage_vols): ?>
            <a href="<?php echo $base_url; ?>/admin/volunteer_dashboard.php"
               class="btn btn-secondary <?php echo ($current_script === 'volunteer_dashboard.php') ? 'btn-active' : ''; ?>">
                <?php echo htmlspecialchars(__('nav.volunteer_dashboard')); ?>
            </a>
        <?php endif; ?>
        <?php if ($mod_feedback && $can_manage_feed): ?>
            <a href="<?php echo $base_url; ?>/admin/feedback_dashboard.php"
               class="btn btn-secondary <?php echo ($current_script === 'feedback_dashboard.php') ? 'btn-active' : ''; ?>">
                <?php echo htmlspecialchars(__('nav.feedback_dashboard')); ?>
            </a>
        <?php endif; ?>
        <?php if ($can_manage_sets): ?>
            <a href="<?php echo $base_url; ?>/admin/settings.php"
               class="btn btn-secondary <?php echo ($current_script === 'settings.php') ? 'btn-active' : ''; ?>">
                <?php echo htmlspecialchars(__('nav.settings')); ?>
            </a>
        <?php endif; ?>
    <?php endif; ?>
    <?php if (!$is_logged_in): ?>
        <a href="<?php echo $base_url; ?>/user/login.php"
           class="btn <?php echo ($current_script === 'login.php') ? 'btn-active' : ''; ?> nav-push-right">
            <?php echo htmlspecialchars(__('nav.login')); ?>
        </a>
    <?php endif; ?>
    <?php if ($is_logged_in && $mod_feedback): ?>
        <a href="<?php echo $base_url; ?>/feedback.php"
           class="btn btn-secondary <?php echo ($current_script === 'feedback.php') ? 'btn-active' : ''; ?> nav-push-right">
            <?php echo htmlspecialchars(__('nav.feedback')); ?>
        </a>
    <?php endif; ?>
</nav>
<hr style="border:0.0625rem solid var(--border-color);margin-top:1rem;margin-bottom:1.5rem;">
