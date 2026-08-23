<?php
declare(strict_types=1);

namespace App\Controllers;

use App\Services\GHFeedbackService;
use PDO;

/**
 * Admin-only GitHub feedback gateway (manage_settings).
 */
class GHFeedbackController
{
    private PDO $pdo;
    private GHFeedbackService $feedbackService;

    public function __construct(PDO $pdo)
    {
        $this->pdo = $pdo;
        $this->feedbackService = new GHFeedbackService();
    }

    public function index(): void
    {
        \require_permission(
            $this->pdo,
            'manage_settings',
            'Send feedback and report issues to the pRD Team'
        );

        $basePath = defined('BASE_PATH') ? rtrim((string) BASE_PATH, '/') : '';
        $recentLogs = $this->fetchRecentAuditLogs();

        require_once __DIR__ . '/../Views/admin/ghfeedback.php';
    }

    /**
     * JSON search — same permission as the UI.
     */
    public function search(): void
    {
        $this->requireJsonAdmin();
        header('Content-Type: application/json; charset=utf-8');

        $query = isset($_GET['q']) && is_string($_GET['q']) ? trim($_GET['q']) : '';
        if ($query === '' || mb_strlen($query) < 3) {
            echo json_encode([]);
            return;
        }

        $issues = $this->feedbackService->searchExistingIssues($query);
        echo json_encode($issues);
    }

    public function store(): void
    {
        $this->requireJsonAdmin();
        \verify_csrf_token();
        header('Content-Type: application/json; charset=utf-8');

        $type = isset($_POST['type']) && is_string($_POST['type']) ? $_POST['type'] : 'bug';
        $logExcerpt = $this->buildLogExcerptFromPost();

        $built = $this->buildIssueFromPost($type, $logExcerpt);
        if ($built === null) {
            return; // error JSON already sent
        }

        [$title, $description, $labels] = $built;

        // Optional final body from preview step (admin may have edited)
        if (isset($_POST['final_body']) && is_string($_POST['final_body']) && trim($_POST['final_body']) !== '') {
            $description = trim($_POST['final_body']);
        }
        if (isset($_POST['final_title']) && is_string($_POST['final_title']) && trim($_POST['final_title']) !== '') {
            $title = trim($_POST['final_title']);
        }

        // Allocation / triage ticks are owned by the pRD team — never trust client ticks
        $description = $this->applyOfficialWorkflowBlock($type, $description);

        $confirmPublic = isset($_POST['confirm_public']) && (string) $_POST['confirm_public'] === '1';
        $confirmSensitive = isset($_POST['confirm_sensitive']) && (string) $_POST['confirm_sensitive'] === '1';
        $previewOnly = isset($_POST['preview_only']) && (string) $_POST['preview_only'] === '1';

        if ($previewOnly) {
            echo json_encode([
                'preview' => true,
                'title' => $title,
                'body' => $description,
                'looks_sensitive' => $this->feedbackService->bodyLooksSensitive($title . "\n" . $description),
            ]);
            return;
        }

        if (!$confirmPublic) {
            http_response_code(400);
            echo json_encode(['error' => 'Please confirm you have read the public preview before sending.']);
            return;
        }

        if ($this->feedbackService->bodyLooksSensitive($title . "\n" . $description) && !$confirmSensitive) {
            http_response_code(400);
            echo json_encode([
                'error' => 'This text may contain secrets or credentials. Remove them or confirm you still want to publish.',
                'looks_sensitive' => true,
            ]);
            return;
        }

        $result = $this->feedbackService->createNewIssue($title, $description, $labels);
        if (isset($result['number'], $result['html_url'])) {
            echo json_encode([
                'success' => true,
                'message' => 'Feedback submitted successfully to GitHub.',
                'issue_number' => $result['number'],
                'html_url' => $result['html_url'],
            ]);
            return;
        }

        http_response_code(500);
        echo json_encode([
            'error' => isset($result['error']) && is_string($result['error'])
                ? $result['error']
                : 'Failed to submit feedback through the gateway.',
        ]);
    }

