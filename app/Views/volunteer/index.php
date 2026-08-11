<?php
/**
 * MIGRATED FILE MAPPING
 * ---------------------
 * Original Old File: roote/volunteer.php
 * Migrated Date: 2026-08-05 07:01:53
 */
declare(strict_types=1);
/**
 * @var \PDO $pdo
 * @var string $formTitle
 * @var string $formIntro
 * @var array<int, array<string, mixed>> $columns
 * @var string $message
 * @var string $error
 * @var array<mixed, mixed> $submittedData
 * @var string $submittedFirst
 * @var string $submittedSurname
 * @var string $submittedEmail
 */
$submittedUsername = isset($_SESSION['submitted_volunteer_username']) && is_string($_SESSION['submitted_volunteer_username'])
    ? $_SESSION['submitted_volunteer_username'] : '';

require_once ROOT_PATH . '/partials/header.php';
$basePath = defined('BASE_PATH') ? rtrim(BASE_PATH, '/') : '';
?>
<div class="container my-5" style="max-width: 650px;" role="region" aria-label="<?= htmlspecialchars(__('volunteer.aria_region'), ENT_QUOTES, 'UTF-8') ?>">
    <div class="card border-0 shadow-sm p-4 bg-white">
        <h3 class="h4 fw-bold text-dark mb-2"><?= htmlspecialchars($formTitle, ENT_QUOTES, 'UTF-8') ?></h3>
        <p class="text-muted small mb-4"><?= nl2br(htmlspecialchars($formIntro, ENT_QUOTES, 'UTF-8')) ?></p>
        <?php if ($error !== ''): ?>
            <div class="alert alert-danger alert-dismissible fade show shadow-sm" role="alert" aria-live="polite">
                <strong><?= htmlspecialchars($error, ENT_QUOTES, 'UTF-8') ?></strong>
                <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
            </div>
        <?php endif; ?>
        <?php if ($message !== ''): ?>
            <div class="alert alert-success alert-dismissible fade show shadow-sm" role="alert" aria-live="polite">
                <strong><?= htmlspecialchars($message, ENT_QUOTES, 'UTF-8') ?></strong>
                <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
            </div>
        <?php endif; ?>
        <form method="POST" action="<?= $basePath ?>/volunteer" id="volunteer-form">
            <?= function_exists('csrf_field') ? csrf_field() : '' ?>

            <div class="d-none" aria-hidden="true">
                <label for="website_url" class="form-label"><?= htmlspecialchars(__('volunteer.honeypot_label'), ENT_QUOTES, 'UTF-8') ?></label>
                <input type="text" id="website_url" name="website_url" value="" autocomplete="off" tabindex="-1" class="form-control">
            </div>

            <div class="row g-3 mb-3">
                <div class="col-md-6">
                    <label for="volunteer_first_name" class="form-label small fw-bold">
                        <?= htmlspecialchars(__('feedback.first_name_label'), ENT_QUOTES, 'UTF-8') ?> <span class="text-danger">*</span>
                    </label>
                    <input type="text" id="volunteer_first_name" name="volunteer_first_name" value="<?= htmlspecialchars($submittedFirst, ENT_QUOTES, 'UTF-8') ?>" required class="form-control form-control-sm">
                </div>
                <div class="col-md-6">
                    <label for="volunteer_surname" class="form-label small fw-bold">
                        <?= htmlspecialchars(__('feedback.surname_label'), ENT_QUOTES, 'UTF-8') ?> <span class="text-danger">*</span>
                    </label>
                    <input type="text" id="volunteer_surname" name="volunteer_surname" value="<?= htmlspecialchars($submittedSurname, ENT_QUOTES, 'UTF-8') ?>" required class="form-control form-control-sm">
                </div>
            </div>

            <div class="mb-3">
                <label for="volunteer_email" class="form-label small fw-bold">
                    <?= htmlspecialchars(__('forgot_password.email_label'), ENT_QUOTES, 'UTF-8') ?> <span class="text-danger">*</span>
                </label>
                <input type="email" id="volunteer_email" name="volunteer_email" value="<?= htmlspecialchars($submittedEmail, ENT_QUOTES, 'UTF-8') ?>" required class="form-control form-control-sm">
            </div>

            <div class="mb-3">
                <label for="preferred_username" class="form-label small fw-bold">Preferred username (optional)</label>
                <input type="text"
                       id="preferred_username"
                       name="preferred_username"
                       class="form-control form-control-sm"
                       autocomplete="username"
                       pattern="[A-Za-z0-9_\-]+"
                       maxlength="50"
                       title="Letters, numbers, underscore, and hyphen only"
                       value="<?= htmlspecialchars($submittedUsername, ENT_QUOTES, 'UTF-8') ?>">
                <div class="form-text">Letters, numbers, underscore, hyphen. Leave blank or choose auto-allocate below.</div>
                <button type="button" class="btn btn-outline-secondary btn-sm mt-1" id="check-username-btn">Check availability</button>
                <div id="username-check-result" class="form-text mt-1" aria-live="polite"></div>
            </div>
            <div class="form-check mb-4">
                <input type="checkbox" class="form-check-input" name="auto_username" id="auto_username" value="1">
                <label class="form-check-label small" for="auto_username">Allocate a unique username for me</label>
            </div>

            <hr class="text-muted my-4">

            <?php if (!empty($columns)): ?>
                <?php foreach ($columns as $col):
                    $cId = isset($col['id']) ? (int)$col['id'] : 0;
                    $cName = isset($col['column_name']) && is_string($col['column_name']) ? $col['column_name'] : '';
                    $savedVal = $submittedData[$cId] ?? '';
                    $maxLen = isset($col['max_length']) ? (int)$col['max_length'] : 0;
                    $maxAttr = $maxLen > 0 ? 'maxlength="' . $maxLen . '"' : '';
                    $subtype = isset($col['field_subtype']) && is_string($col['field_subtype']) ? $col['field_subtype'] : '';
                    $fieldOpts = isset($col['field_options']) && is_string($col['field_options']) ? $col['field_options'] : '';
                    $options = array_filter(array_map('trim', explode(',', $fieldOpts)));
                    $allowMulti = !empty($col['allow_multiple']);
                    $isRequired = !empty($col['is_required']) && !($allowMulti || $subtype === 'checkbox');
                ?>
                    <div class="mb-3">
                        <label for="field_<?= $cId ?>" class="form-label small fw-bold">
                            <?= htmlspecialchars($cName, ENT_QUOTES, 'UTF-8') ?>:
                            <?php if (!empty($col['is_required'])): ?>
                                <span class="text-danger" title="<?= htmlspecialchars(__('volunteer.required_field_title'), ENT_QUOTES, 'UTF-8') ?>">*</span>
                            <?php endif; ?>
                        </label>
                        <?php if (isset($col['data_type']) && $col['data_type'] === 'BOOLEAN'): ?>
                            <?php
                                $fmt = isset($col['boolean_display_format']) && is_string($col['boolean_display_format']) ? $col['boolean_display_format'] : 'yes_no';
                                $opt1 = ($fmt === 'true_false') ? __('data_entry.bool_true') : __('data_entry.bool_yes_true');
                                $opt2 = ($fmt === 'true_false') ? __('data_entry.bool_false') : __('data_entry.bool_no_false');
                            ?>
                            <select id="field_<?= $cId ?>" name="fields[<?= $cId ?>]" class="form-select form-select-sm" <?= $isRequired ? 'required' : '' ?>>
                                <option value=""><?= htmlspecialchars(__('feedback.select_placeholder'), ENT_QUOTES, 'UTF-8') ?></option>
                                <option value="1" <?= ($savedVal === '1') ? 'selected' : '' ?>><?= $opt1 ?></option>
                                <option value="0" <?= ($savedVal === '0') ? 'selected' : '' ?>><?= $opt2 ?></option>
                            </select>
                        <?php elseif ($subtype === 'email'): ?>
                            <input type="email" id="field_<?= $cId ?>" name="fields[<?= $cId ?>]" value="<?= htmlspecialchars(is_string($savedVal) ? $savedVal : '', ENT_QUOTES, 'UTF-8') ?>" class="form-control form-control-sm" <?= $maxAttr ?> <?= $isRequired ? 'required' : '' ?>>
                        <?php elseif ($subtype === 'url'): ?>
                            <input type="url" id="field_<?= $cId ?>" name="fields[<?= $cId ?>]" value="<?= htmlspecialchars(is_string($savedVal) ? $savedVal : '', ENT_QUOTES, 'UTF-8') ?>" class="form-control form-control-sm" <?= $maxAttr ?> <?= $isRequired ? 'required' : '' ?>>
                        <?php elseif ($subtype === 'number'): ?>
                            <input type="number" id="field_<?= $cId ?>" name="fields[<?= $cId ?>]" value="<?= htmlspecialchars(is_string($savedVal) ? $savedVal : '', ENT_QUOTES, 'UTF-8') ?>" class="form-control form-control-sm" <?= $isRequired ? 'required' : '' ?>>
                        <?php elseif ($subtype === 'textarea'): ?>
                            <textarea id="field_<?= $cId ?>" name="fields[<?= $cId ?>]" rows="3" class="form-control form-control-sm auto-expand-textarea" style="resize: vertical;" <?= $maxAttr ?> <?= $isRequired ? 'required' : '' ?>><?= htmlspecialchars(is_string($savedVal) ? $savedVal : '', ENT_QUOTES, 'UTF-8') ?></textarea>
                        <?php elseif ($subtype === 'select' || $subtype === 'dropdown'): ?>
                            <?php
                                $selectedVals = $allowMulti
                                    ? (is_array($savedVal) ? $savedVal : explode(', ', is_string($savedVal) ? $savedVal : ''))
                                    : [is_string($savedVal) ? $savedVal : ''];
                            ?>
                            <select id="field_<?= $cId ?>" name="fields[<?= $cId ?>]<?= $allowMulti ? '[]' : '' ?>" class="form-select form-select-sm" <?= $allowMulti ? 'multiple size="4"' : '' ?>>
                                <?php if (!$allowMulti): ?>
                                    <option value=""><?= htmlspecialchars(__('feedback.select_placeholder'), ENT_QUOTES, 'UTF-8') ?></option>
                                <?php endif; ?>
                                <?php foreach ($options as $opt): ?>
                                    <option value="<?= htmlspecialchars($opt, ENT_QUOTES, 'UTF-8') ?>" <?= in_array($opt, $selectedVals, true) ? 'selected' : '' ?>><?= htmlspecialchars($opt, ENT_QUOTES, 'UTF-8') ?></option>
                                <?php endforeach; ?>
                            </select>
                            <?php if ($allowMulti): ?>
                                <div class="form-text text-muted small"><?= htmlspecialchars(__('volunteer.multi_select_hint'), ENT_QUOTES, 'UTF-8') ?></div>
                            <?php endif; ?>
                        <?php elseif ($subtype === 'checkbox' || ($subtype === 'radio' && $allowMulti)): ?>
                            <?php $selectedVals = is_array($savedVal) ? $savedVal : explode(', ', is_string($savedVal) ? $savedVal : ''); ?>
                            <div class="d-flex flex-column gap-1">
                                <?php foreach ($options as $opt): ?>
                                    <div class="form-check">
                                        <input class="form-check-input" type="checkbox" name="fields[<?= $cId ?>][]" value="<?= htmlspecialchars($opt, ENT_QUOTES, 'UTF-8') ?>" id="chk_<?= $cId ?>_<?= md5($opt) ?>" <?= in_array($opt, $selectedVals, true) ? 'checked' : '' ?>>
                                        <label class="form-check-label small" for="chk_<?= $cId ?>_<?= md5($opt) ?>">
                                            <?= htmlspecialchars($opt, ENT_QUOTES, 'UTF-8') ?>
                                        </label>
                                    </div>
                                <?php endforeach; ?>
                            </div>
                        <?php elseif ($subtype === 'radio'): ?>
                            <div class="d-flex gap-3 flex-wrap">
                                <?php foreach ($options as $opt): ?>
                                    <div class="form-check">
                                        <input class="form-check-input" type="radio" name="fields[<?= $cId ?>]" value="<?= htmlspecialchars($opt, ENT_QUOTES, 'UTF-8') ?>" id="rad_<?= $cId ?>_<?= md5($opt) ?>" <?= ($savedVal === $opt) ? 'checked' : '' ?>>
                                        <label class="form-check-label small" for="rad_<?= $cId ?>_<?= md5($opt) ?>">
                                            <?= htmlspecialchars($opt, ENT_QUOTES, 'UTF-8') ?>
                                        </label>
                                    </div>
                                <?php endforeach; ?>
                            </div>
                        <?php else: ?>
                            <input type="text" id="field_<?= $cId ?>" name="fields[<?= $cId ?>]" value="<?= htmlspecialchars(is_string($savedVal) ? $savedVal : '', ENT_QUOTES, 'UTF-8') ?>" class="form-control form-control-sm" <?= $maxAttr ?> <?= $isRequired ? 'required' : '' ?>>
                        <?php endif; ?>
                    </div>
                <?php endforeach; ?>
            <?php endif; ?>

            <div class="mb-3">
                <?= render_form_captcha_widget($pdo) ?>
            </div>
            <button type="submit" class="btn btn-primary btn-sm px-4 fw-bold mt-2" id="volunteer-submit-btn"><?= htmlspecialchars(__('volunteer.submit_btn'), ENT_QUOTES, 'UTF-8') ?></button>
        </form>
    </div>
