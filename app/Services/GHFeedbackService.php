<?php
declare(strict_types=1);

namespace App\Services;

/**
 * Client for the central pRD → GitHub feedback gateway.
 * No GitHub credentials are stored in the install.
 */
class GHFeedbackService
{
    private string $proxyUrl = 'https://feedback.getprd.org/gh_proxy.php';

    /**
     * Shared with gateway config.php register_key.
     * Stops casual curl registration. Anyone with this pRD source can still copy it.
     * Override in config.local.php: define('PRD_GH_REGISTER_KEY', '...');
     */
    private function registerKey(): string
    {
        if (defined('PRD_GH_REGISTER_KEY') && is_string(PRD_GH_REGISTER_KEY) && PRD_GH_REGISTER_KEY !== '') {
            return PRD_GH_REGISTER_KEY;
        }
        return '73f80c77fae20984a2affde26e794c26bff8d6fe6b5b45e3d14b79667e2689e4';
    }

    private string $stateFile;
    private ?string $instanceUuid = null;
    private ?string $apiToken = null;

    public function __construct()
    {
        // Project-root storage/ (sibling of public/) — not web-served when docroot is public/
        $storageDir = dirname(__DIR__, 2) . '/storage';
        if (!is_dir($storageDir)) {
            @mkdir($storageDir, 0755, true);
        }
        $this->stateFile = $storageDir . '/gh_instance.json';
        $this->loadState();
    }

    private function loadState(): void
    {
        if (is_file($this->stateFile)) {
            $raw = @file_get_contents($this->stateFile);
            if (is_string($raw) && $raw !== '') {
                $data = json_decode($raw, true);
                if (is_array($data)) {
                    $this->instanceUuid = isset($data['instance_uuid']) && is_string($data['instance_uuid'])
                        ? $data['instance_uuid'] : null;
                    $this->apiToken = isset($data['api_token']) && is_string($data['api_token'])
                        ? $data['api_token'] : null;
                }
            }
        }

        $needsRegister = ($this->instanceUuid === null || $this->instanceUuid === '')
            || ($this->apiToken === null || $this->apiToken === '');
        if ($needsRegister) {
            $this->registerInstance();
        }
    }

    private function registerInstance(): void
    {
        // Keep a stable UUID. Never mint a new one if we already have one
        // (revoked installs must not silently re-enrol).
        if ($this->instanceUuid === null || $this->instanceUuid === '') {
            $this->instanceUuid = $this->generateUuidV4();
            $this->saveState();
        }

        $siteUrl = isset($_SERVER['HTTP_HOST']) && is_string($_SERVER['HTTP_HOST'])
            ? strtolower($_SERVER['HTTP_HOST']) : 'unknown-host';

        $payload = [
            'action' => 'register',
            'instance_uuid' => $this->instanceUuid,
            'site_url' => $siteUrl,
            'register_key' => $this->registerKey(),
        ];

        $result = $this->rawPost(json_encode($payload, JSON_THROW_ON_ERROR), false);
        if (isset($result['error']) && is_string($result['error'])) {
            $err = strtolower($result['error']);
            if (str_contains($err, 'deactivated') || str_contains($err, 'already registered')) {
                // Persist UUID without a token so we do not try a new identity.
                $this->apiToken = null;
                $this->saveState();
                return;
            }
        }
        if (isset($result['api_token']) && is_string($result['api_token']) && $result['api_token'] !== '') {
            $this->apiToken = $result['api_token'];
            $this->saveState();
        }
    }

    private function saveState(): void
    {
        $data = [
            'instance_uuid' => $this->instanceUuid,
            'api_token' => $this->apiToken,
        ];
        @file_put_contents(
            $this->stateFile,
            json_encode($data, JSON_PRETTY_PRINT | JSON_UNESCAPED_SLASHES),
            LOCK_EX
        );
    }

    /**
     * @return list<array<string, mixed>>
     */
    public function searchExistingIssues(string $keywords): array
    {
        $response = $this->sendRequest([
            'action' => 'search',
            'query' => $keywords,
        ]);
        return is_array($response) && array_is_list($response) ? $response
            : (isset($response['items']) && is_array($response['items']) ? $response['items'] : []);
    }

    /**
     * @param list<string> $labels
     * @return array<string, mixed>
     */
    public function createNewIssue(string $title, string $description, array $labels): array
    {
        return $this->sendRequest([
            'action' => 'create_issue',
            'title' => $title,
            'body' => $description,
            'labels' => array_values($labels),
        ]);
    }

    /**
     * @return array<string, mixed>
     */
    public function commentOnIssue(int $issueNumber, string $comment): array
    {
        return $this->sendRequest([
            'action' => 'comment_issue',
            'issue_number' => $issueNumber,
            'comment' => $comment,
        ]);
    }

