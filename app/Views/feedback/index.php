<?php
/**
 * MIGRATED FILE MAPPING
 * ---------------------
 * Original Old File: roote/feedback.php
 * Migrated Date: 2026-08-05 06:58:54
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
 * @var string $submittedSubject
 */

require_once ROOT_PATH . '/partials/header.php';
$basePath = defined('BASE_PATH') ? rtrim(BASE_PATH, '/') : '';
?>

<div class="container my-5" style="max-width: 650px;">
    <div class="card border-0 shadow-sm p-4 bg-white">
        <h3 class="h4 fw-bold text-dark mb-2"><?= htmlspecialchars($formTitle, ENT_QUOTES, 'UTF-8') ?></h3>
        <p class="text-muted small mb-4"><?= nl2br(htmlspecialchars($formIntro, ENT_QUOTES, 'UTF-8')) ?></p>

        <?php if ($message !== ''): ?>
            <div class="alert alert-success alert-dismissible fade show shadow-sm" role="alert">
                <strong><?= htmlspecialchars($message, ENT_QUOTES, 'UTF-8') ?></strong>
                <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
            </div>
        <?php endif; ?>

        <?php if ($error !== ''): ?>
            <div class="alert alert-danger alert-dismissible fade show shadow-sm" role="alert">
                <strong><?= htmlspecialchars($error, ENT_QUOTES, 'UTF-8') ?></strong>
                <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
            </div>
        <?php endif; ?>

        <form method="POST" action="<?= $basePath ?>/feedback">
            <?= function_exists('csrf_field') ? csrf_field() : '' ?>
            
            <!-- Honeypot -->
            <div class="d-none" aria-hidden="true">
                <label for="website_hp" class="form-label"><?= htmlspecialchars(__('feedback.hp_label'), ENT_QUOTES, 'UTF-8') ?></label>
                <input type="text" id="website_hp" name="website_hp" tabindex="-1" autocomplete="off" class="form-control">
            </div>

            <!-- Core Static Identity Fields -->
            <div class="row g-3 mb-3">
                <div class="col-md-6">
                    <label for="feedback_first_name" class="form-label small fw-bold">
                        <?= htmlspecialchars(__('feedback.first_name_label'), ENT_QUOTES, 'UTF-8') ?> <span class="text-danger">*</span>
                    </label>
                    <input type="text" id="feedback_first_name" name="feedback_first_name" value="<?= htmlspecialchars($submittedFirst, ENT_QUOTES, 'UTF-8') ?>" required class="form-control form-control-sm">
                </div>
                <div class="col-md-6">
                    <label for="feedback_surname" class="form-label small fw-bold">
                        <?= htmlspecialchars(__('feedback.surname_label'), ENT_QUOTES, 'UTF-8') ?> <span class="text-danger">*</span>
                    </label>
                    <input type="text" id="feedback_surname" name="feedback_surname" value="<?= htmlspecialchars($submittedSurname, ENT_QUOTES, 'UTF-8') ?>" required class="form-control form-control-sm">
                </div>
            </div>

            <div class="mb-3">
                <label for="feedback_email" class="form-label small fw-bold">
                    <?= htmlspecialchars(__('feedback.email_label'), ENT_QUOTES, 'UTF-8') ?> <span class="text-danger">*</span>
                </label>
                <input type="email" id="feedback_email" name="feedback_email" value="<?= htmlspecialchars($submittedEmail, ENT_QUOTES, 'UTF-8') ?>" required class="form-control form-control-sm">
            </div>

            <div class="mb-4">
                <label for="feedback_subject" class="form-label small fw-bold">
                    <?= htmlspecialchars(__('feedback.subject_label'), ENT_QUOTES, 'UTF-8') ?> <span class="text-danger">*</span>
                </label>
                <input type="text" id="feedback_subject" name="feedback_subject" value="<?= htmlspecialchars($submittedSubject, ENT_QUOTES, 'UTF-8') ?>" required class="form-control form-control-sm">
            </div>

            <hr class="text-muted my-4">

            <!-- Dynamic Custom Fields -->
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
                                <span class="text-danger" title="<?= htmlspecialchars(__('feedback.required_title'), ENT_QUOTES, 'UTF-8') ?>">*</span>
                            <?php endif; ?>
                        </label>

                        <?php if (isset($col['data_type']) && $col['data_type'] === 'BOOLEAN'): ?>
                            <?php 
                                $fmt = isset($col['boolean_display_format']) && is_string($col['boolean_display_format']) ? $col['boolean_display_format'] : 'yes_no';
                                $opt1 = ($fmt === 'true_false') ? 'True' : 'Yes';
                                $opt2 = ($fmt === 'true_false') ? 'False' : 'No';
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
                                <div class="form-text text-muted small"><?= htmlspecialchars(__('feedback.multi_select_hint'), ENT_QUOTES, 'UTF-8') ?></div>
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
            
            <!-- Dynamic CAPTCHA Widget -->
            <div class="mb-3">
                <?= render_form_captcha_widget($pdo) ?>
            </div>

            <button type="submit" class="btn btn-primary btn-sm px-4 fw-bold mt-2"><?= htmlspecialchars(__('feedback.submit_btn'), ENT_QUOTES, 'UTF-8') ?></button>
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
</script>

<?php require_once ROOT_PATH . '/partials/footer.php'; ?>
