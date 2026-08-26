<?php

declare(strict_types=1);

namespace AmarMayor\Tests\Unit\Domain;

use AmarMayor\Database\DatabaseManager;
use AmarMayor\Domain\ComplaintCore\ComplaintService;
use AmarMayor\Domain\FieldOperations\FieldTaskService;
use AmarMayor\Tests\TestCase;
use PDO;

class FieldOperationsTest extends TestCase
{
    private ComplaintService $complaintService;
    private FieldTaskService $taskService;

    public function setUp(): void
    {
        parent::setUp();
        $this->complaintService = new ComplaintService();
        $this->taskService = new FieldTaskService();
    }

    public function testFieldTaskLifecycleAndEvidenceSubmission(): void
    {
        $pdo = DatabaseManager::getConnection();

        // 1. Create citizen and worker
        $stmt = $pdo->prepare("INSERT INTO users (uuid, phone, user_type, status, preferred_language, created_at) VALUES ('test-citizen-f1', '+8801711000003', 'citizen', 'active', 'bn', NOW())");
        $stmt->execute();
        $citizenUserId = (int)$pdo->lastInsertId();

        $pStmt = $pdo->prepare("INSERT INTO persons (full_name_bn, full_name_en, created_at) VALUES ('মাঠকর্মী ১', 'Worker 1', NOW())");
        $pStmt->execute();
        $personId = (int)$pdo->lastInsertId();

        $eStmt = $pdo->prepare("INSERT INTO employees (person_id, employee_code, designation_bn, designation_en, created_at) VALUES (?, 'WRK-TEST-01', 'পরিচ্ছন্নতাকর্মী', 'Cleaner', NOW())");
        $eStmt->execute([$personId]);
        $workerEmpId = (int)$pdo->lastInsertId();

        $pSupStmt = $pdo->prepare("INSERT INTO persons (full_name_bn, full_name_en, created_at) VALUES ('সুপারভাইজার টেস্ট', 'Supervisor Test', NOW())");
        $pSupStmt->execute();
        $personSupId = (int)$pdo->lastInsertId();

        $eSupStmt = $pdo->prepare("INSERT INTO employees (person_id, employee_code, designation_bn, designation_en, created_at) VALUES (?, 'SUP-TEST-F1', 'সুপারভাইজার', 'Supervisor', NOW())");
        $eSupStmt->execute([$personSupId]);
        $supervisorEmpId = (int)$pdo->lastInsertId();

        $catId = (int)$pdo->query("SELECT id FROM complaint_categories WHERE slug = 'cleanliness'")->fetchColumn();
        $subId = (int)$pdo->query("SELECT id FROM complaint_subcategories WHERE slug = 'garbage_pile'")->fetchColumn();
        $wardId = (int)$pdo->query("SELECT id FROM wards WHERE ward_number = 1")->fetchColumn();

        $complaintId = 0;
        $taskId = 0;
        try {
            // 2. Submit complaint
            $complaint = $this->complaintService->createComplaint([
                'citizen_user_id' => $citizenUserId,
                'category_id' => $catId,
                'subcategory_id' => $subId,
                'ward_id' => $wardId,
                'description' => 'রাস্তার পাশে ময়লার স্তূপ অপসারণ প্রয়োজন।',
            ]);
            $complaintId = (int)$complaint['id'];

            // 3. Supervisor creates field task
            $taskId = $this->taskService->createTask(
                $complaintId,
                $supervisorEmpId,
                $workerEmpId,
                null,
                'আজকের মধ্যে ময়লা অপসারণ করে ছবি আপলোড করুন।'
            );
            $this->assert($taskId > 0);

            // 4. Worker starts task
            $this->taskService->startTask($taskId, $workerEmpId);

            $cAfterStart = $this->complaintService->getComplaintById($complaintId);
            $this->assertEquals('in_progress', $cAfterStart['internal_status']);
            $this->assertEquals('in_progress', $cAfterStart['citizen_status']);

            // 5. Worker uploads after-work evidence photo
            $mediaId = $this->taskService->addTaskEvidence(
                $taskId,
                $citizenUserId,
                '/uploads/tasks/after_cleaning_01.jpg',
                'after_work',
                'image',
                24.747,
                90.420
            );
            $this->assert($mediaId > 0);

            // 6. Worker completes task
            $this->taskService->completeTask($taskId, $workerEmpId, 'সম্পূর্ণ এলাকা পরিষ্কার করা হয়েছে।');

            // 7. Verify Task is completed, but Complaint is 'work_completed' (NOT closed/resolved yet!)
            $cAfterWork = $this->complaintService->getComplaintById($complaintId);
            $this->assertEquals('work_completed', $cAfterWork['internal_status']);
            $this->assertEquals('work_completed', $cAfterWork['citizen_status']);
            $this->assertNull($cAfterWork['closed_at'], "Complaint must NOT be closed upon worker completion");

            $tasks = $this->taskService->getTasksForComplaint($complaintId);
            $this->assertCount(1, $tasks);
            $this->assertEquals('completed', $tasks[0]['task_status']);
            $this->assertCount(1, $tasks[0]['evidence']);
        } finally {
            if ($taskId) {
                $pdo->exec("DELETE FROM task_evidence WHERE field_task_id = {$taskId}");
                $pdo->exec("DELETE FROM field_task_assignments WHERE field_task_id = {$taskId}");
                $pdo->exec("DELETE FROM field_tasks WHERE id = {$taskId}");
            }
            if ($complaintId) {
                $pdo->exec("DELETE FROM complaint_media WHERE complaint_id = {$complaintId}");
                $pdo->exec("DELETE FROM complaint_locations WHERE complaint_id = {$complaintId}");
                $pdo->exec("DELETE FROM complaint_status_history WHERE complaint_id = {$complaintId}");
                $pdo->exec("DELETE FROM complaints WHERE id = {$complaintId}");
            }
            $pdo->exec("DELETE FROM employees WHERE id IN ({$workerEmpId}, {$supervisorEmpId})");
            $pdo->exec("DELETE FROM persons WHERE id IN ({$personId}, {$personSupId})");
            $pdo->exec("DELETE FROM users WHERE id = {$citizenUserId}");
        }
    }
}
