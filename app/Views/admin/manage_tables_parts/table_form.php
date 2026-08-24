<?php
declare(strict_types=1);
/** @var array{action?: string, post?: array<string, mixed>}|null $formDraft */
$formDraft = $formDraft ?? null;
$draftPost = (is_array($formDraft) && isset($formDraft['post']) && is_array($formDraft['post'])) ? $formDraft['post'] : [];
$draftAction = is_array($formDraft) && isset($formDraft['action']) ? (string) $formDraft['action'] : '';
$useTableDraft = in_array($draftAction, ['create_table', 'update_table'], true);
$draftTableName = $useTableDraft && isset($draftPost['table_name']) && is_string($draftPost['table_name'])
    ? $draftPost['table_name'] : null;
$draftTableDesc = $useTableDraft && isset($draftPost['description']) && is_string($draftPost['description'])
    ? $draftPost['description'] : null;
$openTableForm = $editTable || ($draftTableName !== null && $draftTableName !== '');
?>
<!-- Create New Table / Edit Table Collapsible Section -->
<div class="card shadow-sm border-0 mb-4">
    <div class="card-body">
        <details id="table-form-details" <?= $openTableForm ? 'open' : '' ?>>
            <summary class="fw-bold fs-5 text-dark" style="cursor: pointer;">
                <?= $editTable ? htmlspecialchars(__('manage_tables.edit_table_summary'), ENT_QUOTES, 'UTF-8') . ' ' . htmlspecialchars((string)($editTable['table_name'] ?? ''), ENT_QUOTES, 'UTF-8') : htmlspecialchars(__('manage_tables.create_table_summary'), ENT_QUOTES, 'UTF-8') ?>
            </summary>
          
            <div class="mt-3 pt-3 border-top">
                <form method="POST" action="<?= $basePath ?>/admin/tables">
                    <?= csrf_field() ?>
                    <input type="hidden" name="action" value="<?= $editTable ? 'update_table' : 'create_table' ?>">
                    <input type="hidden" name="table_id" value="<?= $activeTableId ?>">
                    <?php if ($editTable): ?>
                        <input type="hidden" name="table_id" value="<?= (int)($editTable['id'] ?? 0) ?>">
                    <?php endif; ?>

                    <div class="mb-3">
                        <label for="table_name" class="form-label fw-bold"><?= htmlspecialchars(__('manage_tables.table_name_label'), ENT_QUOTES, 'UTF-8') ?> <span class="text-danger">*</span></label>
                        <input type="text" id="table_name" name="table_name" value="<?= htmlspecialchars((string)($editTable['table_name'] ?? $draftTableName ?? ''), ENT_QUOTES, 'UTF-8') ?>" placeholder="e.g. Parish Records" required class="form-control max-width-400">
                    </div>

                    <div class="mb-3">
                        <label for="table_description" class="form-label fw-bold"><?= htmlspecialchars(__('manage_tables.table_desc_label'), ENT_QUOTES, 'UTF-8') ?></label>
                        <textarea id="table_description" name="description" rows="2" placeholder="Brief summary of records stored in this table..." class="form-control"><?= htmlspecialchars((string)($editTable['description'] ?? $draftTableDesc ?? ''), ENT_QUOTES, 'UTF-8') ?></textarea>
                    </div>

                    <div>
                        <button type="submit" class="btn btn-primary"><?= $editTable ? htmlspecialchars(__('manage_tables.save_table_btn'), ENT_QUOTES, 'UTF-8') : htmlspecialchars(__('manage_tables.create_table_btn'), ENT_QUOTES, 'UTF-8') ?></button>
                        <?php if ($editTable): ?>
                            <a href="<?= $basePath ?>/admin/tables?table_id=<?= $activeTableId ?>" class="btn btn-outline-secondary ms-2"><?= htmlspecialchars(__('btn.cancel'), ENT_QUOTES, 'UTF-8') ?></a>
                        <?php endif; ?>
                    </div>
                </form>

                <?php if ($editTable): ?>
                    <form method="POST" action="<?= $basePath ?>/admin/tables" class="mt-3 pt-3 border-top" onsubmit="return confirm('⚠️ Are you sure you want to delete this table? All associated records and structure will be permanently removed.');">
                        <?= csrf_field() ?>
                        <input type="hidden" name="action" value="delete_table">
                        <input type="hidden" name="table_id" value="<?= (int)($editTable['id'] ?? 0) ?>">
                        <button type="submit" class="btn btn-outline-danger btn-sm"><?= htmlspecialchars(__('manage_tables.delete_table_btn'), ENT_QUOTES, 'UTF-8') ?></button>
                    </form>
                <?php endif; ?>
            </div>
        </details>
    </div>
</div>
