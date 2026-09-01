<?php
declare(strict_types=1);
$__t = $__t ?? static function (string $key, string $fallback = ''): string {
    $v = function_exists('__') ? (string) __($key) : $key;
    return ($v !== $key && $v !== '') ? $v : ($fallback !== '' ? $fallback : $key);
};
$prdAppearSvc = (isset($pdo) && $pdo instanceof PDO) ? new \App\Services\AppearanceService($pdo) : null;
$appearanceRoles = $prdAppearSvc ? $prdAppearSvc->roles() : [];
$appearance = $appearance ?? ($prdAppearSvc ? $prdAppearSvc->load() : [
    'colors' => [], 'font' => 'system', 'type_scale' => 'md', 'strapline' => '', 'logo_file' => '', 'nav_order' => [], 'nav_custom' => [],
]);
$colorLabels = [
    'page_bg' => $__t('appearance.color_page_bg', 'Page background'),
    'text' => $__t('appearance.color_text', 'Main text'),
    'link' => $__t('appearance.color_link', 'Links'),
    'header_bg' => $__t('appearance.color_header_bg', 'Header background'),
    'header_text' => $__t('appearance.color_header_text', 'Header text'),
    'button' => $__t('appearance.color_button', 'Main buttons'),
    'button_text' => $__t('appearance.color_button_text', 'Main button text'),
    'quiet_button' => $__t('appearance.color_quiet', 'Quiet buttons'),
    'border' => $__t('appearance.color_border', 'Lines and borders'),
    'success' => $__t('appearance.color_success', 'Success'),
    'warning' => $__t('appearance.color_warning', 'Warning'),
    'danger' => $__t('appearance.color_danger', 'Danger'),
];
$navLabels = [
    'search' => $__t('nav.search', 'Search'),
    'volunteer' => $__t('nav.volunteer', 'Volunteer'),
    'feedback' => $__t('nav.feedback', 'Feedback'),
    'leaderboard' => $__t('nav.leaderboard', 'Leaderboard'),
    'data_entry' => $__t('nav.data_entry', 'Data entry'),
    'similar' => $__t('nav.similar_records', 'Similar records'),
    'moderation' => $__t('nav.moderation', 'Moderation'),
    'admin' => $__t('nav.admin', 'Admin'),
];
?>
<div class="tab-pane fade" id="panel-appearance" role="tabpanel" aria-labelledby="tab-appearance">
    <form id="appearance-form" method="POST" action="<?= htmlspecialchars($basePath, ENT_QUOTES, 'UTF-8') ?>/admin/appearance/save" enctype="multipart/form-data">
        <?= csrf_field() ?>
        <ul class="nav nav-pills flex-wrap gap-1 mb-3" role="tablist" aria-label="<?= htmlspecialchars($__t('appearance.subtabs', 'Appearance sections'), ENT_QUOTES, 'UTF-8') ?>">
            <li class="nav-item" role="presentation"><button type="button" class="nav-link active" data-bs-toggle="tab" data-bs-target="#appear-colors" role="tab"><?= htmlspecialchars($__t('appearance.colors_heading', 'Colours'), ENT_QUOTES, 'UTF-8') ?></button></li>
            <li class="nav-item" role="presentation"><button type="button" class="nav-link" data-bs-toggle="tab" data-bs-target="#appear-brand" role="tab"><?= htmlspecialchars($__t('appearance.brand_heading', 'Logo And Strapline'), ENT_QUOTES, 'UTF-8') ?></button></li>
            <li class="nav-item" role="presentation"><button type="button" class="nav-link" data-bs-toggle="tab" data-bs-target="#appear-type" role="tab"><?= htmlspecialchars($__t('appearance.type_heading', 'Type'), ENT_QUOTES, 'UTF-8') ?></button></li>
            <li class="nav-item" role="presentation"><button type="button" class="nav-link" data-bs-toggle="tab" data-bs-target="#appear-nav" role="tab"><?= htmlspecialchars($__t('appearance.nav_heading', 'Navigation'), ENT_QUOTES, 'UTF-8') ?></button></li>
            <li class="nav-item" role="presentation"><button type="button" class="nav-link" data-bs-toggle="tab" data-bs-target="#appear-footer" role="tab"><?= htmlspecialchars($__t('appearance.footer_heading', 'Footer'), ENT_QUOTES, 'UTF-8') ?></button></li>
        </ul>
        <div class="tab-content">
        <div class="tab-pane fade show active" id="appear-colors" role="tabpanel">
        <div class="card shadow-sm border-0 p-4 mb-4">
            <h4 class="h5 fw-bold mb-3"><?= htmlspecialchars($__t('appearance.colors_heading', 'Colours'), ENT_QUOTES, 'UTF-8') ?></h4>
            <p class="small text-muted"><?= htmlspecialchars($__t('appearance.colors_help', 'High contrast still overrides these when someone uses that toggle.'), ENT_QUOTES, 'UTF-8') ?></p>
            <div class="row g-3">
                <?php foreach ($colorLabels as $key => $label): ?>
                    <?php if (str_starts_with($key, 'footer_')) { continue; } ?>
                    <?php $val = $appearance['colors'][$key] ?? '#000000'; ?>
                    <div class="col-md-4 col-sm-6">
                        <label class="form-label small fw-bold" for="color_<?= htmlspecialchars($key, ENT_QUOTES, 'UTF-8') ?>"><?= htmlspecialchars(function_exists('prd_title_case') ? prd_title_case((string) $label) : (string) $label, ENT_QUOTES, 'UTF-8') ?></label>
                        <div class="d-flex gap-2 align-items-center appear-field">
                            <input type="color" class="form-control form-control-color" id="swatch_<?= htmlspecialchars($key, ENT_QUOTES, 'UTF-8') ?>"
                                   value="<?= htmlspecialchars($val, ENT_QUOTES, 'UTF-8') ?>"
                                   oninput="document.getElementById('color_<?= htmlspecialchars($key, ENT_QUOTES, 'UTF-8') ?>').value=this.value"
                                   aria-label="<?= htmlspecialchars($label, ENT_QUOTES, 'UTF-8') ?>">
                            <input type="text" class="form-control form-control-sm font-monospace" name="color[<?= htmlspecialchars($key, ENT_QUOTES, 'UTF-8') ?>]"
                                   id="color_<?= htmlspecialchars($key, ENT_QUOTES, 'UTF-8') ?>"
                                   value="<?= htmlspecialchars($val, ENT_QUOTES, 'UTF-8') ?>" pattern="#[0-9A-Fa-f]{6}" maxlength="7">
                            <span class="appear-saved small text-success" hidden></span>
                        </div>
                    </div>
                <?php endforeach; ?>
            </div>
        </div>
        </div>
        <div class="tab-pane fade" id="appear-brand" role="tabpanel">
        <div class="card shadow-sm border-0 p-4 mb-4">
            <h4 class="h5 fw-bold mb-3"><?= htmlspecialchars($__t('appearance.brand_heading', 'Logo And Strapline'), ENT_QUOTES, 'UTF-8') ?></h4>
            <?php if (!empty($appearance['logo_file'])): ?>
                <p class="mb-2"><img src="<?= htmlspecialchars($basePath, ENT_QUOTES, 'UTF-8') ?>/branding/logo" alt="" style="max-height:48px;width:auto;"></p>
                <div class="form-check mb-2">
                    <input class="form-check-input" type="checkbox" name="remove_logo" value="1" id="remove_logo">
                    <label class="form-check-label" for="remove_logo"><?= htmlspecialchars($__t('appearance.remove_logo', 'Remove the current logo'), ENT_QUOTES, 'UTF-8') ?></label>
                </div>
            <?php endif; ?>
            <div class="mb-3">
                <label class="form-label fw-bold" for="logo"><?= htmlspecialchars($__t('appearance.logo_label', 'Logo (PNG, JPEG, SVG or WebP, max 8 MB)'), ENT_QUOTES, 'UTF-8') ?></label>
                <input type="file" class="form-control" name="logo" id="logo" accept="image/png,image/jpeg,image/svg+xml,image/webp">
            </div>
            <div class="mb-0">
                <label class="form-label fw-bold" for="strapline"><?= htmlspecialchars($__t('appearance.strapline', 'Strapline (replaces the application name in the top left when a logo or strapline is set)'), ENT_QUOTES, 'UTF-8') ?></label>
                <input type="text" class="form-control" name="strapline" id="strapline" maxlength="120" value="<?= htmlspecialchars((string) $appearance['strapline'], ENT_QUOTES, 'UTF-8') ?>">
            </div>
            <div class="row g-3 mt-2">
                <div class="col-md-6">
                    <label class="form-label fw-bold" for="logo_height"><?= htmlspecialchars($__t('appearance.logo_height', 'Logo size'), ENT_QUOTES, 'UTF-8') ?></label>
                    <select class="form-select" name="logo_height" id="logo_height">
                        <option value="compact" <?= ($appearance['logo_height'] ?? '') === 'compact' ? 'selected' : '' ?>><?= htmlspecialchars($__t('appearance.logo_compact', 'Compact (fits a slim bar)'), ENT_QUOTES, 'UTF-8') ?></option>
                        <option value="regular" <?= ($appearance['logo_height'] ?? 'regular') === 'regular' ? 'selected' : '' ?>><?= htmlspecialchars($__t('appearance.logo_regular', 'Regular (grows the header a little)'), ENT_QUOTES, 'UTF-8') ?></option>
                        <option value="tall" <?= ($appearance['logo_height'] ?? '') === 'tall' ? 'selected' : '' ?>><?= htmlspecialchars($__t('appearance.logo_tall', 'Tall (parish crest, badge)'), ENT_QUOTES, 'UTF-8') ?></option>
                    </select>
                </div>
                <div class="col-md-6">
                    <label class="form-label fw-bold" for="logo_valign"><?= htmlspecialchars($__t('appearance.logo_valign', 'Align with the words'), ENT_QUOTES, 'UTF-8') ?></label>
                    <select class="form-select" name="logo_valign" id="logo_valign">
                        <option value="top" <?= ($appearance['logo_valign'] ?? '') === 'top' ? 'selected' : '' ?>><?= htmlspecialchars($__t('appearance.logo_top', 'Top'), ENT_QUOTES, 'UTF-8') ?></option>
                        <option value="center" <?= ($appearance['logo_valign'] ?? 'center') === 'center' ? 'selected' : '' ?>><?= htmlspecialchars($__t('appearance.logo_center', 'Middle'), ENT_QUOTES, 'UTF-8') ?></option>
                        <option value="bottom" <?= ($appearance['logo_valign'] ?? '') === 'bottom' ? 'selected' : '' ?>><?= htmlspecialchars($__t('appearance.logo_bottom', 'Bottom'), ENT_QUOTES, 'UTF-8') ?></option>
                    </select>
                </div>
            </div>
        </div>
        </div>
        <div class="tab-pane fade" id="appear-type" role="tabpanel">
        <div class="card shadow-sm border-0 p-4 mb-4">
            <h4 class="h5 fw-bold mb-3"><?= htmlspecialchars($__t('appearance.type_heading', 'Type'), ENT_QUOTES, 'UTF-8') ?></h4>
            <div class="row g-3">
                <div class="col-md-6">
                    <label class="form-label fw-bold" for="font"><?= htmlspecialchars($__t('appearance.font', 'Font'), ENT_QUOTES, 'UTF-8') ?></label>
                    <select class="form-select" name="font" id="font">
                        <option value="bootstrap" <?= ($appearance['font'] ?? '') === 'bootstrap' ? 'selected' : '' ?>><?= htmlspecialchars($__t('appearance.font_bootstrap', 'Original pRD / Bootstrap'), ENT_QUOTES, 'UTF-8') ?></option>
                        <option value="sans" <?= ($appearance['font'] ?? '') === 'sans' ? 'selected' : '' ?>><?= htmlspecialchars($__t('appearance.font_sans', 'Clear sans'), ENT_QUOTES, 'UTF-8') ?></option>
                        <option value="system" <?= ($appearance['font'] ?? '') === 'system' ? 'selected' : '' ?>><?= htmlspecialchars($__t('appearance.font_system', 'System UI'), ENT_QUOTES, 'UTF-8') ?></option>
                        <option value="verdana" <?= ($appearance['font'] ?? '') === 'verdana' ? 'selected' : '' ?>><?= htmlspecialchars($__t('appearance.font_verdana', 'Verdana'), ENT_QUOTES, 'UTF-8') ?></option>
                        <option value="trebuchet" <?= ($appearance['font'] ?? '') === 'trebuchet' ? 'selected' : '' ?>><?= htmlspecialchars($__t('appearance.font_trebuchet', 'Trebuchet'), ENT_QUOTES, 'UTF-8') ?></option>
                        <option value="georgia" <?= ($appearance['font'] ?? '') === 'georgia' ? 'selected' : '' ?>><?= htmlspecialchars($__t('appearance.font_georgia', 'Georgia'), ENT_QUOTES, 'UTF-8') ?></option>
                        <option value="serif" <?= ($appearance['font'] ?? '') === 'serif' ? 'selected' : '' ?>><?= htmlspecialchars($__t('appearance.font_serif', 'Palatino serif'), ENT_QUOTES, 'UTF-8') ?></option>
                    </select>
                </div>
                <div class="col-md-4">
                    <label class="form-label fw-bold" for="type_scale"><?= htmlspecialchars($__t('appearance.scale', 'Size'), ENT_QUOTES, 'UTF-8') ?></label>
                    <select class="form-select" name="type_scale" id="type_scale">
                        <option value="sm" <?= $appearance['type_scale'] === 'sm' ? 'selected' : '' ?>><?= htmlspecialchars($__t('appearance.scale_sm', 'Smaller'), ENT_QUOTES, 'UTF-8') ?></option>
                        <option value="md" <?= $appearance['type_scale'] === 'md' ? 'selected' : '' ?>><?= htmlspecialchars($__t('appearance.scale_md', 'Normal'), ENT_QUOTES, 'UTF-8') ?></option>
                        <option value="lg" <?= $appearance['type_scale'] === 'lg' ? 'selected' : '' ?>><?= htmlspecialchars($__t('appearance.scale_lg', 'Larger'), ENT_QUOTES, 'UTF-8') ?></option>
                    </select>
                </div>
                <div class="col-md-4">
                    <label class="form-label fw-bold" for="letter_spacing"><?= htmlspecialchars($__t('appearance.letter_spacing', 'Letter spacing'), ENT_QUOTES, 'UTF-8') ?></label>
                    <select class="form-select" name="letter_spacing" id="letter_spacing">
                        <option value="default" <?= ($appearance['letter_spacing'] ?? 'default') === 'default' ? 'selected' : '' ?>><?= htmlspecialchars($__t('appearance.spacing_default', 'Default'), ENT_QUOTES, 'UTF-8') ?></option>
                        <option value="roomy" <?= ($appearance['letter_spacing'] ?? '') === 'roomy' ? 'selected' : '' ?>><?= htmlspecialchars($__t('appearance.spacing_roomy', 'Roomier (helps many dyslexic readers)'), ENT_QUOTES, 'UTF-8') ?></option>
                        <option value="roomier" <?= ($appearance['letter_spacing'] ?? '') === 'roomier' ? 'selected' : '' ?>><?= htmlspecialchars($__t('appearance.spacing_roomier', 'Widest'), ENT_QUOTES, 'UTF-8') ?></option>
                    </select>
                </div>
            </div>
        </div>
        </div>
        <div class="tab-pane fade" id="appear-nav" role="tabpanel">
        <div class="card shadow-sm border-0 p-4 mb-4">
            <h4 class="h5 fw-bold mb-3"><?= htmlspecialchars($__t('appearance.nav_heading', 'Navigation'), ENT_QUOTES, 'UTF-8') ?></h4>
            <p class="small text-muted"><?= htmlspecialchars($__t('appearance.nav_help', 'Drag the handle to reorder. People still only see links they are allowed to use. Login and profile stay on the right.'), ENT_QUOTES, 'UTF-8') ?></p>
            <ul id="appearance-nav-sort" class="list-group mb-4">
                <?php foreach ($appearance['nav_order'] as $key): ?>
                    <li class="list-group-item d-flex align-items-center gap-3" data-key="<?= htmlspecialchars($key, ENT_QUOTES, 'UTF-8') ?>">
                        <span class="text-muted fs-5 appearance-nav-handle" style="cursor:grab;" title="<?= htmlspecialchars($__t('appearance.drag', 'Drag to reorder'), ENT_QUOTES, 'UTF-8') ?>">☰</span>
                        <span><?= htmlspecialchars($navLabels[$key] ?? $key, ENT_QUOTES, 'UTF-8') ?></span>
                        <input type="hidden" name="nav_order[]" value="<?= htmlspecialchars($key, ENT_QUOTES, 'UTF-8') ?>">
                    </li>
                <?php endforeach; ?>
            </ul>
            <style>.sortable-ghost { opacity: 0.4; background: #f8f9fa !important; }</style>
            <hr>
            <h5 class="h6 fw-bold"><?= htmlspecialchars($__t('appearance.custom_heading', 'Custom Links'), ENT_QUOTES, 'UTF-8') ?></h5>
            <p class="small text-muted"><?= htmlspecialchars($__t('appearance.custom_help', 'Add as many as you need. Set Parent to nest a link under another (dropdown). Use # as the address for a parent that is only a menu title.'), ENT_QUOTES, 'UTF-8') ?></p>
            <div id="appearance-custom-rows">
            <?php
            $customs = $appearance['nav_custom'];
            if ($customs === []) {
                $customs[] = ['id' => 'c1', 'label' => '', 'url' => '', 'who' => 'everyone', 'blank' => false, 'parent' => ''];
            }
            $roleLabel = static function (string $name) use ($__t): string {
                $key = 'role.label_' . strtolower($name);
                $v = $__t($key, '');
                return ($v !== '' && $v !== $key) ? $v : ucwords(str_replace('_', ' ', $name));
            };
            foreach ($customs as $idx => $c):
                $cid = (string) ($c['id'] ?? ('c' . ($idx + 1)));
            ?>
                <div class="border rounded p-3 mb-2 appear-field appearance-custom-row">
                    <input type="hidden" name="custom_id[]" value="<?= htmlspecialchars($cid, ENT_QUOTES, 'UTF-8') ?>">
                    <div class="row g-2">
                        <div class="col-md-3">
                            <label class="form-label small"><?= htmlspecialchars($__t('appearance.custom_label', 'Label'), ENT_QUOTES, 'UTF-8') ?></label>
                            <input class="form-control form-control-sm" name="custom_label[]" value="<?= htmlspecialchars((string) ($c['label'] ?? ''), ENT_QUOTES, 'UTF-8') ?>">
                        </div>
                        <div class="col-md-3">
                            <label class="form-label small"><?= htmlspecialchars($__t('appearance.custom_url', 'Address'), ENT_QUOTES, 'UTF-8') ?></label>
                            <input class="form-control form-control-sm" name="custom_url[]" value="<?= htmlspecialchars((string) ($c['url'] ?? ''), ENT_QUOTES, 'UTF-8') ?>" placeholder="https:// or /page or #">
                        </div>
                        <div class="col-md-2">
                            <label class="form-label small"><?= htmlspecialchars($__t('appearance.custom_parent', 'Parent'), ENT_QUOTES, 'UTF-8') ?></label>
                            <select class="form-select form-select-sm custom-parent" name="custom_parent[]">
                                <option value=""><?= htmlspecialchars($__t('appearance.parent_none', 'Top level'), ENT_QUOTES, 'UTF-8') ?></option>
                                <?php foreach ($appearance['nav_custom'] as $p): ?>
                                    <?php if (($p['id'] ?? '') === $cid || ($p['label'] ?? '') === '') { continue; } ?>
                                    <option value="<?= htmlspecialchars((string) $p['id'], ENT_QUOTES, 'UTF-8') ?>" <?= (($c['parent'] ?? '') === ($p['id'] ?? '')) ? 'selected' : '' ?>><?= htmlspecialchars((string) $p['label'], ENT_QUOTES, 'UTF-8') ?></option>
                                <?php endforeach; ?>
                            </select>
                        </div>
                        <div class="col-md-2">
                            <label class="form-label small"><?= htmlspecialchars($__t('appearance.custom_who', 'Who sees it'), ENT_QUOTES, 'UTF-8') ?></label>
                            <select class="form-select form-select-sm" name="custom_who[]">
                                <option value="everyone" <?= (($c['who'] ?? '') === 'everyone') ? 'selected' : '' ?>><?= htmlspecialchars($__t('appearance.who_everyone', 'Everyone'), ENT_QUOTES, 'UTF-8') ?></option>
                                <option value="logged_in" <?= (($c['who'] ?? '') === 'logged_in') ? 'selected' : '' ?>><?= htmlspecialchars($__t('appearance.who_logged', 'Anyone logged in'), ENT_QUOTES, 'UTF-8') ?></option>
                                <?php foreach ($appearanceRoles as $role): ?>
                                    <?php $rv = 'role:' . (int) $role['id']; $rn = (string) ($role['role_name'] ?? ''); ?>
                                    <option value="<?= htmlspecialchars($rv, ENT_QUOTES, 'UTF-8') ?>" <?= (($c['who'] ?? '') === $rv) ? 'selected' : '' ?>><?= htmlspecialchars($roleLabel($rn), ENT_QUOTES, 'UTF-8') ?></option>
                                <?php endforeach; ?>
                            </select>
                        </div>
                        <div class="col-md-2 d-flex align-items-end justify-content-between">
                            <div class="form-check">
                                <input class="form-check-input" type="checkbox" name="custom_blank[<?= $idx ?>]" value="1" <?= !empty($c['blank']) ? 'checked' : '' ?>>
                                <label class="form-check-label small"><?= htmlspecialchars($__t('appearance.new_tab', 'New tab'), ENT_QUOTES, 'UTF-8') ?></label>
                            </div>
                            <span class="appear-saved small text-success" hidden></span>
                        </div>
                    </div>
                </div>
            <?php endforeach; ?>
            </div>
            <button type="button" class="btn btn-sm btn-outline-primary" id="appearance-add-link">+ <?= htmlspecialchars($__t('appearance.add_link', 'Add a link'), ENT_QUOTES, 'UTF-8') ?></button>
        </div>
        </div>
        <div class="tab-pane fade" id="appear-footer" role="tabpanel">
        <div class="card shadow-sm border-0 p-4 mb-4">
            <h4 class="h5 fw-bold mb-3"><?= htmlspecialchars($__t('appearance.footer_heading', 'Footer'), ENT_QUOTES, 'UTF-8') ?></h4>
            <p class="small text-muted"><?= htmlspecialchars($__t('appearance.footer_help', 'Colours apply to the site footer. The software credit and copyright line stay as they are. Footer source text is still edited under Core settings.'), ENT_QUOTES, 'UTF-8') ?></p>
            <div class="row g-3">
                <?php
                $footerColorLabels = [
                    'footer_bg' => $__t('appearance.color_footer_bg', 'Footer background'),
                    'footer_text' => $__t('appearance.color_footer_text', 'Footer text'),
                    'footer_link' => $__t('appearance.color_footer_link', 'Footer links'),
                ];
                foreach ($footerColorLabels as $key => $label):
                    $val = $appearance['colors'][$key] ?? '#212529';
                ?>
                    <div class="col-md-4 col-sm-6">
                        <label class="form-label small fw-bold" for="color_<?= htmlspecialchars($key, ENT_QUOTES, 'UTF-8') ?>"><?= htmlspecialchars(function_exists('prd_title_case') ? prd_title_case((string) $label) : (string) $label, ENT_QUOTES, 'UTF-8') ?></label>
                        <div class="d-flex gap-2 align-items-center appear-field">
                            <input type="color" class="form-control form-control-color" id="swatch_<?= htmlspecialchars($key, ENT_QUOTES, 'UTF-8') ?>"
                                   value="<?= htmlspecialchars($val, ENT_QUOTES, 'UTF-8') ?>"
                                   oninput="document.getElementById('color_<?= htmlspecialchars($key, ENT_QUOTES, 'UTF-8') ?>').value=this.value"
                                   aria-label="<?= htmlspecialchars($label, ENT_QUOTES, 'UTF-8') ?>">
                            <input type="text" class="form-control form-control-sm font-monospace" name="color[<?= htmlspecialchars($key, ENT_QUOTES, 'UTF-8') ?>]"
                                   id="color_<?= htmlspecialchars($key, ENT_QUOTES, 'UTF-8') ?>"
                                   value="<?= htmlspecialchars($val, ENT_QUOTES, 'UTF-8') ?>" pattern="#[0-9A-Fa-f]{6}" maxlength="7">
                            <span class="appear-saved small text-success" hidden></span>
                        </div>
                    </div>
                <?php endforeach; ?>
            </div>
            <hr>
            <h5 class="h6 fw-bold"><?= htmlspecialchars($__t('appearance.footer_links', 'Footer links'), ENT_QUOTES, 'UTF-8') ?></h5>
            <p class="small text-muted"><?= htmlspecialchars($__t('appearance.footer_links_help', 'These sit above the source line. Nest with Parent. Credit line stays underneath.'), ENT_QUOTES, 'UTF-8') ?></p>
            <div id="appearance-footer-rows">
            <?php
            $fcustoms = $appearance['footer_custom'] ?? [];
            if ($fcustoms === []) {
                $fcustoms[] = ['id' => 'c1', 'label' => '', 'url' => '', 'who' => 'everyone', 'blank' => false, 'parent' => ''];
            }
            foreach ($fcustoms as $idx => $c):
                $cid = (string) ($c['id'] ?? ('c' . ($idx + 1)));
            ?>
                <div class="border rounded p-3 mb-2 appear-field appearance-footer-row">
                    <input type="hidden" name="footer_id[]" value="<?= htmlspecialchars($cid, ENT_QUOTES, 'UTF-8') ?>">
                    <div class="row g-2">
                        <div class="col-md-3">
                            <label class="form-label small"><?= htmlspecialchars($__t('appearance.custom_label', 'Label'), ENT_QUOTES, 'UTF-8') ?></label>
                            <input class="form-control form-control-sm" name="footer_label[]" value="<?= htmlspecialchars((string) ($c['label'] ?? ''), ENT_QUOTES, 'UTF-8') ?>">
                        </div>
                        <div class="col-md-3">
                            <label class="form-label small"><?= htmlspecialchars($__t('appearance.custom_url', 'Address'), ENT_QUOTES, 'UTF-8') ?></label>
                            <input class="form-control form-control-sm" name="footer_url[]" value="<?= htmlspecialchars((string) ($c['url'] ?? ''), ENT_QUOTES, 'UTF-8') ?>" placeholder="https:// or /page or #">
                        </div>
                        <div class="col-md-2">
                            <label class="form-label small"><?= htmlspecialchars($__t('appearance.custom_parent', 'Parent'), ENT_QUOTES, 'UTF-8') ?></label>
                            <select class="form-select form-select-sm" name="footer_parent[]">
                                <option value=""><?= htmlspecialchars($__t('appearance.parent_none', 'Top level'), ENT_QUOTES, 'UTF-8') ?></option>
                                <?php foreach (($appearance['footer_custom'] ?? []) as $p): ?>
                                    <?php if (($p['id'] ?? '') === $cid || ($p['label'] ?? '') === '') { continue; } ?>
                                    <option value="<?= htmlspecialchars((string) $p['id'], ENT_QUOTES, 'UTF-8') ?>" <?= (($c['parent'] ?? '') === ($p['id'] ?? '')) ? 'selected' : '' ?>><?= htmlspecialchars((string) $p['label'], ENT_QUOTES, 'UTF-8') ?></option>
                                <?php endforeach; ?>
                            </select>
                        </div>
                        <div class="col-md-2">
                            <label class="form-label small"><?= htmlspecialchars($__t('appearance.custom_who', 'Who sees it'), ENT_QUOTES, 'UTF-8') ?></label>
                            <select class="form-select form-select-sm" name="footer_who[]">
                                <option value="everyone" <?= (($c['who'] ?? '') === 'everyone') ? 'selected' : '' ?>><?= htmlspecialchars($__t('appearance.who_everyone', 'Everyone'), ENT_QUOTES, 'UTF-8') ?></option>
                                <option value="logged_in" <?= (($c['who'] ?? '') === 'logged_in') ? 'selected' : '' ?>><?= htmlspecialchars($__t('appearance.who_logged', 'Anyone logged in'), ENT_QUOTES, 'UTF-8') ?></option>
                                <?php foreach ($appearanceRoles as $role): ?>
                                    <?php $rv = 'role:' . (int) $role['id']; $rn = (string) ($role['role_name'] ?? ''); ?>
                                    <option value="<?= htmlspecialchars($rv, ENT_QUOTES, 'UTF-8') ?>" <?= (($c['who'] ?? '') === $rv) ? 'selected' : '' ?>><?= htmlspecialchars($roleLabel($rn), ENT_QUOTES, 'UTF-8') ?></option>
                                <?php endforeach; ?>
                            </select>
                        </div>
                        <div class="col-md-2 d-flex align-items-end">
                            <div class="form-check">
                                <input class="form-check-input" type="checkbox" name="footer_blank[<?= $idx ?>]" value="1" <?= !empty($c['blank']) ? 'checked' : '' ?>>
                                <label class="form-check-label small"><?= htmlspecialchars($__t('appearance.new_tab', 'New tab'), ENT_QUOTES, 'UTF-8') ?></label>
                            </div>
                        </div>
                    </div>
                </div>
            <?php endforeach; ?>
            </div>
            <button type="button" class="btn btn-sm btn-outline-primary" id="appearance-add-footer-link">+ <?= htmlspecialchars($__t('appearance.add_link', 'Add a link'), ENT_QUOTES, 'UTF-8') ?></button>
        </div>
        </div>
        </div>
        <div class="d-flex flex-wrap gap-2 align-items-center">
            <button type="submit" name="reset_appearance" value="1" class="btn btn-outline-secondary"
                    onclick="return confirm('<?= htmlspecialchars($__t('appearance.reset_confirm', 'Reset colours, logo, type and menu order?'), ENT_QUOTES, 'UTF-8') ?>');">
                <?= htmlspecialchars($__t('appearance.reset', 'Reset to default'), ENT_QUOTES, 'UTF-8') ?>
            </button>
        </div>
    </form>
    <script src="https://cdnjs.cloudflare.com/ajax/libs/Sortable/1.15.0/Sortable.min.js"></script>
    <script>
    (function () {
        var form = document.getElementById('appearance-form');
        if (!form) return;
        var status = document.getElementById('appearance-save-status');
        var saving = <?= json_encode($__t('appearance.saving', 'Saving…'), JSON_UNESCAPED_UNICODE) ?>;
        var saved = <?= json_encode($__t('appearance.saved', 'Appearance saved.'), JSON_UNESCAPED_UNICODE) ?>;
        var failed = <?= json_encode($__t('appearance.save_failed', 'Could not save just then. Try again.'), JSON_UNESCAPED_UNICODE) ?>;
        var timer = null;
        var lastEl = null;
        function markSaved(el) {
            var wrap = el && el.closest ? el.closest('.appear-field') : null;
            if (!wrap) wrap = el && el.parentElement;
            if (!wrap) return;
            var tag = wrap.querySelector('.appear-saved');
            if (!tag) {
                tag = document.createElement('span');
                tag.className = 'appear-saved small text-success ms-2';
                wrap.appendChild(tag);
            }
            tag.hidden = false;
            tag.textContent = saved;
        }
        function persist() {
            var data = new FormData(form);
            data.delete('reset_appearance');
            if (status) status.textContent = saving;
            fetch(form.action, {
                method: 'POST',
                body: data,
                credentials: 'same-origin',
                headers: { 'X-Requested-With': 'XMLHttpRequest' }
            }).then(function (r) { return r.json(); }).then(function (j) {
                var msg = (j && j.message) ? j.message : saved;
                if (status) status.textContent = msg;
                markSaved(lastEl);
            }).catch(function () {
                if (status) status.textContent = failed;
            });
        }
        function schedule(el) {
            lastEl = el || lastEl;
            clearTimeout(timer);
            timer = setTimeout(persist, 200);
        }
        form.addEventListener('change', function (e) { schedule(e.target); });
        form.addEventListener('focusout', function (e) {
            if (e.target && (e.target.tagName === 'INPUT' || e.target.tagName === 'SELECT' || e.target.tagName === 'TEXTAREA')) {
                schedule(e.target);
            }
        });
        function bindAdd(btnId, rowWrapId, rowClass) {
            var btn = document.getElementById(btnId);
            var wrap = document.getElementById(rowWrapId);
            if (!btn || !wrap) return;
            btn.addEventListener('click', function () {
                var first = wrap.querySelector('.' + rowClass);
                if (!first) return;
                var clone = first.cloneNode(true);
                clone.querySelectorAll('input[type="text"], input[type="hidden"]').forEach(function (i) {
                    if (i.name && i.name.indexOf('_id[]') !== -1) i.value = 'c' + Date.now();
                    else i.value = '';
                });
                clone.querySelectorAll('select').forEach(function (s) { s.selectedIndex = 0; });
                clone.querySelectorAll('input[type="checkbox"]').forEach(function (c) { c.checked = false; });
                wrap.appendChild(clone);
            });
        }
        bindAdd('appearance-add-link', 'appearance-custom-rows', 'appearance-custom-row');
        bindAdd('appearance-add-footer-link', 'appearance-footer-rows', 'appearance-footer-row');
        var list = document.getElementById('appearance-nav-sort');
        if (list && window.Sortable) {
            Sortable.create(list, {
                handle: '.appearance-nav-handle',
                ghostClass: 'sortable-ghost',
                onEnd: persist
            });
        }
        form.addEventListener('submit', function (e) {
            if (e.submitter && e.submitter.name === 'reset_appearance') return;
            e.preventDefault();
            persist();
        });
    })();
    </script>
</div>
