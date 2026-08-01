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
    exit(__('record_history.exit_no_record'));
}

// Fetch ALL columns belonging to this record's table, including data type and formatting details
$stmt = $pdo->prepare("
    SELECT 
        r.id AS record_id,
        r.table_id,
        tc.id AS column_id,
        tc.column_name,
        tc.data_type,
        tc.boolean_display_format,
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
    exit(__('record_history.exit_not_found'));
}

$message = $_SESSION['message'] ?? '';
$error = $_SESSION['error'] ?? '';
unset($_SESSION['message'], $_SESSION['error']);
?>
<?php require_once '../partials/header.php'; ?>

<div class="search-box-container suggest-edit-container" role="region" aria-label="<?php echo htmlspecialchars(__('suggest_edit.aria_region')); ?>">
    <div style="display: flex; justify-content: space-between; align-items: center; margin-bottom: 1rem;">
        <h3 style="margin: 0;"><?php echo htmlspecialchars(__('suggest_edit.heading_prefix')); ?> #<?php echo htmlspecialchars($record_id); ?></h3>
        <a href="<?php echo htmlspecialchars($return_url); ?>" class="btn btn-secondary" style="font-size: 0.9rem; text-decoration: none; padding: 0.4rem 0.8rem;">← <?php echo htmlspecialchars(__('suggest_edit.return_btn')); ?></a>
    </div>

    <?php if (!empty($error)): ?>
        <p class="alert-danger" role="alert"><strong><?php echo htmlspecialchars($error); ?></strong></p>
    <?php endif; ?>
    <?php if (!empty($message)): ?>
        <p class="alert-success" role="status"><strong><?php echo htmlspecialchars($message); ?></strong> <?php echo htmlspecialchars(__('suggest_edit.success_msg_suffix')); ?></p>
    <?php endif; ?>

    <h4><?php echo htmlspecialchars(__('suggest_edit.current_values_heading')); ?></h4>
    <ul class="suggest-edit-list">
        <?php foreach ($record_data as $data): ?>
            <li>
                <strong><?php echo htmlspecialchars($data['column_name']); ?>:</strong> 
                <?php if ($data['value_content'] !== ''): ?>
                    <?php if (($data['data_type'] ?? '') === 'BOOLEAN'): ?>
                        <?php echo htmlspecialchars(format_boolean_value($data['value_content'], $data['boolean_display_format'] ?? 'yes_no')); ?>
                    <?php else: ?>
                        <?php echo htmlspecialchars($data['value_content']); ?>
                    <?php endif; ?>
                <?php else: ?>
                    <em style="color: #888;"><?php echo htmlspecialchars(__('suggest_edit.empty_label')); ?></em>
                <?php endif; ?>
            </li>
        <?php endforeach; ?>
    </ul>

    <hr style="border: 0.0625rem solid var(--border-color); margin: 1.5rem 0;">

    <h3><?php echo htmlspecialchars(__('suggest_edit.submit_heading')); ?></h3>
    <form method="POST" action="actions/save_suggest_edit.php" onsubmit="return confirm('<?php echo htmlspecialchars(__('suggest_edit.confirm_prompt')); ?>');">
        <?php echo csrf_field(); ?>
        <input type="hidden" name="record_id" value="<?php echo htmlspecialchars($record_id); ?>">
        <input type="hidden" name="return_url" value="<?php echo htmlspecialchars($return_url); ?>">

        <label for="column_id"><?php echo htmlspecialchars(__('suggest_edit.select_column_label')); ?></label><br>
        <select id="column_id" name="column_id" required class="suggest-edit-select" onchange="renderInputType()">
            <?php foreach ($record_data as $data): ?>
                <option value="<?php echo $data['column_id']; ?>">
                    <?php echo htmlspecialchars($data['column_name']); ?>
                </option>
            <?php endforeach; ?>
        </select><br>

        <div id="input-container" style="margin-top: 1rem;">
            <!-- Dynamic input field rendered via JavaScript depending on column type -->
        </div>

        <label for="reasoning" style="margin-top: 1rem;"><?php echo htmlspecialchars(__('suggest_edit.reasoning_label')); ?></label><br>
        <textarea id="reasoning" name="reasoning" rows="3" placeholder="<?php echo htmlspecialchars(__('suggest_edit.reasoning_placeholder')); ?>" class="suggest-edit-textarea" oninput="this.style.height = ''; this.style.height = this.scrollHeight + 'px';" style="overflow:hidden;"></textarea><br>

        <button type="submit" class="btn" style="margin-top: 1rem;"><?php echo htmlspecialchars(__('suggest_edit.submit_btn')); ?></button>
    </form>
