<?php
// admin/actions/save_notice_inline.php - Handles inline updates and deletions for notices from settings.php
require_once '../../db/db.php';
require_once '../../db/auth_helpers.php';
session_start();

// Enforce admin-only access
require_role($pdo, ['admin']);

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $notice_id = intval($_POST['notice_id'] ?? 0);
    $update_action = $_POST['update_action'] ?? '';

    if ($notice_id > 0) {
        if ($update_action === 'delete') {
            $stmt = $pdo->prepare("DELETE FROM site_notices WHERE id = ?");
            $stmt->execute([$notice_id]);
            $_SESSION['message'] = "Notice deleted successfully.";
        } elseif ($update_action === 'save') {
            $title = trim($_POST['title'] ?? '');
            $content = trim($_POST['content'] ?? '');
            
            // Capture roles array from checkboxes and convert to comma-separated string
            $roles_array = $_POST['target_roles'] ?? [];
            if (in_array('everyone', $roles_array)) {
                $target_roles = 'everyone';
            } else {
                $target_roles = !empty($roles_array) ? implode(',', $roles_array) : 'public';
            }

            $is_dismissible = isset($_POST['is_dismissible']) ? 1 : 0;
            $is_active = isset($_POST['is_active']) ? 1 : 0;
            $display_order = intval($_POST['display_order'] ?? 0);

            if (!empty($title) && !empty($content)) {
                $stmt = $pdo->prepare("
                    UPDATE site_notices 
                    SET title = ?, content = ?, target_roles = ?, is_dismissible = ?, is_active = ?, display_order = ? 
                    WHERE id = ?
                ");
                $stmt->execute([$title, $content, $target_roles, $is_dismissible, $is_active, $display_order, $notice_id]);
                $_SESSION['message'] = "Notice updated successfully.";
            } else {
                $_SESSION['error'] = "Notice title and content cannot be empty.";
            }
        }
    } else {
        $_SESSION['error'] = "Invalid notice reference.";
    }
}

header('Location: ../settings.php');
exit;
