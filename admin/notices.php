<?php
// admin/notices.php - Admin interface for managing site notices and announcements
require_once '../db/db.php';
require_once '../db/auth_helpers.php';
require_once '../includes/functions.php';

// Standard admin bootstrap (permission + flash messages)
$current_user = require_admin_page($pdo, 'manage_notices', 'Manage site-wide notices and broadcast announcements');
$message = $GLOBALS['message'] ?? '';
$error   = $GLOBALS['error']   ?? '';

// Handle form submissions for creating/deleting notices
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    verify_csrf_token();
    $action = $_POST['action'] ?? '';

    if ($action === 'create') {
        $title = trim($_POST['title'] ?? '');
        $content = trim($_POST['content'] ?? '');
        $is_dismissible = isset($_POST['is_dismissible']) ? 1 : 0;
        $target_roles = isset($_POST['target_roles']) ? implode(',', $_POST['target_roles']) : 'everyone';
        $display_order = intval($_POST['display_order'] ?? 0);

        if (empty($title) || empty($content)) {
            $_SESSION['error'] = __('notices.error_blank');
        } else {
            $stmt = $pdo->prepare("INSERT INTO site_notices (title, content, target_roles, is_dismissible, display_order) VALUES (?, ?, ?, ?, ?)");
            $stmt->execute([$title, $content, $target_roles, $is_dismissible, $display_order]);
            $_SESSION['message'] = __('notices.msg_created');
        }
        header('Location: notices.php');
        exit;
    } elseif ($action === 'delete') {
        $id = intval($_POST['notice_id'] ?? 0);
        $stmt = $pdo->prepare("DELETE FROM site_notices WHERE id = ?");
        $stmt->execute([$id]);
        $_SESSION['message'] = __('notices.msg_deleted');
        header('Location: notices.php');
        exit;
    }
}

$notices = $pdo->query("SELECT * FROM site_notices ORDER BY display_order ASC, id DESC")->fetchAll();
?>
<?php require_once '../partials/header.php'; ?>

<div class="search-box-container" role="region" aria-label="Notices Management">
    <h3><?php echo htmlspecialchars(__('notices.heading')); ?></h3>
    <p><?php echo htmlspecialchars(__('notices.subheading')); ?></p>

    <?php if (!empty($error)): ?>
        <p class="alert-danger" role="alert"><strong><?php echo htmlspecialchars($error); ?></strong></p>
    <?php endif; ?>
    <?php if (!empty($message)): ?>
        <p class="alert-success" role="status"><strong><?php echo htmlspecialchars($message); ?></strong></p>
    <?php endif; ?>

    <!-- Create Notice Form -->
    <div style="background: rgba(0,0,0,0.02); padding: 1.5rem; border-radius: 6px; margin-bottom: 2rem;">
        <h4><?php echo htmlspecialchars(__('notices.create_heading')); ?></h4>
        <form method="POST">
            <?php echo csrf_field(); ?>
            <input type="hidden" name="action" value="create">
           
            <label for="title"><?php echo htmlspecialchars(__('notices.title_label')); ?></label><br>
            <input type="text" id="title" name="title" required class="profile-input" style="width: 100%; margin-bottom: 1rem;"><br>

            <label for="content"><?php echo htmlspecialchars(__('notices.content_label')); ?></label><br>
            <textarea id="content" name="content" rows="3" required class="profile-input" style="width: 100%; margin-bottom: 1rem;"></textarea><br>

            <label><?php echo htmlspecialchars(__('notices.target_roles_label')); ?></label><br>
            <div style="display: flex; gap: 1rem; margin-bottom: 1rem; flex-wrap: wrap;">
                <label><input type="checkbox" name="target_roles[]" value="everyone" checked> <?php echo htmlspecialchars(__('notices.role_everyone')); ?></label>
                <label><input type="checkbox" name="target_roles[]" value="public"> <?php echo htmlspecialchars(__('notices.role_public')); ?></label>
                <label><input type="checkbox" name="target_roles[]" value="user"> <?php echo htmlspecialchars(__('notices.role_users')); ?></label>
                <label><input type="checkbox" name="target_roles[]" value="moderator"> <?php echo htmlspecialchars(__('notices.role_moderators')); ?></label>
                <label><input type="checkbox" name="target_roles[]" value="admin"> <?php echo htmlspecialchars(__('notices.role_admins')); ?></label>
            </div>

            <div style="display: flex; gap: 2rem; margin-bottom: 1rem; align-items: center;">
                <label>
                    <input type="checkbox" name="is_dismissible" value="1" checked> <?php echo htmlspecialchars(__('notices.dismissible_label')); ?>
                </label>
                <div>
                    <label for="display_order"><?php echo htmlspecialchars(__('notices.display_order_label')); ?></label>
                    <input type="number" id="display_order" name="display_order" value="0" style="width: 70px; padding: 0.2rem;">
                </div>
            </div>

            <button type="submit" class="btn"><?php echo htmlspecialchars(__('notices.publish_btn')); ?></button>
        </form>
    </div>

    <!-- Existing Notices Table -->
    <h4><?php echo htmlspecialchars(__('notices.existing_heading')); ?></h4>
    <table class="data-table" style="width: 100%; border-collapse: collapse;">
        <thead>
            <tr>
                <th><?php echo htmlspecialchars(__('notices.th_order')); ?></th>
                <th><?php echo htmlspecialchars(__('notices.th_title')); ?></th>
                <th><?php echo htmlspecialchars(__('notices.th_target_roles')); ?></th>
                <th><?php echo htmlspecialchars(__('notices.th_dismissible')); ?></th>
                <th><?php echo htmlspecialchars(__('index.th_actions')); ?></th>
            </tr>
        </thead>
        <tbody>
            <?php if (empty($notices)): ?>
                <tr><td colspan="5" style="text-align: center; padding: 1rem;"><?php echo htmlspecialchars(__('notices.no_notices')); ?></td></tr>
            <?php else: ?>
                <?php foreach ($notices as $n): ?>
                    <tr>
                        <td><?php echo $n['display_order']; ?></td>
                        <td><strong><?php echo htmlspecialchars($n['title']); ?></strong><br><small><?php echo htmlspecialchars(substr($n['content'], 0, 80)); ?>...</small></td>
                        <td><?php echo htmlspecialchars($n['target_roles']); ?></td>
                        <td><?php echo $n['is_dismissible'] ? htmlspecialchars(__('notices.yes')) : htmlspecialchars(__('notices.no_sticky')); ?></td>
                        <td>
                            <form method="POST" onsubmit="return confirm('<?php echo htmlspecialchars(__('notices.delete_confirm')); ?>');" style="display:inline;">
                                <?php echo csrf_field(); ?>
                                <input type="hidden" name="action" value="delete">
                                <input type="hidden" name="notice_id" value="<?php echo $n['id']; ?>">
                                <button type="submit" class="btn btn-danger" style="font-size: 0.8rem; padding: 0.2rem 0.5rem;"><?php echo htmlspecialchars(__('btn.delete')); ?></button>
                            </form>
                        </td>
                    </tr>
                <?php endforeach; ?>
            <?php endif; ?>
        </tbody>
    </table>
</div>

<?php require_once '../partials/footer.php'; ?>
