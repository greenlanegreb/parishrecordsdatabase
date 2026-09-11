<?php
declare(strict_types=1);

namespace App\Controllers;

use App\Services\TokenCleanupService;
use Exception;
use PDO;

class AdminCronController
{
    public function __construct(private PDO $pdo)
    {
    }

    public function runTokenCleanup(): void
    {
        $isCli = (PHP_SAPI === 'cli');
        if (!$isCli) {
            $method = isset($_SERVER['REQUEST_METHOD']) && is_string($_SERVER['REQUEST_METHOD'])
                ? $_SERVER['REQUEST_METHOD'] : 'GET';
            if ($method !== 'POST') {
                http_response_code(405);
                exit('Method Not Allowed');
            }
            verify_csrf_token();
            $currentUser = require_permission($this->pdo, 'manage_settings', 'Perform maintenance token cleanup');
        }

        $actorId = (!$isCli && isset($currentUser['id'])) ? (int) $currentUser['id'] : null;
        $ip = $isCli ? '127.0.0.1' : (isset($_SERVER['REMOTE_ADDR']) && is_string($_SERVER['REMOTE_ADDR']) ? $_SERVER['REMOTE_ADDR'] : '127.0.0.1');
        $svc = new TokenCleanupService($this->pdo);

        try {
            $n = $svc->run();
            $details = sprintf(
                'Token maintenance executed successfully. Purged %d expired tokens (%d invite, %d reset) and %d leftover invite tokens on activated accounts.',
                $n['invite'] + $n['reset'],
                $n['invite'],
                $n['reset'],
                $n['stale_invite']
            );
            $svc->audit($actorId, $ip, 'TOKEN_CLEANUP_SUCCESS', $details);
            if ($isCli) {
                echo '[SUCCESS] ' . $details . PHP_EOL;
                exit(0);
            }
            $_SESSION['message'] = $details;
            $base = defined('BASE_PATH') ? rtrim((string) BASE_PATH, '/') : '';
            header('Location: ' . $base . '/admin/settings?tab=maintenance');
            exit;
        } catch (Exception $e) {
            $errorDetails = 'Token maintenance failed: ' . $e->getMessage();
            try {
                $svc->audit($actorId, $ip, 'TOKEN_CLEANUP_FAIL', $errorDetails);
            } catch (Exception $ignore) {
            }
            if ($isCli) {
                fwrite(STDERR, '[ERROR] ' . $errorDetails . PHP_EOL);
                exit(1);
            }
            $_SESSION['error'] = $errorDetails;
            $base = defined('BASE_PATH') ? rtrim((string) BASE_PATH, '/') : '';
            header('Location: ' . $base . '/admin/settings?tab=maintenance');
            exit;
        }
    }
}