</div>

<script>
const columnMeta = <?php echo json_encode($record_data); ?>;
const optYesTrueText = '<?php echo htmlspecialchars(__('data_entry.bool_yes_true')); ?>';
const optNoFalseText = '<?php echo htmlspecialchars(__('data_entry.bool_no_false')); ?>';
const optMaleText = '<?php echo htmlspecialchars(__('data_entry.bool_male')); ?>';
const optFemaleText = '<?php echo htmlspecialchars(__('data_entry.bool_female')); ?>';
const optTrueText = '<?php echo htmlspecialchars(__('data_entry.bool_true')); ?>';
const optFalseText = '<?php echo htmlspecialchars(__('data_entry.bool_false')); ?>';
const optTickText = '<?php echo htmlspecialchars(__('data_entry.bool_tick')); ?>';
const optCrossText = '<?php echo htmlspecialchars(__('data_entry.bool_cross')); ?>';
const selectPlaceholder = '<?php echo htmlspecialchars(__('feedback.select_placeholder')); ?>';
const proposedValueLabel = '<?php echo htmlspecialchars(__('suggest_edit.proposed_value_label')); ?>';

function renderInputType() {
    const select = document.getElementById('column_id');
    const container = document.getElementById('input-container');
    const selectedColId = select.value;
    
    const col = columnMeta.find(c => c.column_id == selectedColId);
    if (!col) return;

    container.innerHTML = '';

    if (col.data_type === 'BOOLEAN') {
        let fmt = col.boolean_display_format || 'yes_no';
        let opt1Text = optYesTrueText;
        let opt2Text = optNoFalseText;
        
        if (fmt === 'male_female') { opt1Text = optMaleText; opt2Text = optFemaleText; }
        else if (fmt === 'true_false') { opt1Text = optTrueText; opt2Text = optFalseText; }
        else if (fmt === 'tick_cross') { opt1Text = optTickText; opt2Text = optCrossText; }

        let currentValue = col.value_content;

        container.innerHTML = `
            <label for="proposed_value">${proposedValueLabel}</label><br>
            <select id="proposed_value" name="proposed_value" required class="suggest-edit-select" style="width: 100%; padding: 0.5rem; margin-top: 0.25rem;">
                <option value="">${selectPlaceholder}</option>
                <option value="1" ${currentValue === '1' ? 'selected' : ''}>${opt1Text}</option>
                <option value="0" ${currentValue === '0' ? 'selected' : ''}>${opt2Text}</option>
            </select>
        `;
    } else {
        let currentValue = col.value_content;
        container.innerHTML = `
            <label for="proposed_value">${proposedValueLabel}</label><br>
            <textarea id="proposed_value" name="proposed_value" rows="3" required class="suggest-edit-textarea" oninput="this.style.height = ''; this.style.height = this.scrollHeight + 'px';" style="overflow:hidden; width: 100%;">${escapeHtml(currentValue)}</textarea>
        `;
        const textarea = document.getElementById('proposed_value');
        if (textarea) {
            textarea.style.height = '';
            textarea.style.height = textarea.scrollHeight + 'px';
        }
    }
}

function escapeHtml(text) {
    return text
        .replace(/&/g, "&amp;")
        .replace(/</g, "&lt;")
        .replace(/>/g, "&gt;")
        .replace(/"/g, "&quot;")
        .replace(/'/g, "&#039;");
}

document.addEventListener('DOMContentLoaded', () => {
    renderInputType();
});
</script>

<?php require_once '../partials/footer.php'; ?>
