<?php
declare(strict_types=1);

return [
    'version' => 39,
    'description' => 'Min/max bounds on volunteer and feedback whole-number fields',
    'up' => static function (PDO $pdo): void {
        $add = static function (PDO $pdo, string $table, string $column, string $ddl): void {
            $stmt = $pdo->query('SHOW COLUMNS FROM `' . str_replace('`', '', $table) . '` LIKE ' . $pdo->quote($column));
            if ($stmt === false || $stmt->fetch() === false) {
                $pdo->exec('ALTER TABLE `' . str_replace('`', '', $table) . '` ADD COLUMN ' . $ddl);
            }
        };
        $add($pdo, 'volunteer_columns', 'min_value', 'min_value INT NULL DEFAULT NULL AFTER max_length');
        $add($pdo, 'volunteer_columns', 'max_value', 'max_value INT NULL DEFAULT NULL AFTER min_value');
        $add($pdo, 'feedback_columns', 'min_value', 'min_value INT NULL DEFAULT NULL AFTER max_length');
        $add($pdo, 'feedback_columns', 'max_value', 'max_value INT NULL DEFAULT NULL AFTER min_value');
    },
];
