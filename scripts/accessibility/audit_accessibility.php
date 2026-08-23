<?php
// scripts/accessibility/audit_accessibility.php

$viewsPath = realpath(__DIR__ . '/../../resources/views'); 
if (!$viewsPath) {
    $viewsPath = realpath(__DIR__ . '/../../'); 
}

$excludeDirs = ['vendor', '.git', 'node_modules', 'scripts', 'lang', 'tests'];

function scanForA11yGaps($dir, $excludeDirs) {
    $iterator = new RecursiveIteratorIterator(
        new RecursiveDirectoryIterator($dir, RecursiveDirectoryIterator::SKIP_DOTS),
        RecursiveIteratorIterator::SELF_FIRST
    );

    $issuesFound = 0;

    echo "♿ Scanning templates in: {$dir} for advanced accessibility & structure checks...\n\n";

    foreach ($iterator as $file) {
        $path = $file->getRealPath();
        
        $skip = false;
        foreach ($excludeDirs as $exclude) {
            if (strpos($path, DIRECTORY_SEPARATOR . $exclude . DIRECTORY_SEPARATOR) !== false) {
                $skip = true;
                break;
            }
        }
        if ($skip) continue;

        if ($file->isFile() && in_array($file->getExtension(), ['php', 'html', 'twig'])) {
            $content = file_get_contents($path);
            if ($content === false) continue;

            $relativePath = str_replace(dirname($dir, 2) . DIRECTORY_SEPARATOR, '', $path);

            // 1. Audit <img> tags for missing 'alt'
            preg_match_all('/<img\b[^>]*>/is', $content, $imgMatches, PREG_OFFSET_CAPTURE);
            foreach ($imgMatches[0] as $match) {
                $tag = $match[0];
                $offset = $match[1];
                $lineNum = substr_count(substr($content, 0, $offset), "\n") + 1;

                if (stripos($tag, 'alt=') === false) {
                    echo "  [Missing Alt] File: {$relativePath} | Line: {$lineNum}\n";
                    echo "    -> " . trim(preg_replace('/\s+/', ' ', $tag)) . "\n\n";
                    $issuesFound++;
                }
            }

            // 2. Audit <input> tags for lacking identifiers/aria (ignoring hidden/honeypots)
            preg_match_all('/<input\b[^>]*>/is', $content, $inputMatches, PREG_OFFSET_CAPTURE);
            foreach ($inputMatches[0] as $match) {
                $tag = $match[0];
                $offset = $match[1];
                $lineNum = substr_count(substr($content, 0, $offset), "\n") + 1;

                $isHiddenType = (stripos($tag, 'type="hidden"') !== false || stripos($tag, "type='hidden'") !== false);
                $isAriaHidden = (stripos($tag, 'aria-hidden="true"') !== false || stripos($tag, "aria-hidden='true'") !== false);
                $isClassHidden = (preg_match('/class=["\'][^"\']*\bd-none\b[^"\']*["\']/i', $tag) || preg_match('/class=["\'][^"\']*\bhidden\b[^"\']*["\']/i', $tag));

                if ($isHiddenType || $isAriaHidden || $isClassHidden) {
                    continue;
                }

                $hasIdentifier = (
                    stripos($tag, 'id=') !== false ||
                    stripos($tag, 'aria-label=') !== false ||
                    stripos($tag, 'aria-labelledby=') !== false
                );

                if (!$hasIdentifier) {
                    echo "  [Input Lacking Identifier/Aria] File: {$relativePath} | Line: {$lineNum}\n";
                    echo "    -> " . trim(preg_replace('/\s+/', ' ', $tag)) . "\n\n";
                    $issuesFound++;
                }
            }

            // 3. Audit <button> tags for icon-only buttons missing aria
            preg_match_all('/<button\b[^>]*>([\s\S]*?)<\/button>/is', $content, $btnMatches, PREG_OFFSET_CAPTURE);
            foreach ($btnMatches[0] as $i => $match) {
                $fullTag = $match[0];
                $offset = $match[1];
                $lineNum = substr_count(substr($content, 0, $offset), "\n") + 1;
                $innerContent = $btnMatches[1][$i][0];

                $hasAria = (stripos($fullTag, 'aria-label') !== false || stripos($fullTag, 'aria-labelledby') !== false);
                $hasText = trim(strip_tags($innerContent)) !== '';
                $hasIcon = (stripos($innerContent, '<i') !== false || stripos($innerContent, '<svg') !== false);

                if ($hasIcon && !$hasAria && !$hasText) {
                    echo "  [Icon Button Missing Aria] File: {$relativePath} | Line: {$lineNum}\n";
                    echo "    -> " . trim(preg_replace('/\s+/', ' ', $fullTag)) . "\n\n";
                    $issuesFound++;
                }
            }

            // 4. Audit for Harmful Tabindex (> 0 disrupts natural keyboard flow)
            preg_match_all('/tabindex\s*=\s*["\']?([1-9]\d*)["\']?/i', $content, $tabMatches, PREG_OFFSET_CAPTURE);
            foreach ($tabMatches[0] as $i => $match) {
                $fullMatch = $match[0];
                $offset = $match[1];
                $lineNum = substr_count(substr($content, 0, $offset), "\n") + 1;

                echo "  [Harmful Tabindex > 0] File: {$relativePath} | Line: {$lineNum}\n";
                echo "    -> Found {$fullMatch} (Avoid positive tabindexes; rely on natural DOM order)\n\n";
                $issuesFound++;
            }

            // 5. Audit for Clickable Non-Interactive Elements (<div> or <span> with onclick but no role/tabindex)
            preg_match_all('/<(div|span)\b[^>]*\bonclick\b[^>]*>/is', $content, $clickMatches, PREG_OFFSET_CAPTURE);
            foreach ($clickMatches[0] as $match) {
                $tag = $match[0];
                $offset = $match[1];
                $lineNum = substr_count(substr($content, 0, $offset), "\n") + 1;

                $hasRole = stripos($tag, 'role=') !== false;
                $hasTabindex = stripos($tag, 'tabindex=') !== false;

                if (!$hasRole || !$hasTabindex) {
                    echo "  [Inaccessible Clickable Element] File: {$relativePath} | Line: {$lineNum}\n";
                    echo "    -> " . trim(preg_replace('/\s+/', ' ', $tag)) . "\n";
                    echo "       (Elements with onclick should have role=\"button\" and tabindex=\"0\" for keyboard users)\n\n";
                    $issuesFound++;
                }
            }

            // 6. Audit Heading Hierarchy (Checks for multiple H1s or skipped levels)
            preg_match_all('/<h([1-6])\b[^>]*>/is', $content, $hMatches, PREG_OFFSET_CAPTURE);
            $h1Count = 0;
            $lastLevel = 0;
            foreach ($hMatches[1] as $i => $levelMatch) {
                $level = (int)$levelMatch[0];
                $offset = $hMatches[0][$i][1];
                $lineNum = substr_count(substr($content, 0, $offset), "\n") + 1;

                if ($level === 1) {
                    $h1Count++;
                    if ($h1Count > 1) {
                        echo "  [Heading Hierarchy] File: {$relativePath} | Line: {$lineNum}\n";
                        echo "    -> Multiple <h1> tags detected on a single page (should typically be only one).\n\n";
                        $issuesFound++;
                    }
                }

                // Check for skipped levels (e.g. jumping from h2 directly to h4)
                if ($lastLevel > 0 && $level > ($lastLevel + 1)) {
                    echo "  [Heading Hierarchy Jump] File: {$relativePath} | Line: {$lineNum}\n";
                    echo "    -> Skipped heading level: jumped from <h{$lastLevel}> to <h{$level}>.\n\n";
                    $issuesFound++;
                }
                $lastLevel = $level;
            }
        }
    }

    echo "--------------------------------------------------\n";
    echo "Audit Complete. Total accessibility & structure warnings: {$issuesFound}\n";
}

scanForA11yGaps($viewsPath, $excludeDirs);
