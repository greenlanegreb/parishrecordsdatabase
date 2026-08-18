<?php
declare(strict_types=1);

/**
 * Book-library demo pack definition.
 */
return [
    'slug' => 'library',
    'label' => 'Book library',
    'summary' => 'A small catalogue layout for titles, authors, and shelf location — useful if you want PRD as a collection register.',
    'tables' => [
        [
            'table_name' => 'Library catalogue (demo)',
            'description' => 'Demo table for a simple book collection. Safe to delete via Demo packs.',
            'columns' => [
                ['column_name' => 'Title', 'data_type' => 'VARCHAR', 'max_length' => 200, 'is_required' => 1],
                ['column_name' => 'Author', 'data_type' => 'VARCHAR', 'max_length' => 120, 'is_required' => 1],
                ['column_name' => 'Year', 'data_type' => 'INT', 'is_required' => 0],
                ['column_name' => 'ISBN', 'data_type' => 'VARCHAR', 'max_length' => 20, 'is_required' => 0],
                ['column_name' => 'Location', 'data_type' => 'VARCHAR', 'max_length' => 80, 'is_required' => 0],
                ['column_name' => 'Notes', 'data_type' => 'TEXT', 'is_required' => 0],
            ],
            'records' => [
                [
                    'Title' => 'A Short History of Exampleton',
                    'Author' => 'H. J. Whitfield',
                    'Year' => '1924',
                    'ISBN' => '',
                    'Location' => 'Shelf A1',
                    'Notes' => 'Demo row — local history pamphlet.',
                ],
                [
                    'Title' => 'Parish Life in the Nineteenth Century',
                    'Author' => 'Clara Naylor',
                    'Year' => '1971',
                    'ISBN' => '9780000000001',
                    'Location' => 'Shelf B3',
                    'Notes' => 'Demo row — secondary reading.',
                ],
                [
                    'Title' => 'Maps of the Green Lane district',
                    'Author' => 'Survey Office',
                    'Year' => '1888',
                    'ISBN' => '',
                    'Location' => 'Oversize drawer',
                    'Notes' => 'Demo row — not for loan.',
                ],
            ],
        ],
    ],
];
