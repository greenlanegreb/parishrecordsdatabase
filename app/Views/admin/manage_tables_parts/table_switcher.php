<?php
declare(strict_types=1);
?>
<!-- Table Selector Bar & Quick Management -->
<?php if (!empty($tables)): ?>
<div class="card shadow-sm border-0 mb-4 bg-light">
    <div class="card-body d-flex justify-content-between align-items-center flex-wrap gap-3">
        <div>
            <label for="table_switcher" class="form-label fw-bold small"><?= htmlspecialchars(__('manage_tables.switcher_label'), ENT_QUOTES, 'UTF-8') ?></label>
            <select id="table_switcher" class="form-select" onchange="if(this.value) window.location.href='<?= $basePath ?>/admin/tables?table_id=' + this.value;">
                <?php foreach ($tables as $t): ?>
                    <?php 
                        $tId = isset($t['id']) ? (int)$t['id'] : 0;
                        $tName = isset($t['table_name']) && is_string($t['table_name']) ? $t['table_name'] : '';
                    ?>
                    <option value="<?= $tId ?>" <?= ($tId === $activeTableId) ? 'selected' : '' ?>>
                        <?= htmlspecialchars($tName, ENT_QUOTES, 'UTF-8') ?> (ID: <?= $tId ?>)
                    </option>
                <?php endforeach; ?>
            </select>
        </div>
      
        <?php if ($activeTableInfo): ?>
            <?php $activeTblId = isset($activeTableInfo['id']) ? (int)$activeTableInfo['id'] : 0; ?>
            <div class="d-flex gap-2 align-items-center">
                <a href="<?= $basePath ?>/admin/tables?edit_table=<?= $activeTblId ?>&table_id=<?= $activeTableId ?>" class="btn btn-sm btn-outline-secondary"><?= htmlspecialchars(__('manage_tables.edit_metadata_btn'), ENT_QUOTES, 'UTF-8') ?></a>
                <?php if ($activeTblId > 1): ?>
                    <form method="POST" action="<?= $basePath ?>/admin/tables" class="d-inline" onsubmit="return confirm('<?= htmlspecialchars(__('manage_tables.delete_table_confirm'), ENT_QUOTES, 'UTF-8') ?>');">
                        <?= csrf_field() ?>
                        <input type="hidden" name="action" value="delete_table">
                        <input type="hidden" name="table_id" value="<?= $activeTblId ?>">
                        <button type="submit" class="btn btn-sm btn-danger"><?= htmlspecialchars(__('manage_tables.delete_table_btn'), ENT_QUOTES, 'UTF-8') ?></button>
                    </form>
                <?php endif; ?>
            </div>
        <?php endif; ?>
    </div>
</div>
<?php endif; ?>
