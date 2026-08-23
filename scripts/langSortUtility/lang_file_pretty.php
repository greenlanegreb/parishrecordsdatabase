<?php
// scripts/langSortUtility/lang_file_pretty.php

// Automatically locate the lang directory relative to this script's location
$langDir = realpath(__DIR__ . '/../../lang');

if (!$langDir) {
    die("Error: Could not locate 'lang' directory.\n");
}

$pattern = '/^(\s*)([\'"][\w\.-]+[\'"])\s*=>\s*([\'"])(.*?)(\3\s*,?\s*)$/';

function processFile($filePath, $pattern) {
    $lines = file($filePath);
    if ($lines === false) {
        echo "Error: Could not read file {$filePath}\n";
        return false;
    }

    // Pass 1: Find max key length
    $maxKeyLen = 0;
    foreach ($lines as $line) {
        $trimmed = trim($line);
        if (preg_match($pattern, $trimmed, $matches)) {
            $maxKeyLen = max($maxKeyLen, strlen($matches[2]));
        }
    }

    if ($maxKeyLen === 0) {
        echo "Skipping (no valid keys found): " . basename($filePath) . "\n";
        return false;
    }

    // Pass 2: Rebuild lines with aligned arrows, keeping comments intact
    $newLines = [];
    foreach ($lines as $line) {
        $trimmed = trim($line);
        if (preg_match($pattern, $trimmed, $matches)) {
            $key = $matches[2];
            $quote = $matches[3];
            $val = $matches[4];
            $suffix = $matches[5];

            $paddedKey = str_pad($key, $maxKeyLen, ' ', STR_PAD_RIGHT);
            $newLines[] = "    {$paddedKey} => {$quote}{$val}{$suffix}\n";
        } else {
            $newLines[] = $line;
        }
    }

    file_put_contents($filePath, implode('', $newLines));
    echo "Prettified: " . basename($filePath) . "\n";
    return true;
}

// Check if a specific file argument was passed
if (isset($argv[1])) {
    $arg = $argv[1];
    // Resolve file path (supports passing just 'en.php' or a path)
    $targetFile = file_exists($arg) ? $arg : $langDir . '/' . basename($arg);
    
    if (!file_exists($targetFile)) {
        die("Error: Language file '{$targetFile}' not found.\n");
    }
    
    processFile($targetFile, $pattern);
} else {
    // Process the whole directory if no file is specified
    $files = glob($langDir . '/*.php');
    $count = 0;
    foreach ($files as $file) {
        if (processFile($file, $pattern)) {
            $count++;
        }
    }
    echo "\nDone! Prettified {$count} files.\n";
}