    public function comment(): void
    {
        $this->requireJsonAdmin();
        \verify_csrf_token();
        header('Content-Type: application/json; charset=utf-8');

        $issueNumber = filter_var($_POST['issue_number'] ?? null, FILTER_VALIDATE_INT);
        $comment = isset($_POST['comment']) && is_string($_POST['comment'])
            ? trim($_POST['comment'])
            : (isset($_POST['description']) && is_string($_POST['description']) ? trim($_POST['description']) : '');

        if ($issueNumber === false || $issueNumber < 1 || $comment === '') {
            http_response_code(400);
            echo json_encode(['error' => 'Issue number and comment text are required.']);
            return;
        }

        if (isset($_POST['final_body']) && is_string($_POST['final_body']) && trim($_POST['final_body']) !== '') {
            $comment = trim($_POST['final_body']);
        }

        $confirmPublic = isset($_POST['confirm_public']) && (string) $_POST['confirm_public'] === '1';
        $confirmSensitive = isset($_POST['confirm_sensitive']) && (string) $_POST['confirm_sensitive'] === '1';
        $previewOnly = isset($_POST['preview_only']) && (string) $_POST['preview_only'] === '1';

        if ($previewOnly) {
            echo json_encode([
                'preview' => true,
                'title' => 'Comment on #' . $issueNumber,
                'body' => $comment,
                'looks_sensitive' => $this->feedbackService->bodyLooksSensitive($comment),
            ]);
            return;
        }

        if (!$confirmPublic) {
            http_response_code(400);
            echo json_encode(['error' => 'Please confirm you have read the public preview before sending.']);
            return;
        }

        if ($this->feedbackService->bodyLooksSensitive($comment) && !$confirmSensitive) {
            http_response_code(400);
            echo json_encode([
                'error' => 'This text may contain secrets or credentials. Remove them or confirm you still want to publish.',
                'looks_sensitive' => true,
            ]);
            return;
        }

        $result = $this->feedbackService->commentOnIssue($issueNumber, $comment);
        if (isset($result['id'])) {
            echo json_encode([
                'success' => true,
                'message' => 'Comment posted successfully to GitHub.',
            ]);
            return;
        }

        http_response_code(500);
        echo json_encode([
            'error' => isset($result['error']) && is_string($result['error'])
                ? $result['error']
                : 'Failed to post comment through the gateway.',
        ]);
    }

    private function requireJsonAdmin(): void
    {
        \require_permission(
            $this->pdo,
            'manage_settings',
            'Send feedback and report issues to the pRD Team'
        );
    }

    /**
     * @return list<array<string, mixed>>
     */
    private function fetchRecentAuditLogs(): array
    {
        try {
            // Prefer created_at; fall back if an older schema used a spaced name
            $sql = 'SELECT id, action, details, created_at AS logged_at
                    FROM audit_logs
                    WHERE created_at >= (NOW() - INTERVAL 48 HOUR)
                    ORDER BY id DESC
                    LIMIT 50';
            $stmt = $this->pdo->query($sql);
            if ($stmt === false) {
                return [];
            }
            $rows = $stmt->fetchAll(PDO::FETCH_ASSOC);
            return is_array($rows) ? $rows : [];
        } catch (\Throwable $e) {
            return [];
        }
    }

