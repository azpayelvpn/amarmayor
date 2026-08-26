<?php

declare(strict_types=1);

namespace AmarMayor\Http\Controllers;

use AmarMayor\Auth\Auth;
use AmarMayor\Domain\ComplaintCore\ComplaintService;
use AmarMayor\Domain\FieldOperations\FieldTaskService;
use AmarMayor\Domain\ResolutionQuality\ResolutionService;
use AmarMayor\Http\Request;
use AmarMayor\Http\Response;

class ComplaintController
{
    private ComplaintService $complaintService;
    private FieldTaskService $taskService;
    private ResolutionService $resolutionService;

    public function __construct(
        ?ComplaintService $complaintService = null,
        ?FieldTaskService $taskService = null,
        ?ResolutionService $resolutionService = null
    ) {
        $this->complaintService = $complaintService ?: new ComplaintService();
        $this->taskService = $taskService ?: new FieldTaskService();
        $this->resolutionService = $resolutionService ?: new ResolutionService();
    }

    /**
     * Submit complaint via API.
     */
    public function submit(Request $request): Response
    {
        $user = Auth::user();
        if (!$user) {
            return Response::error('UNAUTHORIZED', 'Authentication required', 401);
        }

        $body = $request->getJsonBody();
        $categoryId = (int)($body['category_id'] ?? 0);
        $subcategoryId = (int)($body['subcategory_id'] ?? 0);
        $wardId = (int)($body['ward_id'] ?? 0);
        $description = trim((string)($body['description'] ?? ''));

        if (!$categoryId || !$subcategoryId || !$wardId || empty($description)) {
            return Response::error('VALIDATION_ERROR', 'category_id, subcategory_id, ward_id, and description are required', 422);
        }

        $complaint = $this->complaintService->createComplaint([
            'citizen_user_id' => $user->id,
            'category_id' => $categoryId,
            'subcategory_id' => $subcategoryId,
            'ward_id' => $wardId,
            'description' => $description,
            'latitude' => isset($body['latitude']) ? (float)$body['latitude'] : 24.7471,
            'longitude' => isset($body['longitude']) ? (float)$body['longitude'] : 90.4203,
            'approximate_address' => $body['approximate_address'] ?? null,
            'landmark' => $body['landmark'] ?? null,
            'priority' => $body['priority'] ?? null,
            'operational_classification' => $body['operational_classification'] ?? null,
            'media' => $body['media'] ?? [],
        ]);

        return Response::json($complaint, 201);
    }

    /**
     * Track complaint publicly by tracking number.
     */
    public function track(Request $request, ?string $trackingNumber = null): Response
    {
        $number = $trackingNumber ?: (string)$request->getAttribute('trackingNumber', '');
        $viewingUserId = Auth::id();

        $complaint = $this->complaintService->getComplaintByTrackingNumber($number, $viewingUserId);

        if (!$complaint) {
            return Response::error('NOT_FOUND', 'Complaint not found', 404);
        }

        return Response::json($complaint);
    }

    /**
     * Get authenticated citizen's submitted complaints.
     */
    public function getMyComplaints(Request $request): Response
    {
        $user = Auth::user();
        if (!$user) {
            return Response::error('UNAUTHORIZED', 'Authentication required', 401);
        }

        $complaints = $this->complaintService->getCitizenComplaints($user->id);
        return Response::json($complaints);
    }

    /**
     * Get full complaint details by ID (operational view).
     */
    public function getComplaint(Request $request, ?string $id = null): Response
    {
        $complaintId = (int)($id ?: $request->getAttribute('id', 0));
        $complaint = $this->complaintService->getComplaintById($complaintId);

        if (!$complaint) {
            return Response::error('NOT_FOUND', 'Complaint not found', 404);
        }

        return Response::json($complaint);
    }

    /**
     * Supervisor assigns a field task.
     */
    public function createTask(Request $request, ?string $id = null): Response
    {
        $complaintId = (int)($id ?: $request->getAttribute('id', 0));
        $body = $request->getJsonBody();

        $supervisorEmpId = (int)($body['supervisor_employee_id'] ?? 1);
        $workerEmpId = isset($body['worker_employee_id']) ? (int)$body['worker_employee_id'] : null;
        $teamId = isset($body['team_id']) ? (int)$body['team_id'] : null;
        $instructions = $body['instructions'] ?? null;

        $taskId = $this->taskService->createTask($complaintId, $supervisorEmpId, $workerEmpId, $teamId, $instructions, Auth::id());
        return Response::json(['task_id' => $taskId, 'message' => 'Task created successfully'], 201);
    }

    /**
     * Worker starts a task.
     */
    public function startTask(Request $request, ?string $taskId = null): Response
    {
        $tId = (int)($taskId ?: $request->getAttribute('taskId', 0));
        $body = $request->getJsonBody();
        $workerEmpId = (int)($body['worker_employee_id'] ?? 1);

        $this->taskService->startTask($tId, $workerEmpId, Auth::id());
        return Response::json(['message' => 'Task started']);
    }

    /**
     * Worker marks task completed.
     */
    public function completeTask(Request $request, ?string $taskId = null): Response
    {
        $tId = (int)($taskId ?: $request->getAttribute('taskId', 0));
        $body = $request->getJsonBody();
        $workerEmpId = (int)($body['worker_employee_id'] ?? 1);
        $notes = $body['notes'] ?? null;

        $this->taskService->completeTask($tId, $workerEmpId, $notes, Auth::id());
        return Response::json(['message' => 'Task marked as completed by worker']);
    }

    /**
     * Supervisor verifies field completion.
     */
    public function verify(Request $request, ?string $id = null): Response
    {
        $complaintId = (int)($id ?: $request->getAttribute('id', 0));
        $body = $request->getJsonBody();
        $isSatisfactory = !empty($body['is_satisfactory']);
        $notes = $body['notes'] ?? null;

        $this->resolutionService->supervisorVerify($complaintId, Auth::id() ?: 1, $isSatisfactory, $notes);
        return Response::json(['message' => $isSatisfactory ? 'Complaint verified and awaiting citizen confirmation' : 'Complaint returned for rework']);
    }

    /**
     * Citizen confirms resolution or requests more work.
     */
    public function confirm(Request $request, ?string $id = null): Response
    {
        $complaintId = (int)($id ?: $request->getAttribute('id', 0));
        $user = Auth::user();
        if (!$user) {
            return Response::error('UNAUTHORIZED', 'Authentication required', 401);
        }

        $body = $request->getJsonBody();
        $isResolved = !empty($body['is_resolved']);
        $rating = isset($body['rating']) ? (int)$body['rating'] : null;
        $comment = $body['comment'] ?? null;
        $unresolvedReason = $body['unresolved_reason'] ?? null;

        $this->resolutionService->citizenConfirm($complaintId, $user->id, $isResolved, $rating, $comment, $unresolvedReason);
        return Response::json(['message' => $isResolved ? 'Resolution confirmed and complaint closed' : 'Needs more work recorded; executive attention triggered']);
    }
}
