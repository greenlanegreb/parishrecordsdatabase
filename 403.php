<?php
declare(strict_types=1);
/**
 * MIGRATED FILE MAPPING
 * ---------------------
 * Original Old File: 403.php
 * Migrated Date: 2026-08-05 07:30:00
 */

// 403.php - Custom forbidden access handler
require_once __DIR__ . '/errors/error_template.php';

render_http_error(
    403, 
    'Access Forbidden', 
    'You do not have the necessary permissions or administrative privileges to view this resource.'
);
