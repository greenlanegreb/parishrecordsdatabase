<?php
/**
 * Localization Key Audit Script
 * Place this in scripts/langSortUtility/ and run via CLI or Composer.
 */
declare(strict_types=1);

$langFile = __DIR__ . '/../../lang/en.php';

if (!file_exists($langFile)) {
    echo "Error: Language file not found at {$langFile}\n";
    exit(1);
}

// Keys we want to verify exist
$keysToCheck = [
    // Errors & HTTP Templates
    'admin_errors.heading',
    'admin_errors.subheading',
    'admin_errors.label_error_id',
    'admin_errors.placeholder_id',
    'admin_errors.btn_find',
    'admin_errors.not_found',
    'admin_errors.found_heading',
    'admin_errors.time_utc',
    'admin_errors.local_time',
    'admin_errors.type',
    'admin_errors.message',
    'admin_errors.file',
    'admin_errors.line_prefix',
    'admin_errors.request',
    'admin_errors.stack_trace',
    'admin_errors.recent_heading',
    'admin_errors.no_recent',
    'admin_errors.th_id',
    'admin_errors.th_time',
    'admin_errors.th_message',
    'error_template.debug_details',
    'error_template.stack_trace',
    'error_template.file_label',
    'error_template.line_label',

    // Admin Users Management
    'admin_users.find_user',
    'admin_users.search_placeholder',
    'admin_users.search_help',
    'admin_users.no_search_match',
    'admin_users.delete_btn',

    // Update Database & Index
    'index.remove_emergency_file',
    'update_database.remove_emergency_file',
    'update_database.backup_notice',

    // Volunteer Form
    'index.check_availability',
    'index.allocate_unique_username',
    'volunteer.check_availability',
    'volunteer.allocate_unique_username',

    // Suggest Edit
    'suggest_edit.leave_blank',

    // Data Entry Workstation
    'data_entry.error_loading',

    // Settings
    'settings.default_timezone',
    'settings.default_date_format',
    'settings.default_time_format',
    'settings.footer_compiled_notice',
    'settings.delete_btn',
    'settings.error_log_tab',
    'settings.smtp_host_label',
    'settings.port_label',
    'settings.default_lang_note',
    'settings.footer_notice_helper',

    // Manage Tables & Schemas
    'manage_tables.delete_table_btn',
    'manage_tables.yes',
    'manage_tables.no',

    // Notices Manager
    'notices.add_new',
    'notices.title_label',
    'notices.content_label',
    'notices.display_order',
    'notices.active',
    'notices.dismissible',
    'notices.audience',
    'notices.everyone',
    'notices.create_notice_btn',
    'notices.save_btn',
    'notices.delete_btn',
    'notices.delete_confirm',
];

$lines = file($langFile);
$seenKeys = [];
$duplicates = [];

// 1. Scan for duplicates and line numbers across the entire file
if ($lines !== false) {
    foreach ($lines as $index => $line) {
        $lineNum = $index + 1;
        // Match string keys inside single or double quotes before =>
        if (preg_match('/^\s*[\'"]([a-zA-Z0-9_\.-]+)[\'"]\s*=>/u', $line, $matches)) {
            $key = $matches[1];
            if (isset($seenKeys[$key])) {
                $duplicates[$key][] = $lineNum;
            } else {
                $seenKeys[$key] = $lineNum;
                // Also store initial occurrence for reference if duplicates arise
                $duplicates[$key] = [$seenKeys[$key]];
            }
        }
    }
}

// Filter out non-duplicates from the tracking array
$actualDuplicates = array_filter($duplicates, fn($lineNums) => count($lineNums) > 1);

echo "=== DUPLICATE KEY AUDIT ===\n";
if (empty($actualDuplicates)) {
    echo "✔ No duplicate keys found in {$langFile}!\n\n";
} else {
    echo "✖ DUPLICATES DETECTED:\n";
    foreach ($actualDuplicates as $key => $lineNums) {
        $first = array_shift($lineNums);
        echo "  - Key '{$key}' has duplicates:\n";
        echo "     Original at line: {$first}\n";
        foreach ($lineNums as $dupLine) {
            echo "     Duplicate at line: {$dupLine}\n";
        }
    }
    echo "\n";
}

// 2. Check existence of our specific checklist keys
echo "=== CHECKLIST KEY EXISTENCE AUDIT ===\n";
$missingKeys = [];
foreach ($keysToCheck as $checkKey) {
    if (!isset($seenKeys[$checkKey])) {
        $missingKeys[] = $checkKey;
    }
}

if (empty($missingKeys)) {
    echo "✔ All " . count($keysToCheck) . " audited keys exist in {$langFile}!\n";
} else {
    echo "✖ MISSING KEYS:\n";
    foreach ($missingKeys as $missing) {
        echo "  - {$missing}\n";
    }
}