    /**
     * Strip personal / sensitive patterns from audit detail text before public publish.
     */
    public function sanitizeLogDetails(string $details): string
    {
        $details = preg_replace('/\b(?:\d{1,3}\.){3}\d{1,3}\b/', '[REDACTED_IP]', $details) ?? $details;
        $details = preg_replace(
            '/\b(?:[A-F0-9]{1,4}:){7}[A-F0-9]{1,4}\b/i',
            '[REDACTED_IP]',
            $details
        ) ?? $details;
        $details = preg_replace(
            '/[a-zA-Z0-9._%+-]+@[a-zA-Z0-9.-]+\.[a-zA-Z]{2,}/',
            '[REDACTED_EMAIL]',
            $details
        ) ?? $details;
        $details = preg_replace(
            '/\b(?:\+\d{1,3}\s?)?(?:\(\d{3}\)|\d{3})[-.\s]?\d{3}[-.\s]?\d{4}\b/',
            '[REDACTED_PHONE]',
            $details
        ) ?? $details;
        $details = preg_replace(
            '/\b(password_hash|passwd|api[_-]?key|secret|token|authorization)\b\s*[:=]\s*\S+/i',
            '$1: [REDACTED]',
            $details
        ) ?? $details;
        $details = preg_replace(
            '/-----BEGIN [A-Z ]+-----[\s\S]*?-----END [A-Z ]+-----/',
            '[REDACTED_PEM]',
            $details
        ) ?? $details;
        $details = preg_replace(
            '/\b(?:mysql|postgres|mongodb):\/\/\S+/i',
            '[REDACTED_DSN]',
            $details
        ) ?? $details;

        return $details;
    }

    /**
     * Heuristic: body may still contain secrets (warn admin, do not hard-block).
     */
    public function bodyLooksSensitive(string $body): bool
    {
        $patterns = [
            '/password_hash/i',
            '/-----BEGIN [A-Z ]+-----/',
            '/\b(?:mysql|postgres|mongodb):\/\//i',
            '/\bapi[_-]?key\b\s*[:=]/i',
            '/\bsecret\b\s*[:=]/i',
            '/\bBearer\s+[A-Za-z0-9\-._~+\/]+=*/i',
        ];
        foreach ($patterns as $re) {
            if (preg_match($re, $body) === 1) {
                return true;
            }
        }
        return false;
    }

    /**
     * @param array<string, mixed> $payload
     * @return array<string, mixed>
     */
    private function sendRequest(array $payload): array
    {
        if ($this->apiToken === null || $this->apiToken === '') {
            return ['error' => 'Instance failed to register with the feedback gateway.'];
        }

        try {
            $jsonPayload = json_encode($payload, JSON_THROW_ON_ERROR);
        } catch (\JsonException $e) {
            return ['error' => 'Could not encode feedback payload.'];
        }

        $timestamp = (string) time();
        // Bind time to signature so proxies can reject stale replays
        $signature = hash_hmac('sha256', $timestamp . '.' . $jsonPayload, $this->apiToken);

        return $this->rawPost($jsonPayload, true, [
            'X-PRD-Token: ' . $this->apiToken,
            'X-PRD-Signature: ' . $signature,
            'X-PRD-Timestamp: ' . $timestamp,
        ]);
    }

    /**
     * @param list<string> $extraHeaders
     * @return array<string, mixed>
     */
    private function rawPost(string $jsonPayload, bool $authenticated, array $extraHeaders = []): array
    {
        if (!function_exists('curl_init')) {
            return ['error' => 'cURL is required for the feedback gateway.'];
        }

        $headers = array_merge(['Content-Type: application/json'], $extraHeaders);
        $ch = curl_init($this->proxyUrl);
        if ($ch === false) {
            return ['error' => 'Could not contact the feedback gateway.'];
        }

        curl_setopt_array($ch, [
            CURLOPT_RETURNTRANSFER => true,
            CURLOPT_POST => true,
            CURLOPT_POSTFIELDS => $jsonPayload,
            CURLOPT_HTTPHEADER => $headers,
            CURLOPT_CONNECTTIMEOUT => 8,
            CURLOPT_TIMEOUT => 20,
            CURLOPT_PROTOCOLS => CURLPROTO_HTTPS,
            CURLOPT_REDIR_PROTOCOLS => CURLPROTO_HTTPS,
        ]);

        $response = curl_exec($ch);
        $errno = curl_errno($ch);
        $httpCode = (int) curl_getinfo($ch, CURLINFO_HTTP_CODE);
        curl_close($ch);

        if ($errno !== 0 || !is_string($response)) {
            return ['error' => 'Could not reach the feedback gateway. Please try again later.'];
        }

        $decoded = json_decode($response, true);
        if (!is_array($decoded)) {
            return ['error' => 'Unexpected response from the feedback gateway.'];
        }

        if ($httpCode >= 400 && !isset($decoded['error'])) {
            $decoded['error'] = 'Feedback gateway returned HTTP ' . $httpCode;
        }

        return $decoded;
    }

    private function generateUuidV4(): string
    {
        $data = random_bytes(16);
        $data[6] = chr((ord($data[6]) & 0x0f) | 0x40);
        $data[8] = chr((ord($data[8]) & 0x3f) | 0x80);

        return vsprintf('%s%s-%s-%s-%s-%s%s%s', str_split(bin2hex($data), 4));
    }
}
