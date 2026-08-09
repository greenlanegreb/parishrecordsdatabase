<?php
declare(strict_types=1);
?>
<!-- TAB 6: Audit Log -->
<div class="tab-pane fade" id="panel-audit" role="tabpanel" aria-labelledby="tab-audit">
    <h4 class="h5 fw-bold text-dark mb-1"><?= htmlspecialchars(__('settings.audit_heading'), ENT_QUOTES, 'UTF-8') ?></h4>
    <p class="text-muted small mb-3"><?= htmlspecialchars(__('settings.audit_subheading'), ENT_QUOTES, 'UTF-8') ?></p>

    <!-- Audit Maintenance Actions -->
    <div class="card shadow-sm border-0 bg-light p-3 mb-4 d-flex flex-row flex-wrap gap-2 align-items-center">
        <form method="POST" action="<?= $basePath ?>/admin/audit/purge" onsubmit="return confirm('<?= htmlspecialchars(__('settings.purge_all_confirm'), ENT_QUOTES, 'UTF-8') ?>');">
            <?= csrf_field() ?>
            <input type="hidden" name="purge_type" value="all">
            <button type="submit" class="btn btn-sm btn-danger"><?= htmlspecialchars(__('settings.clear_all_audit_btn'), ENT_QUOTES, 'UTF-8') ?></button>
        </form>

        <form method="POST" action="<?= $basePath ?>/admin/audit/purge" onsubmit="return confirm('<?= htmlspecialchars(__('settings.purge_records_confirm'), ENT_QUOTES, 'UTF-8') ?>');">
            <?= csrf_field() ?>
            <input type="hidden" name="purge_type" value="records_only">
            <button type="submit" class="btn btn-sm btn-outline-secondary"><?= htmlspecialchars(__('settings.clear_records_audit_btn'), ENT_QUOTES, 'UTF-8') ?></button>
        </form>

        <?php foreach ($distinctActions as $act): ?>
            <form method="POST" action="<?= $basePath ?>/admin/audit/purge" onsubmit="return confirm('Clear all audit logs matching action type: <?= htmlspecialchars($act, ENT_QUOTES, 'UTF-8') ?>?');">
                <?= csrf_field() ?>
                <input type="hidden" name="purge_type" value="<?= htmlspecialchars($act, ENT_QUOTES, 'UTF-8') ?>">
                <button type="submit" class="btn btn-sm btn-outline-secondary">Clear '<?= htmlspecialchars($act, ENT_QUOTES, 'UTF-8') ?>' Logs</button>
            </form>
        <?php endforeach; ?>
    </div>

    <!-- Full Audit Log Table View -->
    <div class="card shadow-sm border-0">
        <div class="table-responsive" style="max-height: 600px;">
            <table class="table table-hover align-middle mb-0 small" role="table">
                <thead class="table-light sticky-top">
                    <tr>
                        <th scope="col" class="py-3 ps-3"><?= htmlspecialchars(__('settings.th_id'), ENT_QUOTES, 'UTF-8') ?></th>
                        <th scope="col" class="py-3"><?= htmlspecialchars(__('settings.th_timestamp'), ENT_QUOTES, 'UTF-8') ?></th>
                        <th scope="col" class="py-3"><?= htmlspecialchars(__('settings.th_actor'), ENT_QUOTES, 'UTF-8') ?></th>
                        <th scope="col" class="py-3"><?= htmlspecialchars(__('settings.th_action'), ENT_QUOTES, 'UTF-8') ?></th>
                        <th scope="col" class="py-3"><?= htmlspecialchars(__('settings.th_record_id'), ENT_QUOTES, 'UTF-8') ?></th>
                        <th scope="col" class="py-3"><?= htmlspecialchars(__('settings.th_details'), ENT_QUOTES, 'UTF-8') ?></th>
                        <th scope="col" class="py-3 pe-3"><?= htmlspecialchars(__('settings.th_ip'), ENT_QUOTES, 'UTF-8') ?></th>
                    </tr>
                </thead>
                <tbody>
                    <?php if (empty($auditLogs)): ?>
                        <tr>
                            <td colspan="7" class="text-center py-4 text-muted"><?= htmlspecialchars(__('settings.no_audit_logs'), ENT_QUOTES, 'UTF-8') ?></td>
                        </tr>
                    <?php else: ?>
                        <?php foreach ($auditLogs as $al): ?>
                            <?php 
                                $alId = isset($al['id']) ? (int)$al['id'] : 0;
                                $alCreatedAt = isset($al['created_at']) && is_string($al['created_at']) ? $al['created_at'] : '';
                                $alUsername = isset($al['username']) && is_string($al['username']) ? $al['username'] : __('settings.system_guest');
                                $alAction = isset($al['action']) && is_string($al['action']) ? $al['action'] : '';
                                $alRecordId = isset($al['record_id']) ? (int)$al['record_id'] : 0;
                                $alDetails = isset($al['details']) && is_string($al['details']) ? $al['details'] : '';
                                $alIp = isset($al['ip_address']) && is_string($al['ip_address']) ? $al['ip_address'] : 'N/A';
                            ?>
                            <tr>
                                <td class="ps-3 fw-bold"><?= $alId ?></td>
                                <td class="text-nowrap"><?= format_user_time($alCreatedAt, $userTimezone, $fullFormatStr) ?></td>
                                <td><?= htmlspecialchars($alUsername, ENT_QUOTES, 'UTF-8') ?></td>
                                <td><span class="badge bg-light text-dark border"><?= htmlspecialchars($alAction, ENT_QUOTES, 'UTF-8') ?></span></td>
                                <td><?= $alRecordId > 0 ? '#' . $alRecordId : '—' ?></td>
                                <td class="text-break"><?= htmlspecialchars($alDetails, ENT_QUOTES, 'UTF-8') ?></td>
                                <td class="pe-3 font-monospace"><?= htmlspecialchars($alIp, ENT_QUOTES, 'UTF-8') ?></td>
                            </tr>
                        <?php endforeach; ?>
                    <?php endif; ?>
                </tbody>
            </table>
        </div>
    </div>
    <p class="text-muted small mt-2"><?= htmlspecialchars(__('settings.audit_limit_note'), ENT_QUOTES, 'UTF-8') ?></p>
</div>
