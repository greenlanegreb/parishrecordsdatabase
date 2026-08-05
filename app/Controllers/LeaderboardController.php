<?php
/**
 * MIGRATED FILE MAPPING
 * ---------------------
 * Original Old File: roote/leaderboard.php
 * Migrated Date: 2026-08-05 06:45:49
 */declare(strict_types=1);


namespace App\Controllers;

use PDO;

class LeaderboardController
{
    private PDO $pdo;

    public function __construct(PDO $pdo)
    {
        $this->pdo = $pdo;
    }

    public function index(): void
    {
        if (session_status() === PHP_SESSION_NONE) {
            session_start();
        }

        require_once __DIR__ . '/../../db/auth_helpers.php';
        require_once __DIR__ . '/../../includes/functions.php';

        if (!is_module_enabled($this->pdo, 'leaderboard')) {
            http_response_code(403);
            exit('403 Forbidden: The Leaderboard module is currently disabled.');
        }

        /** @var array{id: int|string, username: string, first_name?: string, surname?: string, points?: int|string, role?: string, attribution_display_mode?: string}|null $currentUser */
        $currentUser = function_exists('get_current_user_data') ? get_current_user_data($this->pdo) : null;
        $isLoggedIn = ($currentUser !== null || isset($_SESSION['user_id']));

        $hasPublicPermission = guest_has_permission($this->pdo, 'view_leaderboard');

        if ($currentUser === null && !$hasPublicPermission) {
            $base = defined('BASE_PATH') && is_string(BASE_PATH) ? rtrim(BASE_PATH, '/') : '';
            header('Location: ' . $base . '/user/login.php');
            exit;
        }

        $stmt = $this->pdo->prepare("
            SELECT u.id, u.username, u.first_name, u.surname, u.points, r.role_name AS role, u.attribution_display_mode, u.is_active
            FROM users u
            LEFT JOIN roles r ON u.role_id = r.id
            WHERE u.is_active = 1
            ORDER BY u.points DESC, u.username ASC
            LIMIT 50
        ");
        $stmt->execute();
        /** @var array<int, array<string, mixed>> $allUsers */
        $allUsers = $stmt->fetchAll(PDO::FETCH_ASSOC);

        $leaderboardUsers = [];
        foreach ($allUsers as $u) {
            $mode = isset($u['attribution_display_mode']) && is_string($u['attribution_display_mode']) ? $u['attribution_display_mode'] : '';
            if (!in_array($mode, ['initials_random', 'full_name', 'volunteers_only'], true)) {
                $mode = 'initials_random';
            }

            if ($mode === 'volunteers_only' && !$isLoggedIn) {
                continue;
            }

            $u['display_name'] = format_user_display_name($this->pdo, $u, $currentUser);
            $leaderboardUsers[] = $u;
        }

        // Pass variables to View
        require_once __DIR__ . '/../Views/leaderboard/index.php';
    }
}
