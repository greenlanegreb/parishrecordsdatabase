<?php
/**
 * MIGRATED FILE MAPPING
 * ---------------------
 * Original Old File: admin/volunteer_dashboard.php
 * Migrated Date: 2026-08-04 11:30:00
 */
declare(strict_types=1);

/** @var string $message */
/** @var string $error */
/** @var array<int, array<string, mixed>> $columns */
/** @var array<int, array<string, mixed>> $submissions */
/** @var array<int, array<int, string>> $submissionValues */
/** @var string $userTimezone */
/** @var string $fullFormatStr */
/** @var array{id: int, username: string, timezone?: string, date_format?: string} $currentUser */

require_once ROOT_PATH . '/partials/header.php';
$basePath = defined('BASE_PATH') ? rtrim(BASE_PATH, '/') : '';
?>
<div class="container-fluid py-4" style="max-width: 1500px;" role="region" aria-label="Volunteer Portal Dashboard">
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

    <div class="d-flex justify-content-between align-items-center mb-4 flex-wrap gap-3">
        <div>
            <h3 class="fw-bold mb-1"><?= htmlspecialchars(__('volunteer_dashboard.heading'), ENT_QUOTES, 'UTF-8') ?></h3>
            <p class="text-muted mb-0"><?= htmlspecialchars(__('volunteer_dashboard.subheading'), ENT_QUOTES, 'UTF-8') ?></p>
        </div>
        <div class="d-flex gap-2 flex-wrap">
            <a href="<?= $basePath ?>/admin/volunteers/emails" class="btn btn-outline-secondary">✉️ <?= htmlspecialchars(__('volunteer_dashboard.manage_emails_btn'), ENT_QUOTES, 'UTF-8') ?></a>
            <a href="<?= $basePath ?>/admin/volunteers/schema" class="btn btn-outline-secondary">⚙️ <?= htmlspecialchars(__('volunteer_dashboard.manage_schema_btn'), ENT_QUOTES, 'UTF-8') ?></a>
        </div>
    </div>

    <div class="card shadow-sm border-0">
        <div class="table-responsive">
            <table class="table table-striped table-hover align-middle mb-0 w-100" role="table">
                <thead class="table-light">
                    <tr>
                        <th scope="col" class="py-3 ps-3"><?= htmlspecialchars(__('volunteer_dashboard.th_status'), ENT_QUOTES, 'UTF-8') ?></th>
                        <th scope="col" class="py-3"><?= htmlspecialchars(__('volunteer_dashboard.th_name'), ENT_QUOTES, 'UTF-8') ?></th>
                        <th scope="col" class="py-3"><?= htmlspecialchars(__('feedback_schema.th_email'), ENT_QUOTES, 'UTF-8') ?></th>
                        <?php foreach ($columns as $col): ?>
                            <?php $colName = isset($col['column_name']) && is_string($col['column_name']) ? $col['column_name'] : ''; ?>
                            <th scope="col" class="py-3"><?= htmlspecialchars($colName, ENT_QUOTES, 'UTF-8') ?></th>
                        <?php endforeach; ?>
                        <th scope="col" class="py-3"><?= htmlspecialchars(__('volunteer_dashboard.th_interview_notes'), ENT_QUOTES, 'UTF-8') ?></th>
                        <th scope="col" class="py-3"><?= htmlspecialchars(__('manage_tables.th_date_created'), ENT_QUOTES, 'UTF-8') ?></th>
                        <th scope="col" class="py-3 text-end pe-3"><?= htmlspecialchars(__('index.th_actions'), ENT_QUOTES, 'UTF-8') ?></th>
                    </tr>
                </thead>
                <tbody>
                    <?php if (empty($submissions)): ?>
                        <tr>
                            <td colspan="<?= count($columns) + 6 ?>" class="text-center py-4 text-muted"><?= htmlspecialchars(__('volunteer_dashboard.no_submissions'), ENT_QUOTES, 'UTF-8') ?></td>
                        </tr>
                    <?php else: ?>
                        <?php foreach ($submissions as $sub): ?>
                            <?php
                                $subId = isset($sub['id']) ? (int) $sub['id'] : 0;
                                $subEmail = isset($sub['email']) && is_string($sub['email']) ? $sub['email'] : '';
                                $firstName = isset($sub['first_name']) && is_string($sub['first_name']) ? $sub['first_name'] : '';
                                $surname = isset($sub['surname']) && is_string($sub['surname']) ? $sub['surname'] : '';
                                $fullName = isset($sub['applicant_display']) && is_string($sub['applicant_display'])
                                    ? $sub['applicant_display']
                                    : (trim($firstName . ' ' . $surname) !== ''
                                        ? trim($firstName . ' ' . $surname)
                                        : ('Volunteer #' . $subId));
                                $prefUser = isset($sub['preferred_username_display']) && is_string($sub['preferred_username_display'])
                                    ? $sub['preferred_username_display'] : '';
                                $status = isset($sub['status']) && is_string($sub['status']) ? $sub['status'] : 'Pending Review';
                                $badgeClass = match ($status) {
                                    'Accepted' => 'bg-success',
                                    'Chat Scheduled' => 'bg-warning text-dark',
                                    'Rejected' => 'bg-danger',
                                    default => 'bg-secondary'
                                };
                                $interviewDate = isset($sub['interview_date']) && is_string($sub['interview_date'])
                                    ? $sub['interview_date'] : '';
                                $interviewNotes = isset($sub['interview_notes']) && is_string($sub['interview_notes'])
                                    ? $sub['interview_notes'] : '';
                                $createdAt = isset($sub['created_at']) && is_string($sub['created_at'])
                                    ? $sub['created_at'] : '';
                            ?>
                            <tr>
                                <td class="ps-3">
                                    <span class="badge <?= $badgeClass ?>"><?= htmlspecialchars($status, ENT_QUOTES, 'UTF-8') ?></span>
                                </td>
                                <td class="fw-bold text-dark">
                                    <?= htmlspecialchars($fullName, ENT_QUOTES, 'UTF-8') ?>
                                    <?php if ($prefUser !== ''): ?>
                                        <br><small class="text-muted fw-normal">@<?= htmlspecialchars($prefUser, ENT_QUOTES, 'UTF-8') ?></small>
                                    <?php endif; ?>
                                </td>
                                <td>
                                    <?php if ($subEmail !== ''): ?>
                                        <a href="mailto:<?= htmlspecialchars($subEmail, ENT_QUOTES, 'UTF-8') ?>" class="text-decoration-none"><?= htmlspecialchars($subEmail, ENT_QUOTES, 'UTF-8') ?></a>
                                    <?php endif; ?>
                                </td>

                                <?php foreach ($columns as $col): ?>
                                    <?php
                                        $colId = isset($col['id']) ? (int) $col['id'] : 0;
                                        $dataType = isset($col['data_type']) && is_string($col['data_type']) ? $col['data_type'] : '';
                                        $boolFormat = isset($col['boolean_display_format']) && is_string($col['boolean_display_format'])
                                            ? $col['boolean_display_format'] : 'yes_no';
                                        $dateFormat = isset($currentUser['date_format']) && is_string($currentUser['date_format'])
                                            ? $currentUser['date_format'] : 'd/m/Y';
                                        $rawVal = $submissionValues[$subId][$colId] ?? '';
                                        if ($dataType === 'BOOLEAN') {
                                            $displayVal = format_boolean_value($rawVal, $boolFormat);
                                        } elseif ($dataType === 'DATE') {
                                            $displayVal = format_display_date($rawVal, $dateFormat);
                                        } else {
                                            $displayVal = nl2br(htmlspecialchars((string) $rawVal, ENT_QUOTES, 'UTF-8'));
                                        }
                                    ?>
                                    <td><?= $displayVal ?></td>
                                <?php endforeach; ?>

                                <td>
                                    <?php if ($interviewDate !== ''): ?>
                                        <small class="d-block"><strong><?= htmlspecialchars(__('volunteer_dashboard.chat_label'), ENT_QUOTES, 'UTF-8') ?></strong> <?= format_user_time($interviewDate, $userTimezone, $fullFormatStr) ?></small>
                                    <?php endif; ?>
                                    <?php if ($interviewNotes !== ''): ?>
                                        <small class="text-muted d-block text-truncate" style="max-width: 200px;" title="<?= htmlspecialchars($interviewNotes, ENT_QUOTES, 'UTF-8') ?>">
                                            <strong><?= htmlspecialchars(__('volunteer_dashboard.notes_label'), ENT_QUOTES, 'UTF-8') ?></strong> <?= htmlspecialchars($interviewNotes, ENT_QUOTES, 'UTF-8') ?>
                                        </small>
                                    <?php else: ?>
                                        <small class="text-muted fst-italic"><?= htmlspecialchars(__('volunteer_dashboard.no_notes'), ENT_QUOTES, 'UTF-8') ?></small>
                                    <?php endif; ?>
                                </td>
                                <td><?= format_user_time($createdAt, $userTimezone, $fullFormatStr) ?></td>
                                <td class="text-end pe-3">
                                    <div class="dropdown">
                                        <button class="btn btn-sm btn-outline-dark dropdown-toggle" type="button" data-bs-toggle="dropdown" aria-expanded="false">
                                            <?= htmlspecialchars(__('index.th_actions'), ENT_QUOTES, 'UTF-8') ?>
                                        </button>
                                        <ul class="dropdown-menu dropdown-menu-end shadow-sm">
                                            <li>
                                                <button type="button" class="dropdown-item"
                                                    onclick="openInterviewModal(<?= $subId ?>, '<?= htmlspecialchars(addslashes($status), ENT_QUOTES, 'UTF-8') ?>', '<?= htmlspecialchars($interviewDate, ENT_QUOTES, 'UTF-8') ?>', `<?= htmlspecialchars(addslashes($interviewNotes), ENT_QUOTES, 'UTF-8') ?>`)">
                                                    <?= htmlspecialchars(__('volunteer_dashboard.chat_notes_btn'), ENT_QUOTES, 'UTF-8') ?>
                                                </button>
                                            </li>
                                            <?php if ($subEmail !== ''): ?>
                                            <li>
                                                <a class="dropdown-item" href="<?= $basePath ?>/admin/users/create?email=<?= urlencode($subEmail) ?>&amp;first_name=<?= urlencode($firstName) ?>&amp;surname=<?= urlencode($surname) ?>&amp;volunteer_id=<?= $subId ?><?= $prefUser !== '' ? '&amp;username=' . urlencode($prefUser) : '' ?>">
                                                    <?= htmlspecialchars(__('volunteer_dashboard.accept_invite_btn'), ENT_QUOTES, 'UTF-8') ?>
                                                </a>
                                            </li>
                                            <?php endif; ?>
                                            <li><hr class="dropdown-divider"></li>
                                            <li>
                                                <form method="POST" action="<?= $basePath ?>/admin/volunteers"
                                                      onsubmit="return confirm('<?= htmlspecialchars(__('volunteer_dashboard.delete_confirm'), ENT_QUOTES, 'UTF-8') ?>');">
                                                    <?= csrf_field() ?>
                                                    <input type="hidden" name="action" value="delete_volunteer">
                                                    <input type="hidden" name="volunteer_id" value="<?= $subId ?>">
                                                    <button type="submit" class="dropdown-item text-danger"><?= htmlspecialchars(__('btn.delete'), ENT_QUOTES, 'UTF-8') ?></button>
                                                </form>
                                            </li>
                                        </ul>
                                    </div>
                                </td>
                            </tr>
                        <?php endforeach; ?>
                    <?php endif; ?>
                </tbody>
            </table>
        </div>
    </div>
