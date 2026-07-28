<?php
// tests/PermissionHelpersTest.php - Smoke tests for permission helpers
declare(strict_types=1);

use PHPUnit\Framework\TestCase;

final class PermissionHelpersTest extends TestCase
{
    private PDO $pdo;

    protected function setUp(): void
    {
        $this->pdo = $GLOBALS['pdo'] ?? null;
        if (!$this->pdo instanceof PDO) {
            $this->fail('No PDO connection — check tests/bootstrap.php and tests/db.php');
        }

        if (session_status() === PHP_SESSION_ACTIVE) {
            $_SESSION = [];
        } else {
            session_start();
            $_SESSION = [];
        }
        unset($_SESSION['user_id']);
    }

    public function test_guest_has_permission_function_exists(): void
    {
        $this->assertTrue(function_exists('guest_has_permission'));
        $this->assertTrue(function_exists('has_permission'));
    }

    public function test_guest_has_permission_returns_boolean(): void
    {
        $result = guest_has_permission($this->pdo, 'view_public');
        $this->assertIsBool($result);
    }

    public function test_guest_view_public_matches_database_matrix(): void
    {
        $helperSays = guest_has_permission($this->pdo, 'view_public');

        $stmt = $this->pdo->prepare("
            SELECT COUNT(*)
            FROM role_permissions rp
            JOIN roles r ON rp.role_id = r.id
            JOIN permissions p ON rp.permission_id = p.id
            WHERE p.permission_key = 'view_public'
              AND LOWER(r.role_name) = 'guest'
        ");
        $stmt->execute();
        $dbSays = ((int) $stmt->fetchColumn()) > 0;

        $this->assertSame(
            $dbSays,
            $helperSays,
            'guest_has_permission(view_public) must match the role_permissions matrix'
        );
    }

    public function test_has_permission_without_login_uses_guest_fallback(): void
    {
        unset($_SESSION['user_id']);

        $helperSays = has_permission($this->pdo, 'view_public');

        $stmt = $this->pdo->prepare("
            SELECT COUNT(*)
            FROM role_permissions rp
            JOIN roles r ON rp.role_id = r.id
            JOIN permissions p ON rp.permission_id = p.id
            WHERE p.permission_key = 'view_public'
              AND LOWER(r.role_name) = 'guest'
        ");
        $stmt->execute();
        $dbSays = ((int) $stmt->fetchColumn()) > 0;

        $this->assertSame(
            $dbSays,
            $helperSays,
            'has_permission without login should follow guest matrix for view_public'
        );
    }

    public function test_unknown_permission_returns_boolean(): void
    {
        $result = guest_has_permission($this->pdo, 'phpunit_smoke_permission_that_should_not_exist_xyz');
        $this->assertIsBool($result);
        $this->assertFalse($result);
    }
}
