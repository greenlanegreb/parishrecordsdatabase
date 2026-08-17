<?php
/**
 * Translation Auditor Script
 * 
 * Usage: php scripts/langSortUtility/check_translations.php (or via Composer)
 */

declare(strict_types=1);

// --- CONFIGURATION ---
$baseDir       = realpath(__DIR__ . '/../../'); // Points to your project root (prd/)
$langDir       = $baseDir . '/lang';            // Path to your language files folder
$sourceDirs    = [$baseDir . '/app'];           // Paths to your codebase/templates
$referenceLang = 'en.php';                      // Source of truth baseline

if ($baseDir === false) {
    die("Error: Could not resolve project root base directory.\n");
}

$referenceFile = $langDir . '/' . $referenceLang;

if (!file_exists($referenceFile)) {
    die("Error: Reference file '{$referenceFile}' not found.\n");
}

// 1. Load Reference Keys
$referenceKeys = include $referenceFile;
if (!is_array($referenceKeys)) {
    die("Error: Reference file does not return a valid PHP array.\n");
}

echo "==================================================\n";
echo " TRANSLATION AUDIT REPORT (Base: {$referenceLang})\n";
echo "==================================================\n\n";

// 2. Scan All Other PHP Language Files
$langFiles = glob($langDir . '/*.php');
foreach ($langFiles as $file) {
    $filename = basename($file);
    if ($filename === $referenceLang) {
        continue;
    }

    $currentKeys = include $file;
    if (!is_array($currentKeys)) {
        echo "[$filename] WARNING: File does not return a valid array.\n\n";
        continue;
    }

    $missing = array_diff_key($referenceKeys, $currentKeys);
    $extra   = array_diff_key($currentKeys, $referenceKeys);

    echo "--- File: {$filename} ---\n";
    if (empty($missing) && empty($extra)) {
        echo " [OK] Perfectly synced with {$referenceLang}!\n\n";
        continue;
    }

    if (!empty($missing)) {
        echo " ❌ Missing " . count($missing) . " key(s):\n";
        foreach (array_keys($missing) as $key) {
            echo "   - '{$key}'\n";
        }
    }

    if (!empty($extra)) {
        echo " ⚠️ Extra/Redundant " . count($extra) . " key(s):\n";
        foreach (array_keys($extra) as $key) {
            echo "   - '{$key}'\n";
        }
    }
    echo "\n";
}

// 3. Scan Codebase for Potential Hardcoded Strings
echo "==================================================\n";
echo " SCANNING FOR POTENTIAL HARD-CODED STRINGS\n";
echo "==================================================\n";
echo "(Looking for plain text inside HTML attributes or output tags...)\n\n";

$hardcodedMatches = [];

foreach ($sourceDirs as $dir) {
    if (!is_dir($dir)) {
        continue;
    }

    $iterator = new RecursiveIteratorIterator(new RecursiveDirectoryIterator($dir));
    foreach ($iterator as $file) {
        if ($file->isFile() && in_array($file->getExtension(), ['php', 'html', 'twig'], true)) {
            $pathname = $file->getPathname();
            // Get path relative to project root
            $relativePath = str_replace($baseDir . '/', '', $pathname);
            
            $content = file_get_contents($pathname);
            if ($content === false) {
                continue;
            }
            
            // Heuristic regex to spot plain text between HTML tags
            preg_match_all('/>\s*([A-Za-z0-9\s,\!\?\.]{3,50})\s*</u', $content, $matches);
            
            if (!empty($matches[1])) {
                foreach ($matches[1] as $text) {
                    $trimmed = trim($text);
                    if (!is_numeric($trimmed) && preg_match('/[a-zA-Z]{3,}/', $trimmed)) {
                        $hardcodedMatches[$relativePath][] = $trimmed;
                    }
                }
            }
        }
    }
}

if (empty($hardcodedMatches)) {
    echo " [OK] No obvious hardcoded strings detected in scanned source paths.\n";
} else {
    foreach ($hardcodedMatches as $filePath => $strings) {
        $uniqueStrings = array_unique($strings);
        echo "File: {$filePath} (" . count($uniqueStrings) . " potential hard-coded strings):\n";
        foreach ($uniqueStrings as $str) {
            echo "   -> \"{$str}\"\n";
        }
        echo "\n";
    }
}
