<?php
declare(strict_types=1);

/**
 * Admin GitHub feedback UI.
 * @var array<int, array<string, mixed>> $recentLogs
 * @var string $basePath
 */
$recentLogs = $recentLogs ?? [];
$basePath = isset($basePath) && is_string($basePath) ? rtrim($basePath, '/') : (defined('BASE_PATH') ? rtrim((string) BASE_PATH, '/') : '');

$nt = static function (string $key, string $fallback): string {
    $t = function_exists('__') ? (string) __($key) : $key;
    return ($t !== $key && $t !== '') ? $t : $fallback;
};

require_once ROOT_PATH . '/partials/header.php';
?>
<div class="container-fluid px-4 py-4" role="region" aria-labelledby="feedbackHeading">
    <h1 class="h2 mb-4" id="feedbackHeading"><?= htmlspecialchars($nt('gh.heading', 'pRD Centralised Feedback System'), ENT_QUOTES, 'UTF-8') ?></h1>

    <div class="text-muted mb-4" id="feedbackIntro">
        <p><?= htmlspecialchars($nt('gh.intro_p1', 'Thank you for trusting pRD for your Database Project. This resource is available to you, exclusively, as an Administrator.'), ENT_QUOTES, 'UTF-8') ?></p>
        <ol class="mb-0 ps-3">
            <li><?= htmlspecialchars($nt('gh.intro_step1', 'This system connects directly to GitHub and checks for existing issues.'), ENT_QUOTES, 'UTF-8') ?></li>
            <li><?= htmlspecialchars($nt('gh.intro_step2', 'You can add a comment on an existing issue if you feel the issue is not already totally covered.'), ENT_QUOTES, 'UTF-8') ?></li>
            <li><?= htmlspecialchars($nt('gh.intro_step3', 'Or, if you have spotted a new issue, the system provides a convenient facility to open a new issue on GitHub which will be picked up by the pRD Team.'), ENT_QUOTES, 'UTF-8') ?></li>
        </ol>
    </div>

    <!-- Consent Gate -->
    <div class="card shadow-sm mb-4 border-primary" id="consentCard" aria-labelledby="consentHeading">
        <div class="card-body bg-light">
            <h2 class="h5 card-title text-primary" id="consentHeading"><?= htmlspecialchars($nt('gh.consent_title', 'System Connection Notice & Permission'), ENT_QUOTES, 'UTF-8') ?></h2>
            <p class="small text-dark mb-3" id="consentDescription">
                <?= htmlspecialchars($nt('gh.consent_text', 'This system connects to pRD\'s server so your report can be delivered to GitHub. No personal information is shared with pRD\'s server beyond what is needed for that delivery. Your report will be publicly visible on the internet. We need your permission to proceed.'), ENT_QUOTES, 'UTF-8') ?>
            </p>
            <div class="form-check">
                <input class="form-check-input" type="checkbox" id="consentCheckbox" aria-describedby="consentDescription">
                <label class="form-check-label fw-bold text-dark" for="consentCheckbox">
                    <?= htmlspecialchars($nt('gh.consent_agree', 'I agree to proceed with the Centralised Feedback System under these terms.'), ENT_QUOTES, 'UTF-8') ?>
                </label>
            </div>
        </div>
    </div>

    <!-- Workspace (hidden until consent) -->
    <div class="row" id="feedbackWorkspace" hidden>
        <div class="col-md-7 mb-4">
            <div class="card shadow-sm">
                <div class="card-body">
                    <div id="modeIndicator" class="alert alert-info py-2 mb-3" hidden role="status" aria-live="polite">
                        <span id="modeText"><?= htmlspecialchars($nt('gh.creating_new', 'Creating New Issue'), ENT_QUOTES, 'UTF-8') ?></span>
                        <button type="button" class="btn btn-sm btn-outline-secondary float-end py-0" id="cancelCommentMode">
                            <?= htmlspecialchars($nt('gh.switch_new', 'Switch to New Issue'), ENT_QUOTES, 'UTF-8') ?>
                        </button>
                    </div>

                    <form id="feedbackForm" novalidate>
                        <?= csrf_field() ?>
                        <input type="hidden" id="selectedIssueNumber" name="issue_number" value="">
                        <input type="hidden" name="preview_only" id="previewOnlyField" value="0">
                        <input type="hidden" name="confirm_public" id="confirmPublicField" value="0">
                        <input type="hidden" name="confirm_sensitive" id="confirmSensitiveField" value="0">
                        <input type="hidden" name="final_title" id="finalTitleField" value="">
                        <input type="hidden" name="final_body" id="finalBodyField" value="">

                        <div class="mb-3" id="typeFieldContainer">
                            <label for="feedbackType" class="form-label fw-bold"><?= htmlspecialchars($nt('gh.select_type', 'Select Issue Type'), ENT_QUOTES, 'UTF-8') ?></label>
                            <select class="form-select" id="feedbackType" name="type" aria-label="<?= htmlspecialchars($nt('gh.select_type', 'Select Issue Type'), ENT_QUOTES, 'UTF-8') ?>">
                                <option value="bug"><?= htmlspecialchars($nt('gh.type_bug', '1. Bug Report'), ENT_QUOTES, 'UTF-8') ?></option>
                                <option value="enhancement"><?= htmlspecialchars($nt('gh.type_enhancement', '2. Feature Extension or Request'), ENT_QUOTES, 'UTF-8') ?></option>
                                <option value="documentation"><?= htmlspecialchars($nt('gh.type_documentation', '3. Documentation Improvement Request'), ENT_QUOTES, 'UTF-8') ?></option>
                                <option value="translation"><?= htmlspecialchars($nt('gh.type_translation', '4. Suggest a Language Translation'), ENT_QUOTES, 'UTF-8') ?></option>
                            </select>
                        </div>

                        <div id="topDuplicateGate" class="mb-3 alert alert-warning border-warning" hidden role="region" aria-label="<?= htmlspecialchars($nt('gh.duplicate_warning', 'Duplicate warning gate'), ENT_QUOTES, 'UTF-8') ?>">
                            <div class="form-check">
                                <input class="form-check-input duplicate-sync-checkbox" type="checkbox" id="confirmNotDuplicateTop">
                                <label class="form-check-label small text-dark" for="confirmNotDuplicateTop">
                                    <?= htmlspecialchars($nt('gh.duplicate_confirm_text', '(a)Similar open issue(s) was/were found. I confirm this is a distinct issue that has not yet been reported.'), ENT_QUOTES, 'UTF-8') ?>
                                </label>
                            </div>
                        </div>

                        <div class="mb-3" id="titleFieldContainer">
                            <label for="feedbackTitle" class="form-label" id="titleLabel"><?= htmlspecialchars($nt('gh.title_summary', 'Title / Summary'), ENT_QUOTES, 'UTF-8') ?></label>
                            <input type="text" class="form-control" id="feedbackTitle" name="title" placeholder="<?= htmlspecialchars($nt('gh.title_placeholder', 'Brief summary of the issue'), ENT_QUOTES, 'UTF-8') ?>" autocomplete="off">
                        </div>

                        <div id="bugFields" class="type-fields-group">
                            <div class="mb-3">
                                <label for="bugDescription" class="form-label"><?= htmlspecialchars($nt('gh.detailed_description', 'Detailed Description'), ENT_QUOTES, 'UTF-8') ?></label>
                                <textarea class="form-control" id="bugDescription" name="description" rows="4" placeholder="<?= htmlspecialchars($nt('gh.bug_desc_placeholder', 'A clear and concise description of what the bug is.'), ENT_QUOTES, 'UTF-8') ?>"></textarea>
                            </div>
                            <div class="mb-3">
                                <label for="bugSteps" class="form-label"><?= htmlspecialchars($nt('gh.steps_to_reproduce', 'Steps to Reproduce'), ENT_QUOTES, 'UTF-8') ?></label>
                                <textarea class="form-control" id="bugSteps" name="steps" rows="3" placeholder="1. Go to '...'&#10;2. Click on '....'&#10;3. See error"></textarea>
                            </div>
                            <div class="mb-3">
                                <label for="bugExpected" class="form-label"><?= htmlspecialchars($nt('gh.expected_behavior', 'Expected Behavior'), ENT_QUOTES, 'UTF-8') ?></label>
                                <textarea class="form-control" id="bugExpected" name="expected" rows="2" placeholder="<?= htmlspecialchars($nt('gh.expected_placeholder', 'A clear and concise description of what you expected to happen.'), ENT_QUOTES, 'UTF-8') ?>"></textarea>
                            </div>
                            <div class="mb-3">
                                <fieldset class="p-3 border rounded bg-light">
                                    <legend class="col-form-label pt-0 fw-bold small"><?= htmlspecialchars($nt('gh.severity_level', 'Severity Level'), ENT_QUOTES, 'UTF-8') ?></legend>
                                    <div class="form-check mb-2">
                                        <input class="form-check-input" type="radio" name="severity" id="sevLow" value="low">
                                        <label class="form-check-label small" for="sevLow">
                                            <strong><?= htmlspecialchars($nt('gh.sev_low', 'Low (priority: low)'), ENT_QUOTES, 'UTF-8') ?></strong> — <?= htmlspecialchars($nt('gh.sev_low_desc', 'ONLY IF - Not a security issue and/or Does NOT relate to functionality being completely broken.'), ENT_QUOTES, 'UTF-8') ?>
                                        </label>
                                    </div>
                                    <div class="form-check mb-2">
                                        <input class="form-check-input" type="radio" name="severity" id="sevMed" value="medium" checked>
                                        <label class="form-check-label small" for="sevMed">
                                            <strong><?= htmlspecialchars($nt('gh.sev_med', 'Medium (priority: medium)'), ENT_QUOTES, 'UTF-8') ?></strong> — <?= htmlspecialchars($nt('gh.sev_med_desc', 'ONLY IF - Not a security issue and/or IS functionality being largely broken.'), ENT_QUOTES, 'UTF-8') ?>
                                        </label>
                                    </div>
                                    <div class="form-check">
                                        <input class="form-check-input" type="radio" name="severity" id="sevHigh" value="high">
                                        <label class="form-check-label small" for="sevHigh">
                                            <strong><?= htmlspecialchars($nt('gh.sev_high', 'High (priority: high)'), ENT_QUOTES, 'UTF-8') ?></strong> — <?= htmlspecialchars($nt('gh.sev_high_desc', 'Security Issue and/or Feature Completely Broken.'), ENT_QUOTES, 'UTF-8') ?>
                                        </label>
                                    </div>
                                </fieldset>
                            </div>
                        </div>

                        <div id="featureFields" class="type-fields-group" hidden>
                            <div class="mb-3">
                                <label for="featureProblem" class="form-label"><?= htmlspecialchars($nt('gh.feature_problem', 'Is your feature extension or request related to a problem? Please describe.'), ENT_QUOTES, 'UTF-8') ?></label>
                                <textarea class="form-control" id="featureProblem" name="problem" rows="4"></textarea>
                            </div>
                            <div class="mb-3">
                                <label for="featureSolution" class="form-label"><?= htmlspecialchars($nt('gh.feature_solution', "Please describe the solution you'd like"), ENT_QUOTES, 'UTF-8') ?></label>
                                <textarea class="form-control" id="featureSolution" name="solution" rows="4" placeholder="<?= htmlspecialchars($nt('gh.solution_placeholder', 'A clear and concise description of what you want to happen.'), ENT_QUOTES, 'UTF-8') ?>"></textarea>
                            </div>
                        </div>

                        <div id="documentationFields" class="type-fields-group" hidden>
                            <div class="mb-3">
                                <label for="docName" class="form-label"><?= htmlspecialchars($nt('gh.doc_related', 'What document within the Wiki does your suggestion relate to?'), ENT_QUOTES, 'UTF-8') ?></label>
                                <input type="text" class="form-control" id="docName" name="doc_name" placeholder="<?= htmlspecialchars($nt('gh.doc_placeholder', 'Wiki document name or URL'), ENT_QUOTES, 'UTF-8') ?>">
                            </div>
                            <div class="mb-3">
                                <label for="docParagraphs" class="form-label"><?= htmlspecialchars($nt('gh.doc_paragraphs', 'Please provide the paragraph number(s) or quote the paragraph(s) below:'), ENT_QUOTES, 'UTF-8') ?></label>
                                <textarea class="form-control" id="docParagraphs" name="paragraphs" rows="3"></textarea>
                            </div>
                            <div class="mb-3">
                                <label for="docProposed" class="form-label"><?= htmlspecialchars($nt('gh.doc_proposed', 'What do you think the affected paragraph(s) should say instead?'), ENT_QUOTES, 'UTF-8') ?></label>
                                <textarea class="form-control" id="docProposed" name="proposed" rows="4"></textarea>
                            </div>
                            <div class="mb-3">
                                <label for="docReasoning" class="form-label"><?= htmlspecialchars($nt('gh.doc_reasoning', 'Please provide your Reasoning:'), ENT_QUOTES, 'UTF-8') ?></label>
                                <textarea class="form-control" id="docReasoning" name="reasoning" rows="3"></textarea>
                            </div>
                        </div>

                        <div id="translationFields" class="type-fields-group" hidden>
                            <div class="mb-3">
                                <label for="offendingText" class="form-label"><?= htmlspecialchars($nt('gh.trans_offending', 'Please copy and paste the Offending Text:'), ENT_QUOTES, 'UTF-8') ?></label>
                                <textarea class="form-control" id="offendingText" name="offending_text" rows="3"></textarea>
                            </div>
                            <div class="mb-3">
                                <label for="userAction" class="form-label"><?= htmlspecialchars($nt('gh.trans_action', 'What were you doing on pRD at the time please?'), ENT_QUOTES, 'UTF-8') ?></label>
                                <textarea class="form-control" id="userAction" name="user_action" rows="3"></textarea>
                            </div>
                        </div>

                        <div class="mb-3" id="additionalFieldContainer">
                            <label for="feedbackAdditional" class="form-label"><?= htmlspecialchars($nt('gh.additional_context', 'Additional Context'), ENT_QUOTES, 'UTF-8') ?></label>
                            <textarea class="form-control" id="feedbackAdditional" name="additional" rows="3" placeholder="<?= htmlspecialchars($nt('gh.additional_placeholder', 'Add any other context here please.'), ENT_QUOTES, 'UTF-8') ?>"></textarea>
                        </div>

                        <div id="commentOnlyFields" hidden>
                            <div class="mb-3">
                                <label for="issueComment" class="form-label fw-bold"><?= htmlspecialchars($nt('gh.comment_label', 'Your comment'), ENT_QUOTES, 'UTF-8') ?></label>
                                <p class="small text-muted" id="commentHelp"><?= htmlspecialchars($nt('gh.comment_help', 'This is added to the existing GitHub issue. It does not change that issue\'s labels or priority.'), ENT_QUOTES, 'UTF-8') ?></p>
                                <textarea class="form-control" id="issueComment" name="comment" rows="6" aria-describedby="commentHelp"></textarea>
                            </div>
                        </div>

                        <!-- Audit logs: opt-in, all unchecked by default -->
                        <div class="mb-3 border rounded p-3 bg-light" id="auditLogsContainer">
                            <label class="form-label fw-bold small mb-1" id="auditLogsLegend"><?= htmlspecialchars($nt('gh.select_audit_logs', 'Select Relevant Audit Logs (Past 48 Hours)'), ENT_QUOTES, 'UTF-8') ?></label>
                            <p class="text-muted small mb-2" id="auditLogsHelp"><?= htmlspecialchars($nt('gh.select_audit_logs_desc', 'Optional. Tick any recent log events that help the team — only if you are happy for a sanitized version to appear on a public GitHub issue.'), ENT_QUOTES, 'UTF-8') ?></p>
                            <?php if ($recentLogs === []): ?>
                                <p class="text-muted small fst-italic mb-0"><?= htmlspecialchars($nt('gh.no_recent_logs', 'No audit logs found within the last 48 hours.'), ENT_QUOTES, 'UTF-8') ?></p>
                            <?php else: ?>
                                <div style="max-height: 180px; overflow-y: auto;" class="border bg-white p-2 rounded" role="group" aria-labelledby="auditLogsLegend" aria-describedby="auditLogsHelp">
                                    <?php foreach ($recentLogs as $log):
                                        $logId = isset($log['id']) ? (int) $log['id'] : 0;
                                        $loggedAt = isset($log['logged_at']) && is_string($log['logged_at']) ? $log['logged_at'] : '';
                                        $action = isset($log['action']) && is_string($log['action']) ? $log['action'] : '';
                                        $details = isset($log['details']) && is_string($log['details']) ? $log['details'] : '';
                                        ?>
                                        <div class="form-check mb-1">
                                            <input class="form-check-input" type="checkbox" name="selected_logs[]" value="<?= $logId ?>" id="log_<?= $logId ?>">
                                            <label class="form-check-label font-monospace text-wrap" style="font-size: 0.78rem;" for="log_<?= $logId ?>">
                                                <strong>[<?= htmlspecialchars($loggedAt, ENT_QUOTES, 'UTF-8') ?>]</strong>
                                                <span class="badge bg-secondary"><?= htmlspecialchars($action, ENT_QUOTES, 'UTF-8') ?></span>
                                                <?= htmlspecialchars(mb_substr($details, 0, 90), ENT_QUOTES, 'UTF-8') ?><?= mb_strlen($details) > 90 ? '…' : '' ?>
                                            </label>
                                        </div>
                                    <?php endforeach; ?>
                                </div>
                            <?php endif; ?>
                        </div>

                        <div id="bottomDuplicateGate" class="mb-3 alert alert-warning border-warning" hidden role="region" aria-label="<?= htmlspecialchars($nt('gh.duplicate_warning', 'Duplicate warning gate'), ENT_QUOTES, 'UTF-8') ?>">
                            <div class="form-check">
                                <input class="form-check-input duplicate-sync-checkbox" type="checkbox" id="confirmNotDuplicateBottom">
                                <label class="form-check-label small text-dark" for="confirmNotDuplicateBottom">
                                    <?= htmlspecialchars($nt('gh.duplicate_confirm_text', '(a)Similar open issue(s) was/were found. I confirm this is a distinct issue that has not yet been reported.'), ENT_QUOTES, 'UTF-8') ?>
                                </label>
                            </div>
                        </div>

                        <button type="submit" class="btn btn-primary" id="submitBtn">
                            <?= htmlspecialchars($nt('gh.submit_btn', 'Submit Feedback'), ENT_QUOTES, 'UTF-8') ?>
                        </button>
                    </form>

                    <!-- Public preview / final edit -->
                    <div id="previewPanel" class="mt-4 border rounded p-3 bg-light" hidden role="region" aria-labelledby="previewHeading">
                        <h2 class="h6 fw-bold" id="previewHeading"><?= htmlspecialchars($nt('gh.preview_heading', 'Preview before sending'), ENT_QUOTES, 'UTF-8') ?></h2>
                        <p class="small text-dark mb-2" id="previewReminder">
                            <strong><?= htmlspecialchars($nt('gh.preview_reminder', 'Please read this once more before sending. The text below will be public on GitHub. You can remove personal details; the team allocation tick boxes are reset when it is sent.'), ENT_QUOTES, 'UTF-8') ?></strong>
                        </p>
                        <div class="mb-2">
                            <label for="previewTitle" class="form-label small fw-bold"><?= htmlspecialchars($nt('gh.preview_title_label', 'Title'), ENT_QUOTES, 'UTF-8') ?></label>
                            <input type="text" class="form-control form-control-sm" id="previewTitle" aria-describedby="previewReminder">
                        </div>
                        <div class="mb-2">
                            <label for="previewBody" class="form-label small fw-bold"><?= htmlspecialchars($nt('gh.preview_body_label', 'Body (you can edit or remove personal details)'), ENT_QUOTES, 'UTF-8') ?></label>
                            <textarea class="form-control font-monospace small" id="previewBody" rows="12" aria-describedby="previewReminder"></textarea>
                        </div>
                        <div id="sensitiveWarn" class="alert alert-danger py-2 small" hidden role="alert">
                            <?= htmlspecialchars($nt('gh.sensitive_warn', 'This text may contain secrets or credentials. Remove them, or confirm below if you still want to publish.'), ENT_QUOTES, 'UTF-8') ?>
                            <div class="form-check mt-2 mb-0">
                                <input class="form-check-input" type="checkbox" id="confirmSensitiveCheck">
                                <label class="form-check-label" for="confirmSensitiveCheck"><?= htmlspecialchars($nt('gh.sensitive_confirm', 'I understand and still want to publish'), ENT_QUOTES, 'UTF-8') ?></label>
                            </div>
                        </div>
                        <div class="form-check mb-3">
                            <input class="form-check-input" type="checkbox" id="confirmPublicCheck">
                            <label class="form-check-label" for="confirmPublicCheck"><?= htmlspecialchars($nt('gh.public_confirm', 'I have read the preview and understand it will be public'), ENT_QUOTES, 'UTF-8') ?></label>
                        </div>
                        <div class="d-flex flex-wrap gap-2">
                            <button type="button" class="btn btn-sm btn-outline-secondary" id="previewBackBtn"><?= htmlspecialchars($nt('gh.preview_back', 'Back to form'), ENT_QUOTES, 'UTF-8') ?></button>
                            <button type="button" class="btn btn-sm btn-primary" id="previewSendBtn"><?= htmlspecialchars($nt('gh.preview_send', 'Send to GitHub'), ENT_QUOTES, 'UTF-8') ?></button>
                        </div>
                    </div>

                    <div id="formResponse" class="mt-3" role="alert" aria-live="assertive"></div>
                </div>
            </div>
        </div>

        <div class="col-md-5">
            <div class="card shadow-sm bg-light">
                <div class="card-body">
                    <h2 class="h5 card-title"><?= htmlspecialchars($nt('gh.similar_issues_title', 'Similar Existing Issues'), ENT_QUOTES, 'UTF-8') ?></h2>
                    <p class="text-muted small">
                        <?= htmlspecialchars($nt('gh.similar_issues_desc', 'As you type a title, we check for open issues. Click any issue below if you want to add your comments directly to it instead of creating a duplicate.'), ENT_QUOTES, 'UTF-8') ?>
                    </p>
                    <div id="existingIssuesList" class="list-group list-group-flush small" aria-live="polite" aria-atomic="true" role="region" aria-label="<?= htmlspecialchars($nt('gh.similar_issues_title', 'Similar Existing Issues'), ENT_QUOTES, 'UTF-8') ?>">
                        <span class="text-muted"><?= htmlspecialchars($nt('gh.type_to_search', 'Start typing a title to search...'), ENT_QUOTES, 'UTF-8') ?></span>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<script>
