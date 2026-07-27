<?php
// api/export.php - Handles CSV Export generation based on active search filters
require_once '../db/db.php';
require_once '../db/auth_helpers.php';
require_once '../includes/functions.php';
session_start();

// Enforce permission-based access control for exporting database records
$current_user = require_permission($pdo, 'export_data', 'Export database records and search result sets to CSV');

// Log the export activity to the system audit trail
$audit = $pdo->prepare("INSERT INTO audit_logs (user_id, action, details, ip_address) VALUES (?, 'CSV_EXPORT', ?, ?)");
$audit->execute([$current_user['id'], "Generated CSV database export", $_SERVER['REMOTE_ADDR']]);

generate_csv_export($pdo, 'psd-export');
