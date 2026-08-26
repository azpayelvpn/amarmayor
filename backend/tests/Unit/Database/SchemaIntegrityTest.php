<?php

declare(strict_types=1);

namespace AmarMayor\Tests\Unit\Database;

use AmarMayor\Database\DatabaseManager;
use AmarMayor\Tests\TestCase;
use PDO;

class SchemaIntegrityTest extends TestCase
{
    public function testAllRequiredTablesExist(): void
    {
        $pdo = DatabaseManager::getConnection();
        $tables = $pdo->query("SHOW TABLES")->fetchAll(PDO::FETCH_COLUMN);

        $expectedTables = [
            '_migrations',
            'users',
            'roles',
            'permissions',
            'role_permissions',
            'user_roles',
            'user_scopes',
            'user_tokens',
            'cities',
            'zones',
            'wards',
            'ward_zone_history',
            'offices',
            'reserved_seats',
            'reserved_seat_wards',
            'persons',
            'representation_types',
            'representation_assignments',
            'representation_areas',
            'departments',
            'service_units',
            'employees',
            'employee_postings',
            'employee_responsibilities',
            'skills',
            'employee_skills',
            'teams',
            'team_members',
            'team_coverage',
            'complaint_categories',
            'complaint_subcategories',
            'operational_classifications',
            'priorities',
            'service_deadline_rules',
            'routing_rules',
            'complaints',
            'complaint_locations',
            'complaint_media',
            'complaint_status_history',
            'complaint_ownership_history',
            'complaint_supporters',
            'field_tasks',
            'field_task_assignments',
            'task_evidence',
            'support_requests',
            'citizen_feedback',
            'executive_attention',
            'executive_directives',
            'explanation_requests',
            'complaint_messages',
            'internal_notes',
            'office_messages',
            'city_notices',
            'citizen_pulse',
            'notifications',
            'notification_preferences',
            'audit_logs',
            'background_jobs',
            'background_job_attempts',
            'settings',
        ];

        foreach ($expectedTables as $table) {
            $this->assertTrue(in_array($table, $tables, true), "Database must contain table: {$table}");
        }
    }

