<?php

declare(strict_types=1);

namespace AmarMayor\Tests\Unit\Domain;

use AmarMayor\Database\DatabaseManager;
use AmarMayor\Domain\ComplaintConfig\RoutingConfigService;
use AmarMayor\Domain\ComplaintCore\ComplaintService;
use AmarMayor\Domain\ComplaintRouting\ComplaintRoutingEngine;
use AmarMayor\Support\Security;
use AmarMayor\Tests\TestCase;
use PDO;

class ComplaintRoutingEngineTest extends TestCase
{
    private ComplaintService $complaintService;
    private RoutingConfigService $routingConfigService;
    private ComplaintRoutingEngine $routingEngine;

    public function setUp(): void
    {
        parent::setUp();
        $this->routingConfigService = new RoutingConfigService();
        $this->routingEngine = new ComplaintRoutingEngine($this->routingConfigService);
        $this->complaintService = new ComplaintService(null, $this->routingEngine);
    }

    public function testAutomaticRoutingOnComplaintSubmission(): void
    {
        $pdo = DatabaseManager::getConnection();
        $testUuid = Security::uuid();
        $phone = '+88017' . sprintf('%08d', random_int(10000000, 99999999));

        // 1. Create a citizen
        $stmt = $pdo->prepare("
            INSERT INTO users (uuid, phone, phone_lookup_hash, user_type, status, preferred_language, created_at)
            VALUES (?, ?, 'hash-r1', 'citizen', 'active', 'bn', NOW())
        ");
        $stmt->execute([$testUuid, $phone]);
        $citizenUserId = (int)$pdo->lastInsertId();

        // 2. Create a supervisor employee
        $pStmt = $pdo->prepare("INSERT INTO persons (full_name_bn, full_name_en, created_at) VALUES ('সুপারভাইজার ১', 'Supervisor 1', NOW())");
        $pStmt->execute();
        $personId = (int)$pdo->lastInsertId();

        $empCode = 'SUP-R-' . random_int(1000, 9999);
        $eStmt = $pdo->prepare("INSERT INTO employees (person_id, employee_code, designation_bn, designation_en, created_at) VALUES (?, ?, 'সুপারভাইজার', 'Supervisor', NOW())");
        $eStmt->execute([$personId, $empCode]);
        $supervisorEmpId = (int)$pdo->lastInsertId();

        $catId = (int)$pdo->query("SELECT id FROM complaint_categories WHERE slug = 'cleanliness'")->fetchColumn();
        $subId = (int)$pdo->query("SELECT id FROM complaint_subcategories WHERE slug = 'dustbin_overflow'")->fetchColumn();
        $wardId = (int)$pdo->query("SELECT id FROM wards WHERE ward_number = 1")->fetchColumn();
        $deptId = (int)$pdo->query("SELECT id FROM departments WHERE slug = 'waste_management'")->fetchColumn();

        $ruleId = 0;
        $complaintId = 0;
        try {
            // 3. Configure deterministic rule assigning Supervisor 1 to Cleanliness in Ward 1
            $ruleId = $this->routingConfigService->createRoutingRule([
                'category_id' => $catId,
                'subcategory_id' => $subId,
                'ward_id' => $wardId,
                'department_id' => $deptId,
                'assigned_supervisor_employee_id' => $supervisorEmpId,
                'effective_from' => '2026-01-01 00:00:00',
            ]);

            // 4. Submit Complaint
            $complaint = $this->complaintService->createComplaint([
                'citizen_user_id' => $citizenUserId,
                'category_id' => $catId,
                'subcategory_id' => $subId,
                'ward_id' => $wardId,
                'description' => 'টেস্ট স্বয়ংক্রিয় রাউটিং অভিযোগ',
            ]);

            $complaintId = (int)$complaint['id'];

            // 5. Verify Automatic Routing Results
            $this->assertEquals($deptId, (int)$complaint['department_id']);
            $this->assertEquals($supervisorEmpId, (int)$complaint['current_supervisor_employee_id']);
            $this->assertEquals('assigned', $complaint['internal_status']);
            $this->assertEquals('assigned', $complaint['citizen_status']);

            // 6. Verify Ownership History
            $own = $pdo->query("SELECT * FROM complaint_ownership_history WHERE complaint_id = {$complaintId}")->fetch(PDO::FETCH_ASSOC);
            $this->assertNotNull($own);
            $this->assertEquals($supervisorEmpId, (int)$own['to_supervisor_employee_id']);
            $this->assertEquals($deptId, (int)$own['to_department_id']);
        } finally {
            if ($ruleId) {
                $pdo->exec("DELETE FROM routing_rules WHERE id = {$ruleId}");
            }
            $pdo->exec("DELETE FROM complaint_ownership_history WHERE complaint_id IN (SELECT id FROM complaints WHERE citizen_user_id = {$citizenUserId}) OR transferred_by_user_id = {$citizenUserId}");
            $pdo->exec("DELETE FROM complaint_status_history WHERE complaint_id IN (SELECT id FROM complaints WHERE citizen_user_id = {$citizenUserId}) OR actor_user_id = {$citizenUserId}");
            $pdo->exec("DELETE FROM complaint_locations WHERE complaint_id IN (SELECT id FROM complaints WHERE citizen_user_id = {$citizenUserId})");
            $pdo->exec("DELETE FROM complaints WHERE citizen_user_id = {$citizenUserId}");
            $pdo->exec("DELETE FROM employees WHERE id = {$supervisorEmpId}");
            $pdo->exec("DELETE FROM persons WHERE id = {$personId}");
            $pdo->exec("DELETE FROM users WHERE id = {$citizenUserId}");
        }
    }
}
