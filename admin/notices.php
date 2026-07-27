<?php
// admin/notices.php - Admin interface for managing site notices and announcements
require_once '../db/db.php';
require_once '../db/auth_helpers.php';
session_start();

$current_user = require_role($pdo, ['admin']);

$message = $_SESSION['message'] ?? '';
$error = $_SESSION['error'] ?? '';
unset($_SESSION['message'], $_SESSION['error']);

// Handle form submissions for creating/deleting notices
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $action = $_POST['action'] ?? '';

    if ($action === 'create') {
        $title = trim($_POST['title'] ?? '');
        $content = trim($_POST['content'] ?? '');
        $is_dismissible = isset($_POST['is_dismissible']) ? 1 : 0;
        $target_roles = isset($_POST['target_roles']) ? implode(',', $_POST['target_roles']) : 'everyone';
        $display_order = intval($_POST['display_order'] ?? 0);

        if (empty($title) || empty($content)) {
            $_SESSION['error'] = "Title and content cannot be blank.";
        } else {
            $stmt = $pdo->prepare("INSERT INTO site_notices (title, content, target_roles, is_dismissible, display_order) VALUES (?, ?, ?, ?, ?)");
            $stmt->execute([$title, $content, $target_roles, $is_dismissible, $display_order]);
            $_SESSION['message'] = "Notice created successfully!";
        }
        header('Location: notices.php');
        exit;
    } elseif ($action === 'delete') {
        $id = intval($_POST['notice_id'] ?? 0);
        $stmt = $pdo->prepare("DELETE FROM site_notices WHERE id = ?");
        $stmt->execute([$id]);
        $_SESSION['message'] = "Notice deleted.";
        header('Location: notices.php');
        exit;
    }
}

$notices = $pdo->query("SELECT * FROM site_notices ORDER BY display_order ASC, id DESC")->fetchAll();
?>

<?php require_once '../partials/header.php'; ?>

<div class="search-box-container" role="region" aria-label="Notices Management">
    <h3>Site Notices & Announcements Manager</h3>
    <p>Create dynamic alerts, welcome banners, or targeted notifications for specific user roles.</p>

    <?php if (!empty($error)): ?>
        <p class="alert-danger"><strong><?php echo htmlspecialchars($error); ?></strong></p>
    <?php endif; ?>
    <?php if (!empty($message)): ?>
        <p class="alert-success"><strong><?php echo htmlspecialchars($message); ?></strong></p>
    <?php endif; ?>

    <!-- Create Notice Form -->
    <div style="background: rgba(0,0,0,0.02); padding: 1.5rem; border-radius: 6px; margin-bottom: 2rem;">
        <h4>Create New Notice</h4>
        <form method="POST">
            <input type="hidden" name="action" value="create">
            
            <label for="title">Notice Title / Heading:</label><br>
            <input type="text" id="title" name="title" required class="profile-input" style="width: 100%; margin-bottom: 1rem;"><br>

            <label for="content">Notice Content (HTML/Text allowed):</label><br>
            <textarea id="content" name="content" rows="3" required class="profile-input" style="width: 100%; margin-bottom: 1rem;"></textarea><br>

            <label>Target Audience (Select roles or everyone):</label><br>
            <div style="display: flex; gap: 1rem; margin-bottom: 1rem; flex-wrap: wrap;">
                <label><input type="checkbox" name="target_roles[]" value="everyone" checked> Everyone</label>
                <label><input type="checkbox" name="target_roles[]" value="public"> Public (Guests)</label>
                <label><input type="checkbox" name="target_roles[]" value="user"> Users</label>
                <label><input type="checkbox" name="target_roles[]" value="moderator"> Moderators</label>
                <label><input type="checkbox" name="target_roles[]" value="admin"> Admins</label>
            </div>

            <div style="display: flex; gap: 2rem; margin-bottom: 1rem; align-items: center;">
                <label>
                    <input type="checkbox" name="is_dismissible" value="1" checked> Make Dismissible (Includes close 'X' button)
                </label>
                <div>
                    <label for="display_order">Display Order:</label>
                    <input type="number" id="display_order" name="display_order" value="0" style="width: 70px; padding: 0.2rem;">
                </div>
            </div>

            <button type="submit" class="btn">Publish Notice</button>
        </form>
    </div>

    <!-- Existing Notices Table -->
    <h4>Active & Existing Notices</h4>
    <table class="data-table" style="width: 100%; border-collapse: collapse;">
        <thead>
            <tr>
                <th>Order</th>
                <th>Title</th>
                <th>Target Roles</th>
                <th>Dismissible</th>
                <th>Actions</th>
            </tr>
        </thead>
        <tbody>
            <?php if (empty($notices)): ?>
                <tr><td colspan="5" style="text-align: center; padding: 1rem;">No notices created yet.</td></tr>
            <?php else: ?>
                <?php foreach ($notices as $n): ?>
                    <tr>
                        <td><?php echo $n['display_order']; ?></td>
                        <td><strong><?php echo htmlspecialchars($n['title']); ?></strong><br><small><?php echo htmlspecialchars(substr($n['content'], 0, 80)); ?>...</small></td>
                        <td><?php echo htmlspecialchars($n['target_roles']); ?></td>
                        <td><?php echo $n['is_dismissible'] ? 'Yes' : 'No (Sticky)'; ?></td>
                        <td>
                            <form method="POST" onsubmit="return confirm('Delete this notice?');" style="display:inline;">
                                <input type="hidden" name="action" value="delete">
                                <input type="hidden" name="notice_id" value="<?php echo $n['id']; ?>">
                                <button type="submit" class="btn btn-danger" style="font-size: 0.8rem; padding: 0.2rem 0.5rem;">Delete</button>
                            </form>
                        </td>
                    </tr>
                <?php endforeach; ?>
            <?php endif; ?>
        </tbody>
    </table>
</div>

<?php require_once '../partials/footer.php'; ?>
