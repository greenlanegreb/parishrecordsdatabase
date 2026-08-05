<?php

return [
    'version'     => 16,
    'description' => 'Create feedback email templates table for customizable support ticket notifications',
    'up'          => function (PDO $pdo) {
        $pdo->exec("
            CREATE TABLE IF NOT EXISTS feedback_email_templates (
                id INT AUTO_INCREMENT PRIMARY KEY,
                trigger_event VARCHAR(100) NOT NULL UNIQUE,
                template_name VARCHAR(255) NOT NULL,
                subject VARCHAR(255) NOT NULL,
                body TEXT NOT NULL,
                updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP
            )
        ");

        $pdo->exec("
            INSERT IGNORE INTO feedback_email_templates (trigger_event, template_name, subject, body) VALUES 
            ('ticket_received', 'Support Ticket Received Auto-Responder', 'Support Ticket #{ticket_id} Received: {subject}', 'Dear {first_name},\n\nThank you for reaching out to {system_name} support. We have received your ticket regarding \"{subject}\" and our team will get back to you shortly.\n\nBest regards,\nThe Support Team'),
            ('ticket_replied', 'Ticket Admin Reply Notification', 'Update on Support Ticket #{ticket_id}', 'Dear {first_name},\n\nAn administrator has posted a new reply to your support ticket #{ticket_id}.\n\nLog in to view the dialogue and respond.\n\nBest regards,\nThe Support Team'),
            ('ticket_completed', 'Support Ticket Resolved Notice', 'Support Ticket #{ticket_id} Has Been Resolved', 'Dear {first_name},\n\nYour support ticket #{ticket_id} has been marked as Completed / Resolved. If you need further assistance, feel free to open a new ticket.\n\nBest regards,\nThe Support Team')
        ");
    },
];
