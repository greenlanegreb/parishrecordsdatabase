<?php
declare(strict_types=1);
?>
<!-- TAB 2: Modules Management -->
<div class="tab-pane fade" id="panel-modules" role="tabpanel" aria-labelledby="tab-modules">
    <div class="card shadow-sm border-0 p-4">
        <form method="POST" action="<?= $basePath ?>/admin/modules">
            <?= csrf_field() ?>
            <h4 class="h5 fw-bold text-dark mb-1"><?= htmlspecialchars(__('settings.modules_heading'), ENT_QUOTES, 'UTF-8') ?></h4>
            <p class="text-muted small mb-4"><?= htmlspecialchars(__('settings.modules_subheading'), ENT_QUOTES, 'UTF-8') ?></p>
            
            <div class="d-flex flex-column gap-3 mb-4">
                <div class="card bg-light border-0 p-3">
                    <div class="form-check">
                        <input type="checkbox" name="module_users_enabled" id="module_users_enabled" value="1" <?= ($modUsersVal === '1') ? 'checked' : '' ?> onchange="handleUserManagementToggle(this);" class="form-check-input fs-5">
                        <label for="module_users_enabled" class="form-check-label fw-bold text-dark"><?= htmlspecialchars(__('settings.mod_users'), ENT_QUOTES, 'UTF-8') ?></label>
                    </div>
                    <p class="text-muted small mb-0 mt-1 ms-4"><?= htmlspecialchars(__('settings.mod_users_desc'), ENT_QUOTES, 'UTF-8') ?></p>
                </div>

                <div class="card bg-light border-0 p-3">
                    <div class="form-check">
                        <input type="checkbox" name="module_leaderboard_enabled" id="module_leaderboard_enabled" value="1" <?= ($modLeaderboardVal === '1' && $modUsersVal === '1') ? 'checked' : '' ?> <?= ($modUsersVal !== '1') ? 'disabled' : '' ?> class="form-check-input fs-5">
                        <label for="module_leaderboard_enabled" class="form-check-label fw-bold text-dark"><?= htmlspecialchars(__('settings.mod_leaderboard'), ENT_QUOTES, 'UTF-8') ?></label>
                    </div>
                    <p class="text-muted small mb-0 mt-1 ms-4">
                        <?= htmlspecialchars(__('settings.mod_leaderboard_desc'), ENT_QUOTES, 'UTF-8') ?> 
                        <span id="leaderboard_dependency_note" class="text-danger fw-bold" style="display: <?= ($modUsersVal !== '1') ? 'inline' : 'none' ?>;"><?= htmlspecialchars(__('settings.mod_leaderboard_note'), ENT_QUOTES, 'UTF-8') ?></span>
                    </p>
                </div>

                <div class="card bg-light border-0 p-3">
                    <div class="form-check">
                        <input type="checkbox" name="module_moderation_enabled" id="module_moderation_enabled" value="1" <?= ($modModerationVal === '1') ? 'checked' : '' ?> class="form-check-input fs-5">
                        <label for="module_moderation_enabled" class="form-check-label fw-bold text-dark"><?= htmlspecialchars(__('settings.mod_moderation'), ENT_QUOTES, 'UTF-8') ?></label>
                    </div>
                    <p class="text-muted small mb-0 mt-1 ms-4"><?= htmlspecialchars(__('settings.mod_moderation_desc'), ENT_QUOTES, 'UTF-8') ?></p>
                </div>

                <div class="card bg-light border-0 p-3">
                    <div class="form-check">
                        <input type="checkbox" name="module_volunteers_enabled" id="module_volunteers_enabled" value="1" <?= ($modVolunteersVal === '1') ? 'checked' : '' ?> class="form-check-input fs-5">
                        <label for="module_volunteers_enabled" class="form-check-label fw-bold text-dark"><?= htmlspecialchars(__('settings.mod_volunteers'), ENT_QUOTES, 'UTF-8') ?></label>
                    </div>
                    <p class="text-muted small mb-0 mt-1 ms-4"><?= htmlspecialchars(__('settings.mod_volunteers_desc'), ENT_QUOTES, 'UTF-8') ?></p>
                </div>

                <div class="card bg-light border-0 p-3">
                    <div class="form-check">
                        <input type="checkbox" name="module_feedback_enabled" id="module_feedback_enabled" value="1" <?= ($modFeedbackVal === '1') ? 'checked' : '' ?> class="form-check-input fs-5">
                        <label for="module_feedback_enabled" class="form-check-label fw-bold text-dark"><?= htmlspecialchars(__('settings.mod_feedback'), ENT_QUOTES, 'UTF-8') ?></label>
                    </div>
                    <p class="text-muted small mb-0 mt-1 ms-4"><?= htmlspecialchars(__('settings.mod_feedback_desc'), ENT_QUOTES, 'UTF-8') ?></p>
                </div>
            </div>

            <button type="submit" class="btn btn-primary"><?= htmlspecialchars(__('settings.save_modules_btn'), ENT_QUOTES, 'UTF-8') ?></button>
        </form>
    </div>
</div>
