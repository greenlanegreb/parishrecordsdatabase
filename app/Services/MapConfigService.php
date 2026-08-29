<?php
declare(strict_types=1);

namespace App\Services;

use PDO;

/**
 * Resolve map tile URL / attribution and geocode endpoint from site settings.
 */
class MapConfigService
{
    public static function setting(PDO $pdo, string $key, string $default = ''): string
    {
        if (function_exists('get_setting')) {
            $v = get_setting($pdo, $key, $default);
            return is_string($v) ? $v : $default;
        }
        try {
            $s = $pdo->prepare('SELECT setting_value FROM site_settings WHERE setting_key = ? LIMIT 1');
            $s->execute([$key]);
            $v = $s->fetchColumn();
            return is_string($v) && $v !== '' ? $v : $default;
        } catch (\Throwable $e) {
            return $default;
        }
    }

    /**
     * @return array{url:string,attribution:string,provider:string}
     */
    public static function tiles(PDO $pdo): array
    {
        $provider = strtolower(trim(self::setting($pdo, 'map_tile_provider', 'default')));
        $custom = trim(self::setting($pdo, 'map_tile_url', ''));
        $apiKey = trim(self::setting($pdo, 'map_tile_api_key', ''));

        $osm = [
            'url' => 'https://{s}.tile.openstreetmap.org/{z}/{x}/{y}.png',
            'attribution' => '&copy; <a href="https://www.openstreetmap.org/copyright" target="_blank" rel="noopener">OpenStreetMap</a>',
            'provider' => 'osm',
        ];

        $carto = static function (string $key) use ($osm): array {
            if ($key === '') {
                return $osm;
            }
            return [
                'url' => 'https://{s}.basemaps.cartocdn.com/rastertiles/voyager/{z}/{x}/{y}.png?key=' . rawurlencode($key),
                'attribution' => '&copy; <a href="https://www.openstreetmap.org/copyright" target="_blank" rel="noopener">OpenStreetMap</a> &copy; <a href="https://carto.com/attributions" target="_blank" rel="noopener">CARTO</a>',
                'provider' => 'carto',
            ];
        };

        if ($provider === 'mapbox') {
            if ($apiKey === '') {
                return $osm;
            }
            return [
                'url' => 'https://api.mapbox.com/styles/v1/mapbox/streets-v12/tiles/{z}/{x}/{y}?access_token=' . rawurlencode($apiKey),
                'attribution' => '&copy; <a href="https://www.openstreetmap.org/copyright" target="_blank" rel="noopener">OpenStreetMap</a> &copy; <a href="https://www.mapbox.com/" target="_blank" rel="noopener">Mapbox</a>',
                'provider' => 'mapbox',
            ];
        }

        if ($provider === 'stadia') {
            if ($apiKey === '') {
                return $osm;
            }
            return [
                'url' => 'https://tiles.stadiamaps.com/tiles/alidade_smooth/{z}/{x}/{y}{r}.png?api_key=' . rawurlencode($apiKey),
                'attribution' => '&copy; <a href="https://www.openstreetmap.org/copyright" target="_blank" rel="noopener">OpenStreetMap</a> &copy; <a href="https://stadiamaps.com/" target="_blank" rel="noopener">Stadia Maps</a>',
                'provider' => 'stadia',
            ];
        }

        if ($provider === 'carto' || $provider === 'voyager') {
            return $carto($apiKey);
        }

        if ($provider === 'custom' && $custom !== '') {
            $u = strtolower($custom);
            $hasEmbeddedKey = str_contains($u, 'access_token=') || str_contains($u, 'api_key=') || str_contains($u, '?key=') || str_contains($u, '&key=');
            $looksKeyed = str_contains($u, 'stadiamaps.com') || str_contains($u, 'mapbox.com') || str_contains($u, 'basemaps.cartocdn.com') || str_contains($u, 'carto.com');
            if ($looksKeyed && $apiKey === '' && !$hasEmbeddedKey) {
                return $osm;
            }
            if ($looksKeyed && $apiKey !== '' && !$hasEmbeddedKey && str_contains($u, 'basemaps.cartocdn.com')) {
                $sep = str_contains($custom, '?') ? '&' : '?';
                $custom .= $sep . 'key=' . rawurlencode($apiKey);
            }
            return [
                'url' => $custom,
                'attribution' => '&copy; <a href="https://www.openstreetmap.org/copyright" target="_blank" rel="noopener">OpenStreetMap</a>',
                'provider' => 'custom',
            ];
        }

        if ($provider === 'osm') {
            return $osm;
        }

        // default: OSM (no key). Admin can choose CARTO + paste a free CARTO key.
        return $osm;
    }

    public static function geocodeConfig(PDO $pdo): array
    {
        return [
            'provider' => self::setting($pdo, 'map_geocode_provider', 'nominatim'),
            'api_key' => self::setting($pdo, 'map_geocode_api_key', ''),
        ];
    }
}
