<?php
declare(strict_types=1);

namespace App\Services;

use PDO;

class LocationValueService
{
    /** @return list<array{hex:string,label:string}> */
    public static function palette(): array
    {
        return [
            ['hex' => '#c0392b', 'label' => 'Red'],
            ['hex' => '#d35400', 'label' => 'Orange'],
            ['hex' => '#f1c40f', 'label' => 'Yellow'],
            ['hex' => '#27ae60', 'label' => 'Green'],
            ['hex' => '#16a085', 'label' => 'Teal'],
            ['hex' => '#2980b9', 'label' => 'Blue'],
            ['hex' => '#8e44ad', 'label' => 'Purple'],
            ['hex' => '#2c3e50', 'label' => 'Navy'],
            ['hex' => '#7f8c8d', 'label' => 'Grey'],
            ['hex' => '#e84393', 'label' => 'Pink'],
            ['hex' => '#6d4c41', 'label' => 'Brown'],
            ['hex' => '#000000', 'label' => 'Black'],
        ];
    }

    public static function defaultColor(): string
    {
        return '#c0392b';
    }

    public static function normalizeColor(string $hex): string
    {
        $hex = strtolower(trim($hex));
        foreach (self::palette() as $row) {
            if (strtolower($row['hex']) === $hex) {
                return $row['hex'];
            }
        }
        return self::defaultColor();
    }

    /**
     * @param array<string, mixed> $posted
     * @return array{q:string,label:string,lat:float,lng:float,title:string,body:string,color:string}|null
     */
    public static function fromPosted(array $posted): ?array
    {
        $lat = isset($posted['lat']) ? (float) $posted['lat'] : 0.0;
        $lng = isset($posted['lng']) ? (float) $posted['lng'] : 0.0;
        $label = isset($posted['label']) && is_string($posted['label']) ? trim($posted['label']) : '';
        $title = isset($posted['title']) && is_string($posted['title']) ? trim($posted['title']) : '';
        $body = isset($posted['body']) && is_string($posted['body']) ? trim($posted['body']) : '';
        $q = isset($posted['q']) && is_string($posted['q']) ? trim($posted['q']) : '';
        $color = isset($posted['color']) && is_string($posted['color']) ? $posted['color'] : self::defaultColor();
        if ($lat === 0.0 && $lng === 0.0 && $label === '' && $title === '') {
            return null;
        }
        return [
            'q' => $q,
            'label' => $label,
            'lat' => $lat,
            'lng' => $lng,
            'title' => $title,
            'body' => $body,
            'color' => self::normalizeColor($color),
        ];
    }

    /**
     * @return array{q:string,label:string,lat:float,lng:float,title:string,body:string,color:string}|null
     */
    public static function decode(?string $raw): ?array
    {
        if ($raw === null || trim($raw) === '') {
            return null;
        }
        $data = json_decode($raw, true);
        if (!is_array($data) || !isset($data['lat'], $data['lng'])) {
            return null;
        }
        return [
            'q' => isset($data['q']) && is_string($data['q']) ? $data['q'] : '',
            'label' => isset($data['label']) && is_string($data['label']) ? $data['label'] : '',
            'lat' => (float) $data['lat'],
            'lng' => (float) $data['lng'],
            'title' => isset($data['title']) && is_string($data['title']) ? $data['title'] : '',
            'body' => isset($data['body']) && is_string($data['body']) ? $data['body'] : '',
            'color' => self::normalizeColor(isset($data['color']) && is_string($data['color']) ? $data['color'] : ''),
        ];
    }

    /**
     * @param array{q:string,label:string,lat:float,lng:float,title:string,body:string,color:string} $data
     */
    public static function encode(array $data): string
    {
        return json_encode($data, JSON_UNESCAPED_UNICODE) ?: '';
    }

    public static function isComplete(?array $data): bool
    {
        if ($data === null) {
            return false;
        }
        return $data['lat'] !== 0.0 && $data['lng'] !== 0.0
            && $data['label'] !== '' && $data['title'] !== '' && $data['body'] !== '';
    }

    public static function upsertPin(PDO $pdo, int $tableId, int $recordId, int $columnId, array $data): void
    {
        $sql = 'INSERT INTO map_pins (table_id, record_id, column_id, lat, lng, label, title, body, color)
                VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?)
                ON DUPLICATE KEY UPDATE
                    table_id = VALUES(table_id),
                    lat = VALUES(lat),
                    lng = VALUES(lng),
                    label = VALUES(label),
                    title = VALUES(title),
                    body = VALUES(body),
                    color = VALUES(color)';
        $stmt = $pdo->prepare($sql);
        $stmt->execute([
            $tableId,
            $recordId,
            $columnId,
            $data['lat'],
            $data['lng'],
            $data['label'],
            $data['title'],
            $data['body'],
            $data['color'],
        ]);
    }

    public static function deletePin(PDO $pdo, int $recordId, int $columnId): void
    {
        $stmt = $pdo->prepare('DELETE FROM map_pins WHERE record_id = ? AND column_id = ?');
        $stmt->execute([$recordId, $columnId]);
    }

    public static function tableHasLocationColumn(PDO $pdo, int $tableId): bool
    {
        $stmt = $pdo->prepare("SELECT COUNT(*) FROM table_columns WHERE table_id = ? AND data_type = 'LOCATION'");
        $stmt->execute([$tableId]);
        return (int) $stmt->fetchColumn() > 0;
    }
}
