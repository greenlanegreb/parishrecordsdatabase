<?php
declare(strict_types=1);
require_once ROOT_PATH . '/partials/header.php';
$basePath = defined('BASE_PATH') ? rtrim((string) BASE_PATH, '/') : '';
$reviewId = (int) ($review['id'] ?? 0);
$aId = (int) ($review['record_a_id'] ?? 0);
$bId = (int) ($review['record_b_id'] ?? 0);
$score = (int) ($review['score_percent'] ?? 0);
$tableName = isset($review['table_name']) && is_string($review['table_name'])
    ? $review['table_name'] : '';
$fields = is_array($fields ?? null) ? $fields : [];

/**
 * @param array<string, mixed> $field
 */
$cellLabel = static function (array $field, string $side): string {
    $displayKey = $side === 'a' ? 'display_a' : 'display_b';
    $rawKey = $side === 'a' ? 'value_a' : 'value_b';
    $display = isset($field[$displayKey]) && is_string($field[$displayKey]) ? $field[$displayKey] : '';
    $raw = isset($field[$rawKey]) ? (string) $field[$rawKey] : '';
    $text = $display !== '' ? $display : $raw;
    return $text !== '' ? $text : '—';
};

$previewLine = static function (array $fieldList, string $side, int $limit = 3) use ($cellLabel): string {
    $bits = [];
    foreach ($fieldList as $field) {
        if (!is_array($field)) {
            continue;
        }
        $label = $cellLabel($field, $side);
        if ($label === '—') {
            continue;
        }
        $name = isset($field['column_name']) ? (string) $field['column_name'] : '';
        $bits[] = ($name !== '' ? $name . ': ' : '') . $label;
        if (count($bits) >= $limit) {
            break;
        }
    }
    return $bits !== [] ? implode(' · ', $bits) : '';
};
$previewA = $previewLine($fields, 'a');
$previewB = $previewLine($fields, 'b');
?>
<div class="container py-4" style="max-width: 960px;" role="region" aria-labelledby="dupMergeHeading">
    <h1 class="h3 fw-bold mb-2" id="dupMergeHeading"><?= htmlspecialchars(__('dup_merge.heading') !== 'dup_merge.heading' ? __('dup_merge.heading') : 'Join two similar records', ENT_QUOTES, 'UTF-8') ?></h1>
    <?php if ($tableName !== ''): ?>
        <p class="text-muted mb-1">
            <span class="fw-semibold"><?= htmlspecialchars(__('dup_merge.table_label') !== 'dup_merge.table_label' ? __('dup_merge.table_label') : 'Table', ENT_QUOTES, 'UTF-8') ?>:</span>
            <?= htmlspecialchars($tableName, ENT_QUOTES, 'UTF-8') ?>
        </p>
    <?php endif; ?>
    <p class="text-muted"><?= htmlspecialchars(sprintf(__('dup_merge.intro') !== 'dup_merge.intro' ? __('dup_merge.intro') : 'These two look about %s%% similar. Choose which record should remain, then for each field pick the value you want to keep. The other record is removed after you confirm.', (string) $score), ENT_QUOTES, 'UTF-8') ?></p>

    <form method="POST" action="<?= htmlspecialchars($basePath, ENT_QUOTES, 'UTF-8') ?>/admin/duplicates/merge">
        <?= csrf_field() ?>
        <input type="hidden" name="review_id" value="<?= $reviewId ?>">

        <fieldset class="mb-4 border rounded p-3 bg-light">
            <legend class="fw-bold float-none w-auto px-2 fs-6"><?= htmlspecialchars(__('dup_merge.keep_legend') !== 'dup_merge.keep_legend' ? __('dup_merge.keep_legend') : 'Which record should remain?', ENT_QUOTES, 'UTF-8') ?></legend>
            <p class="small text-muted mb-2"><?= htmlspecialchars(__('dup_merge.keep_help') !== 'dup_merge.keep_help' ? __('dup_merge.keep_help') : 'This is the row that stays in the table. You still choose field values below — they can come from either record.', ENT_QUOTES, 'UTF-8') ?></p>
            <div class="form-check mb-2">
                <input class="form-check-input" type="radio" name="keep_record_id" id="keep_a" value="<?= $aId ?>" required>
                <label class="form-check-label" for="keep_a">
                    <span class="fw-semibold"><?= htmlspecialchars(sprintf(__('dup_merge.keep_a') !== 'dup_merge.keep_a' ? __('dup_merge.keep_a') : 'Keep record #%s', (string) $aId), ENT_QUOTES, 'UTF-8') ?></span>
                    <?php if ($previewA !== ''): ?>
                        <span class="d-block small text-muted"><?= htmlspecialchars($previewA, ENT_QUOTES, 'UTF-8') ?></span>
                    <?php endif; ?>
                </label>
            </div>
            <div class="form-check">
                <input class="form-check-input" type="radio" name="keep_record_id" id="keep_b" value="<?= $bId ?>">
                <label class="form-check-label" for="keep_b">
                    <span class="fw-semibold"><?= htmlspecialchars(sprintf(__('dup_merge.keep_b') !== 'dup_merge.keep_b' ? __('dup_merge.keep_b') : 'Keep record #%s', (string) $bId), ENT_QUOTES, 'UTF-8') ?></span>
                    <?php if ($previewB !== ''): ?>
                        <span class="d-block small text-muted"><?= htmlspecialchars($previewB, ENT_QUOTES, 'UTF-8') ?></span>
                    <?php endif; ?>
                </label>
            </div>
        </fieldset>

        <h2 class="h5 fw-bold mb-2"><?= htmlspecialchars(__('dup_merge.fields_heading') !== 'dup_merge.fields_heading' ? __('dup_merge.fields_heading') : 'Choose a value for each field', ENT_QUOTES, 'UTF-8') ?></h2>
        <p class="small text-muted"><?= htmlspecialchars(__('dup_merge.fields_help') !== 'dup_merge.fields_help' ? __('dup_merge.fields_help') : 'Yellow rows differ between the two records. Matching rows are already the same either way.', ENT_QUOTES, 'UTF-8') ?></p>

        <?php if ($fields === []): ?>
            <p class="text-muted"><?= htmlspecialchars(__('dup_merge.no_fields') !== 'dup_merge.no_fields' ? __('dup_merge.no_fields') : 'No field values were found for these records.', ENT_QUOTES, 'UTF-8') ?></p>
        <?php else: ?>
            <div class="table-responsive mb-3">
                <table class="table table-bordered align-middle">
                    <caption class="visually-hidden"><?= htmlspecialchars(__('dup_merge.fields_caption') !== 'dup_merge.fields_caption' ? __('dup_merge.fields_caption') : 'Choose a value for each field', ENT_QUOTES, 'UTF-8') ?></caption>
                    <thead class="table-light">
                        <tr>
                            <th scope="col"><?= htmlspecialchars(__('dup_merge.col_field') !== 'dup_merge.col_field' ? __('dup_merge.col_field') : 'Field', ENT_QUOTES, 'UTF-8') ?></th>
                            <th scope="col"><?= htmlspecialchars(sprintf(__('dup_merge.col_record_a') !== 'dup_merge.col_record_a' ? __('dup_merge.col_record_a') : 'Record #%s', (string) $aId), ENT_QUOTES, 'UTF-8') ?></th>
                            <th scope="col"><?= htmlspecialchars(sprintf(__('dup_merge.col_record_b') !== 'dup_merge.col_record_b' ? __('dup_merge.col_record_b') : 'Record #%s', (string) $bId), ENT_QUOTES, 'UTF-8') ?></th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php foreach ($fields as $field): ?>
                            <?php
                                if (!is_array($field)) {
                                    continue;
                                }
                                $cid = (int) ($field['id'] ?? 0);
                                $name = isset($field['column_name']) ? (string) $field['column_name'] : '';
                                $rawA = isset($field['value_a']) ? (string) $field['value_a'] : '';
                                $rawB = isset($field['value_b']) ? (string) $field['value_b'] : '';
                                $labelA = $cellLabel($field, 'a');
                                $labelB = $cellLabel($field, 'b');
                                $diff = ($rawA !== $rawB);
                                // Default pick: same → A; only one side filled → that side; both differ → A (still changeable)
                                $checkA = true;
                                if ($diff && $rawA === '' && $rawB !== '') {
                                    $checkA = false;
                                }
                            ?>
                            <tr class="<?= $diff ? 'table-warning' : '' ?>">
                                <th scope="row" class="text-nowrap"><?= htmlspecialchars($name, ENT_QUOTES, 'UTF-8') ?></th>
                                <td>
                                    <div class="form-check">
                                        <input class="form-check-input" type="radio"
                                               name="keep_col[<?= $cid ?>]"
                                               id="col<?= $cid ?>a"
                                               value="a"
                                               <?= $checkA ? 'checked' : '' ?>
                                               required>
                                        <label class="form-check-label" for="col<?= $cid ?>a"><?= htmlspecialchars($labelA, ENT_QUOTES, 'UTF-8') ?></label>
                                    </div>
                                </td>
                                <td>
                                    <div class="form-check">
                                        <input class="form-check-input" type="radio"
                                               name="keep_col[<?= $cid ?>]"
                                               id="col<?= $cid ?>b"
                                               value="b"
                                               <?= !$checkA ? 'checked' : '' ?>>
                                        <label class="form-check-label" for="col<?= $cid ?>b"><?= htmlspecialchars($labelB, ENT_QUOTES, 'UTF-8') ?></label>
                                    </div>
                                </td>
                            </tr>
                        <?php endforeach; ?>
                    </tbody>
                </table>
            </div>
        <?php endif; ?>

        <div class="d-flex flex-wrap gap-2">
            <button type="submit" class="btn btn-primary"
                    onclick="return confirm(<?= json_encode(__('dup_merge.confirm') !== 'dup_merge.confirm' ? __('dup_merge.confirm') : 'Join these into one record? The other record will be removed permanently.', JSON_HEX_TAG | JSON_HEX_APOS | JSON_HEX_QUOT | JSON_UNESCAPED_UNICODE) ?>);">
                <?= htmlspecialchars(__('dup_merge.save_btn') !== 'dup_merge.save_btn' ? __('dup_merge.save_btn') : 'Join into one record', ENT_QUOTES, 'UTF-8') ?>
            </button>
            <a class="btn btn-outline-secondary text-decoration-none" href="<?= htmlspecialchars($basePath, ENT_QUOTES, 'UTF-8') ?>/admin/duplicates">
                <?= htmlspecialchars(__('btn.cancel') !== 'btn.cancel' ? __('btn.cancel') : 'Cancel', ENT_QUOTES, 'UTF-8') ?>
            </a>
        </div>
    </form>
</div>
<?php require_once ROOT_PATH . '/partials/footer.php'; ?>
