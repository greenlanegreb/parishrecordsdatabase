<?php
/**
 * MIGRATED FILE MAPPING
 * ---------------------
 * Original Old File: admin/manage_volunteer_emails.php/admin/actions/save_volunteer_email_template.php
 * Migrated Date: 2026-08-05 03:28:00
 */declare(strict_types=1);

/**
 * MIGRATED FILE MAPPING
 * ---------------------
 * Original Old File: admin/manage_volunteer_emails.php
 * Migrated Date: 2026-08-04 09:50:18
 */

/** @string $message */
/** @string $error */
/** @array<int, array<string, mixed>> $templates */
/** @array<int, string> $columns */

require_once __DIR__ . '/../partials/header.php';
?>

<div class="container py-4" style="max-width: 1100px;">
    <h3 class="fw-bold mb-1"><?= htmlspecialchars(__('volunteer_emails.heading'), ENT_QUOTES, 'UTF-8') ?></h3>
    <p class="text-muted mb-3"><?= htmlspecialchars(__('volunteer_emails.subheading'), ENT_QUOTES, 'UTF-8') ?></p>

    <div class="mb-4">
        <a href="/admin/volunteers" class="btn btn-outline-secondary">← <?= htmlspecialchars(__('volunteer_emails.back_to_dashboard'), ENT_QUOTES, 'UTF-8') ?></a>
    </div>

    <!-- Feedback Alerts -->
    <?php if (!empty($message)): ?>
        <div class="alert alert-success alert-dismissible fade show" role="alert">
            <strong><?= htmlspecialchars($message, ENT_QUOTES, 'UTF-8') ?></strong>
            <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
        </div>
    <?php endif; ?>
    <?php if (!empty($error)): ?>
        <div class="alert alert-danger alert-dismissible fade show" role="alert">
            <strong><?= htmlspecialchars($error, ENT_QUOTES, 'UTF-8') ?></strong>
            <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
        </div>
    <?php endif; ?>

    <div class="row g-4">
        <!-- Left Column: Template Editor List -->
        <div class="col-lg-8">
            <?php foreach ($templates as $tpl): ?>
                <?php 
                    $tplId = isset($tpl['id']) ? (int)$tpl['id'] : 0;
                    $tplName = isset($tpl['template_name']) && is_string($tpl['template_name']) ? $tpl['template_name'] : '';
                    $triggerEvent = isset($tpl['trigger_event']) && is_string($tpl['trigger_event']) ? $tpl['trigger_event'] : '';
                    $tplSubject = isset($tpl['subject']) && is_string($tpl['subject']) ? $tpl['subject'] : '';
                    $tplBody = isset($tpl['body']) && is_string($tpl['body']) ? $tpl['body'] : '';
                ?>
                <div class="card shadow-sm border-0 mb-3">
                    <div class="card-body">
                        <details>
                            <summary class="fw-bold fs-5 text-dark" style="cursor: pointer;">
                                <?= htmlspecialchars($tplName, ENT_QUOTES, 'UTF-8') ?> 
                                <code class="badge bg-light text-dark border fs-6 ms-2 fw-normal"><?= htmlspecialchars($triggerEvent, ENT_QUOTES, 'UTF-8') ?></code>
                            </summary>
                            
                            <div class="mt-3 pt-3 border-top">
                                <form method="POST" action="/admin/volunteers/emails/store">
                                    <?= csrf_field() ?>
                                    <input type="hidden" name="template_id" value="<?= $tplId ?>">

                                    <div class="mb-3">
                                        <label for="subject_<?= $tplId ?>" class="form-label fw-bold"><?= htmlspecialchars(__('feedback_emails.email_subject'), ENT_QUOTES, 'UTF-8') ?></label>
                                        <input type="text" id="subject_<?= $tplId ?>" name="subject" value="<?= htmlspecialchars($tplSubject, ENT_QUOTES, 'UTF-8') ?>" required class="form-control">
                                    </div>

                                    <div class="mb-3">
                                        <label for="body_<?= $tplId ?>" class="form-label fw-bold"><?= htmlspecialchars(__('feedback_emails.email_body'), ENT_QUOTES, 'UTF-8') ?></label>
                                        <textarea id="body_<?= $tplId ?>" name="body" rows="8" required class="form-control font-monospace"><?= htmlspecialchars($tplBody, ENT_QUOTES, 'UTF-8') ?></textarea>
                                    </div>

                                    <button type="submit" class="btn btn-primary"><?= htmlspecialchars(__('feedback_emails.save_template_btn'), ENT_QUOTES, 'UTF-8') ?></button>
                                </form>
                            </div>
                        </details>
                    </div>
                </div>
            <?php endforeach; ?>
        </div>

        <!-- Right Column: Available Placeholders Sidebar -->
        <div class="col-lg-4">
            <div class="card shadow-sm border-0 bg-light p-3">
                <h4 class="h5 fw-bold text-dark mt-0"><?= htmlspecialchars(__('feedback_emails.placeholders_heading'), ENT_QUOTES, 'UTF-8') ?></h4>
                <p class="text-muted small"><?= htmlspecialchars(__('feedback_emails.placeholders_desc'), ENT_QUOTES, 'UTF-8') ?></p>
                
                <hr class="text-muted my-2">
                
                <strong class="small d-block mb-1"><?= htmlspecialchars(__('feedback_emails.fixed_tags'), ENT_QUOTES, 'UTF-8') ?></strong>
                <ul class="ps-3 small font-monospace mb-3 text-secondary">
                    <li>{first_name}</li>
                    <li>{surname}</li>
                    <li>{email}</li>
                    <li>{submission_id}</li>
                    <li>{system_name}</li>
                </ul>

                <?php if (!empty($columns)): ?>
                    <strong class="small d-block mb-1"><?= htmlspecialchars(__('feedback_emails.custom_tags'), ENT_QUOTES, 'UTF-8') ?></strong>
                    <p class="text-muted" style="font-size: 0.78rem; margin-bottom: 4px;"><?= htmlspecialchars(__('volunteer_emails.custom_tags_desc'), ENT_QUOTES, 'UTF-8') ?></p>
                    <ul class="ps-3 small font-monospace text-primary mb-0">
                        <?php foreach ($columns as $colLabel): 
                            $safeCol = is_string($colLabel) ? $colLabel : '';
                            $tag = '{' . strtolower(preg_replace('/[^a-zA-Z0-9_]/', '_', trim($safeCol))) . '}';
                        ?>
                            <li class="mb-2">
                                <?= htmlspecialchars($tag, ENT_QUOTES, 'UTF-8') ?><br>
                                <span class="text-muted font-sans-serif" style="font-size: 0.75rem;">(<?= htmlspecialchars($safeCol, ENT_QUOTES, 'UTF-8') ?>)</span>
                            </li>
                        <?php endforeach; ?>
                    </ul>
                <?php endif; ?>
            </div>
        </div>
    </div>
</div>

<?php require_once __DIR__ . '/../partials/footer.php'; ?>
