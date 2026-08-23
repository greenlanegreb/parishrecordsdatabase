<?php
declare(strict_types=1);

namespace App\Controllers;

use PDO;

class ApiMapPointsController
{
    public function __construct(private PDO $pdo)
    {
    }

    public function index(): void
    {
        header('Content-Type: application/json; charset=utf-8');
        if (!is_module_enabled($this->pdo, 'maps')) {
            http_response_code(403);
            echo json_encode(['points' => []]);
            return;
        }
        $tableId = isset($_GET['table_id']) ? (int) $_GET['table_id'] : 0;
        if ($tableId < 1) {
            echo json_encode(['points' => []]);
            return;
        }
        $currentUser = function_exists('get_current_user_data') ? get_current_user_data($this->pdo) : null;
        if (!user_can_view_table($this->pdo, $tableId, $currentUser)) {
            http_response_code(403);
            echo json_encode(['points' => []]);
            return;
        }
        $south = isset($_GET['south']) ? (float) $_GET['south'] : -90;
        $north = isset($_GET['north']) ? (float) $_GET['north'] : 90;
        $west = isset($_GET['west']) ? (float) $_GET['west'] : -180;
        $east = isset($_GET['east']) ? (float) $_GET['east'] : 180;
        if ($south > $north) {
            [$south, $north] = [$north, $south];
        }
        $sql = 'SELECT id, record_id, lat, lng, label, title, body, color
                FROM map_pins
                WHERE table_id = ? AND lat BETWEEN ? AND ? AND lng BETWEEN ? AND ?
                ORDER BY id DESC
                LIMIT 800';
        $stmt = $this->pdo->prepare($sql);
        $stmt->execute([$tableId, $south, $north, $west, $east]);
        $rows = $stmt->fetchAll(PDO::FETCH_ASSOC) ?: [];

        $filters = isset($_GET['filters']) && is_array($_GET['filters']) ? $_GET['filters'] : [];
        $hasFilters = false;
        foreach ($filters as $v) {
            if (is_string($v) && trim($v) !== '') {
                $hasFilters = true;
                break;
            }
        }
        if ($hasFilters) {
            $keep = $this->matchingRecordIds($tableId, $filters);
            $rows = array_values(array_filter($rows, static function ($row) use ($keep) {
                return isset($keep[(int) $row['record_id']]);
            }));
        }

        $points = [];
        foreach ($rows as $row) {
            $points[] = [
                'id' => (int) $row['id'],
                'record_id' => (int) $row['record_id'],
                'lat' => (float) $row['lat'],
                'lng' => (float) $row['lng'],
                'label' => (string) $row['label'],
                'title' => (string) $row['title'],
                'body' => (string) $row['body'],
                'color' => (string) $row['color'],
            ];
        }
        echo json_encode(['points' => $points], JSON_UNESCAPED_UNICODE);
    }

    /**
     * @param array<mixed, mixed> $filters
     * @return array<int, true>
     */
    private function matchingRecordIds(int $tableId, array $filters): array
    {
        $keep = [];
        $recStmt = $this->pdo->prepare('SELECT id FROM records WHERE table_id = ?');
        $recStmt->execute([$tableId]);
        $ids = $recStmt->fetchAll(PDO::FETCH_COLUMN) ?: [];
        if ($ids === []) {
            return [];
        }
        $valStmt = $this->pdo->query('SELECT record_id, column_id, value_content FROM record_values');
        $map = [];
        if ($valStmt !== false) {
            foreach ($valStmt->fetchAll(PDO::FETCH_ASSOC) as $v) {
                $map[(int) $v['record_id']][(int) $v['column_id']] = (string) $v['value_content'];
            }
        }
        $search = [];
        $dates = [];
        foreach ($filters as $cid => $val) {
            if (!is_string($val) || trim($val) === '') {
                continue;
            }
            $search[(int) $cid] = trim($val);
        }
        foreach ($ids as $rid) {
            $rid = (int) $rid;
            if (function_exists('record_matches_filters')) {
                if (record_matches_filters($rid, $map, $search, $dates)) {
                    $keep[$rid] = true;
                }
            } else {
                $ok = true;
                foreach ($search as $cid => $needle) {
                    $hay = $map[$rid][$cid] ?? '';
                    if (stripos($hay, $needle) === false) {
                        $ok = false;
                        break;
                    }
                }
                if ($ok) {
                    $keep[$rid] = true;
                }
            }
        }
        return $keep;
    }
}