</div>
<script>
document.addEventListener('input', function (event) {
    if (event.target.classList.contains('auto-expand-textarea')) {
        event.target.style.height = 'auto';
        event.target.style.height = (event.target.scrollHeight) + 'px';
    }
});

(function () {
    const autoBox = document.getElementById('auto_username');
    const userInput = document.getElementById('preferred_username');
    const checkBtn = document.getElementById('check-username-btn');
    const resultEl = document.getElementById('username-check-result');
    const form = document.getElementById('volunteer-form');
    if (!autoBox || !userInput || !checkBtn || !resultEl || !form) return;

    function usernameFormatOk(value) {
        return value === '' || /^[A-Za-z0-9_-]+$/.test(value);
    }

    function syncUsernameField() {
        if (autoBox.checked) {
            userInput.value = '';
            userInput.disabled = true;
            checkBtn.disabled = true;
            resultEl.textContent = '';
            resultEl.classList.remove('text-success', 'text-danger', 'text-warning');
            userInput.setCustomValidity('');
        } else {
            userInput.disabled = false;
            checkBtn.disabled = false;
        }
    }
    autoBox.addEventListener('change', syncUsernameField);
    syncUsernameField();

    // Strip illegal characters + message if anything was removed
    userInput.addEventListener('input', function () {
        const before = userInput.value;
        const cleaned = before.replace(/[^A-Za-z0-9_-]/g, '');
        if (cleaned !== before) {
            userInput.value = cleaned;
            resultEl.textContent = 'Only letters, numbers, underscore, and hyphen are allowed.';
            resultEl.classList.remove('text-success', 'text-warning');
            resultEl.classList.add('text-danger');
        }
        const v = userInput.value.trim();
        if (v !== '' && !usernameFormatOk(v)) {
            userInput.setCustomValidity('Use only letters, numbers, underscore, and hyphen.');
        } else {
            userInput.setCustomValidity('');
            if (resultEl.textContent.indexOf('Only letters') === 0 || resultEl.textContent.indexOf('Use only letters') === 0) {
                resultEl.textContent = '';
                resultEl.classList.remove('text-danger');
            }
        }
    });

    checkBtn.addEventListener('click', function () {
        const name = userInput.value.trim();
        if (name !== '' && !usernameFormatOk(name)) {
            resultEl.textContent = 'Use only letters, numbers, underscore, and hyphen.';
            resultEl.classList.remove('text-success', 'text-warning');
            resultEl.classList.add('text-danger');
            return;
        }

        resultEl.textContent = 'Checking…';
        resultEl.classList.remove('text-success', 'text-danger', 'text-warning');

        const fd = new FormData(form);
        fd.set('username', name);

        fetch('<?= htmlspecialchars($basePath, ENT_QUOTES, 'UTF-8') ?>/volunteer/check-username', {
            method: 'POST',
            body: fd,
            credentials: 'same-origin',
            headers: { 'X-Requested-With': 'XMLHttpRequest' }
        })
        .then(function (r) { return r.json(); })
        .then(function (data) {
            resultEl.textContent = data.message || '';
            if (data.limited) {
                resultEl.classList.add('text-warning');
                userInput.disabled = true;
                checkBtn.disabled = true;
            } else if (data.available) {
                resultEl.classList.add('text-success');
            } else {
                resultEl.classList.add('text-danger');
            }
        })
        .catch(function () {
            resultEl.textContent = 'Could not check username. Try again.';
            resultEl.classList.add('text-danger');
        });
    });

    // Prevent double submit while mail is slow
    form.addEventListener('submit', function () {
        const btn = document.getElementById('volunteer-submit-btn');
        if (btn) {
            btn.disabled = true;
            btn.textContent = 'Submitting…';
        }
    });
})();
</script>
<?php require_once ROOT_PATH . '/partials/footer.php'; ?>
