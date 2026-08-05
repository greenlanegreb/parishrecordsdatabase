<?php
// db/migrations/018_add_password_reset_email_template.php

return [
    'version'     => 18,
    'description' => 'Add password reset and access link email template to user_email_templates table',
    'up'          => function (PDO $pdo) {
        $pdo->exec("
            INSERT IGNORE INTO user_email_templates (trigger_event, template_name, subject, body) VALUES 
            ('password_reset', 'Password Reset / Access Link Template', 'Password reset request for {system_name}', 'Hello {first_name},\n\nA request has been made to reset your password or access your account on {system_name}.\n\nPlease click the secure link below to set a new password (valid for 24 hours):\n\n{invite_link}\n\nIf you did not request this, please ignore this email.\n\nBest regards,\nThe Team')
        ");
    },
];
