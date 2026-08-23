<?php
// scripts/langSortUtility/deduplicate_installer_keys.php

$langDir = realpath(__DIR__ . '/../../lang');

if (!$langDir || !is_dir($langDir)) {
    die("Error: Could not find lang/ directory at {$langDir}\n");
}

$keysToCheck = [
    'install.heading',
    'install.done_heading',
    'install.done_message',
    'install.subheading',
    'install.req_heading',
    'install.req_fail_msg'
];

$files = glob($langDir . '/*.php');
$modifiedCount = 0;
$totalDuplicatesRemoved = 0;

echo "Scanning language files for duplicate installer keys...\n\n";

foreach ($files as $filePath) {
    $filename = basename($filePath);
    $content = file_get_contents($filePath);
    if ($content === false) continue;

    $fileChanged = false;
    $fileRemovedCount = 0;

    foreach ($keysToCheck as $key) {
        $pattern = '/^\s*[\'"]' . preg_quote($key, '/') . '[\'"]\s*=>\s*[\'"](.*?)[\'"]\s*,?\s*(?:\r\n|\n|\r)/m';
        
        preg_match_all($pattern, $content, $matches, PREG_OFFSET_CAPTURE);

        // If there are 2 or more instances, remove the first one (highest up)
        if (count($matches[0]) >= 2) {
            $firstMatchText = $matches[0][0][0];
            $firstMatchVal  = $matches[1][0][0]; // Captured value for reporting
            $firstMatchOffset = $matches[0][0][1];
            $firstMatchLength = strlen($firstMatchText);

            // Surgical removal of the top instance
            $content = substr_replace($content, '', $firstMatchOffset, $firstMatchLength);
            $fileChanged = true;
            $fileRemovedCount++;
            $totalDuplicatesRemoved++;

            echo "  [FILE: {$filename}] Removed top duplicate -> Key: '{$key}' | Value: '{$firstMatchVal}'\n";
        }
    }

    if ($fileChanged) {
        file_put_contents($filePath, $content);
        $modifiedCount++;
        echo "  --> Saved changes to {$filename} ({$fileRemovedCount} duplicate(s) removed).\n\n";
    }
}

echo "--------------------------------------------------\n";
echo "Sanity Check Complete:\n";
echo "- Files modified: {$modifiedCount}\n";
echo "- Total top duplicates removed: {$totalDuplicatesRemoved}\n";
echo "Any files with only 1 instance (or 0) were cleanly skipped.\n";
