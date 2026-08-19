<?php
declare(strict_types=1);

namespace App\Services;

use Exception;
use PDO;

/**
 * Install and remove demo table packs (shared by installer and admin).
 */
class DemoPackService
{
    public function __construct(private PDO $pdo)
    {
    }

    /**
     * @return list<array{slug: string, label: string, summary: string, installed: bool, has_demo_data: bool}>
     */
    public function listPacks(): array
    {
        $out = [];
        foreach ($this->availableSlugs() as $slug) {
            $def = $this->loadDefinition($slug);
            $out[] = [
                'slug' => $slug,
                'label' => $def['label'],
                'summary' => $def['summary'],
                'installed' => $this->hasArtifact($slug, 'table'),
                'has_demo_data' => $this->hasArtifact($slug, 'record'),
            ];
        }
        return $out;
    }

    /**
     * @param list<string> $slugs
     */
    public function installPacks(array $slugs, bool $withData, array $currentUser): void
    {
        $this->assertArtifactsTable();
        $userId = isset($currentUser['id']) ? (int) $currentUser['id'] : 0;
        if ($userId < 1) {
            throw new Exception('A user id is required to create demo tables.');
        }

        foreach ($slugs as $slug) {
            $slug = $this->normaliseSlug((string) $slug);
            if ($slug === '') {
                continue;
            }
            if ($this->hasArtifact($slug, 'table')) {
                if ($withData && !$this->hasArtifact($slug, 'record')) {
                    $this->installDataOnly($slug, $currentUser);
                }
                continue;
            }
            $this->installOne($slug, $withData, $currentUser);
        }
    }

    public function removeDemoData(string $slug): void
    {
        $this->assertArtifactsTable();
        $slug = $this->normaliseSlug($slug);
        $ids = $this->refIds($slug, 'record');
        if ($ids === []) {
            return;
        }
        $this->pdo->beginTransaction();
        try {
            $ph = implode(',', array_fill(0, count($ids), '?'));
            $delV = $this->pdo->prepare("DELETE FROM record_values WHERE record_id IN ({$ph})");
            $delV->execute($ids);
            $delR = $this->pdo->prepare("DELETE FROM records WHERE id IN ({$ph})");
            $delR->execute($ids);
            $delA = $this->pdo->prepare("DELETE FROM demo_artifacts WHERE pack_slug = ? AND artifact_type = 'record'");
            $delA->execute([$slug]);
            $this->pdo->commit();
        } catch (Exception $e) {
            if ($this->pdo->inTransaction()) {
                $this->pdo->rollBack();
            }
            throw $e;
        }
    }

    /**
     * Removes pack tables, columns, demo rows, and any extra rows the admin added in those tables.
     */
    public function removePack(string $slug, array $currentUser): void
    {
        $this->assertArtifactsTable();
        $slug = $this->normaliseSlug($slug);
        $tableIds = $this->refIds($slug, 'table');
        $tables = new TableService($this->pdo);
        foreach ($tableIds as $tableId) {
            if ($tableId <= 1) {
                continue;
            }
            try {
                $tables->deleteTable($tableId, $currentUser);
            } catch (Exception $e) {
                // Table may already have been deleted in Manage Tables
            }
        }
        $del = $this->pdo->prepare('DELETE FROM demo_artifacts WHERE pack_slug = ?');
        $del->execute([$slug]);
    }

    /**
     * @return list<string>
     */
    public function availableSlugs(): array
    {
        return ['parish', 'library'];
    }

