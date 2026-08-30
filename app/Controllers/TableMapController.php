<?php
declare(strict_types=1);

namespace App\Controllers;

use App\Services\LocationValueService;
use App\Services\MapConfigService;
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
        $isAdmin = function_exists('is_admin') && is_admin($this->pdo);
        if (!$isAdmin && !user_can_view_table($this->pdo, $tableId, $currentUser)) {
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
        $colStmt = $this->pdo->prepare(
            'SELECT id, column_name, data_type, field_options, allow_multiple, boolean_display_format
             FROM table_columns WHERE table_id = ? ORDER BY id ASC'
        );
        $colStmt->execute([$tableId]);
        $columns = $colStmt->fetchAll(PDO::FETCH_ASSOC) ?: [];

        $tilesCfg = MapConfigService::tiles($this->pdo);
        $tileUrl = $tilesCfg['url'];
        $tileAttribution = $tilesCfg['attribution'];

        $siteDateFormat = function_exists('get_setting')
            ? (string) get_setting($this->pdo, 'default_date_format', 'd/m/Y')
            : 'd/m/Y';
        if ($siteDateFormat === '') {
            $siteDateFormat = 'd/m/Y';
        }
        $userDateFormat = $siteDateFormat;
        if (is_array($currentUser)
            && isset($currentUser['date_format'])
            && is_string($currentUser['date_format'])
            && $currentUser['date_format'] !== ''
        ) {
            $userDateFormat = $currentUser['date_format'];
        }
        $datePlaceholder = function_exists('get_date_placeholder')
            ? get_date_placeholder($userDateFormat)
            : 'YYYY-MM-DD';

        $basePath = defined('BASE_PATH') ? rtrim((string) BASE_PATH, '/') : '';
        $activeTableId = $tableId;
        require_once dirname(__DIR__) . '/Views/map/table.php';
    }
}
