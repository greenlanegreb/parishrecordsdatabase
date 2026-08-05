<?php
/**
 * MIGRATED FILE MAPPING
 * ---------------------
 * Original Old File: user/logout.php
 * Migrated Date: 2026-08-05 05:02:08
 */declare(strict_types=1);


namespace App\Controllers;

class UserLogoutController
{
    public function logout(): void
    {
        if (session_status() === PHP_SESSION_NONE) {
            session_start();
        }

        // Unset all session variables
        $_SESSION = [];

        // If it's desired to kill the session, also delete the session cookie.
        if (ini_get("session.use_cookies")) {
            $params = session_get_cookie_params();
            setcookie(
                session_name(),
                '',
                time() - 42000,
                $params["path"],
                $params["domain"],
                $params["secure"],
                $params["httponly"]
            );
        }

        // Finally, destroy the session
        session_destroy();

        // Redirect back to the login page
        header("Location: /user/login.php");
        exit;
    }
}
