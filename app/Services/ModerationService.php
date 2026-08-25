<?php
declare(strict_types=1);

namespace App\Services;

use PDO;
use Exception;

class ModerationService
{
    private PDO $pdo;

    public function __construct(PDO $pdo)
    {
        $this->pdo = $pdo;
    }

    public function handleSuggestion(int $suggestionId, string $action, string $finalValue, array $currentUser, string $remoteAddr, string $rationale = ''): void
    {
        if (!in_array($action, ['approve', 'reject'], true)) {
            throw new Exception("Invalid moderation action.");
        }

        $sStmt = $this->pdo->prepare("
            SELECT es.*, r.table_id 
            FROM edit_suggestions es
            JOIN records r ON es.record_id = r.id
            WHERE es.id = ?
        ");
        $sStmt->execute([$suggestionId]);
        /** @array<string, mixed>|false $suggestion */
        $suggestion = $sStmt->fetch(PDO::FETCH_ASSOC);

        if ($suggestion === false) {
            throw new Exception("Suggestion not found.");
        }

        $tableId = isset($suggestion['table_id']) ? (int)$suggestion['table_id'] : 0;
        $modPermKey = 'moderate_table_' . $tableId;

        if (!\is_admin($this->pdo) && !\has_permission($this->pdo, $modPermKey)) {
            throw new Exception("Unauthorized: You do not have moderation permission for this specific table.");
        }

        $suggestorId = isset($suggestion['suggested_by']) ? $suggestion['suggested_by'] : null;
        $alreadyProcessed = isset($suggestion['points_awarded']) && (int)$suggestion['points_awarded'] === 1;

        try {
            $this->pdo->beginTransaction();

            if ($action === 'approve') {
                $cStmt = $this->pdo->prepare("SELECT id, is_required, data_type, field_options, allow_multiple, min_value, max_value FROM table_columns WHERE column_name = ? AND table_id = ?");
                $cStmt->execute([$suggestion['column_name'], $tableId]);
                /** @array<string, mixed>|false $col */
                $col = $cStmt->fetch(PDO::FETCH_ASSOC);
                
                $originalCreatorId = null;
                if ($col !== false) {
                    // Normalize date inputs automatically if the column is a DATE type
                    if (isset($col['data_type']) && (string)$col['data_type'] === 'DATE' && $finalValue !== '') {
                        $finalValue = normalize_incoming_date($finalValue);
                    }
                    if (!function_exists('flatten_posted_column_value')) {
                        require_once dirname(__DIR__, 2) . '/includes/column_options.php';
                    }
                    $finalValue = flatten_posted_column_value($finalValue);
                    $dtype = isset($col['data_type']) ? (string)$col['data_type'] : '';
                    if ($dtype === 'SELECT') {
                        $opts = parse_column_options($col['field_options'] ?? '');
                        if ($finalValue !== '' && !column_values_are_allowed($finalValue, $opts, !empty($col['allow_multiple']))) {
                            throw new Exception(__('save_data_entry.err_invalid_choice') !== 'save_data_entry.err_invalid_choice' ? sprintf(__('save_data_entry.err_invalid_choice'), (string)($suggestion['column_name'] ?? '')) : 'Please choose a listed option.');
                        }
                    } elseif ($dtype === 'INT' && $finalValue !== '') {
                        if (!preg_match('/^-?\d+$/', $finalValue)) {
                            throw new Exception(__('save_data_entry.err_not_number') !== 'save_data_entry.err_not_number' ? sprintf(__('save_data_entry.err_not_number'), (string)($suggestion['column_name'] ?? '')) : 'That field must be a whole number.');
                        }
                        $n = (int) $finalValue;
                        if (isset($col['min_value']) && $col['min_value'] !== null && $col['min_value'] !== '' && $n < (int)$col['min_value']) {
                            throw new Exception(__('save_data_entry.err_min') !== 'save_data_entry.err_min' ? sprintf(__('save_data_entry.err_min'), (string)($suggestion['column_name'] ?? '')) : 'That value is below the minimum.');
                        }
                        if (isset($col['max_value']) && $col['max_value'] !== null && $col['max_value'] !== '' && $n > (int)$col['max_value']) {
                            throw new Exception(__('save_data_entry.err_max') !== 'save_data_entry.err_max' ? sprintf(__('save_data_entry.err_max'), (string)($suggestion['column_name'] ?? '')) : 'That value is above the maximum.');
                        }
                    }

                    if (!empty($col['is_required']) && $finalValue === '') {
                        throw new Exception("Cannot approve: This column is marked as required and cannot be left blank.");
                    }

                    $dupMode = function_exists('get_setting') ? get_setting($this->pdo, 'duplicate_mode', 'warn') : 'warn';
                    $dupPicky = function_exists('get_setting') ? get_setting($this->pdo, 'duplicate_picky', 'similar') : 'similar';
                    if ($dupMode !== 'off') {
                        $recMeta = $this->pdo->prepare('SELECT table_id FROM records WHERE id = ?');
                        $recMeta->execute([(int) $suggestion['record_id']]);
                        $tId = (int) $recMeta->fetchColumn();
                        if ($tId > 0) {
                            $allCols = $this->pdo->prepare('SELECT * FROM table_columns WHERE table_id = ?');
                            $allCols->execute([$tId]);
                            $colsMap = [];
                            $valuesByCol = [];
                            foreach ($allCols->fetchAll(PDO::FETCH_ASSOC) as $cRow) {
                                $cid = isset($cRow['id']) ? (int) $cRow['id'] : 0;
                                if ($cid < 1) {
                                    continue;
                                }
                                $colsMap[$cid] = $cRow;
                                $valuesByCol[$cid] = '';
                            }
                            $rvStmt = $this->pdo->prepare('SELECT column_id, value_content FROM record_values WHERE record_id = ?');
                            $rvStmt->execute([(int) $suggestion['record_id']]);
                            while ($rv = $rvStmt->fetch(PDO::FETCH_ASSOC)) {
                                $cid = isset($rv['column_id']) ? (int) $rv['column_id'] : 0;
                                $valuesByCol[$cid] = isset($rv['value_content']) && is_string($rv['value_content']) ? $rv['value_content'] : '';
                            }
                            $valuesByCol[(int) $col['id']] = $finalValue;
                            $dupes = (new DuplicateCheckService($this->pdo))->findMatches(
                                $tId,
                                $valuesByCol,
                                $colsMap,
                                $dupPicky === 'exact' ? 'exact' : 'similar',
                                (int) $suggestion['record_id']
                            );
                            if ($dupes !== []) {
                                $top = $dupes[0];
                                $pct = isset($top['bucket']) ? (int) $top['bucket'] : 0;
                                $oid = isset($top['id']) ? (int) $top['id'] : 0;
                                if ($dupMode === 'block') {
                                    throw new Exception(
                                        __('moderate.dup_blocked') !== 'moderate.dup_blocked'
                                            ? sprintf(__('moderate.dup_blocked'), (string) $pct, (string) $oid)
                                            : sprintf('Cannot approve: this would look %s%% similar to record #%s.', (string) $pct, (string) $oid)
                                    );
                                }
                                $_SESSION['message'] = (
                                    __('moderate.dup_approved_note') !== 'moderate.dup_approved_note'
                                        ? sprintf(__('moderate.dup_approved_note'), (string) $pct, (string) $oid)
                                        : sprintf('Approved. This now looks %s%% similar to record #%s.', (string) $pct, (string) $oid)
                                );
                                if ($dupMode === 'flag' && function_exists('audit')) {
                                    audit(
                                        $this->pdo,
                                        (int) $currentUser['id'],
                                        'DUPLICATE_APPROVED',
                                        'Approved a suggestion that looks similar to record ' . $oid,
                                        $remoteAddr
                                    );
                                }
                            }
                        }
                    }

                    $creatorStmt = $this->pdo->prepare("SELECT created_by FROM records WHERE id = ?");
                    $creatorStmt->execute([$suggestion['record_id']]);
                    $originalCreatorId = $creatorStmt->fetchColumn();

                    $checkVal = $this->pdo->prepare("SELECT id FROM record_values WHERE record_id = ? AND column_id = ?");
                    $checkVal->execute([$suggestion['record_id'], $col['id']]);
                    
                    if ($checkVal->fetch()) {
                        $upStmt = $this->pdo->prepare("UPDATE record_values SET value_content = ? WHERE record_id = ? AND column_id = ?");
                        $upStmt->execute([$finalValue, $suggestion['record_id'], $col['id']]);
                    } else {
                        $insStmt = $this->pdo->prepare("INSERT INTO record_values (record_id, column_id, value_content) VALUES (?, ?, ?)");
                        $insStmt->execute([$suggestion['record_id'], $col['id'], $finalValue]);
                    }
                    // Keep map pins in sync when a LOCATION field is approved / overridden
                    $colType = isset($col['data_type']) && is_string($col['data_type']) ? $col['data_type'] : '';
                    if ($colType === 'LOCATION') {
                        \App\Services\LocationValueService::syncPinFromStoredValue(
                            $this->pdo,
                            $tableId,
                            (int) $suggestion['record_id'],
                            (int) $col['id'],
                            is_string($finalValue) ? $finalValue : null
                        );
                    }
                }

                $statusStmt = $this->pdo->prepare("UPDATE edit_suggestions SET status = 'approved', points_awarded = 1, moderator_rationale = ? WHERE id = ?");
                $statusStmt->execute([$rationale !== '' ? $rationale : null, $suggestionId]);

                if (!$alreadyProcessed) {
                    \adjust_user_points($this->pdo, (int)$currentUser['id'], 1);

                    if ($suggestorId !== null) {
                        \adjust_user_points($this->pdo, (int)$suggestorId, 1);
                    }

                    if ($originalCreatorId !== false && $originalCreatorId !== null) {
                        \adjust_user_points($this->pdo, (int)$originalCreatorId, -1);
                    }
                }

                if (empty($_SESSION['message'])) {
                    $_SESSION['message'] = "Suggestion #{$suggestionId} approved and applied.";
                }
            } else {
                $statusStmt = $this->pdo->prepare("UPDATE edit_suggestions SET status = 'rejected', points_awarded = 1, moderator_rationale = ? WHERE id = ?");
                $statusStmt->execute([$rationale !== '' ? $rationale : null, $suggestionId]);

                if (!$alreadyProcessed && $suggestorId !== null) {
                    \adjust_user_points($this->pdo, (int)$suggestorId, -1);
                }

                $_SESSION['message'] = "Suggestion #{$suggestionId} has been rejected.";
            }
            
            $audit = $this->pdo->prepare("INSERT INTO audit_logs (user_id, action, record_id, details, ip_address) VALUES (?, ?, ?, ?, ?)");
            $audit->execute([$currentUser['id'], strtoupper($action) . '_SUGGESTION', $suggestion['record_id'], "Handled suggestion ID: {$suggestionId} in table ID {$tableId}", $remoteAddr]);

            $this->pdo->commit();

            $mailHelper = dirname(__DIR__, 2) . '/includes/suggestion_outcome_mail.php';
            if (is_file($mailHelper)) {
                require_once $mailHelper;
            }
            if (function_exists('send_suggestion_outcome_mail')) {
                $suggestion['moderator_rationale'] = $rationale;
                $wanted = (($suggestion['notify_outcome'] ?? 0) === 1
                    || ($suggestion['notify_outcome'] ?? '') === '1');
                $sent = send_suggestion_outcome_mail($this->pdo, $suggestion, $action);
                if ($wanted && $sent) {
                    $_SESSION['message'] = (isset($_SESSION['message']) ? $_SESSION['message'] . ' ' : '')
                        . 'An outcome email was sent to the person who suggested this.';
                } elseif ($wanted && !$sent) {
                    $_SESSION['error'] = 'The change was saved, but the outcome email could not be sent. Please check Mail settings and that the suggest-edit template exists.';
                }
            }
        } catch (Exception $e) {
            if ($this->pdo->inTransaction()) {
                $this->pdo->rollBack();
            }
            throw $e;
        }
    }
}
