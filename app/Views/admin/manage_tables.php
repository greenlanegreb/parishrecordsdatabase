<?php
/**
 * MIGRATED FILE MAPPING
 * ---------------------
 * Original Old File: admin/manage_tables.php/admin/actions/save_manage_tables.php
 * Migrated Date: 2026-08-05 03:18:39
 */declare(strict_types=1);

/**
 * MIGRATED FILE MAPPING
 * ---------------------
 * Original Old File: admin/manage_tables.php
 * Migrated Date: 2026-08-04 09:40:12
 */

/** @string $message */
/** @string $error */
/** @string $userTimezone */
/** @string $fullFormatStr */
/** @array<int, array<string, mixed>> $tables */
/** @int $activeTableId */
/** @array<string, mixed>|null $activeTableInfo */
/** @array<string, mixed>|null $editTable */
/** @array<string, mixed>|null $editCol */
/** @array<int, array<string, mixed>> $columns */

require_once __DIR__ . '/../partials/header.php';
?>

<!-- Include SortableJS CDN for drag-and-drop support -->
<script src="https://cdnjs.cloudflare.com/ajax/libs/Sortable/1.15.0/Sortable.min.js"></script>

<div class="container py-4" role="region" aria-label="Dynamic Table Management" style="max-width: 1100px;">
    <h3 class="fw-bold mb-1"><?= htmlspecialchars(__('manage_tables.heading'), ENT_QUOTES, 'UTF-8') ?></h3>
    <p class="text-muted mb-3"><?= htmlspecialchars(__('manage_tables.subheading'), ENT_QUOTES, 'UTF-8') ?></p>

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

    <!-- Table Selector Bar & Quick Management -->
    <?php if (!empty($tables)): ?>
    <div class="card shadow-sm border-0 mb-4 bg-light">
        <div class="card-body d-flex justify-content-between align-items-center flex-wrap gap-3">
            <div>
                <label for="table_switcher" class="form-label fw-bold small"><?= htmlspecialchars(__('manage_tables.switcher_label'), ENT_QUOTES, 'UTF-8') ?></label>
                <select id="table_switcher" class="form-select" onchange="if(this.value) window.location.href='/admin/tables?table_id=' + this.value;">
                    <?php foreach ($tables as $t): ?>
                        <?php 
                            $tId = isset($t['id']) ? (int)$t['id'] : 0;
                            $tName = isset($t['table_name']) && is_string($t['table_name']) ? $t['table_name'] : '';
                        ?>
                        <option value="<?= $tId ?>" <?= ($tId === $activeTableId) ? 'selected' : '' ?>>
                            <?= htmlspecialchars($tName, ENT_QUOTES, 'UTF-8') ?> (ID: <?= $tId ?>)
                        </option>
                    <?php endforeach; ?>
                </select>
            </div>
          
            <?php if ($activeTableInfo): ?>
                <?php $activeTblId = isset($activeTableInfo['id']) ? (int)$activeTableInfo['id'] : 0; ?>
                <div class="d-flex gap-2 align-items-center">
                    <a href="/admin/tables?edit_table=<?= $activeTblId ?>&table_id=<?= $activeTableId ?>" class="btn btn-sm btn-outline-secondary"><?= htmlspecialchars(__('manage_tables.edit_metadata_btn'), ENT_QUOTES, 'UTF-8') ?></a>
                    <?php if ($activeTblId > 1): ?>
                        <form method="POST" action="/admin/tables/store" class="d-inline" onsubmit="return confirm('<?= htmlspecialchars(__('manage_tables.delete_table_confirm'), ENT_QUOTES, 'UTF-8') ?>');">
                            <?= csrf_field() ?>
                            <input type="hidden" name="action" value="delete_table">
                            <input type="hidden" name="table_id" value="<?= $activeTblId ?>">
                            <button type="submit" class="btn btn-sm btn-danger"><?= htmlspecialchars(__('manage_tables.delete_table_btn'), ENT_QUOTES, 'UTF-8') ?></button>
                        </form>
                    <?php endif; ?>
                </div>
            <?php endif; ?>
        </div>
    </div>
    <?php endif; ?>

    <!-- Create New Table / Edit Table Collapsible Section -->
    <div class="card shadow-sm border-0 mb-4">
        <div class="card-body">
            <details id="table-form-details" <?= $editTable ? 'open' : '' ?>>
                <summary class="fw-bold fs-5 text-dark" style="cursor: pointer;">
                    <?= $editTable ? htmlspecialchars(__('manage_tables.edit_table_summary'), ENT_QUOTES, 'UTF-8') . ' ' . htmlspecialchars((string)($editTable['table_name'] ?? ''), ENT_QUOTES, 'UTF-8') : htmlspecialchars(__('manage_tables.create_table_summary'), ENT_QUOTES, 'UTF-8') ?>
                </summary>
              
                <div class="mt-3 pt-3 border-top">
                    <form method="POST" action="/admin/tables/store">
                        <?= csrf_field() ?>
                        <input type="hidden" name="action" value="<?= $editTable ? 'update_table' : 'create_table' ?>">
                        <input type="hidden" name="table_id" value="<?= $activeTableId ?>">
                        <?php if ($editTable): ?>
                            <input type="hidden" name="table_id" value="<?= (int)($editTable['id'] ?? 0) ?>">
                        <?php endif; ?>

                        <div class="mb-3">
                            <label for="table_name" class="form-label fw-bold"><?= htmlspecialchars(__('manage_tables.table_name_label'), ENT_QUOTES, 'UTF-8') ?> <span class="text-danger">*</span></label>
                            <input type="text" id="table_name" name="table_name" value="<?= $editTable ? htmlspecialchars((string)($editTable['table_name'] ?? ''), ENT_QUOTES, 'UTF-8') : '' ?>" placeholder="e.g. Parish Records" required class="form-control max-width-400">
                        </div>

                        <div class="mb-3">
                            <label for="table_description" class="form-label fw-bold"><?= htmlspecialchars(__('manage_tables.table_desc_label'), ENT_QUOTES, 'UTF-8') ?></label>
                            <textarea id="table_description" name="description" rows="2" placeholder="Brief summary of records stored in this table..." class="form-control"><?= $editTable ? htmlspecialchars((string)($editTable['description'] ?? ''), ENT_QUOTES, 'UTF-8') : '' ?></textarea>
                        </div>

                        <div>
                            <button type="submit" class="btn btn-primary"><?= $editTable ? htmlspecialchars(__('manage_tables.save_table_btn'), ENT_QUOTES, 'UTF-8') : htmlspecialchars(__('manage_tables.create_table_btn'), ENT_QUOTES, 'UTF-8') ?></button>
                            <?php if ($editTable): ?>
                                <a href="/admin/tables?table_id=<?= $activeTableId ?>" class="btn btn-outline-secondary ms-2"><?= htmlspecialchars(__('btn.cancel'), ENT_QUOTES, 'UTF-8') ?></a>
                            <?php endif; ?>
                        </div>
                    </form>
                </div>
            </details>
        </div>
    </div>

    <?php if ($activeTableInfo): ?>
        <hr class="my-4">

        <!-- Collapsible Column Form Container -->
        <div class="card shadow-sm border-0 mb-4">
            <div class="card-body">
                <details id="create-column-details" <?= $editCol ? 'open' : '' ?>>
                    <summary class="fw-bold fs-5 text-dark" style="cursor: pointer;">
                        <?= $editCol ? htmlspecialchars(__('manage_tables.edit_col_summary'), ENT_QUOTES, 'UTF-8') . ' ' . htmlspecialchars((string)($editCol['column_name'] ?? ''), ENT_QUOTES, 'UTF-8') : htmlspecialchars(__('manage_tables.add_col_summary_prefix'), ENT_QUOTES, 'UTF-8') . ' "' . htmlspecialchars((string)($activeTableInfo['table_name'] ?? ''), ENT_QUOTES, 'UTF-8') . '"' ?>
                    </summary>
                  
                    <div class="mt-3 pt-3 border-top">
                        <form method="POST" action="/admin/tables/store">
                            <?= csrf_field() ?>
                            <input type="hidden" name="action" value="<?= $editCol ? 'update' : 'create' ?>">
                            <input type="hidden" name="table_id" value="<?= $activeTableId ?>">
                            <?php if ($editCol): ?>
                                <input type="hidden" name="column_id" value="<?= (int)($editCol['id'] ?? 0) ?>">
                            <?php endif; ?>
                          
                            <div class="mb-3">
                                <label for="column_name" class="form-label fw-bold"><?= htmlspecialchars(__('manage_tables.col_name_label'), ENT_QUOTES, 'UTF-8') ?> <span class="text-danger">*</span></label>
                                <input type="text" id="column_name" name="column_name" value="<?= $editCol ? htmlspecialchars((string)($editCol['column_name'] ?? ''), ENT_QUOTES, 'UTF-8') : '' ?>" required class="form-control max-width-400">
                            </div>

                            <div class="mb-3">
                                <label for="data_type" class="form-label fw-bold"><?= htmlspecialchars(__('feedback_schema.data_type_label'), ENT_QUOTES, 'UTF-8') ?></label>
                                <select id="data_type" name="data_type" class="form-select max-width-400" onchange="toggleFieldOptions(this.value)">
                                    <option value="VARCHAR" <?= ($editCol && ($editCol['data_type'] ?? '') === 'VARCHAR') ? 'selected' : '' ?>><?= htmlspecialchars(__('feedback_schema.type_varchar'), ENT_QUOTES, 'UTF-8') ?></option>
                                    <option value="TEXT" <?= ($editCol && ($editCol['data_type'] ?? '') === 'TEXT') ? 'selected' : '' ?>><?= htmlspecialchars(__('manage_tables.type_text_long'), ENT_QUOTES, 'UTF-8') ?></option>
                                    <option value="INT" <?= ($editCol && ($editCol['data_type'] ?? '') === 'INT') ? 'selected' : '' ?>><?= htmlspecialchars(__('feedback_schema.type_int'), ENT_QUOTES, 'UTF-8') ?></option>
                                    <option value="BOOLEAN" <?= ($editCol && ($editCol['data_type'] ?? '') === 'BOOLEAN') ? 'selected' : '' ?>><?= htmlspecialchars(__('feedback_schema.type_boolean'), ENT_QUOTES, 'UTF-8') ?></option>
                                    <option value="DATE" <?= ($editCol && ($editCol['data_type'] ?? '') === 'DATE') ? 'selected' : '' ?>><?= htmlspecialchars(__('feedback_schema.type_date'), ENT_QUOTES, 'UTF-8') ?></option>
                                </select>
                            </div>

                            <!-- Dynamic Boolean Display Style Option -->
                            <div id="boolean_options_wrapper" class="mb-3" style="display: <?= ($editCol && ($editCol['data_type'] ?? '') === 'BOOLEAN') ? 'block' : 'none' ?>;">
                                <label for="boolean_display_format" class="form-label fw-bold"><?= htmlspecialchars(__('feedback_schema.boolean_format'), ENT_QUOTES, 'UTF-8') ?></label>
                                <select id="boolean_display_format" name="boolean_display_format" class="form-select max-width-400">
                                    <option value="yes_no" <?= ($editCol && (string)($editCol['boolean_display_format'] ?? '') === 'yes_no') ? 'selected' : '' ?>><?= htmlspecialchars(__('index.opt_yes_true'), ENT_QUOTES, 'UTF-8') ?></option>
                                    <option value="true_false" <?= ($editCol && (string)($editCol['boolean_display_format'] ?? '') === 'true_false') ? 'selected' : '' ?>><?= htmlspecialchars(__('index.opt_true'), ENT_QUOTES, 'UTF-8') ?></option>
                                    <option value="tick_cross" <?= ($editCol && (string)($editCol['boolean_display_format'] ?? '') === 'tick_cross') ? 'selected' : '' ?>><?= htmlspecialchars(__('index.opt_tick'), ENT_QUOTES, 'UTF-8') ?></option>
                                    <option value="male_female" <?= ($editCol && (string)($editCol['boolean_display_format'] ?? '') === 'male_female') ? 'selected' : '' ?>><?= htmlspecialchars(__('index.opt_male'), ENT_QUOTES, 'UTF-8') ?> / <?= htmlspecialchars(__('index.opt_female'), ENT_QUOTES, 'UTF-8') ?></option>
                                </select>
                            </div>

                            <!-- Dynamic Date Search Behavior Option -->
                            <div id="date_options_wrapper" class="mb-3" style="display: <?= ($editCol && ($editCol['data_type'] ?? '') === 'DATE') ? 'block' : 'none' ?>;">
                                <label for="date_search_behavior" class="form-label fw-bold"><?= htmlspecialchars(__('manage_tables.date_behavior_label'), ENT_QUOTES, 'UTF-8') ?></label>
                                <select id="date_search_behavior" name="date_search_behavior" class="form-select max-width-400">
                                    <option value="manual_only" <?= ($editCol && (string)($editCol['date_search_behavior'] ?? '') === 'manual_only') ? 'selected' : '' ?>><?= htmlspecialchars(__('manage_tables.date_bhv_manual'), ENT_QUOTES, 'UTF-8') ?></option>
                                    <option value="admin_only" <?= ($editCol && (string)($editCol['date_search_behavior'] ?? '') === 'admin_only') ? 'selected' : '' ?>><?= htmlspecialchars(__('manage_tables.date_bhv_admin'), ENT_QUOTES, 'UTF-8') ?></option>
                                    <option value="all_dates" <?= ($editCol && (string)($editCol['date_search_behavior'] ?? '') === 'all_dates') ? 'selected' : '' ?>><?= htmlspecialchars(__('manage_tables.date_bhv_all'), ENT_QUOTES, 'UTF-8') ?></option>
                                </select>
                            </div>

                            <div class="mb-3">
                                <label for="max_length" class="form-label fw-bold"><?= htmlspecialchars(__('feedback_schema.max_length_label'), ENT_QUOTES, 'UTF-8') ?></label>
                                <input type="number" id="max_length" name="max_length" value="<?= $editCol ? htmlspecialchars((string)($editCol['max_length'] ?? ''), ENT_QUOTES, 'UTF-8') : '' ?>" placeholder="e.g. 255 characters" class="form-control max-width-400">
                            </div>

                            <div class="mb-3 form-check">
                                <input type="checkbox" id="is_required" name="is_required" value="1" <?= ($editCol && !empty($editCol['is_required'])) ? 'checked' : '' ?> class="form-check-input">
                                <label for="is_required" class="form-check-label"><?= htmlspecialchars(__('manage_tables.req_toggle_label'), ENT_QUOTES, 'UTF-8') ?></label>
                            </div>

                            <div class="mb-3 form-check">
                                <input type="checkbox" id="exclude_from_public_search" name="exclude_from_public_search" value="1" <?= ($editCol && !empty($editCol['exclude_from_public_search'])) ? 'checked' : '' ?> class="form-check-input">
                                <label for="exclude_from_public_search" class="form-check-label"><?= htmlspecialchars(__('manage_tables.exclude_search_label'), ENT_QUOTES, 'UTF-8') ?></label>
                            </div>

                            <button type="submit" class="btn btn-primary"><?= $editCol ? htmlspecialchars(__('feedback_schema.save_field_btn'), ENT_QUOTES, 'UTF-8') : htmlspecialchars(__('manage_tables.create_col_btn'), ENT_QUOTES, 'UTF-8') ?></button>
                            <?php if ($editCol): ?>
                                <a href="/admin/tables?table_id=<?= $activeTableId ?>" class="btn btn-outline-secondary ms-2"><?= htmlspecialchars(__('btn.cancel'), ENT_QUOTES, 'UTF-8') ?></a>
                            <?php endif; ?>
                        </form>
                    </div>
                </details>
            </div>
        </div>

        <script>
        function toggleFieldOptions(val) {
            var boolWrapper = document.getElementById('boolean_options_wrapper');
            var dateWrapper = document.getElementById('date_options_wrapper');
          
            boolWrapper.style.display = (val === 'BOOLEAN') ? 'block' : 'none';
            dateWrapper.style.display = (val === 'DATE') ? 'block' : 'none';
        }
        </script>

        <hr class="my-4">

        <div class="d-flex justify-content-between align-items-center mb-3">
            <h3 class="fw-bold mb-0"><?= htmlspecialchars(__('manage_tables.existing_cols_heading_prefix'), ENT_QUOTES, 'UTF-8') ?> "<?= htmlspecialchars((string)($activeTableInfo['table_name'] ?? ''), ENT_QUOTES, 'UTF-8') ?>"</h3>
        </div>

        <!-- Drag-and-Drop Table Wrapping Container -->
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
                                    $colId = isset($col['id']) ? (int)$col['id'] : 0;
                                    $colName = isset($col['column_name']) && is_string($col['column_name']) ? $col['column_name'] : '';
                                    $dataType = isset($col['data_type']) && is_string($col['data_type']) ? $col['data_type'] : '';
                                    $isRequired = !empty($col['is_required']);
                                    $excludePublic = empty($col['exclude_from_public_search']);
                                    $maxLength = isset($col['max_length']) ? $col['max_length'] : 'N/A';
                                    $username = isset($col['username']) && is_string($col['username']) ? $col['username'] : __('feedback_schema.system_user');
                                    $createdAt = isset($col['created_at']) && is_string($col['created_at']) ? $col['created_at'] : '';
                                    $boolFormat = isset($col['boolean_display_format']) && is_string($col['boolean_display_format']) ? $col['boolean_display_format'] : 'yes_no';
                                    $dateBhv = isset($col['date_search_behavior']) && is_string($col['date_search_behavior']) ? $col['date_search_behavior'] : 'manual_only';
                                ?>
                                <tr data-column-id="<?= $colId ?>" style="cursor: grab;">
                                    <td class="text-center text-muted ps-3 fs-5" title="Drag to reorder">☰</td>
                                    <td><span class="fw-bold"><?= htmlspecialchars($colName, ENT_QUOTES, 'UTF-8') ?></span></td>
                                    <td><code class="text-dark"><?= htmlspecialchars($dataType, ENT_QUOTES, 'UTF-8') ?></code></td>
                                    <td><?= $isRequired ? '<span class="text-success fw-bold">Yes</span>' : '<span class="text-muted">No</span>' ?></td>
                                    <td>
                                        <?= $excludePublic ? '<span class="text-success fw-bold">Yes</span>' : '<span class="text-danger fw-bold">' . htmlspecialchars(__('manage_tables.status_hidden'), ENT_QUOTES, 'UTF-8') . '</span>' ?>
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
                                    <td><?= htmlspecialchars((string)$maxLength, ENT_QUOTES, 'UTF-8') ?></td>
                                    <td><?= htmlspecialchars($username, ENT_QUOTES, 'UTF-8') ?></td>
                                    <td><?= format_user_time($createdAt, $userTimezone, $fullFormatStr) ?></td>
                                    <td class="text-end pe-3 text-nowrap">
                                        <a href="/admin/tables?table_id=<?= $activeTableId ?>&edit_column=<?= $colId ?>#create-column-details" class="btn btn-sm btn-outline-secondary me-1"><?= htmlspecialchars(__('feedback_schema.edit_btn'), ENT_QUOTES, 'UTF-8') ?></a>
                                      
                                        <form method="POST" action="/admin/tables/store" class="d-inline" onsubmit="return confirm('<?= htmlspecialchars(__('manage_tables.delete_col_confirm'), ENT_QUOTES, 'UTF-8') ?>');">
                                            <?= csrf_field() ?>
                                            <input type="hidden" name="action" value="delete">
                                            <input type="hidden" name="column_id" value="<?= $colId ?>">
                                            <input type="hidden" name="table_id" value="<?= $activeTableId ?>">
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

        <!-- AJAX Sortable Initialization Script -->
        <?php if (!empty($columns)): ?>
        <script>
        document.addEventListener('DOMContentLoaded', function () {
            var tbody = document.getElementById('sortable-columns-body');
            if (tbody) {
                Sortable.create(tbody, {
                    animation: 150,
                    ghostClass: 'sortable-ghost',
                    onEnd: function () {
                        var rows = tbody.querySelectorAll('tr[data-column-id]');
                        var sortOrders = {};
                      
                        rows.forEach(function (row, index) {
                            var colId = row.getAttribute('data-column-id');
                            sortOrders[colId] = index + 1;
                        });

                        var formData = new URLSearchParams();
                        formData.append('action', 'update_order_batch');
                        formData.append('table_id', '<?= $activeTableId ?>');
                        formData.append('csrf_token', '<?= generate_csrf_token() ?>');
                      
                        for (var colId in sortOrders) {
                            formData.append('sort_orders[' + colId + ']', sortOrders[colId]);
                        }

                        fetch('/admin/tables/store', {
                            method: 'POST',
                            headers: {
                                'Content-Type': 'application/x-www-form-urlencoded',
                            },
                            body: formData
                        })
                        .then(response => {
                            if (!response.ok) {
                                console.error('Failed to sync sort order via AJAX.');
                            }
                        })
                        .catch(error => {
                            console.error('AJAX error:', error);
                        });
                    }
                });
            }
        });
        </script>
        <style>
            .sortable-ghost {
                opacity: 0.4;
                background: #f8f9fa !important;
            }
        </style>
        <?php endif; ?>
    <?php endif; ?>
</div>

<?php require_once __DIR__ . '/../partials/footer.php'; ?>
