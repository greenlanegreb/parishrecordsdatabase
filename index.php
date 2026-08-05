<?php
declare(strict_types=1);

require_once __DIR__ . '/db/db.php';
require_once __DIR__ . '/app/Controllers/Public/HomeController.php';

$controller = new App\Controllers\Public\HomeController($pdo);
$controller->index();
