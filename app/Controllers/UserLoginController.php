<?php
/**
 * MIGRATED FILE MAPPING
 * ---------------------
 * Original Old File: user/login.php/user/actions/authenticate.php
 * Migrated Date: 2026-08-05 04:58:44
 */
declare(strict_types=1);

namespace App\Controllers;

class UserLoginController
{
    public function show(): void
    {
        // If the user is already authenticated, redirect them away from the login page
        if (isset($_SESSION['user_id'])) {
            $basePath = defined('BASE_PATH') ? rtrim(BASE_PATH, '/') : '';
            header('Location: ' . $basePath . '/data-entry');
            exit;
        }

        $error = $_SESSION['error'] ?? '';
        $message = $_SESSION['message'] ?? '';
        unset($_SESSION['error'], $_SESSION['message']);

        require_once __DIR__ . '/../Views/user/login.php';
    }
}
