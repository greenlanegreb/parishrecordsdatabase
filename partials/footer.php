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
?>
</main> <!-- Closing main layout wrapper -->
<footer class="site-footer bg-white border-top py-4 mt-auto text-center" role="contentinfo">
    <div class="container">
        <p class="small text-muted mb-1"><?= htmlspecialchars($footerCompiled, ENT_QUOTES, 'UTF-8') ?></p>
        <p class="small text-muted mb-0">
            <?= htmlspecialchars(__('footer.software_notice'), ENT_QUOTES, 'UTF-8') ?>
            &copy; <?= date('Y') ?> Benjamin-Elijah Cakebread-Snow. <?= htmlspecialchars(__('footer.rights_reserved'), ENT_QUOTES, 'UTF-8') ?>
        </p>
    </div>
</footer>
<!-- Bootstrap 5 JS Bundle CDN -->
<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js" integrity="sha384-YvpcrYf0tY3lHB60NNkmXc5s9fDVZLESaAA55NDzOxhy9GkcIdslK1eN7N6jIeHz" crossorigin="anonymous"></script>
</body>
</html>
