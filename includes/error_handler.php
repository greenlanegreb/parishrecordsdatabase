<?php
// includes/error_handler.php
declare(strict_types=1);

function register_global_error_handlers(string $logDir): void {
    // Catch standard PHP notices, warnings, and deprecations
    set_error_handler(function (int $severity, string $message, string $file, int $line) use ($logDir): bool {
        if (!(error_reporting() & $severity)) {
            // This error code is not included in error_reporting
            return false;
        }
        $err = new \ErrorException($message, 0, $severity, $file, $line);
        handle_system_error($err, 'PhpError', $logDir);
        return true;
    });

    // Catch unhandled exceptions
    set_exception_handler(function (\Throwable $exception) use ($logDir): void {
        handle_system_error($exception, 'Exception', $logDir);
    });

    // Catch fatal compilation and parse shutdowns
    register_shutdown_function(function () use ($logDir): void {
        $error = error_get_last();
        if ($error !== null && in_array($error['type'], [E_ERROR, E_PARSE, E_CORE_ERROR, E_COMPILE_ERROR], true)) {
            $fatal = new \ErrorException($error['message'], 0, $error['type'], $error['file'], $error['line']);
            handle_system_error($fatal, 'FatalError', $logDir);
        }
    });
}

function handle_system_error(\Throwable $exception, string $type, string $logDir): void {
    if (!is_dir($logDir)) {
        mkdir($logDir, 0755, true);
    }

    $errorData = [
        "timestamp"   => gmdate('Y-m-d\TH:i:s\Z'),
        "error_type"  => $type . '_' . get_class($exception),
        "message"     => $exception->getMessage(),
        "file"        => $exception->getFile(),
        "line"        => $exception->getLine(),
        "request_uri" => $_SERVER['REQUEST_URI'] ?? 'CLI/Unknown',
        "method"      => $_SERVER['REQUEST_METHOD'] ?? 'UNKNOWN',
        "ip"          => $_SERVER['REMOTE_ADDR'] ?? '127.0.0.1'
    ];

    // Always log full details silently to disk
    error_log(json_encode($errorData) . PHP_EOL, 3, $logDir . '/error_structured.log');

    if (!headers_sent()) {
        http_response_code(500);
    }

    // Determine environment safety (Local vs Production)
    $isLocal = (getenv('APP_ENV') === 'development' || file_exists(__DIR__ . '/../.env.local'));
    
    $errorTitle = 'An Unexpected Error Occurred';
    $errorMessage = $isLocal 
        ? $exception->getMessage() 
        : 'The system encountered an unhandled exception and has safely halted execution.';
    $trace = $isLocal ? $exception->getTraceAsString() : '';
    $errorFile = $isLocal ? $exception->getFile() : '';
    $errorLine = $isLocal ? $exception->getLine() : 0;

    $basePath = defined('BASE_PATH') && is_string(BASE_PATH) ? rtrim(BASE_PATH, '/') : '';

    // Load the pure view template
    $templatePath = __DIR__ . '/../app/Views/errors/error_template.php';
    if (is_file($templatePath)) {
        require $templatePath;
    } else {
        echo "<h1>500 — {$errorTitle}</h1><p>{$errorMessage}</p>";
    }
    exit;
}
