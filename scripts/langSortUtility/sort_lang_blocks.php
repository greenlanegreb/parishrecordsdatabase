<?php
// Save as sort_lang_blocks.php in your project root
// Run via terminal: php sort_lang_blocks.php

declare(strict_types=1);

$langPath = __DIR__ . '/../../lang'; 
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

        $blocks = [];
        $headerComments = []; // Captures anything between declare() and return [
        $leadingComments = []; // Captures comments right at the top of return [
        $currentComments = [];
        $currentKey = null;
        $currentLines = [];
        
        $parsingArray = false;

        foreach ($lines as $line) {
            $trimmed = trim($line);

            // Detect start of return [ and capture everything above it as header comments
            if (!$parsingArray) {
                if (str_contains($trimmed, 'return [')) {
                    $parsingArray = true;
                } else {
                    // Collect comments and spacing between declare() and return [
                    if (!str_contains($trimmed, '<?php') && !str_contains($trimmed, 'declare')) {
                        $headerComments[] = $line;
                    }
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

                // If no blocks exist yet, these are leading comments right inside return [
                if (empty($blocks)) {
                    $leadingComments[] = $line;
                } else {
                    $currentComments[] = $line;
                }
                continue;
            }

            // Check if line starts a new key-value pair
            if (preg_match('/^\s*[\'"]([a-zA-Z0-9_\.-]+)[\'"]\s*=>/', $line, $matches)) {
                if ($currentKey !== null) {
                    $blocks[count($blocks) - 1]['items'][$currentKey] = implode('', $currentLines);
                }

                $currentKey = $matches[1];
                $currentLines = [$line];

                // If we collected comments prior, create a new block
                if (!empty($currentComments)) {
                    $blocks[] = [
                        'comments' => $currentComments,
                        'items' => []
                    ];
                    $currentComments = [];
                } elseif (empty($blocks)) {
                    // Fallback block if no comments at all before this key
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

        // Rebuild the file content safely keeping top header and block comments
        $newContent = "<?php\ndeclare(strict_types=1);\n";

        // Print header comments captured between declare and return [
        foreach ($headerComments as $hLine) {
            $newContent .= $hLine;
        }

        $newContent .= "return [\n";

        // Print any leading comments inside return [
        foreach ($leadingComments as $leadLine) {
            $newContent .= $leadLine;
        }

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

echo "\n✨ Success! Block-sorted {$successCount} files while protecting all header, leading, and tracking comments.\n";
echo "📁 Backup saved in: {$backupPath}\n";
