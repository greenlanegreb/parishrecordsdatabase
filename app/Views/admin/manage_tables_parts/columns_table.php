<?php
declare(strict_types=1);
?>
<div class="d-flex justify-content-between align-items-center mb-3">
    <h3 class="fw-bold mb-0"><?= htmlspecialchars(__('manage_tables.existing_cols_heading_prefix'), ENT_QUOTES, 'UTF-8') ?> "<?= htmlspecialchars((string) ($activeTableInfo['table_name'] ?? ''), ENT_QUOTES, 'UTF-8') ?>"</h3>
</div>
<div class="card shadow-sm border-0">
    <div class="table-responsive">
        <table class="table table-hover align-middle mb-0" role="table">
            <thead class="table-light">
                <tr>
                    <th scope="col" style="width: 50px;" class="text-center py-3 ps-3"><?= htmlspecialchars(__('feedback_schema.th_move'), ENT_QUOTES, 'UTF-8') ?></th>
                    <th scope="col" class="py-3"><?= htmlspecialchars(__('feedback_schema.th_field_name'), ENT_QUOTES, 'UTF-8') ?></th>
                    <th scope="col" class="py-3"><?= htmlspecialchars(__('feedback_schema.th_data_type'), ENT_QUOTES, 'UTF-8') ?></th>
                    <th scope="col" class="py-3"><?= htmlspecialchars(__('feedback_schema.th_required'), ENT_QUOTES, 'UTF-8') ?></th>
                    <th scope="col" class="py-3"><?= htmlspecialchars(__('manage_tables.th_public_search'), ENT_QUOTES, 'UTF-8') ?></th>
                    <th scope="col" class="py-3"><?= htmlspecialchars(__('manage_tables.th_display_format'), ENT_QUOTES, 'UTF-8') ?></th>
                    <th scope="col" class="py-3"><?= htmlspecialchars(__('feedback_schema.th_max_length'), ENT_QUOTES, 'UTF-8') ?></th>
                    <th scope="col" class="py-3"><?= htmlspecialchars(__('feedback_schema.th_created_by'), ENT_QUOTES, 'UTF-8') ?></th>
                    <th scope="col" class="py-3"><?= htmlspecialchars(__('manage_tables.th_date_created'), ENT_QUOTES, 'UTF-8') ?></th>
                    <th scope="col" class="py-3 pe-3 text-end"><?= htmlspecialchars(__('index.th_actions'), ENT_QUOTES, 'UTF-8') ?></th>
                </tr>
            </thead>
            <tbody id="sortable-columns-body">
                <?php if (empty($columns)): ?>
                    <tr>
                        <td colspan="10" class="text-center py-4 text-muted"><?= htmlspecialchars(__('manage_tables.no_columns_found'), ENT_QUOTES, 'UTF-8') ?></td>
                    </tr>
                <?php else: ?>
                    <?php foreach ($columns as $col): ?>
                        <?php
                            $colId = isset($col['id']) ? (int) $col['id'] : 0;
                            $colName = isset($col['column_name']) && is_string($col['column_name']) ? $col['column_name'] : '';
                            $dataType = isset($col['data_type']) && is_string($col['data_type']) ? $col['data_type'] : '';
                            $isRequired = !empty($col['is_required']);
                            $excludePublic = empty($col['exclude_from_public_search']);
                            $maxLength = isset($col['max_length']) ? $col['max_length'] : 'N/A';
                            $createdBy = isset($col['created_by_display']) && is_string($col['created_by_display']) && $col['created_by_display'] !== ''
                                ? $col['created_by_display']
                                : (__('feedback_schema.system_user') ?? 'System');
                            $createdAt = isset($col['created_at']) && is_string($col['created_at']) ? $col['created_at'] : '';
                            $boolFormat = isset($col['boolean_display_format']) && is_string($col['boolean_display_format'])
                                ? $col['boolean_display_format'] : 'yes_no';
                            $dateBhv = isset($col['date_search_behavior']) && is_string($col['date_search_behavior'])
                                ? $col['date_search_behavior'] : 'manual_only';
                        ?>
                        <tr data-column-id="<?= $colId ?>" style="cursor: grab;">
                            <td class="text-center text-muted ps-3 fs-5" title="Drag to reorder">☰</td>
                            <td><span class="fw-bold"><?= htmlspecialchars($colName, ENT_QUOTES, 'UTF-8') ?></span></td>
                            <td><code class="text-dark"><?= htmlspecialchars($dataType, ENT_QUOTES, 'UTF-8') ?></code></td>
                            <td><?= $isRequired ? '<span class="text-success fw-bold">Yes</span>' : '<span class="text-muted">No</span>' ?></td>
                            <td>
                                <?= $excludePublic
                                    ? '<span class="text-success fw-bold">Yes</span>'
                                    : '<span class="text-danger fw-bold">' . htmlspecialchars(__('manage_tables.status_hidden'), ENT_QUOTES, 'UTF-8') . '</span>' ?>
                            </td>
                            <td>
                                <?php
                                    if ($dataType === 'BOOLEAN') {
                                        echo htmlspecialchars($boolFormat, ENT_QUOTES, 'UTF-8');
                                    } elseif ($dataType === 'DATE') {
                                        echo htmlspecialchars($dateBhv, ENT_QUOTES, 'UTF-8');
                                    } else {
                                        echo 'N/A';
                                    }
                                ?>
                            </td>
                            <td><?= htmlspecialchars((string) $maxLength, ENT_QUOTES, 'UTF-8') ?></td>
                            <td><?= htmlspecialchars($createdBy, ENT_QUOTES, 'UTF-8') ?></td>
                            <td><?= format_user_time($createdAt, $userTimezone, $fullFormatStr) ?></td>
                            <td class="text-end pe-3 text-nowrap">
                                <a href="<?= $basePath ?>/admin/tables?table_id=<?= (int) $activeTableId ?>&edit_column=<?= $colId ?>#create-column-details" class="btn btn-sm btn-outline-secondary me-1"><?= htmlspecialchars(__('feedback_schema.edit_btn'), ENT_QUOTES, 'UTF-8') ?></a>
                                <form method="POST" action="<?= $basePath ?>/admin/tables" class="d-inline" onsubmit="return confirm('<?= htmlspecialchars(__('manage_tables.delete_col_confirm'), ENT_QUOTES, 'UTF-8') ?>');">
                                    <?= csrf_field() ?>
                                    <input type="hidden" name="action" value="delete">
                                    <input type="hidden" name="column_id" value="<?= $colId ?>">
                                    <input type="hidden" name="table_id" value="<?= (int) $activeTableId ?>">
                                    <button type="submit" class="btn btn-sm btn-danger" formnovalidate><?= htmlspecialchars(__('btn.delete'), ENT_QUOTES, 'UTF-8') ?></button>
                                </form>
                            </td>
                        </tr>
                    <?php endforeach; ?>
                <?php endif; ?>
            </tbody>
        </table>
    </div>
</div>
