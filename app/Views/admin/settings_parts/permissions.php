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
                        $la = function_exists('permission_display_label') ? permission_display_label($ka) : $ka;
                        $lb = function_exists('permission_display_label') ? permission_display_label($kb) : $kb;
                        return strcasecmp((string) $la, (string) $lb);
                    });
                    $rolesSorted = is_array($rolesList) ? $rolesList : [];
                    usort($rolesSorted, static function (array $a, array $b): int {
                        $na = isset($a['role_name']) && is_string($a['role_name']) ? $a['role_name'] : '';
                        $nb = isset($b['role_name']) && is_string($b['role_name']) ? $b['role_name'] : '';
                        $da = function_exists('role_display_name') ? role_display_name($na) : $na;
                        $db = function_exists('role_display_name') ? role_display_name($nb) : $nb;
                        return strcasecmp((string) $da, (string) $db);
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
                                <table class="table table-hover align-middle mb-0 perm-role-table" role="table">
                                    <thead class="table-light">
                                        <tr>
                                            <th scope="col" style="width: 20%;" class="py-2"><?= htmlspecialchars($st('settings.th_role', 'Role'), ENT_QUOTES, 'UTF-8') ?></th>
                                            <th scope="col" style="width: 80%;" class="py-2"><?= htmlspecialchars($st('settings.th_capabilities', 'Capabilities'), ENT_QUOTES, 'UTF-8') ?></th>
                                        </tr>
                                    </thead>
                                    <tbody>
                                        <?php foreach ($rolesSorted as $r): ?>
                                            <?php
                                                $rId = isset($r['id']) ? (int) $r['id'] : 0;
                                                $rName = isset($r['role_name']) && is_string($r['role_name']) ? $r['role_name'] : '';
                                            ?>
                                            <tr class="align-top">
                                                <td class="fw-bold text-capitalize align-top">
                                                    <div class="d-flex flex-column gap-1">
                                                        <span title="<?= htmlspecialchars($rName, ENT_QUOTES, 'UTF-8') ?>"><?= htmlspecialchars(function_exists('role_display_name') ? role_display_name($rName) : $rName, ENT_QUOTES, 'UTF-8') ?></span>
                                                        <?php if ($rId > 4): ?>
                                                            <button type="submit"
                                                                    form="delete-role-form-<?= $rId ?>"
                                                                    class="btn btn-sm btn-danger py-0 px-2 align-self-center mx-auto"
                                                                    style="font-size: 0.75rem;">
                                                                <?= htmlspecialchars($st('settings.delete_btn', 'Delete'), ENT_QUOTES, 'UTF-8') ?>
                                                            </button>
                                                        <?php endif; ?>
                                                    </div>
                                                </td>
                                                <td>
                                                    <div class="perm-grid">
                                                        <?php foreach ($catPerms as $p): ?>
                                                            <?php
                                                                $pId = isset($p['id']) ? (int) $p['id'] : 0;
                                                                $pkey = isset($p['permission_key']) && is_string($p['permission_key']) ? $p['permission_key'] : '';
                                                                $pDesc = isset($p['description']) && is_string($p['description']) ? $p['description'] : '';
                                                                $pLabel = function_exists('permission_display_label') ? permission_display_label($pkey) : ucwords(str_replace('_', ' ', $pkey));
                                                                if (function_exists('prd_title_case')) {
                                                                    $pLabel = prd_title_case((string) $pLabel);
                                                                }
                                                                $isChecked = isset($activeMappings[$rId][$pId]);
                                                                $isLockedAdmin = ($rId === 1 && $isChecked);
                                                                $isGuestRole = function_exists('is_guest_role_name') && is_guest_role_name($rName);
                                                                $guestOk = !$isGuestRole || (function_exists('guest_may_hold_permission') && guest_may_hold_permission($pkey));
                                                                if (!$guestOk) {
                                                                    continue;
                                                                }
                                                                $parentKey = function_exists('permission_parent_key') ? permission_parent_key($pkey) : null;
                                                                $needsHint = $parentKey !== null
                                                                    ? ($st('settings.perm_needs', 'Requires') . ': ' . ucwords(str_replace('_', ' ', $parentKey)) . ' permission.')
                                                                    : '';
                                                                if ($pkey === 'export_data') {
                                                                    $needsHint = $st('settings.perm_export_needs_view', 'Requires permission to view the table being exported.');
                                                                }
                                                                $infoText = trim($pDesc . ($needsHint !== '' ? ' — ' . $needsHint : ''));
                                                                if ($infoText === '') {
                                                                    $infoText = $pLabel;
                                                                }
                                                            ?>
                                                            <span class="d-inline-flex flex-wrap align-items-center gap-1 bg-light border rounded px-2 py-1 mb-0 perm-chip">
                                                                <label class="d-inline-flex align-items-center gap-2 mb-0" style="white-space: nowrap;">
                                                                    <input type="checkbox"
                                                                           name="permissions[<?= $rId ?>][<?= $pId ?>]"
                                                                           value="1"
                                                                           class="form-check-input m-0 flex-shrink-0 js-perm"
                                                                           style="float: none; position: static;"
                                                                           data-role="<?= $rId ?>"
                                                                           data-key="<?= htmlspecialchars($pkey, ENT_QUOTES, 'UTF-8') ?>"
                                                                           <?= $parentKey ? 'data-requires="' . htmlspecialchars($parentKey, ENT_QUOTES, 'UTF-8') . '"' : '' ?>
                                                                           <?= $isChecked ? 'checked' : '' ?>
                                                                           <?= $isLockedAdmin ? 'disabled' : '' ?>>
                                                                    <?php if ($isLockedAdmin): ?>
                                                                        <input type="hidden" name="permissions[<?= $rId ?>][<?= $pId ?>]" value="1">
                                                                    <?php endif; ?>
                                                                    <span class="small"><?= htmlspecialchars($pLabel, ENT_QUOTES, 'UTF-8') ?></span>
                                                                </label>
                                                                <button type="button"
                                                                        class="btn btn-sm btn-outline-secondary rounded-circle p-0 js-perm-info"
                                                                        style="width:1.35rem;height:1.35rem;line-height:1.2;font-size:0.75rem;"
                                                                        aria-expanded="false"
                                                                        aria-label="<?= htmlspecialchars($st('settings.perm_more_info', 'More about') . ' ' . $pLabel, ENT_QUOTES, 'UTF-8') ?>"><?= htmlspecialchars($st('settings.perm_info_mark', 'i'), ENT_QUOTES, 'UTF-8') ?></button>
                                                                <span class="perm-hint d-none small text-muted mt-1 w-100" role="note"></span>
                                                            </span>
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

        <p class="small text-muted mt-3 mb-0" id="perm-save-status" aria-live="polite"></p>
    </form>
    <script>
    (function () {
        var form = document.querySelector('#panel-permissions form[action*="permissions/save"]');
        var statusEl = document.getElementById('perm-save-status');
        var savedWord = <?= json_encode($st('settings.perm_saved', 'Saved')) ?>;
        function persistPerms() {
            if (!form) return;
            var data = new FormData(form);
            fetch(form.action, {
                method: 'POST',
                body: data,
                credentials: 'same-origin',
                headers: { 'X-Requested-With': 'XMLHttpRequest' }
            }).then(function (r) { return r.json(); }).then(function (j) {
                if (statusEl) {
                    statusEl.textContent = savedWord;
                    statusEl.className = 'small text-success mt-3 mb-0';
                }
            }).catch(function () {
                if (statusEl) {
                    statusEl.textContent = <?= json_encode($st('settings.perm_save_failed', 'Could not save just then.')) ?>;
                    statusEl.className = 'small text-danger mt-3 mb-0';
                }
            });
        }
        if (form) {
            form.addEventListener('change', function (e) {
                if (e.target && e.target.classList && e.target.classList.contains('js-perm')) {
                    persistPerms();
                }
            });
        }
        var needsWord = <?= json_encode($st('settings.perm_needs', 'Requires')) ?>;
        var usedByWord = <?= json_encode($st('settings.perm_used_by', 'Required by')) ?>;
        var lockedWord = <?= json_encode($st('settings.perm_locked', 'This permission is mandatory for Administrators.')) ?>;
        var offWord = <?= json_encode($st('settings.perm_needs_parent', 'Please turn that on first.')) ?>;

        function box(role, key) {
            return document.querySelector('.js-perm[data-role="' + role + '"][data-key="' + key + '"]');
        }
        function labelFor(cb) {
            var span = cb.closest('label');
            if (!span) return cb.getAttribute('data-key');
            var t = span.querySelector('span.small');
            return t ? t.textContent.trim() : cb.getAttribute('data-key');
        }
        function childrenOf(role, key) {
            return Array.prototype.slice.call(document.querySelectorAll('.js-perm[data-role="' + role + '"][data-requires="' + key + '"]'));
        }
        function syncRole(role) {
            document.querySelectorAll('.js-perm[data-role="' + role + '"][data-requires]').forEach(function (child) {
                if (child.dataset.locked === '1') return;
                var parent = box(role, child.getAttribute('data-requires'));
                if (!parent) return;
                var ok = parent.checked;
                child.disabled = !ok;
                if (!ok) child.checked = false;
                var lab = child.closest('label');
                if (lab) lab.classList.toggle('text-muted', !ok);
            });
        }
        function hintText(cb) {
            var parts = [];
            var req = cb.getAttribute('data-requires');
            var role = cb.getAttribute('data-role');
            if (cb.disabled && cb.dataset.locked === '1') parts.push(lockedWord);
            if (cb.getAttribute('data-key') === 'export_data') {
                parts.push(<?= json_encode($st('settings.perm_export_needs_view', 'Requires permission to view the table being exported.')) ?>);
            }
            if (req) {
                var parent = box(role, req);
                var pname = parent ? labelFor(parent) : req;
                var line = needsWord + ': ' + pname + ' permission.';
                if (!parent || !parent.checked) line += ' ' + offWord;
                parts.push(line);
            }
            var kids = childrenOf(role, cb.getAttribute('data-key')).map(labelFor);
            if (kids.length) parts.push(usedByWord + ': ' + kids.join(', ') + '.');
            return parts.join('. ');
        }
        document.querySelectorAll('.js-perm').forEach(function (cb) {
            if (cb.disabled && cb.nextElementSibling && cb.nextElementSibling.type === 'hidden') {
                cb.dataset.locked = '1';
            }
            cb.addEventListener('change', function () {
                var role = cb.getAttribute('data-role');
                if (cb.checked && cb.getAttribute('data-requires')) {
                    var parent = box(role, cb.getAttribute('data-requires'));
                    if (parent && !parent.disabled) parent.checked = true;
                }
                syncRole(role);
            });
        });
        document.querySelectorAll('.js-perm-info').forEach(function (btn) {
            btn.addEventListener('click', function (e) {
                e.preventDefault();
                var chip = btn.closest('.perm-chip');
                var cb = chip ? chip.querySelector('.js-perm') : null;
                var hint = chip ? chip.querySelector('.perm-hint') : null;
                if (!hint || !cb) return;
                var open = !hint.classList.contains('d-none');
                document.querySelectorAll('.perm-hint').forEach(function (h) { h.classList.add('d-none'); h.textContent = ''; });
                document.querySelectorAll('.js-perm-info').forEach(function (b) { b.setAttribute('aria-expanded', 'false'); });
                if (open) return;
                hint.textContent = hintText(cb) || <?= json_encode($st('settings.perm_no_links', 'This permission does not depend on another one.')) ?>;
                hint.classList.remove('d-none');
                btn.setAttribute('aria-expanded', 'true');
            });
        });
        var roles = {};
        document.querySelectorAll('.js-perm').forEach(function (cb) { roles[cb.getAttribute('data-role')] = true; });
        Object.keys(roles).forEach(syncRole);
    })();
    </script>
</div>
