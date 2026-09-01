<?php
declare(strict_types=1);
$basePath = isset($basePath) && is_string($basePath) ? $basePath : '';
$auditLogs = $auditLogs ?? [];
$distinctActions = $distinctActions ?? [];
$st = static function (string $key, string $fallback): string {
    $t = function_exists('__') ? (string) __($key) : $key;
    if ($t === $key || $t === '') {
        $t = $fallback;
    }
    return function_exists('prd_title_case') ? prd_title_case($t) : $t;
};
?>
<div class="tab-pane fade" id="panel-audit" role="tabpanel" aria-labelledby="tab-audit">
    <h4 class="h5 fw-bold text-dark mb-1"><?= htmlspecialchars($st('settings.audit_heading', 'Audit Log'), ENT_QUOTES, 'UTF-8') ?></h4>
    <p class="text-muted small mb-3"><?= htmlspecialchars($st('settings.audit_subheading', 'See Who Changed What, Then Clear Old Entries If You Need To.'), ENT_QUOTES, 'UTF-8') ?></p>

    <div class="card shadow-sm border-0 bg-light p-3 mb-4">
        <div class="dropdown">
            <button class="btn btn-sm btn-outline-danger dropdown-toggle" type="button" id="auditClearMenu" data-bs-toggle="dropdown" data-bs-display="static" aria-expanded="false">
                <?= htmlspecialchars($st('settings.audit_clear_label', 'Clear Logs'), ENT_QUOTES, 'UTF-8') ?>
            </button>
            <ul class="dropdown-menu shadow-sm" aria-labelledby="auditClearMenu">
                <li>
                    <form method="POST" action="<?= htmlspecialchars($basePath, ENT_QUOTES, 'UTF-8') ?>/admin/audit/purge" onsubmit="return confirm(<?= json_encode(__('settings.purge_all_confirm') !== 'settings.purge_all_confirm' ? __('settings.purge_all_confirm') : 'Clear the whole audit log?', JSON_UNESCAPED_UNICODE) ?>);">
                        <?= csrf_field() ?>
                        <input type="hidden" name="purge_type" value="all">
                        <button type="submit" class="dropdown-item text-danger"><?= htmlspecialchars($st('settings.clear_all_audit_btn', 'Clear All Audit Logs'), ENT_QUOTES, 'UTF-8') ?></button>
                    </form>
                </li>
                <li>
                    <form method="POST" action="<?= htmlspecialchars($basePath, ENT_QUOTES, 'UTF-8') ?>/admin/audit/purge" onsubmit="return confirm(<?= json_encode(__('settings.purge_records_confirm') !== 'settings.purge_records_confirm' ? __('settings.purge_records_confirm') : 'Clear record-related audit rows?', JSON_UNESCAPED_UNICODE) ?>);">
                        <?= csrf_field() ?>
                        <input type="hidden" name="purge_type" value="records_only">
                        <button type="submit" class="dropdown-item"><?= htmlspecialchars($st('settings.clear_records_audit_btn', 'Clear Records-Related Logs'), ENT_QUOTES, 'UTF-8') ?></button>
                    </form>
                </li>
                <?php if (!empty($distinctActions)): ?>
                    <li><hr class="dropdown-divider"></li>
                    <?php foreach ($distinctActions as $act): ?>
                        <?php if (!is_string($act) || $act === '') { continue; } ?>
                        <li>
                            <form method="POST" action="<?= htmlspecialchars($basePath, ENT_QUOTES, 'UTF-8') ?>/admin/audit/purge" onsubmit="return confirm(<?= json_encode($st('settings.purge_action_confirm', 'Clear Logs For This Action?'), JSON_UNESCAPED_UNICODE) ?>);">
                                <?= csrf_field() ?>
                                <input type="hidden" name="purge_type" value="<?= htmlspecialchars($act, ENT_QUOTES, 'UTF-8') ?>">
                                <button type="submit" class="dropdown-item"><?= htmlspecialchars($st('settings.clear_action_logs', 'Clear') . ' ' . $act . ' ' . $st('settings.logs_word', 'Logs'), ENT_QUOTES, 'UTF-8') ?></button>
                            </form>
                        </li>
                    <?php endforeach; ?>
                <?php endif; ?>
            </ul>
        </div>
    </div>

    <div class="card shadow-sm border-0 mb-3">
        <div class="table-responsive">
            <table class="table table-striped table-hover align-middle mb-0 small w-100" role="table">
                <thead class="table-light">
                    <tr>
                        <th scope="col" class="py-3 ps-4"><?= htmlspecialchars($st('settings.th_id', 'ID'), ENT_QUOTES, 'UTF-8') ?></th>
                        <th scope="col" class="py-3"><?= htmlspecialchars($st('settings.th_timestamp', 'Timestamp'), ENT_QUOTES, 'UTF-8') ?></th>
                        <th scope="col" class="py-3"><?= htmlspecialchars($st('settings.th_actor', 'Actor'), ENT_QUOTES, 'UTF-8') ?></th>
                        <th scope="col" class="py-3"><?= htmlspecialchars($st('settings.th_action', 'Action'), ENT_QUOTES, 'UTF-8') ?></th>
                        <th scope="col" class="py-3"><?= htmlspecialchars($st('settings.th_record_id', 'Record'), ENT_QUOTES, 'UTF-8') ?></th>
                        <th scope="col" class="py-3"><?= htmlspecialchars($st('settings.th_details', 'Details'), ENT_QUOTES, 'UTF-8') ?></th>
                        <th scope="col" class="py-3 pe-4"><?= htmlspecialchars($st('settings.th_ip', 'IP'), ENT_QUOTES, 'UTF-8') ?></th>
                    </tr>
                </thead>
                <tbody>
                    <?php if (empty($auditLogs)): ?>
                        <tr>
                            <td colspan="7" class="text-center py-4 text-muted"><?= htmlspecialchars($st('settings.no_audit_logs', 'No Audit Logs Yet.'), ENT_QUOTES, 'UTF-8') ?></td>
                        </tr>
                    <?php else: ?>
                        <?php foreach ($auditLogs as $al): ?>
                            <?php
                                $alId = isset($al['id']) ? (int) $al['id'] : 0;
                                $alCreatedAt = isset($al['created_at']) && is_string($al['created_at']) ? $al['created_at'] : '';
                                $alUsername = isset($al['username']) && is_string($al['username']) ? $al['username'] : $st('settings.system_guest', 'System / Guest');
                                $alAction = isset($al['action']) && is_string($al['action']) ? $al['action'] : '';
                                $alRecordId = isset($al['record_id']) ? (int) $al['record_id'] : 0;
                                $alDetails = isset($al['details']) && is_string($al['details']) ? $al['details'] : '';
                                $alIp = isset($al['ip_address']) && is_string($al['ip_address']) ? $al['ip_address'] : 'N/A';
                            ?>
                            <tr>
                                <td class="ps-4 fw-bold"><?= $alId ?></td>
                                <td class="text-nowrap"><?= function_exists('format_user_time') ? format_user_time($alCreatedAt, $userTimezone ?? 'UTC', $fullFormatStr ?? 'd/m/Y H:i') : htmlspecialchars($alCreatedAt, ENT_QUOTES, 'UTF-8') ?></td>
                                <td><?= htmlspecialchars($alUsername, ENT_QUOTES, 'UTF-8') ?></td>
                                <td><span class="badge bg-light text-dark border"><?= htmlspecialchars($alAction, ENT_QUOTES, 'UTF-8') ?></span></td>
                                <td><?= $alRecordId > 0 ? '#' . $alRecordId : '—' ?></td>
                                <td class="text-break"><?= htmlspecialchars($alDetails, ENT_QUOTES, 'UTF-8') ?></td>
                                <td class="pe-4 font-monospace"><?= htmlspecialchars($alIp, ENT_QUOTES, 'UTF-8') ?></td>
                            </tr>
                        <?php endforeach; ?>
                    <?php endif; ?>
                </tbody>
            </table>
        </div>
    </div>
    <p class="text-muted small mt-2"><?= htmlspecialchars($st('settings.audit_limit_note', 'Showing Recent Entries Only.'), ENT_QUOTES, 'UTF-8') ?></p>
</div>
