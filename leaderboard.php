<?php
// leaderboard.php - Volunteer Contribution Leaderboard (Publicly Accessible)
require_once 'db/db.php';
require_once 'db/auth_helpers.php';
require_once 'includes/functions.php';
session_start();

if (!is_module_enabled($pdo, 'leaderboard')) {
    http_response_code(403);
    exit('403 Forbidden: The Leaderboard module is currently disabled.');
}

$current_user = function_exists('get_current_user_data') ? get_current_user_data($pdo) : null;
$is_logged_in = ($current_user !== null || isset($_SESSION['user_id']));

$has_public_permission = guest_has_permission($pdo, 'view_leaderboard');

if (!$current_user && !$has_public_permission) {
    $base = defined('BASE_PATH') ? rtrim(BASE_PATH, '/') : '';
    header('Location: ' . $base . '/user/login.php');
    exit;
}

$stmt = $pdo->prepare("
    SELECT u.id, u.username, u.first_name, u.surname, u.points, r.role_name AS role, u.attribution_display_mode, u.is_active
    FROM users u
    LEFT JOIN roles r ON u.role_id = r.id
    WHERE u.is_active = 1
    ORDER BY u.points DESC, u.username ASC
    LIMIT 50
");
$stmt->execute();
$all_users = $stmt->fetchAll(PDO::FETCH_ASSOC);

$leaderboard_users = [];
foreach ($all_users as $u) {
    $mode = $u['attribution_display_mode'] ?? '';
    if (!in_array($mode, ['initials_random', 'full_name', 'volunteers_only'])) {
        $mode = 'initials_random';
    }

    if ($mode === 'volunteers_only' && !$is_logged_in) {
        continue;
    }

    $u['display_name'] = format_user_display_name($pdo, $u, $current_user);

    $leaderboard_users[] = $u;
}
?>
<?php require_once 'partials/header.php'; ?>

<div class="search-box-container" role="region" aria-label="Leaderboard View">
    <h3>Volunteer Contribution Leaderboard</h3>
    <p>Recognizing the efforts of our community members helping compile and transcribe parish records.</p>

    <div style="overflow-x:auto;margin-top:1.5rem;">
        <table class="data-table" style="width:100%;border-collapse:collapse;text-align:left;">
            <thead>
                <tr style="border-bottom:2px solid var(--border-color);">
                    <th style="padding:0.75rem;">Rank</th>
                    <th style="padding:0.75rem;">Volunteer</th>
                    <th style="padding:0.75rem;">Role</th>
                    <th style="padding:0.75rem;text-align:right;">Score</th>
                </tr>
            </thead>
            <tbody>
                <?php if (empty($leaderboard_users)): ?>
                    <tr>
                        <td colspan="4" style="padding:1rem;text-align:center;">No active users found on the leaderboard yet.</td>
                    </tr>
                <?php else: ?>
                    <?php $rank = 1; foreach ($leaderboard_users as $u): ?>
                        <?php
                            $is_current = ($is_logged_in && isset($current_user['username']) && $u['username'] === $current_user['username']);
                            $row_style  = $is_current ? 'background-color:rgba(0,123,255,0.1);font-weight:bold;' : '';
                        ?>
                        <tr style="border-bottom:1px solid var(--border-color);<?php echo $row_style; ?>">
                            <td style="padding:0.75rem;">
                                <?php
                                    if ($rank === 1) echo '🥇 ';
                                    elseif ($rank === 2) echo '🥈 ';
                                    elseif ($rank === 3) echo '🥉 ';
                                    echo $rank;
                                ?>
                            </td>
                            <td style="padding:0.75rem;">
                                <?php echo htmlspecialchars($u['display_name']); ?>
                                <?php if ($is_current): ?>
                                    <span style="font-size:0.8rem;color:var(--primary-color,#007bff);margin-left:0.5rem;">(You)</span>
                                <?php endif; ?>
                            </td>
                            <td style="padding:0.75rem;text-transform:capitalize;"><?php echo htmlspecialchars($u['role'] ?? 'User'); ?></td>
                            <td style="padding:0.75rem;text-align:right;">⭐ <?php echo intval($u['points']); ?></td>
                        </tr>
                    <?php $rank++; endforeach; ?>
                <?php endif; ?>
            </tbody>
        </table>
    </div>
</div>

<?php require_once 'partials/footer.php'; ?>
