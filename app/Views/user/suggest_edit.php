<?php
/**
 * MIGRATED FILE MAPPING
 * ---------------------
 * Original Old File: user/suggest_edit.php/user/actions/save_suggest_edit.php
 * Migrated Date: 2026-08-05 05:28:02
 */
declare(strict_types=1);

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
/** @PDO|null $pdo */

/** @var array<string, mixed> $suggestDraft */
$suggestDraft = $suggestDraft ?? [];
$draftColId = isset($suggestDraft['column_id']) ? (int) $suggestDraft['column_id'] : 0;
$draftProposed = isset($suggestDraft['proposed_value']) && is_string($suggestDraft['proposed_value'])
    ? $suggestDraft['proposed_value'] : '';
$draftReasoning = isset($suggestDraft['reasoning']) && is_string($suggestDraft['reasoning'])
    ? $suggestDraft['reasoning'] : '';
$draftNotify = !empty($suggestDraft['notify_outcome']);
$draftNotifyEmail = isset($suggestDraft['notify_email']) && is_string($suggestDraft['notify_email'])
    ? $suggestDraft['notify_email'] : '';

require_once ROOT_PATH . '/partials/header.php';
$basePath = defined('BASE_PATH') ? rtrim(BASE_PATH, '/') : '';

// Determine user's preferred date format with a robust fallback for non-logged-in or unconfigured users
$userDateFormat = 'd/m/Y';
if (isset($pdo) && $pdo instanceof PDO && isset($_SESSION['user_id']) && function_exists('get_current_user_data')) {
    $currUserData = get_current_user_data($pdo);
    if ($currUserData !== false && $currUserData !== null && isset($currUserData['date_format']) && is_string($currUserData['date_format'])) {
        $userDateFormat = $currUserData['date_format'];
    }
}

