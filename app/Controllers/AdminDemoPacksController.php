<?php
declare(strict_types=1);

namespace App\Controllers;

use App\Services\DemoPackService;
use Exception;
use PDO;

class AdminDemoPacksController
{
    public function __construct(private PDO $pdo)
    {
    }

    public function index(): void
    {
        $currentUser = $this->requireAccess();
        $basePath = defined('BASE_PATH') ? rtrim((string) BASE_PATH, '/') : '';
        $service = new DemoPackService($this->pdo);
        $packs = [];
        $error = isset($_SESSION['error']) && is_string($_SESSION['error']) ? $_SESSION['error'] : '';
        $message = isset($_SESSION['message']) && is_string($_SESSION['message']) ? $_SESSION['message'] : '';
        unset($_SESSION['error'], $_SESSION['message']);
        try {
            $packs = $service->listPacks();
        } catch (Exception $e) {
            $error = $e->getMessage();
        }
        require_once __DIR__ . '/../Views/admin/demo_packs.php';
    }

    public function handle(): void
    {
        $currentUser = $this->requireAccess();
        verify_csrf_token();
        $basePath = defined('BASE_PATH') ? rtrim((string) BASE_PATH, '/') : '';
        $action = isset($_POST['demo_action']) && is_string($_POST['demo_action']) ? $_POST['demo_action'] : '';
        $service = new DemoPackService($this->pdo);

        try {
            if ($action === 'install') {
                $slugs = isset($_POST['packs']) && is_array($_POST['packs']) ? $_POST['packs'] : [];
                $clean = [];
                foreach ($slugs as $s) {
                    if (is_string($s) && $s !== '') {
                        $clean[] = $s;
                    }
                }
                $withData = isset($_POST['with_data']) && (string) $_POST['with_data'] === '1';
                if ($clean === []) {
                    throw new Exception('Choose at least one pack.');
                }
                $service->installPacks($clean, $withData, $currentUser);
                $_SESSION['message'] = $withData
                    ? 'Demo tables, columns, and sample rows have been added.'
                    : 'Demo tables and columns have been added (no sample rows).';
            } elseif ($action === 'remove_data') {
                $slug = isset($_POST['pack_slug']) && is_string($_POST['pack_slug']) ? $_POST['pack_slug'] : '';
                $service->removeDemoData($slug);
                $_SESSION['message'] = 'Demo sample rows were removed. Tables and columns were left in place.';
            } elseif ($action === 'remove_pack') {
                $slug = isset($_POST['pack_slug']) && is_string($_POST['pack_slug']) ? $_POST['pack_slug'] : '';
                $service->removePack($slug, $currentUser);
                $_SESSION['message'] = 'The demo pack (tables, columns, sample rows, and any extra rows in those tables) was removed.';
            } else {
                throw new Exception('Unknown action.');
            }
        } catch (Exception $e) {
            $_SESSION['error'] = $e->getMessage();
        }

        header('Location: ' . $basePath . '/admin#tab-demo');
        exit;
    }

    /**
     * @return array{id: int|string, username?: string}
     */
    private function requireAccess(): array
    {
        if (function_exists('has_permission') && has_permission($this->pdo, 'manage_columns')) {
            return require_permission(
                $this->pdo,
                'manage_columns',
                'Install or remove demo tables'
            );
        }
        return require_permission(
            $this->pdo,
            'manage_settings',
            'Install or remove demo tables'
        );
    }
}
