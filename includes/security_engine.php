<?php
// includes/security_engine.php - CAPTCHA verification, rendering, and threat defense firewall

/**
 * Executes a threat firewall check (Rate limiting, bad User-Agents, spam content analysis)
 */
function run_form_firewall_check(PDO $pdo) {
    $ip = $_SERVER['REMOTE_ADDR'] ?? '0.0.0.0';
    $user_agent = trim($_SERVER['HTTP_USER_AGENT'] ?? '');

    // 1. Block empty or extremely suspicious User-Agents
    if (empty($user_agent) || strlen($user_agent) < 5) {
        error_log("Firewall blocked request: Empty or invalid User-Agent from IP {$ip}");
        return __('security_engine.err_suspicious_agent');
    }

    // 2. Check for common malicious scraper bots in User-Agent string
    $blocked_bots = ['scanner', 'nikto', 'sqlmap', 'crawler/bot-bad', 'masscan'];
    foreach ($blocked_bots as $bot) {
        if (stripos($user_agent, $bot) !== false) {
            error_log("Firewall blocked malicious bot signature '{$bot}' from IP {$ip}");
            return __('security_engine.err_access_denied');
        }
    }

    // 3. Simple Rolling IP Rate Limiting Check (e.g., max 15 submissions per IP in the last 10 minutes)
    try {
        $stmt = $pdo->prepare("SELECT COUNT(*) FROM audit_logs WHERE ip_address = ? AND created_at >= (NOW() - INTERVAL 10 MINUTE)");
        $stmt->execute([$ip]);
        if ($stmt->fetchColumn() > 15) {
            return __('security_engine.err_rate_limit');
        }
    } catch (Exception $e) {
        // Fail open if audit_logs table isn't present
    }

    // 4. Content / Payload Spam Inspection (Check text fields for blatant URL spam patterns)
    foreach ($_POST as $key => $val) {
        if (is_string($val)) {
            if (preg_match_all('/https?:\/\/[^\s]+/i', $val, $matches) > 4) {
                return __('security_engine.err_excessive_links');
            }
        }
    }

    return true; // Passed firewall inspection
}

/**
 * Multi-provider CAPTCHA verification helper
 */
function verify_form_captcha(PDO $pdo) {
    $provider = get_setting($pdo, 'captcha_provider', 'none');
    if ($provider === 'none' || empty($provider)) {
        return true; // CAPTCHA disabled
    }

    $secret_key = '';
    $token_param_name = '';
    $verify_url = '';

    switch ($provider) {
        case 'turnstile':
            $secret_key = get_setting($pdo, 'turnstile_secret_key', '');
            $token_param_name = 'cf-turnstile-response';
            $verify_url = 'https://challenges.cloudflare.com/turnstile/v0/siteverify';
            break;
        case 'recaptcha':
            $secret_key = get_setting($pdo, 'recaptcha_secret_key', '');
            $token_param_name = 'g-recaptcha-response';
            $verify_url = 'https://www.google.com/recaptcha/api/siteverify';
            break;
        case 'hcaptcha':
            $secret_key = get_setting($pdo, 'hcaptcha_secret_key', '');
            $token_param_name = 'h-captcha-response';
            $verify_url = 'https://hcaptcha.com/siteverify';
            break;
        default:
            return true;
    }

    if (empty($secret_key)) {
        return true; // Fail open if secret key is missing
    }

    $submitted_token = $_POST[$token_param_name] ?? '';
    if (empty($submitted_token)) {
        return __('security_engine.err_complete_captcha');
    }

    $data = [
        'secret'   => $secret_key,
        'response' => $submitted_token,
        'remoteip' => $_SERVER['REMOTE_ADDR'] ?? ''
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
    $result = @file_get_contents($verify_url, false, $context);

    if ($result === false) {
        return true; // Fail open if CAPTCHA server is unreachable
    }

    $response_data = json_decode($result, true);
    if (empty($response_data['success'])) {
        return __('security_engine.err_captcha_failed');
    }

    return true;
}

/**
 * Renders the correct client-side CAPTCHA widget and script based on site settings.
 */
function render_form_captcha_widget(PDO $pdo) {
    $provider = get_setting($pdo, 'captcha_provider', 'none');
    if ($provider === 'none' || empty($provider)) {
        return ''; // CAPTCHA disabled
    }

    $site_key = '';
    $widget_html = '';

    switch ($provider) {
        case 'turnstile':
            $site_key = get_setting($pdo, 'turnstile_site_key', '');
            if (!empty($site_key)) {
                $widget_html = '<script src="https://challenges.cloudflare.com/turnstile/v0/api.js" async defer></script>'
                           . '<div class="cf-turnstile" data-sitekey="' . htmlspecialchars($site_key) . '" style="margin-bottom: 1rem;"></div>';
            }
            break;

        case 'recaptcha':
            $site_key = get_setting($pdo, 'recaptcha_site_key', '');
            if (!empty($site_key)) {
                $widget_html = '<script src="https://www.google.com/recaptcha/api.js" async defer></script>'
                           . '<div class="g-recaptcha" data-sitekey="' . htmlspecialchars($site_key) . '" style="margin-bottom: 1rem;"></div>';
            }
            break;

        case 'hcaptcha':
            $site_key = get_setting($pdo, 'hcaptcha_site_key', '');
            if (!empty($site_key)) {
                $widget_html = '<script src="https://js.hcaptcha.com/1/api.js" async defer></script>'
                           . '<div class="h-captcha" data-sitekey="' . htmlspecialchars($site_key) . '" style="margin-bottom: 1rem;"></div>';
            }
            break;
    }

    return $widget_html;
}
