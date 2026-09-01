<?php
/**
 * MIGRATED FILE MAPPING
 * ---------------------
 * Original Old File: admin/users.php/admin/actions/save_user_management.php
 * Migrated Date: 2026-08-05 03:50:05
 */
declare(strict_types=1);

namespace App\Controllers;

use App\Services\UserService;
use Exception;
use PDO;

class AdminUsersController
{
    private PDO $pdo;

    public function __construct(PDO $pdo)
    {
        $this->pdo = $pdo;
    }

    public function index(): void
    {
        require_module($this->pdo, 'users');

        /** @var array{id: int, username: string, timezone?: string} $currentUser */
        $currentUser = require_user_admin($this->pdo, 'manage_users', 'Manage user accounts, roles, and status');

        $message = $GLOBALS['message'] ?? '';
        $error   = $GLOBALS['error'] ?? '';
        $queryGet = $_GET;

        $prefillEmail   = isset($queryGet['email']) && is_string($queryGet['email']) ? trim($queryGet['email']) : '';
        $prefillFirst   = isset($queryGet['first_name']) && is_string($queryGet['first_name']) ? trim($queryGet['first_name']) : '';
        $prefillSurname = isset($queryGet['surname']) && is_string($queryGet['surname']) ? trim($queryGet['surname']) : '';
        $volunteerId    = isset($queryGet['volunteer_id']) ? (int) $queryGet['volunteer_id'] : 0;

        $firstAdminId = 1;
        try {
            $faStmt = $this->pdo->query("
                SELECT u.id FROM users u
                JOIN roles r ON u.role_id = r.id
                WHERE LOWER(r.role_name) = 'admin'
                ORDER BY u.created_at ASC, u.id ASC
                LIMIT 1
            ");
            $faId = $faStmt !== false ? $faStmt->fetchColumn() : false;
            if ($faId !== false && $faId !== null) {
                $firstAdminId = (int) $faId;
            }
        } catch (Exception $e) {
            // Fallback to 1
        }

        $usersStmt = $this->pdo->query("
            SELECT u.id, u.username, u.email, u.points, u.email_verified, u.two_fa_enabled,
                   (u.password_hash IS NOT NULL AND u.password_hash <> '') AS has_login_password,
                   u.is_active, u.created_at, u.role_id, r.role_name
            FROM users u
            LEFT JOIN roles r ON u.role_id = r.id
            ORDER BY u.created_at DESC
        ");
        /** @var array<int, array<string, mixed>> $users */
        $users = $usersStmt !== false ? $usersStmt->fetchAll(PDO::FETCH_ASSOC) : [];

        $rolesStmt = $this->pdo->query("SELECT id, role_name FROM roles ORDER BY id ASC");
        /** @var array<int, array<string, mixed>> $rolesList */
        $rolesList = $rolesStmt !== false ? $rolesStmt->fetchAll(PDO::FETCH_ASSOC) : [];

        $userTimezone  = isset($currentUser['timezone']) && is_string($currentUser['timezone'])
            ? $currentUser['timezone'] : 'UTC';
        $fullFormatStr = get_user_datetime_format($currentUser);
        $basePath      = defined('BASE_PATH') && is_string(BASE_PATH) ? rtrim(BASE_PATH, '/') : '';

        require_once __DIR__ . '/../Views/admin/users.php';
    }

    public function createForm(): void
    {
        require_module($this->pdo, 'users');

        /** @var array{id: int, username: string, timezone?: string} $currentUser */
        $currentUser = require_user_admin($this->pdo, 'manage_users', 'Manage user accounts, roles, and status');

        $message = $GLOBALS['message'] ?? '';
        $error   = $GLOBALS['error'] ?? '';
        $queryGet = $_GET;

                $prefillEmail   = isset($queryGet['email']) && is_string($queryGet['email']) ? trim($queryGet['email']) : '';
        $prefillFirst   = isset($queryGet['first_name']) && is_string($queryGet['first_name']) ? trim($queryGet['first_name']) : '';
        $prefillSurname = isset($queryGet['surname']) && is_string($queryGet['surname']) ? trim($queryGet['surname']) : '';
        $volunteerId    = isset($queryGet['volunteer_id']) ? (int) $queryGet['volunteer_id'] : 0;
        $prefillUsername = '';

        if ($volunteerId > 0) {
            $vStmt = $this->pdo->prepare(
                'SELECT preferred_username, first_name, surname, email
                 FROM volunteer_submissions WHERE id = ? LIMIT 1'
            );
            $vStmt->execute([$volunteerId]);
            $vol = $vStmt->fetch(PDO::FETCH_ASSOC);
            if (is_array($vol)) {
                if ($prefillEmail === '' && !empty($vol['email']) && is_string($vol['email'])) {
                    $prefillEmail = trim($vol['email']);
                }
                if ($prefillFirst === '' && !empty($vol['first_name']) && is_string($vol['first_name'])) {
                    $prefillFirst = trim($vol['first_name']);
                }
                if ($prefillSurname === '' && !empty($vol['surname']) && is_string($vol['surname'])) {
                    $prefillSurname = trim($vol['surname']);
                }
                if (!empty($vol['preferred_username']) && is_string($vol['preferred_username'])) {
                    $prefillUsername = trim($vol['preferred_username']);
                }
            }
        }

        $rolesStmt = $this->pdo->query("SELECT id, role_name FROM roles ORDER BY id ASC");
        /** @var array<int, array{id: int, role_name: string}> $rolesList */
        $rolesList = $rolesStmt !== false ? $rolesStmt->fetchAll(PDO::FETCH_ASSOC) : [];

        $basePath = defined('BASE_PATH') && is_string(BASE_PATH) ? rtrim(BASE_PATH, '/') : '';

        require_once __DIR__ . '/../Views/admin/create_user.php';
    }

    public function handleAction(): void
    {
        require_module($this->pdo, 'users');

        if (($_SERVER['REQUEST_METHOD'] ?? 'GET') !== 'POST') {
            http_response_code(405);
            exit('Method Not Allowed');
        }

        verify_csrf_token();

        /** @var array{id: int, username: string} $currentUser */
        $currentUser = require_user_permission($this->pdo, 'manage_users', 'Manage user accounts, roles, and status');

        $post         = $_POST;
        $action       = isset($post['action']) && is_string($post['action']) ? $post['action'] : '';
        $targetUserId = isset($post['target_user_id']) ? (int) $post['target_user_id'] : 0;

        if ($targetUserId <= 0) {
            flash_error('Invalid target user specified.');
            redirect('/admin/users');
        }

        $targetStmt = $this->pdo->prepare('SELECT id, username FROM users WHERE id = ?');
        $targetStmt->execute([$targetUserId]);
        /** @var array{id: int, username: string}|false $targetUser */
        $targetUser = $targetStmt->fetch(PDO::FETCH_ASSOC);

        if ($targetUser === false) {
            flash_error('User not found.');
            redirect('/admin/users');
        }

        // Protect the first admin account
        $firstAdminId = 1;
        try {
            $faStmt = $this->pdo->query("
                SELECT u.id FROM users u
                JOIN roles r ON u.role_id = r.id
                WHERE LOWER(r.role_name) = 'admin'
                ORDER BY u.created_at ASC, u.id ASC
                LIMIT 1
            ");
            $faId = $faStmt !== false ? $faStmt->fetchColumn() : false;
            if ($faId !== false && $faId !== null) {
                $firstAdminId = (int) $faId;
            }
        } catch (Exception $e) {
            // Fallback to 1
        }

        if ($targetUserId === $firstAdminId && in_array($action, ['change_role', 'suspend', 'delete'], true)) {
            http_response_code(403);
            flash_error('Security Error: The primary system administrator account cannot be modified or deleted.');
            redirect('/admin/users');
        }

        $remoteAddr  = isset($_SERVER['REMOTE_ADDR']) && is_string($_SERVER['REMOTE_ADDR'])
            ? $_SERVER['REMOTE_ADDR'] : '127.0.0.1';
        $userService = new UserService($this->pdo);

        try {
            switch ($action) {
                case 'change_role':
                    $newRoleId = isset($post['new_role_id']) ? (int) $post['new_role_id'] : 0;
                    flash_success($userService->changeRole($targetUserId, $targetUser, $newRoleId, $currentUser, $remoteAddr));
                    break;

                case 'override_points':
                    $newPoints = isset($post['new_points']) ? (int) $post['new_points'] : 0;
                    flash_success($userService->overridePoints($targetUserId, $targetUser, $newPoints, $currentUser, $remoteAddr));
                    break;

                case 'update_email':
                    $newEmail = isset($post['new_email']) && is_string($post['new_email']) ? trim($post['new_email']) : '';
                    flash_success($userService->updateEmail($targetUserId, $targetUser, $newEmail, $currentUser, $remoteAddr));
                    break;

                case 'send_password_reset':
                    flash_success($userService->sendPasswordReset($targetUserId, $currentUser, $remoteAddr));
                    break;

                case 'resend_invite':
                    flash_success($userService->resendInvite($targetUserId, $currentUser, $remoteAddr));
                    break;

                case 'suspend':
                    flash_success($userService->suspendUser($targetUserId, $targetUser, $currentUser, $remoteAddr));
                    break;

                case 'unsuspend':
                    flash_success($userService->unsuspendUser($targetUserId, $targetUser, $currentUser, $remoteAddr));
                    break;

                case 'delete':
                    flash_success($userService->deleteUser($targetUserId, $targetUser, $currentUser, $remoteAddr));
                    break;

                case 'reset_2fa':
                    flash_success($userService->reset2fa($targetUserId, $targetUser, $currentUser, $remoteAddr));
                    break;

                default:
                    flash_error('Unknown management action requested.');
                    break;
            }
        } catch (Exception $e) {
            flash_error('Database error: ' . $e->getMessage());
        }

        redirect('/admin/users');
    }
}
