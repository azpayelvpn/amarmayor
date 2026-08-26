<?php

declare(strict_types=1);

namespace AmarMayor\Tests\Feature;

use AmarMayor\Auth\Auth;
use AmarMayor\Auth\User;
use AmarMayor\Database\DatabaseManager;
use AmarMayor\Http\Controllers\ComplaintController;
use AmarMayor\Http\Controllers\ExecutiveController;
use AmarMayor\Http\Request;
use AmarMayor\Support\Security;
use AmarMayor\Tests\TestCase;
use PDO;

class MilestoneBEndToEndFlowTest extends TestCase
{
    private ComplaintController $complaintController;
    private ExecutiveController $executiveController;

    public function setUp(): void
    {
        parent::setUp();
        $this->complaintController = new ComplaintController();
        $this->executiveController = new ExecutiveController();
    }

    public function testCompleteMilestoneBLifecycleEndToEnd(): void
    {
        $pdo = DatabaseManager::getConnection();

        $cPhone = '+88017' . sprintf('%08d', random_int(10000000, 99999999));
        $mPhone = '+88017' . sprintf('%08d', random_int(10000000, 99999999));

        // 1. Setup Test Users
        $cStmt = $pdo->prepare("INSERT INTO users (uuid, phone, user_type, status, preferred_language, created_at) VALUES (?, ?, 'citizen', 'active', 'bn', NOW())");
        $cStmt->execute([Security::uuid(), $cPhone]);
        $citizenUserId = (int)$pdo->lastInsertId();

        $mStmt = $pdo->prepare("INSERT INTO users (uuid, phone, user_type, status, preferred_language, created_at) VALUES (?, ?, 'admin', 'active', 'bn', NOW())");
        $mStmt->execute([Security::uuid(), $mPhone]);
        $mayorUserId = (int)$pdo->lastInsertId();

        // 2. Setup Supervisor & Worker Employees
        $pSupStmt = $pdo->prepare("INSERT INTO persons (full_name_bn, full_name_en, created_at) VALUES ('ইটুই সুপারভাইজার', 'E2E Supervisor', NOW())");
        $pSupStmt->execute();
        $personSupId = (int)$pdo->lastInsertId();

        $eSupStmt = $pdo->prepare("INSERT INTO employees (person_id, employee_code, designation_bn, designation_en, created_at) VALUES (?, ?, 'সুপারভাইজার', 'Supervisor', NOW())");
        $eSupStmt->execute([$personSupId, 'SUP-E2E-' . random_int(1000, 9999)]);
        $supervisorEmpId = (int)$pdo->lastInsertId();

        $pWrkStmt = $pdo->prepare("INSERT INTO persons (full_name_bn, full_name_en, created_at) VALUES ('ইটুই কর্মী', 'E2E Worker', NOW())");
        $pWrkStmt->execute();
        $personWrkId = (int)$pdo->lastInsertId();

        $eWrkStmt = $pdo->prepare("INSERT INTO employees (person_id, employee_code, designation_bn, designation_en, created_at) VALUES (?, ?, 'মাঠকর্মী', 'Field Worker', NOW())");
        $eWrkStmt->execute([$personWrkId, 'WRK-E2E-' . random_int(1000, 9999)]);
        $workerEmpId = (int)$pdo->lastInsertId();

        $catId = (int)$pdo->query("SELECT id FROM complaint_categories WHERE slug = 'cleanliness'")->fetchColumn();
        $subId = (int)$pdo->query("SELECT id FROM complaint_subcategories WHERE slug = 'garbage_pile'")->fetchColumn();
        $wardId = (int)$pdo->query("SELECT id FROM wards WHERE ward_number = 1")->fetchColumn();

        $complaintId = 0;
        $taskId = 0;
        try {
            // STEP 1: Citizen Submits Complaint via API
            $citizenUser = User::findById($citizenUserId);
            Auth::setUser($citizenUser);

            $submitReq = new Request('POST', '/api/v1/complaints', [], [
                'category_id' => $catId,
                'subcategory_id' => $subId,
                'ward_id' => $wardId,
                'description' => 'ই-টু-ই টেস্ট ময়লা জমে আছে।',
                'approximate_address' => 'জিল রোড, ময়মনসিংহ',
                'landmark' => 'জিল রোড মোড়',
            ]);

            $submitResp = $this->complaintController->submit($submitReq);
            $this->assertEquals(201, $submitResp->getStatusCode());

            $sDecoded = json_decode($submitResp->getBody(), true);
            $cData = $sDecoded['data'] ?? $sDecoded;
            $complaintId = (int)$cData['id'];
            $trackingNumber = $cData['public_complaint_number'];
            $this->assert($complaintId > 0);

            // STEP 2: Public Tracking via API
            Auth::setUser(null); // Unauthenticated public check
            $trackReq = new Request('GET', "/api/v1/complaints/track/{$trackingNumber}");
            $trackResp = $this->complaintController->track($trackReq, $trackingNumber);
            $this->assertEquals(200, $trackResp->getStatusCode());
            $tDecoded = json_decode($trackResp->getBody(), true);
            $trackData = $tDecoded['data'] ?? $tDecoded;
            $this->assertEquals('জিল রোড মোড়', $trackData['public_safe_address']);

            // STEP 3: Supervisor Dispatches Field Task via API
            $supReq = new Request('POST', "/api/v1/complaints/{$complaintId}/tasks", [], [
                'supervisor_employee_id' => $supervisorEmpId,
                'worker_employee_id' => $workerEmpId,
                'instructions' => 'দ্রুত কাজ করুন',
            ]);
            $supResp = $this->complaintController->createTask($supReq, (string)$complaintId);
            $this->assertEquals(201, $supResp->getStatusCode());
            $taskDecoded = json_decode($supResp->getBody(), true);
            $taskData = $taskDecoded['data'] ?? $taskDecoded;
            $taskId = (int)$taskData['task_id'];

            // STEP 4: Worker Starts Task
            $startReq = new Request('POST', "/api/v1/tasks/{$taskId}/start", [], ['worker_employee_id' => $workerEmpId]);
            $startResp = $this->complaintController->startTask($startReq, (string)$taskId);
            $this->assertEquals(200, $startResp->getStatusCode());

            // STEP 5: Worker Completes Task
            $compReq = new Request('POST', "/api/v1/tasks/{$taskId}/complete", [], ['worker_employee_id' => $workerEmpId, 'notes' => 'কাজ শেষ']);
            $compResp = $this->complaintController->completeTask($compReq, (string)$taskId);
            $this->assertEquals(200, $compResp->getStatusCode());

            // STEP 6: Supervisor Verifies Work
            $verReq = new Request('POST', "/api/v1/complaints/{$complaintId}/verify", [], [
                'is_satisfactory' => true,
                'notes' => 'যাচাই সম্পন্ন'
            ]);
            $verResp = $this->complaintController->verify($verReq, (string)$complaintId);
            $this->assertEquals(200, $verResp->getStatusCode());

            // STEP 7: Citizen Reopens ("Not Resolved")
            Auth::setUser($citizenUser);
            $reopenReq = new Request('POST', "/api/v1/complaints/{$complaintId}/confirm", [], [
                'is_resolved' => false,
                'comment' => 'এখনো কিছু বাকি আছে',
                'unresolved_reason' => 'incomplete_work'
            ]);
            $reopenResp = $this->complaintController->confirm($reopenReq, (string)$complaintId);
            $this->assertEquals(200, $reopenResp->getStatusCode());

            // STEP 8: Mayor Inspects Attention Queue
            $mayorUser = User::findById($mayorUserId);
            Auth::setUser($mayorUser);

            $attnReq = new Request('GET', '/api/v1/executive/attention-queue');
            $attnResp = $this->executiveController->getAttentionQueue($attnReq);
            $this->assertEquals(200, $attnResp->getStatusCode());
            $attnDecoded = json_decode($attnResp->getBody(), true);
            $attnList = $attnDecoded['data'] ?? $attnDecoded;

            $hasAttn = false;
            foreach ($attnList as $item) {
                if ((int)$item['complaint_id'] === $complaintId && $item['trigger_type'] === 'citizen_reopen') {
                    $hasAttn = true;
                    break;
                }
            }
            $this->assertTrue($hasAttn, "Reopened complaint must appear in Mayor's Attention Required Queue");

            // STEP 9: Mayor Issues Executive Directive
            $dirReq = new Request('POST', '/api/v1/executive/directives', [], [
                'complaint_id' => $complaintId,
                'directive_type' => 'remedy_incomplete',
                'instruction' => 'অসম্পূর্ণ অংশ অবিলম্বে সম্পন্ন করুন।'
            ]);
            $dirResp = $this->executiveController->issueDirective($dirReq);
            $this->assertEquals(201, $dirResp->getStatusCode());

            // STEP 10: Final Citizen Confirmation
            Auth::setUser($citizenUser);
            $finalReq = new Request('POST', "/api/v1/complaints/{$complaintId}/confirm", [], [
                'is_resolved' => true,
                'rating' => 5,
                'comment' => 'সম্পূর্ণ পরিষ্কার হয়েছে, মেয়র মহোদয়কে ধন্যবাদ।'
            ]);
            $finalResp = $this->complaintController->confirm($finalReq, (string)$complaintId);
            $this->assertEquals(200, $finalResp->getStatusCode());

            // Verify Final Closure
            $finalRow = $pdo->query("SELECT internal_status, citizen_status, closed_at FROM complaints WHERE id = {$complaintId}")->fetch(PDO::FETCH_ASSOC);
            $this->assertEquals('closed', $finalRow['internal_status']);
            $this->assertEquals('resolved', $finalRow['citizen_status']);
            $this->assertNotNull($finalRow['closed_at']);
        } finally {
            $userList = "{$citizenUserId}, {$mayorUserId}";
            $pdo->exec("DELETE FROM executive_directives WHERE executive_user_id IN ({$userList}) OR complaint_id IN (SELECT id FROM complaints WHERE citizen_user_id IN ({$userList}))");
            $pdo->exec("DELETE FROM executive_attention WHERE complaint_id IN (SELECT id FROM complaints WHERE citizen_user_id IN ({$userList}))");
            $pdo->exec("DELETE FROM citizen_feedback WHERE citizen_user_id IN ({$userList}) OR complaint_id IN (SELECT id FROM complaints WHERE citizen_user_id IN ({$userList}))");
            $pdo->exec("DELETE FROM task_evidence WHERE field_task_id IN (SELECT id FROM field_tasks WHERE complaint_id IN (SELECT id FROM complaints WHERE citizen_user_id IN ({$userList})))");
            $pdo->exec("DELETE FROM field_task_assignments WHERE field_task_id IN (SELECT id FROM field_tasks WHERE complaint_id IN (SELECT id FROM complaints WHERE citizen_user_id IN ({$userList})))");
            $pdo->exec("DELETE FROM field_tasks WHERE complaint_id IN (SELECT id FROM complaints WHERE citizen_user_id IN ({$userList}))");
            $pdo->exec("DELETE FROM complaint_media WHERE uploader_user_id IN ({$userList}) OR complaint_id IN (SELECT id FROM complaints WHERE citizen_user_id IN ({$userList}))");
            $pdo->exec("DELETE FROM complaint_ownership_history WHERE transferred_by_user_id IN ({$userList}) OR complaint_id IN (SELECT id FROM complaints WHERE citizen_user_id IN ({$userList}))");
            $pdo->exec("DELETE FROM complaint_status_history WHERE actor_user_id IN ({$userList}) OR complaint_id IN (SELECT id FROM complaints WHERE citizen_user_id IN ({$userList}))");
            $pdo->exec("DELETE FROM complaint_locations WHERE complaint_id IN (SELECT id FROM complaints WHERE citizen_user_id IN ({$userList}))");
            $pdo->exec("DELETE FROM complaints WHERE citizen_user_id IN ({$userList})");
            $pdo->exec("DELETE FROM employees WHERE id IN ({$supervisorEmpId}, {$workerEmpId})");
            $pdo->exec("DELETE FROM persons WHERE id IN ({$personSupId}, {$personWrkId})");
            $pdo->exec("DELETE FROM users WHERE id IN ({$userList})");
        }
    }
}
