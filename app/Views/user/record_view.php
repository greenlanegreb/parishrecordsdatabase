<?php
declare(strict_types=1);
/** @var array<string, mixed> $record */
/** @var array<int, array<string, mixed>> $columns */
/** @var array<int, string> $values */
/** @var string $createdByLabel */
/** @var string $createdAtLabel */
/** @var bool $canSuggestEdit */
/** @var bool $canEditRecords */
/** @var bool $canDeleteRecords */
$basePath = defined('BASE_PATH') ? rtrim((string) BASE_PATH, '/') : '';
$t = static function (string $key, string $fallback): string {
    $v = function_exists('__') ? (string) __($key) : $key;
    return ($v !== $key && $v !== '') ? $v : $fallback;
};
require_once dirname(__DIR__, 3) . '/partials/header.php';
$rid = (int) ($record['id'] ?? 0);
?>
<div class="container py-4" style="max-width: 46rem;">
    <p class="mb-2">
        <a href="<?= htmlspecialchars((string) ($returnUrl ?? $basePath . '/'), ENT_QUOTES, 'UTF-8') ?>" class="small">&larr; <?= htmlspecialchars($t('record.back', 'Back to the list'), ENT_QUOTES, 'UTF-8') ?></a>
    </p>
    <h1 class="h4 fw-bold mb-3">
        <?= htmlspecialchars((string) ($record['table_name'] ?? $t('record.heading', 'Record')), ENT_QUOTES, 'UTF-8') ?>
        <span class="text-muted fw-normal">#<?= $rid ?></span>
    </h1>
    <dl class="row mb-4">
        <?php foreach ($columns as $col): ?>
            <?php
            $cid = (int) ($col['id'] ?? 0);
            $name = (string) ($col['column_name'] ?? '');
            $raw = $values[$cid] ?? '';
            $dataType = isset($col['data_type']) && is_string($col['data_type']) ? $col['data_type'] : '';
            $typeKey = strtoupper(trim($dataType));
            $boolFormat = isset($col['boolean_display_format']) && is_string($col['boolean_display_format'])
                ? $col['boolean_display_format'] : 'yes_no';
            $looksIsoDate = (bool) preg_match('/^\d{4}-\d{2}-\d{2}/', trim($raw));
            if ($typeKey === 'BOOLEAN' && function_exists('format_boolean_value')) {
                $shown = format_boolean_value($raw, $boolFormat);
            } elseif ($typeKey === 'LOCATION' && class_exists(\App\Services\LocationValueService::class)) {
                $shown = \App\Services\LocationValueService::formatDisplay($raw);
            } elseif ($typeKey === 'TIME' && function_exists('format_display_time')) {
                $shown = format_display_time($raw, $userTimePref ?? null);
            } elseif (($typeKey === 'DATE' || $typeKey === 'DATETIME' || $looksIsoDate) && function_exists('format_display_date')) {
                $shown = format_display_date($raw, $userDateFormat ?? null);
            } else {
                $shown = $raw;
            }
            ?>
            <dt class="col-sm-4"><?= htmlspecialchars($name, ENT_QUOTES, 'UTF-8') ?></dt>
            <dd class="col-sm-8"><?= ($shown === '' || $shown === null) ? '—' : htmlspecialchars((string) $shown, ENT_QUOTES, 'UTF-8') ?></dd>
        <?php endforeach; ?>
        <dt class="col-sm-4"><?= htmlspecialchars($t('index.th_added_by', 'Added by'), ENT_QUOTES, 'UTF-8') ?></dt>
        <dd class="col-sm-8"><?= htmlspecialchars((string) $createdByLabel, ENT_QUOTES, 'UTF-8') ?></dd>
        <dt class="col-sm-4"><?= htmlspecialchars($t('index.th_date_added', 'Date added'), ENT_QUOTES, 'UTF-8') ?></dt>
        <dd class="col-sm-8"><?= htmlspecialchars((string) $createdAtLabel, ENT_QUOTES, 'UTF-8') ?></dd>
    </dl>
    <div class="d-flex flex-wrap gap-2">
        <?php if (!empty($canExport)): ?>
            <button type="button" class="btn btn-sm btn-outline-secondary" onclick="window.print()"><?= htmlspecialchars($t('record.print', 'Print this record'), ENT_QUOTES, 'UTF-8') ?></button>
        <?php endif; ?>
        <a class="btn btn-sm btn-outline-secondary" href="<?= htmlspecialchars($basePath . '/record_history.php?record_id=' . $rid, ENT_QUOTES, 'UTF-8') ?>"><?= htmlspecialchars($t('api_search.history_btn', 'History'), ENT_QUOTES, 'UTF-8') ?></a>
        <?php if (!empty($canSuggestEdit)): ?>
            <a class="btn btn-sm btn-outline-primary" href="<?= htmlspecialchars($basePath . '/user/suggest-edit?record_id=' . $rid, ENT_QUOTES, 'UTF-8') ?>"><?= htmlspecialchars($t('api_search.suggest_edit_btn', 'Suggest edit'), ENT_QUOTES, 'UTF-8') ?></a>
        <?php endif; ?>
        <?php if (!empty($canEditRecords)): ?>
            <a class="btn btn-sm btn-outline-secondary" href="<?= htmlspecialchars($basePath . '/records/' . $rid . '/edit?return=' . rawurlencode((string) ($returnUrl ?? $basePath . '/')), ENT_QUOTES, 'UTF-8') ?>"><?= htmlspecialchars($t('btn.edit', 'Edit'), ENT_QUOTES, 'UTF-8') ?></a>
        <?php endif; ?>
        <?php if (!empty($canDeleteRecords)): ?>
            <form method="POST" action="<?= htmlspecialchars($basePath . '/records/delete', ENT_QUOTES, 'UTF-8') ?>" class="d-inline"
                  onsubmit="return confirm('<?= htmlspecialchars($t('data_entry.delete_record_confirm', 'Delete this record permanently?'), ENT_QUOTES, 'UTF-8') ?>');">
                <?= function_exists('csrf_field') ? csrf_field() : '' ?>
                <input type="hidden" name="record_id" value="<?= $rid ?>">
                <input type="hidden" name="return_url" value="<?= htmlspecialchars((string) ($returnUrl ?? $basePath . '/'), ENT_QUOTES, 'UTF-8') ?>">
                <button type="submit" class="btn btn-sm btn-outline-danger"><?= htmlspecialchars($t('data_entry.delete_record_btn', 'Delete'), ENT_QUOTES, 'UTF-8') ?></button>
            </form>
        <?php endif; ?>
    </div>
</div>
<?php require_once dirname(__DIR__, 3) . '/partials/footer.php'; ?>
