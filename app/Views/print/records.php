<?php
declare(strict_types=1);
$basePath = defined('BASE_PATH') ? rtrim((string) BASE_PATH, '/') : '';
$systemName = isset($systemName) && is_string($systemName) && $systemName !== '' ? $systemName : 'pRD';
$tableName = isset($tableName) && is_string($tableName) ? $tableName : '';
$printedAt = isset($printedAt) && is_string($printedAt) ? $printedAt : date('Y-m-d H:i');
$tid = isset($_GET['table_id']) ? (int) $_GET['table_id'] : 0;
$brandLine = 'pRD — ' . $systemName;
?>
<!DOCTYPE html>
<html lang="<?= htmlspecialchars(function_exists('get_active_language') ? get_active_language() : 'en', ENT_QUOTES, 'UTF-8') ?>">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?= htmlspecialchars($brandLine . ' · ' . $tableName, ENT_QUOTES, 'UTF-8') ?></title>
    <style>
        :root { color-scheme: light; }
        body {
            font-family: Georgia, "Times New Roman", serif;
            color: #111;
            margin: 1.25rem;
            line-height: 1.45;
        }
        .no-print { margin-bottom: 1rem; font-family: system-ui, sans-serif; }
        .doc-head {
            border-bottom: 2px solid #222;
            padding-bottom: 0.6rem;
            margin-bottom: 1rem;
        }
        .brand {
            font-family: system-ui, sans-serif;
            font-size: 0.8rem;
            letter-spacing: 0.04em;
            text-transform: uppercase;
            color: #444;
            margin: 0 0 0.2rem;
        }
        h1 {
            font-size: 1.35rem;
            font-weight: 700;
            margin: 0 0 0.25rem;
        }
        .meta {
            font-family: system-ui, sans-serif;
            font-size: 0.8rem;
            color: #444;
            margin: 0;
        }
        table {
            width: 100%;
            border-collapse: collapse;
            table-layout: auto;
            font-size: 11pt;
        }
        thead { display: table-header-group; }
        tfoot { display: table-footer-group; }
        th, td {
            border-bottom: 1px solid #ccc;
            padding: 0.45rem 0.55rem;
            text-align: left;
            vertical-align: top;
            overflow-wrap: anywhere;
            hyphens: auto;
        }
        th {
            background: #f4f4f4;
            font-family: system-ui, sans-serif;
            font-size: 0.78rem;
            font-weight: 700;
            text-transform: uppercase;
            letter-spacing: 0.02em;
            border-bottom: 2px solid #222;
        }
        tr:nth-child(even) td { background: #fafafa; }
        @page {
            size: A4 landscape;
            margin: 16mm 12mm 18mm;
            @bottom-left {
                content: "<?= htmlspecialchars($brandLine, ENT_QUOTES, 'UTF-8') ?>";
                font-size: 9pt;
                color: #444;
            }
            @bottom-right {
                content: "Page " counter(page) " of " counter(pages);
                font-size: 9pt;
                color: #444;
            }
        }
        @media print {
            .no-print { display: none !important; }
            body { margin: 0; }
            a { color: inherit; text-decoration: none; }
        }
        @media screen {
            body { max-width: 1100px; margin: 1.5rem auto; }
        }
    </style>
</head>
<body>
    <div class="no-print">
        <button type="button" onclick="window.print()"><?= htmlspecialchars(__('cols.print_now') !== 'cols.print_now' ? __('cols.print_now') : 'Print or save as PDF', ENT_QUOTES, 'UTF-8') ?></button>
        <a href="<?= htmlspecialchars($basePath !== '' ? $basePath . '/' : '/', ENT_QUOTES, 'UTF-8') ?>"><?= htmlspecialchars(__('cols.back') !== 'cols.back' ? __('cols.back') : 'Back', ENT_QUOTES, 'UTF-8') ?></a>
    </div>

    <header class="doc-head">
        <p class="brand">pRD</p>
        <h1><?= htmlspecialchars($tableName, ENT_QUOTES, 'UTF-8') ?></h1>
        <p class="meta"><?= htmlspecialchars($systemName, ENT_QUOTES, 'UTF-8') ?> · <?= htmlspecialchars($printedAt, ENT_QUOTES, 'UTF-8') ?></p>
    </header>

    <table>
        <thead>
            <tr>
                <?php foreach ($columns as $col): ?>
                    <th><?= htmlspecialchars((string) ($col['column_name'] ?? ''), ENT_QUOTES, 'UTF-8') ?></th>
                <?php endforeach; ?>
                <?php if (!function_exists('show_created_by_column') || show_created_by_column($tid)): ?>
                    <th><?= htmlspecialchars(__('index.th_created_by') !== 'index.th_created_by' ? __('index.th_created_by') : 'Created by', ENT_QUOTES, 'UTF-8') ?></th>
                <?php endif; ?>
                <?php if (!function_exists('show_created_at_column') || show_created_at_column($tid)): ?>
                    <th><?= htmlspecialchars(__('index.th_date_added') !== 'index.th_date_added' ? __('index.th_date_added') : 'Date added', ENT_QUOTES, 'UTF-8') ?></th>
                <?php endif; ?>
            </tr>
        </thead>
        <tbody>
            <?php foreach ($records as $rec): ?>
                <?php
                    $recId = isset($rec['id']) ? (int) $rec['id'] : 0;
                    if (function_exists('record_matches_filters')
                        && !record_matches_filters($recId, $recordValues, $searchFilters, $dateFilters)) {
                        continue;
                    }
                ?>
                <tr>
                    <?php foreach ($columns as $col): ?>
                        <?php
                            $cid = isset($col['id']) ? (int) $col['id'] : 0;
                            $raw = $recordValues[$recId][$cid] ?? '';
                            $dtype = isset($col['data_type']) && is_string($col['data_type']) ? $col['data_type'] : '';
                            if ($dtype === 'DATE' && function_exists('format_display_date')) {
                                $raw = format_display_date($raw, $userDateFormat);
                            } elseif ($dtype === 'BOOLEAN' && function_exists('format_boolean_value')) {
                                $fmt = isset($col['boolean_display_format']) ? (string) $col['boolean_display_format'] : 'yes_no';
                                $raw = format_boolean_value($raw, $fmt);
                            }
                        ?>
                        <td><?= htmlspecialchars((string) $raw, ENT_QUOTES, 'UTF-8') ?></td>
                    <?php endforeach; ?>
                    <?php if (!function_exists('show_created_by_column') || show_created_by_column($tid)): ?>
                        <td><?= htmlspecialchars((string) ($rec['username'] ?? ''), ENT_QUOTES, 'UTF-8') ?></td>
                    <?php endif; ?>
                    <?php if (!function_exists('show_created_at_column') || show_created_at_column($tid)): ?>
                        <td><?= htmlspecialchars((string) ($rec['created_at'] ?? ''), ENT_QUOTES, 'UTF-8') ?></td>
                    <?php endif; ?>
                </tr>
            <?php endforeach; ?>
        </tbody>
    </table>
</body>
</html>
