<?php
declare(strict_types=1);
/**
 * MIGRATED FILE MAPPING
 * ---------------------
 * Original Old File: 404.php
 * Migrated Date: 2026-08-05 07:45:00
 */

// 404.php - Custom page not found handler
require_once __DIR__ . '/errors/error_template.php';

render_http_error(
    404, 
    'Page Not Found', 
    'The page, resource, or record you are looking for could not be found or has been removed.'
);
