<?php

return [
    'version'     => 7,
    'description' => 'Add schema-driven feedback columns, tickets, ticket values, and threaded dialogue reply tables',
    'up'          => function (PDO $pdo) {
        // 1. Feedback Custom Form Fields Definition Table
        $pdo->exec("
            CREATE TABLE IF NOT EXISTS feedback_columns (
                id INT AUTO_INCREMENT PRIMARY KEY,
                column_name VARCHAR(100) NOT NULL,
                data_type VARCHAR(50) NOT NULL DEFAULT 'VARCHAR',
                boolean_display_format VARCHAR(50) DEFAULT 'yes_no',
                sort_order INT NOT NULL DEFAULT 0,
                is_required TINYINT(1) DEFAULT 0,
                created_by INT NULL,
                created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
            ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;
        ");

        // 2. Parent Tickets / Submissions Table
        $pdo->exec("
            CREATE TABLE IF NOT EXISTS feedback_tickets (
                id INT AUTO_INCREMENT PRIMARY KEY,
                user_id INT NULL,
                name VARCHAR(150) NOT NULL,
                email VARCHAR(150) NOT NULL,
                subject VARCHAR(255) NOT NULL,
                status VARCHAR(50) DEFAULT 'Pending',
                created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
            ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;
        ");

        // 3. Ticket Dynamic Field Values Table
        $pdo->exec("
            CREATE TABLE IF NOT EXISTS feedback_ticket_values (
                id INT AUTO_INCREMENT PRIMARY KEY,
                ticket_id INT NOT NULL,
                column_id INT NOT NULL,
                value_content TEXT NULL,
                INDEX (ticket_id),
                INDEX (column_id)
            ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;
        ");

        // 4. Threaded Ticket Replies & Dialogue Table
        $pdo->exec("
            CREATE TABLE IF NOT EXISTS feedback_ticket_replies (
                id INT AUTO_INCREMENT PRIMARY KEY,
                ticket_id INT NOT NULL,
                user_id INT NULL,
                message TEXT NOT NULL,
                is_admin_reply TINYINT(1) DEFAULT 0,
                created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
                INDEX (ticket_id)
            ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;
        ");
    },
];
