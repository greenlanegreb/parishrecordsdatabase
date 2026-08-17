<?php
declare(strict_types=1);

$langPath = __DIR__ . '/../../lang';
$referenceFile = $langPath . '/en.php';

if (!file_exists($referenceFile)) {
    exit("Reference file en.php not found.\n");
}

$referenceData = include $referenceFile;
$referenceKeys = array_keys($referenceData);
$totalRefKeys = count($referenceKeys);

echo "==================================================\n";
echo "Reference File: en.php\n";
echo "Total Key-Value Pairs: {$totalRefKeys}\n";
echo "==================================================\n\n";

$files = glob($langPath . '/*.php');

foreach ($files as $file) {
    $filename = basename($file);
    if ($filename === 'en.php') {
        continue;
    }

    $langData = include $file;
    $langKeys = array_keys(is_array($langData) ? $langData : []);
    $totalLangKeys = count(is_array($langData) ? $langData : []);
    
    $missing = array_diff($referenceKeys, $langKeys);

    echo "--------------------------------------------------\n";
    echo "File Checked: {$filename} (Total Keys: {$totalLangKeys}/{$totalRefKeys})\n";
    echo "--------------------------------------------------\n";

    if (empty($missing)) {
        echo "✅ All reference keys are present!\n";
    } else {
        echo "❌ Missing " . count($missing) . " key(s):\n";
        foreach ($missing as $key) {
            echo "   - {$key}\n";
        }
    }
    echo "\n";
}
