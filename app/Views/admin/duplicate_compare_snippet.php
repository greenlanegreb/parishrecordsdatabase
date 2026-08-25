<?php
declare(strict_types=1);
/**
 * Compact side-by-side field values for a duplicate_reviews queue row.
 * Expects $row with optional field_compare list from DuplicateReviewService::enrichQueueRow.
 */
$fields = isset($row['field_compare']) && is_array($row['field_compare']) ? $row['field_compare'] : [];
$recA = (int) ($row['record_a_id'] ?? 0);
$recB = (int) ($row['record_b_id'] ?? 0);
?>
<?php if ($fields === []): ?>
    <p class="small text-muted mb-0">#<?= $recA ?> · #<?= $recB ?>
        <span class="d-block"><?= htmlspecialchars(__('dup_queue.no_field_preview') !== 'dup_queue.no_field_preview' ? __('dup_queue.no_field_preview') : 'Field values could not be loaded for this pair. Open Review and join to compare.', ENT_QUOTES, 'UTF-8') ?></span>
    </p>
<?php else: ?>
    <div class="table-responsive">
        <table class="table table-sm table-bordered mb-2" style="font-size: 0.85rem;">
            <caption class="visually-hidden"><?= htmlspecialchars(__('dup_queue.compare_caption') !== 'dup_queue.compare_caption' ? __('dup_queue.compare_caption') : 'Field values for both records', ENT_QUOTES, 'UTF-8') ?></caption>
            <thead>
                <tr>
                    <th scope="col"><?= htmlspecialchars(__('dup_queue.col_field') !== 'dup_queue.col_field' ? __('dup_queue.col_field') : 'Field', ENT_QUOTES, 'UTF-8') ?></th>
                    <th scope="col">#<?= $recA ?></th>
                    <th scope="col">#<?= $recB ?></th>
                </tr>
            </thead>
            <tbody>
                <?php foreach ($fields as $f): ?>
                    <?php
                        $name = isset($f['column_name']) ? (string) $f['column_name'] : '';
                        $rawA = isset($f['value_a']) ? (string) $f['value_a'] : '';
                        $rawB = isset($f['value_b']) ? (string) $f['value_b'] : '';
                        $va = isset($f['display_a']) && is_string($f['display_a']) && $f['display_a'] !== ''
                            ? $f['display_a']
                            : $rawA;
                        $vb = isset($f['display_b']) && is_string($f['display_b']) && $f['display_b'] !== ''
                            ? $f['display_b']
                            : $rawB;
                        // Prefer raw for "same or different" so formatting does not hide real differences
                        $diff = ($rawA !== $rawB);
                    ?>
                    <tr class="<?= $diff ? 'table-warning' : '' ?>">
                        <th scope="row" class="text-nowrap"><?= htmlspecialchars($name, ENT_QUOTES, 'UTF-8') ?></th>
                        <td><?= htmlspecialchars($va !== '' ? $va : '—', ENT_QUOTES, 'UTF-8') ?></td>
                        <td><?= htmlspecialchars($vb !== '' ? $vb : '—', ENT_QUOTES, 'UTF-8') ?></td>
                    </tr>
                <?php endforeach; ?>
            </tbody>
        </table>
    </div>
<?php endif; ?>
