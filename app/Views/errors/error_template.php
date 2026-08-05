<?php
/**
 * MIGRATED FILE MAPPING
 * ---------------------
 * Original Old File: errors/error_template.php
 * Migrated Date: 2026-08-05 06:05:12
 */declare(strict_types=1);

/**
 * MIGRATED FILE MAPPING
 * ---------------------
 * Original Old File: errors/error_template.php
 * Migrated Date: 2026-08-04 16:45:00
 */

/**
 * Renders a custom HTTP error page using Bootstrap 5 and modern markup.
 * 
 * @param int $code HTTP response status code
 * @param string $title Error page title
 * @param string $message Detailed description or error body message
 */
function render_http_error(int $code, string $title, string $message): void
{
    if (!headers_sent()) {
        http_response_code($code);
    }
    
    $basePath = defined('BASE_PATH') && is_string(BASE_PATH) ? rtrim(BASE_PATH, '/') : '';
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?= htmlspecialchars($title, ENT_QUOTES, 'UTF-8') ?></title>
    <!-- Bootstrap 5 CSS CDN -->
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet" integrity="sha384-QWTKZyjpPEjISv5WaRU9OFeRpok6YctnYmDr5pNlyT2bRjXh0JMhjY6hW+ALEwIH" crossorigin="anonymous">
    <link rel="stylesheet" href="<?= htmlspecialchars($basePath, ENT_QUOTES, 'UTF-8') ?>/assets/style.css">
</head>
<body id="page-body" class="bg-light d-flex justify-content-center align-items-center min-vh-100">
    <div class="card shadow-sm border-0 p-4 w-100 text-center" style="max-width: 480px;" role="alert">
        <h2 class="h4 fw-bold text-danger mb-3"><?= htmlspecialchars($code . ' — ' . $title, ENT_QUOTES, 'UTF-8') ?></h2>
        <p class="text-muted small mb-4" style="line-height: 1.5;"><?= htmlspecialchars($message, ENT_QUOTES, 'UTF-8') ?></p>
        <div class="mt-2">
            <a href="<?= htmlspecialchars($basePath, ENT_QUOTES, 'UTF-8') ?>/index.php" class="btn btn-primary px-4"><?= htmlspecialchars(__('error_template.return_home_btn'), ENT_QUOTES, 'UTF-8') ?></a>
        </div>
    </div>
    <!-- Bootstrap 5 JS Bundle CDN -->
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js" integrity="sha384-YvpcrYf0tY3lHB60NNkmXc5s9fDVZLESaAA55NDzOxhy9GkcIdslK1eN7N6jIeHz" crossorigin="anonymous"></script>
</body>
</html>
<?php
    exit;
}
