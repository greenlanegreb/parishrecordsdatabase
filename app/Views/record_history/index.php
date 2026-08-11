<?php
/**
 * MIGRATED FILE MAPPING
 * ---------------------
 * Original Old File: roote/record_history.php
 * Migrated Date: 2026-08-05 06:49:21
 */
declare(strict_types=1);
/**
 * @var int $recordId
 * @var array{id: int|string, table_id: int|string, table_name: string} $record
 * @var bool $canPurgeAudit
 * @var string $userTimezone
 * @var string $userDateFormat
 * @var string $fullFormatStr
 * @var string $message
 * @var string $error
 * @var array<int, array<string, mixed>> $historyLogs
 * @var array<int, array<string, mixed>> $currentValues
 * @var string $returnUrl
 */
require_once ROOT_PATH . '/partials/header.php';
$basePath = defined('BASE_PATH') ? rtrim(BASE_PATH, '/') : '';
?>
<div class="container my-4" style="max-width: 900px;">
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

    <div class="d-flex justify-content-between align-items-center mb-3">
        <h3 class="h4 fw-bold text-dark mb-0"><?= htmlspecialchars(__('record_history.heading_prefix'), ENT_QUOTES, 'UTF-8') ?> #<?= (int) $recordId ?></h3>
        <a href="<?= htmlspecialchars($returnUrl, ENT_QUOTES, 'UTF-8') ?>" class="btn btn-outline-secondary btn-sm px-3 text-decoration-none">← <?= htmlspecialchars(__('record_history.return_btn'), ENT_QUOTES, 'UTF-8') ?></a>
    </div>

    <p class="text-muted small mb-4">
        <?= htmlspecialchars(__('record_history.directory_table_label'), ENT_QUOTES, 'UTF-8') ?>
        <strong class="text-dark"><?= htmlspecialchars((string) $record['table_name'], ENT_QUOTES, 'UTF-8') ?></strong><br>
        <?= htmlspecialchars(__('record_history.subheading_lifecycle'), ENT_QUOTES, 'UTF-8') ?>
    </p>

    <div class="card border-0 shadow-sm p-4 mb-4 bg-white">
        <h4 class="h6 fw-bold border-bottom pb-2 mb-3"><?= htmlspecialchars(__('record_history.snapshot_heading'), ENT_QUOTES, 'UTF-8') ?></h4>
        <div class="row g-3">
            <?php foreach ($currentValues as $cv): ?>
                <?php
                    $colName = isset($cv['column_name']) && is_string($cv['column_name']) ? $cv['column_name'] : '';
                    $valCont = isset($cv['value_content']) && is_string($cv['value_content']) ? $cv['value_content'] : '';
                    $dataType = isset($cv['data_type']) && is_string($cv['data_type']) ? $cv['data_type'] : '';
                    $boolFormat = isset($cv['boolean_display_format']) && is_string($cv['boolean_display_format']) ? $cv['boolean_display_format'] : 'yes_no';
                    if ($dataType === 'BOOLEAN') {
                        $displayVal = format_boolean_value($valCont, $boolFormat);
                    } elseif ($dataType === 'DATE') {
                        $displayVal = format_display_date($valCont, $userDateFormat);
                    } else {
                        $displayVal = $valCont !== '' ? $valCont : __('record_history.empty_value');
                    }
                ?>
                <div class="col-md-4 col-sm-6">
                    <span class="d-block text-uppercase text-muted fw-bold" style="font-size: 0.75rem;"><?= htmlspecialchars($colName, ENT_QUOTES, 'UTF-8') ?>:</span>
                    <div class="text-dark fw-medium text-break"><?= htmlspecialchars($displayVal, ENT_QUOTES, 'UTF-8') ?></div>
                </div>
            <?php endforeach; ?>
        </div>
    </div>

    <h4 class="h5 fw-bold text-dark mb-3"><?= htmlspecialchars(__('record_history.timeline_heading'), ENT_QUOTES, 'UTF-8') ?></h4>
    <?php if (empty($historyLogs)): ?>
        <p class="text-muted fst-italic"><?= htmlspecialchars(__('record_history.no_history'), ENT_QUOTES, 'UTF-8') ?></p>
    <?php else: ?>
        <div class="d-flex flex-column gap-3">
            <?php foreach ($historyLogs as $log): ?>
                <?php
                    $logId = isset($log['id']) ? (int) $log['id'] : 0;
                    $logAction = isset($log['action']) && is_string($log['action']) ? $log['action'] : '';
                    $createdAt = isset($log['created_at']) && is_string($log['created_at']) ? $log['created_at'] : '';
                    $details = isset($log['details']) && is_string($log['details']) ? $log['details'] : '';
                    $sugCol = isset($log['sug_column']) && is_string($log['sug_column']) ? $log['sug_column'] : '';
                    $sugVal = isset($log['sug_value']) && is_string($log['sug_value']) ? $log['sug_value'] : '';
                    $sugReasoning = isset($log['sug_reasoning']) && is_string($log['sug_reasoning']) ? $log['sug_reasoning'] : '';
                    $sugStatus = isset($log['sug_status']) && is_string($log['sug_status']) ? $log['sug_status'] : '';

                    $contributor = isset($log['contributor_display']) && is_string($log['contributor_display']) && $log['contributor_display'] !== ''
                        ? $log['contributor_display']
                        : 'Contributor';

                    $actionLabels = [
                        'INSERT'             => 'Record created',
                        'UPDATE'             => 'Record updated',
                        'PURGE_RECORD'       => 'Record removed',
                        'EDIT_SUGGESTION'    => 'Edit suggested',
                        'APPROVE_SUGGESTION' => 'Suggestion approved',
                        'REJECT_SUGGESTION'  => 'Suggestion not accepted',
                    ];
                    $displayAction = $actionLabels[$logAction]
                        ?? ucwords(strtolower(str_replace('_', ' ', $logAction)));

                    $publicDetails = $details;
                    $publicDetails = preg_replace('/\s*in table ID\s*\d+/i', '', $publicDetails) ?? $publicDetails;
                    $publicDetails = preg_replace(
                        '/Handled suggestion ID:\s*\d+/i',
                        'A suggested change was reviewed',
                        $publicDetails
                    ) ?? $publicDetails;
                    $publicDetails = preg_replace(
                        '/Created record entry\.?/i',
                        'This record was created.',
                        $publicDetails
                    ) ?? $publicDetails;
                    $publicDetails = preg_replace(
                        '/Suggested edit for column:\s*/i',
                        'Suggested change for field: ',
                        $publicDetails
                    ) ?? $publicDetails;
                    $publicDetails = preg_replace(
                        '/\(Proposed:\s*/i',
                        '(Suggested: ',
                        $publicDetails
                    ) ?? $publicDetails;

                    $valueLabel = ($logAction === 'APPROVE_SUGGESTION' || $sugStatus === 'approved')
                        ? 'Approved value'
                        : 'Suggested value';
                ?>
                <div class="card border-0 shadow-sm p-3 border-start border-primary border-4 bg-white">
                    <div class="d-flex flex-wrap justify-content-between align-items-center gap-2 mb-2">
                        <div class="d-flex align-items-center gap-2">
                            <span class="badge bg-light text-dark fw-bold border"><?= htmlspecialchars($displayAction, ENT_QUOTES, 'UTF-8') ?></span>
                            <small class="text-muted"><?= htmlspecialchars(format_user_time($createdAt, $userTimezone, $fullFormatStr), ENT_QUOTES, 'UTF-8') ?></small>
                        </div>
                        <?php if ($canPurgeAudit): ?>
                            <form action="<?= $basePath ?>/purge_audit_entry" method="POST" onsubmit="return confirm('<?= htmlspecialchars(__('record_history.purge_confirm'), ENT_QUOTES, 'UTF-8') ?>');" class="mb-0">
                                <?= function_exists('csrf_field') ? csrf_field() : '' ?>
                                <input type="hidden" name="audit_id" value="<?= $logId ?>">
                                <input type="hidden" name="record_id" value="<?= (int) $recordId ?>">
                                <button type="submit" class="btn btn-outline-danger btn-sm py-0 px-2" style="font-size: 0.7rem;"><?= htmlspecialchars(__('record_history.purge_btn'), ENT_QUOTES, 'UTF-8') ?></button>
                            </form>
                        <?php endif; ?>
                    </div>

                    <div class="small text-secondary mb-2">
                        <strong>Contributor:</strong> <?= htmlspecialchars($contributor, ENT_QUOTES, 'UTF-8') ?>
                    </div>

                    <div class="small text-dark bg-light p-2 rounded text-break mb-2" style="line-height: 1.4;">
                        <?= nl2br(htmlspecialchars($publicDetails, ENT_QUOTES, 'UTF-8')) ?>
                    </div>

                    <?php if ($sugCol !== '' || $sugVal !== ''): ?>
                        <div class="bg-primary bg-opacity-10 border border-primary border-opacity-25 p-2 rounded small">
                            <?php if ($sugCol !== ''): ?>
                                <div><strong>Field:</strong> <?= htmlspecialchars($sugCol, ENT_QUOTES, 'UTF-8') ?></div>
                            <?php endif; ?>
                            <?php if ($sugVal !== ''): ?>
                                <?php
                                    $displaySugVal = $sugVal;
                                    $parsedTime = strtotime($sugVal);
                                    if ($parsedTime !== false && (preg_match('/^\d{4}[-\/\.]\d{2}[-\/\.]\d{2}$/', $sugVal) || preg_match('/^\d{2}[-\/\.]\d{2}[-\/\.]\d{4}$/', $sugVal))) {
                                        $isoDate = date('Y-m-d', $parsedTime);
                                        $displaySugVal = format_display_date($isoDate, $userDateFormat);
                                    }
                                ?>
                                <div>
                                    <strong><?= htmlspecialchars($valueLabel, ENT_QUOTES, 'UTF-8') ?>:</strong>
                                    <span class="text-primary fw-bold"><?= htmlspecialchars($displaySugVal, ENT_QUOTES, 'UTF-8') ?></span>
                                </div>
                            <?php endif; ?>
                            <?php if ($sugReasoning !== ''): ?>
                                <div><strong><?= htmlspecialchars(__('record_history.reasoning_evidence'), ENT_QUOTES, 'UTF-8') ?></strong> <?= htmlspecialchars($sugReasoning, ENT_QUOTES, 'UTF-8') ?></div>
                            <?php endif; ?>
                        </div>
                    <?php endif; ?>
                </div>
            <?php endforeach; ?>
        </div>
    <?php endif; ?>
</div>
<?php require_once ROOT_PATH . '/partials/footer.php'; ?>
