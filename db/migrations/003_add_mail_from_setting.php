<?php
// db/migrations/003_add_mail_from_setting.php

return [
    'version'     => 3,
    'description' => 'Add mail_from configuration key to site_settings table',
    'up'          => function (PDO $pdo) {
        // Idempotent check: safely insert the mail_from setting if it doesn't already exist
        $stmt = $pdo->prepare("SELECT setting_value FROM site_settings WHERE setting_key = ?");
        $stmt->execute(['mail_from']);
        if (!$stmt->fetch()) {
            $insert = $pdo->prepare("INSERT INTO site_settings (setting_key, setting_value) VALUES (?, ?)");
            $insert->execute(['mail_from', '']);
        }
    },
];
