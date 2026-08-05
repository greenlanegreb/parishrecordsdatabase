<?php
/**
 * MIGRATED FILE MAPPING
 * ---------------------
 * Original Old File: user/suggest_edit.php/user/actions/save_suggest_edit.php
 * Migrated Date: 2026-08-05 05:28:02
 */declare(strict_types=1);

/**
 * MIGRATED FILE MAPPING
 * ---------------------
 * Original Old File: user/suggest_edit.php
 * Migrated Date: 2026-08-04 15:30:00
 */

/** @string $error */
/** @string $message */
/** @string $recordId */
/** @string $returnUrl */
/** @array<int, array<string, mixed>> $recordData */

require_once __DIR__ . '/../partials/header.php';
?>

<div class="container py-4" style="max-width: 800px;" role="region" aria-label="<?= htmlspecialchars(__('suggest_edit.aria_region'), ENT_QUOTES, 'UTF-8') ?>">
    <div class="d-flex justify-content-between align-items-center mb-3">
        <h3 class="h4 fw-bold text-dark mb-0"><?= htmlspecialchars(__('suggest_edit.heading_prefix'), ENT_QUOTES, 'UTF-8') ?> #<?= htmlspecialchars($recordId, ENT_QUOTES, 'UTF-8') ?></h3>
        <a href="<?= htmlspecialchars($returnUrl, ENT_QUOTES, 'UTF-8') ?>" class="btn btn-sm btn-secondary text-decoration-none">← <?= htmlspecialchars(__('suggest_edit.return_btn'), ENT_QUOTES, 'UTF-8') ?></a>
    </div>

    <?php if (!empty($error)): ?>
        <div class="alert alert-danger alert-dismissible fade show" role="alert">
            <strong><?= htmlspecialchars($error, ENT_QUOTES, 'UTF-8') ?></strong>
            <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
        </div>
    <?php endif; ?>
    <?php if (!empty($message)): ?>
        <div class="alert alert-success alert-dismissible fade show" role="alert">
            <strong><?= htmlspecialchars($message, ENT_QUOTES, 'UTF-8') ?></strong> <?= htmlspecialchars(__('suggest_edit.success_msg_suffix'), ENT_QUOTES, 'UTF-8') ?>
            <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
        </div>
    <?php endif; ?>

    <div class="card shadow-sm border-0 p-4 mb-4">
        <h4 class="h5 fw-bold text-dark mb-3"><?= htmlspecialchars(__('suggest_edit.current_values_heading'), ENT_QUOTES, 'UTF-8') ?></h4>
        <ul class="list-unstyled mb-0">
            <?php foreach ($recordData as $data): ?>
                <?php 
                    $colName = isset($data['column_name']) && is_string($data['column_name']) ? $data['column_name'] : '';
                    $valCont = isset($data['value_content']) && is_string($data['value_content']) ? $data['value_content'] : '';
                    $dataType = isset($data['data_type']) && is_string($data['data_type']) ? $data['data_type'] : '';
                    $boolFmt = isset($data['boolean_display_format']) && is_string($data['boolean_display_format']) ? $data['boolean_display_format'] : 'yes_no';
                ?>
                <li class="mb-2">
                    <strong><?= htmlspecialchars($colName, ENT_QUOTES, 'UTF-8') ?>:</strong> 
                    <?php if ($valCont !== ''): ?>
                        <?php if ($dataType === 'BOOLEAN'): ?>
                            <?= htmlspecialchars(format_boolean_value($valCont, $boolFmt), ENT_QUOTES, 'UTF-8') ?>
                        <?php else: ?>
                            <?= htmlspecialchars($valCont, ENT_QUOTES, 'UTF-8') ?>
                        <?php endif; ?>
                    <?php else: ?>
                        <em class="text-muted"><?= htmlspecialchars(__('suggest_edit.empty_label'), ENT_QUOTES, 'UTF-8') ?></em>
                    <?php endif; ?>
                </li>
            <?php endforeach; ?>
        </ul>
    </div>

    <div class="card shadow-sm border-0 p-4">
        <h3 class="h5 fw-bold text-dark mb-3"><?= htmlspecialchars(__('suggest_edit.submit_heading'), ENT_QUOTES, 'UTF-8') ?></h3>
        <form method="POST" action="/user/actions/save_suggest_edit.php" onsubmit="return confirm('<?= htmlspecialchars(__('suggest_edit.confirm_prompt'), ENT_QUOTES, 'UTF-8') ?>');">
            <?= csrf_field() ?>
            <input type="hidden" name="record_id" value="<?= htmlspecialchars($recordId, ENT_QUOTES, 'UTF-8') ?>">
            <input type="hidden" name="return_url" value="<?= htmlspecialchars($returnUrl, ENT_QUOTES, 'UTF-8') ?>">

            <div class="mb-3">
                <label for="column_id" class="form-label small fw-bold"><?= htmlspecialchars(__('suggest_edit.select_column_label'), ENT_QUOTES, 'UTF-8') ?></label>
                <select id="column_id" name="column_id" required class="form-select form-select-sm" onchange="renderInputType()">
                    <?php foreach ($recordData as $data): ?>
                        <?php 
                            $cId = isset($data['column_id']) ? (int)$data['column_id'] : 0;
                            $cName = isset($data['column_name']) && is_string($data['column_name']) ? $data['column_name'] : '';
                        ?>
                        <option value="<?= $cId ?>">
                            <?= htmlspecialchars($cName, ENT_QUOTES, 'UTF-8') ?>
                        </option>
                    <?php endforeach; ?>
                </select>
            </div>

            <div id="input-container" class="mb-3">
                <!-- Dynamic input field rendered via JavaScript depending on column type -->
            </div>

            <div class="mb-3">
                <label for="reasoning" class="form-label small fw-bold"><?= htmlspecialchars(__('suggest_edit.reasoning_label'), ENT_QUOTES, 'UTF-8') ?></label>
                <textarea id="reasoning" name="reasoning" rows="3" placeholder="<?= htmlspecialchars(__('suggest_edit.reasoning_placeholder'), ENT_QUOTES, 'UTF-8') ?>" class="form-control form-control-sm" oninput="this.style.height = ''; this.style.height = this.scrollHeight + 'px';" style="overflow:hidden;"></textarea>
            </div>

            <button type="submit" class="btn btn-sm btn-primary"><?= htmlspecialchars(__('suggest_edit.submit_btn'), ENT_QUOTES, 'UTF-8') ?></button>
        </form>
    </div>
