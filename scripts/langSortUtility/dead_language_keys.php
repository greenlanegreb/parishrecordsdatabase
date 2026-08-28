<?php
/**
 * pRD Dead Language Key Scanner (v2)
 * 
 * Scans the codebase for unused language keys by referencing the project's lang directory.
 * Outputs results to a dedicated report file without making any destructive changes.
 */

$baseDir = realpath(__DIR__ . '/../../');
$langDir = $baseDir . '/lang';
$outputFile = __DIR__ . '/dead_keys_report.txt';

if (!$baseDir || !is_dir($baseDir)) {
    die("Error: Could not resolve the base pRD directory.\n");
}

if (!is_dir($langDir)) {
    die("Error: Language directory not found at {$langDir}\n");
}

// 1. Load all keys from all files in the lang directory
$definedKeys = [];
$iterator = new RecursiveIteratorIterator(
    new RecursiveDirectoryIterator($langDir, RecursiveDirectoryIterator::SKIP_DOTS)
);

foreach ($iterator as $file) {
    if ($file->isFile() && strtolower($file->getExtension()) === 'php') {
        $langFilePath = $file->getRealPath();
        $langData = include($langFilePath);
        
        if (!is_array($langData)) {
            $content = file_get_contents($langFilePath);
            $langData = @eval('? >' . $content);
        }
        
        if (is_array($langData)) {
            $definedKeys = array_merge($definedKeys, $langData);
        }
    }
}

$flatKeys = array_keys($definedKeys);

// Inject a temporary test key to prove the scanner catches dead keys
$flatKeys[] = 'test.definitely_dead_key_xyz';

echo "Loaded " . count($flatKeys) . " unique language keys (including test key) from the lang directory.\n";

// 2. Gather all project files to scan
$excludeDirs = [
    realpath(__DIR__),
    $baseDir . '/.git',
    $baseDir . '/vendor'
];

$fileIterator = new RecursiveIteratorIterator(
    new RecursiveDirectoryIterator($baseDir, RecursiveDirectoryIterator::SKIP_DOTS),
    RecursiveIteratorIterator::LEAVES_ONLY
);

$searchableFiles = [];
foreach ($fileIterator as $file) {
    if ($file->isFile()) {
        $filePath = $file->getRealPath();
        
        $excluded = false;
        foreach ($excludeDirs as $exDir) {
            if ($exDir && strpos($filePath, $exDir) === 0) {
                $excluded = true;
                break;
            }
        }
        
        if (!$excluded) {
            $ext = pathinfo($filePath, PATHINFO_EXTENSION);
            if (in_array(strtolower($ext), ['php', 'html', 'js', 'twig', 'phtml'])) {
                $searchableFiles[] = $filePath;
            }
        }
    }
}

echo "Scanning " . count($searchableFiles) . " project files for key usage...\n";

// 3. Read all file contents into memory
$allCodeContents = '';
foreach ($searchableFiles as $file) {
    $allCodeContents .= file_get_contents($file) . "\n";
}

// 4. Check each key using a robust pattern match
$deadKeys = [];
foreach ($flatKeys as $key) {
    // Escape dots and special regex chars, look for the key inside quotes anywhere in the code
    $escapedKey = preg_quote($key, '/');
    // Matches the key wrapped in single quotes, double quotes, or array index patterns
    $pattern = '/([\'"])\s*' . $escapedKey . '\s*\1/';
    
    if (!preg_match($pattern, $allCodeContents)) {
        // Fallback check: simple string presence just in case it's used without direct quotes
        if (strpos($allCodeContents, $key) === false) {
            $deadKeys[] = $key;
        }
    }
}

// 5. Format and write out results
$report = "=====================================================\n";
$report .= " pRD Dead Language Key Scan Report\n";
$report .= " Generated: " . date('Y-m-d H:i:s') . "\n";
$report .= " Base Directory Scanned: {$baseDir}\n";
$report .= " Lang Directory Checked: {$langDir}\n";
$report .= " Total Keys Checked: " . count($flatKeys) . "\n";
$report .= " Unused Keys Found: " . count($deadKeys) . "\n";
$report .= "=====================================================\n\n";

if (empty($deadKeys)) {
    $report .= "No dead language keys found! Every key appears to be in use.\n";
} else {
    $report .= "The following keys were not found anywhere in the codebase:\n\n";
    foreach ($deadKeys as $deadKey) {
        $report .= "- '{$deadKey}'\n";
    }
}

file_put_contents($outputFile, $report);

echo "Scan complete! Found " . count($deadKeys) . " dead keys.\n";
echo "Report saved to: {$outputFile}\n";
