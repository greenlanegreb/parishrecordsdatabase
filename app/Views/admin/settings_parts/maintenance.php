<?php
declare(strict_types=1);
?>
<!-- TAB 3: Maintenance Mode & Cron Settings -->
<div class="tab-pane fade" id="panel-maintenance" role="tabpanel" aria-labelledby="tab-maintenance">
    <div class="card shadow-sm border-0 p-4 mb-4">
        <form method="POST" action="<?= $basePath ?>/admin/maintenance/save">
            <?= csrf_field() ?>
            <h4 class="h5 fw-bold text-dark mb-3"><?= htmlspecialchars(__('settings.maintenance_heading'), ENT_QUOTES, 'UTF-8') ?></h4>
            
            <div class="mb-3 form-check">
                <input type="checkbox" name="maintenance_mode" id="maintenance_mode" value="1" <?= ($maintenanceMode === '1') ? 'checked' : '' ?> class="form-check-input fs-5">
                <label for="maintenance_mode" class="form-check-label fw-bold text-dark"><?= htmlspecialchars(__('settings.maintenance_toggle'), ENT_QUOTES, 'UTF-8') ?></label>
            </div>

            <div class="mb-3">
                <label for="maintenance_reason" class="form-label fw-bold"><?= htmlspecialchars(__('settings.maintenance_reason_label'), ENT_QUOTES, 'UTF-8') ?></label>
                <textarea id="maintenance_reason" name="maintenance_reason" rows="3" required class="form-control"><?= htmlspecialchars($maintenanceReason, ENT_QUOTES, 'UTF-8') ?></textarea>
            </div>

            <div class="mb-4">
                <label for="maintenance_eta" class="form-label fw-bold"><?= htmlspecialchars(__('settings.maintenance_eta_label'), ENT_QUOTES, 'UTF-8') ?></label>
                <input type="text" id="maintenance_eta" name="maintenance_eta" value="<?= htmlspecialchars($maintenanceEta, ENT_QUOTES, 'UTF-8') ?>" required class="form-control max-width-350">
            </div>

            <button type="submit" class="btn btn-danger"><?= htmlspecialchars(__('settings.save_maintenance_btn'), ENT_QUOTES, 'UTF-8') ?></button>
        </form>
    </div>

    <!-- Intelligent Cron Discovery & Token Maintenance Tool -->
    <div class="card shadow-sm border-0 bg-light p-4">
        <h4 class="h5 fw-bold text-dark mb-2"><?= htmlspecialchars(__('settings.cron_maintenance_heading'), ENT_QUOTES, 'UTF-8') ?></h4>
        <p class="text-muted small mb-3"><?= htmlspecialchars(__('settings.cron_maintenance_desc'), ENT_QUOTES, 'UTF-8') ?></p>
        
        <div class="mb-3">
            <label class="form-label small fw-bold"><?= htmlspecialchars(__('settings.cron_command_label'), ENT_QUOTES, 'UTF-8') ?></label>
            <input type="text" readonly value="<?= htmlspecialchars(PHP_BINARY, ENT_QUOTES, 'UTF-8') ?> <?= htmlspecialchars(realpath(__DIR__ . '/../../../db/actions/cron_token_cleanup.php') ?: '', ENT_QUOTES, 'UTF-8') ?>" class="form-control font-monospace bg-white" onclick="this.select();">
        </div>

        <form method="POST" action="<?= $basePath ?>/admin/cron/token-cleanup">
            <?= csrf_field() ?>
            <button type="submit" class="btn btn-sm btn-outline-secondary"><?= htmlspecialchars(__('settings.run_token_cleanup_btn'), ENT_QUOTES, 'UTF-8') ?></button>
        </form>
    </div>
</div>
