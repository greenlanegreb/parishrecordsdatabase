<?php
/**
 * MIGRATED FILE MAPPING
 * ---------------------
 * Original Old File: user/verify_2fa.php/user/actions/save_verify_2fa.php
 * Migrated Date: 2026-08-05 05:30:28
 */declare(strict_types=1);


namespace App\Controllers;

class UserVerify2faController
{
    public function show(): void
    {
        if (session_status() === PHP_SESSION_NONE) {
            session_start();
        }

        if (!isset($_SESSION['pending_2fa_user_id'])) {
            http_response_code(403);
            $remoteAddr = isset($_SERVER['REMOTE_ADDR']) && is_string($_SERVER['REMOTE_ADDR']) ? $_SERVER['REMOTE_ADDR'] : '127.0.0.1';
            error_log("Unauthorized direct access attempt to verify_2fa.php from IP: " . $remoteAddr);
            header('Location: /user/login.php');
            exit;
        }

        $error = $_SESSION['error'] ?? '';
        unset($_SESSION['error']);

        require_once __DIR__ . '/../Views/user/verify_2fa.php';
    }
}