    private function buildLogExcerptFromPost(): string
    {
        $selectedLogIds = $_POST['selected_logs'] ?? [];
        if (!is_array($selectedLogIds) || $selectedLogIds === []) {
            return '';
        }

        $cleanIds = array_values(array_filter(array_map('intval', $selectedLogIds), static fn (int $id): bool => $id > 0));
        if ($cleanIds === []) {
            return '';
        }

        try {
            $inQuery = implode(',', array_fill(0, count($cleanIds), '?'));
            $stmt = $this->pdo->prepare(
                "SELECT action, details, created_at AS logged_at
                 FROM audit_logs
                 WHERE id IN ($inQuery)
                 ORDER BY id DESC"
            );
            $stmt->execute($cleanIds);
            $selectedLogs = $stmt->fetchAll(PDO::FETCH_ASSOC);
            if (!is_array($selectedLogs) || $selectedLogs === []) {
                return '';
            }

            $logExcerpt = "### Selected System Audit Logs (Past 48 Hours - Sanitized):\n";
            foreach ($selectedLogs as $log) {
                $action = isset($log['action']) && is_string($log['action']) ? $log['action'] : 'UNKNOWN';
                $rawDetails = isset($log['details']) && is_string($log['details']) ? $log['details'] : '';
                $safeDetails = $this->feedbackService->sanitizeLogDetails($rawDetails);
                $time = isset($log['logged_at']) && is_string($log['logged_at']) ? $log['logged_at'] : '';
                $logExcerpt .= "- **[{$time}]** `{$action}`: {$safeDetails}\n";
            }
            return $logExcerpt;
        } catch (\Throwable $e) {
            // Do not leak exception detail into a public GitHub issue
            return "### Selected System Audit Logs:\n_(Could not load the selected logs.)_\n";
        }
    }

