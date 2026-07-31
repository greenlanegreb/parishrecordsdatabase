<?php
// admin/volunteer_dashboard.php - Dynamic Admin view for volunteer submissions with workflow
require_once '../db/db.php';
require_once '../db/auth_helpers.php';
require_once '../includes/functions.php';

if (!is_module_enabled($pdo, 'volunteers')) {
    http_response_code(403);
    exit('403 Forbidden: The Volunteer Portal module is currently disabled.');
}

$current_user = require_admin_page($pdo, 'manage_volunteers', 'Manage and review volunteer applications and workflow');
$message = $GLOBALS['message'] ?? '';
$error = $GLOBALS['error'] ?? '';

[$user_timezone, $full_format_str] = get_user_time_prefs($current_user);
$system_name = get_system_name($pdo);

// Fetch schema columns
$cols_stmt = $pdo->query("SELECT * FROM volunteer_columns ORDER BY sort_order ASC, column_name ASC");
$columns = $cols_stmt->fetchAll(PDO::FETCH_ASSOC);

// Fetch submissions
$subs_stmt = $pdo->query("SELECT vs.*, u.username FROM volunteer_submissions vs LEFT JOIN users u ON vs.created_by = u.id ORDER BY vs.created_at DESC");
$submissions = $subs_stmt->fetchAll(PDO::FETCH_ASSOC);

// Fetch submission values map
$vals_stmt = $pdo->query("SELECT submission_id, column_id, value_content FROM volunteer_submission_values");
$raw_values = $vals_stmt->fetchAll(PDO::FETCH_ASSOC);
$submission_values = [];
foreach ($raw_values as $val) {
    $submission_values[$val['submission_id']][$val['column_id']] = $val['value_content'];
}
?>
<?php require_once '../partials/header.php'; ?>

<div style="display: flex; justify-content: space-between; align-items: center; margin-bottom: 1.5rem; flex-wrap: wrap; gap: 1rem;">
    <div>
        <h3>Volunteer Submissions & Workflow</h3>
        <p>Review applications, schedule volunteer chats, take interview notes, and accept candidates into the system.</p>
    </div>
    <div style="display: flex; gap: 0.5rem; flex-wrap: wrap;">
        <a href="manage_volunteer_emails.php" class="btn btn-secondary" style="text-decoration: none;">✉️ Manage Email Templates</a>
        <a href="manage_volunteer_schema.php" class="btn btn-secondary" style="text-decoration: none;">⚙️ Manage Form Schema</a>
    </div>
</div>

<?php if (!empty($message)): ?>
    <p class="alert-success" role="status"><strong><?php echo htmlspecialchars($message); ?></strong></p>
<?php endif; ?>
<?php if (!empty($error)): ?>
    <p class="alert-danger" role="alert"><strong><?php echo htmlspecialchars($error); ?></strong></p>
<?php endif; ?>

