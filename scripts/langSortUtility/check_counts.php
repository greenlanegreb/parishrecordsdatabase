<?php
// Anchored from scripts/langSortUtility/ up to the project root
$origDir = __DIR__ . '/../../lang_backup_blocks_2026-08-15_14-31-33'; // Update if your backup folder name differs slightly
$newDir = __DIR__ . '/../../lang';

// Gracefully handle a missing backup directory
if (!is_dir($origDir)) {
    echo "❌ Error: Backup directory not found:\n   {$origDir}\n";
    echo "   Please check that your backup folder name matches or exists.\n";
    exit(1);
}

if (!is_dir($newDir)) {
    echo "❌ Error: Live language directory not found:\n   {$newDir}\n";
    exit(1);
}

$iterator = new RecursiveIteratorIterator(new RecursiveDirectoryIterator($origDir, RecursiveDirectoryIterator::SKIP_DOTS));
$mismatches = 0;

foreach ($iterator as $file) {
    if ($file->getExtension() === 'php') {
        $relPath = str_replace($origDir . '/', '', $file->getPathname());
        $origArr = include $file->getPathname();
        $newPath = $newDir . '/' . $relPath;
        
        if (!file_exists($newPath)) {
            echo "❌ Missing new file: {$relPath}\n";
            continue;
        }
        
        $newArr = include $newPath;
        
        $origCount = is_array($origArr) ? count($origArr) : 0;
        $newCount = is_array($newArr) ? count($newArr) : 0;
        
        if ($origCount !== $newCount) {
            echo "⚠️ Count mismatch in {$relPath}: Original had {$origCount}, New has {$newCount}\n";
            $mismatches++;
        }
    }
}

if ($mismatches === 0) {
    echo "✨ Perfect! Every file has the exact same number of keys before and after sorting. Zero keys lost!\n";
} else {
    echo "Found {$mismatches} mismatches.\n";
}
