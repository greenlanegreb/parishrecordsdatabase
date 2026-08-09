<?php
declare(strict_types=1);
?>
<!-- TAB 4: Site Notices -->
<div class="tab-pane fade" id="panel-notices" role="tabpanel" aria-labelledby="tab-notices">
    <div class="d-flex justify-content-between align-items-center mb-3">
        <h4 class="h5 fw-bold text-dark mb-0"><?= htmlspecialchars(__('settings.notices_heading'), ENT_QUOTES, 'UTF-8') ?></h4>
        <a href="<?= $basePath ?>/admin/notices" class="btn btn-sm btn-outline-secondary"><?= htmlspecialchars(__('settings.add_notice_btn'), ENT_QUOTES, 'UTF-8') ?></a>
    </div>

    <?php if (empty($notices)): ?>
        <div class="card shadow-sm border-0 text-center py-5 text-muted bg-light">
            <?= htmlspecialchars(__('settings.no_notices'), ENT_QUOTES, 'UTF-8') ?>
        </div>
    <?php else: ?>
        <div class="d-flex flex-column gap-3">
            <?php foreach ($notices as $n): ?>
                <?php 
                    $noticeId = isset($n['id']) ? (int)$n['id'] : 0;
                    $title = isset($n['title']) && is_string($n['title']) ? $n['title'] : '';
                    $content = isset($n['content']) && is_string($n['content']) ? $n['content'] : '';
                    $isActive = !empty($n['is_active']);
                ?>
                <div class="card shadow-sm border-0">
                    <div class="card-body">
                        <details>
                            <summary class="fw-bold fs-6 text-dark d-flex justify-content-between align-items-center" style="cursor: pointer;">
                                <span><?= htmlspecialchars($title, ENT_QUOTES, 'UTF-8') ?></span>
                                <span class="badge <?= $isActive ? 'bg-success' : 'bg-danger' ?>"><?= $isActive ? htmlspecialchars(__('settings.status_active'), ENT_QUOTES, 'UTF-8') : htmlspecialchars(__('settings.status_inactive'), ENT_QUOTES, 'UTF-8') ?></span>
                            </summary>
                            <div class="mt-3 pt-3 border-top">
                                <form method="POST" action="<?= $basePath ?>/admin/notices/inline-save">
                                    <?= csrf_field() ?>
                                    <input type="hidden" name="notice_id" value="<?= $noticeId ?>">
                                    
                                    <div class="mb-2">
                                        <label class="form-label small fw-bold"><?= htmlspecialchars(__('notices.title_label'), ENT_QUOTES, 'UTF-8') ?></label>
                                        <input type="text" name="title" value="<?= htmlspecialchars($title, ENT_QUOTES, 'UTF-8') ?>" class="form-control form-control-sm" required>
                                    </div>

                                    <div class="mb-3">
                                        <label class="form-label small fw-bold"><?= htmlspecialchars(__('settings.notice_content_label'), ENT_QUOTES, 'UTF-8') ?></label>
                                        <textarea name="content" rows="3" class="form-control form-control-sm" required><?= htmlspecialchars($content, ENT_QUOTES, 'UTF-8') ?></textarea>
                                    </div>

                                    <button type="submit" name="update_action" value="save" class="btn btn-sm btn-primary"><?= htmlspecialchars(__('settings.save_notice_btn'), ENT_QUOTES, 'UTF-8') ?></button>
                                </form>
                            </div>
                        </details>
                    </div>
                </div>
            <?php endforeach; ?>
        </div>
    <?php endif; ?>
</div>
