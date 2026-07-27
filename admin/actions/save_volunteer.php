<?php
// admin/actions/save_volunteer.php - Handles volunteer submission deletions
require_once '../../db/db.php';
require_once '../../db/auth_helpers.php';
session_start();

// Enforce strict administrator privileges via central helper
require_role($pdo, 'admin');

if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['action']) && $_POST['action'] === 'delete_volunteer') {
    $volunteer_id = intval($_POST['volunteer_id'] ?? 0);
    if ($volunteer_id > 0) {
        $del_stmt = $pdo->prepare("DELETE FROM volunteers WHERE id = ?");
        if ($del_stmt->execute([$volunteer_id])) {
            $_SESSION['message'] = "Volunteer entry #{$volunteer_id} has been successfully deleted.";
        } else {
            $_SESSION['error'] = "Failed to delete volunteer entry.";
        }
    }
}

header('Location: ../volunteer_dashboard.php');
exit;
