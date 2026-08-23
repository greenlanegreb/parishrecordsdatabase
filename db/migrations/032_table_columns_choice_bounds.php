<?php
declare(strict_types=1);
return [
    'version' => 32,
    'description' => 'Ensure choice-list and integer bound columns exist on table_columns (covers baseline installs that skipped 028)',
    'up' => static function (PDO $pdo): void {
        $add = static function (PDO $pdo, string $column, string $ddl): void {
            $stmt = $pdo->query('SHOW COLUMNS FROM table_columns LIKE ' . $pdo->quote($column));
            if ($stmt === false || $stmt->fetch() === false) {
                $pdo->exec('ALTER TABLE table_columns ADD COLUMN ' . $ddl);
            }
        };
        $add($pdo, 'field_options', 'field_options TEXT NULL');
        $add($pdo, 'allow_multiple', 'allow_multiple TINYINT(1) NOT NULL DEFAULT 0');
        $add($pdo, 'min_value', 'min_value INT NULL DEFAULT NULL');
        $add($pdo, 'max_value', 'max_value INT NULL DEFAULT NULL');
    },
];
