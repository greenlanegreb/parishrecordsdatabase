<?php

return [
    'version'     => 9,
    'description' => 'Add field_subtype, field_options, and allow_multiple columns to feedback_columns table',
    'up'          => function (PDO $pdo) {
        $chk1 = $pdo->query("SHOW COLUMNS FROM feedback_columns LIKE 'field_subtype'");
        if (!$chk1->fetch()) {
            $pdo->exec("ALTER TABLE feedback_columns ADD COLUMN field_subtype VARCHAR(50) NULL AFTER data_type");
        }

        $chk2 = $pdo->query("SHOW COLUMNS FROM feedback_columns LIKE 'field_options'");
        if (!$chk2->fetch()) {
            $pdo->exec("ALTER TABLE feedback_columns ADD COLUMN field_options TEXT NULL AFTER field_subtype");
        }

        $chk3 = $pdo->query("SHOW COLUMNS FROM feedback_columns LIKE 'allow_multiple'");
        if (!$chk3->fetch()) {
            $pdo->exec("ALTER TABLE feedback_columns ADD COLUMN allow_multiple TINYINT(1) DEFAULT 0 AFTER field_options");
        }
    },
];
