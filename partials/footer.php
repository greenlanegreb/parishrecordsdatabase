<?php
declare(strict_types=1);
/**
 * MIGRATED FILE MAPPING
 * ---------------------
 * Original Old File: partials/footer.php
 * Migrated Date: 2026-08-04 19:00:00
 */
$footerCompiled = __('footer.compiled_notice');
$pdoForFooter = (isset($GLOBALS['pdo']) && $GLOBALS['pdo'] instanceof PDO) ? $GLOBALS['pdo'] : null;
if ($pdoForFooter instanceof PDO && function_exists('get_setting')) {
    $custom = get_setting($pdoForFooter, 'footer_compiled_notice', '');
    if ($custom !== '') {
        $footerCompiled = $custom;
    }
}
$footerLinks = [];
if ($pdoForFooter instanceof PDO) {
    try {
        $u = function_exists('get_current_user_data') ? get_current_user_data($pdoForFooter) : null;
        $footerLinks = (new \App\Services\AppearanceService($pdoForFooter))->visibleFooterLinks([
            'logged_in' => isset($_SESSION['user_id']),
            'admin_user' => function_exists('has_permission') && has_permission($pdoForFooter, 'manage_settings'),
            'role_ids' => (isset($u['role_id']) ? [(int) $u['role_id']] : []),
        ]);
    } catch (\Throwable $e) {
        $footerLinks = [];
    }
}
?>
</main>
<footer class="site-footer border-top py-4 mt-auto text-center" role="contentinfo">
    <div class="container">
        <?php if ($footerLinks !== []): ?>
        <nav class="mb-3" aria-label="<?= htmlspecialchars(__('appearance.footer_links') !== 'appearance.footer_links' ? __('appearance.footer_links') : 'Footer links', ENT_QUOTES, 'UTF-8') ?>">
            <ul class="list-inline mb-0">
                <?php foreach ($footerLinks as $fl): ?>
                    <?php if (($fl['kind'] ?? '') === 'menu'): ?>
                        <li class="list-inline-item dropdown">
                            <a class="small dropdown-toggle text-decoration-none" href="#" data-bs-toggle="dropdown"><?= htmlspecialchars((string) $fl['label'], ENT_QUOTES, 'UTF-8') ?></a>
                            <ul class="dropdown-menu text-start">
                                <?php foreach ($fl['children'] ?? [] as $ch): ?>
                                    <li><a class="dropdown-item" href="<?= htmlspecialchars((string) $ch['href'], ENT_QUOTES, 'UTF-8') ?>" <?= !empty($ch['blank']) ? 'target="_blank" rel="noopener noreferrer"' : '' ?>><?= htmlspecialchars((string) $ch['label'], ENT_QUOTES, 'UTF-8') ?></a></li>
                                <?php endforeach; ?>
                            </ul>
                        </li>
                    <?php else: ?>
                        <li class="list-inline-item">
                            <a class="small text-decoration-none" href="<?= htmlspecialchars((string) $fl['href'], ENT_QUOTES, 'UTF-8') ?>" <?= !empty($fl['blank']) ? 'target="_blank" rel="noopener noreferrer"' : '' ?>><?= htmlspecialchars((string) $fl['label'], ENT_QUOTES, 'UTF-8') ?></a>
                        </li>
                    <?php endif; ?>
                <?php endforeach; ?>
            </ul>
        </nav>
        <?php endif; ?>
        <p class="small mb-1"><?= htmlspecialchars($footerCompiled, ENT_QUOTES, 'UTF-8') ?></p>
        <p class="small mb-0">
            <?= htmlspecialchars(__('footer.software_notice'), ENT_QUOTES, 'UTF-8') ?>
            &copy; <?= date('Y') ?> greenlanegreb. <?= htmlspecialchars(__('footer.rights_reserved'), ENT_QUOTES, 'UTF-8') ?>
        </p>
    </div>
</footer>
<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js" integrity="sha384-YvpcrYf0tY3lHB60NNkmXc5s9fDVZLESaAA55NDzOxhy9GkcIdslK1eN7N6jIeHz" crossorigin="anonymous"></script>
</body>
</html>
