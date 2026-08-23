<?php
// normalize_comments_test.php

$langDir = __DIR__ . '/lang';
$testDir = '/tmp/normalized_lang';

if (!is_dir($testDir)) {
    mkdir($testDir, 0755, true);
}

$files = glob($langDir . '/*.php');
$processedCount = 0;

// Regex to match translation lines: indentation, key, arrow, quote, value, suffix/comma
$pattern = '/^(\s*)([\'"][\w\.-]+[\'"])\s*=>\s*([\'"])(.*?)(\3\s*,?\s*)$/';

foreach ($files as $file) {
    $lines = file($file);
    if ($lines === false) {
        continue;
    }

    // Pass 1: Find the maximum key length across valid translation lines
    $maxKeyLen = 0;
    foreach ($lines as $line) {
        $trimmed = trim($line);
        if (preg_match($pattern, $trimmed, $matches)) {
            $maxKeyLen = max($maxKeyLen, strlen($matches[2]));
        }
    }

    // If no keys found, skip
    if ($maxKeyLen === 0) {
        echo "Skipping (no keys found): " . basename($file) . "\n";
        continue;
    }

    // Pass 2: Rebuild lines, aligning arrows and keeping comments/headers intact
    $newLines = [];
    foreach ($lines as $line) {
        $trimmed = trim($line);
        if (preg_match($pattern, $trimmed, $matches)) {
            $key = $matches[2];
            $quote = $matches[3];
            $val = $matches[4];
            $suffix = $matches[5];

            // Pad the key for uniform alignment
            $paddedKey = str_pad($key, $maxKeyLen, ' ', STR_PAD_RIGHT);
            $newLines[] = "    {$paddedKey} => {$quote}{$val}{$suffix}\n";
        } else {
            // Keep comments, headers, empty lines, and brackets untouched
            $newLines[] = $line;
        }
    }

    // Write to the test folder
    file_put_contents($testDir . '/' . basename($file), implode('', $newLines));
    echo "Normalized (comments preserved): " . basename($file) . "\n";
    $processedCount++;
}

echo "\nDone! Check your comment-safe test files in: {$testDir}\n";
