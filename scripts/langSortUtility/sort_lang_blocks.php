<?php
// Save as sort_lang_blocks.php in your project root
// Run via terminal: php sort_lang_blocks.php

declare(strict_types=1);

$langPath = __DIR__ . '/../../lang'; // Adjust if your language directory is elsewhere
$backupPath = __DIR__ . '/../../lang_backup_blocks_' . date('Y-m-d_H-i-s');

if (!is_dir($langPath)) {
    exit("❌ Error: Language directory not found at: {$langPath}\n");
}

echo "📦 Creating backup in: {$backupPath}\n";
mkdir($backupPath, 0755, true);

$iterator = new RecursiveIteratorIterator(
    new RecursiveDirectoryIterator($langPath, RecursiveDirectoryIterator::SKIP_DOTS)
);

$successCount = 0;

echo "🔄 Processing and block-sorting language files...\n\n";

foreach ($iterator as $file) {
    if ($file->getExtension() === 'php') {
        $filePath = $file->getPathname();
        $relativePath = str_replace($langPath . '/', '', $filePath);
        
        // Backup file
        $backupFile = $backupPath . '/' . $relativePath;
        @mkdir(dirname($backupFile), 0755, true);
        copy($filePath, $backupFile);

        $lines = file($filePath);
        if ($lines === false) continue;

        // Parse file into blocks: [ 'comments' => [...], 'items' => [ 'key' => 'full_line_text' ] ]
        $blocks = [];
        $currentComments = [];
        $currentKey = null;
        $currentLines = [];
        
        $parsingArray = false;

        foreach ($lines as $line) {
            $trimmed = trim($line);

            // Detect start of return [
            if (!$parsingArray) {
                if (str_contains($trimmed, 'return [')) {
                    $parsingArray = true;
                }
                continue;
            }

            // Detect end of array ]
            if ($trimmed === '];') {
                if ($currentKey !== null) {
                    $blocks[count($blocks) - 1]['items'][$currentKey] = implode('', $currentLines);
                }
                break;
            }

            // Check if line is a comment or whitespace outside of an active key
            if (str_starts_with($trimmed, '//') || $trimmed === '') {
                if ($currentKey !== null) {
                    // Save previous key-value item
                    $blocks[count($blocks) - 1]['items'][$currentKey] = implode('', $currentLines);
                    $currentKey = null;
                    $currentLines = [];
                }
                $currentComments[] = $line;
                continue;
            }

            // Check if line starts a new key-value pair (e.g., 'key.name' =>)
            if (preg_match('/^\s*[\'"]([a-zA-Z0-9_\.-]+)[\'"]\s*=>/', $line, $matches)) {
                if ($currentKey !== null) {
                    // Save previous item
                    $blocks[count($blocks) - 1]['items'][$currentKey] = implode('', $currentLines);
                }

                $currentKey = $matches[1];
                $currentLines = [$line];

                // If this is the start of a new block (we collected comments prior)
                if (!empty($currentComments)) {
                    $blocks[] = [
                        'comments' => $currentComments,
                        'items' => []
                    ];
                    $currentComments = [];
                } elseif (empty($blocks)) {
                    // Fallback block if no comments at the very top
                    $blocks[] = [
                        'comments' => [],
                        'items' => []
                    ];
                }
            } else {
                // Continuation line of a multi-line value
                if ($currentKey !== null) {
                    $currentLines[] = $line;
                }
            }
        }

        // Rebuild the file content with sorted items inside each block
        $newContent = "<?php\ndeclare(strict_types=1);\n\nreturn [\n";

        foreach ($blocks as $block) {
            // Write block comments if any
            if (!empty($block['comments'])) {
                foreach ($block['comments'] as $comLine) {
                    $newContent .= $comLine;
                }
            }

            // Sort items alphabetically by key within this specific block
            $items = $block['items'];
            uksort($items, 'strcasecmp');

            foreach ($items as $itemLine) {
                $newContent .= $itemLine;
            }
        }

        $newContent .= "];\n";

        file_put_contents($filePath, $newContent);
        echo "  ✔ Block-sorted: {$relativePath}\n";
        $successCount++;
    }
}

echo "\n✨ Success! Block-sorted {$successCount} files while keeping all tracking comments intact.\n";
echo "📁 Backup saved in: {$backupPath}\n";
