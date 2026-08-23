<?php
/**
 * MIGRATED FILE MAPPING
 * ---------------------
 * Original Old File: user/register.php/user/actions/save_register.php
 * Migrated Date: 2026-08-05 05:14:08
 */
declare(strict_types=1);

namespace App\Controllers;

use PDO;

class UserRegisterController
{
    private PDO $pdo;

    public function __construct(PDO $pdo)
    {
        $this->pdo = $pdo;
    }

    public function show(): void
    {
        // Ensure the users module is enabled; otherwise block access to registration
        if (!is_module_enabled($this->pdo, 'users')) {
            http_response_code(403);
            exit('403 Forbidden: The User Management module is currently disabled.');
        }

        $message = $_SESSION['message'] ?? '';
        $error = $_SESSION['error'] ?? '';
        $fieldErrors = $_SESSION['field_errors'] ?? [];
        $oldUsername = $_SESSION['old_username'] ?? '';
        $oldEmail = $_SESSION['old_email'] ?? '';

        unset($_SESSION['message'], $_SESSION['error']);

        require_once __DIR__ . '/../Views/user/register.php';
    }
}
