<?php
/**
 * MIGRATED FILE MAPPING
 * ---------------------
 * Original Old File: user/onboarding.php/user/actions/save_onboarding.php
 * Migrated Date: 2026-08-05 05:05:06
 *
 * Shared personal-details helpers; language apply keeps form draft; names required.
 */
declare(strict_types=1);

namespace App\Controllers;

use PDO;

class UserOnboardingActionController
{
    private PDO $pdo;

    public function __construct(PDO $pdo)
    {
        $this->pdo = $pdo;
    }

    public function save(): void
    {
        $serverMethod = isset($_SERVER['REQUEST_METHOD']) && is_string($_SERVER['REQUEST_METHOD'])
            ? $_SERVER['REQUEST_METHOD'] : 'GET';
        if ($serverMethod !== 'POST') {
            http_response_code(405);
            exit('Method Not Allowed');
        }

        \verify_csrf_token();

        $basePath = defined('BASE_PATH') ? rtrim((string) BASE_PATH, '/') : '';

        if (!isset($_SESSION['user_id'])) {
            header('Location: ' . $basePath . '/login');
            exit;
        }

        /** @var array{id: int|string, username: string}|null $currentUser */
        $currentUser = \get_current_user_data($this->pdo);
        if ($currentUser === null) {
            header('Location: ' . $basePath . '/login');
            exit;
        }

        require_once dirname(__DIR__, 2) . '/includes/user_preferences.php';

        $normalized = \user_normalize_personal_details($_POST);
        $applyLangOnly = isset($_POST['apply_language']) && (string) $_POST['apply_language'] === '1';

        // Language switch: keep typed values, switch UI language, stay on onboarding
        if ($applyLangOnly) {
            \user_store_personal_draft('onboarding_draft', $normalized);
            \user_apply_ui_language($normalized['language'], $this->pdo);
            header('Location: ' . $basePath . '/user/onboarding');
            exit;
        }

        $err = \user_validate_personal_details_required($normalized);
        if ($err !== '') {
            \user_store_personal_draft('onboarding_draft', $normalized);
            $_SESSION['error'] = $err;
            header('Location: ' . $basePath . '/user/onboarding');
            exit;
        }

        $userId = $currentUser['id'];
        if (!\user_save_personal_details($this->pdo, $userId, $normalized, true)) {
            \user_store_personal_draft('onboarding_draft', $normalized);
            $_SESSION['error'] = function_exists('__')
                ? __('onboarding.err_save_failed')
                : 'Failed to save onboarding preferences. Please try again.';
            header('Location: ' . $basePath . '/user/onboarding');
            exit;
        }

        unset($_SESSION['onboarding_draft']);
        \user_apply_ui_language($normalized['language'], $this->pdo);

        $_SESSION['message'] = function_exists('__')
            ? __('onboarding.msg_welcome')
            : 'Welcome aboard! Your preferences have been saved.';

        // after_save: profile (2FA) or skip to app
        $after = isset($_POST['after_save']) && is_string($_POST['after_save'])
            ? $_POST['after_save'] : 'continue';
        if ($after === 'profile' && \function_exists('has_permission') && \has_permission($this->pdo, 'access_profile')) {
            header('Location: ' . $basePath . '/profile');
        } else {
            header('Location: ' . $basePath . '/data-entry');
        }
        exit;
    }
}
