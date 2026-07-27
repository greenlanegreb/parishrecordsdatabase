-- phpMyAdmin SQL Dump
-- version 5.2.3
-- https://www.phpmyadmin.net/
--
-- Host: localhost
-- Generation Time: Jul 25, 2026 at 01:32 AM
-- Server version: 10.11.18-MariaDB-0+deb12u1
-- PHP Version: 8.2.32

SET SQL_MODE = "NO_AUTO_VALUE_ON_ZERO";
START TRANSACTION;
SET time_zone = "+00:00";


/*!40101 SET @OLD_CHARACTER_SET_CLIENT=@@CHARACTER_SET_CLIENT */;
/*!40101 SET @OLD_CHARACTER_SET_RESULTS=@@CHARACTER_SET_RESULTS */;
/*!40101 SET @OLD_COLLATION_CONNECTION=@@COLLATION_CONNECTION */;
/*!40101 SET NAMES utf8mb4 */;

--
-- Database: `dbname`
--

-- --------------------------------------------------------

--
-- Table structure for table `audit_logs`
--

CREATE TABLE `audit_logs` (
  `id` int(11) NOT NULL,
  `user_id` int(11) DEFAULT NULL,
  `action` varchar(50) NOT NULL,
  `record_id` int(11) DEFAULT NULL,
  `details` text DEFAULT NULL,
  `ip_address` varchar(45) DEFAULT NULL,
  `created_at` timestamp NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Dumping data for table `audit_logs`
--

INSERT INTO `audit_logs` (`id`, `user_id`, `action`, `record_id`, `details`, `ip_address`, `created_at`) VALUES
(1, 1, 'CREATE_COLUMN', NULL, 'Created column: Surname (Format: yes_no)', 'redacted_ip_address', '2026-07-23 13:25:04'),
(2, 1, 'CREATE_COLUMN', NULL, 'Created column: First Name (Format: yes_no)', 'redacted_ip_address', '2026-07-23 13:26:08'),
(3, 1, 'CREATE_COLUMN', NULL, 'Created column: Middle Name(s) (Format: yes_no)', 'redacted_ip_address', '2026-07-23 13:26:46'),
(4, 1, 'CREATE_COLUMN', NULL, 'Created column: Date of Birth (Format: yes_no)', 'redacted_ip_address', '2026-07-23 13:27:53'),
(5, 1, 'CREATE_COLUMN', NULL, 'Created column: Baptism Date (Format: yes_no)', 'redacted_ip_address', '2026-07-23 13:28:39'),
(6, 1, 'CREATE_COLUMN', NULL, 'Created column: Location of Baptism (Format: yes_no)', 'redacted_ip_address', '2026-07-23 13:30:08'),
(7, 1, 'DELETE_COLUMN', NULL, 'Deleted column ID 6: Location of Baptism', 'redacted_ip_address', '2026-07-23 13:30:36'),
(8, 1, 'CREATE_COLUMN', NULL, 'Created column: Location of Baptism (Format: yes_no)', 'redacted_ip_address', '2026-07-23 13:31:00'),
(9, 1, 'CREATE_COLUMN', NULL, 'Created column: Father\'s Name (Format: yes_no)', 'redacted_ip_address', '2026-07-23 13:32:31'),
(10, 1, 'CREATE_COLUMN', NULL, 'Created column: Mother\'s Name (Format: yes_no)', 'redacted_ip_address', '2026-07-23 13:32:47'),
(11, 1, 'UPDATE_COLUMN', NULL, 'Updated column ID 5: Baptism Date', 'redacted_ip_address', '2026-07-23 14:33:52'),
(12, 1, 'CREATE_COLUMN', NULL, 'Created column: test column timezone', 'redacted_ip_address', '2026-07-23 14:51:01'),
(13, 1, 'UPDATE_COLUMN', NULL, 'Updated column ID 5: Baptism Date (Required: Yes)', 'redacted_ip_address', '2026-07-23 15:50:19'),
(14, 1, 'UPDATE_COLUMN', NULL, 'Updated column ID 4: Date of Birth (Required: Yes)', 'redacted_ip_address', '2026-07-23 15:50:28'),
(15, 1, 'UPDATE_COLUMN', NULL, 'Updated column ID 7: Location of Baptism (Required: Yes)', 'redacted_ip_address', '2026-07-23 15:50:45'),
(16, 1, 'UPDATE_COLUMN', NULL, 'Updated column ID 2: First Name (Required: Yes)', 'redacted_ip_address', '2026-07-23 15:50:58'),
(17, 1, 'UPDATE_COLUMN', NULL, 'Updated column ID 1: Surname (Required: Yes)', 'redacted_ip_address', '2026-07-23 15:51:09'),
(18, 1, 'UPDATE_COLUMN', NULL, 'Updated column ID 10: test column timezone (Required: Yes)', 'redacted_ip_address', '2026-07-23 15:51:25'),
(19, 1, 'DELETE_COLUMN', NULL, 'Deleted column ID 10: test column timezone', 'redacted_ip_address', '2026-07-23 15:51:31'),
(20, 1, 'CREATE_COLUMN', NULL, 'Created column: Item For Sale (Required: No)', 'redacted_ip_address', '2026-07-23 16:53:41'),
(21, 1, 'CREATE_COLUMN', NULL, 'Created column: Price (Required: No)', 'redacted_ip_address', '2026-07-23 16:54:19'),
(22, 1, 'UPDATE_COLUMN', NULL, 'Updated column ID 12: Original Price (Required: No)', 'redacted_ip_address', '2026-07-23 16:55:32'),
(23, 1, 'UPDATE_COLUMN', NULL, 'Updated column ID 12: Original Price (Required: Yes)', 'redacted_ip_address', '2026-07-23 16:55:47'),
(24, 1, 'DELETE_COLUMN', NULL, 'Deleted column ID 12: Original Price', 'redacted_ip_address', '2026-07-23 20:23:19'),
(25, 1, 'DELETE_COLUMN', NULL, 'Deleted column ID 11: Item For Sale', 'redacted_ip_address', '2026-07-23 20:23:29'),
(26, 1, 'UPDATE_COLUMN', NULL, 'Updated column ID 5: Baptism Date (Required: Yes)', 'redacted_ip_address', '2026-07-23 21:11:01'),
(27, 1, 'UPDATE_COLUMN', NULL, 'Updated column ID 4: Date of Birth (Required: No)', 'redacted_ip_address', '2026-07-23 21:23:51'),
(28, 1, 'CREATE_COLUMN', NULL, 'Created column: Sex (Required: Yes)', 'redacted_ip_address', '2026-07-23 22:07:37'),
(29, 1, 'UPDATE_COLUMN', NULL, 'Updated column ID 13: Sex (Required: Yes)', 'redacted_ip_address', '2026-07-23 23:24:44'),
(30, 1, 'UPDATE_COLUMN', NULL, 'Updated column ID 13: Sex (Required: Yes)', 'redacted_ip_address', '2026-07-23 23:45:47'),
(31, 1, 'UPDATE_COLUMN', NULL, 'Updated column ID 13: Sex (Required: Yes)', 'redacted_ip_address', '2026-07-23 23:46:02');

-- --------------------------------------------------------

--
-- Table structure for table `dynamic_tables`
--

CREATE TABLE `dynamic_tables` (
  `id` int(11) NOT NULL,
  `table_name` varchar(64) NOT NULL,
  `created_by` int(11) DEFAULT NULL,
  `created_at` timestamp NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- --------------------------------------------------------

--
-- Table structure for table `edit_suggestions`
--

CREATE TABLE `edit_suggestions` (
  `id` int(11) NOT NULL,
  `record_id` int(11) DEFAULT NULL,
  `suggested_by` int(11) DEFAULT NULL,
  `column_name` varchar(64) NOT NULL,
  `proposed_value` text NOT NULL,
  `status` enum('pending','approved','rejected') DEFAULT 'pending',
  `created_at` timestamp NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- --------------------------------------------------------

--
-- Table structure for table `feedback`
--

CREATE TABLE `feedback` (
  `id` int(11) NOT NULL,
  `name` varchar(100) NOT NULL,
  `email` varchar(255) NOT NULL,
  `message` text NOT NULL,
  `created_at` timestamp NULL DEFAULT current_timestamp(),
  `status` varchar(50) DEFAULT 'Pending',
  `admin_notes` text DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `feedback`
--

INSERT INTO `feedback` (`id`, `name`, `email`, `message`, `created_at`, `status`, `admin_notes`) VALUES
(2, 'somename', 'someemail@email.com', 'Nice!!', '2026-07-23 16:39:48', 'Pending', NULL);

-- --------------------------------------------------------

--
-- Table structure for table `records`
--

CREATE TABLE `records` (
  `id` int(11) NOT NULL,
  `created_by` int(11) DEFAULT NULL,
  `created_at` timestamp NULL DEFAULT current_timestamp(),
  `updated_at` timestamp NULL DEFAULT current_timestamp() ON UPDATE current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- --------------------------------------------------------

--
-- Table structure for table `record_values`
--

CREATE TABLE `record_values` (
  `id` int(11) NOT NULL,
  `record_id` int(11) NOT NULL,
  `column_id` int(11) NOT NULL,
  `value_content` text DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- --------------------------------------------------------

--
-- Table structure for table `site_notices`
--

CREATE TABLE `site_notices` (
  `id` int(11) NOT NULL,
  `title` varchar(255) NOT NULL,
  `content` text NOT NULL,
  `target_roles` varchar(255) DEFAULT 'everyone',
  `is_dismissible` tinyint(1) DEFAULT 1,
  `is_active` tinyint(1) DEFAULT 1,
  `display_order` int(11) DEFAULT 0,
  `created_at` timestamp NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `site_notices`
--

INSERT INTO `site_notices` (`id`, `title`, `content`, `target_roles`, `is_dismissible`, `is_active`, `display_order`, `created_at`) VALUES
(1, 'Welcome to the Parish Records Directory.', '<p>You can search isolated columns or use multiple search boxes simultaneously and download your search results. You can also click table headers to sort. If you would like further improvements, please let us know!</p>', 'everyone', 1, 1, 0, '2026-07-23 04:18:57'),
(2, 'PSD Is Looking for Volunteers Please!', 'We are looking for people to help with data entry. If you can spare some time, please click the \"Volunteer\" button above. Thank You!', 'everyone', 1, 1, 0, '2026-07-23 04:21:29');

-- --------------------------------------------------------

--
-- Table structure for table `site_settings`
--

CREATE TABLE `site_settings` (
  `setting_key` varchar(100) NOT NULL,
  `setting_value` text NOT NULL,
  `updated_at` timestamp NULL DEFAULT current_timestamp() ON UPDATE current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Dumping data for table `site_settings`
--

INSERT INTO `site_settings` (`setting_key`, `setting_value`, `updated_at`) VALUES
('mail_domain', 'domain.com', '2026-07-24 21:48:10'),
('mail_driver', 'mail', '2026-07-24 21:48:10'),
('maintenance_eta', 'Shortly', '2026-07-23 04:32:15'),
('maintenance_mode', '0', '2026-07-23 04:59:41'),
('maintenance_reason', 'Scheduled system maintenance and database updates.', '2026-07-23 04:32:15'),
('smtp_encryption', 'tls', '2026-07-24 21:48:10'),
('smtp_host', '', '2026-07-24 21:48:10'),
('smtp_pass', '', '2026-07-24 21:48:10'),
('smtp_port', '587', '2026-07-24 21:48:10'),
('smtp_user', '', '2026-07-24 21:48:10'),
('system_name', 'Parish Records Directory (PRD)', '2026-07-23 16:52:16');

-- --------------------------------------------------------

--
-- Table structure for table `table_columns`
--

CREATE TABLE `table_columns` (
  `id` int(11) NOT NULL,
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
  `exclude_from_public_search` tinyint(1) DEFAULT 0
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Dumping data for table `table_columns`
--

INSERT INTO `table_columns` (`id`, `column_name`, `data_type`, `max_length`, `is_required`, `created_by`, `created_at`, `boolean_display_format`, `sort_order`, `updated_at`, `date_search_behavior`, `exclude_from_public_search`) VALUES
(1, 'Surname', 'VARCHAR', 20, 1, 1, '2026-07-23 13:25:04', NULL, 3, '2026-07-23 21:27:33', 'manual_only', 0),
(2, 'First Name', 'VARCHAR', 20, 1, 1, '2026-07-23 13:26:08', NULL, 4, '2026-07-23 21:27:41', 'manual_only', 0),
(3, 'Middle Name(s)', 'VARCHAR', 20, 0, 1, '2026-07-23 13:26:46', 'yes_no', 5, '2026-07-23 21:28:26', 'manual_only', 0),
(4, 'Date of Birth', 'DATE', 10, 0, 1, '2026-07-23 13:27:53', NULL, 8, '2026-07-23 21:28:48', 'manual_only', 0),
(5, 'Baptism Date', 'DATE', 10, 1, 1, '2026-07-23 13:28:39', NULL, 1, '2026-07-23 21:27:06', 'manual_only', 0),
(7, 'Location of Baptism', 'TEXT', 70, 1, 1, '2026-07-23 13:31:00', NULL, 2, '2026-07-23 21:27:24', 'manual_only', 0),
(8, 'Father\'s Name', 'VARCHAR', 20, 0, 1, '2026-07-23 13:32:31', 'yes_no', 6, '2026-07-23 21:28:37', 'manual_only', 0),
(9, 'Mother\'s Name', 'VARCHAR', 20, 0, 1, '2026-07-23 13:32:47', 'yes_no', 7, '2026-07-23 21:28:42', 'manual_only', 0),
(13, 'Sex', 'BOOLEAN', 7, 1, 1, '2026-07-23 22:07:37', 'male_female', 0, '2026-07-23 23:46:02', NULL, 0);

-- --------------------------------------------------------

--
-- Table structure for table `users`
--

CREATE TABLE `users` (
  `id` int(11) NOT NULL,
  `username` varchar(50) NOT NULL,
  `first_name` varchar(50) DEFAULT NULL,
  `surname` varchar(50) DEFAULT NULL,
  `leaderboard_display_mode` varchar(20) DEFAULT 'initials_random',
  `email` varchar(100) NOT NULL,
  `password_hash` varchar(255) NOT NULL,
  `role` enum('admin','moderator','user','viewer') DEFAULT 'user',
  `points` int(11) DEFAULT 0,
  `is_active` tinyint(1) DEFAULT 1,
  `email_verified` tinyint(1) DEFAULT 0,
  `verification_token` varchar(64) DEFAULT NULL,
  `token_expires_at` datetime DEFAULT NULL,
  `google_2fa_secret` varchar(32) DEFAULT NULL,
  `two_fa_enabled` tinyint(1) DEFAULT 0,
  `created_at` timestamp NULL DEFAULT current_timestamp(),
  `backup_codes` text DEFAULT NULL,
  `timezone` varchar(64) DEFAULT 'UTC',
  `date_format` varchar(32) DEFAULT 'd/m/Y H:i',
  `time_format` varchar(8) DEFAULT '24',
  `is_new_user` tinyint(1) DEFAULT 1
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Dumping data for table `users`
--

INSERT INTO `users` (`id`, `username`, `first_name`, `surname`, `leaderboard_display_mode`, `email`, `password_hash`, `role`, `points`, `is_active`, `email_verified`, `verification_token`, `token_expires_at`, `google_2fa_secret`, `two_fa_enabled`, `created_at`, `backup_codes`, `timezone`, `date_format`, `time_format`, `is_new_user`) VALUES
(1, 'username', 'firstname', 'surname', 'initials_random', 'email@email.com', 'hashed_password', 'admin', 0, 1, 1, NULL, NULL, NULL, 0, '2026-07-22 16:00:55', NULL, 'Europe/London', 'd/m/Y', '24', 0);

-- --------------------------------------------------------

--
-- Table structure for table `volunteers`
--

CREATE TABLE `volunteers` (
  `id` int(11) NOT NULL,
  `name` varchar(100) NOT NULL,
  `email` varchar(255) NOT NULL,
  `experience` text NOT NULL,
  `created_at` timestamp NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Indexes for dumped tables
--

--
-- Indexes for table `audit_logs`
--
ALTER TABLE `audit_logs`
  ADD PRIMARY KEY (`id`),
  ADD KEY `user_id` (`user_id`);

--
-- Indexes for table `dynamic_tables`
--
ALTER TABLE `dynamic_tables`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `table_name` (`table_name`),
  ADD KEY `created_by` (`created_by`);

--
-- Indexes for table `edit_suggestions`
--
ALTER TABLE `edit_suggestions`
  ADD PRIMARY KEY (`id`),
  ADD KEY `suggested_by` (`suggested_by`);

--
-- Indexes for table `feedback`
--
ALTER TABLE `feedback`
  ADD PRIMARY KEY (`id`);

--
-- Indexes for table `records`
--
ALTER TABLE `records`
  ADD PRIMARY KEY (`id`),
  ADD KEY `created_by` (`created_by`);

--
-- Indexes for table `record_values`
--
ALTER TABLE `record_values`
  ADD PRIMARY KEY (`id`),
  ADD KEY `record_id` (`record_id`),
  ADD KEY `column_id` (`column_id`);

--
-- Indexes for table `site_notices`
--
ALTER TABLE `site_notices`
  ADD PRIMARY KEY (`id`);

--
-- Indexes for table `site_settings`
--
ALTER TABLE `site_settings`
  ADD PRIMARY KEY (`setting_key`);

--
-- Indexes for table `table_columns`
--
ALTER TABLE `table_columns`
  ADD PRIMARY KEY (`id`),
  ADD KEY `created_by` (`created_by`);

--
-- Indexes for table `users`
--
ALTER TABLE `users`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `username` (`username`),
  ADD UNIQUE KEY `email` (`email`);

--
-- Indexes for table `volunteers`
--
ALTER TABLE `volunteers`
  ADD PRIMARY KEY (`id`);

--
-- AUTO_INCREMENT for dumped tables
--

--
-- AUTO_INCREMENT for table `audit_logs`
--
ALTER TABLE `audit_logs`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=32;

--
-- AUTO_INCREMENT for table `dynamic_tables`
--
ALTER TABLE `dynamic_tables`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `edit_suggestions`
--
ALTER TABLE `edit_suggestions`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `feedback`
--
ALTER TABLE `feedback`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=3;

--
-- AUTO_INCREMENT for table `records`
--
ALTER TABLE `records`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `record_values`
--
ALTER TABLE `record_values`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `site_notices`
--
ALTER TABLE `site_notices`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=3;

--
-- AUTO_INCREMENT for table `table_columns`
--
ALTER TABLE `table_columns`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=14;

--
-- AUTO_INCREMENT for table `users`
--
ALTER TABLE `users`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=2;

--
-- AUTO_INCREMENT for table `volunteers`
--
ALTER TABLE `volunteers`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT;

--
-- Constraints for dumped tables
--

--
-- Constraints for table `audit_logs`
--
ALTER TABLE `audit_logs`
  ADD CONSTRAINT `audit_logs_ibfk_1` FOREIGN KEY (`user_id`) REFERENCES `users` (`id`);

--
-- Constraints for table `dynamic_tables`
--
ALTER TABLE `dynamic_tables`
  ADD CONSTRAINT `dynamic_tables_ibfk_1` FOREIGN KEY (`created_by`) REFERENCES `users` (`id`);

--
-- Constraints for table `edit_suggestions`
--
ALTER TABLE `edit_suggestions`
  ADD CONSTRAINT `edit_suggestions_ibfk_1` FOREIGN KEY (`suggested_by`) REFERENCES `users` (`id`);

--
-- Constraints for table `records`
--
ALTER TABLE `records`
  ADD CONSTRAINT `records_ibfk_1` FOREIGN KEY (`created_by`) REFERENCES `users` (`id`);

--
-- Constraints for table `record_values`
--
ALTER TABLE `record_values`
  ADD CONSTRAINT `record_values_ibfk_1` FOREIGN KEY (`record_id`) REFERENCES `records` (`id`) ON DELETE CASCADE,
  ADD CONSTRAINT `record_values_ibfk_2` FOREIGN KEY (`column_id`) REFERENCES `table_columns` (`id`) ON DELETE CASCADE;

--
-- Constraints for table `table_columns`
--
ALTER TABLE `table_columns`
  ADD CONSTRAINT `table_columns_ibfk_1` FOREIGN KEY (`created_by`) REFERENCES `users` (`id`);
COMMIT;

/*!40101 SET CHARACTER_SET_CLIENT=@OLD_CHARACTER_SET_CLIENT */;
/*!40101 SET CHARACTER_SET_RESULTS=@OLD_CHARACTER_SET_RESULTS */;
/*!40101 SET COLLATION_CONNECTION=@OLD_COLLATION_CONNECTION */;
