<?php

declare(strict_types=1);

namespace AmarMayor\Tests\Unit\Domain;

use AmarMayor\Database\DatabaseManager;
use AmarMayor\Domain\Workforce\WorkforceService;
use AmarMayor\Tests\TestCase;
use PDO;

class WorkforceServiceTest extends TestCase
{
    private WorkforceService $workforceService;

    public function setUp(): void
    {
        parent::setUp();
        $this->workforceService = new WorkforceService();
    }

    public function testGetDepartmentsWithServiceUnits(): void
    {
        $departments = $this->workforceService->getDepartments(true);
        $this->assert(count($departments) >= 6, "Must contain all core MCC departments");

        $wasteDept = null;
        foreach ($departments as $dept) {
            if ($dept['slug'] === 'waste_management' || str_contains($dept['name_en'], 'Waste')) {
                $wasteDept = $dept;
                break;
            }
        }

        $this->assertNotNull($wasteDept);
        $this->assertNotNull($wasteDept['service_units']);
    }

    public function testCreateEmployeeAndTransfer(): void
    {
        $pdo = DatabaseManager::getConnection();
        $dept1Id = (int)$pdo->query("SELECT id FROM departments LIMIT 1")->fetchColumn();
        $dept2Id = (int)$pdo->query("SELECT id FROM departments ORDER BY id DESC LIMIT 1")->fetchColumn();
        $ward1Id = (int)$pdo->query("SELECT id FROM wards WHERE ward_number = 1")->fetchColumn();

        $employeeId = 0;
        try {
            $employeeId = $this->workforceService->createEmployee([
                'full_name_bn' => 'আফজাল হোসেন',
                'full_name_en' => 'Afzal Hossain',
                'phone' => '01711223344',
                'employee_code' => 'EMP-TEST-' . random_int(1000, 9999),
                'designation_bn' => 'পরিচ্ছন্নতা পরিদর্শক',
                'designation_en' => 'Conservancy Inspector',
                'employment_type' => 'permanent',
                'duty_status' => 'on_duty',
                'department_id' => $dept1Id,
                'ward_id' => $ward1Id,
                'posting_order_number' => 'MCC-POST-01',
                'effective_from' => '2026-01-01 00:00:00',
            ]);

            $this->assert($employeeId > 0);

            // Update duty status
            $this->workforceService->updateEmployeeDutyStatus($employeeId, 'on_leave');
            $dutyStatus = $pdo->query("SELECT duty_status FROM employees WHERE id = {$employeeId}")->fetchColumn();
            $this->assertEquals('on_leave', $dutyStatus);

            // Transfer employee to Dept 2
            $this->workforceService->transferEmployee(
                $employeeId,
                $dept2Id,
                null,
                null,
                null,
                'MCC-TRANSFER-01',
                '2026-08-26 12:00:00'
            );

            // Verify active posting is Dept 2 and previous posting has effective_to
            $postings = $pdo->query("
                SELECT department_id, effective_from, effective_to 
                FROM employee_postings 
                WHERE employee_id = {$employeeId} 
                ORDER BY id ASC
            ")->fetchAll(PDO::FETCH_ASSOC);

            $this->assertCount(2, $postings);
            $this->assertEquals('2026-08-26 12:00:00', $postings[0]['effective_to']);
            $this->assertNull($postings[1]['effective_to']);
            $this->assertEquals($dept2Id, (int)$postings[1]['department_id']);
        } finally {
            if ($employeeId) {
                $pdo->exec("DELETE FROM employee_postings WHERE employee_id = {$employeeId}");
                $pdo->exec("DELETE FROM employees WHERE id = {$employeeId}");
            }
        }
    }

    public function testCreateTeamAndQuery(): void
    {
        $pdo = DatabaseManager::getConnection();
        $deptId = (int)$pdo->query("SELECT id FROM departments LIMIT 1")->fetchColumn();
        $ward1Id = (int)$pdo->query("SELECT id FROM wards WHERE ward_number = 1")->fetchColumn();
        $ward2Id = (int)$pdo->query("SELECT id FROM wards WHERE ward_number = 2")->fetchColumn();

        $teamId = 0;
        try {
            $teamId = $this->workforceService->createTeam(
                'দ্রুত রেসপন্স টিম ১',
                'Quick Response Team 1',
                $deptId,
                null,
                null,
                [],
                [$ward1Id, $ward2Id]
            );

            $this->assert($teamId > 0);

            // Query teams for Ward 1
            $ward1Teams = $this->workforceService->getTeams(null, $ward1Id);
            $found = false;
            foreach ($ward1Teams as $t) {
                if ($t['id'] === $teamId) {
                    $found = true;
                    $this->assertCount(2, $t['covered_wards']);
                }
            }
            $this->assertTrue($found, "Team must cover Ward 1");
        } finally {
            if ($teamId) {
                $pdo->exec("DELETE FROM team_coverage WHERE team_id = {$teamId}");
                $pdo->exec("DELETE FROM team_members WHERE team_id = {$teamId}");
                $pdo->exec("DELETE FROM teams WHERE id = {$teamId}");
            }
        }
    }
}
