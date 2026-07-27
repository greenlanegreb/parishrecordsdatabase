<?php
// 403.php - Custom forbidden access handler
require_once __DIR__ . '/errors/error_template.php';
render_http_error(403, 'Access Forbidden', 'You do not have the necessary permissions or administrative privileges to view this resource.');