    /**
     * @return array{slug: string, label: string, summary: string, tables: list<array<string, mixed>>}
     */
    public function loadDefinition(string $slug): array
    {
        $slug = $this->normaliseSlug($slug);
        $file = dirname(__DIR__, 2) . '/db/demo/' . $slug . '.php';
        if (!is_file($file)) {
            throw new Exception('Unknown demo pack.');
        }
        /** @var mixed $def */
        $def = require $file;
        if (!is_array($def) || !isset($def['slug'], $def['tables']) || !is_array($def['tables'])) {
            throw new Exception('Invalid demo pack definition.');
        }
        return [
            'slug' => (string) $def['slug'],
            'label' => isset($def['label']) && is_string($def['label']) ? $def['label'] : $slug,
            'summary' => isset($def['summary']) && is_string($def['summary']) ? $def['summary'] : '',
            'tables' => $def['tables'],
        ];
    }

    private function installOne(string $slug, bool $withData, array $currentUser): void
    {
        $def = $this->loadDefinition($slug);
        $tables = new TableService($this->pdo);

        foreach ($def['tables'] as $tableDef) {
            if (!is_array($tableDef)) {
                continue;
            }
            $tableName = isset($tableDef['table_name']) && is_string($tableDef['table_name'])
                ? trim($tableDef['table_name']) : '';
            $description = isset($tableDef['description']) && is_string($tableDef['description'])
                ? $tableDef['description'] : '';
            if ($tableName === '') {
                continue;
            }

            $tableId = $tables->createTable([
                'table_name' => $tableName,
                'description' => $description,
            ], $currentUser);
            $this->track($slug, 'table', $tableId);

            $colIdsByName = [];
            $columns = isset($tableDef['columns']) && is_array($tableDef['columns']) ? $tableDef['columns'] : [];
            foreach ($columns as $col) {
                if (!is_array($col)) {
                    continue;
                }
                $colName = isset($col['column_name']) && is_string($col['column_name']) ? $col['column_name'] : '';
                if ($colName === '') {
                    continue;
                }
                $post = [
                    'column_name' => $colName,
                    'data_type' => isset($col['data_type']) && is_string($col['data_type']) ? $col['data_type'] : 'VARCHAR',
                    'max_length' => $col['max_length'] ?? '',
                    'date_search_behavior' => $col['date_search_behavior'] ?? 'manual_only',
                ];
                if (!empty($col['is_required'])) {
                    $post['is_required'] = '1';
                }
                if (isset($col['field_options']) && is_string($col['field_options']) && $col['field_options'] !== '') {
                    $post['field_options'] = $col['field_options'];
                }
                if (!empty($col['allow_multiple'])) {
                    $post['allow_multiple'] = '1';
                }
                if (isset($col['min_value']) && $col['min_value'] !== '') {
                    $post['min_value'] = (string) $col['min_value'];
                }
                if (isset($col['max_value']) && $col['max_value'] !== '') {
                    $post['max_value'] = (string) $col['max_value'];
                }
                $tables->saveColumn('create', $tableId, $post, $currentUser);
                $q = $this->pdo->prepare(
                    'SELECT id FROM table_columns WHERE table_id = ? AND column_name = ? ORDER BY id DESC LIMIT 1'
                );
                $q->execute([$tableId, $colName]);
                $colId = (int) $q->fetchColumn();
                if ($colId > 0) {
                    $colIdsByName[$colName] = $colId;
                    $this->track($slug, 'column', $colId);
                }
            }

            if ($withData) {
                $rows = isset($tableDef['records']) && is_array($tableDef['records']) ? $tableDef['records'] : [];
                $this->insertRecords($slug, $tableId, $colIdsByName, $rows, $currentUser);
            }
        }
    }