</div>

<script>
const columnMeta = <?= json_encode($recordData) ?>;
const optYesTrueText = '<?= htmlspecialchars(__('data_entry.bool_yes_true'), ENT_QUOTES, 'UTF-8') ?>';
const optNoFalseText = '<?= htmlspecialchars(__('data_entry.bool_no_false'), ENT_QUOTES, 'UTF-8') ?>';
const optMaleText = '<?= htmlspecialchars(__('data_entry.bool_male'), ENT_QUOTES, 'UTF-8') ?>';
const optFemaleText = '<?= htmlspecialchars(__('data_entry.bool_female'), ENT_QUOTES, 'UTF-8') ?>';
const optTrueText = '<?= htmlspecialchars(__('data_entry.bool_true'), ENT_QUOTES, 'UTF-8') ?>';
const optFalseText = '<?= htmlspecialchars(__('data_entry.bool_false'), ENT_QUOTES, 'UTF-8') ?>';
const optTickText = '<?= htmlspecialchars(__('data_entry.bool_tick'), ENT_QUOTES, 'UTF-8') ?>';
const optCrossText = '<?= htmlspecialchars(__('data_entry.bool_cross'), ENT_QUOTES, 'UTF-8') ?>';
const selectPlaceholder = '<?= htmlspecialchars(__('feedback.select_placeholder'), ENT_QUOTES, 'UTF-8') ?>';
const proposedValueLabel = '<?= htmlspecialchars(__('suggest_edit.proposed_value_label'), ENT_QUOTES, 'UTF-8') ?>';

function renderInputType() {
    const select = document.getElementById('column_id');
    const container = document.getElementById('input-container');
    const selectedColId = select.value;
    
    const col = columnMeta.find(c => c.column_id == selectedColId);
    if (!col) return;

    container.innerHTML = '';

    if (col.data_type === 'BOOLEAN') {
        let fmt = col.boolean_display_format || 'yes_no';
        let opt1Text = optYesTrueText;
        let opt2Text = optNoFalseText;
        
        if (fmt === 'male_female') { opt1Text = optMaleText; opt2Text = optFemaleText; }
        else if (fmt === 'true_false') { opt1Text = optTrueText; opt2Text = optFalseText; }
        else if (fmt === 'tick_cross') { opt1Text = optTickText; opt2Text = optCrossText; }

        let currentValue = col.value_content;

        container.innerHTML = `
            <label for="proposed_value" class="form-label small fw-bold">${proposedValueLabel}</label>
            <select id="proposed_value" name="proposed_value" required class="form-select form-select-sm">
                <option value="">${selectPlaceholder}</option>
                <option value="1" ${currentValue === '1' ? 'selected' : ''}>${opt1Text}</option>
                <option value="0" ${currentValue === '0' ? 'selected' : ''}>${opt2Text}</option>
            </select>
        `;
    } else {
        let currentValue = col.value_content;
        container.innerHTML = `
            <label for="proposed_value" class="form-label small fw-bold">${proposedValueLabel}</label>
            <textarea id="proposed_value" name="proposed_value" rows="3" required class="form-control form-control-sm" oninput="this.style.height = ''; this.style.height = this.scrollHeight + 'px';" style="overflow:hidden;">${escapeHtml(currentValue)}</textarea>
        `;
        const textarea = document.getElementById('proposed_value');
        if (textarea) {
            textarea.style.height = '';
            textarea.style.height = textarea.scrollHeight + 'px';
        }
    }
}

function escapeHtml(text) {
    return text
        .replace(/&/g, "&amp;")
        .replace(/</g, "&lt;")
        .replace(/>/g, "&gt;")
        .replace(/"/g, "&quot;")
        .replace(/'/g, "&#039;");
}

document.addEventListener('DOMContentLoaded', () => {
    renderInputType();
});
</script>

<?php require_once __DIR__ . '/../partials/footer.php'; ?>
