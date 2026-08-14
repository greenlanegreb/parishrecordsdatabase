<?php
/**
 * db/seed_defaults.php
 *
 * Idempotent product defaults for fresh installs (and optional "fill missing" later).
 * - Email templates (user / feedback / volunteer)
 * - Public form title/intro settings
 *
 * Safe to call multiple times: all inserts use INSERT IGNORE on unique keys.
 */
declare(strict_types=1);

/**
 * Seed default email templates and form settings if missing.
 */
function seed_application_defaults(PDO $pdo): void
{
    seed_default_form_settings($pdo);
    seed_default_user_email_templates($pdo);
    seed_default_feedback_email_templates($pdo);
    seed_default_volunteer_email_templates($pdo);
}

function seed_default_form_settings(PDO $pdo): void
{
    $pairs = [
        'volunteer_form_settings' => [
            'form_title' => 'Volunteer for Data Entry',
            'form_intro' => 'Interested in helping transcribe and contribute? Let us know a little about yourself and any relevant experience.',
        ],
        'feedback_form_settings' => [
            'form_title' => 'Submit Support Ticket or Feedback',
            'form_intro' => 'Fill out the form below to open a ticket with our team.',
        ],
    ];

    foreach ($pairs as $table => $settings) {
        try {
            $stmt = $pdo->prepare(
                "INSERT IGNORE INTO {$table} (setting_key, setting_value) VALUES (?, ?)"
            );
            foreach ($settings as $key => $value) {
                $stmt->execute([$key, $value]);
            }
        } catch (Throwable $e) {
            // Table may not exist on very old DBs; install baseline includes these tables.
        }
    }
}

function seed_default_user_email_templates(PDO $pdo): void
{
    $rows = [
        [
            'user_invitation',
            'User Account Invitation Template',
            'You have been invited to join {system_name}',
            "Hello {first_name},\n\nYou have been invited to join {system_name} as a {role_name}.\n\nYour assigned username is: {username}\n\nPlease click the secure link below to set your password and activate your account (valid for 24 hours):\n\n{invite_link}\n\nBest regards,\nThe Team",
        ],
        [
            'password_reset',
            'Password Reset / Access Link Template',
            'Password reset request for {system_name}',
            "Hello {first_name},\n\nA request has been made to reset your password or access your account on {system_name}.\n\nPlease click the secure link below to set a new password (valid for 24 hours):\n\n{invite_link}\n\nIf you did not request this, please ignore this email.\n\nBest regards,\nThe Team",
        ],
    ];

    try {
        $stmt = $pdo->prepare(
            'INSERT IGNORE INTO user_email_templates (trigger_event, template_name, subject, body) VALUES (?, ?, ?, ?)'
        );
        foreach ($rows as $row) {
            $stmt->execute($row);
        }
    } catch (Throwable $e) {
        // ignore if table missing
    }
}

function seed_default_feedback_email_templates(PDO $pdo): void
{
    $rows = [
        [
            'ticket_received',
            'Support Ticket Received Auto-Responder',
            'Support Ticket #{ticket_id} Received: {subject}',
            "Dear {first_name},\n\nThank you for reaching out to {system_name} support. We have received your ticket regarding \"{subject}\" and our team will get back to you shortly.\n\nBest regards,\nThe Support Team",
        ],
        [
            'ticket_replied',
            'Ticket Admin Reply Notification',
            'Update on Support Ticket #{ticket_id}',
            "Dear {first_name},\n\nAn administrator has posted a new reply to your support ticket #{ticket_id}.\n\nLog in to view the dialogue and respond.\n\nBest regards,\nThe Support Team",
        ],
        [
            'ticket_completed',
            'Support Ticket Resolved Notice',
            'Support Ticket #{ticket_id} Has Been Resolved',
            "Dear {first_name},\n\nYour support ticket #{ticket_id} has been marked as Completed / Resolved. If you need further assistance, feel free to open a new ticket.\n\nBest regards,\nThe Support Team",
        ],
    ];

    try {
        $stmt = $pdo->prepare(
            'INSERT IGNORE INTO feedback_email_templates (trigger_event, template_name, subject, body) VALUES (?, ?, ?, ?)'
        );
        foreach ($rows as $row) {
            $stmt->execute($row);
        }
    } catch (Throwable $e) {
        // ignore
    }
}

function seed_default_volunteer_email_templates(PDO $pdo): void
{
    $rows = [
        [
            'submission_received',
            'Application Received Auto-Responder',
            'We have received your volunteer application!',
            "Dear {first_name},\n\nThank you for applying to volunteer with {system_name}! We have received your application and are reviewing your details.\n\nBest regards,\nThe Team",
        ],
        [
            'chat_scheduled',
            'Interview / Chat Scheduled Notice',
            'Volunteer Chat Scheduled for #{submission_id}',
            "Dear {first_name},\n\nYour volunteer interview chat has been scheduled. We look forward to speaking with you!\n\nBest regards,\nThe Team",
        ],
        [
            'application_accepted',
            'Application Accepted & Invite',
            'Welcome to {system_name} - Setup Your Account',
            "Dear {first_name},\n\nCongratulations! Your volunteer application has been accepted. You can now setup your user account using the credentials provided by your administrator.\n\nWelcome aboard!",
        ],
    ];

    try {
        $stmt = $pdo->prepare(
            'INSERT IGNORE INTO volunteer_email_templates (trigger_event, template_name, subject, body) VALUES (?, ?, ?, ?)'
        );
        foreach ($rows as $row) {
            $stmt->execute($row);
        }
    } catch (Throwable $e) {
        // ignore
    }
}
