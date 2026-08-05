<?php
declare(strict_types=1);
/**
 * MIGRATED FILE MAPPING
 * ---------------------
 * Original Old File: volunteer.php
 * Migrated Date: 2026-08-05 11:00:00
 */

require_once __DIR__ . '/db/db.php';
require_once __DIR__ . '/app/Controllers/Public/VolunteerController.php';

$controller = new App\Controllers\Public\VolunteerController($pdo);
$controller->index();
