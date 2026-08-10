<?php
// includes/error_handler.php
declare(strict_types=1);

function register_global_error_handlers(string $logDir): void
{
    // Catch standard PHP notices, warnings, and deprecations
    set_error_handler(function (int $severity, string $message, string $file, int $line) use ($logDir): bool {
        if (!(error_reporting() & $severity)) {
            // This error code is not included in error_reporting
            return false;
        }
        $err = new \ErrorException($message, 0, $errno, $file, $line);
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

/**
 * Strip common secrets from text before logging or displaying.
 */
function redact_sensitive_text(string $text): string
{
    // Query-string style secrets: ?token=...&password=...
    $text = preg_replace(
        '/([?&](token|invite_token|reset_token|password|passwd|secret|api_key|access_token)=)[^&\s]+/i',
        '$1[REDACTED]',
        $text
    ) ?? $text;

    // key=value or key: value style secrets in messages
    $text = preg_replace(
        '/\b(password|passwd|secret|api_key|token|invite_token|reset_token)\s*[:=]\s*\S+/i',
        '$1=[REDACTED]',
        $text
    ) ?? $text;

    // mysql URLs with embedded credentials
    $text = preg_replace(
        '/mysql:\/\/[^@\s]+@/i',
        'mysql://[REDACTED]@',
        $text
    ) ?? $text;

    return $text;
}

function handle_system_error(\Throwable $exception, string $type, string $logDir): void
{
    if (!is_dir($logDir)) {
        @mkdir($logDir, 0755, true);
    }

    $errorId = 'E-' . gmdate('Ymd') . '-' . strtoupper(bin2hex(random_bytes(3)));

    $message = redact_sensitive_text($exception->getMessage());
    $trace   = redact_sensitive_text($exception->getTraceAsString());
    $uri     = redact_sensitive_text((string) ($_SERVER['REQUEST_URI'] ?? 'CLI/Unknown'));

    $errorData = [
        'id'          => $errorId,
        'timestamp'   => gmdate('Y-m-d\TH:i:s\Z'),
        'error_type'  => $type . '_' . get_class($exception),
        'message'     => $message,
        'file'        => $exception->getFile(),
        'line'        => $exception->getLine(),
        'trace'       => $trace,
        'request_uri' => $uri,
        'method'      => $_SERVER['REQUEST_METHOD'] ?? 'UNKNOWN',
        'ip'          => $_SERVER['REMOTE_ADDR'] ?? '127.0.0.1',
    ];

    // Always log structured details to disk (redacted)
    $logLine = json_encode($errorData, JSON_UNESCAPED_SLASHES);
    if ($logLine !== false) {
        @error_log($logLine . PHP_EOL, 3, $logDir . '/error_structured.log');
    }
    // Fallback if structured write fails
    @error_log('[' . $errorId . '] ' . $message . ' in ' . $exception->getFile() . ':' . $exception->getLine());

    if (!headers_sent()) {
        http_response_code(500);
    }

    // Detailed on-screen errors only when APP_DEBUG is true in config.local.php
    $isLocal = defined('APP_DEBUG') && APP_DEBUG === true;

    $errorCode  = 500;
    $errorTitle = 'An Unexpected Error Occurred';
    $errorMessage = $isLocal
        ? $message
        : 'The system encountered an unhandled exception and has safely halted execution. Reference: ' . $errorId;
    $errorFile = $isLocal ? $exception->getFile() : '';
    $errorLine = $isLocal ? $exception->getLine() : 0;
    // $trace already redacted; empty when not debugging
    if (!$isLocal) {
        $trace = '';
    }

    $basePath = defined('BASE_PATH') && is_string(BASE_PATH) ? rtrim(BASE_PATH, '/') : '';

    // Load the pure view template
    $templatePath = __DIR__ . '/../app/Views/errors/error_template.php';
    if (is_file($templatePath)) {
        require $templatePath;
    } else {
        echo '<h1>500 — ' . htmlspecialchars($errorTitle, ENT_QUOTES, 'UTF-8') . '</h1>';
        echo '<p>' . htmlspecialchars($errorMessage, ENT_QUOTES, 'UTF-8') . '</p>';
    }
    exit;
}
