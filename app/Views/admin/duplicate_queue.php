<?php
declare(strict_types=1);
require_once ROOT_PATH . '/partials/header.php';
$basePath = defined('BASE_PATH') ? rtrim((string) BASE_PATH, '/') : '';
$queue = $queue ?? [];
$tables = $tables ?? [];
$snippet = __DIR__ . '/duplicate_compare_snippet.php';
?>
<div class="container py-4" role="region" aria-labelledby="dupQueueHeading">
    <h1 class="h3 fw-bold mb-3" id="dupQueueHeading"><?= htmlspecialchars(__('dup_queue.heading') !== 'dup_queue.heading' ? __('dup_queue.heading') : 'Similar records to review', ENT_QUOTES, 'UTF-8') ?></h1>
    <p class="text-muted"><?= htmlspecialchars(__('dup_queue.intro') !== 'dup_queue.intro' ? __('dup_queue.intro') : 'Scan a table for rows that already look alike. Compare the values below, mark them as different, or join them into one record.', ENT_QUOTES, 'UTF-8') ?></p>

    <?php if (!empty($error)): ?>
        <div class="alert alert-danger" role="alert"><?= htmlspecialchars((string) $error, ENT_QUOTES, 'UTF-8') ?></div>
    <?php endif; ?>
    <?php if (!empty($message)): ?>
        <div class="alert alert-success" role="status"><?= htmlspecialchars((string) $message, ENT_QUOTES, 'UTF-8') ?></div>
    <?php endif; ?>

    <form method="POST" action="<?= htmlspecialchars($basePath, ENT_QUOTES, 'UTF-8') ?>/admin/duplicates/scan" class="card border-0 shadow-sm p-3 mb-4">
        <?= csrf_field() ?>
        <label class="form-label fw-bold" for="scan_table"><?= htmlspecialchars(__('dup_queue.scan_label') !== 'dup_queue.scan_label' ? __('dup_queue.scan_label') : 'Look for similar records in', ENT_QUOTES, 'UTF-8') ?></label>
        <div class="d-flex flex-wrap gap-2 align-items-end">
            <select name="table_id" id="scan_table" class="form-select" style="max-width: 20rem;" required>
                <option value=""><?= htmlspecialchars(__('dup_queue.choose_table') !== 'dup_queue.choose_table' ? __('dup_queue.choose_table') : 'Choose a table', ENT_QUOTES, 'UTF-8') ?></option>
                <?php foreach ($tables as $t): ?>
                    <option value="<?= (int) $t['id'] ?>"><?= htmlspecialchars((string) $t['table_name'], ENT_QUOTES, 'UTF-8') ?></option>
                <?php endforeach; ?>
            </select>
            <button type="submit" class="btn btn-primary"><?= htmlspecialchars(__('dup_queue.scan_btn') !== 'dup_queue.scan_btn' ? __('dup_queue.scan_btn') : 'Scan this table', ENT_QUOTES, 'UTF-8') ?></button>
        </div>
        <p class="form-text mb-0 mt-2"><?= htmlspecialchars(__('dup_queue.scan_help') !== 'dup_queue.scan_help' ? __('dup_queue.scan_help') : 'If this list is empty, choose a table and scan. Only pending pairs appear here.', ENT_QUOTES, 'UTF-8') ?></p>
    </form>

    <?php if (empty($queue)): ?>
        <p class="text-muted"><?= htmlspecialchars(__('dup_queue.empty') !== 'dup_queue.empty' ? __('dup_queue.empty') : 'Nothing waiting. Scan a table above to look for lookalikes.', ENT_QUOTES, 'UTF-8') ?></p>
    <?php else: ?>
        <div class="row g-3">
            <?php foreach ($queue as $row): ?>
                <div class="col-12">
                    <article class="card border-0 shadow-sm">
                        <div class="card-body">
                            <div class="d-flex flex-wrap justify-content-between align-items-start gap-2 mb-2">
                                <h2 class="h6 fw-bold mb-0"><?= htmlspecialchars((string) ($row['table_name'] ?? ''), ENT_QUOTES, 'UTF-8') ?></h2>
                                <span class="badge text-bg-warning"><?= (int) ($row['score_percent'] ?? 0) ?>% <?= htmlspecialchars(__('dup_queue.similar') !== 'dup_queue.similar' ? __('dup_queue.similar') : 'similar', ENT_QUOTES, 'UTF-8') ?></span>
                            </div>
                            <?php require $snippet; ?>
                            <div class="d-flex flex-wrap gap-2 mt-2">
                                <a class="btn btn-sm btn-primary" href="<?= htmlspecialchars($basePath, ENT_QUOTES, 'UTF-8') ?>/admin/duplicates/merge?id=<?= (int) $row['id'] ?>"><?= htmlspecialchars(__('dup_queue.merge_btn') !== 'dup_queue.merge_btn' ? __('dup_queue.merge_btn') : 'Review and join', ENT_QUOTES, 'UTF-8') ?></a>
                                <form method="POST" action="<?= htmlspecialchars($basePath, ENT_QUOTES, 'UTF-8') ?>/admin/duplicates/dismiss" class="d-inline">
                                    <?= csrf_field() ?>
                                    <input type="hidden" name="review_id" value="<?= (int) $row['id'] ?>">
                                    <button type="submit" class="btn btn-sm btn-outline-secondary"><?= htmlspecialchars(__('dup_queue.dismiss_btn') !== 'dup_queue.dismiss_btn' ? __('dup_queue.dismiss_btn') : 'Not a duplicate', ENT_QUOTES, 'UTF-8') ?></button>
                                </form>
                            </div>
                        </div>
                    </article>
                </div>
            <?php endforeach; ?>
        </div>
    <?php endif; ?>
</div>
<?php require_once ROOT_PATH . '/partials/footer.php'; ?>
