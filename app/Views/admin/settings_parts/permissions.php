<?php
declare(strict_types=1);
?>
<!-- TAB 5: Roles & Permissions Matrix -->
<div class="tab-pane fade" id="panel-permissions" role="tabpanel" aria-labelledby="tab-permissions">
    <h4 class="h5 fw-bold text-dark mb-1"><?= htmlspecialchars(__('settings.permissions_heading'), ENT_QUOTES, 'UTF-8') ?></h4>
    <p class="text-muted small mb-4"><?= htmlspecialchars(__('settings.permissions_subheading'), ENT_QUOTES, 'UTF-8') ?></p>

    <!-- Role Creation Form Card -->
    <div class="card shadow-sm border-0 bg-light p-4 mb-4">
        <h5 class="h6 fw-bold text-dark mb-3"><?= htmlspecialchars(__('settings.create_role_heading'), ENT_QUOTES, 'UTF-8') ?></h5>
        <form method="POST" action="<?= $basePath ?>/admin/roles/save" class="row g-3 align-items-end">
            <?= csrf_field() ?>
            <div class="col-md-8">
                <label for="role_name" class="form-label small fw-bold"><?= htmlspecialchars(__('settings.role_name_label'), ENT_QUOTES, 'UTF-8') ?></label>
                <input type="text" id="role_name" name="role_name" placeholder="e.g. archivist" required class="form-control">
            </div>
            <div class="col-md-4">
                <button type="submit" class="btn btn-primary w-100"><?= htmlspecialchars(__('settings.create_role_btn'), ENT_QUOTES, 'UTF-8') ?></button>
            </div>
        </form>
    </div>

    <form method="POST" action="<?= $basePath ?>/admin/permissions/save">
        <?= csrf_field() ?>
        <div class="d-flex flex-column gap-3">
            <?php foreach ($categorizedPerms as $categoryName => $catPerms): ?>
                <div class="card shadow-sm border-0">
                    <div class="card-body">
                        <details>
                            <summary class="fw-bold fs-6 text-primary" style="cursor: pointer;">
                                <?= htmlspecialchars($categoryName, ENT_QUOTES, 'UTF-8') ?> <span class="fw-normal text-muted small">(<?= count($catPerms) ?> permissions)</span>
                            </summary>
                            <div class="mt-3 pt-3 border-top table-responsive">
                                <table class="table table-hover align-middle mb-0" role="table">
                                    <thead class="table-light">
                                        <tr>
                                            <th scope="col" style="width: 25%;" class="py-2"><?= htmlspecialchars(__('settings.th_role'), ENT_QUOTES, 'UTF-8') ?></th>
                                            <th scope="col" style="width: 75%;" class="py-2"><?= htmlspecialchars(__('settings.th_capabilities'), ENT_QUOTES, 'UTF-8') ?></th>
                                        </tr>
                                    </thead>
                                    <tbody>
                                        <?php foreach ($rolesList as $r): ?>
                                            <?php 
                                                $rId = isset($r['id']) ? (int)$r['id'] : 0;
                                                $rName = isset($r['role_name']) && is_string($r['role_name']) ? $r['role_name'] : '';
                                            ?>
                                            <tr>
                                                <td class="fw-bold text-capitalize align-top">
                                                    <div class="d-flex flex-column gap-1">
                                                        <span><?= htmlspecialchars($rName, ENT_QUOTES, 'UTF-8') ?></span>
                                                        <?php if ($rId > 4): ?>
                                                            <form method="POST" action="<?= $basePath ?>/admin/roles/save" onsubmit="return confirm('<?= htmlspecialchars(__('settings.delete_role_confirm'), ENT_QUOTES, 'UTF-8') ?>');" class="d-inline">
                                                                <?= csrf_field() ?>
                                                                <input type="hidden" name="delete_role_id" value="<?= $rId ?>">
                                                                <button type="submit" class="btn btn-sm btn-danger py-0 px-2" style="font-size: 0.75rem;">Delete</button>
                                                            </form>
                                                        <?php endif; ?>
                                                    </div>
                                                </td>
                                                <td>
                                                    <div class="d-flex flex-wrap gap-2">
                                                        <?php foreach ($catPerms as $p): ?>
                                                            <?php 
                                                                $pId = isset($p['id']) ? (int)$p['id'] : 0;
                                                                $pkey = isset($p['permission_key']) && is_string($p['permission_key']) ? $p['permission_key'] : '';
                                                                $pDesc = isset($p['description']) && is_string($p['description']) ? $p['description'] : '';
                                                                
                                                                $isChecked = isset($activeMappings[$rId][$pId]);
                                                                $isLockedAdmin = ($rId === 1 && $isChecked);
                                                            ?>
                                                            <div class="form-check bg-light border rounded px-2 py-1 m-0" title="<?= htmlspecialchars($pDesc, ENT_QUOTES, 'UTF-8') ?>">
                                                                <input type="checkbox" name="permissions[<?= $rId ?>][<?= $pId ?>]" value="1" <?= $isChecked ? 'checked' : '' ?> <?= $isLockedAdmin ? 'disabled' : '' ?> class="form-check-input">
                                                                <?php if ($isLockedAdmin): ?>
                                                                    <input type="hidden" name="permissions[<?= $rId ?>][<?= $pId ?>]" value="1">
                                                                <?php endif; ?>
                                                                <label class="form-check-label small font-monospace"><?= htmlspecialchars($pkey, ENT_QUOTES, 'UTF-8') ?></label>
                                                            </div>
                                                        <?php endforeach; ?>
                                                    </div>
                                                </td>
                                            </tr>
                                        <?php endforeach; ?>
                                    </tbody>
                                </table>
                            </div>
                        </details>
                    </div>
                </div>
            <?php endforeach; ?>
        </div>

        <div class="mt-4">
            <button type="submit" class="btn btn-primary"><?= htmlspecialchars(__('settings.save_permissions_btn'), ENT_QUOTES, 'UTF-8') ?></button>
        </div>
    </form>
</div>
