<?php
declare(strict_types=1);
/**
 * Map pins: optional hide from map (record stays in table).
 */
return [
    'version' => 37,
    'description' => 'map_pins.hide_from_map for excluding places from the map without deleting data',
    'up' => static function (PDO $pdo): void {
        $stmt = $pdo->query("SHOW COLUMNS FROM map_pins LIKE 'hide_from_map'");
        if ($stmt === false || $stmt->fetch() === false) {
            $pdo->exec(
                'ALTER TABLE map_pins ADD COLUMN hide_from_map TINYINT(1) NOT NULL DEFAULT 0 AFTER color'
            );
        }
    },
];
