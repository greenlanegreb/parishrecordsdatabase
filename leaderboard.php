<?php
// leaderboard.php - Volunteer Contribution Leaderboard (Publicly Accessible)
require_once 'db/db.php';
require_once 'db/auth_helpers.php';
session_start();

// Optional user session lookup for public page (won't block guests)
$current_user = (function_exists('get_current_user_data') && isset($pdo)) ? get_current_user_data($pdo) : null;
$is_logged_in = ($current_user !== null || isset($_SESSION['user_id']));

// Fetch all active users ordered by points descending
$stmt = $pdo->prepare("SELECT username, first_name, surname, points, role, leaderboard_display_mode FROM users WHERE is_active = 1 ORDER BY points DESC, username ASC LIMIT 50");
$stmt->execute();
$all_users = $stmt->fetchAll(PDO::FETCH_ASSOC);

// Process visibility and privacy rules (Defaulting to anonymous/initials)
$leaderboard_users = [];
foreach ($all_users as $u) {
    $mode = $u['leaderboard_display_mode'] ?? '';
    if (!in_array($mode, ['initials_random', 'full_name', 'volunteers_only'])) {
        $mode = 'initials_random';
    }
    
    // If set to volunteers_only and the visitor is not logged in, hide them completely
    if ($mode === 'volunteers_only' && !$is_logged_in) {
        continue;
    }
    
    // Determine display name formatting based on user preference
    if ($mode === 'full_name') {
        $parts = [];
        if (!empty($u['first_name'])) {
            $parts[] = $u['first_name'];
        }
        if (!empty($u['surname'])) {
            $parts[] = $u['surname'];
        }
        $u['display_name'] = !empty($parts) ? implode(' ', $parts) : $u['username'];
    } else {
        // Default to initials and random number
        $f_initial = !empty($u['first_name']) ? strtoupper(mb_substr($u['first_name'], 0, 1)) : strtoupper(mb_substr($u['username'], 0, 1));
        $s_initial = !empty($u['surname']) ? strtoupper(mb_substr($u['surname'], 0, 1)) : '';
        $rand_num = abs(crc32($u['username'])) % 9000 + 1000;
        $u['display_name'] = "{$f_initial}{$s_initial}-{$rand_num}";
    }
    
    $leaderboard_users[] = $u;
}
?>

<?php require_once 'partials/header.php'; ?>

<div class="search-box-container" role="region" aria-label="Leaderboard View">
    <h3>Volunteer Contribution Leaderboard</h3>
    <p>Recognizing the efforts of our community members helping compile and transcribe parish records.</p>

    <div style="overflow-x: auto; margin-top: 1.5rem;">
        <table class="data-table" style="width: 100%; border-collapse: collapse; text-align: left;">
            <thead>
                <tr style="border-bottom: 2px solid var(--border-color);">
                    <th style="padding: 0.75rem;">Rank</th>
                    <th style="padding: 0.75rem;">Volunteer</th>
                    <th style="padding: 0.75rem;">Role</th>
                    <th style="padding: 0.75rem; text-align: right;">Score</th>
                </tr>
            </thead>
            <tbody>
                <?php if (empty($leaderboard_users)): ?>
                    <tr>
                        <td colspan="4" style="padding: 1rem; text-align: center;">No active users found on the leaderboard yet.</td>
                    </tr>
                <?php else: ?>
                    <?php $rank = 1; foreach ($leaderboard_users as $u): ?>
                        <?php 
                            $is_current = ($is_logged_in && isset($current_user['username']) && $u['username'] === $current_user['username']);
                            $row_style = $is_current ? 'background-color: rgba(0, 123, 255, 0.1); font-weight: bold;' : '';
                        ?>
                        <tr style="border-bottom: 1px solid var(--border-color); <?php echo $row_style; ?>">
                            <td style="padding: 0.75rem;">
                                <?php 
                                    if ($rank === 1) echo '🥇 ';
                                    elseif ($rank === 2) echo '🥈 ';
                                    elseif ($rank === 3) echo '🥉 ';
                                    echo $rank; 
                                ?>
                            </td>
                            <td style="padding: 0.75rem;">
                                <?php echo htmlspecialchars($u['display_name']); ?>
                                <?php if ($is_current): ?>
                                    <span style="font-size: 0.8rem; color: var(--primary-color, #007bff); margin-left: 0.5rem;">(You)</span>
                                <?php endif; ?>
                            </td>
                            <td style="padding: 0.75rem; text-transform: capitalize;"><?php echo htmlspecialchars($u['role']); ?></td>
                            <td style="padding: 0.75rem; text-align: right;">⭐ <?php echo intval($u['points']); ?></td>
                        </tr>
                    <?php $rank++; endforeach; ?>
                <?php endif; ?>
            </tbody>
        </table>
    </div>
</div>

<?php require_once 'partials/footer.php'; ?>
