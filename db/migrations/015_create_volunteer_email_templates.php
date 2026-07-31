<?php

return [
    'version'     => 15,
    'description' => 'Create volunteer email templates table for customizable trigger notifications',
    'up'          => function (PDO $pdo) {
        $pdo->exec("
            CREATE TABLE IF NOT EXISTS volunteer_email_templates (
                id INT AUTO_INCREMENT PRIMARY KEY,
                trigger_event VARCHAR(100) NOT NULL UNIQUE,
                template_name VARCHAR(255) NOT NULL,
                subject VARCHAR(255) NOT NULL,
                body TEXT NOT NULL,
                updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP
            )
        ");

        // Insert sensible default templates
        $pdo->exec("
            INSERT IGNORE INTO volunteer_email_templates (trigger_event, template_name, subject, body) VALUES 
            ('submission_received', 'Application Received Auto-Responder', 'We have received your volunteer application!', 'Dear {first_name},\n\nThank you for applying to volunteer with {system_name}! We have received your application and are reviewing your details.\n\nBest regards,\nThe Team'),
            ('chat_scheduled', 'Interview / Chat Scheduled Notice', 'Volunteer Chat Scheduled for #{submission_id}', 'Dear {first_name},\n\nYour volunteer interview chat has been scheduled. We look forward to speaking with you!\n\nBest regards,\nThe Team'),
            ('application_accepted', 'Application Accepted & Invite', 'Welcome to {system_name} - Setup Your Account', 'Dear {first_name},\n\nCongratulations! Your volunteer application has been accepted. You can now setup your user account using the credentials provided by your administrator.\n\nWelcome aboard!')
        ");
    },
];
