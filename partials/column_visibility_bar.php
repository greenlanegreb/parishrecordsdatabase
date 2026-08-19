<?php
declare(strict_types=1);
/** @var array<int, array<string, mixed>> $columns */
/** @var array<int, array<string, mixed>> $visibleColumns */
$columns = $columns ?? [];
$visibleColumns = $visibleColumns ?? $columns;
$visibleIds = function_exists('visible_column_ids') ? visible_column_ids($visibleColumns) : [];
?>
<details class="mb-3">
    <summary class="fw-bold" style="cursor: pointer;">
        <?= htmlspecialchars(__('cols.show_heading') !== 'cols.show_heading' ? __('cols.show_heading') : 'Choose which columns to show', ENT_QUOTES, 'UTF-8') ?>
    </summary>
    <p class="form-text mt-2 mb-2">
        <?= htmlspecialchars(__('cols.show_help') !== 'cols.show_help' ? __('cols.show_help') : 'Untick a column to hide it here, in downloads, and when you print. On a phone, fewer columns are easier to read.', ENT_QUOTES, 'UTF-8') ?>
    </p>
    <div class="d-flex flex-wrap gap-3" role="group" aria-label="<?= htmlspecialchars(__('cols.show_heading') !== 'cols.show_heading' ? __('cols.show_heading') : 'Choose which columns to show', ENT_QUOTES, 'UTF-8') ?>">
        <?php foreach ($columns as $col): ?>
            <?php
                $cid = isset($col['id']) ? (int) $col['id'] : 0;
                $cname = isset($col['column_name']) && is_string($col['column_name']) ? $col['column_name'] : '';
                if ($cid < 1) {
                    continue;
                }
            ?>
            <div class="form-check">
                <input class="form-check-input js-col-vis" type="checkbox" name="cols[]" value="<?= $cid ?>" id="colvis_<?= $cid ?>" <?= in_array($cid, $visibleIds, true) ? 'checked' : '' ?>>
                <label class="form-check-label" for="colvis_<?= $cid ?>"><?= htmlspecialchars($cname, ENT_QUOTES, 'UTF-8') ?></label>
            </div>
        <?php endforeach; ?>
            <?php $tableIdForMeta = isset($activeTableId) ? (int) $activeTableId : 0; ?>
            <div class="form-check">
                <input class="form-check-input js-col-vis" type="checkbox" name="cols[]" value="created_by" id="colvis_created_by" <?= ($tableIdForMeta < 1 || !function_exists('show_created_by_column') || show_created_by_column($tableIdForMeta)) ? 'checked' : '' ?>>
                <label class="form-check-label" for="colvis_created_by"><?= htmlspecialchars(__('index.th_created_by') !== 'index.th_created_by' ? __('index.th_created_by') : 'Created by', ENT_QUOTES, 'UTF-8') ?></label>
            </div>
            <div class="form-check">
                <input class="form-check-input js-col-vis" type="checkbox" name="cols[]" value="created_at" id="colvis_created_at" <?= ($tableIdForMeta < 1 || !function_exists('show_created_at_column') || show_created_at_column($tableIdForMeta)) ? 'checked' : '' ?>>
                <label class="form-check-label" for="colvis_created_at"><?= htmlspecialchars(__('index.th_date_added') !== 'index.th_date_added' ? __('index.th_date_added') : 'Date added', ENT_QUOTES, 'UTF-8') ?></label>
            </div>
    </div>
</details>
