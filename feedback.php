<?php
declare(strict_types=1);
/**
 * MIGRATED FILE MAPPING
 * ---------------------
 * Original Old File: feedback.php
 * Migrated Date: 2026-08-05 10:30:00
 */

require_once __DIR__ . '/db/db.php';
require_once __DIR__ . '/app/Controllers/Public/FeedbackController.php';

$controller = new App\Controllers\Public\FeedbackController($pdo);
$controller->index();
