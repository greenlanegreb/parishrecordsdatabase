<?php
declare(strict_types=1);
require_once ROOT_PATH . '/partials/header.php';
$basePath = defined('BASE_PATH') ? rtrim((string) BASE_PATH, '/') : '';
?>
<div class="container py-4" role="region" aria-labelledby="dupQueueHeading">
    <h1 class="h3 fw-bold mb-3" id="dupQueueHeading"><?= htmlspecialchars(__('dup_queue.heading') !== 'dup_queue.heading' ? __('dup_queue.heading') : 'Similar records to review', ENT_QUOTES, 'UTF-8') ?></h1>
    <p class="text-muted"><?= htmlspecialchars(__('dup_queue.intro') !== 'dup_queue.intro' ? __('dup_queue.intro') : 'Scan a table for rows that already look alike. You can say they are different, or join them into one record.', ENT_QUOTES, 'UTF-8') ?></p>

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
    </form>

    <?php if (empty($queue)): ?>
        <p><?= htmlspecialchars(__('dup_queue.empty') !== 'dup_queue.empty' ? __('dup_queue.empty') : 'Nothing waiting. Scan a table to look for older lookalikes.', ENT_QUOTES, 'UTF-8') ?></p>
    <?php else: ?>
        <div class="table-responsive">
            <table class="table align-middle">
                <caption class="visually-hidden"><?= htmlspecialchars(__('dup_queue.heading') !== 'dup_queue.heading' ? __('dup_queue.heading') : 'Similar records to review', ENT_QUOTES, 'UTF-8') ?></caption>
                <thead>
                    <tr>
                        <th><?= htmlspecialchars(__('dup_queue.col_table') !== 'dup_queue.col_table' ? __('dup_queue.col_table') : 'Table', ENT_QUOTES, 'UTF-8') ?></th>
                        <th><?= htmlspecialchars(__('dup_queue.col_records') !== 'dup_queue.col_records' ? __('dup_queue.col_records') : 'Records', ENT_QUOTES, 'UTF-8') ?></th>
                        <th><?= htmlspecialchars(__('dup_queue.col_score') !== 'dup_queue.col_score' ? __('dup_queue.col_score') : 'How similar', ENT_QUOTES, 'UTF-8') ?></th>
                        <th><?= htmlspecialchars(__('dup_queue.col_actions') !== 'dup_queue.col_actions' ? __('dup_queue.col_actions') : 'What to do', ENT_QUOTES, 'UTF-8') ?></th>
                    </tr>
                </thead>
                <tbody>
                    <?php foreach ($queue as $row): ?>
                        <tr>
                            <td><?= htmlspecialchars((string) ($row['table_name'] ?? ''), ENT_QUOTES, 'UTF-8') ?></td>
                            <td>#<?= (int) $row['record_a_id'] ?> · #<?= (int) $row['record_b_id'] ?></td>
                            <td><?= (int) $row['score_percent'] ?>%</td>
                            <td>
                                <div class="d-flex flex-wrap gap-2">
                                    <a class="btn btn-sm btn-primary" href="<?= htmlspecialchars($basePath, ENT_QUOTES, 'UTF-8') ?>/admin/duplicates/merge?id=<?= (int) $row['id'] ?>"><?= htmlspecialchars(__('dup_queue.merge_btn') !== 'dup_queue.merge_btn' ? __('dup_queue.merge_btn') : 'Join into one', ENT_QUOTES, 'UTF-8') ?></a>
                                    <form method="POST" action="<?= htmlspecialchars($basePath, ENT_QUOTES, 'UTF-8') ?>/admin/duplicates/dismiss">
                                        <?= csrf_field() ?>
                                        <input type="hidden" name="review_id" value="<?= (int) $row['id'] ?>">
                                        <button type="submit" class="btn btn-sm btn-outline-secondary"><?= htmlspecialchars(__('dup_queue.dismiss_btn') !== 'dup_queue.dismiss_btn' ? __('dup_queue.dismiss_btn') : 'Not a duplicate', ENT_QUOTES, 'UTF-8') ?></button>
                                    </form>
                                </div>
                            </td>
                        </tr>
                    <?php endforeach; ?>
                </tbody>
            </table>
        </div>
    <?php endif; ?>
</div>
<?php require_once ROOT_PATH . '/partials/footer.php'; ?>
