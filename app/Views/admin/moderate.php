<?php
/**
 * MIGRATED FILE MAPPING
 * ---------------------
 * Original Old File: admin/moderate.php
 * Migrated Date: 2026-08-04 10:05:22
 */
declare(strict_types=1);

/** @string $message */
/** @string $error */
/** @string $userTimezone */
/** @string $fullFormatStr */
/** @array{id: int, username: string, date_format?: string} $currentUser */
/** @array<int, array<string, mixed>> $pendingSuggestions */
/** @array<int, array<string, mixed>> $dupQueue */
/** @array<int, array<string, mixed>> $dupTables */
/** @bool $dupTab */

require_once ROOT_PATH . '/partials/header.php';
$basePath = defined('BASE_PATH') ? rtrim((string) BASE_PATH, '/') : '';
$dupQueue = $dupQueue ?? [];
$dupTables = $dupTables ?? [];
$dupTab = !empty($dupTab);
?>

<div class="container py-4" style="max-width: 1200px;">
    <?php if (!empty($error)): ?>
        <div class="alert alert-danger alert-dismissible fade show" role="alert">
            <strong><?= htmlspecialchars($error, ENT_QUOTES, 'UTF-8') ?></strong>
            <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="<?= htmlspecialchars(__('btn.close') !== 'btn.close' ? __('btn.close') : 'Close', ENT_QUOTES, 'UTF-8') ?>"></button>
        </div>
    <?php endif; ?>
    <?php if (!empty($message)): ?>
        <div class="alert alert-success alert-dismissible fade show" role="alert">
            <strong><?= htmlspecialchars($message, ENT_QUOTES, 'UTF-8') ?></strong>
            <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="<?= htmlspecialchars(__('btn.close') !== 'btn.close' ? __('btn.close') : 'Close', ENT_QUOTES, 'UTF-8') ?>"></button>
        </div>
    <?php endif; ?>

    <h3 class="fw-bold mb-1"><?= htmlspecialchars(__('moderate.heading'), ENT_QUOTES, 'UTF-8') ?></h3>
    <p class="text-muted mb-3"><?= htmlspecialchars(__('moderate.subheading'), ENT_QUOTES, 'UTF-8') ?></p>

    <ul class="nav nav-pills mb-3" role="tablist">
        <li class="nav-item" role="presentation">
            <button type="button" class="nav-link <?= $dupTab ? '' : 'active' ?>" id="tab-suggestions" data-bs-toggle="tab" data-bs-target="#panel-suggestions" role="tab" aria-controls="panel-suggestions" aria-selected="<?= $dupTab ? 'false' : 'true' ?>">
                <?= htmlspecialchars(__('moderate.tab_suggestions') !== 'moderate.tab_suggestions' ? __('moderate.tab_suggestions') : 'Suggestions', ENT_QUOTES, 'UTF-8') ?>
                <span class="badge text-bg-secondary ms-1"><?= count($pendingSuggestions) ?></span>
            </button>
        </li>
        <li class="nav-item" role="presentation">
            <button type="button" class="nav-link <?= $dupTab ? 'active' : '' ?>" id="tab-similar" data-bs-toggle="tab" data-bs-target="#panel-similar" role="tab" aria-controls="panel-similar" aria-selected="<?= $dupTab ? 'true' : 'false' ?>">
                <?= htmlspecialchars(__('moderate.tab_similar') !== 'moderate.tab_similar' ? __('moderate.tab_similar') : 'Similar records', ENT_QUOTES, 'UTF-8') ?>
                <span class="badge text-bg-secondary ms-1"><?= count($dupQueue) ?></span>
            </button>
        </li>
    </ul>

    <div class="tab-content">
        <div class="tab-pane fade <?= $dupTab ? '' : 'show active' ?>" id="panel-suggestions" role="tabpanel" aria-labelledby="tab-suggestions">
            <div class="alert alert-primary border-start border-4 border-primary shadow-sm mb-4">
                <p class="mb-0 small text-dark">
                    ⚡ <strong><?= htmlspecialchars(__('moderate.shortcut_label'), ENT_QUOTES, 'UTF-8') ?></strong> <?= htmlspecialchars(__('moderate.shortcut_desc'), ENT_QUOTES, 'UTF-8') ?>
                </p>
            </div>

            <div class="card shadow-sm border-0">
                <div class="table-responsive">
                    <table class="table table-hover align-middle mb-0" role="table">
                        <thead class="table-light">
                            <tr>
                                <th scope="col" class="py-3 ps-3"><?= htmlspecialchars(__('moderate.th_id_date'), ENT_QUOTES, 'UTF-8') ?></th>
                                <th scope="col" class="py-3"><?= htmlspecialchars(__('moderate.th_table_record'), ENT_QUOTES, 'UTF-8') ?></th>
                                <th scope="col" class="py-3"><?= htmlspecialchars(__('moderate.th_comparison'), ENT_QUOTES, 'UTF-8') ?></th>
                                <th scope="col" class="py-3 pe-3 text-end"><?= htmlspecialchars(__('moderate.th_actions'), ENT_QUOTES, 'UTF-8') ?></th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php if (empty($pendingSuggestions)): ?>
                                <tr>
                                    <td colspan="4" class="text-center py-4 text-muted"><?= htmlspecialchars(__('moderate.no_suggestions'), ENT_QUOTES, 'UTF-8') ?></td>
                                </tr>
                            <?php else: ?>
                                <?php foreach ($pendingSuggestions as $s): ?>
                                    <?php require __DIR__ . '/moderate_parts/suggestion_row.php'; ?>
                                <?php endforeach; ?>
                            <?php endif; ?>
                        </tbody>
                    </table>
                </div>
            </div>
        </div>

        <div class="tab-pane fade <?= $dupTab ? 'show active' : '' ?>" id="panel-similar" role="tabpanel" aria-labelledby="tab-similar">
            <p class="text-muted"><?= htmlspecialchars(__('dup_queue.intro') !== 'dup_queue.intro' ? __('dup_queue.intro') : 'Scan a table for rows that already look alike. You can say they are different, or join them into one record.', ENT_QUOTES, 'UTF-8') ?></p>

            <form method="POST" action="<?= htmlspecialchars($basePath, ENT_QUOTES, 'UTF-8') ?>/admin/duplicates/scan" class="card border-0 shadow-sm p-3 mb-4">
                <?= csrf_field() ?>
                <label class="form-label fw-bold" for="scan_table"><?= htmlspecialchars(__('dup_queue.scan_label') !== 'dup_queue.scan_label' ? __('dup_queue.scan_label') : 'Look for similar records in', ENT_QUOTES, 'UTF-8') ?></label>
                <div class="d-flex flex-wrap gap-2 align-items-end">
                    <select name="table_id" id="scan_table" class="form-select" style="max-width: 20rem;" required>
                        <option value=""><?= htmlspecialchars(__('dup_queue.choose_table') !== 'dup_queue.choose_table' ? __('dup_queue.choose_table') : 'Choose a table', ENT_QUOTES, 'UTF-8') ?></option>
                        <?php foreach ($dupTables as $t): ?>
                            <option value="<?= (int) $t['id'] ?>"><?= htmlspecialchars((string) $t['table_name'], ENT_QUOTES, 'UTF-8') ?></option>
                        <?php endforeach; ?>
                    </select>
                    <button type="submit" class="btn btn-primary"><?= htmlspecialchars(__('dup_queue.scan_btn') !== 'dup_queue.scan_btn' ? __('dup_queue.scan_btn') : 'Scan this table', ENT_QUOTES, 'UTF-8') ?></button>
                </div>
            </form>

            <?php if (empty($dupQueue)): ?>
                <p><?= htmlspecialchars(__('dup_queue.empty') !== 'dup_queue.empty' ? __('dup_queue.empty') : 'Nothing waiting. Scan a table to look for older lookalikes.', ENT_QUOTES, 'UTF-8') ?></p>
            <?php else: ?>
                <div class="row g-3">
                    <?php foreach ($dupQueue as $row): ?>
                        <div class="col-12 col-md-6">
                            <article class="card h-100 border-0 shadow-sm">
                                <div class="card-body">
                                    <h2 class="h6 fw-bold"><?= htmlspecialchars((string) ($row['table_name'] ?? ''), ENT_QUOTES, 'UTF-8') ?></h2>
                                    <p class="mb-2">#<?= (int) $row['record_a_id'] ?> · #<?= (int) $row['record_b_id'] ?></p>
                                    <p class="mb-3"><span class="badge text-bg-warning"><?= (int) $row['score_percent'] ?>%</span></p>
                                    <div class="d-flex flex-wrap gap-2">
                                        <a class="btn btn-sm btn-primary" href="<?= htmlspecialchars($basePath, ENT_QUOTES, 'UTF-8') ?>/admin/duplicates/merge?id=<?= (int) $row['id'] ?>"><?= htmlspecialchars(__('dup_queue.merge_btn') !== 'dup_queue.merge_btn' ? __('dup_queue.merge_btn') : 'Join into one', ENT_QUOTES, 'UTF-8') ?></a>
                                        <form method="POST" action="<?= htmlspecialchars($basePath, ENT_QUOTES, 'UTF-8') ?>/admin/duplicates/dismiss">
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
    </div>
</div>

<script>
document.addEventListener('DOMContentLoaded', () => {
    document.querySelectorAll('.moderation-form').forEach(form => {
        form.addEventListener('keydown', (e) => {
            if ((e.ctrlKey || e.metaKey) && e.key === 'Enter') {
                e.preventDefault();
                const approveBtn = form.querySelector('.approve-btn');
                if (approveBtn && confirm('<?= htmlspecialchars(__('moderate.approve_confirm'), ENT_QUOTES, 'UTF-8') ?>')) {
                    const actionInput = document.createElement('input');
                    actionInput.type = 'hidden';
                    actionInput.name = 'action';
                    actionInput.value = 'approve';
                    form.appendChild(actionInput);
                    form.submit();
                }
            }
            if (e.key === 'Escape' && e.target.tagName === 'INPUT' && e.target.type === 'text') {
                e.preventDefault();
                e.target.value = '';
            }
        });
    });
});
</script>

<?php require_once ROOT_PATH . '/partials/date_input_script.php'; ?>
<?php require_once ROOT_PATH . '/partials/footer.php'; ?>
