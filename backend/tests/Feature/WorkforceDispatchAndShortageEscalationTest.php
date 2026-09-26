<?php

declare(strict_types=1);

namespace AmarMayor\Tests\Feature;

use AmarMayor\Controllers\Web\DashboardWebController;
use AmarMayor\Database\DatabaseManager;
use AmarMayor\Domain\ComplaintCore\ComplaintService;
use AmarMayor\Domain\FieldOperations\FieldTaskService;
use AmarMayor\Http\Auth;
use AmarMayor\Http\Request;
use AmarMayor\Support\Security;
use AmarMayor\Tests\TestCase;
use PDO;

class WorkforceDispatchAndShortageEscalationTest extends TestCase
{
    private PDO $pdo;
    private int $citizenUserId = 0;
    private int $supUserId = 0;
    private int $supEmpId = 0;
    private int $teamLeaderUserId = 0;
    private int $teamLeaderEmpId = 0;
    private int $deptHeadUserId = 0;
    private int $deptHeadEmpId = 0;
    private int $complaintId = 0;
    private string $publicNum = '';

    public function setUp(): void
    {
        parent::setUp();
        $this->pdo = DatabaseManager::getConnection();

        $suffix = bin2hex(random_bytes(4));

        // 1. Setup Citizen
        $cuuid = Security::uuid();
        $this->pdo->exec("INSERT INTO users (uuid, email, phone, user_type, status, created_at) 
                          VALUES ('{$cuuid}', 'cit_wf_{$suffix}@example.com', '01711{$suffix}', 'citizen', 'active', NOW())");
        $this->citizenUserId = (int)$this->pdo->lastInsertId();

        // 2. Setup Supervisor for Ward 1
        $suuid = Security::uuid();
        $this->pdo->exec("INSERT INTO users (uuid, email, phone, user_type, status, created_at) 
                          VALUES ('{$suuid}', 'sup_wf_{$suffix}@example.com', '01811{$suffix}', 'supervisor', 'active', NOW())");
        $this->supUserId = (int)$this->pdo->lastInsertId();

        $this->pdo->exec("INSERT INTO persons (user_id, full_name_bn, full_name_en, official_phone, created_at)
                          VALUES ({$this->supUserId}, 'সুপারভাইজার টেস্ট', 'Supervisor Test', '01811{$suffix}', NOW())");
        $supPersonId = (int)$this->pdo->lastInsertId();

        $this->pdo->exec("INSERT INTO employees (person_id, employee_code, designation_bn, designation_en, created_at)
                          VALUES ({$supPersonId}, 'EMP-SUP-WF-{$suffix}', 'ওয়ার্ড সুপারভাইজার', 'Ward Supervisor', NOW())");
        $this->supEmpId = (int)$this->pdo->lastInsertId();

        $this->pdo->exec("INSERT INTO employee_responsibilities (employee_id, area_type, area_id, responsibility_type, effective_from, is_demo, created_at)
                          VALUES ({$this->supEmpId}, 'ward', '1', 'supervisor', CURDATE(), 1, NOW())");

        // 3. Setup Team Leader for Ward 1
        $tluuid = Security::uuid();
        $this->pdo->exec("INSERT INTO users (uuid, email, phone, user_type, status, created_at) 
                          VALUES ('{$tluuid}', 'tl_wf_{$suffix}@example.com', '01611{$suffix}', 'team_leader', 'active', NOW())");
        $this->teamLeaderUserId = (int)$this->pdo->lastInsertId();

        $this->pdo->exec("INSERT INTO persons (user_id, full_name_bn, full_name_en, official_phone, created_at)
                          VALUES ({$this->teamLeaderUserId}, 'মাঠ দলনেতা টেস্ট', 'Field Team Leader Test', '01611{$suffix}', NOW())");
        $tlPersonId = (int)$this->pdo->lastInsertId();

        $this->pdo->exec("INSERT INTO employees (person_id, employee_code, designation_bn, designation_en, created_at)
                          VALUES ({$tlPersonId}, 'EMP-TL-WF-{$suffix}', 'মাঠ দলনেতা', 'Field Team Leader', NOW())");
        $this->teamLeaderEmpId = (int)$this->pdo->lastInsertId();

        $this->pdo->exec("INSERT INTO employee_responsibilities (employee_id, area_type, area_id, responsibility_type, effective_from, is_demo, created_at)
                          VALUES ({$this->teamLeaderEmpId}, 'ward', '1', 'primary', CURDATE(), 1, NOW())");

        // 4. Setup Waste Management Department Head (Dept ID 1)
        $huuid = Security::uuid();
        $this->pdo->exec("INSERT INTO users (uuid, email, phone, user_type, status, created_at) 
                          VALUES ('{$huuid}', 'head_waste_{$suffix}@example.com', '01911{$suffix}', 'department_head', 'active', NOW())");
        $this->deptHeadUserId = (int)$this->pdo->lastInsertId();

        $this->pdo->exec("INSERT INTO persons (user_id, full_name_bn, full_name_en, official_phone, created_at)
                          VALUES ({$this->deptHeadUserId}, 'প্রধান বর্জ্য ব্যবস্থাপনা কর্মকর্তা', 'Chief Waste Management Officer', '01911{$suffix}', NOW())");
        $headPersonId = (int)$this->pdo->lastInsertId();

        $this->pdo->exec("INSERT INTO employees (person_id, employee_code, designation_bn, designation_en, created_at)
                          VALUES ({$headPersonId}, 'EMP-WASTE-{$suffix}', 'প্রধান বর্জ্য ব্যবস্থাপনা কর্মকর্তা', 'Chief Waste Management Officer', NOW())");
        $this->deptHeadEmpId = (int)$this->pdo->lastInsertId();

        $this->pdo->exec("INSERT INTO employee_postings (employee_id, department_id, posting_type, effective_from, created_at)
                          VALUES ({$this->deptHeadEmpId}, 1, 'regular', CURDATE(), NOW())");

        // 5. Create Test Complaint in Ward 1
        $complaintService = new ComplaintService();
        $created = $complaintService->createComplaint([
            'citizen_user_id' => $this->citizenUserId,
            'ward_id' => 1,
            'category_id' => 1,
            'subcategory_id' => 1,
            'description' => 'ওয়ার্ড ১ এর বড় রাস্তায় ময়লার স্তূপ',
            'landmark' => 'জিলা স্কুল মোড়',
            'latitude' => 24.7550,
            'longitude' => 90.4050
        ]);
        $this->complaintId = (int)$created['id'];
        $this->publicNum = (string)$created['public_complaint_number'];
    }

    public function tearDown(): void
    {
        if ($this->complaintId) {
            $this->pdo->exec("DELETE FROM task_evidence WHERE field_task_id IN (SELECT id FROM field_tasks WHERE complaint_id = {$this->complaintId})");
            $this->pdo->exec("DELETE FROM field_task_assignments WHERE field_task_id IN (SELECT id FROM field_tasks WHERE complaint_id = {$this->complaintId})");
            $this->pdo->exec("DELETE FROM internal_notes WHERE complaint_id = {$this->complaintId}");
            $this->pdo->exec("DELETE FROM support_requests WHERE complaint_id = {$this->complaintId}");
            $this->pdo->exec("DELETE FROM field_tasks WHERE complaint_id = {$this->complaintId}");
            $this->pdo->exec("DELETE FROM complaint_locations WHERE complaint_id = {$this->complaintId}");
            $this->pdo->exec("DELETE FROM complaint_status_history WHERE complaint_id = {$this->complaintId}");
            $this->pdo->exec("DELETE FROM complaints WHERE id = {$this->complaintId}");
        }

        if ($this->supEmpId) {
            $this->pdo->exec("DELETE FROM employee_responsibilities WHERE employee_id = {$this->supEmpId}");
            $this->pdo->exec("DELETE FROM employees WHERE id = {$this->supEmpId}");
        }
        if ($this->teamLeaderEmpId) {
            $this->pdo->exec("DELETE FROM employee_responsibilities WHERE employee_id = {$this->teamLeaderEmpId}");
            $this->pdo->exec("DELETE FROM employees WHERE id = {$this->teamLeaderEmpId}");
        }
        if ($this->deptHeadEmpId) {
            $this->pdo->exec("DELETE FROM employee_postings WHERE employee_id = {$this->deptHeadEmpId}");
            $this->pdo->exec("DELETE FROM employees WHERE id = {$this->deptHeadEmpId}");
        }
        if ($this->supUserId) {
            $this->pdo->exec("DELETE FROM persons WHERE user_id = {$this->supUserId}");
            $this->pdo->exec("DELETE FROM users WHERE id = {$this->supUserId}");
        }
        if ($this->teamLeaderUserId) {
            $this->pdo->exec("DELETE FROM persons WHERE user_id = {$this->teamLeaderUserId}");
            $this->pdo->exec("DELETE FROM users WHERE id = {$this->teamLeaderUserId}");
        }
        if ($this->deptHeadUserId) {
            $this->pdo->exec("DELETE FROM persons WHERE user_id = {$this->deptHeadUserId}");
            $this->pdo->exec("DELETE FROM users WHERE id = {$this->deptHeadUserId}");
        }
        if ($this->citizenUserId) {
            $this->pdo->exec("DELETE FROM users WHERE id = {$this->citizenUserId}");
        }
    }

    public function testCleanersHaveNoLoginAccountsAndTeamLeadersHaveLogins(): void
    {
        // 1. Verify Cleaners have NULL user_id
        $cleanerCount = (int)$this->pdo->query("
            SELECT COUNT(*) FROM employees e
            INNER JOIN persons p ON p.id = e.person_id
            WHERE e.designation_bn = 'পরিচ্ছন্নতাকর্মী' AND p.user_id IS NULL
        ")->fetchColumn();

        $this->assertGreaterThan(0, $cleanerCount, "Cleaners should exist as manual workers without user_id");

        // 2. Verify all 33 wards have a Team Leader with a user account
        $teamLeaderCount = (int)$this->pdo->query("
            SELECT COUNT(DISTINCT er.area_id) FROM employee_responsibilities er
            INNER JOIN employees e ON e.id = er.employee_id
            INNER JOIN persons p ON p.id = e.person_id
            INNER JOIN user_roles ur ON ur.user_id = p.user_id
            INNER JOIN roles r ON r.id = ur.role_id
            WHERE er.area_type = 'ward' AND r.slug = 'team_leader'
        ")->fetchColumn();

        $this->assertEquals(33, $teamLeaderCount, "All 33 wards must have designated Team Leaders with user logins");
    }

    public function testWardWorkforceStatsCalculation(): void
    {
        $fieldTaskService = new FieldTaskService();
        $stats = $fieldTaskService->getWardWorkforceStats(1);

        $this->assertArrayHasKey('total_workers', $stats);
        $this->assertArrayHasKey('deployed_workers', $stats);
        $this->assertArrayHasKey('available_workers', $stats);
        $this->assertArrayHasKey('team_leaders', $stats);

        $this->assertGreaterThanOrEqual(8, $stats['total_workers'], "Ward 1 should have at least 8 cleaners seeded");
        $this->assertEquals(max(0, $stats['total_workers'] - $stats['deployed_workers']), $stats['available_workers']);
        $this->assertNotEmpty($stats['team_leaders'], "Ward 1 should have team leaders listed");
    }

    public function testSupervisorSquadDispatchLifecycle(): void
    {
        $fieldTaskService = new FieldTaskService();

        // 1. Dispatch squad task with 4 workers and team leader
        $taskId = $fieldTaskService->dispatchSquadTask(
            $this->complaintId,
            $this->supUserId,
            $this->teamLeaderEmpId,
            4,
            'বড় বাজারের ড্রেনের ময়লা দ্রুত অপসারণ করতে হবে।'
        );

        $this->assertGreaterThan(0, $taskId, "Dispatched task ID should be positive");

        // Verify task row
        $task = $this->pdo->query("SELECT * FROM field_tasks WHERE id = {$taskId}")->fetch(PDO::FETCH_ASSOC);
        $this->assertEquals($this->teamLeaderEmpId, (int)$task['team_leader_employee_id']);
        $this->assertEquals(4, (int)$task['worker_count']);
        $this->assertEquals('pending', $task['task_status']);

        // Verify complaint status updated to assigned
        $c = $this->pdo->query("SELECT internal_status FROM complaints WHERE id = {$this->complaintId}")->fetch(PDO::FETCH_ASSOC);
        $this->assertEquals('assigned', $c['internal_status']);

        // Verify note logged
        $note = $this->pdo->query("SELECT note_text FROM internal_notes WHERE complaint_id = {$this->complaintId} ORDER BY id DESC LIMIT 1")->fetchColumn();
        $this->assertStringContainsString('৪ জন', (string)$note);

        // 2. Team leader fetches tasks
        $tlTasks = $fieldTaskService->getTasksForWorker($this->teamLeaderUserId);
        $foundTask = false;
        foreach ($tlTasks as $t) {
            if ((int)$t['id'] === $taskId) {
                $foundTask = true;
                $this->assertEquals(4, (int)$t['worker_count']);
                break;
            }
        }
        $this->assertTrue($foundTask, "Team leader should see dispatched squad task");

        // 3. Team leader starts the task
        $fieldTaskService->startTask($taskId, $this->teamLeaderEmpId);
        $taskAfterStart = $this->pdo->query("SELECT task_status FROM field_tasks WHERE id = {$taskId}")->fetch(PDO::FETCH_ASSOC);
        $this->assertEquals('in_progress', $taskAfterStart['task_status']);

        // 4. Team leader completes the task
        $fieldTaskService->completeTask($taskId, $this->teamLeaderEmpId, 'মাঠের সব ময়লা পরিষ্কার সম্পন্ন হয়েছে।');
        $taskAfterComplete = $this->pdo->query("SELECT task_status, completed_at FROM field_tasks WHERE id = {$taskId}")->fetch(PDO::FETCH_ASSOC);
        $this->assertEquals('completed', $taskAfterComplete['task_status']);
        $this->assertNotNull($taskAfterComplete['completed_at']);

        // Verify complaint transitioned to work_completed
        $cCompleted = $this->pdo->query("SELECT internal_status FROM complaints WHERE id = {$this->complaintId}")->fetch(PDO::FETCH_ASSOC);
        $this->assertEquals('work_completed', $cCompleted['internal_status']);
    }

    public function testWorkforceShortageAndDepartmentAllocationFlow(): void
    {
        $fieldTaskService = new FieldTaskService();

        // 1. Supervisor requests extra manpower due to ward shortage
        $srId = $fieldTaskService->requestWorkforceSupport(
            $this->complaintId,
            $this->supUserId,
            6,
            'বড় নর্দমা পরিষ্কারের জন্য ওয়ার্ডের নিজস্ব কর্মী ঘাটতি রয়েছে, জরুরি ৬ জন কর্মী লাগবে।'
        );

        $this->assertGreaterThan(0, $srId, "Support request ID should be positive");

        $sr = $this->pdo->query("SELECT * FROM support_requests WHERE id = {$srId}")->fetch(PDO::FETCH_ASSOC);
        $this->assertEquals('extra_manpower', $sr['support_type']);
        $this->assertEquals(6, (int)$sr['requested_worker_count']);
        $this->assertEquals('pending', $sr['status']);

        // 2. Verify Mayor / CEO Command Center detects this shortage as Red Alert
        $alerts = $this->pdo->query("
            SELECT sr.*, c.public_complaint_number, w.ward_number
            FROM support_requests sr
            INNER JOIN complaints c ON c.id = sr.complaint_id
            LEFT JOIN wards w ON w.id = c.ward_id
            WHERE sr.support_type = 'extra_manpower'
              AND (sr.status = 'pending' OR sr.fulfillment_status = 'failed_delivery')
              AND sr.id = {$srId}
        ")->fetchAll(PDO::FETCH_ASSOC);

        $this->assertCount(1, $alerts, "Pending workforce shortage must appear in Mayor/CEO Red Alert queue");
        $this->assertEquals(6, (int)$alerts[0]['requested_worker_count']);

        // 3. Department Head allocates 6 workers from central reserve
        $fieldTaskService->allocateWorkforceSupport(
            $srId,
            $this->deptHeadUserId,
            6,
            'central_reserve',
            'কেন্দ্রীয় রিজার্ভ পরিচ্ছন্নতা স্কোয়াড থেকে ৬ জন পরিচ্ছন্নতাকর্মী ওয়ার্ড ১ এ পাঠানো হলো।'
        );

        $srAfterAllocation = $this->pdo->query("SELECT * FROM support_requests WHERE id = {$srId}")->fetch(PDO::FETCH_ASSOC);
        $this->assertEquals('approved', $srAfterAllocation['status']);
        $this->assertEquals('dispatched', $srAfterAllocation['fulfillment_status']);
        $this->assertEquals(6, (int)$srAfterAllocation['allocated_worker_count']);
        $this->assertStringContainsString('রিজার্ভ', (string)$srAfterAllocation['allocated_resource']);

        // 4. Verify shortage alert is resolved and cleared from Red Alert queue
        $alertsAfter = $this->pdo->query("
            SELECT sr.*
            FROM support_requests sr
            WHERE sr.support_type = 'extra_manpower'
              AND (sr.status = 'pending' OR sr.fulfillment_status = 'failed_delivery')
              AND sr.id = {$srId}
        ")->fetchAll(PDO::FETCH_ASSOC);

        $this->assertCount(0, $alertsAfter, "Resolved workforce shortage must no longer appear in Red Alert queue");
    }

    public function testDepartmentHeadCanDeputeWorkersFromAnotherWard(): void
    {
        $fieldTaskService = new FieldTaskService();

        // Supervisor requests 3 workers
        $srId = $fieldTaskService->requestWorkforceSupport(
            $this->complaintId,
            $this->supUserId,
            3,
            'ওয়ার্ড ১ এ অতিরিক্ত ৩ জন জরুরি পরিচ্ছন্নতাকর্মী প্রয়োজন।'
        );

        // Department head deputes 3 workers from Ward 2
        $fieldTaskService->allocateWorkforceSupport(
            $srId,
            $this->deptHeadUserId,
            3,
            'deputation',
            'ওয়ার্ড ২ থেকে ৩ জন কর্মীকে ওয়ার্ড ১ এ সাময়িক ডেপুটেশন দেয়া হলো।',
            2 // Source ward = Ward 2
        );

        $sr = $this->pdo->query("SELECT * FROM support_requests WHERE id = {$srId}")->fetch(PDO::FETCH_ASSOC);
        $this->assertEquals('approved', $sr['status']);
        $this->assertEquals('dispatched', $sr['fulfillment_status']);
        $this->assertEquals(3, (int)$sr['allocated_worker_count']);
        $this->assertEquals(2, (int)$sr['source_ward_id']);
        $this->assertStringContainsString('ওয়ার্ড ২', (string)$sr['allocated_resource']);
    }
}
