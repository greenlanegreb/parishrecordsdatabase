<?php
declare(strict_types=1);

namespace App\Controllers;

use App\Services\AppearanceService;
use PDO;

class AdminAppearanceController
{
    public function __construct(private PDO $pdo)
    {
    }

    public function save(): void
    {
        if (($_SERVER['REQUEST_METHOD'] ?? '') !== 'POST') {
            http_response_code(405);
            exit('Method Not Allowed');
        }
        verify_csrf_token();
        require_permission($this->pdo, 'manage_settings', 'Manage global site settings');
        $svc = new AppearanceService($this->pdo);
        $base = defined('BASE_PATH') ? rtrim((string) BASE_PATH, '/') : '';
        if (isset($_POST['reset_appearance'])) {
            $svc->reset();
            $_SESSION['message'] = function_exists('__') && __('appearance.reset_ok') !== 'appearance.reset_ok'
                ? __('appearance.reset_ok')
                : 'Appearance reset to the default look.';
            header('Location: ' . $base . '/admin/settings?tab=appearance');
            exit;
        }
        $file = isset($_FILES['logo']) && is_array($_FILES['logo']) ? $_FILES['logo'] : null;
        $svc->save($_POST, $file);
        $saved = function_exists('__') && __('appearance.saved') !== 'appearance.saved'
            ? __('appearance.saved')
            : 'Appearance saved.';
        $ajax = isset($_SERVER['HTTP_X_REQUESTED_WITH'])
            && strtolower((string) $_SERVER['HTTP_X_REQUESTED_WITH']) === 'xmlhttprequest';
        if ($ajax) {
            header('Content-Type: application/json; charset=utf-8');
            echo json_encode(['ok' => true, 'message' => $saved]);
            exit;
        }
        $_SESSION['message'] = $saved;
        header('Location: ' . $base . '/admin/settings?tab=appearance');
        exit;
    }

    public function logo(): void
    {
        $svc = new AppearanceService($this->pdo);
        $path = $svc->logoPath();
        if ($path === null) {
            http_response_code(404);
            exit;
        }
        header('Content-Type: ' . $svc->logoMime($path));
        header('Cache-Control: no-store, max-age=0');
        readfile($path);
        exit;
    }
}
