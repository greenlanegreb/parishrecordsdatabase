<?php
declare(strict_types=1);

$langPath = __DIR__ . '/../../lang';

if (!is_dir($langPath)) {
    exit("Language directory not found at: {$langPath}\n");
}

$iterator = new RecursiveIteratorIterator(
    new RecursiveDirectoryIterator($langPath, RecursiveDirectoryIterator::SKIP_DOTS)
);

$totalDuplicatesFound = 0;

echo "Scanning language files for duplicate keys...\n\n";

foreach ($iterator as $file) {
    if ($file->getExtension() === 'php') {
        $filePath = $file->getPathname();
        $relativeName = str_replace($langPath . '/', '', $filePath);
        
        $lines = file($filePath);
        $seenKeys = []; // Tracks key => line_number
        $fileDuplicates = [];

        foreach ($lines as $index => $lineContent) {
            $lineNumber = $index + 1;
            
            // Matches typical language array keys like 'some.key' => or "some.key" =>
            if (preg_match('/^\s*[\'"]([a-zA-Z0-9_\.-]+)[\'"]\s*=>/', $lineContent, $matches)) {
                $key = $matches[1];
                
                if (isset($seenKeys[$key])) {
                    $fileDuplicates[$key][] = [
                        'first_seen' => $seenKeys[$key],
                        'duplicate_at' => $lineNumber
                    ];
                    $totalDuplicatesFound++;
                } else {
                    $seenKeys[$key] = $lineNumber;
                }
            }
        }

        if (!empty($fileDuplicates)) {
            echo "📁 File: {$relativeName}\n";
            foreach ($fileDuplicates as $key => $occurrences) {
                foreach ($occurrences as $occ) {
                    echo "  ⚠️ Key '{$key}' (originally on line {$occ['first_seen']}) is duplicated on line {$occ['duplicate_at']}\n";
                }
            }
            echo "\n";
        }
    }
}

if ($totalDuplicatesFound === 0) {
    echo "✨ Clean! No duplicate keys found in any language files.\n";
} else {
    echo "Total duplicate keys found: {$totalDuplicatesFound}\n";
}
