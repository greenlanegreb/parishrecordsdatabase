<?php
declare(strict_types=1);

require_once __DIR__ . '/db/db.php';
require_once __DIR__ . '/app/Controllers/Public/LeaderboardController.php';

$controller = new App\Controllers\Public\LeaderboardController($pdo);
$controller->index();
