<?php
declare(strict_types=1);

return [
    'version' => 30,
    'description' => 'Store suggest-edit outcome notification details and seed the outcome email template',
    'up' => static function (PDO $pdo): void {
        $add = static function (PDO $pdo, string $column, string $ddl) {
            $stmt = $pdo->query("SHOW COLUMNS FROM edit_suggestions LIKE " . $pdo->quote($column));
            if ($stmt === false || $stmt->fetch() === false) {
                $pdo->exec("ALTER TABLE edit_suggestions ADD COLUMN {$ddl}");
            }
        };
        $add($pdo, 'notify_outcome', 'notify_outcome TINYINT(1) NOT NULL DEFAULT 0 AFTER reasoning');
        $add($pdo, 'notify_email', 'notify_email VARCHAR(255) NULL DEFAULT NULL AFTER notify_outcome');
        $add($pdo, 'moderator_rationale', 'moderator_rationale TEXT NULL DEFAULT NULL AFTER notify_email');

        $pdo->exec("
            CREATE TABLE IF NOT EXISTS user_email_templates (
                id INT AUTO_INCREMENT PRIMARY KEY,
                trigger_event VARCHAR(100) NOT NULL UNIQUE,
                template_name VARCHAR(255) NOT NULL,
                subject VARCHAR(255) NOT NULL,
                body TEXT NOT NULL,
                updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP
            )
        ");
        $ins = $pdo->prepare(
            'INSERT IGNORE INTO user_email_templates (trigger_event, template_name, subject, body) VALUES (?, ?, ?, ?)'
        );
        $ins->execute([
            'suggestion_outcome',
            'Suggest-edit outcome',
            'Update on your suggested change — {system_name}',
            "Hello {first_name},\n\nA moderator has reviewed the change you suggested on {system_name}.\n\nDecision: {decision}\nField: {column_name}\nYour suggestion: {proposed_value}\n\nTheir note:\n{moderator_rationale}\n\nIf you would like to discuss this further, you can open a support ticket here:\n{feedback_link}\n\nThank you for helping to keep the records accurate.\n\n{system_name}",
        ]);
    },
];
