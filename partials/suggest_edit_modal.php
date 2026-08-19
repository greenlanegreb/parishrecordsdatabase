<?php
declare(strict_types=1);
/**
 * MIGRATED FILE MAPPING
 * ---------------------
 * Original Old File: partials/suggest_edit_modal.php
 * Migrated Date: 2026-08-05 07:10:00
 */

/** @var array<int, array<string, mixed>>|null $columns */
if (!isset($columns) || !is_array($columns)) {
    $columns = [];
}

$suggestReturnUrl = isset($suggestReturnUrl) && is_string($suggestReturnUrl) 
    ? $suggestReturnUrl 
    : (defined('BASE_PATH') && is_string(BASE_PATH) ? rtrim(BASE_PATH, '/') . '/index.php' : '/index.php');

$suggestTableId = isset($suggestTableId) ? (int)$suggestTableId : (isset($activeTableId) ? (int)$activeTableId : 0);

$serverSelf = isset($_SERVER['PHP_SELF']) && is_string($_SERVER['PHP_SELF']) ? $_SERVER['PHP_SELF'] : '';
$suggestAction = (strpos($serverSelf, '/user/') !== false)
    ? 'actions/save_suggestion.php'
    : '/user/actions/save_suggestion.php';
?>

<!-- Bootstrap 5 Suggest Edit Modal -->
<div class="modal fade" id="suggestModal" tabindex="-1" aria-labelledby="suggestModalLabel" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered">
        <div class="modal-content shadow-sm border-0">
            <div class="modal-header border-bottom-0 pb-0">
                <h3 class="modal-title h5 fw-bold text-dark" id="suggestModalLabel"><?= htmlspecialchars(__('index.modal_heading'), ENT_QUOTES, 'UTF-8') ?></h3>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="<?= htmlspecialchars(__('btn.cancel'), ENT_QUOTES, 'UTF-8') ?>"></button>
            </div>
            
            <form method="POST" action="<?= htmlspecialchars($suggestAction, ENT_QUOTES, 'UTF-8') ?>">
                <div class="modal-body pt-2">
                    <p class="text-muted small mb-3">
                        <?= htmlspecialchars(__('index.modal_desc'), ENT_QUOTES, 'UTF-8') ?>
                    </p>

                    <?= function_exists('csrf_field') ? csrf_field() : '' ?>
                    <input type="hidden" name="record_id" id="modal_record_id" value="">
                    <input type="hidden" name="return_url" value="<?= htmlspecialchars($suggestReturnUrl, ENT_QUOTES, 'UTF-8') ?>">
                    <input type="hidden" name="table_id" value="<?= $suggestTableId ?>">

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
                        <label for="modal_column_id" class="form-label small fw-bold"><?= htmlspecialchars(__('index.modal_target_column'), ENT_QUOTES, 'UTF-8') ?></label>
                        <select name="column_id" id="modal_column_id" class="form-select" required>
                            <?php foreach ($columns as $col): ?>
                                <?php 
                                    $colId = isset($col['id']) ? (int)$col['id'] : 0;
                                    $colName = isset($col['column_name']) && is_string($col['column_name']) ? $col['column_name'] : '';
                                ?>
                                <option value="<?= $colId ?>">
                                    <?= htmlspecialchars($colName, ENT_QUOTES, 'UTF-8') ?>
                                </option>
                            <?php endforeach; ?>
                        </select>
                    </div>

                    <input type="hidden"  id="modal_proposed_value_posted" name="proposed_value" value="">
                    <div class="mb-3" id="modal_proposed_container">
                        <label for="modal_proposed_value" class="form-label small fw-bold"><?= htmlspecialchars(__('index.modal_proposed_value'), ENT_QUOTES, 'UTF-8') ?></label>
                        <input type="text" id="modal_proposed_value"
                               placeholder="<?= htmlspecialchars(__('index.modal_input_placeholder'), ENT_QUOTES, 'UTF-8') ?>"
                               class="form-control" required>
                    </div>

                    <div class="mb-3">
                        <label for="modal_reasoning" class="form-label small fw-bold"><?= htmlspecialchars(__('suggest_edit.reasoning_label'), ENT_QUOTES, 'UTF-8') ?></label>
                        <textarea name="reasoning" id="modal_reasoning" rows="2"
                                  placeholder="<?= htmlspecialchars(__('suggest_edit.reasoning_placeholder'), ENT_QUOTES, 'UTF-8') ?>"
                                  class="form-control"></textarea>
                        <div class="form-text text-muted small">
                            <?= htmlspecialchars(__('suggest_edit.reasoning_optional') !== 'suggest_edit.reasoning_optional' ? __('suggest_edit.reasoning_optional') : 'Optional — evidence, source, or notes for moderators.', ENT_QUOTES, 'UTF-8') ?>
                        </div>
                    </div>

                    <?php
                    $pdoInstance = (isset($pdo) && $pdo instanceof PDO) ? $pdo : null;
                    if ($pdoInstance !== null) {
                        if (function_exists('render_form_captcha_widget')) {
                            echo render_form_captcha_widget($pdoInstance);
                        } elseif (function_exists('render_form_captcha')) {
                            echo render_form_captcha($pdoInstance);
                        } elseif (function_exists('get_form_captcha_html')) {
                            echo get_form_captcha_html($pdoInstance);
                        }
                    }
                    ?>
                </div>

                <div class="modal-footer border-top-0 pt-0">
                    <button type="button" class="btn btn-secondary btn-sm px-3" data-bs-dismiss="modal"><?= htmlspecialchars(__('btn.cancel'), ENT_QUOTES, 'UTF-8') ?></button>
                    <button type="submit" class="btn btn-primary btn-sm px-4"><?= htmlspecialchars(__('index.modal_submit_btn'), ENT_QUOTES, 'UTF-8') ?></button>
                </div>
            </form>
        </div>
    </div>
