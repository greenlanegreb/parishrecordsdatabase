<?php
declare(strict_types=1);
/**
 * MIGRATED FILE MAPPING
 * ---------------------
 * Original Old File: db/permissions_registry.php
 * Migrated Date: 2026-08-05 12:45:00
 */

// db/permissions_registry.php - Central Registry of Application Permissions & Default Roles

/** @return array{permissions: array<string, string>, default_roles: array<string, array<int, string>>} */
return [
    // 1. The complete master list of all static application permissions (alphabetical)
    'permissions' => [
        'access_data_entry'    => 'Allows accessing the core data entry workstation',
        'access_onboarding'    => 'Allows accessing the first-time user onboarding setup',
        'access_profile'       => 'Allows viewing and managing personal user profile settings',
        'access_suggest_edit'  => 'Allows submitting edit suggestions for records',
        'export_data'          => 'Export data from records table',
        'invite_users'         => 'Create and invite new users',
        'manage_audit_logs'    => 'Allows viewing and managing the global system-wide audit logs',
        'manage_columns'       => 'Configure table columns',
        'manage_feedback'      => 'Manage feedback',
        'manage_notices'       => 'Manage PRD Front Page Notices',
        'manage_settings'      => 'Manage global settings',
        'manage_tables'        => 'Manage dynamic database tables and column schema definitions',
        'manage_users'         => 'Manage user accounts, roles, and status',
        'manage_volunteers'    => 'Manage volunteers',
        'moderate_suggestions' => 'Approve, reject, or override user edit suggestions',
        'purge_audit_entry'    => 'Allows purging individual audit log entries from records',
        'submit_feedback'      => 'Allows submitting public feedback and inquiries',
        'submit_volunteer'     => 'Allows submitting volunteer interest and transcripts',
        'view_as_guest'        => 'View and search records',
        'view_leaderboard'     => 'Allows viewing community contribution leaderboards',
        'view_user_full_names' => 'Controls Visibility of Volunteers Full Names Being Seen Instead of Obscured',
    ],

    // 2. The default permissions assigned to core roles during installation (alphabetical)
    'default_roles' => [
        'admin' => [
            'access_data_entry',
            'access_onboarding',
            'access_profile',
            'access_suggest_edit',
            'export_data',
            'invite_users',
            'manage_audit_logs',
            'manage_columns',
            'manage_feedback',
            'manage_notices',
            'manage_settings',
            'manage_tables',
            'manage_users',
            'manage_volunteers',
            'moderate_suggestions',
            'purge_audit_entry',
            'submit_feedback',
            'submit_volunteer',
            'view_leaderboard',
            'view_as_guest',
            'view_user_full_names',
        ],
        'moderator' => [
            'access_data_entry',
            'access_onboarding',
            'access_profile',
            'access_suggest_edit',
            'export_data',
            'moderate_suggestions',
            'submit_feedback',
            'submit_volunteer',
            'view_leaderboard',
            'view_as_guest',
        ],
        'user' => [
            'access_data_entry',
            'access_onboarding',
            'access_profile',
            'access_suggest_edit',
            'export_data',
            'submit_feedback',
            'submit_volunteer',
            'view_leaderboard',
            'view_as_guest',
        ],
        'guest' => [
            'export_data',
            'submit_feedback',
            'submit_volunteer',
            'view_leaderboard',
            'view_as_guest',
        ]
    ]
];
