<?php
/**
 * MIGRATED FILE MAPPING
 * ---------------------
 * Original Old File: admin/moderate.php/admin/actions/save_moderation.php
 * Migrated Date: 2026-08-05 03:37:44
 */declare(strict_types=1);

/**
 * MIGRATED FILE MAPPING
 * ---------------------
 * Original Old File: admin/moderate.php
 * Migrated Date: 2026-08-04 10:05:22
 */

/** @string $message */
/** @string $error */
/** @string $userTimezone */
/** @string $fullFormatStr */
/** @array{id: int, username: string, date_format?: string} $currentUser */
/** @array<int, array<string, mixed>> $pendingSuggestions */

require_once __DIR__ . '/../partials/header.php';
?>

<div class="container py-4" style="max-width: 1200px;">
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

    <h3 class="fw-bold mb-1"><?= htmlspecialchars(__('moderate.heading'), ENT_QUOTES, 'UTF-8') ?></h3>
    <p class="text-muted mb-3"><?= htmlspecialchars(__('moderate.subheading'), ENT_QUOTES, 'UTF-8') ?></p>

    <div class="alert alert-primary border-start border-4 border-primary shadow-sm mb-4">
        <p class="mb-0 small text-dark">
            ⚡ <strong><?= htmlspecialchars(__('moderate.shortcut_label'), ENT_QUOTES, 'UTF-8') ?></strong> <?= htmlspecialchars(__('moderate.shortcut_desc'), ENT_QUOTES, 'UTF-8') ?>
        </p>
    </div>

    <div class="card shadow-sm border-0">
        <div class="table-responsive">
            <table class="table table-hover align-middle mb-0" role="table">
                <thead class="table-light">
                    <tr>
                        <th scope="col" class="py-3 ps-3"><?= htmlspecialchars(__('moderate.th_id_date'), ENT_QUOTES, 'UTF-8') ?></th>
                        <th scope="col" class="py-3"><?= htmlspecialchars(__('moderate.th_table_record'), ENT_QUOTES, 'UTF-8') ?></th>
                        <th scope="col" class="py-3"><?= htmlspecialchars(__('moderate.th_comparison'), ENT_QUOTES, 'UTF-8') ?></th>
                        <th scope="col" class="py-3 pe-3 text-end"><?= htmlspecialchars(__('moderate.th_actions'), ENT_QUOTES, 'UTF-8') ?></th>
                    </tr>
                </thead>
                <tbody>
                    <?php if (empty($pendingSuggestions)): ?>
                        <tr>
                            <td colspan="4" class="text-center py-4 text-muted"><?= htmlspecialchars(__('moderate.no_suggestions'), ENT_QUOTES, 'UTF-8') ?></td>
                        </tr>
                    <?php else: ?>
                        <?php foreach ($pendingSuggestions as $s): ?>
                            <?php 
                                $sId = isset($s['id']) ? (int)$s['id'] : 0;
                                $recordId = isset($s['record_id']) ? (int)$s['record_id'] : 0;
                                $createdAt = isset($s['created_at']) && is_string($s['created_at']) ? $s['created_at'] : '';
                                $tableName = isset($s['table_name']) && is_string($s['table_name']) ? $s['table_name'] : '';
                                $columnName = isset($s['column_name']) && is_string($s['column_name']) ? $s['column_name'] : '';
                                $isRequired = !empty($s['is_required']);
                                $dataType = isset($s['data_type']) && is_string($s['data_type']) ? $s['data_type'] : '';
                                $boolFormat = isset($s['boolean_display_format']) && is_string($s['boolean_display_format']) ? $s['boolean_display_format'] : 'yes_no';
                                $liveVal = isset($s['current_live_value']) && is_string($s['current_live_value']) ? $s['current_live_value'] : '';
                                $propVal = isset($s['proposed_value']) && is_string($s['proposed_value']) ? $s['proposed_value'] : '';
                                $reasoning = isset($s['reasoning']) && is_string($s['reasoning']) ? $s['reasoning'] : '';

                                $liveDisplay = $liveVal;
                                $propDisplay = $propVal;
                              
                                if ($dataType === 'BOOLEAN') {
                                    $liveDisplay = format_boolean_value($liveVal, $boolFormat);
                                    $propDisplay = format_boolean_value($propVal, $boolFormat);
                                } elseif ($dataType === 'DATE') {
                                    $userDateFormat = isset($currentUser['date_format']) && is_string($currentUser['date_format']) ? $currentUser['date_format'] : 'd/m/Y';
                                    $liveDisplay = format_display_date($liveVal, $userDateFormat);
                                    $propDisplay = format_display_date($propVal, $userDateFormat);
                                }

                                $suggestorData = [
                                    'id' => isset($s['suggestor_id']) ? $s['suggestor_id'] : null,
                                    'username' => isset($s['suggestor_name']) && is_string($s['suggestor_name']) ? $s['suggestor_name'] : __('moderate.guest_user'),
                                    'first_name' => isset($s['suggestor_first']) && is_string($s['suggestor_first']) ? $s['suggestor_first'] : '',
                                    'surname' => isset($s['suggestor_surname']) && is_string($s['suggestor_surname']) ? $s['suggestor_surname'] : '',
                                    'attribution_display_mode' => isset($s['suggestor_mode']) && is_string($s['suggestor_mode']) ? $s['suggestor_mode'] : 'initials_random'
                                ];
                                $suggestorDisplayName = format_user_display_name($pdo, $suggestorData, $currentUser);
                            ?>
                            <tr>
                                <td class="ps-3 align-top">
                                    <span class="fw-bold">#<?= $sId ?></span><br>
                                    <small class="text-muted"><?= format_user_time($createdAt, $userTimezone, $fullFormatStr) ?></small><br>
                                    <small class="text-muted"><?= htmlspecialchars(__('moderate.by_label'), ENT_QUOTES, 'UTF-8') ?> <?= htmlspecialchars($suggestorDisplayName, ENT_QUOTES, 'UTF-8') ?></small>
                                </td>
                                <td class="align-top">
                                    <span class="badge bg-light text-dark border mb-1"><?= htmlspecialchars($tableName, ENT_QUOTES, 'UTF-8') ?></span><br>
                                    <strong><?= htmlspecialchars(__('moderate.record_id_label'), ENT_QUOTES, 'UTF-8') ?></strong> #<?= $recordId ?><br>
                                    <strong><?= htmlspecialchars(__('moderate.column_label'), ENT_QUOTES, 'UTF-8') ?></strong> <?= htmlspecialchars($columnName, ENT_QUOTES, 'UTF-8') ?>
                                    <?php if ($isRequired): ?>
                                        <br><span class="text-danger small fw-bold">(<?= htmlspecialchars(__('moderate.required_badge'), ENT_QUOTES, 'UTF-8') ?>)</span>
                                    <?php endif; ?>
                                </td>
                                <td class="align-top">
                                    <div class="row g-2 p-2 bg-light border rounded mb-2">
                                        <div class="col-md-6 border-end">
                                            <span class="d-block text-uppercase text-muted fw-bold" style="font-size: 0.75rem;"><?= htmlspecialchars(__('moderate.live_value_label'), ENT_QUOTES, 'UTF-8') ?></span>
                                            <div class="text-secondary text-break"><?= htmlspecialchars($liveDisplay !== '' ? $liveDisplay : __('moderate.empty_placeholder'), ENT_QUOTES, 'UTF-8') ?></div>
                                        </div>
                                        <div class="col-md-6">
                                            <span class="d-block text-uppercase text-success fw-bold" style="font-size: 0.75rem;"><?= htmlspecialchars(__('moderate.proposed_value_label'), ENT_QUOTES, 'UTF-8') ?></span>
                                            <div class="text-success fw-medium text-break"><?= htmlspecialchars($propDisplay, ENT_QUOTES, 'UTF-8') ?></div>
                                        </div>
                                    </div>
                                    <?php if ($reasoning !== ''): ?>
                                        <div class="alert alert-warning py-2 px-3 small mb-0">
                                            <strong><?= htmlspecialchars(__('moderate.evidence_label'), ENT_QUOTES, 'UTF-8') ?></strong><br>
                                            <div class="mt-1 text-break"><?= nl2br(htmlspecialchars($reasoning, ENT_QUOTES, 'UTF-8')) ?></div>
                                        </div>
                                    <?php else: ?>
                                        <small class="text-muted fst-italic"><?= htmlspecialchars(__('moderate.no_evidence'), ENT_QUOTES, 'UTF-8') ?></small>
                                    <?php endif; ?>
                                </td>
                                <td class="pe-3 align-top text-end">
                                    <form method="POST" action="/admin/moderate/store" class="moderation-form">
                                        <?= csrf_field() ?>
                                        <input type="hidden" name="suggestion_id" value="<?= $sId ?>">
                                      
                                        <label for="final_value_<?= $sId ?>" class="form-label small fw-bold text-start d-block"><?= htmlspecialchars(__('moderate.override_label'), ENT_QUOTES, 'UTF-8') ?></label>
                                      
                                        <?php if ($dataType === 'BOOLEAN'): ?>
                                            <?php
                                                $opt1Text = __('index.opt_yes_true'); 
                                                $opt2Text = __('index.opt_no_false');
                                                if ($boolFormat === 'male_female') { 
                                                    $opt1Text = __('index.opt_male'); 
                                                    $opt2Text = __('index.opt_female'); 
                                                } elseif ($boolFormat === 'true_false') { 
                                                    $opt1Text = __('index.opt_true'); 
                                                    $opt2Text = __('index.opt_false'); 
                                                } elseif ($boolFormat === 'tick_cross') { 
                                                    $opt1Text = __('index.opt_tick'); 
                                                    $opt2Text = __('index.opt_cross'); 
                                                }
                                            ?>
                                            <select id="final_value_<?= $sId ?>" name="final_value" class="form-select form-select-sm mb-2" <?= $isRequired ? 'required' : '' ?>>
                                                <option value=""><?= htmlspecialchars(__('moderate.select_placeholder'), ENT_QUOTES, 'UTF-8') ?></option>
                                                <option value="1" <?= ($propVal === '1') ? 'selected' : '' ?>><?= htmlspecialchars($opt1Text, ENT_QUOTES, 'UTF-8') ?></option>
                                                <option value="0" <?= ($propVal === '0') ? 'selected' : '' ?>><?= htmlspecialchars($opt2Text, ENT_QUOTES, 'UTF-8') ?></option>
                                            </select>
                                        <?php elseif ($dataType === 'DATE'): ?>
                                            <?php
                                                $userFmt = isset($currentUser['date_format']) && is_string($currentUser['date_format']) ? $currentUser['date_format'] : 'd/m/Y';
                                                $placeholder = 'YYYY-MM-DD';
                                                if ($userFmt === 'd/m/Y' || $userFmt === 'd/m/y') {
                                                    $placeholder = 'DD/MM/YYYY (e.g. 25/05/1500)';
                                                } elseif ($userFmt === 'd.m.Y') {
                                                    $placeholder = 'DD.MM.YYYY (e.g. 25.05.1500)';
                                                } elseif ($userFmt === 'm/d/Y') {
                                                    $placeholder = 'MM/DD/YYYY (e.g. 05/25/1500)';
                                                }
                                            ?>
                                            <input type="text" id="final_value_<?= $sId ?>" name="final_value" value="<?= htmlspecialchars($propVal, ENT_QUOTES, 'UTF-8') ?>" placeholder="<?= htmlspecialchars($placeholder, ENT_QUOTES, 'UTF-8') ?>" <?= $isRequired ? 'required' : '' ?> class="form-control form-control-sm mb-2" title="<?= htmlspecialchars(__('moderate.historical_dates_title'), ENT_QUOTES, 'UTF-8') ?>">
                                        <?php else: ?>
                                            <input type="text" id="final_value_<?= $sId ?>" name="final_value" value="<?= htmlspecialchars($propVal, ENT_QUOTES, 'UTF-8') ?>" <?= $isRequired ? 'required' : '' ?> class="form-control form-control-sm mb-2">
                                        <?php endif; ?>
                                      
                                        <div class="d-flex gap-1 justify-content-end">
                                            <button type="submit" name="action" value="approve" class="btn btn-sm btn-success approve-btn" onclick="return confirm('<?= htmlspecialchars(__('moderate.approve_confirm'), ENT_QUOTES, 'UTF-8') ?>');"><?= htmlspecialchars(__('moderate.approve_btn'), ENT_QUOTES, 'UTF-8') ?></button>
                                            <button type="submit" name="action" value="reject" class="btn btn-sm btn-danger" onclick="return confirm('<?= htmlspecialchars(__('moderate.decline_confirm'), ENT_QUOTES, 'UTF-8') ?>');"><?= htmlspecialchars(__('moderate.decline_btn'), ENT_QUOTES, 'UTF-8') ?></button>
                                        </div>
                                    </form>
                                </td>
                            </tr>
                        <?php endforeach; ?>
                    <?php endif; ?>
                </tbody>
            </table>
        </div>
    </div>
</div>

<script>
document.addEventListener('DOMContentLoaded', () => {
    document.querySelectorAll('.moderation-form').forEach(form => {
        form.addEventListener('keydown', (e) => {
            if ((e.ctrlKey || e.metaKey) && e.key === 'Enter') {
                e.preventDefault();
                const approveBtn = form.querySelector('.approve-btn');
                if (approveBtn && confirm('<?= htmlspecialchars(__('moderate.approve_confirm'), ENT_QUOTES, 'UTF-8') ?>')) {
                    const actionInput = document.createElement('input');
                    actionInput.type = 'hidden';
                    actionInput.name = 'action';
                    actionInput.value = 'approve';
                    form.appendChild(actionInput);
                    form.submit();
                }
            }
            if (e.key === 'Escape' && e.target.tagName === 'INPUT' && e.target.type === 'text') {
                e.preventDefault();
                e.target.value = '';
            }
        });
    });
});
</script>

<?php require_once __DIR__ . '/../partials/footer.php'; ?>
