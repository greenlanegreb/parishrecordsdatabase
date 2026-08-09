<?php
/**
 * MIGRATED FILE MAPPING
 * ---------------------
 * Extracted data-fetching & business logic for Admin Settings.
 */
declare(strict_types=1);

namespace App\Services;

use Exception;
use PDO;

class SettingsService
{
    private PDO $pdo;

    public function __construct(PDO $pdo)
    {
        $this->pdo = $pdo;
    }

    /**
     * Auto-register table-scoped permissions for any existing dynamic tables.
     */
    public function autoRegisterTablePermissions(): void
    {
        try {
            $existingTables = $this->pdo->query("SELECT id, table_name FROM dynamic_tables");
            /** @var array<int, array<string, mixed>> $tableRows */
            $tableRows = $existingTables !== false ? $existingTables->fetchAll(PDO::FETCH_ASSOC) : [];
            foreach ($tableRows as $et) {
                $tId = isset($et['id']) ? (int)$et['id'] : 0;
                $tName = isset($et['table_name']) && is_string($et['table_name']) ? $et['table_name'] : '';
                $viewKey = 'view_table_' . $tId;
                $viewDesc = 'Allows viewing and searching records in table: ' . $tName;
                $modKey = 'moderate_table_' . $tId;
                $modDesc = 'Allows reviewing and moderating suggestions in table: ' . $tName;
                
                $insP = $this->pdo->prepare("INSERT IGNORE INTO permissions (permission_key, description) VALUES (?, ?)");
                $insP->execute([$viewKey, $viewDesc]);
                $insP->execute([$modKey, $modDesc]);
            }
        } catch (Exception $e) {
            // Ignore database table discovery errors if not yet seeded
        }
    }

    public function getSettingVal(string $key, string $default): string
    {
        try {
            $stmt = $this->pdo->prepare("SELECT setting_value FROM site_settings WHERE setting_key = ?");
            $stmt->execute([$key]);
            $val = $stmt->fetchColumn();
            return ($val !== false && $val !== null && is_string($val)) ? $val : $default;
        } catch (Exception $e) {
            return $default;
        }
    }

    /**
     * @return array<int, string>
     */
    public function getAvailableLanguages(): array
    {
        $availableLanguages = [];
        $langDir = __DIR__ . '/../../lang';
        if (is_dir($langDir)) {
            $globFiles = glob($langDir . '/*.php');
            if ($globFiles !== false) {
                foreach ($globFiles as $file) {
                    $code = basename($file, '.php');
                    if (preg_match('/^[a-z_]+$/', $code)) {
                        $availableLanguages[] = $code;
                    }
                }
            }
            sort($availableLanguages);
        }
        if (!in_array('en', $availableLanguages, true)) {
            array_unshift($availableLanguages, 'en');
        }
        return $availableLanguages;
    }

    /**
     * @return array{current: int, latest: int, needsUpdate: bool}
     */
    public function getSchemaStatus(): array
    {
        $schemaCurrent = function_exists('get_schema_version') ? \get_schema_version($this->pdo) : 0;
        $schemaLatest = $schemaCurrent;
        $migrationsDir = __DIR__ . '/../../db/migrations';
        if (is_dir($migrationsDir)) {
            $migGlob = glob($migrationsDir . '/*.php');
            if ($migGlob !== false) {
                foreach ($migGlob as $migFile) {
                    $m = [];
                    if (preg_match('/(\d+)_/', basename($migFile), $m)) {
                        $schemaLatest = max($schemaLatest, (int)$m[1]);
                    }
                }
            }
        }
        return [
            'current' => $schemaCurrent,
            'latest' => $schemaLatest,
            'needsUpdate' => ($schemaCurrent < $schemaLatest)
        ];
    }

    /**
     * @return array<int, array<string, mixed>>
     */
    public function getNotices(): array
    {
        $noticesStmt = $this->pdo->query("SELECT * FROM site_notices ORDER BY display_order ASC, id DESC");
        return $noticesStmt !== false ? $noticesStmt->fetchAll(PDO::FETCH_ASSOC) : [];
    }

    /**
     * @return array<int, array<string, mixed>>
     */
    public function getAuditLogs(): array
    {
        $auditStmt = $this->pdo->query("
            SELECT al.*, u.username 
            FROM audit_logs al 
            LEFT JOIN users u ON al.user_id = u.id 
            ORDER BY al.created_at DESC 
            LIMIT 250
        ");
        return $auditStmt !== false ? $auditStmt->fetchAll(PDO::FETCH_ASSOC) : [];
    }

    /**
     * @return array<int, string>
     */
    public function getDistinctActions(): array
    {
        $actionsStmt = $this->pdo->query("SELECT DISTINCT action FROM audit_logs ORDER BY action ASC");
        return $actionsStmt !== false ? $actionsStmt->fetchAll(PDO::FETCH_COLUMN) : [];
    }

    /**
     * @return array<int, array<string, mixed>>
     */
    public function getRolesList(): array
    {
        $rolesListStmt = $this->pdo->query("SELECT * FROM roles ORDER BY id ASC");
        return $rolesListStmt !== false ? $rolesListStmt->fetchAll(PDO::FETCH_ASSOC) : [];
    }

    /**
     * @return array<int, array<string, mixed>>
     */
    public function getPermissionsList(): array
    {
        $permsListStmt = $this->pdo->query("SELECT * FROM permissions ORDER BY id ASC");
        return $permsListStmt !== false ? $permsListStmt->fetchAll(PDO::FETCH_ASSOC) : [];
    }

    /**
     * @return array<int, array<int, true>>
     */
    public function getActiveMappings(): array
    {
        $activeMappings = [];
        $mapRowsStmt = $this->pdo->query("SELECT role_id, permission_id FROM role_permissions");
        $mapRows = $mapRowsStmt !== false ? $mapRowsStmt->fetchAll(PDO::FETCH_ASSOC) : [];
        foreach ($mapRows as $m) {
            $rId = isset($m['role_id']) ? (int)$m['role_id'] : 0;
            $pId = isset($m['permission_id']) ? (int)$m['permission_id'] : 0;
            $activeMappings[$rId][$pId] = true;
        }
        return $activeMappings;
    }

    public function getPermissionCategory(string $pkey): string
    {
        if (str_starts_with($pkey, 'view_table_') || str_starts_with($pkey, 'moderate_table_')) {
            return 'Dynamic Tables & Records';
        }
        if (in_array($pkey, ['manage_users', 'invite_users', 'access_onboarding', 'view_leaderboard'], true)) {
            return 'Users & Gamification Module';
        }
        if (in_array($pkey, ['manage_volunteers', 'submit_volunteer', 'manage_feedback', 'submit_feedback'], true)) {
            return 'Portals & Submissions Module';
        }
        if (in_array($pkey, ['access_suggest_edit', 'moderate_suggestions', 'manage_feedback'], true)) {
            return 'Moderation Workflow';
        }
        return 'Core System & Settings';
    }
}
