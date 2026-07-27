<?php
// db.php - Database connection with structured JSON error handling & logging

$host = '127.0.0.1';
$db   = 'c21cakebreaddb';
$user = 'c21cakebreaddb';
$pass = '3ajCAbX#4k';
$charset = 'utf8mb4';

$dsn = "mysql:host=$host;dbname=$db;charset=$charset";
$options = [
    PDO::ATTR_ERRMODE            => PDO::ERRMODE_EXCEPTION,
    PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
    PDO::ATTR_EMULATE_PREPARES   => false,
];

// Ensure a secure logs directory exists
$log_dir = __DIR__ . '/../logs';
if (!is_dir($log_dir)) {
    mkdir($log_dir, 0755, true);
}

// Global Exception Handler designed for human clarity and LLM token efficiency
set_exception_handler(function ($exception) use ($log_dir) {
    // 1. Build structured JSON context block
    $error_data = [
        "timestamp" => gmdate('Y-m-d\TH:i:s\Z'),
        "error_type" => get_class($exception),
        "message" => $exception->getMessage(),
        "file" => $exception->getFile(),
        "line" => $exception->getLine(),
        "request_uri" => $_SERVER['REQUEST_URI'] ?? 'CLI/Unknown',
        "method" => $_SERVER['REQUEST_METHOD'] ?? 'UNKNOWN',
        "ip" => $_SERVER['REMOTE_ADDR'] ?? '127.0.0.1'
    ];

    // 2. Write structured log line to disk
    $log_line = json_encode($error_data) . PHP_EOL;
    error_log($log_line, 3, $log_dir . '/error_structured.log');

    // 3. Render clean user-facing output
    if (!headers_sent()) {
        http_response_code(500);
    }
    ?>
    <!DOCTYPE html>
    <html lang="en">
    <head>
        <meta charset="UTF-8">
        <title>System Error</title>
        <link rel="stylesheet" href="/assets/css/style.css">
    </head>
    <body id="page-body">
        <div class="search-box-container error-container" role="alert">
            <h2 class="error-heading">⚠️ An Unexpected Error Occurred</h2>
            <p>The system encountered an unhandled exception and has safely halted execution.</p>
            <p><em>If you are debugging this with your AI collaborator, please provide the following structured error snippet:</em></p>
            
            <pre class="error-pre"><code><?php echo htmlspecialchars(json_encode($error_data, JSON_PRETTY_PRINT)); ?></code></pre>
            
            <p class="error-footer-link"><a href="/index.php" class="btn">Return to Public Home</a></p>
        </div>
    </body>
    </html>
    <?php
    exit;
});

// Register a shutdown function to catch fatal errors (e.g., syntax errors, call to undefined functions)
register_shutdown_function(function () use ($log_dir) {
    $error = error_get_last();
    // Check if the error is a fatal type
    if ($error !== null && in_array($error['type'], [E_ERROR, E_PARSE, E_CORE_ERROR, E_COMPILE_ERROR])) {
        $error_data = [
            "timestamp" => gmdate('Y-m-d\TH:i:s\Z'),
            "error_type" => "FatalError_" . $error['type'],
            "message" => $error['message'],
            "file" => $error['file'],
            "line" => $error['line'],
            "request_uri" => $_SERVER['REQUEST_URI'] ?? 'CLI/Unknown',
            "method" => $_SERVER['REQUEST_METHOD'] ?? 'UNKNOWN',
            "ip" => $_SERVER['REMOTE_ADDR'] ?? '127.0.0.1'
        ];

        // Write to disk
        $log_line = json_encode($error_data) . PHP_EOL;
        error_log($log_line, 3, $log_dir . '/error_structured.log');

        // Render clean UI if headers haven't been sent yet
        if (!headers_sent()) {
            http_response_code(500);
            ?>
            <!DOCTYPE html>
            <html lang="en">
            <head>
                <meta charset="UTF-8">
                <title>System Error</title>
                <link rel="stylesheet" href="/assets/css/style.css">
            </head>
            <body id="page-body">
                <div class="search-box-container error-container" role="alert">
                    <h2 class="error-heading">⚠️ A Critical System Error Occurred</h2>
                    <p>The system encountered a fatal error and has safely halted execution.</p>
                    <p><em>If you are debugging this with your AI collaborator, please provide the following structured error snippet:</em></p>
                    
                    <pre class="error-pre"><code><?php echo htmlspecialchars(json_encode($error_data, JSON_PRETTY_PRINT)); ?></code></pre>
                    
                    <p class="error-footer-link"><a href="/index.php" class="btn">Return to Public Home</a></p>
                </div>
            </body>
            </html>
            <?php
        }
        exit;
    }
});

try {
    $pdo = new PDO($dsn, $user, $pass, $options);
    
    // Include and execute the global maintenance guard
    require_once __DIR__ . '/maintenance_guard.php';
    check_maintenance_mode($pdo);

} catch (\PDOException $e) {
    throw new \PDOException("Database Connection Failed: " . $e->getMessage(), (int)$e->getCode());
}
?>
