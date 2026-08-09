<?php
declare(strict_types=1);
?>
<?php if ($duplicateWarning): ?>
    <div class="alert alert-danger shadow-sm border-0 mb-4 p-4">
        <h4 class="h5 fw-bold mb-2"><?= htmlspecialchars(__('data_entry.dup_heading'), ENT_QUOTES, 'UTF-8') ?></h4>
        <p class="mb-3"><?= htmlspecialchars(__('data_entry.dup_desc'), ENT_QUOTES, 'UTF-8') ?></p>
        <ul class="mb-3">
            <?php foreach ($matches as $match): ?>
                <?php 
                    $mId = isset($match['id']) ? (int)$match['id'] : 0;
                    $mVal = isset($match['value_content']) && is_string($match['value_content']) ? $match['value_content'] : '';
                ?>
                <li><?= htmlspecialchars(sprintf(__('data_entry.dup_item_format'), $mId, $mVal), ENT_QUOTES, 'UTF-8') ?></li>
            <?php endforeach; ?>
        </ul>
        <p class="mb-3"><?= htmlspecialchars(__('data_entry.dup_prompt'), ENT_QUOTES, 'UTF-8') ?></p>
        <form method="POST" action="<?= $basePath ?>/data-entry">
            <?= csrf_field() ?>
            <input type="hidden" name="action" value="insert_record">
            <input type="hidden" name="table_id" value="<?= $activeTableId ?>">
            <?php foreach ($submittedData as $cid => $cval): ?>
                <input type="hidden" name="filters[<?= (int)$cid ?>]" value="<?= htmlspecialchars((string)$cval, ENT_QUOTES, 'UTF-8') ?>">
            <?php endforeach; ?>
            <input type="hidden" name="confirm_duplicate" value="1">
            <button type="submit" class="btn btn-sm btn-danger"><?= htmlspecialchars(__('data_entry.dup_confirm_btn'), ENT_QUOTES, 'UTF-8') ?></button>
            <a href="<?= $basePath ?>/data-entry?table_id=<?= $activeTableId ?>" class="btn btn-sm btn-secondary ms-2 text-decoration-none"><?= htmlspecialchars(__('btn.cancel'), ENT_QUOTES, 'UTF-8') ?></a>
        </form>
    </div>
<?php endif; ?>
