<?php
// api/export.php - Handles CSV Export generation based on active search filters
require_once '../db/db.php';
require_once '../db/auth_helpers.php';
require_once '../includes/functions.php';
session_start();

generate_csv_export($pdo, 'psd-export');
