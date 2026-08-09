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

    public function handleSuggestion(int $suggestionId, string $action, string $finalValue, array $currentUser, string $remoteAddr): void
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
                $cStmt = $this->pdo->prepare("SELECT id, is_required, data_type FROM table_columns WHERE column_name = ? AND table_id = ?");
                $cStmt->execute([$suggestion['column_name'], $tableId]);
                /** @array<string, mixed>|false $col */
                $col = $cStmt->fetch(PDO::FETCH_ASSOC);
                
                $originalCreatorId = null;
                if ($col !== false) {
                    // Normalize date inputs automatically if the column is a DATE type
                    if (isset($col['data_type']) && (string)$col['data_type'] === 'DATE' && $finalValue !== '') {
                        $finalValue = normalize_incoming_date($finalValue);
                    }

                    if (!empty($col['is_required']) && $finalValue === '') {
                        throw new Exception("Cannot approve: This column is marked as required and cannot be left blank.");
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
                }

                $statusStmt = $this->pdo->prepare("UPDATE edit_suggestions SET status = 'approved', points_awarded = 1 WHERE id = ?");
                $statusStmt->execute([$suggestionId]);

                if (!$alreadyProcessed) {
                    \adjust_user_points($this->pdo, (int)$currentUser['id'], 1);

                    if ($suggestorId !== null) {
                        \adjust_user_points($this->pdo, (int)$suggestorId, 1);
                    }

                    if ($originalCreatorId !== false && $originalCreatorId !== null) {
                        \adjust_user_points($this->pdo, (int)$originalCreatorId, -1);
                    }
                }

                $_SESSION['message'] = "Suggestion #{$suggestionId} approved and applied.";
            } else {
                $statusStmt = $this->pdo->prepare("UPDATE edit_suggestions SET status = 'rejected', points_awarded = 1 WHERE id = ?");
                $statusStmt->execute([$suggestionId]);

                if (!$alreadyProcessed && $suggestorId !== null) {
                    \adjust_user_points($this->pdo, (int)$suggestorId, -1);
                }

                $_SESSION['message'] = "Suggestion #{$suggestionId} has been rejected.";
            }
            
            $audit = $this->pdo->prepare("INSERT INTO audit_logs (user_id, action, record_id, details, ip_address) VALUES (?, ?, ?, ?, ?)");
            $audit->execute([$currentUser['id'], strtoupper($action) . '_SUGGESTION', $suggestion['record_id'], "Handled suggestion ID: {$suggestionId} in table ID {$tableId}", $remoteAddr]);

            $this->pdo->commit();
        } catch (Exception $e) {
            if ($this->pdo->inTransaction()) {
                $this->pdo->rollBack();
            }
            throw $e;
        }
    }
}
