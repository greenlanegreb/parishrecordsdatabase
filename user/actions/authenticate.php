<?php
// user/actions/authenticate.php - Handles user credential verification and 2FA routing
require_once '../../db/db.php';
session_start();

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $username = trim($_POST['username'] ?? '');
    $password = $_POST['password'] ?? '';
    
    try {
        // Added is_new_user to the select query
        $stmt = $pdo->prepare("SELECT id, username, password_hash, two_fa_enabled, google_2fa_secret, is_active, is_new_user FROM users WHERE username = ? OR email = ?");
        $stmt->execute([$username, $username]);
        $user = $stmt->fetch();
    } catch (PDOException $e) {
        // SQLSTATE 42S22 = Unknown column, SQLSTATE 42S02 = Base table or view not found
        if (in_array($e->getCode(), ['42S22', '42S02']) || str_contains($e->getMessage(), 'Unknown column') || str_contains($e->getMessage(), 'Base table or view not found')) {
            
            // Check if there are actual pending migrations waiting to be run
            $schema_current = function_exists('get_schema_version') ? get_schema_version($pdo) : 0;
            $schema_latest = $schema_current;
            $migrations_dir = __DIR__ . '/../../db/migrations';
            
            if (is_dir($migrations_dir)) {
                foreach (glob($migrations_dir . '/*.php') as $mig_file) {
                    if (preg_match('/(\d+)_/', basename($mig_file), $m)) {
                        $schema_latest = max($schema_latest, (int) $m[1]);
                    }
                }
            }

            // If a structural error occurred AND updates are waiting, route safely to the update gateway
            if ($schema_current < $schema_latest) {
                $base = defined('BASE_PATH') ? rtrim(BASE_PATH, '/') : '';
                header('Location: ' . $base . '/update_database.php');
                exit;
            }
        }
        
        // If it's a regular database bug or no updates are waiting, throw normally
        throw $e;
    }

    if ($user && $user['is_active'] && password_verify($password, $user['password_hash'])) {
        if ($user['two_fa_enabled']) {
            $_SESSION['pending_2fa_user_id'] = $user['id'];
            header('Location: ../verify_2fa.php');
            exit;
        } else {
            $_SESSION['user_id'] = $user['id'];
            $_SESSION['username'] = $user['username'];

            // Forward new users to onboarding wizard, otherwise go to data_entry
            if (!empty($user['is_new_user'])) {
                header('Location: ../onboarding.php');
            } else {
                header('Location: ../data_entry.php');
            }
            exit;
        }
    } else {
        error_log("Failed login attempt for user: '{$username}' from IP: " . $_SERVER['REMOTE_ADDR']);
        http_response_code(403);
        $_SESSION['error'] = "Invalid credentials or account access restricted.";
        header('Location: ../login.php');
        exit;
    }
}

header('Location: ../login.php');
exit;
