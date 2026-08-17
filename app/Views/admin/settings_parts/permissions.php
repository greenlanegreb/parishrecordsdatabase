<?php
declare(strict_types=1);

/**
 * Roles & permissions matrix.
 * Delete-role forms sit outside the permissions form (no nested <form>);
 * buttons use the HTML form="" attribute to target them.
 */
$bp = isset($basePath) && is_string($basePath) ? rtrim($basePath, '/') : '';
$st = static function (string $key, string $fallback): string {
    $t = __($key);
    return ($t !== $key && $t !== '') ? $t : $fallback;
};
?>

<!-- TAB 5: Roles & Permissions Matrix -->
<div class="tab-pane fade" id="panel-permissions" role="tabpanel" aria-labelledby="tab-permissions">
    <h4 class="h5 fw-bold text-dark mb-1"><?= htmlspecialchars($st('settings.permissions_heading', 'Roles & Permissions'), ENT_QUOTES, 'UTF-8') ?></h4>
    <p class="text-muted small mb-4"><?= htmlspecialchars($st('settings.permissions_subheading', 'Configure role permissions and access controls across the system.'), ENT_QUOTES, 'UTF-8') ?></p>

    <!-- Role Creation Form Card -->
    <div class="card shadow-sm border-0 bg-light p-4 mb-4">
        <h5 class="h6 fw-bold text-dark mb-3"><?= htmlspecialchars($st('settings.create_role_heading', 'Create new role'), ENT_QUOTES, 'UTF-8') ?></h5>
        <form method="POST" action="<?= htmlspecialchars($bp, ENT_QUOTES, 'UTF-8') ?>/admin/roles/save" class="row g-3 align-items-end">
            <?= csrf_field() ?>
            <div class="col-md-8">
                <label for="role_name" class="form-label small fw-bold"><?= htmlspecialchars($st('settings.role_name_label', 'Role name'), ENT_QUOTES, 'UTF-8') ?></label>
                <input type="text" id="role_name" name="role_name" placeholder="e.g. archivist" required class="form-control">
            </div>
            <div class="col-md-4">
                <button type="submit" class="btn btn-primary w-100"><?= htmlspecialchars($st('settings.create_role_btn', 'Create role'), ENT_QUOTES, 'UTF-8') ?></button>
            </div>
        </form>
    </div>

    <?php
    // Hidden delete-role forms (siblings of the permissions form — never nested)
    if (!empty($rolesList) && is_array($rolesList)):
        foreach ($rolesList as $r):
            $rId = isset($r['id']) ? (int) $r['id'] : 0;
            if ($rId <= 4) {
                continue;
            }
            ?>
            <form method="POST"
                  action="<?= htmlspecialchars($bp, ENT_QUOTES, 'UTF-8') ?>/admin/roles/save"
                  id="delete-role-form-<?= $rId ?>"
                  class="d-none"
                  onsubmit="return confirm('<?= htmlspecialchars($st('settings.delete_role_confirm', 'Are you sure you want to delete this role?'), ENT_QUOTES, 'UTF-8') ?>');">
                <?= csrf_field() ?>
                <input type="hidden" name="delete_role_id" value="<?= $rId ?>">
            </form>
            <?php
        endforeach;
    endif;
    ?>

    <form method="POST" action="<?= htmlspecialchars($bp, ENT_QUOTES, 'UTF-8') ?>/admin/permissions/save">
        <?= csrf_field() ?>
        <div class="d-flex flex-column gap-3">
            <?php foreach ($categorizedPerms as $categoryName => $catPerms): ?>
                <?php
                    if (!is_array($catPerms)) {
                        continue;
                    }
                    usort($catPerms, static function (array $a, array $b): int {
                        $ka = isset($a['permission_key']) && is_string($a['permission_key']) ? $a['permission_key'] : '';
                        $kb = isset($b['permission_key']) && is_string($b['permission_key']) ? $b['permission_key'] : '';
                        return strcasecmp($ka, $kb);
                    });
                ?>
                <div class="card shadow-sm border-0">
                    <div class="card-body">
                        <details>
                            <summary class="fw-bold fs-6 text-primary" style="cursor: pointer;">
                                <?= htmlspecialchars((string) $categoryName, ENT_QUOTES, 'UTF-8') ?>
                                <span class="fw-normal text-muted small">(<?= count($catPerms) ?> permissions)</span>
                            </summary>
                            <div class="mt-3 pt-3 border-top table-responsive">
                                <table class="table table-hover align-middle mb-0" role="table">
                                    <thead class="table-light">
                                        <tr>
                                            <th scope="col" style="width: 20%;" class="py-2"><?= htmlspecialchars($st('settings.th_role', 'Role'), ENT_QUOTES, 'UTF-8') ?></th>
                                            <th scope="col" style="width: 80%;" class="py-2"><?= htmlspecialchars($st('settings.th_capabilities', 'Capabilities'), ENT_QUOTES, 'UTF-8') ?></th>
                                        </tr>
                                    </thead>
                                    <tbody>
                                        <?php foreach ($rolesList as $r): ?>
                                            <?php
                                                $rId = isset($r['id']) ? (int) $r['id'] : 0;
                                                $rName = isset($r['role_name']) && is_string($r['role_name']) ? $r['role_name'] : '';
                                            ?>
                                            <tr>
                                                <td class="fw-bold text-capitalize align-top">
                                                    <div class="d-flex flex-column gap-1">
                                                        <span><?= htmlspecialchars($rName, ENT_QUOTES, 'UTF-8') ?></span>
                                                        <?php if ($rId > 4): ?>
                                                            <button type="submit"
                                                                    form="delete-role-form-<?= $rId ?>"
                                                                    class="btn btn-sm btn-danger py-0 px-2 align-self-start"
                                                                    style="font-size: 0.75rem;">
                                                                <?= htmlspecialchars($st('settings.delete_btn', 'Delete'), ENT_QUOTES, 'UTF-8') ?>
                                                            </button>
                                                        <?php endif; ?>
                                                    </div>
                                                </td>
                                                <td>
                                                    <div class="d-flex flex-wrap gap-2 align-items-center">
                                                        <?php foreach ($catPerms as $p): ?>
                                                            <?php
                                                                $pId = isset($p['id']) ? (int) $p['id'] : 0;
                                                                $pkey = isset($p['permission_key']) && is_string($p['permission_key']) ? $p['permission_key'] : '';
                                                                $pDesc = isset($p['description']) && is_string($p['description']) ? $p['description'] : '';
                                                                $pLabel = ucwords(str_replace('_', ' ', $pkey));
                                                                $isChecked = isset($activeMappings[$rId][$pId]);
                                                                $isLockedAdmin = ($rId === 1 && $isChecked);
                                                            ?>
                                                            <label class="d-inline-flex align-items-center gap-2 bg-light border rounded px-2 py-1 mb-0"
                                                                   style="white-space: nowrap;"
                                                                   title="<?= htmlspecialchars($pDesc !== '' ? $pDesc : $pkey, ENT_QUOTES, 'UTF-8') ?>">
                                                                <input type="checkbox"
                                                                       name="permissions[<?= $rId ?>][<?= $pId ?>]"
                                                                       value="1"
                                                                       class="form-check-input m-0 flex-shrink-0"
                                                                       style="float: none; position: static;"
                                                                       <?= $isChecked ? 'checked' : '' ?>
                                                                       <?= $isLockedAdmin ? 'disabled' : '' ?>>
                                                                <?php if ($isLockedAdmin): ?>
                                                                    <input type="hidden" name="permissions[<?= $rId ?>][<?= $pId ?>]" value="1">
                                                                <?php endif; ?>
                                                                <span class="small"><?= htmlspecialchars($pLabel, ENT_QUOTES, 'UTF-8') ?></span>
                                                            </label>
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
            <button type="submit" class="btn btn-primary"><?= htmlspecialchars($st('settings.save_permissions_btn', 'Save permissions'), ENT_QUOTES, 'UTF-8') ?></button>
        </div>
    </form>
</div>
