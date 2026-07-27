<?php
// suggest_edit.php - View for suggesting column edits securely
require_once '../db/db.php';
require_once '../db/auth_helpers.php';
session_start();

$record_id = $_GET['record_id'] ?? null;

if (!$record_id) {
    exit("No record specified.");
}

$stmt = $pdo->prepare("
    SELECT r.id, rv.value_content, tc.column_name, tc.id as column_id 
    FROM record_values rv
    JOIN records r ON rv.record_id = r.id
    JOIN table_columns tc ON rv.column_id = tc.id
    WHERE r.id = ?
");
$stmt->execute([$record_id]);
$record_data = $stmt->fetchAll();

if (!$record_data) {
    exit("Record not found.");
}

$message = $_SESSION['message'] ?? '';
$error = $_SESSION['error'] ?? '';
unset($_SESSION['message'], $_SESSION['error']);
?>

    <?php require_once '../partials/header.php'; ?>

    <div class="search-box-container suggest-edit-container" role="region" aria-label="Suggest Edit">
        <h3>Suggest an Edit for Record #<?php echo htmlspecialchars($record_id); ?></h3>

        <?php if (!empty($error)): ?>
            <p class="alert-danger"><strong><?php echo htmlspecialchars($error); ?></strong></p>
        <?php endif; ?>
        <?php if (!empty($message)): ?>
            <p class="alert-success"><strong><?php echo htmlspecialchars($message); ?></strong></p>
        <?php endif; ?>

        <h4>Current Values:</h4>
        <ul class="suggest-edit-list">
            <?php foreach ($record_data as $data): ?>
                <li><strong><?php echo htmlspecialchars($data['column_name']); ?>:</strong> <?php echo htmlspecialchars($data['value_content']); ?></li>
            <?php endforeach; ?>
        </ul>

        <hr style="border: 0.0625rem solid var(--border-color); margin: 1.5rem 0;">

        <h3>Submit New Proposed Value</h3>
        <form method="POST" action="actions/save_suggest_edit.php" onsubmit="return confirm('Are you sure you are ready to submit this edit suggestion for admin review?');">
            <?php echo csrf_field(); ?>
            <input type="hidden" name="record_id" value="<?php echo htmlspecialchars($record_id); ?>">

            <label for="column_id">Select Column to Edit:</label><br>
            <select id="column_id" name="column_id" required class="suggest-edit-select">
                <?php foreach ($record_data as $data): ?>
                    <option value="<?php echo $data['column_id']; ?>"><?php echo htmlspecialchars($data['column_name']); ?></option>
                <?php endforeach; ?>
            </select><br>

            <label for="proposed_value">Proposed New Value:</label><br>
            <textarea id="proposed_value" name="proposed_value" rows="4" required class="suggest-edit-textarea"></textarea><br>

            <button type="submit" class="btn">Submit Suggestion for Review</button>
        </form>
    </div>

    <?php require_once '../partials/footer.php'; ?>
