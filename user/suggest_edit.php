<?php
// user/suggest_edit.php - View for suggesting column edits securely
require_once '../db/db.php';
require_once '../db/auth_helpers.php';
require_once '../includes/functions.php';
session_start();

// Ensure the moderation module is enabled; otherwise block access to suggestions
if (!is_module_enabled($pdo, 'moderation')) {
    http_response_code(403);
    exit('403 Forbidden: The Moderation Workflow module is currently disabled.');
}

// Enforce permission-based access control
$current_user = require_permission($pdo, 'access_suggest_edit', 'Allows submitting edit suggestions for records');

$record_id = $_GET['record_id'] ?? null;
$return_url = $_GET['return'] ?? $_SERVER['HTTP_REFERER'] ?? '../index.php';

if (!$record_id) {
    exit("No record specified.");
}

// Fetch ALL columns belonging to this record's table, LEFT JOINing any existing values
$stmt = $pdo->prepare("
    SELECT 
        r.id AS record_id,
        r.table_id,
        tc.id AS column_id,
        tc.column_name,
        COALESCE(rv.value_content, '') AS value_content
    FROM records r
    JOIN table_columns tc ON tc.table_id = r.table_id
    LEFT JOIN record_values rv ON rv.record_id = r.id AND rv.column_id = tc.id
    WHERE r.id = ?
    ORDER BY tc.id ASC
");
$stmt->execute([$record_id]);
$record_data = $stmt->fetchAll(PDO::FETCH_ASSOC);

if (!$record_data) {
    exit("Record not found.");
}

$message = $_SESSION['message'] ?? '';
$error = $_SESSION['error'] ?? '';
unset($_SESSION['message'], $_SESSION['error']);
?>
<?php require_once '../partials/header.php'; ?>

<div class="search-box-container suggest-edit-container" role="region" aria-label="Suggest Edit">
    <div style="display: flex; justify-content: space-between; align-items: center; margin-bottom: 1rem;">
        <h3 style="margin: 0;">Suggest an Edit for Record #<?php echo htmlspecialchars($record_id); ?></h3>
        <a href="<?php echo htmlspecialchars($return_url); ?>" class="btn btn-secondary" style="font-size: 0.9rem; text-decoration: none; padding: 0.4rem 0.8rem;">← Return to Record</a>
    </div>

    <?php if (!empty($error)): ?>
        <p class="alert-danger" role="alert"><strong><?php echo htmlspecialchars($error); ?></strong></p>
    <?php endif; ?>
    <?php if (!empty($message)): ?>
        <p class="alert-success" role="status"><strong><?php echo htmlspecialchars($message); ?></strong> Feel free to submit another change below, or use the return link above when finished.</p>
    <?php endif; ?>

    <h4>Current Values:</h4>
    <ul class="suggest-edit-list">
        <?php foreach ($record_data as $data): ?>
            <li>
                <strong><?php echo htmlspecialchars($data['column_name']); ?>:</strong> 
                <?php if ($data['value_content'] !== ''): ?>
                    <?php echo htmlspecialchars($data['value_content']); ?>
                <?php else: ?>
                    <em style="color: #888;">(empty)</em>
                <?php endif; ?>
            </li>
        <?php endforeach; ?>
    </ul>

    <hr style="border: 0.0625rem solid var(--border-color); margin: 1.5rem 0;">

    <h3>Submit New Proposed Value & Evidence</h3>
    <form method="POST" action="actions/save_suggest_edit.php" onsubmit="return confirm('Are you sure you are ready to submit this edit suggestion for admin review?');">
        <?php echo csrf_field(); ?>
        <input type="hidden" name="record_id" value="<?php echo htmlspecialchars($record_id); ?>">
        <input type="hidden" name="return_url" value="<?php echo htmlspecialchars($return_url); ?>">

        <label for="column_id">Select Column to Edit:</label><br>
        <select id="column_id" name="column_id" required class="suggest-edit-select" onchange="updateProposedValueDefault()">
            <?php foreach ($record_data as $data): ?>
                <option value="<?php echo $data['column_id']; ?>"><?php echo htmlspecialchars($data['column_name']); ?></option>
            <?php endforeach; ?>
        </select><br>

        <label for="proposed_value">Proposed New Value:</label><br>
        <textarea id="proposed_value" name="proposed_value" rows="3" required class="suggest-edit-textarea" oninput="this.style.height = ''; this.style.height = this.scrollHeight + 'px';" style="overflow:hidden;"></textarea><br>

        <label for="reasoning">Evidence / Reasoning / Source Notes:</label><br>
        <textarea id="reasoning" name="reasoning" rows="3" placeholder="Provide context, source citations, or rationale for this change..." class="suggest-edit-textarea" oninput="this.style.height = ''; this.style.height = this.scrollHeight + 'px';" style="overflow:hidden;"></textarea><br>

        <button type="submit" class="btn">Submit Suggestion for Review</button>
    </form>
</div>

<script>
const columnValues = <?php echo json_encode(array_column($record_data, 'value_content', 'column_id')); ?>;

function updateProposedValueDefault() {
    const select = document.getElementById('column_id');
    const textarea = document.getElementById('proposed_value');
    const selectedColId = select.value;
    if (columnValues.hasOwnProperty(selectedColId)) {
        textarea.value = columnValues[selectedColId];
        textarea.style.height = '';
        textarea.style.height = textarea.scrollHeight + 'px';
    }
}

document.addEventListener('DOMContentLoaded', () => {
    updateProposedValueDefault();
});
</script>

<?php require_once '../partials/footer.php'; ?>
