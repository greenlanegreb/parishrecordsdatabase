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

        $cfg = MapConfigService::geocodeConfig($this->pdo);
        $provider = $cfg['provider'];
        $apiKey = $cfg['api_key'];
        $out = [];

        if ($provider === 'locationiq' && $apiKey !== '') {
            $out = $this->fetchJson(
                'https://us1.locationiq.com/v1/search?key=' . rawurlencode($apiKey)
                . '&format=json&limit=6&q=' . rawurlencode($query),
                $query,
                'display_name',
                'lat',
                'lon'
            );
        } elseif ($provider === 'opencage' && $apiKey !== '') {
            $out = $this->fetchOpencage($query, $apiKey);
        } else {
            // nominatim (default) — free, rate-limited; always used if paid key missing
            $out = $this->fetchJson(
                'https://nominatim.openstreetmap.org/search?format=jsonv2&limit=6&addressdetails=0&q='
                . rawurlencode($query),
                $query,
                'display_name',
                'lat',
                'lon',
                "User-Agent: pRD-maps/1.0 (parish records; https://getprd.org)\r\nAccept: application/json\r\n"
            );
        }

        if ($out !== []) {
            $this->toCache($key, $out);
        }
        return $out;
    }

    /**
     * @return list<array{label:string,lat:float,lng:float,q:string}>
     */
    private function fetchJson(
        string $url,
        string $query,
        string $labelKey,
        string $latKey,
        string $lngKey,
        string $extraHeaders = "Accept: application/json\r\n"
    ): array {
        $ctx = stream_context_create([
            'http' => [
                'method' => 'GET',
                'header' => $extraHeaders,
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
            if (!is_array($row) || !isset($row[$latKey], $row[$lngKey])) {
                continue;
            }
            $label = isset($row[$labelKey]) && is_string($row[$labelKey]) ? $row[$labelKey] : $query;
            $out[] = [
                'label' => $label,
                'lat' => (float) $row[$latKey],
                'lng' => (float) $row[$lngKey],
                'q' => $query,
            ];
        }
        return $out;
    }

    /**
     * @return list<array{label:string,lat:float,lng:float,q:string}>
     */
    private function fetchOpencage(string $query, string $apiKey): array
    {
        $url = 'https://api.opencagedata.com/geocode/v1/json?q=' . rawurlencode($query)
            . '&key=' . rawurlencode($apiKey) . '&limit=6&no_annotations=1';
        $ctx = stream_context_create([
            'http' => [
                'method' => 'GET',
                'header' => "Accept: application/json\r\n",
                'timeout' => 8,
            ],
        ]);
        $raw = @file_get_contents($url, false, $ctx);
        if ($raw === false || $raw === '') {
            return [];
        }
        $json = json_decode($raw, true);
        if (!is_array($json) || !isset($json['results']) || !is_array($json['results'])) {
            return [];
        }
        $out = [];
        foreach ($json['results'] as $row) {
            if (!is_array($row) || !isset($row['geometry']['lat'], $row['geometry']['lng'])) {
                continue;
            }
            $label = isset($row['formatted']) && is_string($row['formatted']) ? $row['formatted'] : $query;
            $out[] = [
                'label' => $label,
                'lat' => (float) $row['geometry']['lat'],
                'lng' => (float) $row['geometry']['lng'],
                'q' => $query,
            ];
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
            // ignore cache write failures
        }
    }
}
