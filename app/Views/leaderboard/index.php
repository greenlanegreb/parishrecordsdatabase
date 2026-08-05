<?php
/**
 * MIGRATED FILE MAPPING
 * ---------------------
 * Original Old File: roote/leaderboard.php
 * Migrated Date: 2026-08-05 06:46:32
 */declare(strict_types=1);

/**
 * @var array<int, array<string, mixed>> $leaderboardUsers
 * @var bool $isLoggedIn
 * @var array|null $currentUser
 */

require_once __DIR__ . '/../../partials/header.php';
?>

<div class="container my-4" role="region" aria-label="<?= htmlspecialchars(__('leaderboard.aria_region'), ENT_QUOTES, 'UTF-8') ?>">
    <div class="card border-0 shadow-sm p-4 bg-white">
        <h3 class="h4 fw-bold text-dark mb-1"><?= htmlspecialchars(__('leaderboard.heading'), ENT_QUOTES, 'UTF-8') ?></h3>
        <p class="text-muted small mb-4"><?= htmlspecialchars(__('leaderboard.subheading'), ENT_QUOTES, 'UTF-8') ?></p>

        <div class="table-responsive">
            <table class="table table-hover align-middle mb-0" role="table">
                <thead class="table-light">
                    <tr>
                        <th class="py-3 px-3" scope="col"><?= htmlspecialchars(__('leaderboard.th_rank'), ENT_QUOTES, 'UTF-8') ?></th>
                        <th class="py-3 px-3" scope="col"><?= htmlspecialchars(__('leaderboard.th_contributor'), ENT_QUOTES, 'UTF-8') ?></th>
                        <th class="py-3 px-3" scope="col"><?= htmlspecialchars(__('leaderboard.th_role'), ENT_QUOTES, 'UTF-8') ?></th>
                        <th class="py-3 px-3 text-end" scope="col"><?= htmlspecialchars(__('leaderboard.th_score'), ENT_QUOTES, 'UTF-8') ?></th>
                    </tr>
                </thead>
                <tbody>
                    <?php if (empty($leaderboardUsers)): ?>
                        <tr>
                            <td colspan="4" class="text-center py-4 text-muted"><?= htmlspecialchars(__('leaderboard.no_users'), ENT_QUOTES, 'UTF-8') ?></td>
                        </tr>
                    <?php else: ?>
                        <?php $rank = 1; foreach ($leaderboardUsers as $u): ?>
                            <?php
                                $uUsername = isset($u['username']) && is_string($u['username']) ? $u['username'] : '';
                                $currentUsername = ($currentUser !== null && isset($currentUser['username']) && is_string($currentUser['username'])) ? $currentUser['username'] : '';
                                $isCurrent = ($isLoggedIn && $uUsername !== '' && $uUsername === $currentUsername);
                                $rowClass = $isCurrent ? 'table-primary fw-bold' : '';
                            ?>
                            <tr class="<?= $rowClass ?>">
                                <td class="py-3 px-3">
                                    <?php
                                        $medalLabel = '';
                                        if ($rank === 1) { 
                                            $medalLabel = __('leaderboard.medal_gold'); 
                                            echo '<span aria-hidden="true" title="' . htmlspecialchars($medalLabel, ENT_QUOTES, 'UTF-8') . '">🥇 </span>'; 
                                        } elseif ($rank === 2) { 
                                            $medalLabel = __('leaderboard.medal_silver'); 
                                            echo '<span aria-hidden="true" title="' . htmlspecialchars($medalLabel, ENT_QUOTES, 'UTF-8') . '">🥈 </span>'; 
                                        } elseif ($rank === 3) { 
                                            $medalLabel = __('leaderboard.medal_bronze'); 
                                            echo '<span aria-hidden="true" title="' . htmlspecialchars($medalLabel, ENT_QUOTES, 'UTF-8') . '">🥉 </span>'; 
                                        } elseif ($rank === 4) { 
                                            $medalLabel = __('leaderboard.medal_ribbon'); 
                                            echo '<span aria-hidden="true" title="' . htmlspecialchars($medalLabel, ENT_QUOTES, 'UTF-8') . '">🎗️ </span>'; 
                                        } elseif ($rank === 5) { 
                                            $medalLabel = __('leaderboard.medal_rosette'); 
                                            echo '<span aria-hidden="true" title="' . htmlspecialchars($medalLabel, ENT_QUOTES, 'UTF-8') . '">🏵️ </span>'; 
                                        } elseif ($rank === 6) { 
                                            $medalLabel = __('leaderboard.medal_trophy'); 
                                            echo '<span aria-hidden="true" title="' . htmlspecialchars($medalLabel, ENT_QUOTES, 'UTF-8') . '">🏆 </span>'; 
                                        } elseif ($rank === 7) { 
                                            $medalLabel = __('leaderboard.medal_star'); 
                                            echo '<span aria-hidden="true" title="' . htmlspecialchars($medalLabel, ENT_QUOTES, 'UTF-8') . '">🌟 </span>'; 
                                        } elseif ($rank === 8) { 
                                            $medalLabel = __('leaderboard.medal_military'); 
                                            echo '<span aria-hidden="true" title="' . htmlspecialchars($medalLabel, ENT_QUOTES, 'UTF-8') . '">🏅 </span>'; 
                                        } elseif ($rank === 9) { 
                                            $medalLabel = __('leaderboard.medal_glowing'); 
                                            echo '<span aria-hidden="true" title="' . htmlspecialchars($medalLabel, ENT_QUOTES, 'UTF-8') . '">✨ </span>'; 
                                        } elseif ($rank === 10) { 
                                            $medalLabel = __('leaderboard.medal_crown'); 
                                            echo '<span aria-hidden="true" title="' . htmlspecialchars($medalLabel, ENT_QUOTES, 'UTF-8') . '">👑 </span>'; 
                                        }

                                        if ($medalLabel !== '') {
                                            echo '<span class="visually-hidden">' . htmlspecialchars($medalLabel, ENT_QUOTES, 'UTF-8') . ': </span>';
                                        }

                                        echo $rank;
                                    ?>
                                </td>
                                <td class="py-3 px-3">
                                    <?= htmlspecialchars(isset($u['display_name']) && is_string($u['display_name']) ? $u['display_name'] : '', ENT_QUOTES, 'UTF-8') ?>
                                    <?php if ($isCurrent): ?>
                                        <span class="badge bg-primary bg-opacity-10 text-primary ms-2 small"><?= htmlspecialchars(__('leaderboard.you_badge'), ENT_QUOTES, 'UTF-8') ?></span>
                                    <?php endif; ?>
                                </td>
                                <td class="py-3 px-3 text-capitalize"><?= htmlspecialchars(isset($u['role']) && is_string($u['role']) ? $u['role'] : __('leaderboard.default_role'), ENT_QUOTES, 'UTF-8') ?></td>
                                <td class="py-3 px-3 text-end fw-semibold">⭐ <?= isset($u['points']) ? (int)$u['points'] : 0 ?></td>
                            </tr>
                        <?php $rank++; endforeach; ?>
                    <?php endif; ?>
                </tbody>
            </table>
        </div>
    </div>
</div>

<?php require_once __DIR__ . '/../../partials/footer.php'; ?>