</div>

<script>
function openSuggestModal(recordId) {
    var recordInput = document.getElementById('modal_record_id');
    if (recordInput) {
        recordInput.value = recordId;
    }
    var modalElement = document.getElementById('suggestModal');
    if (modalElement && typeof bootstrap !== 'undefined') {
        var myModal = bootstrap.Modal.getOrCreateInstance(modalElement);
        myModal.show();
    }
}

function closeSuggestModal() {
    var modalElement = document.getElementById('suggestModal');
    if (modalElement && typeof bootstrap !== 'undefined') {
        var myModal = bootstrap.Modal.getInstance(modalElement);
        if (myModal) {
            myModal.hide();
        }
    }
}

const modalColumnMeta = <?= json_encode(array_values($columns), JSON_UNESCAPED_UNICODE) ?>;
const modalSelectPlaceholder = <?= json_encode(__('feedback.select_placeholder'), JSON_UNESCAPED_UNICODE) ?>;
const modalMultiHint = <?= json_encode(__('data_entry.multiselect_hint') !== 'data_entry.multiselect_hint' ? __('data_entry.multiselect_hint') : 'Hold Ctrl (or Cmd) to choose more than one.', JSON_UNESCAPED_UNICODE) ?>;

function escapeModalHtml(text) {
    return String(text).replace(/[&<>"']/g, function (m) {
        return ({'&':'&amp;','<':'&lt;','>':'&gt;','"':'&quot;',"'":'&#039;'}[m]);
    });
}

function renderModalProposedInput() {
    const select = document.getElementById('modal_column_id');
    const wrap = document.getElementById('modal_proposed_container');
    if (!select || !wrap) return;
    const col = (modalColumnMeta || []).find(function (c) { return String(c.id) === String(select.value); });
    const label = <?= json_encode(__('index.modal_proposed_value'), JSON_UNESCAPED_UNICODE) ?>;
    const placeholder = <?= json_encode(__('index.modal_input_placeholder'), JSON_UNESCAPED_UNICODE) ?>;
    if (!col) return;
    const type = col.data_type || '';
    if (type === 'SELECT') {
        const rawOpts = String(col.field_options || '').split(/\r\n|\r|\n/).map(function (s) { return s.trim(); }).filter(Boolean);
        const multi = String(col.allow_multiple) === '1' || col.allow_multiple === true || col.allow_multiple === 1;
        const req = (String(col.is_required) === '1' || col.is_required) ? 'required' : '';
        let opts = multi ? '' : '<option value="">' + escapeModalHtml(modalSelectPlaceholder) + '</option>';
        rawOpts.forEach(function (opt) {
            opts += '<option value="' + escapeModalHtml(opt) + '">' + escapeModalHtml(opt) + '</option>';
        });
        wrap.innerHTML = '<label for="modal_proposed_value" class="form-label small fw-bold">' + escapeModalHtml(label) + '</label>' +
            '<select  id="modal_proposed_value" class="form-select" ' + (multi ? 'multiple aria-multiselectable="true" ' : '') + req + '>' + opts + '</select>' +
            (multi ? '<div class="form-text">' + escapeModalHtml(modalMultiHint) + '</div>' : '');
    } else if (type === 'INT') {
        const min = (col.min_value !== null && col.min_value !== undefined && col.min_value !== '') ? ' min="' + escapeModalHtml(String(col.min_value)) + '"' : '';
        const max = (col.max_value !== null && col.max_value !== undefined && col.max_value !== '') ? ' max="' + escapeModalHtml(String(col.max_value)) + '"' : '';
        const req = (String(col.is_required) === '1' || col.is_required) ? ' required' : '';
        wrap.innerHTML = '<label for="modal_proposed_value" class="form-label small fw-bold">' + escapeModalHtml(label) + '</label>' +
            '<input type="number"  id="modal_proposed_value" class="form-control"' + min + max + req + '>';
    } else if (type === 'BOOLEAN') {
        wrap.innerHTML = '<label for="modal_proposed_value" class="form-label small fw-bold">' + escapeModalHtml(label) + '</label>' +
            '<select  id="modal_proposed_value" class="form-select" required>' +
            '<option value="">' + escapeModalHtml(modalSelectPlaceholder) + '</option>' +
            '<option value="1">1</option><option value="0">0</option></select>';
    } else {
        wrap.innerHTML = '<label for="modal_proposed_value" class="form-label small fw-bold">' + escapeModalHtml(label) + '</label>' +
            '<input type="text"  id="modal_proposed_value" placeholder="' + escapeModalHtml(placeholder) + '" class="form-control" required>';
    }
}

function syncModalProposedValue() {
    const ui = document.getElementById('modal_proposed_value');
    const posted = document.getElementById('modal_proposed_value_posted');
    if (!ui || !posted) return;
    if (ui.tagName === 'SELECT') {
        posted.value = Array.from(ui.selectedOptions).map(function (o) { return o.value.trim(); }).filter(Boolean).join(', ');
    } else {
        posted.value = ui.value || '';
    }
}
document.addEventListener('DOMContentLoaded', function () {
    const select = document.getElementById('modal_column_id');
    if (select) {
        select.addEventListener('change', renderModalProposedInput);
        renderModalProposedInput();
    }
    const form = document.querySelector('#suggestModal form');
    if (form) {
        form.addEventListener('submit', syncModalProposedValue);
    }
});
</script>
