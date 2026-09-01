<?php
declare(strict_types=1);

// Fallbacks in case the controller does not pass these
$userTimezone  = $userTimezone  ?? 'UTC';
$fullFormatStr = $fullFormatStr ?? 'd/m/Y H:i';
?>
<?php
$deTableName = '';
if (isset($activeTableInfo['table_name']) && is_string($activeTableInfo['table_name'])) {
    $deTableName = ucfirst($activeTableInfo['table_name']);
} elseif (!empty($allTables) && is_array($allTables)) {
    foreach ($allTables as $t) {
        if ((int) ($t['id'] ?? 0) === (int) ($activeTableId ?? 0)) {
            $deTableName = isset($t['table_name']) && is_string($t['table_name']) ? ucfirst($t['table_name']) : '';
            break;
        }
    }
}
?>
<h3 class="h5 fw-bold mb-3"><?= htmlspecialchars($deTableName !== '' ? $deTableName : __('data_entry.existing_records_heading'), ENT_QUOTES, 'UTF-8') ?></h3>

<div class="card shadow-sm border-0 mb-4">
    <div class="table-responsive">
        <table class="table table-striped table-hover align-middle mb-0 prd-table-sticky prd-col-order" id="data-entry-table" role="table" data-table-id="<?= (int) ($activeTableId ?? 0) ?>">
            <thead class="table-light">
                <tr>
                    <?php foreach (($visibleColumns ?? $columns) as $col): ?>
                        <?php $cName = isset($col['column_name']) && is_string($col['column_name']) ? $col['column_name'] : ''; ?>
                        <?php $cId = isset($col['id']) ? (int) $col['id'] : 0; ?>
                        <th scope="col" class="py-3 text-nowrap" data-col-id="<?= $cId ?>"><?= htmlspecialchars($cName, ENT_QUOTES, 'UTF-8') ?></th>
                    <?php endforeach; ?>
                    <th scope="col" class="py-3 text-end pe-3" data-col-id="actions"><?= htmlspecialchars(__('index.th_actions'), ENT_QUOTES, 'UTF-8') ?></th>
                </tr>
            </thead>
            <tbody id="data-entry-table-body">
                <tr>
                    <td colspan="<?= count($visibleColumns ?? $columns) + 1 ?>" class="text-center py-4 text-muted">
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