    /**
     * @return array{0: string, 1: string, 2: list<string>}|null
     */
    private function buildIssueFromPost(string $type, string $logExcerpt): ?array
    {
        $labels = ['community-report'];
        $title = '';
        $description = '';

        switch ($type) {
            case 'bug':
                $labels[] = 'bug';
                $bugTitle = isset($_POST['title']) && is_string($_POST['title']) ? trim($_POST['title']) : '';
                $bugDesc = isset($_POST['description']) && is_string($_POST['description']) ? trim($_POST['description']) : '';
                $steps = isset($_POST['steps']) && is_string($_POST['steps']) ? trim($_POST['steps']) : '';
                $expected = isset($_POST['expected']) && is_string($_POST['expected']) ? trim($_POST['expected']) : '';
                $additional = isset($_POST['additional']) && is_string($_POST['additional']) ? trim($_POST['additional']) : '';
                $severity = isset($_POST['severity']) && is_string($_POST['severity']) ? strtolower(trim($_POST['severity'])) : 'medium';
                if (!in_array($severity, ['low', 'medium', 'high'], true)) {
                    $severity = 'medium';
                }
                if ($bugTitle === '' || $bugDesc === '') {
                    http_response_code(400);
                    echo json_encode(['error' => 'Title and description are required for bug reports.']);
                    return null;
                }
                $title = '[BUG] ' . $bugTitle;
                $description = "---\nname: pRD Community Bug Report\nabout: This report needs to be triaged as it has been submitted directly from a pRD install.\n---\n\n"
                    . "## Who is doing what? (Please See or Add a Cross within the [ ] For Up to Date Status)\n\n"
                    . "- [ ] **Community Report Validation:** [@greenlanegreb @katherinehuk]\n"
                    . "- [X] **Fix/Code:** [@greenlanegreb ]\n"
                    . "- [ ] **Test/Verify:** [@katherinehuk]\n"
                    . "- [ ] **Test Failed** [@greenlanegreb]\n"
                    . "- [ ] **Test Passed** [@greenlanegreb]\n\n"
                    . "## Description\n{$bugDesc}\n\n"
                    . "## Steps to Reproduce\n{$steps}\n\n"
                    . "## Expected Behavior\n{$expected}\n\n"
                    . "## Please provide any additional context\n{$additional}"
                    . ($logExcerpt !== '' ? "\n\n{$logExcerpt}" : '')
                    . "\n\n**Severity:** `{$severity}`";
                break;

            case 'enhancement':
                $labels[] = 'enhancement';
                $featTitle = isset($_POST['title']) && is_string($_POST['title']) ? trim($_POST['title']) : '';
                $problem = isset($_POST['problem']) && is_string($_POST['problem']) ? trim($_POST['problem']) : '';
                $solution = isset($_POST['solution']) && is_string($_POST['solution']) ? trim($_POST['solution']) : '';
                $additional = isset($_POST['additional']) && is_string($_POST['additional']) ? trim($_POST['additional']) : '';
                if ($featTitle === '' || $problem === '') {
                    http_response_code(400);
                    echo json_encode(['error' => 'Title and problem description are required for feature requests.']);
                    return null;
                }
                $title = '[FEATURE] ' . $featTitle;
                $description = "---\nname: pRD Community Feature Extension or Request\nabout: This report needs to be triaged as it has been submitted directly from a pRD install.\n---\n\n"
                    . "## Who is doing what? (Please see Cross For Current Status)\n\n"
                    . "- [ ] **Community Report Validation:** [@greenlanegreb @katherinehuk]\n"
                    . "- [X] **Build/Code:** [@greenlanegreb ]\n"
                    . "- [ ] **Test/Verify:** [@katherinehuk]\n"
                    . "- [ ] **Test Passed:** [@greenlanegreb]\n"
                    . "- [ ] **Test Failed:** [@greenlanegreb]\n\n"
                    . "## Is your feature extension or request related to a problem? Please describe.\n{$problem}\n\n"
                    . "## Please describe the solution you'd like\n{$solution}\n\n"
                    . "## Please provide any additional context\n{$additional}"
                    . ($logExcerpt !== '' ? "\n\n{$logExcerpt}" : '');
                break;

            case 'documentation':
                $labels[] = 'documentation';
                $docTitle = isset($_POST['title']) && is_string($_POST['title']) ? trim($_POST['title']) : '';
                $docName = isset($_POST['doc_name']) && is_string($_POST['doc_name']) ? trim($_POST['doc_name']) : '';
                $paragraphs = isset($_POST['paragraphs']) && is_string($_POST['paragraphs']) ? trim($_POST['paragraphs']) : '';
                $proposed = isset($_POST['proposed']) && is_string($_POST['proposed']) ? trim($_POST['proposed']) : '';
                $reasoning = isset($_POST['reasoning']) && is_string($_POST['reasoning']) ? trim($_POST['reasoning']) : '';
                $additional = isset($_POST['additional']) && is_string($_POST['additional']) ? trim($_POST['additional']) : '';
                if ($docTitle === '' || $docName === '') {
                    http_response_code(400);
                    echo json_encode(['error' => 'Title and document name are required.']);
                    return null;
                }
                $title = '[DOCS] ' . $docTitle;
                $description = "---\nname: pRD Community Documentation Improvement Request\nabout: This report needs to be triaged as it has been submitted directly from a pRD install.\n---\n\n"
                    . "## Who is doing what? (Please see Cross For Current Status)\n\n"
                    . "- [ ] **Community Report Validation:** [@greenlanegreb @katherinehuk]\n"
                    . "- [X] **Update Documentation:** [@greenlanegreb ]\n"
                    . "- [ ] **Proof Read:** [@katherinehuk]\n"
                    . "- [ ] **Proof Read Passed - Commit:** [@greenlanegreb]\n\n"
                    . "## Please let us know what document within the Wiki that your suggestion relates to?\n{$docName}\n\n"
                    . "## Please provide the paragraph number(s) or quote the paragraph(s) below:\n{$paragraphs}\n\n"
                    . "## Please let us know what you think the affected paragraph(s) should say instead:\n{$proposed}\n\n"
                    . "## Please provide your Reasoning:\n{$reasoning}\n\n"
                    . "## Please provide any additional context\n{$additional}"
                    . ($logExcerpt !== '' ? "\n\n{$logExcerpt}" : '');
                break;

            case 'translation':
                $labels[] = 'area: language';
                $transTitle = isset($_POST['title']) && is_string($_POST['title']) ? trim($_POST['title']) : '';
                $offendingText = isset($_POST['offending_text']) && is_string($_POST['offending_text']) ? trim($_POST['offending_text']) : '';
                $userAction = isset($_POST['user_action']) && is_string($_POST['user_action']) ? trim($_POST['user_action']) : '';
                $additional = isset($_POST['additional']) && is_string($_POST['additional']) ? trim($_POST['additional']) : '';
                if ($transTitle === '' || $offendingText === '') {
                    http_response_code(400);
                    echo json_encode(['error' => 'Title and offending text are required.']);
                    return null;
                }
                $title = '[TRANSLATION] ' . $transTitle;
                $description = "---\nname: pRD Community Language Translation Request\nabout: This report needs to be triaged as it has been submitted directly from a pRD install.\n---\n\n"
                    . "## Who is doing what? (Please see Cross For Current Status)\n\n"
                    . "- [ ] **Community Report Validation:** [@greenlanegreb @katherinehuk]\n"
                    . "- [X] **Translate** [@greenlanegreb ]\n"
                    . "- [ ] **Test Passed:** [@greenlanegreb]\n"
                    . "- [ ] **Test Failed:** [@greenlanegreb]\n\n"
                    . "## Please copy and paste the Offending Text:\n{$offendingText}\n\n"
                    . "## What were you doing on pRD at the time please?\n{$userAction}\n\n"
                    . "## Please provide any additional context\n{$additional}"
                    . ($logExcerpt !== '' ? "\n\n{$logExcerpt}" : '');
                break;

            default:
                http_response_code(400);
                echo json_encode(['error' => 'Invalid feedback type.']);
                return null;
        }

        return [$title, $description, $labels];
    }

