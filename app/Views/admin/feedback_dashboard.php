<?php
/**
 * MIGRATED FILE MAPPING
 * ---------------------
 * Original Old File: admin/feedback_dashboard.php
 * Migrated Date: 2026-08-04 09:28:44
 */
declare(strict_types=1);

/** @string $message */
/** @string $error */
/** @array<int, array<string, mixed>> $tickets */
/** @string $userTimezone */
/** @string $fullFormatStr */
/** @array{id: int, username: string, timezone?: string, date_format?: string} $currentUser */

require_once ROOT_PATH . '/partials/header.php';
$basePath = defined('BASE_PATH') ? rtrim(BASE_PATH, '/') : '';
?>

<div class="container-fluid py-4" style="max-width: 1500px;" role="region" aria-label="Feedback Dashboard">
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
            <h3 class="fw-bold mb-1"><?= htmlspecialchars(__('feedback_dash.heading') ?? 'Support Tickets & Feedback Dashboard', ENT_QUOTES, 'UTF-8') ?></h3>
            <p class="text-muted mb-0"><?= htmlspecialchars(__('feedback_dash.subheading') ?? 'Manage public support requests, update statuses, and participate in direct dialogue.', ENT_QUOTES, 'UTF-8') ?></p>
        </div>
        <div class="d-flex gap-2 flex-wrap">
            <a href="<?= $basePath ?>/admin/feedback/emails" class="btn btn-outline-secondary">✉️ <?= htmlspecialchars(__('feedback_dash.manage_emails') ?? 'Manage Email Templates', ENT_QUOTES, 'UTF-8') ?></a>
            <a href="<?= $basePath ?>/admin/feedback/schema" class="btn btn-outline-secondary">⚙️ <?= htmlspecialchars(__('feedback_dash.manage_schema') ?? 'Manage Ticket Form Schema', ENT_QUOTES, 'UTF-8') ?></a>
        </div>
    </div>

    <!-- Tickets Table Card -->
    <div class="card shadow-sm border-0">
        <div class="table-responsive">
            <table class="table table-hover align-middle mb-0" role="table">
                <table class="table table-hover align-middle mb-0" role="table">
                <thead class="table-light">
                    <tr>
                        <th scope="col" class="py-3 ps-3"><?= htmlspecialchars(__('feedback_dash.th_ticket_date') ?? 'Ticket ID / Date', ENT_QUOTES, 'UTF-8') ?></th>
                        <th scope="col" class="py-3"><?= htmlspecialchars(__('feedback_dash.th_submitter') ?? 'Submitter', ENT_QUOTES, 'UTF-8') ?></th>
                        <th scope="col" class="py-3"><?= htmlspecialchars(__('feedback_dash.th_subject_info') ?? 'Subject / Initial Info', ENT_QUOTES, 'UTF-8') ?></th>
                        <th scope="col" class="py-3"><?= htmlspecialchars(__('feedback_dash.th_status') ?? 'Status', ENT_QUOTES, 'UTF-8') ?></th>
                        <th scope="col" class="py-3 text-end pe-3"><?= htmlspecialchars(__('index.th_actions') ?? 'Actions', ENT_QUOTES, 'UTF-8') ?></th>
                    </tr>
                </thead>
                <tbody>
                    <?php if (empty($tickets)): ?>
                        <tr>
                            <td colspan="5" class="text-center py-4 text-muted"><?= htmlspecialchars(__('feedback_dash.no_tickets') ?? 'No feedback tickets found.', ENT_QUOTES, 'UTF-8') ?></td>
                        </tr>
                    <?php else: ?>
                        <?php foreach ($tickets as $t): ?>
                            <?php 
                                $subId = isset($t['id']) ? (int)$t['id'] : 0;
                                $subEmail = isset($t['email']) && is_string($t['email']) ? $t['email'] : '';
                                $firstName = isset($t['first_name']) && is_string($t['first_name']) ? $t['first_name'] : '';
                                $surname = isset($t['surname']) && is_string($t['surname']) ? $t['surname'] : '';
                                $fullName = trim("{$firstName} {$surname}");
                                if ($fullName === '') {
                                    $fullName = $t['name'] ?? ($t['username'] ?? __('feedback_dash.anonymous') ?? 'Anonymous');
                                }

                                $status = isset($t['status']) && is_string($t['status']) ? $t['status'] : 'Pending';
                                $badgeClass = match(strtolower($status)) {
                                    'completed', 'resolved' => 'bg-success',
                                    'rejected', 'closed' => 'bg-danger',
                                    default => 'bg-warning text-dark'
                                };

                                $createdAt = isset($t['created_at']) && is_string($t['created_at']) ? $t['created_at'] : '';
                                $subjectText = isset($t['subject']) && is_string($t['subject']) ? $t['subject'] : 'Support Inquiry';
                            ?>
                            <tr>
                                <td class="ps-3">
                                    <strong>#<?= $subId ?></strong><br>
                                    <small class="text-muted"><?= format_user_time($createdAt, $userTimezone ?? 'UTC', $fullFormatStr ?? 'Y-m-d H:i:s') ?></small>
                                </td>
                                <td>
                                    <strong class="text-dark"><?= htmlspecialchars($fullName, ENT_QUOTES, 'UTF-8') ?></strong><br>
                                    <?php if ($subEmail !== ''): ?>
                                        <a href="mailto:<?= htmlspecialchars($subEmail, ENT_QUOTES, 'UTF-8') ?>" class="text-decoration-none small"><?= htmlspecialchars($subEmail, ENT_QUOTES, 'UTF-8') ?></a>
                                    <?php endif; ?>
                                </td>
                                <td><?= htmlspecialchars(substr($subjectText, 0, 60), ENT_QUOTES, 'UTF-8') ?>...</td>
                                <td>
                                    <span class="badge <?= $badgeClass ?>"><?= htmlspecialchars($status, ENT_QUOTES, 'UTF-8') ?></span>
                                </td>
                                <td class="text-end pe-3 text-nowrap">
                                    <div class="d-flex justify-content-end gap-1">
                                        <a href="<?= $basePath ?>/admin/tickets/<?= $subId ?>" class="btn btn-sm btn-outline-secondary py-0 px-2" style="font-size: 0.75rem;"><?= htmlspecialchars(__('feedback_dash.open_ticket_btn') ?? 'Open Ticket & Dialogue', ENT_QUOTES, 'UTF-8') ?></a>
                                        
                                        <form method="POST" action="<?= $basePath ?>/admin/tickets/action" class="d-inline" onsubmit="return confirm('<?= htmlspecialchars(__('feedback_dash.delete_confirm') ?? 'Are you sure you want to delete this ticket?', ENT_QUOTES, 'UTF-8') ?>');">
                                            <?= csrf_field() ?>
                                            <input type="hidden" name="action" value="delete_ticket">
                                            <input type="hidden" name="ticket_id" value="<?= $subId ?>">
                                            <button type="submit" class="btn btn-sm btn-danger py-0 px-2" style="font-size: 0.75rem;"><?= htmlspecialchars(__('btn.delete') ?? 'Delete', ENT_QUOTES, 'UTF-8') ?></button>
                                        </form>
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

<?php require_once ROOT_PATH . '/partials/footer.php'; ?>
