<?php
declare(strict_types=1);
return [
    'version' => 31,
    'description' => 'Maps: geocode cache, map pins, module flag, optional tile URL',
    'up' => static function (PDO $pdo): void {
        $pdo->exec(
            "CREATE TABLE IF NOT EXISTS place_geocode_cache (
                id INT NOT NULL AUTO_INCREMENT,
                query_key VARCHAR(191) NOT NULL,
                lat DECIMAL(10,7) NOT NULL,
                lng DECIMAL(10,7) NOT NULL,
                suggested_label VARCHAR(255) NOT NULL DEFAULT '',
                payload TEXT NULL,
                created_at TIMESTAMP NULL DEFAULT CURRENT_TIMESTAMP,
                PRIMARY KEY (id),
                UNIQUE KEY query_key (query_key)
            ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci"
        );
        $pdo->exec(
            "CREATE TABLE IF NOT EXISTS map_pins (
                id INT NOT NULL AUTO_INCREMENT,
                table_id INT NOT NULL,
                record_id INT NOT NULL,
                column_id INT NOT NULL,
                lat DECIMAL(10,7) NOT NULL,
                lng DECIMAL(10,7) NOT NULL,
                label VARCHAR(255) NOT NULL DEFAULT '',
                title VARCHAR(255) NOT NULL DEFAULT '',
                body TEXT NULL,
                color VARCHAR(16) NOT NULL DEFAULT '#c0392b',
                updated_at TIMESTAMP NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
                PRIMARY KEY (id),
                UNIQUE KEY pin_record_col (record_id, column_id),
                KEY table_bbox (table_id, lat, lng)
            ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci"
        );
        $ins = $pdo->prepare(
            'INSERT IGNORE INTO site_settings (setting_key, setting_value) VALUES (?, ?)'
        );
        $ins->execute(['module_maps_enabled', '1']);
        $ins->execute(['map_tile_url', '']);
    },
];
