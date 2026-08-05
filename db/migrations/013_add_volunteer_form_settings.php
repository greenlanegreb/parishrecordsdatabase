<?php

return [
    'version'     => 13,
    'description' => 'Split volunteer name into first_name and surname, and add form title/intro settings',
    'up'          => function (PDO $pdo) {
        // Drop old single name column if it exists and add first_name / surname
        $pdo->exec("
            ALTER TABLE volunteer_submissions 
            DROP COLUMN name,
            ADD COLUMN first_name VARCHAR(100) NOT NULL DEFAULT '' AFTER id,
            ADD COLUMN surname VARCHAR(100) NOT NULL DEFAULT '' AFTER first_name
        ");

        // Create a settings table or columns for volunteer form metadata if not already present
        // Using a lightweight key-value options table or a dedicated settings table check
        $pdo->exec("
            CREATE TABLE IF NOT EXISTS volunteer_form_settings (
                setting_key VARCHAR(100) PRIMARY KEY,
                setting_value TEXT NOT NULL
            )
        ");

        // Insert default form settings
        $pdo->exec("
            INSERT IGNORE INTO volunteer_form_settings (setting_key, setting_value) VALUES 
            ('form_title', 'Volunteer for Data Entry'),
            ('form_intro', 'Interested in helping transcribe and contribute? Let us know a little about yourself and any relevant experience.')
        ");
    },
];
