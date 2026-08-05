<?php
// user/actions/save_suggestion.php - Single endpoint for public + logged-in edit suggestions
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

require_once '../../db/db.php';
require_once '../../db/auth_helpers.php';
require_once '../../includes/functions.php';
require_once '../../includes/security_engine.php';

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    http_response_code(405);
    exit('Method Not Allowed');
}

// Where to send the user back
$return_url = trim($_POST['return_url'] ?? '');
if ($return_url === '' || preg_match('#^https?://#i', $return_url)) {
    // Disallow open redirects; only local relative paths
    $return_url = '../../index.php';
}
// Normalise to site-relative from this script depth when needed
$table_id_return = intval($_POST['table_id'] ?? 0);

function suggestion_redirect(string $return_url): void {
    header('Location: ' . $return_url);
    exit;
}

// Moderation module must be on
if (!is_module_enabled($pdo, 'moderation')) {
    $_SESSION['error'] = 'Suggestions are currently disabled.';
    suggestion_redirect($return_url);
}

// CSRF (session token works for guests too when form includes csrf_field)
verify_csrf_token();

// Firewall (rate limit, UA, excessive links)
$firewall_result = run_form_firewall_check($pdo);
if ($firewall_result !== true) {
    $_SESSION['error'] = $firewall_result;
    suggestion_redirect($return_url);
}

// CAPTCHA when enabled in Settings
$captcha_result = verify_form_captcha($pdo);
if ($captcha_result !== true) {
    $_SESSION['error'] = $captcha_result;
    suggestion_redirect($return_url);
}

// Honeypot (accept either legacy field name)
if (!empty($_POST['website_hp']) || !empty($_POST['website_url'])) {
    $_SESSION['error'] = 'Spam detection triggered.';
    suggestion_redirect($return_url);
}

$record_id = intval($_POST['record_id'] ?? 0);
$column_id = intval($_POST['column_id'] ?? 0);
$column_name = trim($_POST['column_name'] ?? '');
$proposed_value = sanitize_incoming_text($_POST['proposed_value'] ?? '');
$reasoning = sanitize_incoming_text($_POST['reasoning'] ?? '');

if ($record_id < 1) {
    $_SESSION['error'] = 'Invalid record.';
    suggestion_redirect($return_url);
}

// Load record + table
$rec_stmt = $pdo->prepare("SELECT id, table_id FROM records WHERE id = ?");
$rec_stmt->execute([$record_id]);
$record = $rec_stmt->fetch(PDO::FETCH_ASSOC);
if (!$record) {
    $_SESSION['error'] = 'Record not found.';
    suggestion_redirect($return_url);
}

$table_id = (int) $record['table_id'];
$current_user = function_exists('get_current_user_data') ? get_current_user_data($pdo) : null;

// Table visibility is the gate (guest or logged-in)
if (!user_can_view_table($pdo, $table_id, $current_user)) {
    $_SESSION['error'] = 'You are not allowed to suggest edits for this record.';
    suggestion_redirect($return_url);
}

// Resolve column (prefer id; else name scoped to this table)
$col = null;
if ($column_id > 0) {
    $c = $pdo->prepare("SELECT id, column_name, data_type, is_required, boolean_display_format FROM table_columns WHERE id = ? AND table_id = ?");
    $c->execute([$column_id, $table_id]);
    $col = $c->fetch(PDO::FETCH_ASSOC);
} elseif ($column_name !== '') {
    $c = $pdo->prepare("SELECT id, column_name, data_type, is_required, boolean_display_format FROM table_columns WHERE column_name = ? AND table_id = ?");
    $c->execute([$column_name, $table_id]);
    $col = $c->fetch(PDO::FETCH_ASSOC);
}

if (!$col) {
    $_SESSION['error'] = 'Invalid column.';
    suggestion_redirect($return_url);
}

if (!empty($col['is_required']) && $proposed_value === '') {
    $_SESSION['error'] = 'That field is required.';
    suggestion_redirect($return_url);
}

// Allow "0" as a real boolean/text value; block only truly empty when required already handled
if ($proposed_value === '' && empty($col['is_required'])) {
    // Optional empty still not useful as a suggestion
    $_SESSION['error'] = 'Please enter a proposed value.';
    suggestion_redirect($return_url);
}

$suggested_by = $current_user['id'] ?? null;

// Prefer PHP UTC timestamp so a skewed MySQL NOW() does not own the clock
$created_at = gmdate('Y-m-d H:i:s');

try {
    $ins = $pdo->prepare("
        INSERT INTO edit_suggestions
            (record_id, suggested_by, column_name, proposed_value, reasoning, status, points_awarded, created_at)
        VALUES
            (?, ?, ?, ?, ?, 'pending', 0, ?)
    ");
    $ins->execute([
        $record_id,
        $suggested_by,
        $col['column_name'],
        $proposed_value,
        $reasoning !== '' ? $reasoning : null,
        $created_at,
    ]);
    $_SESSION['message'] = 'Your suggestion was submitted for review.';
} catch (Exception $e) {
    // Fallback if reasoning/points columns missing on very old DB
    try {
        $ins = $pdo->prepare("
            INSERT INTO edit_suggestions
                (record_id, suggested_by, column_name, proposed_value, status, created_at)
            VALUES
                (?, ?, ?, ?, 'pending', ?)
        ");
        $ins->execute([
            $record_id,
            $suggested_by,
            $col['column_name'],
            $proposed_value,
            $created_at,
        ]);
        $_SESSION['message'] = 'Your suggestion was submitted for review.';
    } catch (Exception $e2) {
        error_log('save_suggestion failed: ' . $e2->getMessage());
        $_SESSION['error'] = 'Could not save your suggestion.';
    }
}

// Default return targets if caller sent a safe relative path under the app
if ($return_url === '../../index.php' && $table_id_return > 0 && strpos($return_url, 'data_entry') === false) {
    // keep index
}
suggestion_redirect($return_url);
