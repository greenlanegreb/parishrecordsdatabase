<?php
declare(strict_types=1);

/**
 * Settings tab: manage demo packs (only included when a pack is already installed).
 *
 * @var string $basePath
 * @var list<array{slug: string, label: string, summary: string, installed: bool, has_demo_data: bool}> $demoPacks
 */
$basePath = isset($basePath) && is_string($basePath) ? $basePath : '';
$demoPacks = $demoPacks ?? [];

$tc = dirname(__DIR__, 3) . '/includes/prd_title_case.php';
if (is_file($tc)) {
    require_once $tc;
}
$nt = static function (string $key, string $fallback): string {
    $t = function_exists('__') ? (string) __($key) : $key;
    if ($t === $key || $t === '') {
        $t = $fallback;
    }
    return function_exists('prd_title_case') ? prd_title_case($t) : $t;
};
?>
<div class="tab-pane fade" id="panel-demo" role="tabpanel" aria-labelledby="tab-demo">
    <h2 class="h5 fw-bold mb-2"><?= htmlspecialchars($nt('demo.heading', 'Demo packs'), ENT_QUOTES, 'UTF-8') ?></h2>
    <p class="text-muted" id="demoIntro"><?= htmlspecialchars($nt('demo.intro', 'Optional starter tables so you can try PRD before you design your own. You can add one pack or both. Nothing here is required for a live site.'), ENT_QUOTES, 'UTF-8') ?></p>

    <div class="card shadow-sm border-0 mb-4">
        <div class="card-body">
            <h3 class="h6 fw-bold" id="demoInstallHeading"><?= htmlspecialchars($nt('demo.install_heading', 'Add a demo pack'), ENT_QUOTES, 'UTF-8') ?></h3>
            <p class="small text-muted" id="demoInstallHelp">
                <?= htmlspecialchars($nt('demo.install_help', 'Tables and columns only: empty structure you fill yourself. Tables, columns, and sample data: a few made-up rows so you can click around immediately. You can remove the sample rows later without removing the tables.'), ENT_QUOTES, 'UTF-8') ?>
            </p>
            <form method="POST" action="<?= htmlspecialchars($basePath, ENT_QUOTES, 'UTF-8') ?>/admin/demo-packs" aria-labelledby="demoInstallHeading" aria-describedby="demoInstallHelp">
                <?= csrf_field() ?>
                <input type="hidden" name="demo_action" value="install">
                <fieldset class="mb-3">
                    <legend class="form-label fw-bold small"><?= htmlspecialchars($nt('demo.choose_packs', 'Which packs'), ENT_QUOTES, 'UTF-8') ?></legend>
                    <?php foreach ($demoPacks as $pack): ?>
                        <?php
                        $slug = htmlspecialchars($pack['slug'], ENT_QUOTES, 'UTF-8');
                        $installed = !empty($pack['installed']);
                        $helpId = 'pack-help-' . $pack['slug'];
                        ?>
                        <div class="form-check mb-3 py-1">
                            <input class="form-check-input fs-5" type="checkbox" name="packs[]"
                                   value="<?= $slug ?>"
                                   id="pack_<?= $slug ?>"
                                   aria-describedby="<?= htmlspecialchars($helpId, ENT_QUOTES, 'UTF-8') ?>"
                                   <?= $installed ? 'disabled aria-disabled="true"' : '' ?>>
                            <label class="form-check-label" for="pack_<?= $slug ?>">
                                <strong><?= htmlspecialchars(function_exists('prd_title_case') ? prd_title_case((string) $pack['label']) : (string) $pack['label'], ENT_QUOTES, 'UTF-8') ?></strong>
                                <?php if ($installed): ?>
                                    <span class="badge bg-secondary"><?= htmlspecialchars($nt('demo.already_installed', 'Already installed'), ENT_QUOTES, 'UTF-8') ?></span>
                                <?php endif; ?>
                            </label>
                            <p class="small text-muted mb-0" id="<?= htmlspecialchars($helpId, ENT_QUOTES, 'UTF-8') ?>">
                                <?= htmlspecialchars(function_exists('prd_title_case') ? prd_title_case((string) $pack['summary']) : (string) $pack['summary'], ENT_QUOTES, 'UTF-8') ?>
                                <?php if ($installed): ?>
                                    <?= htmlspecialchars($nt('demo.already_installed_hint', 'This pack is already installed, so it cannot be selected again.'), ENT_QUOTES, 'UTF-8') ?>
                                <?php endif; ?>
                            </p>
                        </div>
                    <?php endforeach; ?>
                </fieldset>
                <fieldset class="mb-3">
                    <legend class="form-label fw-bold small"><?= htmlspecialchars($nt('demo.what_to_add', 'What to add'), ENT_QUOTES, 'UTF-8') ?></legend>
                    <div class="form-check mb-3 py-1">
                        <input class="form-check-input fs-5" type="radio" name="with_data" id="demoSchemaOnly" value="0" checked>
                        <label class="form-check-label" for="demoSchemaOnly"><?= htmlspecialchars($nt('demo.schema_only', 'Tables and columns only (no sample rows)'), ENT_QUOTES, 'UTF-8') ?></label>
                    </div>
                    <div class="form-check mb-3 py-1">
                        <input class="form-check-input fs-5" type="radio" name="with_data" id="demoSchemaData" value="1">
                        <label class="form-check-label" for="demoSchemaData"><?= htmlspecialchars($nt('demo.schema_and_data', 'Tables, columns, and sample data'), ENT_QUOTES, 'UTF-8') ?></label>
                    </div>
                </fieldset>
                <button type="submit" class="btn btn-primary"><?= htmlspecialchars($nt('demo.install_btn', 'Add selected packs'), ENT_QUOTES, 'UTF-8') ?></button>
            </form>
        </div>
    </div>

    <div class="card shadow-sm border-0">
        <div class="card-body">
            <h3 class="h6 fw-bold" id="demoRemoveHeading"><?= htmlspecialchars($nt('demo.remove_heading', 'Remove a demo pack'), ENT_QUOTES, 'UTF-8') ?></h3>
            <?php foreach ($demoPacks as $pack): ?>
                <?php if (empty($pack['installed'])) {
                    continue;
                }
                $slug = htmlspecialchars($pack['slug'], ENT_QUOTES, 'UTF-8');
                $confirm = $nt('demo.remove_pack_confirm', 'This deletes the demo tables and columns, the sample rows, and any extra data you added in those demo tables. Other tables are not touched. Continue?');
                ?>
                <section class="border rounded p-3 mb-3" aria-labelledby="installed-<?= $slug ?>">
                    <h4 class="h6 fw-bold mb-2" id="installed-<?= $slug ?>"><?= htmlspecialchars($pack['label'], ENT_QUOTES, 'UTF-8') ?></h4>
                    <?php if (!empty($pack['has_demo_data'])): ?>
                        <form method="POST" action="<?= htmlspecialchars($basePath, ENT_QUOTES, 'UTF-8') ?>/admin/demo-packs" class="mb-2">
                            <?= csrf_field() ?>
                            <input type="hidden" name="demo_action" value="remove_data">
                            <input type="hidden" name="pack_slug" value="<?= $slug ?>">
                            <p class="small text-muted mb-2" id="help-data-<?= $slug ?>">
                                <?= htmlspecialchars($nt('demo.remove_data_help', 'Deletes only the original sample rows. Tables, columns, and any rows you added yourself are kept.'), ENT_QUOTES, 'UTF-8') ?>
                            </p>
                            <button type="submit" class="btn btn-sm btn-outline-secondary" aria-describedby="help-data-<?= $slug ?>">
                                <?= htmlspecialchars($nt('demo.remove_data_btn', 'Remove sample data only'), ENT_QUOTES, 'UTF-8') ?>
                            </button>
                        </form>
                    <?php endif; ?>
                    <form method="POST" action="<?= htmlspecialchars($basePath, ENT_QUOTES, 'UTF-8') ?>/admin/demo-packs" onsubmit="return confirm(<?= json_encode($confirm, JSON_HEX_TAG | JSON_HEX_APOS | JSON_UNESCAPED_UNICODE) ?>);">
                        <?= csrf_field() ?>
                        <input type="hidden" name="demo_action" value="remove_pack">
                        <input type="hidden" name="pack_slug" value="<?= $slug ?>">
                        <p class="small text-muted mb-2" id="help-pack-<?= $slug ?>">
                            <?= htmlspecialchars($nt('demo.remove_pack_help', 'Deletes the demo tables and columns, the sample rows, and any data you have since entered in those demo tables. Your other tables are not affected.'), ENT_QUOTES, 'UTF-8') ?>
                        </p>
                        <button type="submit" class="btn btn-sm btn-outline-danger" aria-describedby="help-pack-<?= $slug ?>">
                            <?= htmlspecialchars($nt('demo.remove_pack_btn', 'Remove tables, columns, and all data in this pack'), ENT_QUOTES, 'UTF-8') ?>
                        </button>
                    </form>
                </section>
            <?php endforeach; ?>
        </div>
    </div>
</div>
