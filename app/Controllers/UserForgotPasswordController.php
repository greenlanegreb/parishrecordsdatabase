<?php
/**
 * MIGRATED FILE MAPPING
 * ---------------------
 * Original Old File: user/forgot_password.php/user/actions/save_forgot_password.php
 * Migrated Date: 2026-08-05 04:54:16
 */declare(strict_types=1);


namespace App\Controllers;

class UserForgotPasswordController
{
    public function show(): void
    {
        if (session_status() === PHP_SESSION_NONE) {
            session_start();
        }

        $error = $_SESSION['error'] ?? '';
        $message = $_SESSION['message'] ?? '';
        unset($_SESSION['error'], $_SESSION['message']);

        require_once __DIR__ . '/../Views/user/forgot_password.php';
    }
}
