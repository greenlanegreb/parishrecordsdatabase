<?php
declare(strict_types=1);
require_once ROOT_PATH . '/partials/header.php';
$basePath = defined('BASE_PATH') ? rtrim((string) BASE_PATH, '/') : '';
$reviewId = (int) ($review['id'] ?? 0);
$aId = (int) ($review['record_a_id'] ?? 0);
$bId = (int) ($review['record_b_id'] ?? 0);
$score = (int) ($review['score_percent'] ?? 0);
?>
<div class="container py-4" role="region" aria-labelledby="dupMergeHeading">
    <h1 class="h3 fw-bold mb-2" id="dupMergeHeading"><?= htmlspecialchars(__('dup_merge.heading') !== 'dup_merge.heading' ? __('dup_merge.heading') : 'Join two similar records', ENT_QUOTES, 'UTF-8') ?></h1>
    <p class="text-muted"><?= htmlspecialchars(sprintf(__('dup_merge.intro') !== 'dup_merge.intro' ? __('dup_merge.intro') : 'These two look about %s%% similar. Choose which record to keep, then pick a value for each field.', (string) $score), ENT_QUOTES, 'UTF-8') ?></p>

    <form method="POST" action="<?= htmlspecialchars($basePath, ENT_QUOTES, 'UTF-8') ?>/admin/duplicates/merge">
        <?= csrf_field() ?>
        <input type="hidden" name="review_id" value="<?= $reviewId ?>">

        <fieldset class="mb-4">
            <legend class="fw-bold"><?= htmlspecialchars(__('dup_merge.keep_legend') !== 'dup_merge.keep_legend' ? __('dup_merge.keep_legend') : 'Which record should remain?', ENT_QUOTES, 'UTF-8') ?></legend>
            <div class="form-check">
                <input class="form-check-input" type="radio" name="keep_record_id" id="keep_a" value="<?= $aId ?>" required>
                <label class="form-check-label" for="keep_a"><?= htmlspecialchars(sprintf(__('dup_merge.keep_a') !== 'dup_merge.keep_a' ? __('dup_merge.keep_a') : 'Keep record #%s', (string) $aId), ENT_QUOTES, 'UTF-8') ?></label>
            </div>
            <div class="form-check">
                <input class="form-check-input" type="radio" name="keep_record_id" id="keep_b" value="<?= $bId ?>">
                <label class="form-check-label" for="keep_b"><?= htmlspecialchars(sprintf(__('dup_merge.keep_b') !== 'dup_merge.keep_b' ? __('dup_merge.keep_b') : 'Keep record #%s', (string) $bId), ENT_QUOTES, 'UTF-8') ?></label>
            </div>
        </fieldset>

        <div class="table-responsive mb-3">
            <table class="table">
                <caption class="visually-hidden"><?= htmlspecialchars(__('dup_merge.fields_caption') !== 'dup_merge.fields_caption' ? __('dup_merge.fields_caption') : 'Choose a value for each field', ENT_QUOTES, 'UTF-8') ?></caption>
                <thead>
                    <tr>
                        <th><?= htmlspecialchars(__('dup_merge.col_field') !== 'dup_merge.col_field' ? __('dup_merge.col_field') : 'Field', ENT_QUOTES, 'UTF-8') ?></th>
                        <th><?= htmlspecialchars(sprintf(__('dup_merge.col_a') !== 'dup_merge.col_a' ? __('dup_merge.col_a') : 'Record #%s', (string) $aId), ENT_QUOTES, 'UTF-8') ?></th>
                        <th><?= htmlspecialchars(sprintf(__('dup_merge.col_b') !== 'dup_merge.col_b' ? __('dup_merge.col_b') : 'Record #%s', (string) $bId), ENT_QUOTES, 'UTF-8') ?></th>
                    </tr>
                </thead>
                <tbody>
                    <?php foreach ($fields as $field): ?>
                        <?php $cid = (int) $field['id']; ?>
                        <tr>
                            <th scope="row"><?= htmlspecialchars((string) $field['column_name'], ENT_QUOTES, 'UTF-8') ?></th>
                            <td>
                                <div class="form-check">
                                    <input class="form-check-input" type="radio" name="keep_col[<?= $cid ?>]" id="col<?= $cid ?>a" value="a" checked>
                                    <label class="form-check-label" for="col<?= $cid ?>a"><?= htmlspecialchars((string) $field['value_a'] !== '' ? (string) $field['value_a'] : '—', ENT_QUOTES, 'UTF-8') ?></label>
                                </div>
                            </td>
                            <td>
                                <div class="form-check">
                                    <input class="form-check-input" type="radio" name="keep_col[<?= $cid ?>]" id="col<?= $cid ?>b" value="b">
                                    <label class="form-check-label" for="col<?= $cid ?>b"><?= htmlspecialchars((string) $field['value_b'] !== '' ? (string) $field['value_b'] : '—', ENT_QUOTES, 'UTF-8') ?></label>
                                </div>
                            </td>
                        </tr>
                    <?php endforeach; ?>
                </tbody>
            </table>
        </div>

        <div class="d-flex flex-wrap gap-2">
            <button type="submit" class="btn btn-primary" onclick="return confirm('<?= htmlspecialchars(__('dup_merge.confirm') !== 'dup_merge.confirm' ? __('dup_merge.confirm') : 'Join these into one record? The other record will be removed.', ENT_QUOTES, 'UTF-8') ?>');"><?= htmlspecialchars(__('dup_merge.save_btn') !== 'dup_merge.save_btn' ? __('dup_merge.save_btn') : 'Join into one record', ENT_QUOTES, 'UTF-8') ?></button>
            <a class="btn btn-outline-secondary text-decoration-none" href="<?= htmlspecialchars($basePath, ENT_QUOTES, 'UTF-8') ?>/admin/moderation?tab=similar"><?= htmlspecialchars(__('btn.cancel') !== 'btn.cancel' ? __('btn.cancel') : 'Cancel', ENT_QUOTES, 'UTF-8') ?></a>
        </div>
    </form>
</div>
<?php require_once ROOT_PATH . '/partials/footer.php'; ?>
