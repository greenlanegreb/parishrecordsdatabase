<?php
// 404.php - Custom page not found handler
require_once __DIR__ . '/errors/error_template.php';
render_http_error(404, 'Page Not Found', 'The page, resource, or record you are looking for could not be found or has been removed.');
