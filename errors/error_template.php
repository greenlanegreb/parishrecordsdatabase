<?php
// errors/error_template.php - Reusable template for custom HTTP error pages
$base_path = defined('BASE_PATH') ? BASE_PATH : '';

function render_http_error($code, $title, $message) {
    global $base_path;
    if (!headers_sent()) {
        http_response_code($code);
    }
    ?>
    <!DOCTYPE html>
    <html lang="en">
    <head>
        <meta charset="UTF-8">
        <title><?php echo htmlspecialchars($title); ?></title>
        <link rel="stylesheet" href="<?php echo $base_path; ?>/assets/css/style.css">
    </head>
    <body id="page-body">
        <div class="search-box-container error-container" role="alert">
            <h2 class="error-heading" style="color: #dc3545; margin-top: 0;"><?php echo htmlspecialchars($code . ' — ' . $title); ?></h2>
            <p style="color: #555; line-height: 1.5; font-size: 1rem;"><?php echo htmlspecialchars($message); ?></p>
            <p class="error-footer-link" style="margin-top: 1.5rem;">
                <a href="<?php echo $base_path; ?>/index.php" class="btn">Return to Public Home</a>
            </p>
        </div>
    </body>
    </html>
    <?php
    exit;
}
