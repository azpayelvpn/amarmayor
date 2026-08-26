<?php

declare(strict_types=1);

namespace AmarMayor\Http\Controllers;

use AmarMayor\Auth\Auth;
use AmarMayor\Domain\ExecutiveAttention\ExecutiveAttentionService;
use AmarMayor\Http\Request;
use AmarMayor\Http\Response;

class ExecutiveController
{
    private ExecutiveAttentionService $executiveService;

    public function __construct(?ExecutiveAttentionService $executiveService = null)
    {
        $this->executiveService = $executiveService ?: new ExecutiveAttentionService();
    }

    public function getAttentionQueue(Request $request): Response
    {
        $triggerType = $request->query('trigger_type');
        $queue = $this->executiveService->getAttentionQueue($triggerType);
        return Response::json($queue);
    }

    public function issueDirective(Request $request): Response
    {
        $body = $request->getJsonBody();
        $complaintId = (int)($body['complaint_id'] ?? 0);
        $directiveType = (string)($body['directive_type'] ?? 'expedite');
        $instruction = trim((string)($body['instruction'] ?? ''));

        if (!$complaintId || empty($instruction)) {
            return Response::error('VALIDATION_ERROR', 'complaint_id and instruction are required', 422);
        }

        $directiveId = $this->executiveService->issueDirective(Auth::id() ?: 1, $complaintId, $directiveType, $instruction);
        return Response::json(['directive_id' => $directiveId, 'message' => 'Directive issued successfully'], 201);
    }

    public function requestExplanation(Request $request): Response
    {
        $body = $request->getJsonBody();
        $complaintId = (int)($body['complaint_id'] ?? 0);
        $targetEmployeeId = (int)($body['target_employee_id'] ?? 0);
        $question = trim((string)($body['question'] ?? ''));
        $dueDate = $body['due_date'] ?? null;

        if (!$complaintId || !$targetEmployeeId || empty($question)) {
            return Response::error('VALIDATION_ERROR', 'complaint_id, target_employee_id, and question are required', 422);
        }

        $requestId = $this->executiveService->requestExplanation(Auth::id() ?: 1, $targetEmployeeId, $complaintId, $question, $dueDate);
        return Response::json(['request_id' => $requestId, 'message' => 'Explanation request issued successfully'], 201);
    }
}
