<?php
declare(strict_types=1);

namespace App\Controllers;

use App\Services\LocationValueService;
use PDO;

class TableMapController
{
    public function __construct(private PDO $pdo)
    {
    }

    public function show(string $id = ''): void
    {
        $tableId = (int) $id;
        if ($tableId < 1 && isset($_GET['table_id'])) {
            $tableId = (int) $_GET['table_id'];
        }
        if (!is_module_enabled($this->pdo, 'maps') || $tableId < 1) {
            require_once dirname(__DIR__, 2) . '/public/403.php';
            return;
        }
        $currentUser = function_exists('get_current_user_data') ? get_current_user_data($this->pdo) : null;
        if (!user_can_view_table($this->pdo, $tableId, $currentUser)) {
            require_once dirname(__DIR__, 2) . '/public/403.php';
            return;
        }
        if (!LocationValueService::tableHasLocationColumn($this->pdo, $tableId)) {
            require_once dirname(__DIR__, 2) . '/public/404.php';
            return;
        }
        $tStmt = $this->pdo->prepare('SELECT id, table_name FROM dynamic_tables WHERE id = ?');
        $tStmt->execute([$tableId]);
        $table = $tStmt->fetch(PDO::FETCH_ASSOC);
        if ($table === false) {
            require_once dirname(__DIR__, 2) . '/public/404.php';
            return;
        }
        $colStmt = $this->pdo->prepare('SELECT id, column_name, data_type FROM table_columns WHERE table_id = ? ORDER BY id ASC');
        $colStmt->execute([$tableId]);
        $columns = $colStmt->fetchAll(PDO::FETCH_ASSOC) ?: [];
        $tileUrl = '';
        try {
            $s = $this->pdo->prepare('SELECT setting_value FROM site_settings WHERE setting_key = ?');
            $s->execute(['map_tile_url']);
            $tileUrl = (string) $s->fetchColumn();
        } catch (\Throwable $e) {
            $tileUrl = '';
        }
        $basePath = defined('BASE_PATH') ? rtrim((string) BASE_PATH, '/') : '';
        $activeTableId = $tableId;
        $currentUser = $currentUser ?? ['date_format' => null];
        require_once dirname(__DIR__) . '/Views/map/table.php';
    }
}
