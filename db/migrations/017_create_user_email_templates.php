<?php
// db/migrations/017_create_user_email_templates.php

return [
    'version'     => 17,
    'description' => 'Create user email templates table for customizable user invitation notifications',
    'up'          => function (PDO $pdo) {
        $pdo->exec("
            CREATE TABLE IF NOT EXISTS user_email_templates (
                id INT AUTO_INCREMENT PRIMARY KEY,
                trigger_event VARCHAR(100) NOT NULL UNIQUE,
                template_name VARCHAR(255) NOT NULL,
                subject VARCHAR(255) NOT NULL,
                body TEXT NOT NULL,
                updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP
            )
        ");

        $pdo->exec("
            INSERT IGNORE INTO user_email_templates (trigger_event, template_name, subject, body) VALUES 
            ('user_invitation', 'User Account Invitation Template', 'You have been invited to join {system_name}', 'Hello {first_name},\n\nYou have been invited to join {system_name} as a {role_name}.\n\nYour assigned username is: {username}\n\nPlease click the secure link below to set your password and activate your account (valid for 24 hours):\n\n{invite_link}\n\nBest regards,\nThe Team')
        ");
    },
];
