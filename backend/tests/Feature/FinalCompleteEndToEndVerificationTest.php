<?php

declare(strict_types=1);

namespace AmarMayor\Tests\Feature;

use AmarMayor\Database\DatabaseManager;
use AmarMayor\Domain\Background\BackgroundJobService;
use AmarMayor\Domain\CommandCenter\CommandCenterService;
use AmarMayor\Domain\Communication\CommunicationService;
use AmarMayor\Domain\ComplaintCore\ComplaintService;
use AmarMayor\Domain\ComplaintRouting\ComplaintRoutingEngine;
use AmarMayor\Domain\ExecutiveAttention\ExecutiveAttentionService;
use AmarMayor\Domain\FieldOperations\FieldTaskService;
use AmarMayor\Domain\Notifications\NotificationService;
use AmarMayor\Domain\PlatformAdmin\PlatformAdminService;
use AmarMayor\Domain\PublicAccountability\PublicAccountabilityService;
use AmarMayor\Domain\ResolutionQuality\ResolutionService;
use AmarMayor\Domain\TechnicalAdmin\SystemHealthService;
use AmarMayor\Support\Security;
use AmarMayor\Tests\TestCase;
use PDO;

class FinalCompleteEndToEndVerificationTest extends TestCase
{
    public function testCompleteSystemLifecycleFromSubmissionToResolutionAndExecutiveOversight(): void
    {
        $pdo = DatabaseManager::getConnection();

        // 1. Initialize Services
        $complaintService = new ComplaintService();
        $routingEngine = new ComplaintRoutingEngine();
        $taskService = new FieldTaskService();
        $resolutionService = new ResolutionService();
        $executiveService = new ExecutiveAttentionService();
        $commandCenterService = new CommandCenterService();
        $communicationService = new CommunicationService();
        $publicService = new PublicAccountabilityService();
        $platformAdminService = new PlatformAdminService();
        $systemHealthService = new SystemHealthService();
        $jobService = new BackgroundJobService();
        $notificationService = new NotificationService();

        // 2. Setup Test Citizen & Supervisor & Mayor
        $stmtC = $pdo->prepare("INSERT INTO users (uuid, phone, user_type, status, preferred_language, created_at) VALUES (?, '+8801755000001', 'citizen', 'active', 'bn', NOW())");
        $stmtC->execute([Security::uuid()]);
        $citizenUserId = (int)$pdo->lastInsertId();

        $stmtM = $pdo->prepare("INSERT INTO users (uuid, phone, user_type, status, preferred_language, created_at) VALUES (?, '+8801755000002', 'admin', 'active', 'bn', NOW())");
        $stmtM->execute([Security::uuid()]);
        $mayorUserId = (int)$pdo->lastInsertId();

        $pStmt = $pdo->prepare("INSERT INTO persons (full_name_bn, full_name_en, created_at) VALUES ('সুপারভাইজার টেস্ট', 'Supervisor Test', NOW())");
        $pStmt->execute();
        $personId = (int)$pdo->lastInsertId();

        $eStmt = $pdo->prepare("INSERT INTO employees (person_id, employee_code, designation_bn, designation_en, created_at) VALUES (?, 'SUP-E2E-01', 'ওয়ার্ড সুপারভাইজার', 'Ward Supervisor', NOW())");
        $eStmt->execute([$personId]);
        $supervisorEmpId = (int)$pdo->lastInsertId();

        $catId = (int)$pdo->query("SELECT id FROM complaint_categories WHERE slug = 'cleanliness'")->fetchColumn();
        $subId = (int)$pdo->query("SELECT id FROM complaint_subcategories WHERE slug = 'garbage_pile'")->fetchColumn();
        $wardId = (int)$pdo->query("SELECT id FROM wards WHERE ward_number = 1")->fetchColumn();
        $deptId = (int)$pdo->query("SELECT id FROM departments WHERE slug = 'waste_management'")->fetchColumn();

        $complaintId = 0;
        $taskId = 0;
        $ruleId = 0;
        try {
            $rStmt = $pdo->prepare("
                INSERT INTO routing_rules (category_id, subcategory_id, ward_id, department_id, assigned_supervisor_employee_id, is_active, effective_from, created_at)
                VALUES (?, ?, ?, ?, ?, 1, NOW(), NOW())
            ");
            $rStmt->execute([$catId, $subId, $wardId, $deptId, $supervisorEmpId]);
            $ruleId = (int)$pdo->lastInsertId();

            // STEP A: Citizen Submits Complaint
            $complaint = $complaintService->createComplaint([
                'citizen_user_id' => $citizenUserId,
                'category_id' => $catId,
                'subcategory_id' => $subId,
                'ward_id' => $wardId,
                'description' => 'রাস্তার মোড়ে ময়লার স্তূপ জমে দুর্গন্ধ ছড়াচ্ছে।',
                'landmark' => 'ছোট বাজার গোলচত্বর',
            ]);
            $complaintId = (int)$complaint['id'];
            $this->assert($complaintId > 0);
            $this->assertEquals('submitted', $complaint['status_history'][0]['to_internal_status']);

            // STEP B: Deterministic Routing Engine Assigns Complaint
            $routed = $routingEngine->routeComplaint($complaintId, $citizenUserId);
            $this->assertTrue($routed);
            $cAfterRoute = $complaintService->getComplaintById($complaintId);
            $this->assertEquals('assigned', $cAfterRoute['internal_status']);

            // STEP C: Supervisor Creates & Worker Executes Field Task
            $taskId = $taskService->createTask($complaintId, $supervisorEmpId, null, null, 'বর্জ্য অপসারণ করুন');
            $this->assert($taskId > 0);

            $taskService->startTask($taskId, $supervisorEmpId);
            $taskService->completeTask($taskId, $supervisorEmpId, 'বর্জ্য অপসারণ সম্পন্ন হয়েছে');

            $cAfterWork = $complaintService->getComplaintById($complaintId);
            $this->assertEquals('work_completed', $cAfterWork['internal_status']);
            $this->assertEquals(1, (int)$cAfterWork['completion_attempts']);

            // STEP D: Supervisor Verifies Work
            $resolutionService->supervisorVerify($complaintId, $supervisorEmpId, true, 'সরজমিনে পরিষ্কার দেখা গেছে');
            $cAfterVerify = $complaintService->getComplaintById($complaintId);
            $this->assertEquals('awaiting_citizen_confirmation', $cAfterVerify['internal_status']);

            // STEP E: Citizen Reopens ("Not Resolved / Needs More Work")
            $resolutionService->citizenConfirm($complaintId, $citizenUserId, false, null, 'কিছু ময়লা এখনো পড়ে আছে।', 'incomplete_work');
            $cAfterReopen = $complaintService->getComplaintById($complaintId);
            $this->assertEquals('needs_more_work', $cAfterReopen['internal_status']);
            $this->assertEquals(1, (int)$cAfterReopen['reopen_count']);
            $this->assertEquals(1, (int)$cAfterReopen['completion_attempts'], 'completion_attempts must NOT double increment');

            // STEP F: Mayor Reviews Attention Queue & Issues Directive
            $queue = $executiveService->getAttentionQueue('citizen_reopen');
            $this->assert(!empty($queue), "Attention queue must contain reopened complaint");

            $directiveId = $executiveService->issueDirective(
                $mayorUserId,
                $complaintId,
                'urgent_action',
                'আজ বিকেলের মধ্যে অবশিষ্ট বর্জ্য অপসারণ করে ছবি জমা দিন।'
            );
            $this->assert($directiveId > 0);

            // STEP G: Worker Re-Completes Work & Citizen Confirms Satisfaction
            $taskService->completeTask($taskId, $supervisorEmpId, 'সম্পূর্ণ এলাকা ধুয়ে-মুছে পরিষ্কার সম্পন্ন');
            $resolutionService->supervisorVerify($complaintId, $supervisorEmpId, true, 'দ্বিতীয়বার চূড়ান্ত পরিদর্শন সফল');

            $resolutionService->citizenConfirm($complaintId, $citizenUserId, true, 5, 'অসাধারণ কাজ হয়েছে, ধন্যবাদ!');

            $cFinal = $complaintService->getComplaintById($complaintId);
            $this->assertEquals('closed', $cFinal['internal_status']);
            $this->assertEquals('resolved', $cFinal['citizen_status']);
            $this->assertNotNull($cFinal['closed_at']);

            // STEP H: Verify Executive KPIs & Public Metrics Updated
            $kpis = $commandCenterService->getExecutiveKpis();
            $this->assert($kpis['total_complaints'] >= 1);
            $this->assert($kpis['citizen_satisfaction_percent'] >= 70.0);

            $pubMetrics = $publicService->getPublicMetrics();
            $this->assert($pubMetrics['citizen_confirmed_resolved'] >= 1);

            // STEP I: Verify Background Job Processing & Notification
            $jobId = $jobService->dispatch('scan_overdue_deadlines', []);
            $processed = $jobService->processPendingJobs(10);
            $this->assert($processed >= 1);

            $notifId = $notificationService->notifyUser($citizenUserId, 'ধন্যবাদ', 'Thank You', 'আপনার সন্তুষ্টি লিপিবদ্ধ হয়েছে।', 'Your feedback has been recorded.');
            $this->assert($notifId > 0);

            // STEP J: Verify Technical Admin System Health
            $health = $systemHealthService->getSystemHealth(true);
            $this->assertEquals('healthy', $health['overall_status']);
        } finally {
            if ($ruleId) {
                $pdo->exec("DELETE FROM routing_rules WHERE id = {$ruleId}");
            }
            if ($taskId) {
                $pdo->exec("DELETE FROM task_evidence WHERE field_task_id = {$taskId}");
                $pdo->exec("DELETE FROM field_task_assignments WHERE field_task_id = {$taskId}");
                $pdo->exec("DELETE FROM field_tasks WHERE id = {$taskId}");
            }
            if ($complaintId) {
                $pdo->exec("DELETE FROM executive_directives WHERE complaint_id = {$complaintId}");
                $pdo->exec("DELETE FROM executive_attention WHERE complaint_id = {$complaintId}");
                $pdo->exec("DELETE FROM citizen_feedback WHERE complaint_id = {$complaintId}");
                $pdo->exec("DELETE FROM complaint_ownership_history WHERE complaint_id = {$complaintId}");
                $pdo->exec("DELETE FROM complaint_status_history WHERE complaint_id = {$complaintId}");
                $pdo->exec("DELETE FROM complaint_locations WHERE complaint_id = {$complaintId}");
                $pdo->exec("DELETE FROM complaints WHERE id = {$complaintId}");
            }
            $pdo->exec("DELETE FROM notifications WHERE user_id = {$citizenUserId}");
            $pdo->exec("DELETE FROM employees WHERE id = {$supervisorEmpId}");
            $pdo->exec("DELETE FROM persons WHERE id = {$personId}");
            $pdo->exec("DELETE FROM users WHERE id IN ({$citizenUserId}, {$mayorUserId})");
        }
    }
}