    /**
     * Official allocation block. Community Report Validation always starts unchecked.
     */
    private function officialWorkflowMarkdown(string $type): string
    {
        switch ($type) {
            case 'enhancement':
                return "## Who is doing what? (Please see Cross For Current Status)\n\n"
                    . "- [ ] **Community Report Validation:** [@greenlanegreb @katherinehuk]\n"
                    . "- [X] **Build/Code:** [@greenlanegreb ]\n"
                    . "- [ ] **Test/Verify:** [@katherinehuk]\n"
                    . "- [ ] **Test Passed:** [@greenlanegreb]\n"
                    . "- [ ] **Test Failed:** [@greenlanegreb]\n\n";
            case 'documentation':
                return "## Who is doing what? (Please see Cross For Current Status)\n\n"
                    . "- [ ] **Community Report Validation:** [@greenlanegreb @katherinehuk]\n"
                    . "- [X] **Update Documentation:** [@greenlanegreb ]\n"
                    . "- [ ] **Proof Read:** [@katherinehuk]\n"
                    . "- [ ] **Proof Read Passed - Commit:** [@greenlanegreb]\n\n";
            case 'translation':
                return "## Who is doing what? (Please see Cross For Current Status)\n\n"
                    . "- [ ] **Community Report Validation:** [@greenlanegreb @katherinehuk]\n"
                    . "- [X] **Translate** [@greenlanegreb ]\n"
                    . "- [ ] **Test Passed:** [@greenlanegreb]\n"
                    . "- [ ] **Test Failed:** [@greenlanegreb]\n\n";
            case 'bug':
            default:
                return "## Who is doing what? (Please See or Add a Cross within the [ ] For Up to Date Status)\n\n"
                    . "- [ ] **Community Report Validation:** [@greenlanegreb @katherinehuk]\n"
                    . "- [X] **Fix/Code:** [@greenlanegreb ]\n"
                    . "- [ ] **Test/Verify:** [@katherinehuk]\n"
                    . "- [ ] **Test Failed** [@greenlanegreb]\n"
                    . "- [ ] **Test Passed** [@greenlanegreb]\n\n";
        }
    }

    /**
     * Replace any client-edited Who-is-doing-what section with the official block.
     */
    private function applyOfficialWorkflowBlock(string $type, string $body): string
    {
        $official = $this->officialWorkflowMarkdown($type);
        $replaced = preg_replace(
            '/## Who is doing what\?[\s\S]*?(?=\n## |\z)/u',
            rtrim($official) . "\n\n",
            $body,
            1
        );
        if (is_string($replaced) && $replaced !== $body) {
            return $replaced;
        }
        return $official . $body;
    }
}
