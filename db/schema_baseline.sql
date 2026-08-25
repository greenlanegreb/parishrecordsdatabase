-- PRD schema baseline (structure only)
-- Snapshot through schema version 27 (includes demo_artifacts).
-- Fresh installs: import this, seed roles, bootstrap permissions, seed_defaults,
-- then set schema_version = 27 and run db/migrate_runner.php for versions > 27.
-- Do not re-run migrations 001–027 on a brand-new install (already in this file).
-- When regenerating this file to a higher snapshot, bump install_baseline_schema_version() to match.
SET NAMES utf8mb4;
SET SQL_MODE = "NO_AUTO_VALUE_ON_ZERO";
SET time_zone = "+00:00";
SET FOREIGN_KEY_CHECKS = 0;
-- --------------------------------------------------------
-- Table structure for table `audit_logs`
-- --------------------------------------------------------
DROP TABLE IF EXISTS `audit_logs`;
CREATE TABLE IF NOT EXISTS `audit_logs` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `user_id` int(11) DEFAULT NULL,
  `action` varchar(50) NOT NULL,
  `record_id` int(11) DEFAULT NULL,
  `details` text DEFAULT NULL,
  `ip_address` varchar(45) DEFAULT NULL,
  `created_at` timestamp NULL DEFAULT current_timestamp(),
  PRIMARY KEY (`id`),
  KEY `user_id` (`user_id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
-- --------------------------------------------------------
-- Table structure for table `dynamic_tables`
-- --------------------------------------------------------
DROP TABLE IF EXISTS `dynamic_tables`;
CREATE TABLE IF NOT EXISTS `dynamic_tables` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `table_name` varchar(64) NOT NULL,
  `description` text DEFAULT NULL,
  `created_by` int(11) DEFAULT NULL,
  `created_at` timestamp NULL DEFAULT current_timestamp(),
  PRIMARY KEY (`id`),
  UNIQUE KEY `table_name` (`table_name`),
  KEY `created_by` (`created_by`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
-- --------------------------------------------------------
-- Table structure for table `edit_suggestions`
-- --------------------------------------------------------
DROP TABLE IF EXISTS `edit_suggestions`;
CREATE TABLE IF NOT EXISTS `edit_suggestions` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `record_id` int(11) DEFAULT NULL,
  `suggested_by` int(11) DEFAULT NULL,
  `column_name` varchar(64) NOT NULL,
  `proposed_value` text NOT NULL,
  `reasoning` text DEFAULT NULL,
  `notify_outcome` tinyint(1) NOT NULL DEFAULT 0,
  `notify_email` varchar(255) DEFAULT NULL,
  `moderator_rationale` text DEFAULT NULL,
  `status` enum('pending','approved','rejected') DEFAULT 'pending',
  `points_awarded` tinyint(1) DEFAULT 0,
  `created_at` timestamp NULL DEFAULT current_timestamp(),
  PRIMARY KEY (`id`),
  KEY `suggested_by` (`suggested_by`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
-- --------------------------------------------------------
-- Table structure for table `feedback`
-- (legacy simple form; dynamic tickets live in feedback_tickets*)
-- --------------------------------------------------------
DROP TABLE IF EXISTS `feedback`;
CREATE TABLE IF NOT EXISTS `feedback` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `name` varchar(100) NOT NULL,
  `email` varchar(255) NOT NULL,
  `message` text NOT NULL,
  `created_at` timestamp NULL DEFAULT current_timestamp(),
  `status` varchar(50) DEFAULT 'Pending',
  `admin_notes` text DEFAULT NULL,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;
-- --------------------------------------------------------
-- Table structure for table `feedback_columns`
-- --------------------------------------------------------
DROP TABLE IF EXISTS `feedback_columns`;
CREATE TABLE IF NOT EXISTS `feedback_columns` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `column_name` varchar(100) NOT NULL,
  `data_type` varchar(50) NOT NULL DEFAULT 'VARCHAR',
  `field_subtype` varchar(50) DEFAULT NULL,
  `field_options` text DEFAULT NULL,
  `allow_multiple` tinyint(1) DEFAULT 0,
  `max_length` int(11) DEFAULT NULL,
  `boolean_display_format` varchar(50) DEFAULT 'yes_no',
  `sort_order` int(11) NOT NULL DEFAULT 0,
  `is_required` tinyint(1) DEFAULT 0,
  `created_by` int(11) DEFAULT NULL,
  `created_at` timestamp NULL DEFAULT current_timestamp(),
  PRIMARY KEY (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;
-- --------------------------------------------------------
-- Table structure for table `feedback_email_templates`
-- --------------------------------------------------------
DROP TABLE IF EXISTS `feedback_email_templates`;
CREATE TABLE IF NOT EXISTS `feedback_email_templates` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `trigger_event` varchar(100) NOT NULL,
  `template_name` varchar(255) NOT NULL,
  `subject` varchar(255) NOT NULL,
  `body` text NOT NULL,
  `updated_at` timestamp NULL DEFAULT current_timestamp() ON UPDATE current_timestamp(),
  PRIMARY KEY (`id`),
  UNIQUE KEY `trigger_event` (`trigger_event`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;
-- --------------------------------------------------------
-- Table structure for table `feedback_form_settings`
-- --------------------------------------------------------
DROP TABLE IF EXISTS `feedback_form_settings`;
CREATE TABLE IF NOT EXISTS `feedback_form_settings` (
  `setting_key` varchar(100) NOT NULL,
  `setting_value` text NOT NULL,
  PRIMARY KEY (`setting_key`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;
-- --------------------------------------------------------
-- Table structure for table `feedback_tickets`
-- --------------------------------------------------------
DROP TABLE IF EXISTS `feedback_tickets`;
CREATE TABLE IF NOT EXISTS `feedback_tickets` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `user_id` int(11) DEFAULT NULL,
  `first_name` varchar(100) NOT NULL DEFAULT '',
  `surname` varchar(100) NOT NULL DEFAULT '',
  `email` varchar(150) NOT NULL,
  `subject` varchar(255) NOT NULL,
  `status` varchar(50) DEFAULT 'Pending',
  `created_at` timestamp NULL DEFAULT current_timestamp(),
  PRIMARY KEY (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;
-- --------------------------------------------------------
-- Table structure for table `feedback_ticket_replies`
-- --------------------------------------------------------
DROP TABLE IF EXISTS `feedback_ticket_replies`;
CREATE TABLE IF NOT EXISTS `feedback_ticket_replies` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `ticket_id` int(11) NOT NULL,
  `user_id` int(11) DEFAULT NULL,
  `message` text NOT NULL,
  `is_admin_reply` tinyint(1) DEFAULT 0,
  `created_at` timestamp NULL DEFAULT current_timestamp(),
  PRIMARY KEY (`id`),
  KEY `ticket_id` (`ticket_id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;
-- --------------------------------------------------------
-- Table structure for table `feedback_ticket_values`
-- --------------------------------------------------------
DROP TABLE IF EXISTS `feedback_ticket_values`;
CREATE TABLE IF NOT EXISTS `feedback_ticket_values` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `ticket_id` int(11) NOT NULL,
  `column_id` int(11) NOT NULL,
  `value_content` text DEFAULT NULL,
  PRIMARY KEY (`id`),
  KEY `ticket_id` (`ticket_id`),
  KEY `column_id` (`column_id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;
-- --------------------------------------------------------
-- Table structure for table `permissions`
-- --------------------------------------------------------
DROP TABLE IF EXISTS `permissions`;
CREATE TABLE IF NOT EXISTS `permissions` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `permission_key` varchar(100) NOT NULL,
  `description` varchar(255) DEFAULT NULL,
  `created_at` timestamp NULL DEFAULT current_timestamp(),
  PRIMARY KEY (`id`),
  UNIQUE KEY `permission_key` (`permission_key`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;
-- --------------------------------------------------------
-- Table structure for table `records`
-- --------------------------------------------------------
DROP TABLE IF EXISTS `records`;
CREATE TABLE IF NOT EXISTS `records` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `table_id` int(11) NOT NULL DEFAULT 1,
  `created_by` int(11) DEFAULT NULL,
  `created_at` timestamp NULL DEFAULT current_timestamp(),
  `updated_at` timestamp NULL DEFAULT current_timestamp() ON UPDATE current_timestamp(),
  PRIMARY KEY (`id`),
  KEY `created_by` (`created_by`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
-- --------------------------------------------------------
-- Table structure for table `record_values`
-- --------------------------------------------------------
DROP TABLE IF EXISTS `record_values`;
CREATE TABLE IF NOT EXISTS `record_values` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `record_id` int(11) NOT NULL,
  `column_id` int(11) NOT NULL,
  `value_content` text DEFAULT NULL,
  PRIMARY KEY (`id`),
  KEY `record_id` (`record_id`),
  KEY `column_id` (`column_id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
-- --------------------------------------------------------
-- Table structure for table `roles`
-- --------------------------------------------------------
DROP TABLE IF EXISTS `roles`;
CREATE TABLE IF NOT EXISTS `roles` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `role_name` varchar(50) NOT NULL,
  `description` varchar(255) DEFAULT NULL,
  `created_at` timestamp NULL DEFAULT current_timestamp(),
  PRIMARY KEY (`id`),
  UNIQUE KEY `role_name` (`role_name`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;
-- --------------------------------------------------------
-- Table structure for table `role_permissions`
-- --------------------------------------------------------
DROP TABLE IF EXISTS `role_permissions`;
CREATE TABLE IF NOT EXISTS `role_permissions` (
  `role_id` int(11) NOT NULL,
  `permission_id` int(11) NOT NULL,
  PRIMARY KEY (`role_id`,`permission_id`),
  KEY `permission_id` (`permission_id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;
-- --------------------------------------------------------
-- Table structure for table `site_notices`
-- --------------------------------------------------------
DROP TABLE IF EXISTS `site_notices`;
CREATE TABLE IF NOT EXISTS `site_notices` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `title` varchar(255) NOT NULL,
  `content` text NOT NULL,
  `target_roles` varchar(255) DEFAULT 'everyone',
  `is_dismissible` tinyint(1) DEFAULT 1,
  `is_active` tinyint(1) DEFAULT 1,
  `display_order` int(11) DEFAULT 0,
  `created_at` timestamp NULL DEFAULT current_timestamp(),
  PRIMARY KEY (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;
-- --------------------------------------------------------
-- Table structure for table `site_settings`
-- --------------------------------------------------------
DROP TABLE IF EXISTS `site_settings`;
CREATE TABLE IF NOT EXISTS `site_settings` (
  `setting_key` varchar(100) NOT NULL,
  `setting_value` text NOT NULL,
  `updated_at` timestamp NULL DEFAULT current_timestamp() ON UPDATE current_timestamp(),
  PRIMARY KEY (`setting_key`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
-- --------------------------------------------------------
-- Table structure for table `table_columns`
-- --------------------------------------------------------
DROP TABLE IF EXISTS `table_columns`;
CREATE TABLE IF NOT EXISTS `table_columns` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `table_id` int(11) NOT NULL DEFAULT 1,
  `column_name` varchar(64) NOT NULL,
  `data_type` varchar(32) NOT NULL,
  `max_length` int(11) DEFAULT NULL,
  `is_required` tinyint(1) DEFAULT 0,
  `created_by` int(11) DEFAULT NULL,
  `created_at` timestamp NULL DEFAULT current_timestamp(),
  `boolean_display_format` varchar(32) DEFAULT 'yes_no',
  `sort_order` int(11) DEFAULT 0,
  `updated_at` timestamp NULL DEFAULT current_timestamp() ON UPDATE current_timestamp(),
  `date_search_behavior` varchar(50) DEFAULT 'manual_only',
  `exclude_from_public_search` tinyint(1) DEFAULT 0,
  `field_options` text DEFAULT NULL,
  `allow_multiple` tinyint(1) NOT NULL DEFAULT 0,
  `min_value` int(11) DEFAULT NULL,
  `max_value` int(11) DEFAULT NULL,
  PRIMARY KEY (`id`),
  KEY `created_by` (`created_by`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
-- --------------------------------------------------------
-- Table structure for table `users`
-- --------------------------------------------------------
DROP TABLE IF EXISTS `users`;
CREATE TABLE IF NOT EXISTS `users` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `username` varchar(50) NOT NULL,
  `first_name` varchar(50) DEFAULT NULL,
  `surname` varchar(50) DEFAULT NULL,
  `attribution_display_mode` varchar(50) DEFAULT 'initials_random',
  `email` varchar(100) NOT NULL,
  `password_hash` varchar(255) NOT NULL,
  `role` enum('admin','moderator','user','viewer') DEFAULT 'user',
  `points` int(11) DEFAULT 0,
  `is_active` tinyint(1) DEFAULT 1,
  `email_verified` tinyint(1) DEFAULT 0,
  `invite_token` varchar(64) DEFAULT NULL,
  `invite_expires_at` datetime DEFAULT NULL,
  `reset_token` varchar(64) DEFAULT NULL,
  `reset_expires_at` datetime DEFAULT NULL,
  `google_2fa_secret` varchar(32) DEFAULT NULL,
  `two_fa_enabled` tinyint(1) DEFAULT 0,
  `created_at` timestamp NULL DEFAULT current_timestamp(),
  `backup_codes` text DEFAULT NULL,
  `timezone` varchar(64) DEFAULT 'UTC',
  `date_format` varchar(32) DEFAULT 'd/m/Y H:i',
  `language` varchar(10) DEFAULT NULL,
  `time_format` varchar(8) DEFAULT '24',
  `is_new_user` tinyint(1) DEFAULT 1,
  `role_id` int(11) DEFAULT 3,
  PRIMARY KEY (`id`),
  UNIQUE KEY `username` (`username`),
  UNIQUE KEY `email` (`email`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
-- --------------------------------------------------------
-- Table structure for table `user_email_templates`
-- --------------------------------------------------------
DROP TABLE IF EXISTS `user_email_templates`;
CREATE TABLE IF NOT EXISTS `user_email_templates` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `trigger_event` varchar(100) NOT NULL,
  `template_name` varchar(255) NOT NULL,
  `subject` varchar(255) NOT NULL,
  `body` text NOT NULL,
  `updated_at` timestamp NULL DEFAULT current_timestamp() ON UPDATE current_timestamp(),
  PRIMARY KEY (`id`),
  UNIQUE KEY `trigger_event` (`trigger_event`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;
-- --------------------------------------------------------
-- Table structure for table `volunteers`
-- (legacy simple form; dynamic flow uses volunteer_submissions*)
-- --------------------------------------------------------
DROP TABLE IF EXISTS `volunteers`;
CREATE TABLE IF NOT EXISTS `volunteers` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `name` varchar(100) NOT NULL,
  `email` varchar(255) NOT NULL,
  `experience` text NOT NULL,
  `created_at` timestamp NULL DEFAULT current_timestamp(),
  PRIMARY KEY (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;
-- --------------------------------------------------------
-- Table structure for table `volunteer_columns`
-- --------------------------------------------------------
DROP TABLE IF EXISTS `volunteer_columns`;
CREATE TABLE IF NOT EXISTS `volunteer_columns` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `column_name` varchar(255) NOT NULL,
  `data_type` varchar(50) NOT NULL DEFAULT 'VARCHAR',
  `field_subtype` varchar(50) DEFAULT NULL,
  `field_options` text DEFAULT NULL,
  `allow_multiple` tinyint(1) DEFAULT 0,
  `max_length` int(11) DEFAULT NULL,
  `boolean_display_format` varchar(50) DEFAULT 'yes_no',
  `sort_order` int(11) DEFAULT 0,
  `is_required` tinyint(1) DEFAULT 0,
  `created_by` int(11) DEFAULT NULL,
  `created_at` timestamp NULL DEFAULT current_timestamp(),
  PRIMARY KEY (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;
-- --------------------------------------------------------
-- Table structure for table `volunteer_email_templates`
-- --------------------------------------------------------
DROP TABLE IF EXISTS `volunteer_email_templates`;
CREATE TABLE IF NOT EXISTS `volunteer_email_templates` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `trigger_event` varchar(100) NOT NULL,
  `template_name` varchar(255) NOT NULL,
  `subject` varchar(255) NOT NULL,
  `body` text NOT NULL,
  `updated_at` timestamp NULL DEFAULT current_timestamp() ON UPDATE current_timestamp(),
  PRIMARY KEY (`id`),
  UNIQUE KEY `trigger_event` (`trigger_event`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;
-- --------------------------------------------------------
-- Table structure for table `volunteer_form_settings`
-- --------------------------------------------------------
DROP TABLE IF EXISTS `volunteer_form_settings`;
CREATE TABLE IF NOT EXISTS `volunteer_form_settings` (
  `setting_key` varchar(100) NOT NULL,
  `setting_value` text NOT NULL,
  PRIMARY KEY (`setting_key`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;
-- --------------------------------------------------------
-- Table structure for table `volunteer_submissions`
-- --------------------------------------------------------
DROP TABLE IF EXISTS `volunteer_submissions`;
CREATE TABLE IF NOT EXISTS `volunteer_submissions` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `first_name` varchar(100) NOT NULL DEFAULT '',
  `surname` varchar(100) NOT NULL DEFAULT '',
  `email` varchar(255) NOT NULL DEFAULT '',
  `preferred_username` varchar(100) DEFAULT NULL,
  `created_by` int(11) DEFAULT NULL,
  `created_at` timestamp NULL DEFAULT current_timestamp(),
  `status` varchar(50) DEFAULT 'Pending Review',
  `interview_date` datetime DEFAULT NULL,
  `interview_notes` text DEFAULT NULL,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;
-- --------------------------------------------------------
-- Table structure for table `volunteer_submission_values`
-- --------------------------------------------------------
DROP TABLE IF EXISTS `volunteer_submission_values`;
CREATE TABLE IF NOT EXISTS `volunteer_submission_values` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `submission_id` int(11) NOT NULL,
  `column_id` int(11) NOT NULL,
  `value_content` text DEFAULT NULL,
  PRIMARY KEY (`id`),
  KEY `submission_id` (`submission_id`),
  KEY `column_id` (`column_id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;
-- --------------------------------------------------------
-- Table structure for table `demo_artifacts`
-- --------------------------------------------------------
DROP TABLE IF EXISTS `demo_artifacts`;
CREATE TABLE IF NOT EXISTS `demo_artifacts` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `pack_slug` varchar(64) NOT NULL,
  `artifact_type` varchar(32) NOT NULL,
  `ref_id` int(11) NOT NULL,
  `created_at` timestamp NULL DEFAULT current_timestamp(),
  PRIMARY KEY (`id`),
  KEY `idx_demo_pack` (`pack_slug`),
  KEY `idx_demo_type_ref` (`artifact_type`,`ref_id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
-- --------------------------------------------------------
-- Foreign keys
-- --------------------------------------------------------
ALTER TABLE `audit_logs`
  ADD CONSTRAINT `audit_logs_ibfk_1` FOREIGN KEY (`user_id`) REFERENCES `users` (`id`);
ALTER TABLE `dynamic_tables`
  ADD CONSTRAINT `dynamic_tables_ibfk_1` FOREIGN KEY (`created_by`) REFERENCES `users` (`id`);
ALTER TABLE `edit_suggestions`
  ADD CONSTRAINT `edit_suggestions_ibfk_1` FOREIGN KEY (`suggested_by`) REFERENCES `users` (`id`);
ALTER TABLE `records`
  ADD CONSTRAINT `records_ibfk_1` FOREIGN KEY (`created_by`) REFERENCES `users` (`id`);
ALTER TABLE `record_values`
  ADD CONSTRAINT `record_values_ibfk_1` FOREIGN KEY (`record_id`) REFERENCES `records` (`id`) ON DELETE CASCADE,
  ADD CONSTRAINT `record_values_ibfk_2` FOREIGN KEY (`column_id`) REFERENCES `table_columns` (`id`) ON DELETE CASCADE;
ALTER TABLE `role_permissions`
  ADD CONSTRAINT `role_permissions_ibfk_1` FOREIGN KEY (`role_id`) REFERENCES `roles` (`id`) ON DELETE CASCADE,
  ADD CONSTRAINT `role_permissions_ibfk_2` FOREIGN KEY (`permission_id`) REFERENCES `permissions` (`id`) ON DELETE CASCADE;
ALTER TABLE `table_columns`
  ADD CONSTRAINT `table_columns_ibfk_1` FOREIGN KEY (`created_by`) REFERENCES `users` (`id`);
ALTER TABLE `volunteer_submission_values`
  ADD CONSTRAINT `volunteer_submission_values_ibfk_1` FOREIGN KEY (`submission_id`) REFERENCES `volunteer_submissions` (`id`) ON DELETE CASCADE,
  ADD CONSTRAINT `volunteer_submission_values_ibfk_2` FOREIGN KEY (`column_id`) REFERENCES `volunteer_columns` (`id`) ON DELETE CASCADE;
SET FOREIGN_KEY_CHECKS = 1;