    public function testCheckConstraintsPreventInvalidData(): void
    {
        $pdo = DatabaseManager::getConnection();

        // 1. Latitude range check constraint
        $failedLat = false;
        try {
            $pdo->exec("INSERT INTO complaints (public_complaint_number, citizen_user_id, category_id, subcategory_id, ward_id, zone_id, description, submitted_at) 
                        VALUES ('CHK-LAT-TEST', 1, 1, 1, 1, 1, 'Test', NOW())");
            $cId = $pdo->lastInsertId();
            $pdo->exec("INSERT INTO complaint_locations (complaint_id, latitude, longitude) VALUES ({$cId}, 150.00000000, 90.00000000)");
        } catch (\Throwable $e) {
            $failedLat = true;
        } finally {
            $pdo->exec("DELETE FROM complaints WHERE public_complaint_number = 'CHK-LAT-TEST'");
        }
        $this->assertTrue($failedLat, "Check constraint must reject latitude > 90 degrees");

        // 2. Effective date check constraint
        $failedDates = false;
        try {
            $pdo->exec("INSERT INTO user_scopes (user_id, scope_type, effective_from, effective_to) 
                        VALUES (1, 'citywide', '2026-08-26 12:00:00', '2026-08-25 12:00:00')");
        } catch (\Throwable $e) {
            $failedDates = true;
        }
        $this->assertTrue($failedDates, "Check constraint must reject effective_to < effective_from");
    }

    public function testTeamCoverageAndFieldTaskAssignmentsHistory(): void
    {
        $pdo = DatabaseManager::getConnection();
        $suffix = bin2hex(random_bytes(4));

        $deptId = 0;
        $teamId = 0;
        $covId1 = 0;
        $covId2 = 0;
        $taskId = 0;
        $ftaId1 = 0;
        $ftaId2 = 0;
        $jobId = 0;
        $bjaId1 = 0;
        $bjaId2 = 0;
        $userId = 0;
        $citizenUserId = 0;
        $operatorUserId = 0;
        $compId = 0;

        $supPersonId = 0;
        $supEmpId = 0;
        $workerPersonId = 0;
        $workerEmpId = 0;

        try {
            // 1. Team Multi-Ward Coverage
            $cityId = (int)$pdo->query("SELECT id FROM cities WHERE slug = 'mcc'")->fetchColumn();
            $pdo->exec("INSERT INTO departments (city_id, slug, name_bn, name_en, created_at) VALUES ({$cityId}, 'dept_{$suffix}', 'বিভাগ', 'Dept', NOW())");
            $deptId = (int)$pdo->lastInsertId();

            $pdo->exec("INSERT INTO teams (department_id, name_bn, name_en, created_at) VALUES ({$deptId}, 'দল ক', 'Team A', NOW())");
            $teamId = (int)$pdo->lastInsertId();

            $ward1 = (int)$pdo->query("SELECT id FROM wards WHERE ward_number = 1")->fetchColumn();
            $ward2 = (int)$pdo->query("SELECT id FROM wards WHERE ward_number = 2")->fetchColumn();

            $pdo->exec("INSERT INTO team_coverage (team_id, area_type, area_id, effective_from, created_at) VALUES ({$teamId}, 'ward', {$ward1}, NOW(), NOW())");
            $covId1 = (int)$pdo->lastInsertId();
            $pdo->exec("INSERT INTO team_coverage (team_id, area_type, area_id, effective_from, created_at) VALUES ({$teamId}, 'ward', {$ward2}, NOW(), NOW())");
            $covId2 = (int)$pdo->lastInsertId();

            $covCount = (int)$pdo->query("SELECT COUNT(*) FROM team_coverage WHERE team_id = {$teamId}")->fetchColumn();
            $this->assertEquals(2, $covCount, "Team can cover multiple wards simultaneously");

            // 2. Complaint Creator vs Citizen Complainant
            $uUuid1 = \AmarMayor\Support\Security::uuid();
            $pdo->exec("INSERT INTO users (uuid, email, user_type, status, created_at) VALUES ('{$uUuid1}', 'citizen_{$suffix}@example.com', 'citizen', 'active', NOW())");
            $citizenUserId = (int)$pdo->lastInsertId();

            $uUuid2 = \AmarMayor\Support\Security::uuid();
            $pdo->exec("INSERT INTO users (uuid, email, user_type, status, created_at) VALUES ('{$uUuid2}', 'operator_{$suffix}@example.com', 'staff', 'active', NOW())");
            $operatorUserId = (int)$pdo->lastInsertId();

            $pdo->exec("INSERT INTO complaints (public_complaint_number, citizen_user_id, created_by_user_id, category_id, subcategory_id, ward_id, zone_id, description, submitted_at, created_at) 
                        VALUES ('COMP-CREATOR-{$suffix}', {$citizenUserId}, {$operatorUserId}, 1, 1, 1, 1, 'Call center entry', NOW(), NOW())");
            $compId = (int)$pdo->lastInsertId();

            $compRow = $pdo->query("SELECT citizen_user_id, created_by_user_id FROM complaints WHERE id = {$compId}")->fetch(PDO::FETCH_ASSOC);
            $this->assertEquals($citizenUserId, (int)$compRow['citizen_user_id']);
            $this->assertEquals($operatorUserId, (int)$compRow['created_by_user_id'], "Distinguishes between citizen complainant and call-center creator");

            // 3. Field Task Assignment History (Reassignment)
            $pdo->exec("INSERT INTO persons (full_name_bn, full_name_en, created_at) VALUES ('সুপারভাইজার', 'Supervisor', NOW())");
            $supPersonId = (int)$pdo->lastInsertId();
            $pdo->exec("INSERT INTO employees (person_id, employee_code, designation_bn, designation_en, created_at) VALUES ({$supPersonId}, 'SUP-CODE-{$suffix}', 'সুপারভাইজার', 'Supervisor', NOW())");
            $supEmpId = (int)$pdo->lastInsertId();

            $pdo->exec("INSERT INTO persons (full_name_bn, full_name_en, created_at) VALUES ('কর্মী', 'Worker', NOW())");
            $workerPersonId = (int)$pdo->lastInsertId();
            $pdo->exec("INSERT INTO employees (person_id, employee_code, designation_bn, designation_en, created_at) VALUES ({$workerPersonId}, 'WORK-CODE-{$suffix}', 'কর্মী', 'Worker', NOW())");
            $workerEmpId = (int)$pdo->lastInsertId();

            $taskCode = "TASK-HIST-{$suffix}";
            $pdo->exec("INSERT INTO field_tasks (complaint_id, task_code, supervisor_employee_id, task_status, created_at) VALUES ({$compId}, '{$taskCode}', {$supEmpId}, 'pending', NOW())");
            $taskId = (int)$pdo->lastInsertId();

            // Initial assignment to Team A
            $pdo->exec("INSERT INTO field_task_assignments (field_task_id, assigned_team_id, effective_from, effective_to, assignment_notes, created_at) 
                        VALUES ({$taskId}, {$teamId}, '2026-08-26 10:00:00', '2026-08-26 12:00:00', 'Initial team dispatch', NOW())");
            $ftaId1 = (int)$pdo->lastInsertId();

            // Reassignment to Worker 1
            $pdo->exec("INSERT INTO field_task_assignments (field_task_id, assigned_worker_employee_id, effective_from, effective_to, assignment_notes, created_at) 
                        VALUES ({$taskId}, {$workerEmpId}, '2026-08-26 12:00:00', NULL, 'Individual specialist assigned', NOW())");
            $ftaId2 = (int)$pdo->lastInsertId();

            $ftaCount = (int)$pdo->query("SELECT COUNT(*) FROM field_task_assignments WHERE field_task_id = {$taskId}")->fetchColumn();
            $this->assertEquals(2, $ftaCount, "Field task assignment history preserves previous and current assignments");

            // 4. Background Job Attempts Log
            $pdo->exec("INSERT INTO background_jobs (job_handler, payload, available_at, created_at) VALUES ('SendSmsNotification', '{\"phone\":\"01700000000\"}', NOW(), NOW())");
            $jobId = (int)$pdo->lastInsertId();

            $pdo->exec("INSERT INTO background_job_attempts (job_id, attempt_number, started_at, finished_at, status, error_message, created_at) 
                        VALUES ({$jobId}, 1, NOW(), NOW(), 'failed', 'SMS Gateway Timeout', NOW())");
            $bjaId1 = (int)$pdo->lastInsertId();

            $pdo->exec("INSERT INTO background_job_attempts (job_id, attempt_number, started_at, finished_at, status, result_summary, created_at) 
                        VALUES ({$jobId}, 2, NOW(), NOW(), 'completed', 'Delivered via Fallback Gateway', NOW())");
            $bjaId2 = (int)$pdo->lastInsertId();

            $attemptCount = (int)$pdo->query("SELECT COUNT(*) FROM background_job_attempts WHERE job_id = {$jobId}")->fetchColumn();
            $this->assertEquals(2, $attemptCount, "Background job attempts history tracks individual retries");
        } finally {
            if ($bjaId1 || $bjaId2) $pdo->exec("DELETE FROM background_job_attempts WHERE job_id = {$jobId}");
            if ($jobId) $pdo->exec("DELETE FROM background_jobs WHERE id = {$jobId}");
            if ($ftaId1 || $ftaId2) $pdo->exec("DELETE FROM field_task_assignments WHERE field_task_id = {$taskId}");
            if ($taskId) $pdo->exec("DELETE FROM field_tasks WHERE id = {$taskId}");
            if ($supEmpId) $pdo->exec("DELETE FROM employees WHERE id = {$supEmpId}");
            if ($workerEmpId) $pdo->exec("DELETE FROM employees WHERE id = {$workerEmpId}");
            if ($supPersonId) $pdo->exec("DELETE FROM persons WHERE id = {$supPersonId}");
            if ($workerPersonId) $pdo->exec("DELETE FROM persons WHERE id = {$workerPersonId}");
            if ($compId) $pdo->exec("DELETE FROM complaints WHERE id = {$compId}");
            if ($operatorUserId) $pdo->exec("DELETE FROM users WHERE id = {$operatorUserId}");
            if ($citizenUserId) $pdo->exec("DELETE FROM users WHERE id = {$citizenUserId}");
            if ($covId1 || $covId2) $pdo->exec("DELETE FROM team_coverage WHERE team_id = {$teamId}");
            if ($teamId) $pdo->exec("DELETE FROM teams WHERE id = {$teamId}");
            if ($deptId) $pdo->exec("DELETE FROM departments WHERE id = {$deptId}");
        }
    }

    public function testCivicEvidenceAndHistoryProtectedAgainstCascadeDeletion(): void
    {
        $pdo = DatabaseManager::getConnection();
        $suffix = bin2hex(random_bytes(4));

        $userId = 0;
        $personId = 0;
        $empId = 0;
        $compId = 0;
        $mediaId = 0;
        $taskId = 0;
        $ftaId = 0;
        $evidenceId = 0;

        try {
            // 1. Setup User and Employee
            $uUuid = \AmarMayor\Support\Security::uuid();
            $pdo->exec("INSERT INTO users (uuid, email, user_type, status, created_at) VALUES ('{$uUuid}', 'citizen_ret_{$suffix}@example.com', 'citizen', 'active', NOW())");
            $userId = (int)$pdo->lastInsertId();

            $pdo->exec("INSERT INTO persons (full_name_bn, full_name_en, created_at) VALUES ('তদারককারী', 'Supervisor', NOW())");
            $personId = (int)$pdo->lastInsertId();

            $pdo->exec("INSERT INTO employees (person_id, employee_code, designation_bn, designation_en, created_at) VALUES ({$personId}, 'EMP-RET-{$suffix}', 'তদারককারী', 'Supervisor', NOW())");
            $empId = (int)$pdo->lastInsertId();

            // 2. Setup Complaint
            $pdo->exec("INSERT INTO complaints (public_complaint_number, citizen_user_id, category_id, subcategory_id, ward_id, zone_id, description, submitted_at, created_at) 
                        VALUES ('COMP-RET-{$suffix}', {$userId}, 1, 1, 1, 1, 'Evidence protection test', NOW(), NOW())");
            $compId = (int)$pdo->lastInsertId();

            // 3. Attach Complaint Media (Evidence)
            $pdo->exec("INSERT INTO complaint_media (complaint_id, uploader_user_id, media_type, original_file_path, mime_type, file_size_bytes, created_at) 
                        VALUES ({$compId}, {$userId}, 'image', '/storage/evidence/orig_{$suffix}.jpg', 'image/jpeg', 1048576, NOW())");
            $mediaId = (int)$pdo->lastInsertId();

            // VERIFICATION 1: Deleting complaint MUST be blocked by RESTRICT on complaint_media
            $complaintDeleteBlocked = false;
            try {
                $pdo->exec("DELETE FROM complaints WHERE id = {$compId}");
            } catch (\PDOException $e) {
                // SQLSTATE[23000]: 1451 Cannot delete or update a parent row: a foreign key constraint fails
                $complaintDeleteBlocked = true;
            }
            $this->assertTrue($complaintDeleteBlocked, "Parent complaint deletion must be blocked when complaint_media evidence exists (ON DELETE RESTRICT)");

            // 4. Setup Field Task and Field Task Assignment
            $pdo->exec("INSERT INTO field_tasks (complaint_id, task_code, supervisor_employee_id, task_status, created_at) 
                        VALUES ({$compId}, 'TASK-RET-{$suffix}', {$empId}, 'pending', NOW())");
            $taskId = (int)$pdo->lastInsertId();

            $pdo->exec("INSERT INTO field_task_assignments (field_task_id, assigned_worker_employee_id, effective_from, assignment_notes, created_at) 
                        VALUES ({$taskId}, {$empId}, NOW(), 'Assignment for test', NOW())");
            $ftaId = (int)$pdo->lastInsertId();

            // VERIFICATION 2: Deleting field task MUST be blocked by RESTRICT on field_task_assignments
            $taskDeleteBlockedByAssignment = false;
            try {
                $pdo->exec("DELETE FROM field_tasks WHERE id = {$taskId}");
            } catch (\PDOException $e) {
                $taskDeleteBlockedByAssignment = true;
            }
            $this->assertTrue($taskDeleteBlockedByAssignment, "Task deletion must be blocked when assignment history exists (ON DELETE RESTRICT)");

            // 5. Attach Task Evidence
            $pdo->exec("INSERT INTO task_evidence (field_task_id, media_id, evidence_stage, server_timestamp) 
                        VALUES ({$taskId}, {$mediaId}, 'before_work', NOW())");
            $evidenceId = (int)$pdo->lastInsertId();

            // VERIFICATION 3: Deleting field task MUST be blocked by RESTRICT on task_evidence
            $pdo->exec("DELETE FROM field_task_assignments WHERE id = {$ftaId}");
            $ftaId = 0; // Assignment removed, but task_evidence remains

            $taskDeleteBlockedByEvidence = false;
            try {
                $pdo->exec("DELETE FROM field_tasks WHERE id = {$taskId}");
            } catch (\PDOException $e) {
                $taskDeleteBlockedByEvidence = true;
            }
            $this->assertTrue($taskDeleteBlockedByEvidence, "Task deletion must be blocked when task_evidence exists (ON DELETE RESTRICT)");

            // VERIFICATION 4: Deleting complaint media MUST be blocked when referenced in task_evidence
            $mediaDeleteBlockedByEvidence = false;
            try {
                $pdo->exec("DELETE FROM complaint_media WHERE id = {$mediaId}");
            } catch (\PDOException $e) {
                $mediaDeleteBlockedByEvidence = true;
            }
            $this->assertTrue($mediaDeleteBlockedByEvidence, "Complaint media deletion must be blocked when referenced as task evidence (ON DELETE RESTRICT)");

        } finally {
            // Clean up in reverse dependency order
            if ($evidenceId) $pdo->exec("DELETE FROM task_evidence WHERE id = {$evidenceId}");
            if ($ftaId) $pdo->exec("DELETE FROM field_task_assignments WHERE id = {$ftaId}");
            if ($taskId) $pdo->exec("DELETE FROM field_tasks WHERE id = {$taskId}");
            if ($mediaId) $pdo->exec("DELETE FROM complaint_media WHERE id = {$mediaId}");
            if ($compId) $pdo->exec("DELETE FROM complaints WHERE id = {$compId}");
            if ($empId) $pdo->exec("DELETE FROM employees WHERE id = {$empId}");
            if ($personId) $pdo->exec("DELETE FROM persons WHERE id = {$personId}");
            if ($userId) $pdo->exec("DELETE FROM users WHERE id = {$userId}");
        }
    }
}
