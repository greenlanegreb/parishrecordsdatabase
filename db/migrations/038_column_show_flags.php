<?php
declare(strict_types=1);

return [
    'version' => 38,
    'description' => 'Column flags: show on list vs full record page',
    'up' => static function (PDO $pdo): void {
        $add = static function (PDO $pdo, string $column, string $ddl): void {
            $stmt = $pdo->query('SHOW COLUMNS FROM table_columns LIKE ' . $pdo->quote($column));
            if ($stmt === false || $stmt->fetch() === false) {
                $pdo->exec('ALTER TABLE table_columns ADD COLUMN ' . $ddl);
            }
        };
        $add($pdo, 'show_in_list', 'show_in_list TINYINT(1) NOT NULL DEFAULT 1');
        $add($pdo, 'show_in_record', 'show_in_record TINYINT(1) NOT NULL DEFAULT 1');
    },
];
