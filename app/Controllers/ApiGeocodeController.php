<?php
declare(strict_types=1);

namespace App\Controllers;

use App\Services\GeocodeService;
use PDO;

class ApiGeocodeController
{
    public function __construct(private PDO $pdo)
    {
    }

    public function search(): void
    {
        header('Content-Type: application/json; charset=utf-8');
        if (!is_module_enabled($this->pdo, 'maps')) {
            http_response_code(403);
            echo json_encode(['error' => 'off', 'results' => []]);
            return;
        }
        if (session_status() === PHP_SESSION_NONE) {
            session_start();
        }
        $q = isset($_GET['q']) && is_string($_GET['q']) ? trim($_GET['q']) : '';
        $svc = new GeocodeService($this->pdo);
        $results = $svc->search($q);
        echo json_encode(['results' => $results], JSON_UNESCAPED_UNICODE);
    }
}
