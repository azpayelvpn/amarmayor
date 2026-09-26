<?php

declare(strict_types=1);

namespace AmarMayor\Tests\Feature;

use AmarMayor\Database\DatabaseManager;
use AmarMayor\Domain\ComplaintCore\ComplaintService;
use AmarMayor\Domain\FieldOperations\FieldTaskService;
use AmarMayor\Http\Auth;
use AmarMayor\Http\Request;
use AmarMayor\Support\Security;
use AmarMayor\Tests\TestCase;
use PDO;

class CrossDepartmentFulfillmentAndFilteringTest extends TestCase
{
    private PDO $pdo;
    private int $citizenUserId = 0;
    private int $supUserId = 0;
    private int $supEmpId = 0;
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
                          VALUES ('{$cuuid}', 'cit_sup_{$suffix}@example.com', '01710{$suffix}', 'citizen', 'active', NOW())");
        $this->citizenUserId = (int)$this->pdo->lastInsertId();

        // 2. Setup Supervisor (Ward 1)
        $suuid = Security::uuid();
        $this->pdo->exec("INSERT INTO users (uuid, email, phone, user_type, status, created_at) 
                          VALUES ('{$suuid}', 'sup_{$suffix}@example.com', '01810{$suffix}', 'supervisor', 'active', NOW())");
        $this->supUserId = (int)$this->pdo->lastInsertId();

        $this->pdo->exec("INSERT INTO persons (user_id, full_name_bn, full_name_en, official_phone, created_at)
                          VALUES ({$this->supUserId}, 'সুপারভাইজার', 'Supervisor', '01810{$suffix}', NOW())");
        $supPersonId = (int)$this->pdo->lastInsertId();

        $this->pdo->exec("INSERT INTO employees (person_id, employee_code, designation_bn, designation_en, created_at)
                          VALUES ({$supPersonId}, 'EMP-SUP-{$suffix}', 'ওয়ার্ড সুপারভাইজার', 'Ward Supervisor', NOW())");
        $this->supEmpId = (int)$this->pdo->lastInsertId();

        $this->pdo->exec("INSERT INTO employee_responsibilities (employee_id, area_type, area_id, responsibility_type, effective_from, is_demo, created_at)
                          VALUES ({$this->supEmpId}, 'ward', '1', 'supervisor', CURDATE(), 1, NOW())");

        // 3. Setup Target Department Head (Engineering, Dept ID 2)
        $huuid = Security::uuid();
        $this->pdo->exec("INSERT INTO users (uuid, email, phone, user_type, status, created_at) 
                          VALUES ('{$huuid}', 'head_eng_{$suffix}@example.com', '01910{$suffix}', 'department_head', 'active', NOW())");
        $this->deptHeadUserId = (int)$this->pdo->lastInsertId();

        $this->pdo->exec("INSERT INTO persons (user_id, full_name_bn, full_name_en, official_phone, created_at)
                          VALUES ({$this->deptHeadUserId}, 'বিভাগীয় প্রকৌশলী', 'Executive Engineer', '01910{$suffix}', NOW())");
        $headPersonId = (int)$this->pdo->lastInsertId();

        $this->pdo->exec("INSERT INTO employees (person_id, employee_code, designation_bn, designation_en, created_at)
                          VALUES ({$headPersonId}, 'EMP-ENG-{$suffix}', 'তত্ত্বাবধায়ক প্রকৌশলী', 'Superintending Engineer', NOW())");
        $this->deptHeadEmpId = (int)$this->pdo->lastInsertId();

        $this->pdo->exec("INSERT INTO employee_postings (employee_id, department_id, posting_type, effective_from, created_at)
                          VALUES ({$this->deptHeadEmpId}, 2, 'regular', CURDATE(), NOW())");

        // 4. Create Flagship Complaint in Ward 1 (Waste Management, Dept 1)
        $complaintService = new ComplaintService();
        $created = $complaintService->createComplaint([
            'citizen_user_id' => $this->citizenUserId,
            'ward_id' => 1,
            'category_id' => 1,
            'subcategory_id' => 1, // Waste subcategory
            'description' => 'বড় ড্রেন ব্লকেজ ও আবর্জনা জমা',
            'landmark' => 'বড় বাজার ড্রেন মোড়',
            'latitude' => 24.7500,
            'longitude' => 90.4000
        ]);
        $this->complaintId = (int)$created['id'];
        $this->publicNum = (string)$created['public_complaint_number'];
    }

    public function tearDown(): void
    {
        if ($this->complaintId) {
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
        if ($this->deptHeadEmpId) {
            $this->pdo->exec("DELETE FROM employee_postings WHERE employee_id = {$this->deptHeadEmpId}");
            $this->pdo->exec("DELETE FROM employees WHERE id = {$this->deptHeadEmpId}");
        }
        if ($this->supUserId) {
            $this->pdo->exec("DELETE FROM persons WHERE user_id = {$this->supUserId}");
            $this->pdo->exec("DELETE FROM users WHERE id = {$this->supUserId}");
        }
        if ($this->deptHeadUserId) {
            $this->pdo->exec("DELETE FROM persons WHERE user_id = {$this->deptHeadUserId}");
            $this->pdo->exec("DELETE FROM users WHERE id = {$this->deptHeadUserId}");
        }
        if ($this->citizenUserId) {
            $this->pdo->exec("DELETE FROM users WHERE id = {$this->citizenUserId}");
        }

        parent::tearDown();
    }

    public function testCrossDepartmentSupportAllocationAndGroundReceiptLifecycle(): void
    {
        $fieldTaskService = new FieldTaskService();

        // 1. Supervisor creates support request for heavy machinery
        $srId = $fieldTaskService->createSupportRequest(
            $this->complaintId,
            null,
            $this->supUserId,
            'machinery',
            2, // Engineering department
            'ড্রেন কাটার জন্য মিনি-এক্সকাভেটর প্রয়োজন'
        );

        $this->assertGreaterThan(0, $srId, "Support request ID should be positive");

        $sr = $this->pdo->query("SELECT * FROM support_requests WHERE id = {$srId}")->fetch(PDO::FETCH_ASSOC);
        $this->assertEquals('pending', $sr['status']);
        $this->assertEquals('pending', $sr['fulfillment_status']);
        $this->assertNull($sr['allocated_resource']);

        // 2. Engineering Department Head approves with explicit resource allocation
        $fieldTaskService->respondSupportRequest(
            $srId,
            $this->deptHeadUserId,
            'approved',
            'মিনি-এক্সকাভেটর বরাদ্দ অনুমোদন করা হলো।',
            'মিনি-এক্সকাভেটর MCC-EX-03',
            'চালক মোঃ রফিক (০১৭১১-২২৩৩৪৪)',
            '১৪ সেপ্টেম্বর সকাল ৯:০০ টা'
        );

        $srAfterApproval = $this->pdo->query("SELECT * FROM support_requests WHERE id = {$srId}")->fetch(PDO::FETCH_ASSOC);
        $this->assertEquals('approved', $srAfterApproval['status']);
        $this->assertEquals('dispatched', $srAfterApproval['fulfillment_status'], "Fulfillment status should be dispatched after approval");
        $this->assertEquals('মিনি-এক্সকাভেটর MCC-EX-03', $srAfterApproval['allocated_resource']);
        $this->assertEquals('চালক মোঃ রফিক (০১৭১১-২২৩৩৪৪)', $srAfterApproval['allocated_operator']);
        $this->assertEquals('১৪ সেপ্টেম্বর সকাল ৯:০০ টা', $srAfterApproval['scheduled_arrival']);

        // 3. Supervisor verifies on site that machinery was received
        $fieldTaskService->verifySupportReceipt(
            $srId,
            $this->supUserId,
            'received',
            'এক্সকাভেটর সাইটে পৌঁছেছে এবং ড্রেন কাটার কাজ শুরু হয়েছে।'
        );

        $srVerified = $this->pdo->query("SELECT * FROM support_requests WHERE id = {$srId}")->fetch(PDO::FETCH_ASSOC);
        $this->assertEquals('received', $srVerified['status']);
        $this->assertEquals('received', $srVerified['fulfillment_status']);
        $this->assertEquals($this->supUserId, (int)$srVerified['received_by_user_id']);
        $this->assertNotNull($srVerified['received_at']);
        $this->assertStringContainsString('ড্রেন কাটার কাজ শুরু', $srVerified['receipt_notes']);
    }

    public function testDeliveryFailureReportsTriggerBottleneckEscalation(): void
    {
        $fieldTaskService = new FieldTaskService();

        // Create second support request
        $srId2 = $fieldTaskService->createSupportRequest(
            $this->complaintId,
            null,
            $this->supUserId,
            'machinery',
            2,
            'সাকশন লরি প্রয়োজন'
        );

        // Department Head approves
        $fieldTaskService->respondSupportRequest(
            $srId2,
            $this->deptHeadUserId,
            'approved',
            'সাকশন লরি পাঠানো হলো',
            'সাকশন লরি MCC-SL-01',
            'চালক মোঃ সুমন (০১৮১১-০০০০০০)',
            'সকাল ৮:০০ টা'
        );

        // Supervisor reports DELIVERY FAILED (machinery did not arrive on site)
        $fieldTaskService->verifySupportReceipt(
            $srId2,
            $this->supUserId,
            'failed_delivery',
            'চালক নির্ধারিত সময়ে আসেনি এবং ফোন ধরেনি। মাঠে কাজ বন্ধ রয়েছে।'
        );

        $srFailed = $this->pdo->query("SELECT * FROM support_requests WHERE id = {$srId2}")->fetch(PDO::FETCH_ASSOC);
        $this->assertEquals('failed_delivery', $srFailed['status']);
        $this->assertEquals('failed_delivery', $srFailed['fulfillment_status']);
        $this->assertStringContainsString('চালক নির্ধারিত সময়ে আসেনি', $srFailed['receipt_notes']);

        // Check that executive query for Mayor and CEO catches this breakdown
        $stmt = $this->pdo->query("
            SELECT sr.*, c.public_complaint_number, d.name_bn as target_dept_name
            FROM support_requests sr
            INNER JOIN complaints c ON c.id = sr.complaint_id
            LEFT JOIN departments d ON d.id = sr.target_department_id
            WHERE sr.fulfillment_status = 'failed_delivery'
            ORDER BY sr.id DESC LIMIT 5
        ");
        $bottlenecks = $stmt->fetchAll(PDO::FETCH_ASSOC);

        $this->assertNotEmpty($bottlenecks, "Failed deliveries should appear in executive bottleneck queue");
        $found = false;
        foreach ($bottlenecks as $b) {
            if ((int)$b['id'] === $srId2) {
                $found = true;
                $this->assertEquals('failed_delivery', $b['fulfillment_status']);
                $this->assertEquals('সাকশন লরি MCC-SL-01', $b['allocated_resource']);
                break;
            }
        }
        $this->assertTrue($found, "Specific failed delivery must be present in executive bottlenecks");
    }

    public function testUnauthorizedUserCannotVerifySupportReceipt(): void
    {
        $fieldTaskService = new FieldTaskService();

        $srId = $fieldTaskService->createSupportRequest(
            $this->complaintId,
            null,
            $this->supUserId,
            'machinery',
            2,
            'টেস্ট আবেদন'
        );

        $thrown = false;
        try {
            // Citizen tries to verify supervisor's support receipt
            $fieldTaskService->verifySupportReceipt(
                $srId,
                $this->citizenUserId,
                'received',
                'নাগরিক কর্তৃক অবৈধ কল'
            );
        } catch (\InvalidArgumentException $e) {
            $thrown = true;
        }

        $this->assertTrue($thrown, "Non-supervisor user should trigger InvalidArgumentException");
    }

    public function testTableFilterAssetsAndMarkupPresent(): void
    {
        // 1. Verify table-filter.js exists
        $jsPath = dirname(__DIR__, 3) . '/public/js/table-filter.js';
        $this->assertTrue(file_exists($jsPath), "table-filter.js must exist in public/js");
        $jsContent = file_get_contents($jsPath);
        $this->assertStringContainsString('table-filter-toolbar', $jsContent);
        $this->assertStringContainsString('filter-search', $jsContent);

        // 2. Verify layout includes table-filter.js
        $layoutPath = dirname(__DIR__, 2) . '/views/layouts/app.php';
        $layoutContent = file_get_contents($layoutPath);
        $this->assertStringContainsString('/js/table-filter.js', $layoutContent, "Layout must load table-filter.js");

        // 3. Verify my_complaints view has filter toolbar and data-search attributes
        $myComplaintsPath = dirname(__DIR__, 2) . '/views/complaints/my_complaints.php';
        $mcContent = file_get_contents($myComplaintsPath);
        $this->assertStringContainsString('table-filter-toolbar', $mcContent, "my_complaints must render table-filter-toolbar");
        $this->assertStringContainsString('data-search', $mcContent);
        $this->assertStringContainsString('filter-pill', $mcContent);

        // 4. Verify department_head view has structured allocation form and filter
        $dhPath = dirname(__DIR__, 2) . '/views/dashboard/roles/department_head.php';
        $dhContent = file_get_contents($dhPath);
        $this->assertStringContainsString('allocated_resource', $dhContent, "department_head must render allocated_resource input");
        $this->assertStringContainsString('allocated_operator', $dhContent, "department_head must render allocated_operator input");
        $this->assertStringContainsString('table-filter-toolbar', $dhContent, "department_head must render table-filter-toolbar");

        // 5. Verify supervisor view has ground verification action buttons
        $supPath = dirname(__DIR__, 2) . '/views/dashboard/roles/supervisor.php';
        $supContent = file_get_contents($supPath);
        $this->assertStringContainsString('verify-receipt', $supContent, "supervisor must render verify-receipt action form");
        $this->assertStringContainsString('মাঠে পেয়েছি', $supContent, "supervisor must have receipt confirmation button");
        $this->assertStringContainsString('পৌঁছায়নি', $supContent, "supervisor must have delivery failure report button");

        // 6. Verify mayor and ceo views have inter-department breakdown alert
        $mayorPath = dirname(__DIR__, 2) . '/views/dashboard/roles/mayor.php';
        $mayorContent = file_get_contents($mayorPath);
        $this->assertStringContainsString('supportBottlenecks', $mayorContent, "mayor dashboard must render supportBottlenecks");

        $ceoPath = dirname(__DIR__, 2) . '/views/dashboard/roles/ceo.php';
        $ceoContent = file_get_contents($ceoPath);
        $this->assertStringContainsString('supportBottlenecks', $ceoContent, "ceo dashboard must render supportBottlenecks");
    }
}
