<?php
declare(strict_types=1);
?>
<?php
$duplicateMode = isset($duplicateMode) && is_string($duplicateMode)
    ? $duplicateMode
    : (isset($_SESSION['duplicate_mode']) && is_string($_SESSION['duplicate_mode']) ? $_SESSION['duplicate_mode'] : 'warn');
?>
<?php if (!empty($duplicateWarning) && !empty($matches)): ?>
    <div class="alert alert-warning shadow-sm border-0 mb-4 p-3 p-md-4"
         role="region"
         aria-labelledby="dupHeading"
         aria-describedby="dupHelp">
        <h2 class="h5 fw-bold mb-2" id="dupHeading"><?= htmlspecialchars(__('data_entry.dup_heading') !== 'data_entry.dup_heading' ? __('data_entry.dup_heading') : 'This looks similar to something already saved', ENT_QUOTES, 'UTF-8') ?></h2>
        <p class="mb-3" id="dupHelp"><?= htmlspecialchars(__('data_entry.dup_desc') !== 'data_entry.dup_desc' ? __('data_entry.dup_desc') : 'Please check the cards below. You can still save if this is a different record.', ENT_QUOTES, 'UTF-8') ?></p>

        <div class="row g-3 mb-3">
            <?php foreach ($matches as $match): ?>
                <?php
                    $mId = isset($match['id']) ? (int) $match['id'] : 0;
                    $bucket = isset($match['bucket']) ? (int) $match['bucket'] : 0;
                    $percent = isset($match['percent']) ? (int) $match['percent'] : $bucket;
                    if ($bucket < 25) {
                        $bucket = 25;
                    }
                    $overview = isset($match['overview']) && is_array($match['overview']) ? $match['overview'] : [];
                    $title = __('data_entry.dup_similar') !== 'data_entry.dup_similar'
                        ? sprintf(__('data_entry.dup_similar'), (string) $bucket, (string) $mId)
                        : sprintf('This looks %s%% similar to record #%s', (string) $bucket, (string) $mId);
                ?>
                <div class="col-12 col-md-6">
                    <article class="card h-100 border-0 shadow-sm" aria-labelledby="dupCard<?= $mId ?>">
                        <div class="card-body">
                            <h3 class="h6 fw-bold mb-3" id="dupCard<?= $mId ?>">
                                <span class="badge text-bg-warning me-1"><?= (int) $bucket ?>%</span>
                                <?= htmlspecialchars($title, ENT_QUOTES, 'UTF-8') ?>
                            </h3>
                            <p class="visually-hidden"><?= htmlspecialchars(
                                __('data_entry.dup_score_exact') !== 'data_entry.dup_score_exact'
                                    ? sprintf(__('data_entry.dup_score_exact'), (string) $percent)
                                    : sprintf('Calculated similarity %s percent.', (string) $percent),
                                ENT_QUOTES,
                                'UTF-8'
                            ) ?></p>
                            <?php if ($overview !== []): ?>
                                <p class="small text-muted text-uppercase fw-bold mb-2"><?= htmlspecialchars(__('data_entry.dup_overview') !== 'data_entry.dup_overview' ? __('data_entry.dup_overview') : 'Already saved', ENT_QUOTES, 'UTF-8') ?></p>
                                <dl class="row mb-0 small">
                                    <?php foreach ($overview as $field): ?>
                                        <?php
                                            $fl = isset($field['label']) && is_string($field['label']) ? $field['label'] : '';
                                            $fv = isset($field['value']) && is_string($field['value']) ? $field['value'] : '';
                                            $close = !empty($field['close']);
                                        ?>
                                        <dt class="col-5 col-sm-4"><?= htmlspecialchars($fl, ENT_QUOTES, 'UTF-8') ?></dt>
                                        <dd class="col-7 col-sm-8">
                                            <?php if ($fv === ''): ?>
                                                <span class="text-muted"><?= htmlspecialchars(__('data_entry.dup_empty') !== 'data_entry.dup_empty' ? __('data_entry.dup_empty') : '(empty)', ENT_QUOTES, 'UTF-8') ?></span>
                                            <?php else: ?>
                                                <?= htmlspecialchars($fv, ENT_QUOTES, 'UTF-8') ?>
                                                <?php if ($close): ?>
                                                    <span class="d-block text-muted"><?= htmlspecialchars(__('data_entry.dup_close_match') !== 'data_entry.dup_close_match' ? __('data_entry.dup_close_match') : 'Very similar spelling', ENT_QUOTES, 'UTF-8') ?></span>
                                                <?php endif; ?>
                                            <?php endif; ?>
                                        </dd>
                                    <?php endforeach; ?>
                                </dl>
                            <?php endif; ?>
                        </div>
                    </article>
                </div>
            <?php endforeach; ?>
        </div>

        <p class="mb-3"><?= htmlspecialchars(__('data_entry.dup_prompt') !== 'data_entry.dup_prompt' ? __('data_entry.dup_prompt') : 'If this is a different record, you can save it anyway.', ENT_QUOTES, 'UTF-8') ?></p>
        <form method="POST" action="<?= htmlspecialchars($basePath, ENT_QUOTES, 'UTF-8') ?>/data-entry">
            <?= csrf_field() ?>
            <input type="hidden" name="action" value="insert_record">
            <input type="hidden" name="table_id" value="<?= (int) $activeTableId ?>">
            <?php foreach ($submittedData as $cid => $cval): ?>
                <?php
                    $flat = is_array($cval)
                        ? implode(', ', array_map('strval', $cval))
                        : (string) $cval;
                ?>
                <input type="hidden" name="filters[<?= (int) $cid ?>]" value="<?= htmlspecialchars($flat, ENT_QUOTES, 'UTF-8') ?>">
            <?php endforeach; ?>
            <input type="hidden" name="confirm_duplicate" value="1">
            <div class="d-flex flex-wrap gap-2">
                <?php if ($duplicateMode !== 'block'): ?>
                <button type="submit" class="btn btn-warning"><?= htmlspecialchars(
                    $duplicateMode === 'flag'
                        ? (__('data_entry.dup_flag_btn') !== 'data_entry.dup_flag_btn' ? __('data_entry.dup_flag_btn') : 'Save and tell a moderator')
                        : (__('data_entry.dup_confirm_btn') !== 'data_entry.dup_confirm_btn' ? __('data_entry.dup_confirm_btn') : 'Save anyway'),
                    ENT_QUOTES,
                    'UTF-8'
                ) ?></button>
                <?php else: ?>
                <p class="mb-0 text-danger fw-bold" role="status"><?= htmlspecialchars(__('data_entry.dup_blocked') !== 'data_entry.dup_blocked' ? __('data_entry.dup_blocked') : 'This record is too similar to one already saved, so it cannot be added.', ENT_QUOTES, 'UTF-8') ?></p>
                <?php endif; ?>
                <a href="<?= htmlspecialchars($basePath, ENT_QUOTES, 'UTF-8') ?>/data-entry?table_id=<?= (int) $activeTableId ?>&amp;dismiss_duplicate=1" class="btn btn-outline-secondary text-decoration-none"><?= htmlspecialchars(__('data_entry.dup_review_btn') !== 'data_entry.dup_review_btn' ? __('data_entry.dup_review_btn') : 'Review what I typed', ENT_QUOTES, 'UTF-8') ?></a>
            </div>
        </form>
    </div>
<?php endif; ?>
