<?php
declare(strict_types=1);

use FastRoute\RouteCollector;

return function (RouteCollector $r): void {

    // --- Public Routes ---
    $r->addRoute('GET', '/', ['App\Controllers\HomeController', 'index']);
    $r->addRoute('GET', '/leaderboard', ['App\Controllers\LeaderboardController', 'index']);
    $r->addRoute('GET', '/volunteer', ['App\Controllers\Public\VolunteerController', 'index']);
    $r->addRoute('POST', '/volunteer', ['App\Controllers\UserSavePublicVolunteerActionController', 'handle']);
    $r->addRoute('POST', '/volunteer/check-username', ['App\Controllers\UserSavePublicVolunteerActionController', 'checkUsername']);
    $r->addRoute('GET', '/feedback', ['App\Controllers\Public\FeedbackController', 'index']);
    $r->addRoute('POST', '/feedback', ['App\Controllers\UserSavePublicTicketActionController', 'handle']);

    // --- Authentication & User Routes ---
    $r->addRoute('GET', '/login', ['App\Controllers\UserLoginController', 'show']);
    $r->addRoute('POST', '/login', ['App\Controllers\UserAuthenticateActionController', 'authenticate']);
    $r->addRoute('GET', '/logout', ['App\Controllers\UserLogoutController', 'logout']);
    $r->addRoute('GET', '/register', ['App\Controllers\UserRegisterController', 'show']);
    $r->addRoute('POST', '/register', ['App\Controllers\UserRegisterActionController', 'register']);
    $r->addRoute('GET', '/user/set-password', ['App\Controllers\UserSetPasswordController', 'show']);
    $r->addRoute('POST', '/user/set-password', ['App\Controllers\UserSetPasswordActionController', 'save']);
    $r->addRoute('GET', '/forgot-password', ['App\Controllers\UserForgotPasswordController', 'show']);
    $r->addRoute('POST', '/forgot-password', ['App\Controllers\UserForgotPasswordActionController', 'handle']);
    $r->addRoute('GET', '/profile', ['App\Controllers\UserProfileController', 'show']);
    $r->addRoute('POST', '/profile', ['App\Controllers\UserProfileActionController', 'handle']);
    $r->addRoute('GET', '/user/onboarding', ['App\Controllers\UserOnboardingController', 'show']);
    $r->addRoute('POST', '/user/onboarding', ['App\Controllers\UserOnboardingActionController', 'save']);
    $r->addRoute('GET', '/setup-2fa', ['App\Controllers\UserSetup2faController', 'show']);
    $r->addRoute('POST', '/setup-2fa', ['App\Controllers\UserSetup2faActionController', 'handle']);
    $r->addRoute('GET', '/user/verify-2fa', ['App\Controllers\UserVerify2faController', 'show']);
    $r->addRoute('POST', '/user/verify-2fa', ['App\Controllers\UserVerify2faActionController', 'handle']);

    // Data Entry Workstation & Actions
    $r->addRoute('GET', '/data-entry', ['App\Controllers\UserDataEntryController', 'index']);
    $r->addRoute('POST', '/data-entry', ['App\Controllers\UserDataEntryActionController', 'save']);

    // --- Admin Dashboard & Management Routes ---
    $r->addRoute('GET', '/admin', ['App\Controllers\AdminSettingsController', 'index']);
    $r->addRoute('GET', '/admin/settings', ['App\Controllers\AdminSettingsController', 'index']);
    $r->addRoute('POST', '/admin', ['App\Controllers\AdminSettingsController', 'store']);

    // Users
    $r->addRoute('GET', '/admin/users', ['App\Controllers\AdminUsersController', 'index']);
    $r->addRoute('GET', '/admin/users/create', ['App\Controllers\AdminUsersController', 'createForm']);
    $r->addRoute('POST', '/admin/users', ['App\Controllers\AdminUsersController', 'handleAction']);
    $r->addRoute('POST', '/admin/users/create', ['App\Controllers\AdminUserActionController', 'create']);

    $r->addRoute('GET', '/admin/moderation', ['App\Controllers\ModerationController', 'index']);
    $r->addRoute('POST', '/admin/moderation', ['App\Controllers\ModerationController', 'store']);
    $r->addRoute('POST', '/admin/modules', ['App\Controllers\AdminModulesController', 'save']);
    $r->addRoute('GET', '/admin/tables', ['App\Controllers\TableManagerController', 'index']);
    $r->addRoute('POST', '/admin/tables', ['App\Controllers\TableManagerController', 'store']);

    // User Email Template Management Routes (Admin)
    $r->addRoute('GET', '/admin/users/emails', ['App\Controllers\UserEmailController', 'index']);
    $r->addRoute('POST', '/admin/users/emails/store', ['App\Controllers\UserEmailController', 'store']);

    // Volunteer Email Template Management Routes (Admin)
    $r->addRoute('GET', '/admin/volunteers/emails', ['App\Controllers\VolunteerEmailController', 'index']);
    $r->addRoute('POST', '/admin/volunteers/emails/store', ['App\Controllers\VolunteerEmailController', 'store']);

    // Volunteer Schema Management Routes (Admin)
    $r->addRoute('GET', '/admin/volunteers/schema', ['App\Controllers\VolunteerSchemaController', 'index']);
    $r->addRoute('POST', '/admin/volunteers/schema/store', ['App\Controllers\VolunteerSchemaController', 'store']);

    // Ticket Management Routes
    $r->addRoute('GET', '/admin/tickets', ['App\Controllers\FeedbackController', 'index']);
    $r->addRoute('POST', '/admin/tickets', ['App\Controllers\FeedbackController', 'index']);
    $r->addRoute('GET', '/admin/tickets/{id:\d+}', ['App\Controllers\AdminTicketController', 'show']);
    $r->addRoute('POST', '/admin/tickets/action', ['App\Controllers\AdminTicketController', 'handleAction']);

    $r->addRoute('GET', '/admin/volunteers', ['App\Controllers\AdminVolunteerController', 'index']);
    $r->addRoute('POST', '/admin/volunteers', ['App\Controllers\AdminVolunteerController', 'handleAction']);

    // Feedback Email Template Management Routes (Admin)
    $r->addRoute('GET', '/admin/feedback/emails', ['App\Controllers\FeedbackEmailController', 'index']);
    $r->addRoute('POST', '/admin/feedback/emails/store', ['App\Controllers\FeedbackEmailController', 'store']);

    // Feedback Schema Management Routes (Admin)
    $r->addRoute('GET', '/admin/feedback/schema', ['App\Controllers\FeedbackSchemaController', 'index']);
    $r->addRoute('POST', '/admin/feedback/schema/store', ['App\Controllers\FeedbackSchemaController', 'store']);

    // --- System / Tools & Records ---
    $r->addRoute('GET', '/update-database', ['App\Controllers\UpdateDatabaseController', 'index']);
    $r->addRoute('POST', '/update-database', ['App\Controllers\UpdateDatabaseController', 'index']);
    $r->addRoute('GET', '/user/suggest-edit', ['App\Controllers\UserSuggestEditController', 'show']);
    $r->addRoute('POST', '/user/suggest-edit/save', ['App\Controllers\UserSuggestEditActionController', 'save']);
    $r->addRoute('GET', '/record_history.php', ['App\Controllers\RecordHistoryController', 'index']);
    $r->addRoute('GET', '/api/export', ['App\Controllers\ApiExportController', 'export']);
    $r->addRoute('GET', '/api/export-json', ['App\Controllers\ApiExportJsonController', 'export']);
    $r->addRoute('GET', '/api/search', ['App\Controllers\ApiSearchController', 'search']);
    $r->addRoute('POST', '/purge_audit_entry', ['App\Controllers\UserPurgeAuditEntryActionController', 'purge']);
    $r->addRoute('POST', '/admin/backup/download', ['App\Controllers\AdminBackupController', 'downloadBackup']);
    $r->addRoute('POST', '/admin/audit/purge', ['App\Controllers\AdminAuditController', 'purge']);
    $r->addRoute('POST', '/admin/maintenance/save', ['App\Controllers\AdminMaintenanceController', 'save']);
    $r->addRoute('POST', '/admin/cron/token-cleanup', ['App\Controllers\AdminCronController', 'runTokenCleanup']);
    $r->addRoute('POST', '/admin/migration/run', ['App\Controllers\AdminMigrationController', 'run']);
    $r->addRoute('POST', '/admin/roles/save', ['App\Controllers\AdminRoleActionController', 'handle']);
    $r->addRoute('POST', '/admin/mail/test', ['App\Controllers\AdminTestMailController', 'send']);
    $r->addRoute('POST', '/admin/notices/inline-save', ['App\Controllers\AdminNoticeActionController', 'handle']);
    $r->addRoute('GET',  '/admin/notices', ['App\Controllers\NoticeController', 'index']);
    $r->addRoute('POST', '/admin/notices', ['App\Controllers\NoticeController', 'store']);
    $r->addRoute('POST', '/admin/permissions/save', ['App\Controllers\AdminPermissionsActionController', 'save']);
};