document.addEventListener('DOMContentLoaded', function () {
    const basePath = <?= json_encode($basePath, JSON_UNESCAPED_UNICODE) ?>;
    const consentCheckbox = document.getElementById('consentCheckbox');
    const consentCard = document.getElementById('consentCard');
    const feedbackWorkspace = document.getElementById('feedbackWorkspace');
    const titleInput = document.getElementById('feedbackTitle');
    const typeSelect = document.getElementById('feedbackType');
    const issuesList = document.getElementById('existingIssuesList');
    const form = document.getElementById('feedbackForm');
    const responseDiv = document.getElementById('formResponse');
    const modeIndicator = document.getElementById('modeIndicator');
    const modeText = document.getElementById('modeText');
    const cancelCommentModeBtn = document.getElementById('cancelCommentMode');
    const selectedIssueNumberInput = document.getElementById('selectedIssueNumber');
    const typeFieldContainer = document.getElementById('typeFieldContainer');
    const titleFieldContainer = document.getElementById('titleFieldContainer');
    const submitBtn = document.getElementById('submitBtn');
    const topDuplicateGate = document.getElementById('topDuplicateGate');
    const bottomDuplicateGate = document.getElementById('bottomDuplicateGate');
    const confirmTop = document.getElementById('confirmNotDuplicateTop');
    const confirmBottom = document.getElementById('confirmNotDuplicateBottom');
    const bugFields = document.getElementById('bugFields');
    const featureFields = document.getElementById('featureFields');
    const documentationFields = document.getElementById('documentationFields');
    const translationFields = document.getElementById('translationFields');
    const previewPanel = document.getElementById('previewPanel');
    const previewTitle = document.getElementById('previewTitle');
    const previewBody = document.getElementById('previewBody');
    const previewOnlyField = document.getElementById('previewOnlyField');
    const confirmPublicField = document.getElementById('confirmPublicField');
    const confirmSensitiveField = document.getElementById('confirmSensitiveField');
    const finalTitleField = document.getElementById('finalTitleField');
    const finalBodyField = document.getElementById('finalBodyField');
    const confirmPublicCheck = document.getElementById('confirmPublicCheck');
    const confirmSensitiveCheck = document.getElementById('confirmSensitiveCheck');
    const sensitiveWarn = document.getElementById('sensitiveWarn');
    const previewBackBtn = document.getElementById('previewBackBtn');
    const previewSendBtn = document.getElementById('previewSendBtn');

    let searchTimeout;
    let hasMatchingIssues = false;

    const i18n = {
        typeAtLeast3: <?= json_encode($nt('gh.type_at_least_3', 'Type at least 3 characters to search...'), JSON_UNESCAPED_UNICODE) ?>,
        noSimilar: <?= json_encode($nt('gh.no_similar_found', 'No similar open issues found.'), JSON_UNESCAPED_UNICODE) ?>,
        clickComment: <?= json_encode($nt('gh.click_to_comment', 'Add a comment on this issue instead →'), JSON_UNESCAPED_UNICODE) ?>,
        openOnGithub: <?= json_encode($nt('gh.open_on_github', 'Open issue in a new tab'), JSON_UNESCAPED_UNICODE) ?>,
        failedCheck: <?= json_encode($nt('gh.failed_check_issues', 'Failed to check existing issues.'), JSON_UNESCAPED_UNICODE) ?>,
        commentingOn: <?= json_encode($nt('gh.commenting_on', 'Commenting on existing issue'), JSON_UNESCAPED_UNICODE) ?>,
        postComment: <?= json_encode($nt('gh.post_comment_btn', 'Post Comment to Issue'), JSON_UNESCAPED_UNICODE) ?>,
        lockedOnto: <?= json_encode($nt('gh.locked_onto_issue', 'Locked onto Issue'), JSON_UNESCAPED_UNICODE) ?>,
        addCommentBelow: <?= json_encode($nt('gh.add_comment_below', 'Add your comment below and submit.'), JSON_UNESCAPED_UNICODE) ?>,
        submitBtn: <?= json_encode($nt('gh.submit_btn', 'Submit Feedback'), JSON_UNESCAPED_UNICODE) ?>,
        typeToSearch: <?= json_encode($nt('gh.type_to_search', 'Start typing a title to search...'), JSON_UNESCAPED_UNICODE) ?>,
        posting: <?= json_encode($nt('gh.posting_comment', 'Posting Comment...'), JSON_UNESCAPED_UNICODE) ?>,
        submitting: <?= json_encode($nt('gh.submitting', 'Submitting...'), JSON_UNESCAPED_UNICODE) ?>,
        networkError: <?= json_encode($nt('gh.network_error', 'Network error while processing request.'), JSON_UNESCAPED_UNICODE) ?>,
        errorOccurred: <?= json_encode($nt('gh.error_occurred', 'An error occurred.'), JSON_UNESCAPED_UNICODE) ?>,
        issueCreated: <?= json_encode($nt('gh.issue_created_label', 'Issue'), JSON_UNESCAPED_UNICODE) ?>,
        createdSuccess: <?= json_encode($nt('gh.created_success', 'created successfully.'), JSON_UNESCAPED_UNICODE) ?>,
        saveLink: <?= json_encode($nt('gh.save_tracking_link', 'Save this tracking link:'), JSON_UNESCAPED_UNICODE) ?>,
        makeNote: <?= json_encode($nt('gh.make_note_link', 'Make a note of this link to check back later.'), JSON_UNESCAPED_UNICODE) ?>,
        previewSend: <?= json_encode($nt('gh.preview_send', 'Send to GitHub'), JSON_UNESCAPED_UNICODE) ?>,
        sending: <?= json_encode($nt('gh.sending', 'Sending…'), JSON_UNESCAPED_UNICODE) ?>
    };

    function showEl(el) { if (el) { el.hidden = false; } }
    function hideEl(el) { if (el) { el.hidden = true; } }

    consentCheckbox.addEventListener('change', function () {
        if (this.checked) {
            hideEl(consentCard);
            showEl(feedbackWorkspace);
            typeSelect.focus();
        } else {
            hideEl(feedbackWorkspace);
            showEl(consentCard);
        }
    });

    typeSelect.addEventListener('change', function () {
        const val = this.value;
        const map = [
            [bugFields, val === 'bug'],
            [featureFields, val === 'enhancement'],
            [documentationFields, val === 'documentation'],
            [translationFields, val === 'translation']
        ];
        map.forEach(([el, on]) => { if (on) showEl(el); else hideEl(el); });
    });

    function syncDuplicateCheckboxes(checked) {
        confirmTop.checked = checked;
        confirmBottom.checked = checked;
        updateSubmitButtonState();
    }
    document.querySelectorAll('.duplicate-sync-checkbox').forEach(function (cb) {
        cb.addEventListener('change', function () { syncDuplicateCheckboxes(this.checked); });
    });

    function updateSubmitButtonState() {
        if (selectedIssueNumberInput.value) {
            submitBtn.disabled = false;
            return;
        }
        submitBtn.disabled = hasMatchingIssues ? !confirmTop.checked : false;
    }

    function escapeHtml(text) {
        return String(text).replace(/[&<>"']/g, function (m) {
            return ({ '&': '&amp;', '<': '&lt;', '>': '&gt;', '"': '&quot;', "'": '&#039;' })[m];
        });
    }

    titleInput.addEventListener('input', function () {
        if (selectedIssueNumberInput.value) return;
        clearTimeout(searchTimeout);
        const query = this.value.trim();
        if (query.length < 3) {
            issuesList.innerHTML = '<span class="text-muted">' + escapeHtml(i18n.typeAtLeast3) + '</span>';
            hasMatchingIssues = false;
            hideEl(topDuplicateGate);
            hideEl(bottomDuplicateGate);
            syncDuplicateCheckboxes(false);
            return;
        }
        searchTimeout = setTimeout(function () {
            fetch(basePath + '/admin/gh-feedback/search?q=' + encodeURIComponent(query), {
                credentials: 'same-origin',
                headers: { 'Accept': 'application/json' }
            })
                .then(function (res) { return res.json(); })
                .then(function (data) {
                    if (!Array.isArray(data) || !data.length) {
                        issuesList.innerHTML = '<span class="text-muted">' + escapeHtml(i18n.noSimilar) + '</span>';
                        hasMatchingIssues = false;
                        hideEl(topDuplicateGate);
                        hideEl(bottomDuplicateGate);
                        syncDuplicateCheckboxes(false);
                        return;
                    }
                    hasMatchingIssues = true;
                    showEl(topDuplicateGate);
                    showEl(bottomDuplicateGate);
                    updateSubmitButtonState();
                    let html = '';
                    data.forEach(function (issue) {
                        const num = issue.number;
                        const title = escapeHtml(issue.title || '');
                        let url = (issue.html_url && String(issue.html_url).indexOf('https://') === 0)
                            ? String(issue.html_url) : '';
                        html += '<div class="list-group-item">'
                            + '<div><strong>#' + num + '</strong>: ' + title + '</div>'
                            + '<div class="d-flex flex-wrap gap-2 mt-1">'
                            + '<button type="button" class="btn btn-sm btn-outline-primary select-issue-link" data-issue-number="' + num + '" data-issue-title="' + title + '">'
                            + escapeHtml(i18n.clickComment) + '</button>';
                        if (url) {
                            html += '<a class="btn btn-sm btn-outline-secondary" href="' + escapeHtml(url) + '" target="_blank" rel="noopener noreferrer">'
                                + escapeHtml(i18n.openOnGithub) + '</a>';
                        }
                        html += '</div></div>';
                    });
                    issuesList.innerHTML = html;
                    document.querySelectorAll('.select-issue-link').forEach(function (item) {
                        item.addEventListener('click', function (e) {
                            e.preventDefault();
                            enterCommentMode(this.dataset.issueNumber, this.dataset.issueTitle);
                        });
                    });
                })
                .catch(function () {
                    issuesList.innerHTML = '<span class="text-danger">' + escapeHtml(i18n.failedCheck) + '</span>';
                });
        }, 400);
    });

    const additionalFieldContainer = document.getElementById('additionalFieldContainer');
    const auditLogsContainer = document.getElementById('auditLogsContainer');
    const commentOnlyFields = document.getElementById('commentOnlyFields');
    const issueComment = document.getElementById('issueComment');

    function setCreateIssueVisibility(showCreate) {
        const createBlocks = [
            typeFieldContainer, titleFieldContainer,
            bugFields, featureFields, documentationFields, translationFields,
            additionalFieldContainer, auditLogsContainer
        ];
        createBlocks.forEach(function (el) {
            if (!el) return;
            if (showCreate) showEl(el); else hideEl(el);
        });
        if (showCreate) {
            hideEl(commentOnlyFields);
            typeSelect.dispatchEvent(new Event('change'));
        } else {
            hideEl(topDuplicateGate);
            hideEl(bottomDuplicateGate);
            showEl(commentOnlyFields);
        }
    }

    function enterCommentMode(issueNumber, issueTitle) {
        selectedIssueNumberInput.value = issueNumber;
        setCreateIssueVisibility(false);
        modeText.innerHTML = escapeHtml(i18n.commentingOn) + ' <strong>#' + escapeHtml(String(issueNumber)) + ': ' + escapeHtml(issueTitle) + '</strong>';
        showEl(modeIndicator);
        submitBtn.textContent = i18n.postComment;
        issuesList.innerHTML = '<div class="alert alert-success small" role="status">' + escapeHtml(i18n.lockedOnto) + ' #' + escapeHtml(String(issueNumber)) + '. ' + escapeHtml(i18n.addCommentBelow) + '</div>';
        updateSubmitButtonState();
        if (issueComment) issueComment.focus();
    }

    cancelCommentModeBtn.addEventListener('click', function () {
        selectedIssueNumberInput.value = '';
        setCreateIssueVisibility(true);
        hideEl(modeIndicator);
        submitBtn.textContent = i18n.submitBtn;
        if (hasMatchingIssues) {
            showEl(topDuplicateGate);
            showEl(bottomDuplicateGate);
        }
        issuesList.innerHTML = '<span class="text-muted">' + escapeHtml(i18n.typeToSearch) + '</span>';
        updateSubmitButtonState();
        titleInput.focus();
    });

    function endpointForMode() {
        return selectedIssueNumberInput.value
            ? basePath + '/admin/gh-feedback/comment'
            : basePath + '/admin/gh-feedback/store';
    }

    form.addEventListener('submit', function (e) {
        e.preventDefault();
        responseDiv.innerHTML = '';
        hideEl(previewPanel);
        submitBtn.disabled = true;
        const isCommenting = !!selectedIssueNumberInput.value;
        submitBtn.textContent = isCommenting ? i18n.posting : i18n.submitting;

        previewOnlyField.value = '1';
        confirmPublicField.value = '0';
        confirmSensitiveField.value = '0';
        finalTitleField.value = '';
        finalBodyField.value = '';

        const formData = new FormData(form);
        fetch(endpointForMode(), { method: 'POST', body: formData, credentials: 'same-origin' })
            .then(function (res) { return res.json().then(function (data) { return { ok: res.ok, data: data }; }); })
            .then(function (result) {
                const data = result.data || {};
                if (data.preview) {
                    previewTitle.value = data.title || '';
                    previewBody.value = data.body || '';
                    if (data.looks_sensitive) showEl(sensitiveWarn); else hideEl(sensitiveWarn);
                    confirmPublicCheck.checked = false;
                    confirmSensitiveCheck.checked = false;
                    showEl(previewPanel);
                    previewPanel.scrollIntoView({ behavior: 'smooth', block: 'nearest' });
                    previewBody.focus();
                } else if (data.error) {
                    responseDiv.innerHTML = '<div class="alert alert-danger" role="alert">' + escapeHtml(data.error) + '</div>';
                } else {
                    responseDiv.innerHTML = '<div class="alert alert-danger" role="alert">' + escapeHtml(i18n.errorOccurred) + '</div>';
                }
            })
            .catch(function () {
                responseDiv.innerHTML = '<div class="alert alert-danger" role="alert">' + escapeHtml(i18n.networkError) + '</div>';
            })
            .finally(function () {
                submitBtn.disabled = false;
                submitBtn.textContent = selectedIssueNumberInput.value ? i18n.postComment : i18n.submitBtn;
                updateSubmitButtonState();
            });
    });

    previewBackBtn.addEventListener('click', function () {
        hideEl(previewPanel);
        submitBtn.focus();
    });

    previewSendBtn.addEventListener('click', function () {
        if (!confirmPublicCheck.checked) {
            confirmPublicCheck.focus();
            return;
        }
        if (!sensitiveWarn.hidden && !confirmSensitiveCheck.checked) {
            confirmSensitiveCheck.focus();
            return;
        }
        previewOnlyField.value = '0';
        confirmPublicField.value = '1';
        confirmSensitiveField.value = (!sensitiveWarn.hidden && confirmSensitiveCheck.checked) ? '1' : '0';
        finalTitleField.value = previewTitle.value;
        finalBodyField.value = previewBody.value;

        previewSendBtn.disabled = true;
        previewSendBtn.textContent = i18n.sending;
        const formData = new FormData(form);
        fetch(endpointForMode(), { method: 'POST', body: formData, credentials: 'same-origin' })
            .then(function (res) { return res.json().then(function (data) { return { ok: res.ok, data: data }; }); })
            .then(function (result) {
                const data = result.data || {};
                if (data.success) {
                    hideEl(previewPanel);
                    if (selectedIssueNumberInput.value) {
                        responseDiv.innerHTML = '<div class="alert alert-success" role="alert">' + escapeHtml(data.message || '') + '</div>';
                        form.reset();
                        cancelCommentModeBtn.click();
                    } else {
                        const url = data.html_url || '';
                        responseDiv.innerHTML = '<div class="alert alert-success" role="alert">'
                            + escapeHtml(data.message || '') + '<br>'
                            + '<strong>' + escapeHtml(i18n.issueCreated) + ' #' + escapeHtml(String(data.issue_number || '')) + '</strong> '
                            + escapeHtml(i18n.createdSuccess) + '<br>'
                            + '<div class="mt-2 p-2 bg-white border rounded"><strong>' + escapeHtml(i18n.saveLink) + '</strong><br>'
                            + '<a href="' + escapeHtml(url) + '" target="_blank" rel="noopener noreferrer" class="text-break">' + escapeHtml(url) + '</a><br>'
                            + '<small class="text-muted">' + escapeHtml(i18n.makeNote) + '</small></div></div>';
                        form.reset();
                        hasMatchingIssues = false;
                        hideEl(topDuplicateGate);
                        hideEl(bottomDuplicateGate);
                        syncDuplicateCheckboxes(false);
                        issuesList.innerHTML = '<span class="text-muted">' + escapeHtml(i18n.typeToSearch) + '</span>';
                    }
                } else {
                    responseDiv.innerHTML = '<div class="alert alert-danger" role="alert">' + escapeHtml(data.error || i18n.errorOccurred) + '</div>';
                }
            })
            .catch(function () {
                responseDiv.innerHTML = '<div class="alert alert-danger" role="alert">' + escapeHtml(i18n.networkError) + '</div>';
            })
            .finally(function () {
                previewSendBtn.disabled = false;
                previewSendBtn.textContent = i18n.previewSend;
            });
    });
});
</script>
<?php require_once ROOT_PATH . '/partials/footer.php'; ?>