// Prepare column metadata for JS, formatting date values to match the user's preferred date format
$jsColumnMeta = array_map(function($item) use ($userDateFormat) {
    $formattedItem = $item;
    if (isset($item['data_type']) && $item['data_type'] === 'DATE' && !empty($item['value_content'])) {
        $formattedItem['value_content_formatted'] = format_display_date($item['value_content'], $userDateFormat);
    } else {
        $formattedItem['value_content_formatted'] = $item['value_content'] ?? '';
    }
    return $formattedItem;
}, $recordData);
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

    <?php if (!empty($duplicateWarning) && !empty($matches)): ?>
        <div class="alert alert-warning shadow-sm border-0 mb-4 p-3" role="region" aria-labelledby="suggestDupHeading">
            <h2 class="h5 fw-bold mb-3" id="suggestDupHeading"><?= htmlspecialchars(__('data_entry.dup_heading') !== 'data_entry.dup_heading' ? __('data_entry.dup_heading') : 'This looks similar to something already saved', ENT_QUOTES, 'UTF-8') ?></h2>
            <div class="row g-3">
                <?php foreach ($matches as $match): ?>
                    <?php
                        $mId = isset($match['id']) ? (int) $match['id'] : 0;
                        $bucket = isset($match['bucket']) ? (int) $match['bucket'] : 25;
                        $title = __('data_entry.dup_similar') !== 'data_entry.dup_similar'
                            ? sprintf(__('data_entry.dup_similar'), (string) $bucket, (string) $mId)
                            : sprintf('This looks %s%% similar to record #%s', (string) $bucket, (string) $mId);
                        $overview = isset($match['overview']) && is_array($match['overview']) ? $match['overview'] : [];
                    ?>
                    <div class="col-12 col-md-6">
                        <article class="card h-100 border-0 shadow-sm" aria-labelledby="sDup<?= $mId ?>">
                            <div class="card-body">
                                <h3 class="h6 fw-bold" id="sDup<?= $mId ?>"><span class="badge text-bg-warning me-1"><?= $bucket ?>%</span> <?= htmlspecialchars($title, ENT_QUOTES, 'UTF-8') ?></h3>
                                <?php foreach ($overview as $field): ?>
                                    <p class="small mb-1"><strong><?= htmlspecialchars((string) ($field['label'] ?? ''), ENT_QUOTES, 'UTF-8') ?>:</strong> <?= htmlspecialchars((string) ($field['value'] ?? ''), ENT_QUOTES, 'UTF-8') ?></p>
                                <?php endforeach; ?>
                            </div>
                        </article>
                    </div>
                <?php endforeach; ?>
            </div>
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
                        <?php elseif ($dataType === 'DATE'): ?>
                            <?= htmlspecialchars(format_display_date($valCont, $userDateFormat), ENT_QUOTES, 'UTF-8') ?>
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
        <form method="POST" action="<?= $basePath ?>/user/suggest-edit/save" onsubmit="return confirm('<?= htmlspecialchars(__('suggest_edit.confirm_prompt'), ENT_QUOTES, 'UTF-8') ?>');">
            <?= csrf_field() ?>
            <input type="hidden" name="record_id" value="<?= htmlspecialchars($recordId, ENT_QUOTES, 'UTF-8') ?>">
            <input type="hidden" name="return_url" value="<?= htmlspecialchars($returnUrl, ENT_QUOTES, 'UTF-8') ?>">

            <!-- Honeypot Anti-Spam Trap -->
            <div class="d-none" aria-hidden="true">
                <label for="website_hp" class="form-label">Leave this field blank</label>
                <input type="text" id="website_hp" name="website_hp" tabindex="-1" autocomplete="off" class="form-control">
            </div>
            <div class="d-none" aria-hidden="true">
                <label for="website_url" class="form-label">Leave this field blank</label>
                <input type="text" id="website_url" name="website_url" tabindex="-1" autocomplete="off" class="form-control">
            </div>

            <div class="mb-3">
                <label for="column_id" class="form-label small fw-bold"><?= htmlspecialchars(__('suggest_edit.select_column_label'), ENT_QUOTES, 'UTF-8') ?></label>
                <select id="column_id" name="column_id" required class="form-select form-select-sm" onchange="renderInputType()">
                    <?php foreach ($recordData as $data): ?>
                        <?php 
                            $cId = isset($data['column_id']) ? (int)$data['column_id'] : 0;
                            $cName = isset($data['column_name']) && is_string($data['column_name']) ? $data['column_name'] : '';
                        ?>
                        <option value="<?= $cId ?>" <?= ($draftColId === $cId) ? 'selected' : '' ?>>
                            <?= htmlspecialchars($cName, ENT_QUOTES, 'UTF-8') ?>
                        </option>
                    <?php endforeach; ?>
                </select>
            </div>

            <input type="hidden" name="proposed_value" id="proposed_value_posted" value="">
            <div id="input-container" class="mb-3">
                <!-- Dynamic input field rendered via JavaScript depending on column type -->
            </div>

            <div class="mb-3">
                <label for="reasoning" class="form-label small fw-bold"><?= htmlspecialchars(__('suggest_edit.reasoning_label'), ENT_QUOTES, 'UTF-8') ?></label>
                <textarea id="reasoning" name="reasoning" rows="3" placeholder="<?= htmlspecialchars(__('suggest_edit.reasoning_placeholder'), ENT_QUOTES, 'UTF-8') ?>" class="form-control form-control-sm" oninput="this.style.height = ''; this.style.height = this.scrollHeight + 'px';" style="overflow:hidden;"><?= htmlspecialchars($draftReasoning, ENT_QUOTES, 'UTF-8') ?></textarea>
            </div>

            <div class="mb-3">
                <div class="form-check">
                    <input class="form-check-input" type="checkbox" name="notify_outcome" value="1" id="notify_outcome" <?= $draftNotify ? 'checked' : '' ?>>
                    <label class="form-check-label" for="notify_outcome"><?= htmlspecialchars(__('suggest_edit.notify_label') !== 'suggest_edit.notify_label' ? __('suggest_edit.notify_label') : 'Email me when a moderator has decided', ENT_QUOTES, 'UTF-8') ?></label>
                </div>
                <?php if (empty($_SESSION['user_id'])): ?>
                    <label class="form-label small mt-2" for="notify_email"><?= htmlspecialchars(__('suggest_edit.notify_email_label') !== 'suggest_edit.notify_email_label' ? __('suggest_edit.notify_email_label') : 'Your email address', ENT_QUOTES, 'UTF-8') ?></label>
                    <input type="email" name="notify_email" id="notify_email" class="form-control form-control-sm" autocomplete="email" maxlength="255" value="<?= htmlspecialchars($draftNotifyEmail, ENT_QUOTES, 'UTF-8') ?>">
                    <div class="form-text"><?= htmlspecialchars(__('suggest_edit.notify_email_help') !== 'suggest_edit.notify_email_help' ? __('suggest_edit.notify_email_help') : 'Used only to tell you the outcome. It is not shown on the public record.', ENT_QUOTES, 'UTF-8') ?></div>
                <?php endif; ?>
            </div>

            <?php if (empty($_SESSION['user_id'])): ?>
                <?php
                $pdoInstance = (isset($pdo) && $pdo instanceof PDO) ? $pdo : null;
                if ($pdoInstance !== null && function_exists('render_form_captcha_widget')) {
                    echo render_form_captcha_widget($pdoInstance);
                }
                ?>
            <?php endif; ?>

            <div class="mb-3">
                <div class="form-check">
                    <input class="form-check-input" type="checkbox" name="report_duplicate" value="1" id="report_duplicate">
                    <label class="form-check-label" for="report_duplicate"><?= htmlspecialchars(__('suggest_edit.report_dup') !== 'suggest_edit.report_dup' ? __('suggest_edit.report_dup') : 'I think this record is a duplicate of another one', ENT_QUOTES, 'UTF-8') ?></label>
                </div>
                <label class="form-label small mt-2" for="duplicate_of"><?= htmlspecialchars(__('suggest_edit.dup_of_label') !== 'suggest_edit.dup_of_label' ? __('suggest_edit.dup_of_label') : 'If you know the other record number, you can add it here', ENT_QUOTES, 'UTF-8') ?></label>
                <input type="number" min="1" step="1" name="duplicate_of" id="duplicate_of" class="form-control form-control-sm" style="max-width: 10rem;" inputmode="numeric" aria-describedby="dupOfHelp">
                <div class="form-text" id="dupOfHelp"><?= htmlspecialchars(__('suggest_edit.dup_of_help') !== 'suggest_edit.dup_of_help' ? __('suggest_edit.dup_of_help') : 'Optional. Moderators will see this note.', ENT_QUOTES, 'UTF-8') ?></div>
            </div>

            <?php if (!empty($duplicateWarning) && !empty($matches) && (!isset($duplicateMode) || $duplicateMode !== 'block')): ?>
                <input type="hidden" name="confirm_duplicate" value="1">
            <?php endif; ?>

            <button type="submit" class="btn btn-sm btn-primary"><?= htmlspecialchars(__('suggest_edit.submit_btn'), ENT_QUOTES, 'UTF-8') ?></button>
        </form>
    </div>