    private function installDataOnly(string $slug, array $currentUser): void
    {
        $def = $this->loadDefinition($slug);
        foreach ($def['tables'] as $tableDef) {
            if (!is_array($tableDef)) {
                continue;
            }
            $tableName = isset($tableDef['table_name']) && is_string($tableDef['table_name'])
                ? $tableDef['table_name'] : '';
            $find = $this->pdo->prepare('SELECT id FROM dynamic_tables WHERE table_name = ? LIMIT 1');
            $find->execute([$tableName]);
            $tableId = (int) $find->fetchColumn();
            if ($tableId < 1) {
                continue;
            }
            $colStmt = $this->pdo->prepare('SELECT id, column_name FROM table_columns WHERE table_id = ?');
            $colStmt->execute([$tableId]);
            $colIdsByName = [];
            foreach ($colStmt->fetchAll(PDO::FETCH_ASSOC) as $row) {
                $n = isset($row['column_name']) && is_string($row['column_name']) ? $row['column_name'] : '';
                if ($n !== '') {
                    $colIdsByName[$n] = (int) $row['id'];
                }
            }
            $rows = isset($tableDef['records']) && is_array($tableDef['records']) ? $tableDef['records'] : [];
            $this->insertRecords($slug, $tableId, $colIdsByName, $rows, $currentUser);
        }
    }

    /**
     * @param array<string, int> $colIdsByName
     * @param list<mixed> $rows
     */
    private function insertRecords(string $slug, int $tableId, array $colIdsByName, array $rows, array $currentUser): void
    {
        $userId = (int) $currentUser['id'];
        $insRec = $this->pdo->prepare('INSERT INTO records (table_id, created_by) VALUES (?, ?)');
        $insVal = $this->pdo->prepare('INSERT INTO record_values (record_id, column_id, value_content) VALUES (?, ?, ?)');

        foreach ($rows as $row) {
            if (!is_array($row)) {
                continue;
            }
            $insRec->execute([$tableId, $userId]);
            $recordId = (int) $this->pdo->lastInsertId();
            if ($recordId < 1) {
                continue;
            }
            $this->track($slug, 'record', $recordId);
            foreach ($row as $colName => $value) {
                if (!is_string($colName) || !isset($colIdsByName[$colName])) {
                    continue;
                }
                $text = is_scalar($value) ? (string) $value : '';
                $insVal->execute([$recordId, $colIdsByName[$colName], $text]);
            }
        }
    }

    private function track(string $slug, string $type, int $refId): void
    {
        if ($refId < 1) {
            return;
        }
        $stmt = $this->pdo->prepare(
            'INSERT INTO demo_artifacts (pack_slug, artifact_type, ref_id) VALUES (?, ?, ?)'
        );
        $stmt->execute([$slug, $type, $refId]);
    }

    private function hasArtifact(string $slug, string $type): bool
    {
        if (!$this->artifactsTableExists()) {
            return false;
        }
        $stmt = $this->pdo->prepare(
            'SELECT 1 FROM demo_artifacts WHERE pack_slug = ? AND artifact_type = ? LIMIT 1'
        );
        $stmt->execute([$slug, $type]);
        return (bool) $stmt->fetchColumn();
    }

    /**
     * @return list<int>
     */
    private function refIds(string $slug, string $type): array
    {
        $stmt = $this->pdo->prepare(
            'SELECT ref_id FROM demo_artifacts WHERE pack_slug = ? AND artifact_type = ?'
        );
        $stmt->execute([$slug, $type]);
        $ids = $stmt->fetchAll(PDO::FETCH_COLUMN);
        $out = [];
        foreach ($ids as $id) {
            $n = (int) $id;
            if ($n > 0) {
                $out[] = $n;
            }
        }
        return $out;
    }

    private function normaliseSlug(string $slug): string
    {
        $slug = strtolower(trim($slug));
        return in_array($slug, $this->availableSlugs(), true) ? $slug : '';
    }

    private function assertArtifactsTable(): void
    {
        if (!$this->artifactsTableExists()) {
            throw new Exception('Demo packs are not available until the database has been updated (demo_artifacts).');
        }
    }

    private function artifactsTableExists(): bool
    {
        try {
            $stmt = $this->pdo->query("SHOW TABLES LIKE 'demo_artifacts'");
            return $stmt !== false && $stmt->fetchColumn() !== false;
        } catch (Exception $e) {
            return false;
        }
    }
}
