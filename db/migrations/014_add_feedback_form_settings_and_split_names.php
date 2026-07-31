<?php

return [
    'version'     => 14,
    'description' => 'Split feedback_tickets name into first_name and surname, and add form settings',
    'up'          => function (PDO $pdo) {
        // Check if name column exists before dropping/splitting to prevent errors
        $columns = $pdo->query("SHOW COLUMNS FROM feedback_tickets LIKE 'name'")->fetchAll();
        if (!empty($columns)) {
            $pdo->exec("
                ALTER TABLE feedback_tickets 
                DROP COLUMN name,
                ADD COLUMN first_name VARCHAR(100) NOT NULL DEFAULT '' AFTER user_id,
                ADD COLUMN surname VARCHAR(100) NOT NULL DEFAULT '' AFTER first_name
            ");
        }

        $pdo->exec("
            CREATE TABLE IF NOT EXISTS feedback_form_settings (
                setting_key VARCHAR(100) PRIMARY KEY,
                setting_value TEXT NOT NULL
            )
        ");

        $pdo->exec("
            INSERT IGNORE INTO feedback_form_settings (setting_key, setting_value) VALUES 
            ('form_title', 'Submit Support Ticket or Feedback'),
            ('form_intro', 'Fill out the form below to open a ticket with our team. Fields marked with * are mandatory.')
        ");
    },
];