</div>

<div class="modal fade" id="interviewModal" tabindex="-1" aria-labelledby="interviewModalLabel" aria-hidden="true">
    <div class="modal-dialog">
        <div class="modal-content">
            <form method="POST" action="<?= $basePath ?>/admin/volunteers">
                <?= csrf_field() ?>
                <input type="hidden" name="action" value="update_interview">
                <input type="hidden" name="volunteer_id" id="modal_volunteer_id">
                <div class="modal-header">
                    <h4 class="modal-title h5 fw-bold" id="interviewModalLabel"><?= htmlspecialchars(__('volunteer_dashboard.modal_heading'), ENT_QUOTES, 'UTF-8') ?></h4>
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                </div>
                <div class="modal-body">
                    <div class="mb-3">
                        <label for="modal_status" class="form-label fw-bold small"><?= htmlspecialchars(__('volunteer_dashboard.modal_status_label'), ENT_QUOTES, 'UTF-8') ?></label>
                        <select id="modal_status" name="status" class="form-select form-select-sm">
                            <option value="Pending Review"><?= htmlspecialchars(__('volunteer_dashboard.status_pending'), ENT_QUOTES, 'UTF-8') ?></option>
                            <option value="Chat Scheduled"><?= htmlspecialchars(__('volunteer_dashboard.status_chat'), ENT_QUOTES, 'UTF-8') ?></option>
                            <option value="Accepted"><?= htmlspecialchars(__('volunteer_dashboard.status_accepted'), ENT_QUOTES, 'UTF-8') ?></option>
                            <option value="Rejected"><?= htmlspecialchars(__('volunteer_dashboard.status_rejected'), ENT_QUOTES, 'UTF-8') ?></option>
                        </select>
                    </div>
                    <div class="mb-3">
                        <label for="modal_interview_date" class="form-label fw-bold small"><?= htmlspecialchars(__('volunteer_dashboard.modal_date_label'), ENT_QUOTES, 'UTF-8') ?></label>
                        <input type="datetime-local" id="modal_interview_date" name="interview_date" class="form-control form-control-sm">
                    </div>
                    <div class="mb-3">
                        <label for="modal_interview_notes" class="form-label fw-bold small"><?= htmlspecialchars(__('volunteer_dashboard.modal_notes_label'), ENT_QUOTES, 'UTF-8') ?></label>
                        <textarea id="modal_interview_notes" name="interview_notes" rows="4" class="form-control form-control-sm" placeholder="<?= htmlspecialchars(__('volunteer_dashboard.modal_notes_placeholder'), ENT_QUOTES, 'UTF-8') ?>"></textarea>
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary btn-sm" data-bs-dismiss="modal"><?= htmlspecialchars(__('btn.cancel'), ENT_QUOTES, 'UTF-8') ?></button>
                    <button type="submit" class="btn btn-primary btn-sm"><?= htmlspecialchars(__('volunteer_dashboard.save_changes_btn'), ENT_QUOTES, 'UTF-8') ?></button>
                </div>
            </form>
        </div>
    </div>
</div>

<script>
function openInterviewModal(id, status, date, notes) {
    document.getElementById('modal_volunteer_id').value = id;
    document.getElementById('modal_status').value = status;
    if (date) {
        document.getElementById('modal_interview_date').value = date.replace(' ', 'T').substring(0, 16);
    } else {
        document.getElementById('modal_interview_date').value = '';
    }
    document.getElementById('modal_interview_notes').value = notes;
    const modalEl = document.getElementById('interviewModal');
    const modalInstance = new bootstrap.Modal(modalEl);
    modalInstance.show();
}
</script>
<?php require_once ROOT_PATH . '/partials/footer.php'; ?>
