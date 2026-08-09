<?php
declare(strict_types=1);

// Fallbacks in case the controller does not pass these
$userTimezone  = $userTimezone  ?? 'UTC';
$fullFormatStr = $fullFormatStr ?? 'd/m/Y H:i';
?>
<h3 class="h5 fw-bold mb-3"><?= htmlspecialchars(__('data_entry.existing_records_heading'), ENT_QUOTES, 'UTF-8') ?></h3>

<div class="card shadow-sm border-0 mb-4">
    <div class="table-responsive">
        <table class="table table-hover align-middle mb-0" id="data-entry-table" role="table">
            <thead class="table-light">
                <tr>
                    <?php foreach ($columns as $col): ?>
                        <?php $cName = isset($col['column_name']) && is_string($col['column_name']) ? $col['column_name'] : ''; ?>
                        <th scope="col" class="py-3"><?= htmlspecialchars($cName, ENT_QUOTES, 'UTF-8') ?></th>
                    <?php endforeach; ?>
                    <th scope="col" class="py-3"><?= htmlspecialchars(__('data_entry.th_added_by'), ENT_QUOTES, 'UTF-8') ?></th>
                    <th scope="col" class="py-3"><?= htmlspecialchars(__('data_entry.th_date_created'), ENT_QUOTES, 'UTF-8') ?></th>
                    <th scope="col" class="py-3 text-end pe-3"><?= htmlspecialchars(__('index.th_actions'), ENT_QUOTES, 'UTF-8') ?></th>
                </tr>
            </thead>
            <tbody id="data-entry-table-body">
                <tr>
                    <td colspan="<?= count($columns) + 3 ?>" class="text-center py-4 text-muted">
                        <?= htmlspecialchars(__('data_entry.loading_records') ?: 'Loading…', ENT_QUOTES, 'UTF-8') ?>
                    </td>
                </tr>
            </tbody>
        </table>
    </div>
</div>

<nav aria-label="Records Pagination" class="d-flex justify-content-center">
    <div id="data-entry-pagination" class="pagination pagination-sm mb-0 gap-1"></div>
</nav>
