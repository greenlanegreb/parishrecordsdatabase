<?php
/**
 * MIGRATED FILE MAPPING
 * ---------------------
 * Original Old File: admin/view_ticket.php
 * Migrated Date: 2026-08-04 11:15:00
 */
declare(strict_types=1);

/** @string $message */
/** @string $error */
/** @int $ticketId */
/** @array{id: int, subject?: string, name?: string, email: string, created_at: string, status: string, user_id?: int, username?: string} $ticket */
/** @array<int, array<string, mixed>> $dynValues */
/** @array<int, array<string, mixed>> $thread */
/** @string $userTimezone */
/** @string $fullFormatStr */

require_once ROOT_PATH . '/partials/header.php';
$basePath = defined('BASE_PATH') ? rtrim(BASE_PATH, '/') : '';
?>

<div class="container py-4" style="max-width: 900px;" role="region" aria-label="Support Ticket Detail">
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

    <div class="mb-3">
        <a href="<?= $basePath ?>/admin/tickets" class="btn btn-sm btn-outline-secondary">← <?= htmlspecialchars(__('view_ticket.back_to_dashboard') ?? 'Back to Dashboard', ENT_QUOTES, 'UTF-8') ?></a>
    </div>

    <!-- Ticket Summary Card -->
    <div class="card shadow-sm border-0 p-4 mb-4">
        <h3 class="fw-bold text-dark mb-2">
            <?= htmlspecialchars(__('view_ticket.ticket_heading_prefix') ?? 'Ticket', ENT_QUOTES, 'UTF-8') ?> #<?= $ticket['id'] ?>: <?= htmlspecialchars($ticket['subject'] ?? __('view_ticket.support_request') ?? 'Support Request', ENT_QUOTES, 'UTF-8') ?>
        </h3>
        <p class="text-muted small mb-3">
            <strong><?= htmlspecialchars(__('view_ticket.submitted_by') ?? 'Submitted by:', ENT_QUOTES, 'UTF-8') ?></strong> 
            <?= htmlspecialchars($ticket['name'] ?? __('view_ticket.anonymous') ?? 'Anonymous', ENT_QUOTES, 'UTF-8') ?> 
            (<a href="mailto:<?= htmlspecialchars($ticket['email'], ENT_QUOTES, 'UTF-8') ?>" class="text-decoration-none"><?= htmlspecialchars($ticket['email'], ENT_QUOTES, 'UTF-8') ?></a>) 
            <?= htmlspecialchars(__('view_ticket.on_date') ?? 'on', ENT_QUOTES, 'UTF-8') ?> 
            <?= format_user_time($ticket['created_at'], $userTimezone, $fullFormatStr) ?>
        </p>
        
        <!-- Custom Schema Fields Response Data -->
        <?php if (!empty($dynValues)): ?>
            <div class="mt-3 pt-3 border-top">
                <h5 class="h6 fw-bold text-dark mb-2"><?= htmlspecialchars(__('view_ticket.submitted_fields') ?? 'Additional Information', ENT_QUOTES, 'UTF-8') ?></h5>
                <ul class="list-unstyled mb-0">
                    <?php foreach ($dynValues as $dv): ?>
                        <?php 
                            $colName = isset($dv['column_name']) && is_string($dv['column_name']) ? $dv['column_name'] : '';
                            $valContent = isset($dv['value_content']) && is_string($dv['value_content']) ? $dv['value_content'] : '';
                        ?>
                        <li class="mb-2"><strong><?= htmlspecialchars($colName, ENT_QUOTES, 'UTF-8') ?>:</strong> <span class="text-muted"><?= nl2br(htmlspecialchars($valContent, ENT_QUOTES, 'UTF-8')) ?></span></li>
                    <?php endforeach; ?>
                </ul>
            </div>
        <?php endif; ?>

        <!-- Status Change Form -->
        <form method="POST" action="<?= $basePath ?>/admin/tickets/action" class="mt-3 pt-3 border-top">
            <?= csrf_field() ?>
            <input type="hidden" name="action" value="update_status">
            <input type="hidden" name="ticket_id" value="<?= $ticket['id'] ?>">
            <label for="status" class="form-label small fw-bold"><?= htmlspecialchars(__('view_ticket.ticket_status_label') ?? 'Status', ENT_QUOTES, 'UTF-8') ?></label>
            <select id="status" name="status" class="form-select form-select-sm max-width-250" onchange="this.form.submit()">
                <option value="Pending" <?= ($ticket['status'] === 'Pending') ? 'selected' : '' ?>><?= htmlspecialchars(__('view_ticket.status_pending') ?? 'Pending', ENT_QUOTES, 'UTF-8') ?></option>
                <option value="In Progress" <?= ($ticket['status'] === 'In Progress') ? 'selected' : '' ?>><?= htmlspecialchars(__('view_ticket.status_progress') ?? 'In Progress', ENT_QUOTES, 'UTF-8') ?></option>
                <option value="Completed" <?= ($ticket['status'] === 'Completed') ? 'selected' : '' ?>><?= htmlspecialchars(__('view_ticket.status_completed') ?? 'Completed', ENT_QUOTES, 'UTF-8') ?></option>
                <option value="Rejected" <?= ($ticket['status'] === 'Rejected') ? 'selected' : '' ?>><?= htmlspecialchars(__('view_ticket.status_rejected') ?? 'Rejected', ENT_QUOTES, 'UTF-8') ?></option>
            </select>
        </form>
    </div>

    <h4 class="h5 fw-bold text-dark mb-3"><?= htmlspecialchars(__('view_ticket.dialogue_heading') ?? 'Dialogue Thread', ENT_QUOTES, 'UTF-8') ?></h4>
    
    <?php if (empty($thread)): ?>
        <div class="card shadow-sm border-0 bg-light text-center py-4 text-muted mb-4">
            <?= htmlspecialchars(__('view_ticket.no_replies') ?? 'No replies in this thread yet.', ENT_QUOTES, 'UTF-8') ?>
        </div>
    <?php else: ?>
        <div class="d-flex flex-column gap-3 mb-4">
            <?php foreach ($thread as $rep): ?>
                <?php 
                    $isAdminReply = !empty($rep['is_admin_reply']);
                    $repUsername = isset($rep['username']) && is_string($rep['username']) ? $rep['username'] : (__('view_ticket.staff') ?? 'Staff');
                    $repCreatedAt = isset($rep['created_at']) && is_string($rep['created_at']) ? $rep['created_at'] : '';
                    $repMessage = isset($rep['message']) && is_string($rep['message']) ? $rep['message'] : '';
                ?>
                <div class="card shadow-sm border-0 <?= $isAdminReply ? 'bg-white border-start border-primary border-4' : 'bg-light' ?>">
                    <div class="card-body">
                        <p class="small text-muted mb-2">
                            <strong><?= $isAdminReply ? '🛡️ ' . htmlspecialchars(__('view_ticket.admin_label') ?? 'Admin', ENT_QUOTES, 'UTF-8') . ' (' . htmlspecialchars($repUsername, ENT_QUOTES, 'UTF-8') . ')' : htmlspecialchars($ticket['name'] ?? __('view_ticket.anonymous') ?? 'Anonymous', ENT_QUOTES, 'UTF-8') ?></strong> — 
                            <em class="text-secondary"><?= format_user_time($repCreatedAt, $userTimezone, $fullFormatStr) ?></em>
                        </p>
                        <div class="text-break" style="white-space: pre-wrap;"><?= htmlspecialchars($repMessage, ENT_QUOTES, 'UTF-8') ?></div>
                    </div>
                </div>
            <?php endforeach; ?>
        </div>
    <?php endif; ?>

    <!-- Reply Box Card -->
    <div class="card shadow-sm border-0 p-4">
        <h4 class="h5 fw-bold text-dark mb-3"><?= htmlspecialchars(__('view_ticket.post_reply_heading') ?? 'Post a Reply', ENT_QUOTES, 'UTF-8') ?></h4>
        <form method="POST" action="<?= $basePath ?>/admin/tickets/action">
            <?= csrf_field() ?>
            <input type="hidden" name="action" value="post_reply">
            <input type="hidden" name="ticket_id" value="<?= $ticket['id'] ?>">
            
            <div class="mb-3">
                <textarea name="reply_message" rows="4" placeholder="<?= htmlspecialchars(__('view_ticket.reply_placeholder') ?? 'Type your reply here...', ENT_QUOTES, 'UTF-8') ?>" class="form-control" required></textarea>
            </div>
            
            <button type="submit" class="btn btn-primary"><?= htmlspecialchars(__('view_ticket.send_reply_btn') ?? 'Send Reply', ENT_QUOTES, 'UTF-8') ?></button>
        </form>
    </div>
</div>

<?php require_once ROOT_PATH . '/partials/footer.php'; ?>
