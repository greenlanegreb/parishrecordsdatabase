<?php
declare(strict_types=1);

/** @var array<int, array<string, mixed>> $recentErrors */
$recentErrors = $recentErrors ?? [];
/** @var array<string, mixed>|null $lookedUpError */
$lookedUpError = $lookedUpError ?? null;
/** @var string $errorLookupId */
$errorLookupId = $errorLookupId ?? '';
/** @var string $basePath */
$basePath = $basePath ?? '';
?>
<div class="tab-pane fade" id="panel-errors" role="tabpanel" aria-labelledby="tab-errors">
    <h4 class="h5 fw-bold text-dark mb-1">Error log</h4>
    <p class="text-muted small mb-3">
        Look up a reference ID shown on an error page (e.g. <code>E-20260810-A1B2C3</code>).
        Full detail is stored in the server log; on-screen detail for visitors depends on <code>APP_DEBUG</code>.
    </p>

    <div class="card shadow-sm border-0 mb-4">
        <div class="card-body">
            <form method="GET" action="<?= htmlspecialchars($basePath, ENT_QUOTES, 'UTF-8') ?>/admin/settings" class="row g-2 align-items-end">
                <input type="hidden" name="tab" value="errors">
                <div class="col-sm-8">
                    <label for="error_id" class="form-label small fw-bold">Error reference ID</label>
                    <input type="text" name="error_id" id="error_id" value="<?= htmlspecialchars($errorLookupId, ENT_QUOTES, 'UTF-8') ?>"
                           class="form-control form-control-sm" placeholder="E-YYYYMMDD-XXXXXX" autocomplete="off">
                </div>
                <div class="col-sm-4">
                    <button type="submit" class="btn btn-sm btn-primary w-100">Find</button>
                </div>
            </form>
        </div>
    </div>

    <?php if ($errorLookupId !== '' && $lookedUpError === null): ?>
        <div class="alert alert-warning small">No log entry found for <code><?= htmlspecialchars($errorLookupId, ENT_QUOTES, 'UTF-8') ?></code>.</div>
    <?php endif; ?>

    <?php if (is_array($lookedUpError)): ?>
        <div class="card shadow-sm border-0 mb-4">
            <div class="card-body small">
                <h5 class="h6 fw-bold">Found: <?= htmlspecialchars((string) ($lookedUpError['id'] ?? ''), ENT_QUOTES, 'UTF-8') ?></h5>
                <dl class="row mb-0">
                    <dt class="col-sm-3">Time (UTC)</dt>
                    <dd class="col-sm-9"><?= htmlspecialchars((string) ($lookedUpError['timestamp'] ?? ''), ENT_QUOTES, 'UTF-8') ?></dd>
                    <dt class="col-sm-3">Your local time</dt>
                    <dd class="col-sm-9">
                    <?= htmlspecialchars(
                        format_user_time(
                            isset($lookedUpError['timestamp']) ? (string) $lookedUpError['timestamp'] : null,
                            $userTimezone,
                            $fullFormatStr
                        ),
                        ENT_QUOTES,
                        'UTF-8'
                    ) ?>
                    </dd>
                    <dt class="col-sm-3">Type</dt>
                    <dd class="col-sm-9"><?= htmlspecialchars((string) ($lookedUpError['error_type'] ?? ''), ENT_QUOTES, 'UTF-8') ?></dd>
                    <dt class="col-sm-3">Message</dt>
                    <dd class="col-sm-9"><?= htmlspecialchars((string) ($lookedUpError['message'] ?? ''), ENT_QUOTES, 'UTF-8') ?></dd>
                    <dt class="col-sm-3">File</dt>
                    <dd class="col-sm-9"><?= htmlspecialchars((string) ($lookedUpError['file'] ?? ''), ENT_QUOTES, 'UTF-8') ?>
                        <?php if (!empty($lookedUpError['line'])): ?>
                            (Line <?= (int) $lookedUpError['line'] ?>)
                        <?php endif; ?>
                    </dd>
                    <dt class="col-sm-3">Request</dt>
                    <dd class="col-sm-9"><?= htmlspecialchars((string) ($lookedUpError['method'] ?? ''), ENT_QUOTES, 'UTF-8') ?>
                        <?= htmlspecialchars((string) ($lookedUpError['request_uri'] ?? ''), ENT_QUOTES, 'UTF-8') ?></dd>
                </dl>
                <?php if (!empty($lookedUpError['trace'])): ?>
                    <label class="form-label fw-bold mt-3 mb-1">Stack trace</label>
                    <pre class="bg-dark text-light p-2 rounded small overflow-auto mb-0" style="max-height: 280px; white-space: pre-wrap;"><code><?= htmlspecialchars((string) $lookedUpError['trace'], ENT_QUOTES, 'UTF-8') ?></code></pre>
                <?php endif; ?>
            </div>
        </div>
    <?php endif; ?>

    <h5 class="h6 fw-bold mb-2">Recent errors</h5>
    <?php if ($recentErrors === []): ?>
        <p class="text-muted small">No entries in the structured error log yet.</p>
    <?php else: ?>
        <div class="table-responsive card shadow-sm border-0">
            <table class="table table-sm table-hover mb-0 small">
                <thead class="table-light">
                    <tr>
                        <th>ID</th>
                        <th>Time</th>
                        <th>Message</th>
                    </tr>
                </thead>
                <tbody>
                    <?php foreach ($recentErrors as $err): ?>
                        <tr>
                            <td class="font-monospace">
                                <a href="<?= htmlspecialchars($basePath, ENT_QUOTES, 'UTF-8') ?>/admin/settings?tab=errors&error_id=<?= urlencode((string) ($err['id'] ?? '')) ?>#tab-errors">
                                    <?= htmlspecialchars((string) ($err['id'] ?? ''), ENT_QUOTES, 'UTF-8') ?>
                                </a>
                            </td>
                              <td>
                                <div class="font-monospace small"><?= htmlspecialchars((string) ($err['timestamp'] ?? ''), ENT_QUOTES, 'UTF-8') ?> UTC</div>
                                <div class="text-muted">
                                    <?= htmlspecialchars(
                                        format_user_time(
                                            isset($err['timestamp']) ? (string) $err['timestamp'] : null,
                                            $userTimezone,
                                            $fullFormatStr
                                        ),
                                        ENT_QUOTES,
                                        'UTF-8'
                                    ) ?>
                                </div>
                            </td>
                            <td><?= htmlspecialchars(mb_strimwidth((string) ($err['message'] ?? ''), 0, 80, '…'), ENT_QUOTES, 'UTF-8') ?></td>
                        </tr>
                    <?php endforeach; ?>
                </tbody>
            </table>
        </div>
    <?php endif; ?>
</div>
