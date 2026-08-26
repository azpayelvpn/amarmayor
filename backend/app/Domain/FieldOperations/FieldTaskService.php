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
}