<div style="overflow-x: auto;">
    <table class="data-table" role="table" style="width: 100%; border-collapse: collapse;">
        <thead>
            <tr>
                <th scope="col">ID</th>
                <th scope="col">Status</th>
                <th scope="col">Name</th>
                <th scope="col">Email</th>
                <?php foreach ($columns as $col): ?>
                    <th scope="col"><?php echo htmlspecialchars($col['column_name']); ?></th>
                <?php endforeach; ?>
                <th scope="col">Interview / Notes</th>
                <th scope="col">Submitted</th>
                <th scope="col" style="text-align: right;">Actions</th>
            </tr>
        </thead>
        <tbody>
            <?php if (empty($submissions)): ?>
                <tr><td colspan="<?php echo count($columns) + 7; ?>" style="text-align: center; color: #666; padding: 1rem;">No volunteer submissions found.</td></tr>
            <?php else: ?>
                <?php foreach ($submissions as $sub): ?>
                    <?php 
                        $sub_email = $sub['email'] ?? '';
                        $first_name = $sub['first_name'] ?? '';
                        $surname = $sub['surname'] ?? '';
                        $full_name = trim("{$first_name} {$surname}");
                        if ($full_name === '') $full_name = 'Volunteer #' . $sub['id'];
                    ?>
                    <tr>
                        <td>#<?php echo $sub['id']; ?></td>
                        <td>
                            <?php 
                                $status = $sub['status'] ?? 'Pending Review';
                                $badge_color = match($status) {
                                    'Accepted' => 'green',
                                    'Chat Scheduled' => 'orange',
                                    'Rejected' => 'red',
                                    default => 'gray'
                                };
                            ?>
                            <span style="color: white; background: <?php echo $badge_color; ?>; padding: 0.2rem 0.5rem; border-radius: 4px; font-size: 0.8rem; font-weight: bold;">
                                <?php echo htmlspecialchars($status); ?>
                            </span>
                        </td>
                        <td><strong><?php echo htmlspecialchars($full_name); ?></strong></td>
                        <td><a href="mailto:<?php echo htmlspecialchars($sub_email); ?>"><?php echo htmlspecialchars($sub_email); ?></a></td>
                        <?php foreach ($columns as $col): 
                            $raw_val = $submission_values[$sub['id']][$col['id']] ?? '';
                            if (($col['data_type'] ?? '') === 'BOOLEAN') {
                                $display_val = format_boolean_value($raw_val, $col['boolean_display_format'] ?? 'yes_no');
                            } elseif (($col['data_type'] ?? '') === 'DATE') {
                                $display_val = format_display_date($raw_val, $current_user['date_format'] ?? 'd/m/Y');
                            } else {
                                $display_val = nl2br(htmlspecialchars($raw_val));
                            }
                        ?>
                            <td><?php echo $display_val; ?></td>
                        <?php endforeach; ?>
                        <td>
                            <?php if (!empty($sub['interview_date'])): ?>
                                <small><strong>Chat:</strong> <?php echo format_user_time($sub['interview_date'], $user_timezone, $full_format_str); ?></small><br>
                            <?php endif; ?>
                            <?php if (!empty($sub['interview_notes'])): ?>
                                <small style="color: #444; display: block; max-width: 200px; overflow: hidden; text-overflow: ellipsis; white-space: nowrap;" title="<?php echo htmlspecialchars($sub['interview_notes']); ?>">
                                    <strong>Notes:</strong> <?php echo htmlspecialchars($sub['interview_notes']); ?>
                                </small>
                            <?php else: ?>
                                <small style="color: #888; font-style: italic;">No notes yet</small>
                            <?php endif; ?>
                        </td>
                        <td><?php echo format_user_time($sub['created_at'], $user_timezone, $full_format_str); ?></td>
                        <td style="white-space: nowrap; text-align: right;">
                            <!-- Trigger Interview Modal Button -->
                            <button type="button" class="btn btn-secondary" style="padding: 0.25rem 0.5rem; font-size: 0.85rem;" onclick="openInterviewModal(<?php echo $sub['id']; ?>, '<?php echo htmlspecialchars(addslashes($sub['status'] ?? 'Pending Review')); ?>', '<?php echo htmlspecialchars($sub['interview_date'] ?? ''); ?>', `<?php echo htmlspecialchars(addslashes($sub['interview_notes'] ?? '')); ?>`)">Chat & Notes</button>

                            <!-- Accept & Invite Bridge Button -->
                            <?php if (!empty($sub_email)): ?>
                                <a href="create_user.php?email=<?php echo urlencode($sub_email); ?>&first_name=<?php echo urlencode($first_name); ?>&surname=<?php echo urlencode($surname); ?>&volunteer_id=<?php echo $sub['id']; ?>" class="btn" style="padding: 0.25rem 0.5rem; font-size: 0.85rem; text-decoration: none; background: #28a745; color: white; margin-left: 4px;" title="Plug into user invite system">Accept & Invite</a>
                            <?php endif; ?>

                            <!-- Delete Form Button -->
                            <form method="POST" action="actions/save_volunteer.php" style="display: inline; margin-left: 4px;" onsubmit="return confirm('Delete this volunteer entry?');">
                                <?php echo csrf_field(); ?>
                                <input type="hidden" name="action" value="delete_volunteer">
                                <input type="hidden" name="volunteer_id" value="<?php echo $sub['id']; ?>">
                                <button type="submit" class="btn btn-danger" style="padding: 0.25rem 0.5rem; font-size: 0.85rem;">Delete</button>
                            </form>
                        </td>
                    </tr>
                <?php endforeach; ?>
            <?php endif; ?>
        </tbody>
    </table>
</div>

<!-- Interview & Notes Modal -->
<div id="interviewModal" style="display: none; position: fixed; top: 0; left: 0; width: 100%; height: 100%; background: rgba(0,0,0,0.5); z-index: 1000; justify-content: center; align-items: center;">
    <div style="background: white; padding: 2rem; border-radius: 8px; width: 100%; max-width: 500px; box-shadow: 0 4px 12px rgba(0,0,0,0.15);">
        <h4 style="margin-top: 0;">Manage Interview & Candidate Notes</h4>
        <form method="POST" action="actions/save_volunteer.php">
            <?php echo csrf_field(); ?>
            <input type="hidden" name="action" value="update_interview">
            <input type="hidden" name="volunteer_id" id="modal_volunteer_id">

            <label for="modal_status">Application Status:</label><br>
            <select id="modal_status" name="status" class="dashboard-input" style="width: 100%; padding: 0.4rem; margin-bottom: 1rem;">
                <option value="Pending Review">Pending Review</option>
                <option value="Chat Scheduled">Chat Scheduled</option>
                <option value="Accepted">Accepted</option>
                <option value="Rejected">Rejected</option>
            </select><br>

            <label for="modal_interview_date">Scheduled Chat / Interview Date & Time:</label><br>
            <input type="datetime-local" id="modal_interview_date" name="interview_date" class="dashboard-input" style="width: 100%; padding: 0.4rem; margin-bottom: 1rem;"><br>

            <label for="modal_interview_notes">Interview / Meeting Notes:</label><br>
            <textarea id="modal_interview_notes" name="interview_notes" rows="4" class="dashboard-input" placeholder="Record feedback from the chat here..." style="width: 100%; padding: 0.4rem; margin-bottom: 1rem; resize: vertical;"></textarea><br>

            <div style="display: flex; justify-content: flex-end; gap: 0.5rem;">
                <button type="button" class="btn btn-secondary" onclick="closeInterviewModal()">Cancel</button>
                <button type="submit" class="btn">Save Changes</button>
            </div>
        </form>
    </div>
</div>

<script>
function openInterviewModal(id, status, date, notes) {
    document.getElementById('modal_volunteer_id').value = id;
    document.getElementById('modal_status').value = status;
    if (date) {
        document.getElementById('modal_interview_date').value = date.replace(' ', 'T').substring(0, 16);
    } else {
        document.getElementById('modal_interview_date').value = '';
    }
    document.getElementById('modal_interview_notes').value = notes;
    document.getElementById('interviewModal').style.display = 'flex';
}

function closeInterviewModal() {
    document.getElementById('interviewModal').style.display = 'none';
}
</script>

<?php require_once '../partials/footer.php'; ?>
