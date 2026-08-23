<?php
declare(strict_types=1);

namespace App\Services;

use PDO;

class GeocodeService
{
    public function __construct(private PDO $pdo)
    {
    }

    /**
     * @return list<array{label:string,lat:float,lng:float,q:string}>
     */
    public function search(string $query): array
    {
        $query = trim($query);
        if ($query === '' || mb_strlen($query) < 2) {
            return [];
        }
        $key = mb_strtolower($query);
        $cached = $this->fromCache($key);
        if ($cached !== []) {
            return $cached;
        }
        $url = 'https://nominatim.openstreetmap.org/search?format=jsonv2&limit=6&addressdetails=0&q='
            . rawurlencode($query);
        $ctx = stream_context_create([
            'http' => [
                'method' => 'GET',
                'header' => "User-Agent: pRD-maps/1.0 (parish records; https://getprd.org)\r\nAccept: application/json\r\n",
                'timeout' => 8,
            ],
        ]);
        $raw = @file_get_contents($url, false, $ctx);
        if ($raw === false || $raw === '') {
            return [];
        }
        $json = json_decode($raw, true);
        if (!is_array($json)) {
            return [];
        }
        $out = [];
        foreach ($json as $row) {
            if (!is_array($row) || !isset($row['lat'], $row['lon'])) {
                continue;
            }
            $label = isset($row['display_name']) && is_string($row['display_name']) ? $row['display_name'] : $query;
            $out[] = [
                'label' => $label,
                'lat' => (float) $row['lat'],
                'lng' => (float) $row['lon'],
                'q' => $query,
            ];
        }
        if ($out !== []) {
            $this->toCache($key, $out);
        }
        return $out;
    }

    /**
     * @return list<array{label:string,lat:float,lng:float,q:string}>
     */
    private function fromCache(string $key): array
    {
        try {
            $stmt = $this->pdo->prepare('SELECT suggested_label, lat, lng, payload FROM place_geocode_cache WHERE query_key = ?');
            $stmt->execute([$key]);
            $row = $stmt->fetch(PDO::FETCH_ASSOC);
        } catch (\Throwable $e) {
            return [];
        }
        if ($row === false) {
            return [];
        }
        if (!empty($row['payload']) && is_string($row['payload'])) {
            $decoded = json_decode($row['payload'], true);
            if (is_array($decoded)) {
                return $decoded;
            }
        }
        return [[
            'label' => (string) $row['suggested_label'],
            'lat' => (float) $row['lat'],
            'lng' => (float) $row['lng'],
            'q' => $key,
        ]];
    }

    /**
     * @param list<array{label:string,lat:float,lng:float,q:string}> $hits
     */
    private function toCache(string $key, array $hits): void
    {
        try {
            $first = $hits[0];
            $stmt = $this->pdo->prepare(
                'INSERT INTO place_geocode_cache (query_key, lat, lng, suggested_label, payload)
                 VALUES (?, ?, ?, ?, ?)
                 ON DUPLICATE KEY UPDATE lat = VALUES(lat), lng = VALUES(lng),
                    suggested_label = VALUES(suggested_label), payload = VALUES(payload)'
            );
            $stmt->execute([
                $key,
                $first['lat'],
                $first['lng'],
                $first['label'],
                json_encode($hits, JSON_UNESCAPED_UNICODE),
            ]);
        } catch (\Throwable $e) {
            // cache is optional
        }
    }
}
