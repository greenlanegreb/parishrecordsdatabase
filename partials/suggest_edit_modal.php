<?php
// partials/suggest_edit_modal.php - Shared suggest-edit modal (index + data entry)
if (!isset($columns) || !is_array($columns)) {
    $columns = [];
}
$suggest_return_url = $suggest_return_url ?? (defined('BASE_PATH') ? rtrim(BASE_PATH, '/') . '/index.php' : 'index.php');
$suggest_table_id = isset($suggest_table_id) ? (int) $suggest_table_id : (int) ($active_table_id ?? 0);
$suggest_action = (strpos($_SERVER['PHP_SELF'] ?? '', '/user/') !== false)
    ? 'actions/save_suggestion.php'
    : 'user/actions/save_suggestion.php';
?>
<div id="suggestModal" style="display:none;position:fixed;top:0;left:0;width:100%;height:100%;background:rgba(0,0,0,0.5);z-index:1000;align-items:center;justify-content:center;">
    <div style="background:white;padding:2rem;border-radius:6px;width:100%;max-width:450px;box-shadow:0 4px 12px rgba(0,0,0,0.15);max-height:90vh;overflow:auto;">
        <h3><?php echo htmlspecialchars(__('index.modal_heading')); ?></h3>
        <p style="font-size:0.9rem;color:#666;margin-bottom:1rem;">
            <?php echo htmlspecialchars(__('index.modal_desc')); ?>
        </p>
        <form method="POST" action="<?php echo htmlspecialchars($suggest_action); ?>">
            <?php echo function_exists('csrf_field') ? csrf_field() : ''; ?>
            <input type="hidden" name="record_id" id="modal_record_id" value="">
            <input type="hidden" name="return_url" value="<?php echo htmlspecialchars($suggest_return_url); ?>">
            <input type="hidden" name="table_id" value="<?php echo (int) $suggest_table_id; ?>">

            <!-- Honeypot -->
            <div style="display:none;" aria-hidden="true">
                <label for="website_hp">Leave this field blank</label>
                <input type="text" id="website_hp" name="website_hp" tabindex="-1" autocomplete="off">
            </div>

            <div style="margin-bottom:1rem;">
                <label for="modal_column_id"><strong><?php echo htmlspecialchars(__('index.modal_target_column')); ?></strong></label><br>
                <select name="column_id" id="modal_column_id" style="width:100%;padding:0.4rem;" required>
                    <?php foreach ($columns as $col): ?>
                        <option value="<?php echo (int) $col['id']; ?>">
                            <?php echo htmlspecialchars($col['column_name']); ?>
                        </option>
                    <?php endforeach; ?>
                </select>
            </div>

            <div style="margin-bottom:1rem;">
                <label for="modal_proposed_value"><strong><?php echo htmlspecialchars(__('index.modal_proposed_value')); ?></strong></label><br>
                <input type="text" name="proposed_value" id="modal_proposed_value"
                       placeholder="<?php echo htmlspecialchars(__('index.modal_input_placeholder')); ?>"
                       style="width:100%;padding:0.4rem;box-sizing:border-box;" required>
            </div>

            <div style="margin-bottom:1rem;">
                <label for="modal_reasoning"><strong><?php echo htmlspecialchars(__('suggest_edit.reasoning_label')); ?></strong></label><br>
                <textarea name="reasoning" id="modal_reasoning" rows="2"
                          placeholder="<?php echo htmlspecialchars(__('suggest_edit.reasoning_placeholder')); ?>"
                          style="width:100%;padding:0.4rem;box-sizing:border-box;"></textarea>
                <small style="color:#666;"><?php echo htmlspecialchars(__('suggest_edit.reasoning_optional') !== 'suggest_edit.reasoning_optional' ? __('suggest_edit.reasoning_optional') : 'Optional — evidence, source, or notes for moderators.'); ?></small>
            </div>

            <?php
            if (function_exists('render_form_captcha')) {
                echo render_form_captcha($pdo);
            } elseif (function_exists('get_form_captcha_html')) {
                echo get_form_captcha_html($pdo);
            }
            ?>

            <div style="display:flex;gap:10px;">
                <button type="submit" class="btn"><?php echo htmlspecialchars(__('index.modal_submit_btn')); ?></button>
                <button type="button" class="btn btn-secondary" onclick="closeSuggestModal()"><?php echo htmlspecialchars(__('btn.cancel')); ?></button>
            </div>
        </form>
    </div>
</div>
<script>
function openSuggestModal(recordId) {
    var el = document.getElementById('modal_record_id');
    var modal = document.getElementById('suggestModal');
    if (el) el.value = recordId;
    if (modal) modal.style.display = 'flex';
}
function closeSuggestModal() {
    var modal = document.getElementById('suggestModal');
    if (modal) modal.style.display = 'none';
}
</script>
