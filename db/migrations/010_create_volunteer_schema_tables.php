<?php

return [
    'version'     => 10,
    'description' => 'Create dynamic schema tables for volunteer form builder',
    'up'          => function (PDO $pdo) {
        $pdo->exec("
            CREATE TABLE IF NOT EXISTS volunteer_columns (
                id INT AUTO_INCREMENT PRIMARY KEY,
                column_name VARCHAR(255) NOT NULL,
                data_type VARCHAR(50) NOT NULL DEFAULT 'VARCHAR',
                field_subtype VARCHAR(50) NULL,
                field_options TEXT NULL,
                allow_multiple TINYINT(1) DEFAULT 0,
                max_length INT NULL,
                boolean_display_format VARCHAR(50) DEFAULT 'yes_no',
                sort_order INT DEFAULT 0,
                is_required TINYINT(1) DEFAULT 0,
                created_by INT NULL,
                created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
            ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;
        ");

        $pdo->exec("
            CREATE TABLE IF NOT EXISTS volunteer_submissions (
                id INT AUTO_INCREMENT PRIMARY KEY,
                created_by INT NULL,
                created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
            ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;
        ");

        $pdo->exec("
            CREATE TABLE IF NOT EXISTS volunteer_submission_values (
                id INT AUTO_INCREMENT PRIMARY KEY,
                submission_id INT NOT NULL,
                column_id INT NOT NULL,
                value_content TEXT NULL,
                FOREIGN KEY (submission_id) REFERENCES volunteer_submissions(id) ON DELETE CASCADE,
                FOREIGN KEY (column_id) REFERENCES volunteer_columns(id) ON DELETE CASCADE
            ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;
        ");
    },
];
