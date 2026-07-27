// Determine the first admin user ID dynamically to protect them server-side
$first_admin_id = 1;
try {
    $fa_stmt = $pdo->query("
        SELECT u.id FROM users u
        JOIN roles r ON u.role_id = r.id
        WHERE LOWER(r.role_name) = 'admin'
        ORDER BY u.created_at ASC, u.id ASC
        LIMIT 1
    ");
    $fa_id = $fa_stmt->fetchColumn();
    if ($fa_id) {
        $first_admin_id = intval($fa_id);
    }
} catch (Exception $e) {
    // Fallback safely to ID 1
}

// Prevent modifying the protected primary admin account via actions
$is_target_first_admin = ($target_user_id === $first_admin_id);
if ($is_target_first_admin && in_array($action, ['change_role', 'suspend'])) {
    http_response_code(403);
    $_SESSION['error'] = "Security Error: The primary system administrator account cannot have its role changed or be suspended.";
    header('Location: ../users.php');
    exit;
}
