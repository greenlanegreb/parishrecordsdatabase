<?php
/**
 * MIGRATED FILE MAPPING
 * ---------------------
 * Original Old File: admin/manage_tables.php
 * Migrated Date: 2026-08-04 09:40:12
 */
declare(strict_types=1);
/** @string $message */
/** @string $error */
/** @string $userTimezone */
/** @string $fullFormatStr */
/** @array<int, array<string, mixed>> $tables */
/** @int $activeTableId */
/** @array<string, mixed>|null $activeTableInfo */
/** @array<string, mixed>|null $editTable */
/** @array<string, mixed>|null $editCol */
/** @array<int, array<string, mixed>> $columns */
require_once ROOT_PATH . '/partials/header.php';
$basePath = defined('BASE_PATH') ? rtrim(BASE_PATH, '/') : '';
$mt = static function (string $key, string $fallback): string {
    $v = function_exists('__') ? (string) __($key) : $key;
    return ($v !== $key && $v !== '') ? $v : $fallback;
};
?>
<script src="https://cdnjs.cloudflare.com/ajax/libs/Sortable/1.15.0/Sortable.min.js"></script>
<div class="container py-4" role="region" aria-label="<?= htmlspecialchars($mt('manage_tables.heading', 'Dynamic Table & Column Management'), ENT_QUOTES, 'UTF-8') ?>">
    <h3 class="fw-bold mb-1"><?= htmlspecialchars($mt('manage_tables.heading', 'Dynamic Table & Column Management'), ENT_QUOTES, 'UTF-8') ?></h3>
    <p class="text-muted mb-3"><?= htmlspecialchars($mt('manage_tables.subheading', 'Add and look after the tables and columns people use to enter records. You can change names, field types, and what appears on the list or the full record page.'), ENT_QUOTES, 'UTF-8') ?></p>
    <?php if (!empty($error)): ?>
        <div class="alert alert-danger alert-dismissible fade show" role="alert">
            <strong><?= htmlspecialchars($error, ENT_QUOTES, 'UTF-8') ?></strong>
            <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
        </div>
    <?php endif; ?>
    <?php if (!empty($message)): ?>
        <div class="alert alert-success alert-dismissible fade show" role="alert">
            <strong><?= htmlspecialchars($message, ENT_QUOTES, 'UTF-8') ?></strong>
            <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
        </div>
    <?php endif; ?>
    <?php require __DIR__ . '/manage_tables_parts/table_switcher.php'; ?>
    <?php require __DIR__ . '/manage_tables_parts/table_form.php'; ?>
    <?php if ($activeTableInfo): ?>
        <hr class="my-4">
        <?php require __DIR__ . '/manage_tables_parts/column_form.php'; ?>
        <hr class="my-4">
        <?php require __DIR__ . '/manage_tables_parts/columns_table.php'; ?>
        <?php require __DIR__ . '/manage_tables_parts/sortable_script.php'; ?>
    <?php endif; ?>
</div>
<?php require_once ROOT_PATH . '/partials/footer.php'; ?>
