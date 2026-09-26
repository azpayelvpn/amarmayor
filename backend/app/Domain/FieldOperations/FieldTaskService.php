<?php

declare(strict_types=1);

namespace AmarMayor\Domain\FieldOperations;

use AmarMayor\Database\DatabaseManager;
use InvalidArgumentException;
use PDO;

class FieldTaskService
{
    /**
     * Creates a field task assigned to a field worker or team under a responsible supervisor.
     */
    public function createTask(
        int $complaintId,
        int $supervisorEmployeeId,
        ?int $workerEmployeeId = null,
        ?int $teamId = null,
        ?string $instructions = null,
        ?int $assignedByUserId = null
    ): int {
        $pdo = DatabaseManager::getConnection();

        $taskCode = 'TSK-' . date('ym') . '-' . sprintf('%05d', random_int(10000, 99999));

        $pdo->beginTransaction();
        try {
            $stmt = $pdo->prepare("
                INSERT INTO field_tasks (
                    complaint_id, task_code, assigned_team_id, assigned_worker_employee_id,
                    supervisor_employee_id, task_status, instructions, created_at
                ) VALUES (?, ?, ?, ?, ?, 'pending', ?, NOW())
            ");
            $stmt->execute([
                $complaintId,
                $taskCode,
                $teamId,
                $workerEmployeeId,
                $supervisorEmployeeId,
                $instructions,
            ]);
            $taskId = (int)$pdo->lastInsertId();

            // Record initial assignment history
            $assignStmt = $pdo->prepare("
                INSERT INTO field_task_assignments (
                    field_task_id, assigned_team_id, assigned_worker_employee_id,
                    assigned_by_user_id, assignment_notes, effective_from, created_at
                ) VALUES (?, ?, ?, ?, ?, NOW(), NOW())
            ");
            $assignStmt->execute([
                $taskId,
                $teamId,
                $workerEmployeeId,
                $assignedByUserId,
                $instructions,
            ]);

            $pdo->commit();
            return $taskId;
        } catch (\Throwable $e) {
            $pdo->rollBack();
            throw $e;
        }
    }

    /**
     * Ward Supervisor dispatches a designated Team Leader with a specific squad worker count.
     */
    public function dispatchSquadTask(
        int $complaintId,
        int $supervisorUserId,
        int $teamLeaderEmpId,
        int $workerCount,
        ?string $instructions = null,
        ?float $estimatedHours = 2.0
    ): int {
        $pdo = DatabaseManager::getConnection();

        // 1. Resolve supervisor employee ID
        $supStmt = $pdo->prepare("
            SELECT e.id FROM employees e
            INNER JOIN persons p ON p.id = e.person_id
            WHERE p.user_id = ? LIMIT 1
        ");
        $supStmt->execute([$supervisorUserId]);
        $supervisorEmpId = (int)$supStmt->fetchColumn() ?: 1;

        // 2. Resolve team for this team leader or ward
        $tStmt = $pdo->prepare("SELECT id FROM teams WHERE team_leader_employee_id = ? LIMIT 1");
        $tStmt->execute([$teamLeaderEmpId]);
        $teamId = $tStmt->fetchColumn();

        if (!$teamId) {
            $cWardStmt = $pdo->prepare("SELECT ward_id FROM complaints WHERE id = ? LIMIT 1");
            $cWardStmt->execute([$complaintId]);
            $wardId = $cWardStmt->fetchColumn();
            if ($wardId) {
                $twStmt = $pdo->prepare("SELECT id FROM teams WHERE ward_id = ? LIMIT 1");
                $twStmt->execute([$wardId]);
                $teamId = $twStmt->fetchColumn();
            }
        }
        $teamId = $teamId ? (int)$teamId : null;

        // 3. Resolve team leader details for note
        $tlStmt = $pdo->prepare("
            SELECT p.full_name_bn, p.official_phone
            FROM employees e
            INNER JOIN persons p ON p.id = e.person_id
            WHERE e.id = ? LIMIT 1
        ");
        $tlStmt->execute([$teamLeaderEmpId]);
        $leaderInfo = $tlStmt->fetch(PDO::FETCH_ASSOC) ?: ['full_name_bn' => 'মাঠ দলনেতা', 'official_phone' => ''];

        $taskCode = 'TSK-' . date('ym') . '-' . sprintf('%05d', random_int(10000, 99999));

        $pdo->beginTransaction();
        try {
            $stmt = $pdo->prepare("
                INSERT INTO field_tasks (
                    complaint_id, task_code, assigned_team_id, team_leader_employee_id,
                    worker_count, estimated_hours, supervisor_employee_id,
                    task_status, instructions, created_at
                ) VALUES (?, ?, ?, ?, ?, ?, ?, 'pending', ?, NOW())
            ");
            $stmt->execute([
                $complaintId,
                $taskCode,
                $teamId,
                $teamLeaderEmpId,
                max(1, $workerCount),
                $estimatedHours,
                $supervisorEmpId,
                $instructions,
            ]);
            $taskId = (int)$pdo->lastInsertId();

            // Record assignment history
            $assignStmt = $pdo->prepare("
                INSERT INTO field_task_assignments (
                    field_task_id, assigned_team_id, assigned_worker_employee_id,
                    assigned_by_user_id, assignment_notes, effective_from, created_at
                ) VALUES (?, ?, ?, ?, ?, NOW(), NOW())
            ");
            $assignStmt->execute([
                $taskId,
                $teamId,
                $teamLeaderEmpId,
                $supervisorUserId,
                $instructions ?: "মাঠ স্কোয়াড মোতায়েন (দলনেতা: {$leaderInfo['full_name_bn']}, পরিচ্ছন্নতাকর্মী: {$workerCount} জন)",
            ]);

            // Update parent complaint status to assigned
            $pdo->prepare("UPDATE complaints SET internal_status = 'assigned', citizen_status = 'assigned' WHERE id = ? AND internal_status IN ('submitted', 'review_required')")
                ->execute([$complaintId]);

            // Record internal coordination note
            $noteText = "সুপারভাইজার কর্তৃক স্কোয়াড মোতায়েন: দলনেতা " . $leaderInfo['full_name_bn'] . " (" . ($leaderInfo['official_phone'] ?: 'মোবাইল') . "), নিয়োজিত পরিচ্ছন্নতাকর্মী: " . to_bn_number((string)$workerCount) . " জন। " . ($instructions ? "নির্দেশনা: {$instructions}" : "");
            $noteStmt = $pdo->prepare("
                INSERT INTO internal_notes (complaint_id, author_user_id, note_type, note_text, created_at)
                VALUES (?, ?, 'assignment', ?, NOW())
            ");
            $noteStmt->execute([$complaintId, $supervisorUserId, $noteText]);

            $pdo->commit();
            return $taskId;
        } catch (\Throwable $e) {
            $pdo->rollBack();
            throw $e;
        }
    }

    /**
     * Worker starts executing the field task.
     */
    public function startTask(int $taskId, int $workerEmployeeId, ?int $actorUserId = null): void
    {
        $pdo = DatabaseManager::getConnection();
        $stmt = $pdo->prepare("SELECT complaint_id, task_status FROM field_tasks WHERE id = ? LIMIT 1");
        $stmt->execute([$taskId]);
        $task = $stmt->fetch(PDO::FETCH_ASSOC);

        if (!$task) {
            throw new InvalidArgumentException("Task ID {$taskId} not found.");
        }

        $complaintId = (int)$task['complaint_id'];

        $pdo->beginTransaction();
        try {
            // Update task status
            $pdo->prepare("UPDATE field_tasks SET task_status = 'in_progress', started_at = NOW() WHERE id = ?")
                ->execute([$taskId]);

            // Update parent complaint status
            $pdo->prepare("UPDATE complaints SET internal_status = 'in_progress', citizen_status = 'in_progress' WHERE id = ?")
                ->execute([$complaintId]);

            // Append status history
            $histStmt = $pdo->prepare("
                INSERT INTO complaint_status_history (
                    complaint_id, from_internal_status, to_internal_status,
                    from_citizen_status, to_citizen_status, action_name, actor_user_id, reason, created_at
                ) VALUES (?, 'assigned', 'in_progress', 'assigned', 'in_progress', 'work_started', ?, 'Field crew commenced operations', NOW())
            ");
            $histStmt->execute([$complaintId, $actorUserId]);

            $pdo->commit();
        } catch (\Throwable $e) {
            $pdo->rollBack();
            throw $e;
        }
    }

    /**
     * Attaches before/in-progress/after photo evidence to a field task.
     */
    public function addTaskEvidence(
        int $taskId,
        int $uploaderUserId,
        string $filePath,
        string $evidenceStage = 'after_work',
        string $mediaType = 'image',
        ?float $lat = null,
        ?float $lng = null
    ): int {
        $pdo = DatabaseManager::getConnection();
        $stmt = $pdo->prepare("SELECT complaint_id FROM field_tasks WHERE id = ? LIMIT 1");
        $stmt->execute([$taskId]);
        $complaintId = $stmt->fetchColumn();

        if (!$complaintId) {
            throw new InvalidArgumentException("Task ID {$taskId} not found.");
        }

        $pdo->beginTransaction();
        try {
            // 1. Insert into complaint_media
            $mStmt = $pdo->prepare("
                INSERT INTO complaint_media (
                    complaint_id, uploader_user_id, media_type, original_file_path,
                    mime_type, file_size_bytes, gps_latitude, gps_longitude, moderation_status, created_at
                ) VALUES (?, ?, ?, ?, 'image/jpeg', 204800, ?, ?, 'approved', NOW())
            ");
            $mStmt->execute([$complaintId, $uploaderUserId, $mediaType, $filePath, $lat, $lng]);
            $mediaId = (int)$pdo->lastInsertId();

            // 2. Insert into task_evidence
            $eStmt = $pdo->prepare("
                INSERT INTO task_evidence (field_task_id, media_id, evidence_stage, server_timestamp)
                VALUES (?, ?, ?, NOW())
            ");
            $eStmt->execute([$taskId, $mediaId, $evidenceStage]);

            $pdo->commit();
            return $mediaId;
        } catch (\Throwable $e) {
            $pdo->rollBack();
            throw $e;
        }
    }

    /**
     * Alias for addTaskEvidence.
     */
    public function submitEvidence(
        int $taskId,
        int $uploaderUserId,
        string $filePath,
        string $evidenceStage = 'after_work',
        string $mediaType = 'image',
        ?float $lat = null,
        ?float $lng = null
    ): int {
        return $this->addTaskEvidence($taskId, $uploaderUserId, $filePath, $evidenceStage, $mediaType, $lat, $lng);
    }

    /**
     * Marks field task completed by worker.
     * Note: Worker completion transitions complaint to 'work_completed', which is NOT final closure!
     */
    public function completeTask(int $taskId, int $workerEmployeeId, ?string $notes = null, ?int $actorUserId = null): void
    {
        $pdo = DatabaseManager::getConnection();
        $stmt = $pdo->prepare("SELECT complaint_id FROM field_tasks WHERE id = ? LIMIT 1");
        $stmt->execute([$taskId]);
        $complaintId = (int)$stmt->fetchColumn();

        if (!$complaintId) {
            throw new InvalidArgumentException("Task ID {$taskId} not found.");
        }

        $pdo->beginTransaction();
        try {
            // 1. Mark task completed
            $pdo->prepare("UPDATE field_tasks SET task_status = 'completed', completed_at = NOW() WHERE id = ?")
                ->execute([$taskId]);

            // 2. Transition parent complaint to 'work_completed' (pending supervisor verification)
            $pdo->prepare("
                UPDATE complaints 
                SET internal_status = 'work_completed', 
                    citizen_status = 'work_completed',
                    completion_attempts = completion_attempts + 1 
                WHERE id = ?
            ")->execute([$complaintId]);

            // 3. Append status history
            $histStmt = $pdo->prepare("
                INSERT INTO complaint_status_history (
                    complaint_id, from_internal_status, to_internal_status,
                    from_citizen_status, to_citizen_status, action_name, actor_user_id, reason, created_at
                ) VALUES (?, 'in_progress', 'work_completed', 'in_progress', 'work_completed', 'work_completed_by_worker', ?, ?, NOW())
            ");
            $histStmt->execute([$complaintId, $actorUserId, $notes]);

            $pdo->commit();
        } catch (\Throwable $e) {
            $pdo->rollBack();
            throw $e;
        }
    }

    /**
     * Returns all tasks for a specific complaint with evidence.
     */
    public function getTasksForComplaint(int $complaintId): array
    {
        $pdo = DatabaseManager::getConnection();
        $stmt = $pdo->prepare("
            SELECT ft.*, 
                   p_worker.full_name_bn as worker_name_bn, p_worker.full_name_en as worker_name_en,
                   p_sup.full_name_bn as supervisor_name_bn, p_sup.full_name_en as supervisor_name_en,
                   t.name_bn as team_name_bn, t.name_en as team_name_en
            FROM field_tasks ft
            LEFT JOIN employees e_worker ON e_worker.id = ft.assigned_worker_employee_id
            LEFT JOIN persons p_worker ON p_worker.id = e_worker.person_id
            LEFT JOIN employees e_sup ON e_sup.id = ft.supervisor_employee_id
            LEFT JOIN persons p_sup ON p_sup.id = e_sup.person_id
            LEFT JOIN teams t ON t.id = ft.assigned_team_id
            WHERE ft.complaint_id = ?
            ORDER BY ft.id ASC
        ");
        $stmt->execute([$complaintId]);
        $tasks = $stmt->fetchAll(PDO::FETCH_ASSOC);

        foreach ($tasks as &$task) {
            $evStmt = $pdo->prepare("
                SELECT te.id as evidence_id, te.evidence_stage, te.server_timestamp,
                       cm.original_file_path, cm.media_type
                FROM task_evidence te
                INNER JOIN complaint_media cm ON cm.id = te.media_id
                WHERE te.field_task_id = ?
                ORDER BY te.id ASC
            ");
            $evStmt->execute([$task['id']]);
            $task['evidence'] = $evStmt->fetchAll(PDO::FETCH_ASSOC);
        }

        return $tasks;
    }

    /**
     * Retrieves all field tasks assigned to a specific worker or worker's teams or team leader.
     */
    public function getTasksForWorker(int $workerUserId): array
    {
        $pdo = DatabaseManager::getConnection();
        $stmt = $pdo->prepare("
            SELECT ft.*, 
                   c.public_complaint_number, cl.landmark, cl.approximate_address, c.description,
                   w.ward_number, z.name_bn as zone_name_bn, z.name_en as zone_name_en,
                   sc.name_bn as subcategory_name_bn, sc.name_en as subcategory_name_en,
                   cat.name_bn as category_name_bn, cat.name_en as category_name_en,
                   p_sup.full_name_bn as supervisor_name_bn, p_sup.full_name_en as supervisor_name_en,
                   p_leader.full_name_bn as team_leader_name_bn, p_leader.full_name_en as team_leader_name_en
            FROM field_tasks ft
            INNER JOIN complaints c ON c.id = ft.complaint_id
            LEFT JOIN complaint_locations cl ON cl.complaint_id = c.id
            LEFT JOIN wards w ON w.id = c.ward_id
            LEFT JOIN zones z ON z.id = w.zone_id
            LEFT JOIN complaint_subcategories sc ON sc.id = c.subcategory_id
            LEFT JOIN complaint_categories cat ON cat.id = sc.category_id
            LEFT JOIN employees e_worker ON e_worker.id = ft.assigned_worker_employee_id
            LEFT JOIN persons p_worker ON p_worker.id = e_worker.person_id
            LEFT JOIN employees e_leader ON e_leader.id = ft.team_leader_employee_id
            LEFT JOIN persons p_leader ON p_leader.id = e_leader.person_id
            LEFT JOIN employees e_sup ON e_sup.id = ft.supervisor_employee_id
            LEFT JOIN persons p_sup ON p_sup.id = e_sup.person_id
            WHERE p_worker.user_id = ? 
               OR p_leader.user_id = ?
               OR ft.assigned_team_id IN (
                    SELECT tm.team_id FROM team_members tm
                    INNER JOIN employees ew ON ew.id = tm.employee_id
                    INNER JOIN persons pw ON pw.id = ew.person_id
                    WHERE pw.user_id = ?
               )
               OR ft.assigned_team_id IN (
                    SELECT t.id FROM teams t
                    INNER JOIN employees el ON el.id = t.team_leader_employee_id
                    INNER JOIN persons pl ON pl.id = el.person_id
                    WHERE pl.user_id = ?
               )
            ORDER BY ft.id DESC
        ");
        $stmt->execute([$workerUserId, $workerUserId, $workerUserId, $workerUserId]);
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }

    /**
     * Retrieves all field tasks and complaints under a supervisor's jurisdiction.
     */
    public function getTasksForSupervisor(int $supervisorUserId): array
    {
        $pdo = DatabaseManager::getConnection();
        $stmt = $pdo->prepare("
            SELECT ft.*, 
                   c.public_complaint_number, cl.landmark, cl.approximate_address, c.description, c.internal_status as complaint_status,
                   w.ward_number, z.name_bn as zone_name_bn, z.name_en as zone_name_en,
                   sc.name_bn as subcategory_name_bn, sc.name_en as subcategory_name_en,
                   cat.name_bn as category_name_bn, cat.name_en as category_name_en,
                   p_worker.full_name_bn as worker_name_bn, p_worker.full_name_en as worker_name_en,
                   p_leader.full_name_bn as team_leader_name_bn, p_leader.full_name_en as team_leader_name_en,
                   t.name_bn as team_name_bn, t.name_en as team_name_en
            FROM field_tasks ft
            INNER JOIN complaints c ON c.id = ft.complaint_id
            LEFT JOIN complaint_locations cl ON cl.complaint_id = c.id
            LEFT JOIN wards w ON w.id = c.ward_id
            LEFT JOIN zones z ON z.id = w.zone_id
            LEFT JOIN complaint_subcategories sc ON sc.id = c.subcategory_id
            LEFT JOIN complaint_categories cat ON cat.id = sc.category_id
            LEFT JOIN employees e_sup ON e_sup.id = ft.supervisor_employee_id
            LEFT JOIN persons p_sup ON p_sup.id = e_sup.person_id
            LEFT JOIN employees e_worker ON e_worker.id = ft.assigned_worker_employee_id
            LEFT JOIN persons p_worker ON p_worker.id = e_worker.person_id
            LEFT JOIN employees e_leader ON e_leader.id = ft.team_leader_employee_id
            LEFT JOIN persons p_leader ON p_leader.id = e_leader.person_id
            LEFT JOIN teams t ON t.id = ft.assigned_team_id
            WHERE p_sup.user_id = ? OR ft.assigned_team_id IN (
                SELECT team.id FROM teams team
                INNER JOIN employees es ON es.id = team.supervisor_employee_id
                INNER JOIN persons ps ON ps.id = es.person_id
                WHERE ps.user_id = ?
            ) OR c.ward_id IN (
                SELECT w.id FROM employee_responsibilities er
                INNER JOIN employees em ON em.id = er.employee_id
                INNER JOIN persons pr ON pr.id = em.person_id
                INNER JOIN wards w ON w.ward_number = er.area_id
                WHERE pr.user_id = ? AND er.area_type = 'ward'
            )
            ORDER BY ft.id DESC
        ");
        $stmt->execute([$supervisorUserId, $supervisorUserId, $supervisorUserId]);
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }

    /**
     * Get live workforce availability metrics and squad team leaders for a specific ward.
     */
    public function getWardWorkforceStats(int $wardId): array
    {
        $pdo = DatabaseManager::getConnection();

        // 1. Total cleaners registered in teams for this ward
        $cleanerStmt = $pdo->prepare("
            SELECT COUNT(DISTINCT tm.employee_id)
            FROM team_members tm
            INNER JOIN teams t ON t.id = tm.team_id
            WHERE t.ward_id = ? AND t.status = 'active'
        ");
        $cleanerStmt->execute([$wardId]);
        $totalCleaners = (int)$cleanerStmt->fetchColumn();

        if ($totalCleaners === 0) {
            $totalCleaners = 8; // Default standard squad count
        }

        // 2. Currently deployed workers in this ward on active tasks
        $deployedStmt = $pdo->prepare("
            SELECT COALESCE(SUM(ft.worker_count), 0)
            FROM field_tasks ft
            INNER JOIN complaints c ON c.id = ft.complaint_id
            WHERE c.ward_id = ? AND ft.task_status IN ('pending', 'in_progress')
        ");
        $deployedStmt->execute([$wardId]);
        $deployedWorkers = (int)$deployedStmt->fetchColumn();

        $availableWorkers = max(0, $totalCleaners - $deployedWorkers);

        // 3. Team leaders active in this ward
        $leadersStmt = $pdo->prepare("
            SELECT e.id as employee_id, p.full_name_bn, p.full_name_en, p.official_phone, e.employee_code, t.id as team_id, t.name_bn as team_name_bn
            FROM teams t
            INNER JOIN employees e ON e.id = t.team_leader_employee_id
            INNER JOIN persons p ON p.id = e.person_id
            WHERE t.ward_id = ? AND t.status = 'active'
        ");
        $leadersStmt->execute([$wardId]);
        $teamLeaders = $leadersStmt->fetchAll(PDO::FETCH_ASSOC);

        // Fallback: If no team leader directly mapped to team, get any leader in waste department
        if (empty($teamLeaders)) {
            $teamLeaders = $pdo->query("
                SELECT e.id as employee_id, p.full_name_bn, p.full_name_en, p.official_phone, e.employee_code, NULL as team_id, 'সাধারণ পরিচ্ছন্নতা দল' as team_name_bn
                FROM employees e
                INNER JOIN persons p ON p.id = e.person_id
                INNER JOIN user_roles ur ON ur.user_id = p.user_id
                INNER JOIN roles r ON r.id = ur.role_id AND r.slug = 'team_leader'
                LIMIT 3
            ")->fetchAll(PDO::FETCH_ASSOC);
        }

        return [
            'total_workers' => $totalCleaners,
            'total_cleaners' => $totalCleaners,
            'deployed_workers' => $deployedWorkers,
            'available_workers' => $availableWorkers,
            'team_leaders' => $teamLeaders,
        ];
    }

    /**
     * Supervisor requests emergency manpower support due to ward workforce shortage.
     */
    public function requestWorkforceSupport(
        int $complaintId,
        int $supervisorUserId,
        int $requestedWorkerCount,
        string $details
    ): int {
        $pdo = DatabaseManager::getConnection();

        $empStmt = $pdo->prepare("
            SELECT e.id FROM employees e
            INNER JOIN persons p ON p.id = e.person_id
            WHERE p.user_id = ? LIMIT 1
        ");
        $empStmt->execute([$supervisorUserId]);
        $empId = (int)$empStmt->fetchColumn() ?: 1;

        $cStmt = $pdo->prepare("SELECT public_complaint_number FROM complaints WHERE id = ? LIMIT 1");
        $cStmt->execute([$complaintId]);
        $complaintNum = $cStmt->fetchColumn() ?: 'MCC-TASK';

        $pdo->beginTransaction();
        try {
            $stmt = $pdo->prepare("
                INSERT INTO support_requests (
                    complaint_id, requested_by_employee_id, support_type,
                    requested_worker_count, target_department_id, details,
                    status, fulfillment_status, created_at
                ) VALUES (?, ?, 'extra_manpower', ?, 1, ?, 'pending', 'pending', NOW())
            ");
            $stmt->execute([
                $complaintId,
                $empId,
                max(1, $requestedWorkerCount),
                $details,
            ]);
            $requestId = (int)$pdo->lastInsertId();

            $noteStmt = $pdo->prepare("
                INSERT INTO internal_notes (complaint_id, author_user_id, note_type, note_text, created_at)
                VALUES (?, ?, 'coordination', ?, NOW())
            ");
            $noteText = "জরুরি অতিরিক্ত জনবল রিকুইজিশন (" . to_bn_number((string)$requestedWorkerCount) . " জন পরিচ্ছন্নতাকর্মী): " . $details;
            $noteStmt->execute([$complaintId, $supervisorUserId, $noteText]);

            $pdo->commit();

            try {
                $notif = new \AmarMayor\Domain\Notifications\NotificationService();
                $notif->notifyDepartmentHead(
                    1,
                    "জরুরি ওয়ার্ড কর্মী সংকট রিকুইজিশন",
                    "Emergency Workforce Shortage Requisition",
                    "অভিযোগ {$complaintNum}-এর জন্য {$requestedWorkerCount} জন অতিরিক্ত পরিচ্ছন্নতাকর্মী চাওয়া হয়েছে: {$details}",
                    "{$requestedWorkerCount} workers requested for complaint {$complaintNum}: {$details}",
                    "workforce_shortage",
                    ['complaint_id' => $complaintId, 'support_request_id' => $requestId]
                );
            } catch (\Throwable $t) {
                // Non-blocking
            }

            return $requestId;
        } catch (\Throwable $e) {
            $pdo->rollBack();
            throw $e;
        }
    }

    /**
     * Department Head allocates reinforcement workforce from reserve pool or inter-ward deputation.
     */
    public function allocateWorkforceSupport(
        int $supportRequestId,
        int $departmentHeadUserId,
        int $allocatedWorkerCount,
        string $allocationSource,
        string $responseNotes,
        ?int $sourceWardId = null,
        ?string $allocatedOperator = null
    ): void {
        $pdo = DatabaseManager::getConnection();

        $stmt = $pdo->prepare("
            SELECT sr.*, c.public_complaint_number, p.user_id as requester_user_id
            FROM support_requests sr
            INNER JOIN complaints c ON c.id = sr.complaint_id
            INNER JOIN employees e ON e.id = sr.requested_by_employee_id
            INNER JOIN persons p ON p.id = e.person_id
            WHERE sr.id = ? LIMIT 1
        ");
        $stmt->execute([$supportRequestId]);
        $req = $stmt->fetch(PDO::FETCH_ASSOC);

        if (!$req) {
            throw new InvalidArgumentException("Support request #{$supportRequestId} not found.");
        }

        if ($allocationSource === 'central_reserve' || $allocationSource === 'reserve_pool') {
            $allocationSource = "কেন্দ্রীয় জরুরি রিজার্ভ পুল";
        } elseif ($allocationSource === 'deputation' && $sourceWardId) {
            $allocationSource = "ওয়ার্ড " . to_bn_number((string)$sourceWardId) . " থেকে সাময়িক ডেপুটেশন";
        }

        $pdo->beginTransaction();
        try {
            $update = $pdo->prepare("
                UPDATE support_requests
                SET status = 'approved',
                    fulfillment_status = 'dispatched',
                    allocated_worker_count = ?,
                    allocated_resource = ?,
                    allocated_operator = ?,
                    source_ward_id = ?,
                    responded_by_user_id = ?,
                    response_notes = ?
                WHERE id = ?
            ");
            $update->execute([
                $allocatedWorkerCount,
                $allocationSource,
                $allocatedOperator,
                $sourceWardId,
                $departmentHeadUserId,
                $responseNotes,
                $supportRequestId,
            ]);

            $noteStmt = $pdo->prepare("
                INSERT INTO internal_notes (complaint_id, author_user_id, note_type, note_text, created_at)
                VALUES (?, ?, 'coordination', ?, NOW())
            ");
            $noteText = "অতিরিক্ত জনবল বরাদ্দ অনুমোদিত (" . to_bn_number((string)$allocatedWorkerCount) . " জন): উৎস - {$allocationSource}। " . $responseNotes;
            $noteStmt->execute([$req['complaint_id'], $departmentHeadUserId, $noteText]);

            $pdo->commit();

            if (!empty($req['requester_user_id'])) {
                try {
                    $notif = new \AmarMayor\Domain\Notifications\NotificationService();
                    $notif->notifyUser(
                        (int)$req['requester_user_id'],
                        "অতিরিক্ত কর্মী বরাদ্দ দেওয়া হয়েছে",
                        "Additional Workforce Dispatched",
                        "অভিযোগ {$req['public_complaint_number']}-এর জন্য {$allocatedWorkerCount} জন কর্মী পাঠানো হয়েছে ({$allocationSource})।",
                        "{$allocatedWorkerCount} workers dispatched for {$req['public_complaint_number']}",
                        "workforce_dispatched",
                        ['complaint_id' => $req['complaint_id'], 'support_request_id' => $supportRequestId]
                    );
                } catch (\Throwable $t) {
                    // Non-blocking
                }
            }
        } catch (\Throwable $e) {
            $pdo->rollBack();
            throw $e;
        }
    }

    /**
     * Creates an inter-department support request (e.g. sanitation needing heavy equipment from engineering).
     */
    public function createSupportRequest(
        int $complaintId,
        ?int $fieldTaskId,
        int $requestedByUserId,
        string $supportType,
        ?int $targetDepartmentId,
        string $details
    ): int {
        $pdo = DatabaseManager::getConnection();

        // Resolve employee ID
        $empStmt = $pdo->prepare("
            SELECT e.id FROM employees e
            INNER JOIN persons p ON p.id = e.person_id
            WHERE p.user_id = ? LIMIT 1
        ");
        $empStmt->execute([$requestedByUserId]);
        $empId = (int)$empStmt->fetchColumn();
        if (!$empId) {
            $empId = 1;
        }

        // Fetch complaint number
        $cStmt = $pdo->prepare("SELECT public_complaint_number FROM complaints WHERE id = ? LIMIT 1");
        $cStmt->execute([$complaintId]);
        $complaintNum = $cStmt->fetchColumn() ?: 'MCC-TASK';

        $pdo->beginTransaction();
        try {
            $stmt = $pdo->prepare("
                INSERT INTO support_requests (
                    complaint_id, field_task_id, requested_by_employee_id,
                    support_type, target_department_id, details, status, created_at
                ) VALUES (?, ?, ?, ?, ?, ?, 'pending', NOW())
            ");
            $stmt->execute([
                $complaintId,
                $fieldTaskId,
                $empId,
                $supportType,
                $targetDepartmentId,
                $details,
            ]);
            $requestId = (int)$pdo->lastInsertId();

            // Record internal note for staff visibility
            $noteStmt = $pdo->prepare("
                INSERT INTO internal_notes (complaint_id, author_user_id, note_type, note_text, created_at)
                VALUES (?, ?, 'coordination', ?, NOW())
            ");
            $noteText = "আন্তঃবিভাগীয় সহায়তা আবেদন ({$supportType}): " . $details;
            $noteStmt->execute([$complaintId, $requestedByUserId, $noteText]);

            $pdo->commit();

            // Trigger notification to target department head
            if ($targetDepartmentId) {
                try {
                    $notif = new \AmarMayor\Domain\Notifications\NotificationService();
                    $notif->notifyDepartmentHead(
                        $targetDepartmentId,
                        "নতুন আন্তঃবিভাগীয় সহায়তা আবেদন",
                        "New Inter-Department Support Request",
                        "অভিযোগ {$complaintNum}-এর জন্য সহায়তা প্রয়োজন: {$details}",
                        "Support needed for complaint {$complaintNum}: {$details}",
                        "cross_department_support",
                        ['complaint_id' => $complaintId, 'support_request_id' => $requestId]
                    );
                } catch (\Throwable $t) {
                    // Non-blocking notification failure
                }
            }

            return $requestId;
        } catch (\Throwable $e) {
            $pdo->rollBack();
            throw $e;
        }
    }

    /**
     * Responds to an inter-department support request (e.g. approve, allocate equipment, or provide advice).
     */
    public function respondSupportRequest(
        int $supportRequestId,
        int $respondedByUserId,
        string $status,
        string $responseNotes,
        ?string $allocatedResource = null,
        ?string $allocatedOperator = null,
        ?string $scheduledArrival = null
    ): void {
        $pdo = DatabaseManager::getConnection();

        $stmt = $pdo->prepare("
            SELECT sr.*, c.public_complaint_number, p.user_id as requester_user_id
            FROM support_requests sr
            INNER JOIN complaints c ON c.id = sr.complaint_id
            INNER JOIN employees e ON e.id = sr.requested_by_employee_id
            INNER JOIN persons p ON p.id = e.person_id
            WHERE sr.id = ? LIMIT 1
        ");
        $stmt->execute([$supportRequestId]);
        $req = $stmt->fetch(PDO::FETCH_ASSOC);

        if (!$req) {
            throw new InvalidArgumentException("Support request #{$supportRequestId} not found.");
        }

        $pdo->beginTransaction();
        try {
            $fulfillmentStatus = $status === 'approved' ? 'dispatched' : 'rejected';
            $update = $pdo->prepare("
                UPDATE support_requests
                SET status = ?, responded_by_user_id = ?, response_notes = ?,
                    allocated_resource = COALESCE(?, allocated_resource),
                    allocated_operator = COALESCE(?, allocated_operator),
                    scheduled_arrival = COALESCE(?, scheduled_arrival),
                    fulfillment_status = ?
                WHERE id = ?
            ");
            $update->execute([
                $status,
                $respondedByUserId,
                $responseNotes,
                $allocatedResource,
                $allocatedOperator,
                $scheduledArrival,
                $fulfillmentStatus,
                $supportRequestId
            ]);

            // Record internal note
            $allocSummary = "";
            if (!empty($allocatedResource) || !empty($allocatedOperator)) {
                $allocSummary = " [বরাদ্দকৃত সরঞ্জাম: " . ($allocatedResource ?: 'অনির্দিষ্ট') . ", চালক/যোগাযোগ: " . ($allocatedOperator ?: 'অনির্দিষ্ট') . ", শিডিউল: " . ($scheduledArrival ?: 'অবিলম্বে') . "]";
            }

            $noteStmt = $pdo->prepare("
                INSERT INTO internal_notes (complaint_id, author_user_id, note_type, note_text, created_at)
                VALUES (?, ?, 'coordination', ?, NOW())
            ");
            $noteText = "সহায়তা আবেদনের উত্তর ({$status}): " . $responseNotes . $allocSummary;
            $noteStmt->execute([$req['complaint_id'], $respondedByUserId, $noteText]);

            $pdo->commit();

            // Notify requesting supervisor
            if (!empty($req['requester_user_id'])) {
                try {
                    $notif = new \AmarMayor\Domain\Notifications\NotificationService();
                    $statusLabel = $status === 'approved' ? 'অনুমোদিত ও বরাদ্দকৃত' : 'পর্যালোচনা সম্পন্ন';
                    $notif->notifyUser(
                        (int)$req['requester_user_id'],
                        "সহায়তা আবেদন {$statusLabel}",
                        "Support Request Updated",
                        "অভিযোগ {$req['public_complaint_number']}-এর সহায়তা আবেদনে সরঞ্জাম বরাদ্দ দেওয়া হয়েছে: {$responseNotes}",
                        "Response for support request on {$req['public_complaint_number']}: {$responseNotes}",
                        "support_response",
                        ['complaint_id' => $req['complaint_id'], 'support_request_id' => $supportRequestId]
                    );
                } catch (\Throwable $t) {
                    // Non-blocking notification failure
                }
            }
        } catch (\Throwable $e) {
            $pdo->rollBack();
            throw $e;
        }
    }

    /**
     * Supervisor verifies whether the allocated support/machinery was actually received on the ground.
     *
     * @param int $supportRequestId
     * @param int $supervisorUserId
     * @param string $fulfillmentStatus 'received' or 'failed_delivery'
     * @param string $receiptNotes
     */
    public function verifySupportReceipt(
        int $supportRequestId,
        int $supervisorUserId,
        string $fulfillmentStatus,
        string $receiptNotes
    ): void {
        $pdo = DatabaseManager::getConnection();

        $stmt = $pdo->prepare("
            SELECT sr.*, c.public_complaint_number, p.user_id as requester_user_id,
                   d.name_bn as target_department_name_bn
            FROM support_requests sr
            INNER JOIN complaints c ON c.id = sr.complaint_id
            INNER JOIN employees e ON e.id = sr.requested_by_employee_id
            INNER JOIN persons p ON p.id = e.person_id
            LEFT JOIN departments d ON d.id = sr.target_department_id
            WHERE sr.id = ? LIMIT 1
        ");
        $stmt->execute([$supportRequestId]);
        $req = $stmt->fetch(PDO::FETCH_ASSOC);

        if (!$req) {
            throw new InvalidArgumentException("Support request #{$supportRequestId} not found.");
        }

        // Validate supervisor identity (requester or elevated supervisor)
        if ((int)$req['requester_user_id'] !== $supervisorUserId) {
            $uStmt = $pdo->prepare("SELECT user_type FROM users WHERE id = ?");
            $uStmt->execute([$supervisorUserId]);
            $uType = $uStmt->fetchColumn();
            if (!in_array($uType, ['supervisor', 'ward_officer', 'platform_super_admin', 'technical_super_admin'])) {
                throw new InvalidArgumentException("অননুমোদিত ব্যবহারকারী: শুধুমাত্র সংশ্লিষ্ট সুপারভাইজার মাঠ প্রাপ্তি যাচাই করতে পারেন।");
            }
        }

        $validStatuses = ['received', 'failed_delivery'];
        if (!in_array($fulfillmentStatus, $validStatuses, true)) {
            throw new InvalidArgumentException("অবৈধ যাচাইকরণ অবস্থা: {$fulfillmentStatus}");
        }

        $pdo->beginTransaction();
        try {
            $update = $pdo->prepare("
                UPDATE support_requests
                SET fulfillment_status = ?,
                    status = ?,
                    received_by_user_id = ?,
                    received_at = NOW(),
                    receipt_notes = ?
                WHERE id = ?
            ");
            $update->execute([
                $fulfillmentStatus,
                $fulfillmentStatus,
                $supervisorUserId,
                $receiptNotes,
                $supportRequestId
            ]);

            // Append internal note
            $statusLabelBn = $fulfillmentStatus === 'received' ? 'মাঠে গ্রহণ সম্পন্ন (Received)' : 'মাঠে পৌঁছায়নি / ডেলিভারি ব্যর্থ (Failed Delivery)';
            $noteText = "মাঠ প্রাপ্তি যাচাইকরণ ({$statusLabelBn}): {$receiptNotes} [সরঞ্জাম: " . ($req['allocated_resource'] ?? 'অনির্দিষ্ট') . ", চালক: " . ($req['allocated_operator'] ?? 'অনির্দিষ্ট') . "]";

            $noteStmt = $pdo->prepare("
                INSERT INTO internal_notes (complaint_id, author_user_id, note_type, note_text, created_at)
                VALUES (?, ?, 'coordination', ?, NOW())
            ");
            $noteStmt->execute([$req['complaint_id'], $supervisorUserId, $noteText]);

            $pdo->commit();

            // If delivery failed, notify target department head
            $notif = new \AmarMayor\Domain\Notifications\NotificationService();
            if ($fulfillmentStatus === 'failed_delivery') {
                if (!empty($req['target_department_id'])) {
                    $notif->notifyDepartmentHead(
                        (int)$req['target_department_id'],
                        "জরুরি: বরাদ্দকৃত সহায়তা মাঠে পৌঁছায়নি!",
                        "Urgent: Allocated Support Not Received on Site",
                        "অভিযোগ {$req['public_complaint_number']}-এর জন্য বরাদ্দকৃত সরঞ্জাম মাঠে পৌঁছায়নি বলে সুপারভাইজার রিপোর্ট করেছেন: {$receiptNotes}",
                        "Support not received on site for {$req['public_complaint_number']}: {$receiptNotes}",
                        "support_failed_delivery",
                        ['complaint_id' => $req['complaint_id'], 'support_request_id' => $supportRequestId]
                    );
                }
            } else {
                if (!empty($req['target_department_id'])) {
                    $notif->notifyDepartmentHead(
                        (int)$req['target_department_id'],
                        "সহায়তা সফলভাবে মাঠে গৃহীত হয়েছে",
                        "Support Received on Site",
                        "অভিযোগ {$req['public_complaint_number']}-এর বরাদ্দকৃত সরঞ্জাম মাঠে পৌঁছেছে এবং কাজ শুরু হয়েছে।",
                        "Support received on site for {$req['public_complaint_number']}",
                        "support_received",
                        ['complaint_id' => $req['complaint_id'], 'support_request_id' => $supportRequestId]
                    );
                }
            }
        } catch (\Throwable $e) {
            $pdo->rollBack();
            throw $e;
        }
    }

    /**
     * Fetch support requests targeting a department.
     */
    public function getSupportRequestsForDepartment(int $departmentId): array
    {
        $pdo = DatabaseManager::getConnection();
        $stmt = $pdo->prepare("
            SELECT sr.*, c.public_complaint_number, c.internal_status as complaint_status,
                   w.ward_number, sc.name_bn as subcategory_name_bn,
                   p.full_name_bn as requester_name_bn, p.official_phone as requester_phone
            FROM support_requests sr
            INNER JOIN complaints c ON c.id = sr.complaint_id
            INNER JOIN wards w ON w.id = c.ward_id
            INNER JOIN complaint_subcategories sc ON sc.id = c.subcategory_id
            INNER JOIN employees e ON e.id = sr.requested_by_employee_id
            INNER JOIN persons p ON p.id = e.person_id
            WHERE sr.target_department_id = ?
            ORDER BY sr.id DESC
        ");
        $stmt->execute([$departmentId]);
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }

    /**
     * Fetch support requests created by a supervisor.
     */
    public function getSupportRequestsForSupervisor(int $supervisorUserId): array
    {
        $pdo = DatabaseManager::getConnection();
        $stmt = $pdo->prepare("
            SELECT sr.*, c.public_complaint_number, d.name_bn as target_department_name_bn
            FROM support_requests sr
            INNER JOIN complaints c ON c.id = sr.complaint_id
            LEFT JOIN departments d ON d.id = sr.target_department_id
            INNER JOIN employees e ON e.id = sr.requested_by_employee_id
            INNER JOIN persons p ON p.id = e.person_id
            WHERE p.user_id = ?
            ORDER BY sr.id DESC
        ");
        $stmt->execute([$supervisorUserId]);
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }
}
