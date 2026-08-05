<?php
declare(strict_types=1);
/**
 * MIGRATED FILE MAPPING
 * ---------------------
 * Original Old File: includes/security_engine.php
 * Migrated Date: 2026-08-04 18:00:00
 */

/**
 * Executes a threat firewall check (Rate limiting, bad User-Agents, spam content analysis)
 *
 * @param PDO $pdo Database connection
 * @return true|string True on success, or an error string on block
 */
function run_form_firewall_check(PDO $pdo)
{
    $ip = isset($_SERVER['REMOTE_ADDR']) && is_string($_SERVER['REMOTE_ADDR']) ? $_SERVER['REMOTE_ADDR'] : '0.0.0.0';
    $userAgent = isset($_SERVER['HTTP_USER_AGENT']) && is_string($_SERVER['HTTP_USER_AGENT']) ? trim($_SERVER['HTTP_USER_AGENT']) : '';

    // 1. Block empty or extremely suspicious User-Agents
    if ($userAgent === '' || strlen($userAgent) < 5) {
        error_log("Firewall blocked request: Empty or invalid User-Agent from IP {$ip}");
        return __('security_engine.err_suspicious_agent');
    }

    // 2. Check for common malicious scraper bots in User-Agent string
    $blockedBots = ['scanner', 'nikto', 'sqlmap', 'crawler/bot-bad', 'masscan'];
    foreach ($blockedBots as $bot) {
        if (stripos($userAgent, $bot) !== false) {
            error_log("Firewall blocked malicious bot signature '{$bot}' from IP {$ip}");
            return __('security_engine.err_access_denied');
        }
    }

    // 3. Simple Rolling IP Rate Limiting Check (e.g., max 15 submissions per IP in the last 10 minutes)
    try {
        $stmt = $pdo->prepare("SELECT COUNT(*) FROM audit_logs WHERE ip_address = ? AND created_at >= (NOW() - INTERVAL 10 MINUTE)");
        $stmt->execute([$ip]);
        $count = $stmt->fetchColumn();
        if ($count !== false && (int)$count > 15) {
            return __('security_engine.err_rate_limit');
        }
    } catch (Exception $e) {
        // Fail open if audit_logs table isn't present
    }

    // 4. Content / Payload Spam Inspection (Check text fields for blatant URL spam patterns)
    foreach ($_POST as $key => $val) {
        if (is_string($val)) {
            $matches = [];
            if (preg_match_all('/https?:\/\/[^\s]+/i', $val, $matches) > 4) {
                return __('security_engine.err_excessive_links');
            }
        }
    }

    return true; // Passed firewall inspection
}

/**
 * Multi-provider CAPTCHA verification helper
 *
 * @param PDO $pdo Database connection
 * @return true|string True on success/disabled, or an error string on failure
 */
function verify_form_captcha(PDO $pdo)
{
    $provider = get_setting($pdo, 'captcha_provider', 'none');
    if ($provider === 'none' || $provider === '') {
        return true; // CAPTCHA disabled
    }

    $secretKey = '';
    $tokenParamName = '';
    $verifyUrl = '';

    switch ($provider) {
        case 'turnstile':
            $secretKey = get_setting($pdo, 'turnstile_secret_key', '');
            $tokenParamName = 'cf-turnstile-response';
            $verifyUrl = 'https://challenges.cloudflare.com/turnstile/v0/siteverify';
            break;
        case 'recaptcha':
            $secretKey = get_setting($pdo, 'recaptcha_secret_key', '');
            $tokenParamName = 'g-recaptcha-response';
            $verifyUrl = 'https://www.google.com/recaptcha/api/siteverify';
            break;
        case 'hcaptcha':
            $secretKey = get_setting($pdo, 'hcaptcha_secret_key', '');
            $tokenParamName = 'h-captcha-response';
            $verifyUrl = 'https://hcaptcha.com/siteverify';
            break;
        default:
            return true;
    }

    if ($secretKey === '') {
        return true; // Fail open if secret key is missing
    }

    $submittedToken = isset($_POST[$tokenParamName]) && is_string($_POST[$tokenParamName]) ? $_POST[$tokenParamName] : '';
    if ($submittedToken === '') {
        return __('security_engine.err_complete_captcha');
    }

    $remoteIp = isset($_SERVER['REMOTE_ADDR']) && is_string($_SERVER['REMOTE_ADDR']) ? $_SERVER['REMOTE_ADDR'] : '';
    $data = [
        'secret'   => $secretKey,
        'response' => $submittedToken,
        'remoteip' => $remoteIp
    ];

    $options = [
        'http' => [
            'header'  => "Content-type: application/x-www-form-urlencoded\r\n",
            'method'  => 'POST',
            'content' => http_build_query($data),
            'timeout' => 5
        ]
    ];

    $context = stream_context_create($options);
    $result = @file_get_contents($verifyUrl, false, $context);

    if ($result === false) {
        return true; // Fail open if CAPTCHA server is unreachable
    }

    /** @var mixed $responseData */
    $responseData = json_decode($result, true);
    if (!is_array($responseData) || empty($responseData['success'])) {
        return __('security_engine.err_captcha_failed');
    }

    return true;
}

/**
 * Renders the correct client-side CAPTCHA widget and script based on site settings.
 *
 * @param PDO $pdo Database connection
 * @return string HTML snippet for the CAPTCHA widget
 */
function render_form_captcha_widget(PDO $pdo): string
{
    $provider = get_setting($pdo, 'captcha_provider', 'none');
    if ($provider === 'none' || $provider === '') {
        return ''; // CAPTCHA disabled
    }

    $siteKey = '';
    $widgetHtml = '';

    switch ($provider) {
        case 'turnstile':
            $siteKey = get_setting($pdo, 'turnstile_site_key', '');
            if ($siteKey !== '') {
                $widgetHtml = '<script src="https://challenges.cloudflare.com/turnstile/v0/api.js" async defer></script>'
                           . '<div class="cf-turnstile" data-sitekey="' . htmlspecialchars($siteKey, ENT_QUOTES, 'UTF-8') . '" style="margin-bottom: 1rem;"></div>';
            }
            break;

        case 'recaptcha':
            $siteKey = get_setting($pdo, 'recaptcha_site_key', '');
            if ($siteKey !== '') {
                $widgetHtml = '<script src="https://www.google.com/recaptcha/api.js" async defer></script>'
                           . '<div class="g-recaptcha" data-sitekey="' . htmlspecialchars($siteKey, ENT_QUOTES, 'UTF-8') . '" style="margin-bottom: 1rem;"></div>';
            }
            break;

        case 'hcaptcha':
            $siteKey = get_setting($pdo, 'hcaptcha_site_key', '');
            if ($siteKey !== '') {
                $widgetHtml = '<script src="https://js.hcaptcha.com/1/api.js" async defer></script>'
                           . '<div class="h-captcha" data-sitekey="' . htmlspecialchars($siteKey, ENT_QUOTES, 'UTF-8') . '" style="margin-bottom: 1rem;"></div>';
            }
            break;
    }

    return $widgetHtml;
}
