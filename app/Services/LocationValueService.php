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
            ['hex' => '#e74c3c', 'label' => 'Bright red'],
            ['hex' => '#d35400', 'label' => 'Orange'],
            ['hex' => '#e67e22', 'label' => 'Amber'],
            ['hex' => '#f1c40f', 'label' => 'Yellow'],
            ['hex' => '#f39c12', 'label' => 'Gold'],
            ['hex' => '#27ae60', 'label' => 'Green'],
            ['hex' => '#2ecc71', 'label' => 'Lime'],
            ['hex' => '#16a085', 'label' => 'Teal'],
            ['hex' => '#1abc9c', 'label' => 'Mint'],
            ['hex' => '#2980b9', 'label' => 'Blue'],
            ['hex' => '#3498db', 'label' => 'Sky'],
            ['hex' => '#8e44ad', 'label' => 'Purple'],
            ['hex' => '#9b59b6', 'label' => 'Violet'],
            ['hex' => '#2c3e50', 'label' => 'Navy'],
            ['hex' => '#34495e', 'label' => 'Slate'],
            ['hex' => '#7f8c8d', 'label' => 'Grey'],
            ['hex' => '#95a5a6', 'label' => 'Silver'],
            ['hex' => '#e84393', 'label' => 'Pink'],
            ['hex' => '#fd79a8', 'label' => 'Rose'],
            ['hex' => '#6d4c41', 'label' => 'Brown'],
            ['hex' => '#a0522d', 'label' => 'Sienna'],
            ['hex' => '#000000', 'label' => 'Black'],
            ['hex' => '#ffffff', 'label' => 'White'],
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
     * @return array{q:string,label:string,lat:float,lng:float,title:string,body:string,color:string,show_on_map:bool}|null
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
        // show_on_map: checkbox posts "1" when ticked; missing = hide
        $show = true;
        if (array_key_exists('show_on_map', $posted)) {
            $raw = $posted['show_on_map'];
            $show = ($raw === '1' || $raw === 1 || $raw === true || $raw === 'on');
        } elseif (array_key_exists('hide_from_map', $posted)) {
            $raw = $posted['hide_from_map'];
            $hide = ($raw === '1' || $raw === 1 || $raw === true || $raw === 'on');
            $show = !$hide;
        }
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
            'show_on_map' => $show,
        ];
    }

    /**
     * @return array{q:string,label:string,lat:float,lng:float,title:string,body:string,color:string,show_on_map:bool}
     */
    public static function decode(?string $json): array
    {
        $empty = [
            'q' => '',
            'label' => '',
            'lat' => 0.0,
            'lng' => 0.0,
            'title' => '',
            'body' => '',
            'color' => self::defaultColor(),
            'show_on_map' => true,
        ];
        if ($json === null || $json === '') {
            return $empty;
        }
        $data = json_decode($json, true);
        if (!is_array($data) || !isset($data['lat'], $data['lng'])) {
            return $empty;
        }
        $show = true;
        if (array_key_exists('show_on_map', $data)) {
            $show = (bool) $data['show_on_map'];
        } elseif (array_key_exists('hide_from_map', $data)) {
            $show = !(bool) $data['hide_from_map'];
        }
        return [
            'q' => isset($data['q']) && is_string($data['q']) ? $data['q'] : '',
            'label' => isset($data['label']) && is_string($data['label']) ? $data['label'] : '',
            'lat' => (float) $data['lat'],
            'lng' => (float) $data['lng'],
            'title' => isset($data['title']) && is_string($data['title']) ? $data['title'] : '',
            'body' => isset($data['body']) && is_string($data['body']) ? $data['body'] : '',
            'color' => self::normalizeColor(isset($data['color']) && is_string($data['color']) ? $data['color'] : ''),
            'show_on_map' => $show,
        ];
    }

    /**
     * @param array{q:string,label:string,lat:float,lng:float,title:string,body:string,color:string,show_on_map?:bool} $data
     */
    public static function encode(array $data): string
    {
        if (!isset($data['show_on_map'])) {
            $data['show_on_map'] = true;
        }
        return json_encode($data, JSON_UNESCAPED_UNICODE) ?: '';
    }


    /**
     * Human-readable location for tables, export, print (not raw JSON).
     */
    public static function formatDisplay(?string $json): string
    {
        if ($json === null || $json === '') {
            return '';
        }
        $trim = trim($json);
        if ($trim === '' || $trim[0] !== '{') {
            return $trim;
        }
        $d = self::decode($json);
        $title = $d['title'];
        $label = $d['label'];
        if ($title !== '' && $label !== '') {
            return $title . ' (' . $label . ')';
        }
        if ($title !== '') {
            return $title;
        }
        if ($label !== '') {
            return $label;
        }
        return '';
    }

    public static function isComplete(?array $data): bool
    {
        if ($data === null) {
            return false;
        }
        return $data['lat'] !== 0.0 && $data['lng'] !== 0.0
            && $data['label'] !== '' && $data['title'] !== '' && $data['body'] !== '';
    }

    /**
     * @param array{q:string,label:string,lat:float,lng:float,title:string,body:string,color:string,show_on_map?:bool} $data
     */
    public static function upsertPin(PDO $pdo, int $tableId, int $recordId, int $columnId, array $data): void
    {
        $show = !isset($data['show_on_map']) || $data['show_on_map'];
        $hide = $show ? 0 : 1;
        $sql = 'INSERT INTO map_pins (table_id, record_id, column_id, lat, lng, label, title, body, color, hide_from_map)
                VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?)
                ON DUPLICATE KEY UPDATE
                    table_id = VALUES(table_id),
                    lat = VALUES(lat),
                    lng = VALUES(lng),
                    label = VALUES(label),
                    title = VALUES(title),
                    body = VALUES(body),
                    color = VALUES(color),
                    hide_from_map = VALUES(hide_from_map)';
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
            $hide,
        ]);
    }

    public static function deletePin(PDO $pdo, int $recordId, int $columnId): void
    {
        $stmt = $pdo->prepare('DELETE FROM map_pins WHERE record_id = ? AND column_id = ?');
        $stmt->execute([$recordId, $columnId]);
    }


    /** Short label for tight table cells (full text still available via title / expand). */
    public static function formatDisplayShort(?string $json, int $maxLen = 48): string
    {
        $full = self::formatDisplay($json);
        if ($full === '') {
            return '';
        }
        if (mb_strlen($full) <= $maxLen) {
            return $full;
        }
        return rtrim(mb_substr($full, 0, $maxLen - 1)) . '…';
    }

    /**
     * Keep map_pins in sync with stored LOCATION JSON (or remove pin if empty/incomplete).
     */
    public static function syncPinFromStoredValue(
        PDO $pdo,
        int $tableId,
        int $recordId,
        int $columnId,
        ?string $json
    ): void {
        if ($json === null || trim($json) === '') {
            self::deletePin($pdo, $recordId, $columnId);
            return;
        }
        $data = self::decode($json);
        if (!self::isComplete($data)) {
            self::deletePin($pdo, $recordId, $columnId);
            return;
        }
        self::upsertPin($pdo, $tableId, $recordId, $columnId, $data);
    }

    public static function tableHasLocationColumn(PDO $pdo, int $tableId): bool
    {
        $stmt = $pdo->prepare("SELECT COUNT(*) FROM table_columns WHERE table_id = ? AND data_type = 'LOCATION'");
        $stmt->execute([$tableId]);
        return (int) $stmt->fetchColumn() > 0;
    }
}