</div>

<script>
const columnMeta = <?= json_encode($jsColumnMeta) ?>;
const draftProposed = <?= json_encode($draftProposed, JSON_UNESCAPED_UNICODE) ?>;
const draftColIdJs = <?= (int) $draftColId ?>;
const userDateFormat = '<?= htmlspecialchars($userDateFormat, ENT_QUOTES, 'UTF-8') ?>';
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
            <select id="proposed_value" required class="form-select form-select-sm">
                <option value="">${selectPlaceholder}</option>
                <option value="1" ${currentValue === '1' ? 'selected' : ''}>${opt1Text}</option>
                <option value="0" ${currentValue === '0' ? 'selected' : ''}>${opt2Text}</option>
            </select>
        `;
    } else if (col.data_type === 'SELECT') {
        const rawOpts = String(col.field_options || '').split(/\r\n|\r|\n/).map(s => s.trim()).filter(Boolean);
        const multi = String(col.allow_multiple) === '1' || col.allow_multiple === true || col.allow_multiple === 1;
        const current = String(col.value_content || '').split(',').map(s => s.trim());
        const req = (String(col.is_required) === '1' || col.is_required === true) ? 'required' : '';
        let optsHtml = '';
        if (!multi) {
            optsHtml += `<option value="">${selectPlaceholder}</option>`;
        }
        rawOpts.forEach(opt => {
            const sel = current.includes(opt) ? 'selected' : '';
            optsHtml += `<option value="${escapeHtml(opt)}" ${sel}>${escapeHtml(opt)}</option>`;
        });
        container.innerHTML = `
            <label for="proposed_value" class="form-label small fw-bold">${proposedValueLabel}</label>
            <select id="proposed_value" class="form-select form-select-sm" ${multi ? 'multiple aria-multiselectable="true"' : ''} ${req}>
                ${optsHtml}
            </select>
        `;
    } else if (col.data_type === 'INT') {
        const min = (col.min_value !== null && col.min_value !== undefined && col.min_value !== '') ? `min="${escapeHtml(String(col.min_value))}"` : '';
        const max = (col.max_value !== null && col.max_value !== undefined && col.max_value !== '') ? `max="${escapeHtml(String(col.max_value))}"` : '';
        const req = (String(col.is_required) === '1' || col.is_required === true) ? 'required' : '';
        container.innerHTML = `
            <label for="proposed_value" class="form-label small fw-bold">${proposedValueLabel}</label>
            <input type="number" id="proposed_value" value="${escapeHtml(col.value_content || '')}" class="form-control form-control-sm" ${min} ${max} ${req}>
        `;
    } else {
        // Use the pre-formatted value for dates, or raw value for text fields
        let currentValue = (col.data_type === 'DATE') ? (col.value_content_formatted || col.value_content) : col.value_content;
        container.innerHTML = `
            <label for="proposed_value" class="form-label small fw-bold">${proposedValueLabel}</label>
            <textarea id="proposed_value" rows="3" required class="form-control form-control-sm" oninput="this.style.height = ''; this.style.height = this.scrollHeight + 'px';" style="overflow:hidden;">${escapeHtml(currentValue)}</textarea>
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
if (draftProposed !== '' && typeof draftProposed === 'string') {
    const posted = document.getElementById('proposed_value_posted');
    if (posted) posted.value = draftProposed;
    const box = document.getElementById('input-container');
    if (box) {
        const input = box.querySelector('input, textarea, select');
        if (input) {
            if (input.tagName === 'SELECT' && input.multiple) {
                const parts = draftProposed.split('|');
                Array.from(input.options).forEach(function (o) { o.selected = parts.indexOf(o.value) !== -1; });
            } else {
                input.value = draftProposed;
            }
        }
    }
}


    const form = document.querySelector('form[action*="suggest-edit/save"]');
    if (form) {
        form.addEventListener('submit', function(e) {
            const select = document.getElementById('column_id');
            const proposedInput = document.getElementById('proposed_value');
            const posted = document.getElementById('proposed_value_posted');
            if (proposedInput && posted) {
                if (proposedInput.tagName === 'SELECT') {
                    posted.value = Array.from(proposedInput.selectedOptions)
                        .map(function (o) { return o.value.trim(); })
                        .filter(Boolean)
                        .join(', ');
                } else {
                    posted.value = proposedInput.value || '';
                }
            }
            if (!select || !proposedInput) return;

            const selectedColId = select.value;
            const col = columnMeta.find(c => c.column_id == selectedColId);

            if (col && col.data_type === 'DATE' && proposedInput.value.trim() !== '') {
                const val = proposedInput.value.trim();
                // Strictly enforce 4-digit year format
                const dateRegex = /^(\d{1,2}[\.\/\-]\d{1,2}[\.\/\-]\d{4})|(\d{4}[\.\/\-]\d{1,2}[\.\/\-]\d{1,2})$/;

                if (!dateRegex.test(val)) {
                    e.preventDefault();
                    alert("Please enter a valid date with a 4-digit year (e.g., DD/MM/YYYY, DD.MM.YYYY, or YYYY-MM-DD).");
                    proposedInput.value = '';
                    proposedInput.focus();
                }
            }
        });
    }
});
</script>

<?php require_once ROOT_PATH . '/partials/footer.php'; ?>
