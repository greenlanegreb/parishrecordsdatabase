<?php
declare(strict_types=1);

/**
 * Parish-records demo pack definition.
 */
return [
    'slug' => 'parish',
    'label' => 'Parish registers',
    'summary' => 'A small set of columns for baptisms, marriages, and burials — the sort of workflow pRD was first built for.',
    'tables' => [
        [
            'table_name' => 'Parish registers (demo)',
            'description' => 'Demo table for parish-style events. Safe to delete via Demo packs.',
            'columns' => [
                [
                    'column_name' => 'Event type',
                    'data_type' => 'SELECT',
                    'is_required' => 1,
                    'field_options' => "Baptism\nMarriage\nBurial",
                ],
                ['column_name' => 'Forename', 'data_type' => 'VARCHAR', 'max_length' => 80, 'is_required' => 1],
                ['column_name' => 'Surname', 'data_type' => 'VARCHAR', 'max_length' => 80, 'is_required' => 1],
                ['column_name' => 'Event date', 'data_type' => 'DATE', 'date_search_behavior' => 'range', 'is_required' => 0],
                ['column_name' => 'Place', 'data_type' => 'VARCHAR', 'max_length' => 120, 'is_required' => 0],
                ['column_name' => 'Notes', 'data_type' => 'TEXT', 'is_required' => 0],
            ],
            'records' => [
                [
                    'Event type' => 'Baptism',
                    'Forename' => 'Mary',
                    'Surname' => 'Ashworth',
                    'Event date' => '1842-03-14',
                    'Place' => 'St Mary, Exampleton',
                    'Notes' => 'Demo row — daughter of John and Ann Ashworth.',
                ],
                [
                    'Event type' => 'Marriage',
                    'Forename' => 'Thomas',
                    'Surname' => 'Bennett',
                    'Event date' => '1861-06-02',
                    'Place' => 'St Mary, Exampleton',
                    'Notes' => 'Demo row — to Sarah Ellis.',
                ],
                [
                    'Event type' => 'Burial',
                    'Forename' => 'Ellen',
                    'Surname' => 'Carter',
                    'Event date' => '1878-11-21',
                    'Place' => 'Exampleton churchyard',
                    'Notes' => 'Demo row — age recorded as 74.',
                ],
            ],
        ],
    ],
];
