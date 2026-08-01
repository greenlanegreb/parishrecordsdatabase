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

<div class="search-box-container" role="region" aria-label="<?php echo htmlspecialchars(__('leaderboard.aria_region')); ?>">
    <h3><?php echo htmlspecialchars(__('leaderboard.heading')); ?></h3>
    <p><?php echo htmlspecialchars(__('leaderboard.subheading')); ?></p>

    <div style="overflow-x:auto;margin-top:1.5rem;">
        <table class="data-table" style="width:100%;border-collapse:collapse;text-align:left;">
            <thead>
                <tr style="border-bottom:2px solid var(--border-color);">
                    <th style="padding:0.75rem;"><?php echo htmlspecialchars(__('leaderboard.th_rank')); ?></th>
                    <th style="padding:0.75rem;"><?php echo htmlspecialchars(__('leaderboard.th_contributor')); ?></th>
                    <th style="padding:0.75rem;"><?php echo htmlspecialchars(__('leaderboard.th_role')); ?></th>
                    <th style="padding:0.75rem;text-align:right;"><?php echo htmlspecialchars(__('leaderboard.th_score')); ?></th>
                </tr>
            </thead>
            <tbody>
                <?php if (empty($leaderboard_users)): ?>
                    <tr>
                        <td colspan="4" style="padding:1rem;text-align:center;"><?php echo htmlspecialchars(__('leaderboard.no_users')); ?></td>
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
                                    $medal_label = '';
                                    if ($rank === 1) { 
                                        $medal_label = __('leaderboard.medal_gold'); 
                                        echo '<span aria-hidden="true" title="' . htmlspecialchars($medal_label) . '">🥇 </span>'; 
                                    } elseif ($rank === 2) { 
                                        $medal_label = __('leaderboard.medal_silver'); 
                                        echo '<span aria-hidden="true" title="' . htmlspecialchars($medal_label) . '">🥈 </span>'; 
                                    } elseif ($rank === 3) { 
                                        $medal_label = __('leaderboard.medal_bronze'); 
                                        echo '<span aria-hidden="true" title="' . htmlspecialchars($medal_label) . '">🥉 </span>'; 
                                    } elseif ($rank === 4) { 
                                        $medal_label = __('leaderboard.medal_ribbon'); 
                                        echo '<span aria-hidden="true" title="' . htmlspecialchars($medal_label) . '">🎗️ </span>'; 
                                    } elseif ($rank === 5) { 
                                        $medal_label = __('leaderboard.medal_rosette'); 
                                        echo '<span aria-hidden="true" title="' . htmlspecialchars($medal_label) . '">🏵️ </span>'; 
                                    } elseif ($rank === 6) { 
                                        $medal_label = __('leaderboard.medal_trophy'); 
                                        echo '<span aria-hidden="true" title="' . htmlspecialchars($medal_label) . '">🏆 </span>'; 
                                    } elseif ($rank === 7) { 
                                        $medal_label = __('leaderboard.medal_star'); 
                                        echo '<span aria-hidden="true" title="' . htmlspecialchars($medal_label) . '">🌟 </span>'; 
                                    } elseif ($rank === 8) { 
                                        $medal_label = __('leaderboard.medal_military'); 
                                        echo '<span aria-hidden="true" title="' . htmlspecialchars($medal_label) . '">🏅 </span>'; 
                                    } elseif ($rank === 9) { 
                                        $medal_label = __('leaderboard.medal_glowing'); 
                                        echo '<span aria-hidden="true" title="' . htmlspecialchars($medal_label) . '">✨ </span>'; 
                                    } elseif ($rank === 10) { 
                                        $medal_label = __('leaderboard.medal_crown'); 
                                        echo '<span aria-hidden="true" title="' . htmlspecialchars($medal_label) . '">👑 </span>'; 
                                    }

                                    if ($medal_label !== '') {
                                        echo '<span class="sr-only" style="position:absolute; width:1px; height:1px; padding:0; margin:-1px; overflow:hidden; clip:rect(0,0,0,0); white-space:nowrap; border:0;">' . htmlspecialchars($medal_label) . ': </span>';
                                    }

                                    echo $rank;
                                ?>
                            </td>
                            <td style="padding:0.75rem;">
                                <?php echo htmlspecialchars($u['display_name']); ?>
                                <?php if ($is_current): ?>
                                    <span style="font-size:0.8rem;color:var(--primary-color,#007bff);margin-left:0.5rem;"><?php echo htmlspecialchars(__('leaderboard.you_badge')); ?></span>
                                <?php endif; ?>
                            </td>
                            <td style="padding:0.75rem;text-transform:capitalize;"><?php echo htmlspecialchars($u['role'] ?? __('leaderboard.default_role')); ?></td>
                            <td style="padding:0.75rem;text-align:right;">⭐ <?php echo intval($u['points']); ?></td>
                        </tr>
                    <?php $rank++; endforeach; ?>
                <?php endif; ?>
            </tbody>
        </table>
    </div>
</div>

<?php require_once 'partials/footer.php'; ?>
