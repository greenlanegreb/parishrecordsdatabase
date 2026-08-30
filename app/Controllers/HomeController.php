<?php
/**
 * MIGRATED FILE MAPPING
 * ---------------------
 * Original Old File: roote/index.php
 * Migrated Date: 2026-08-05 06:39:40
 */
declare(strict_types=1);

namespace App\Controllers;

use PDO;

class HomeController
{
    private PDO $pdo;

    public function __construct(PDO $pdo)
    {
        $this->pdo = $pdo;
    }

    public function index(): void
    {

        // Only send people to the installer when nothing is configured yet
        if (!is_file(__DIR__ . '/../../db/db.php') && !is_file(__DIR__ . '/../../config.local.php')) {
            $basePath = defined('BASE_PATH') && is_string(BASE_PATH) ? rtrim(BASE_PATH, '/') : '';
            header('Location: ' . $basePath . '/install/');
            exit;
        }

        // ------------------------------------------------------------------
        // Permission gate: not-logged-in visitors need view_as_guest
        // ------------------------------------------------------------------
        /** @var array{id: int|string, date_format?: string, timezone?: string, time_format?: string}|null $currentUser */
        $currentUser = function_exists('get_current_user_data') ? get_current_user_data($this->pdo) : null;
        $basePath = defined('BASE_PATH') && is_string(BASE_PATH) ? rtrim(BASE_PATH, '/') : '';

        if ($currentUser === null && !guest_has_permission($this->pdo, 'view_as_guest')) {
            header('Location: ' . $basePath . '/login');
            exit;
        }

        // ------------------------------------------------------------------
        // Check table and column existence for intelligent user guidance
        // ------------------------------------------------------------------
        $tablesCountStmt = $this->pdo->query("SELECT COUNT(*) FROM dynamic_tables");
        $totalTablesCount = $tablesCountStmt !== false ? (int)$tablesCountStmt->fetchColumn() : 0;

        $totalColumnsCount = 0;
        if ($totalTablesCount > 0) {
            $colsCountStmt = $this->pdo->query("SELECT COUNT(*) FROM table_columns");
            $totalColumnsCount = $colsCountStmt !== false ? (int)$colsCountStmt->fetchColumn() : 0;
        }

        // ------------------------------------------------------------------
        // Tables the visitor is allowed to see
        // ------------------------------------------------------------------
        $tablesStmt = $this->pdo->query("SELECT id, table_name FROM dynamic_tables ORDER BY id ASC");
        /** @var array<int, array<string, mixed>> $allTables */
        $allTables = $tablesStmt !== false ? $tablesStmt->fetchAll(PDO::FETCH_ASSOC) : [];
        $availableTables = [];
        foreach ($allTables as $t) {
            $tId = isset($t['id']) ? (int)$t['id'] : 0;
            if (user_can_view_table($this->pdo, $tId, $currentUser)) {
                $availableTables[] = $t;
            }
        }

        $queryGet = $_GET;
        $activeTableId = isset($queryGet['table_id'])
            ? (int)$queryGet['table_id']
            : (!empty($availableTables) ? (int)$availableTables[0]['id'] : 0);

        // Tables exist but none are visible to this visitor (typical guest with no view_table_N):
        // show the home empty state instead of 403 (which loops with "return home").
        if ($activeTableId > 0 && !user_can_view_table($this->pdo, $activeTableId, $currentUser)) {
            $activeTableId = !empty($availableTables) ? (int) $availableTables[0]['id'] : 0;
        }

        // ------------------------------------------------------------------
        // User display preferences (logged-in profile; guest = site/default UK)
        // ------------------------------------------------------------------
        $siteDateFormat = function_exists('get_setting')
            ? get_setting($this->pdo, 'default_date_format', 'd/m/Y')
            : 'd/m/Y';
        if ($siteDateFormat === '') {
            $siteDateFormat = 'd/m/Y';
        }

        if ($currentUser !== null) {
            $userDateFormat = (
                isset($currentUser['date_format']) && is_string($currentUser['date_format']) && $currentUser['date_format'] !== ''
            ) ? $currentUser['date_format'] : $siteDateFormat;

            $userTimezone = (
                isset($currentUser['timezone']) && is_string($currentUser['timezone']) && $currentUser['timezone'] !== ''
            ) ? $currentUser['timezone'] : 'UTC';

            $fullFormatStr = function_exists('get_user_datetime_format')
                ? get_user_datetime_format($currentUser)
                : ($userDateFormat . ' H:i');
        } else {
            $userDateFormat = $siteDateFormat;
            $userTimezone = 'UTC';
            $fullFormatStr = $userDateFormat . ' H:i';
        }

        $datePlaceholder = function_exists('get_date_placeholder')
            ? get_date_placeholder($userDateFormat)
            : 'DD/MM/YYYY (e.g. 25/05/1955)';


        // ------------------------------------------------------------------
        // Columns for the active table
        // ------------------------------------------------------------------
        /** @var array<int, array<string, mixed>> $columns */
        $columns = [];
        if ($totalTablesCount > 0 && $totalColumnsCount > 0 && $activeTableId > 0) {
            $colsStmt = $this->pdo->prepare(
                "SELECT * FROM table_columns WHERE table_id = ? ORDER BY sort_order ASC, column_name ASC"
            );
            $colsStmt->execute([$activeTableId]);
            $columns = $colsStmt->fetchAll(PDO::FETCH_ASSOC);
            $visHelper = dirname(__DIR__, 2) . '/includes/column_visibility.php';
            if (is_file($visHelper)) {
                require_once $visHelper;
            }
            $visibleColumns = function_exists('resolve_visible_columns')
                ? resolve_visible_columns($columns, $activeTableId)
                : $columns;
        }

        $systemName = get_system_name($this->pdo);

        $message = isset($_SESSION['message']) && is_string($_SESSION['message']) ? $_SESSION['message'] : '';
        $error = isset($_SESSION['error']) && is_string($_SESSION['error']) ? $_SESSION['error'] : '';
        unset($_SESSION['message'], $_SESSION['error']);

        $suggestReturnUrl = $basePath !== '' ? $basePath . '/' : '/';
        $suggestTableId = $activeTableId;
        $isAdmin = $currentUser !== null && is_admin($this->pdo);

        // Pass variables to View
        $tableHasMap = is_module_enabled($this->pdo, 'maps')
            && $activeTableId > 0
            && class_exists(\App\Services\LocationValueService::class)
            && \App\Services\LocationValueService::tableHasLocationColumn($this->pdo, $activeTableId);
        $canExport = false;
        if (isset($_SESSION['user_id'])) {
            $canExport = function_exists('has_permission') && has_permission($this->pdo, 'export_data');
        } elseif (function_exists('guest_has_permission')) {
            $canExport = guest_has_permission($this->pdo, 'export_data');
        }
        $basePath = defined('BASE_PATH') ? rtrim((string) BASE_PATH, '/') : '';
        require_once __DIR__ . '/../Views/home/index.php';
    }
}
