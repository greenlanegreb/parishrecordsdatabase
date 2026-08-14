-- PRD seed baseline — core roles only
-- Used by install after schema_baseline.sql on an empty database.
-- Permissions are applied via db/permissions_registry.php (install_bootstrap_permissions).
-- Do not include table_2_moderator (dev test role).

SET NAMES utf8mb4;
SET FOREIGN_KEY_CHECKS = 0;

INSERT INTO `roles` (`id`, `role_name`, `description`, `created_at`) VALUES
(1, 'admin', 'Full system administrator access', CURRENT_TIMESTAMP),
(2, 'moderator', 'Can moderate edit suggestions and oversee data integrity', CURRENT_TIMESTAMP),
(3, 'user', 'Standard contributor for data entry', CURRENT_TIMESTAMP),
(4, 'guest', 'Read-only public visitor', CURRENT_TIMESTAMP);

SET FOREIGN_KEY_CHECKS = 1;
