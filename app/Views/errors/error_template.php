<?php
// app/Views/errors/error_template.php
declare(strict_types=1);

/**
 * Pure MVC View Template for Errors
 * Expects variables passed from the handler:
 * - $errorCode (int) e.g. 404, 500
 * - $errorTitle (string)
 * - $errorMessage (string)
 * - $isLocal (bool) show debug block when true
 * - $errorFile (string) optional
 * - $errorLine (int) optional
 * - $trace (string) optional redacted stack trace
 * - $basePath (string)
 */

$errorCode    = $errorCode ?? 500;
$errorTitle   = $errorTitle ?? 'An Unexpected Error Occurred';
$errorMessage = $errorMessage ?? 'The system encountered an unhandled exception and has safely halted execution.';
$basePath     = defined('BASE_PATH') ? rtrim((string) BASE_PATH, '/') : ($basePath ?? '');
$isLocal      = $isLocal ?? false;
$errorFile    = $errorFile ?? '';
$errorLine    = $errorLine ?? 0;
$trace        = $trace ?? '';
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?= htmlspecialchars($errorCode . ' — ' . $errorTitle, ENT_QUOTES, 'UTF-8') ?></title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet" crossorigin="anonymous">
    <link rel="stylesheet" href="<?= htmlspecialchars($basePath, ENT_QUOTES, 'UTF-8') ?>/assets/style.css">
</head>
<body id="page-body" class="bg-light d-flex justify-content-center align-items-center min-vh-100 py-5">
    <div class="container" style="max-width: 650px;">
        <div class="card shadow-sm border-0 p-4 w-100 bg-white text-center" role="alert">
            <h2 class="h4 fw-bold text-danger mb-3">
                <?= htmlspecialchars($errorCode . ' — ' . $errorTitle, ENT_QUOTES, 'UTF-8') ?>
            </h2>
            <p class="text-secondary small mb-4" style="line-height: 1.5;">
                <?= htmlspecialchars($errorMessage, ENT_QUOTES, 'UTF-8') ?>
            </p>

            <?php if ($isLocal): ?>
                <div class="text-start bg-light p-3 rounded border small mb-4">
                    <div class="fw-bold text-dark mb-1">Debug details</div>
                    <?php if ($errorFile !== ''): ?>
                        <div class="text-muted mb-2">
                            <strong>File:</strong>
                            <?= htmlspecialchars((string) $errorFile, ENT_QUOTES, 'UTF-8') ?>
                            (Line <?= (int) $errorLine ?>)
                        </div>
                    <?php endif; ?>
                    <?php if ($trace !== ''): ?>
                        <label class="form-label fw-bold text-muted small">Stack trace</label>
                        <pre class="bg-dark text-light p-2 rounded small overflow-auto mb-0" style="max-height: 280px; white-space: pre-wrap;"><code><?= htmlspecialchars((string) $trace, ENT_QUOTES, 'UTF-8') ?></code></pre>
                    <?php endif; ?>
                </div>
            <?php endif; ?>

            <div class="mt-2">
                <a href="<?= htmlspecialchars($basePath, ENT_QUOTES, 'UTF-8') ?>/" class="btn btn-primary btn-sm px-4 fw-bold text-decoration-none">
                    <?= htmlspecialchars(function_exists('__') ? __('error_template.return_home_btn') : 'Return to Public Home', ENT_QUOTES, 'UTF-8') ?>
                </a>
            </div>
        </div>
    </div>
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js" crossorigin="anonymous"></script>
</body>
</html>
