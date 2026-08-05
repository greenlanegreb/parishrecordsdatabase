<?php
declare(strict_types=1);
/**
 * MIGRATED FILE MAPPING
 * ---------------------
 * Original Old File: update_database.php
 * Migrated Date: 2026-08-05 09:10:00
 */

require_once __DIR__ . '/db/db.php';
require_once __DIR__ . '/app/Controllers/UpdateDatabaseController.php';

$controller = new App\Controllers\UpdateDatabaseController($pdo);
$controller->index();
