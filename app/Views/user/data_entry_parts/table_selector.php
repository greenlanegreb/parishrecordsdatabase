<?php
declare(strict_types=1);
?>
<?php if (count($availableTables) > 1): ?>
    <div class="card shadow-sm border-0 bg-light p-3 mb-4 d-flex flex-row align-items-center gap-3 flex-wrap">
        <label for="data_entry_table_selector" class="fw-bold"><?= htmlspecialchars(__('data_entry.active_table_label'), ENT_QUOTES, 'UTF-8') ?></label>
        <select id="data_entry_table_selector" class="form-select form-select-sm" style="max-width: 300px;" onchange="location.href='<?= $basePath ?>/data-entry?table_id=' + this.value;">
            <?php foreach ($availableTables as $at): ?>
                <?php 
                    $atId = isset($at['id']) ? (int)$at['id'] : 0;
                    $atName = isset($at['table_name']) && is_string($at['table_name']) ? $at['table_name'] : '';
                ?>
                <option value="<?= $atId ?>" <?= ($atId === $activeTableId) ? 'selected' : '' ?>>
                    <?= htmlspecialchars($atName, ENT_QUOTES, 'UTF-8') ?>
                </option>
            <?php endforeach; ?>
        </select>
    </div>
<?php endif; ?>
