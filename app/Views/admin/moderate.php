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

require_once ROOT_PATH . '/partials/header.php';
?>

<div class="container py-4" style="max-width: 1200px;">
    <?php if (!empty($error)): ?>
        <div class="alert alert-danger alert-dismissible fade show" role="alert">
            <strong><?= htmlspecialchars($error, ENT_QUOTES, 'UTF-8') ?></strong>
            <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
        </div>
    <?php endif; ?>
    <?php if (!empty($message)): ?>
        <div class="alert alert-success alert-dismissible fade show" role="alert">
            <strong><?= htmlspecialchars($message, ENT_QUOTES, 'UTF-8') ?></strong>
            <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
        </div>
    <?php endif; ?>

    <h3 class="fw-bold mb-1"><?= htmlspecialchars(__('moderate.heading'), ENT_QUOTES, 'UTF-8') ?></h3>
    <p class="text-muted mb-3"><?= htmlspecialchars(__('moderate.subheading'), ENT_QUOTES, 'UTF-8') ?></p>

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

<?php require_once ROOT_PATH . '/partials/footer.php'; ?>
