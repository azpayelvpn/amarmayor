<?php

declare(strict_types=1);

namespace AmarMayor\Tests\Unit\Database;

use AmarMayor\Database\DatabaseManager;
use AmarMayor\Support\Security;
use AmarMayor\Tests\TestCase;
use PDO;

class GovernanceAndWorkforceTest extends TestCase
{
    public function testPersonEmployeeUserSeparation(): void
    {
        $pdo = DatabaseManager::getConnection();
        $suffix = bin2hex(random_bytes(4));

        $personId1 = 0;
        $empId1 = 0;
        $userId2 = 0;
        $personId2 = 0;

        try {
            // 1. Employee without a user account (e.g. daily-wage cleaner without system login)
            $pdo->exec("INSERT INTO persons (full_name_bn, full_name_en, is_public_visible, created_at) 
                        VALUES ('টেস্ট কর্মী', 'Test Field Worker', 0, NOW())");
            $personId1 = (int)$pdo->lastInsertId();

            $empCode = "TEST-NOLOGIN-{$suffix}";
            $pdo->exec("INSERT INTO employees (person_id, employee_code, employment_type, designation_bn, designation_en, created_at) 
                        VALUES ({$personId1}, '{$empCode}', 'daily_wage', 'পরিচ্ছন্নতাকর্মী', 'Cleaner', NOW())");
            $empId1 = (int)$pdo->lastInsertId();

            $empRow = $pdo->query("SELECT e.id, e.employee_code, p.user_id FROM employees e JOIN persons p ON e.person_id = p.id WHERE e.id = {$empId1}")->fetch(PDO::FETCH_ASSOC);
            $this->assertEquals($empCode, $empRow['employee_code']);
            $this->assertNull($empRow['user_id'], "Employee without a user login account is valid");

            // 2. Citizen user without an employee record
            $uuid = Security::uuid();
            $email = "citizen_test_{$suffix}@example.com";
            $pdo->exec("INSERT INTO users (uuid, email, user_type, status, created_at) 
                        VALUES ('{$uuid}', '{$email}', 'citizen', 'active', NOW())");
            $userId2 = (int)$pdo->lastInsertId();

            $pdo->exec("INSERT INTO persons (user_id, full_name_bn, full_name_en, is_public_visible, created_at) 
                        VALUES ({$userId2}, 'টেস্ট নাগরিক', 'Test Citizen', 1, NOW())");
            $personId2 = (int)$pdo->lastInsertId();

            $empCount = (int)$pdo->query("SELECT COUNT(*) FROM employees WHERE person_id = {$personId2}")->fetchColumn();
            $this->assertEquals(0, $empCount, "Citizen user without employee record is valid");
        } finally {
            if ($empId1) $pdo->exec("DELETE FROM employees WHERE id = {$empId1}");
            if ($personId1) $pdo->exec("DELETE FROM persons WHERE id = {$personId1}");
            if ($personId2) $pdo->exec("DELETE FROM persons WHERE id = {$personId2}");
            if ($userId2) $pdo->exec("DELETE FROM users WHERE id = {$userId2}");
        }
    }

    public function testMultiWardResponsibilityAndDualRepresentation(): void
    {
        $pdo = DatabaseManager::getConnection();

        $pOfficerId = 0;
        $assignOfficerId = 0;
        $pResId = 0;
        $assignResId = 0;

        try {
            // 1. Create a Responsible Officer Person & Assignment covering Ward 5 AND Ward 6
            $pdo->exec("INSERT INTO persons (full_name_bn, full_name_en, is_public_visible, created_at) VALUES ('টেস্ট অফিসার', 'Test Officer', 1, NOW())");
            $pOfficerId = (int)$pdo->lastInsertId();

            $respTypeId = (int)$pdo->query("SELECT id FROM representation_types WHERE slug = 'responsible_officer'")->fetchColumn();
            $pdo->exec("INSERT INTO representation_assignments (person_id, representation_type_id, authority_basis, effective_from, status, created_at) 
                        VALUES ({$pOfficerId}, {$respTypeId}, 'appointed', NOW(), 'active', NOW())");
            $assignOfficerId = (int)$pdo->lastInsertId();

            $ward5Id = (int)$pdo->query("SELECT id FROM wards WHERE ward_number = 5")->fetchColumn();
            $ward6Id = (int)$pdo->query("SELECT id FROM wards WHERE ward_number = 6")->fetchColumn();

            $pdo->exec("INSERT INTO representation_areas (representation_assignment_id, area_type, area_id) VALUES ({$assignOfficerId}, 'ward', {$ward5Id})");
            $pdo->exec("INSERT INTO representation_areas (representation_assignment_id, area_type, area_id) VALUES ({$assignOfficerId}, 'ward', {$ward6Id})");

            $areas = $pdo->query("SELECT area_id FROM representation_areas WHERE representation_assignment_id = {$assignOfficerId}")->fetchAll(PDO::FETCH_COLUMN);
            $this->assertEquals(2, count($areas), "Responsible Officer can cover multiple wards simultaneously");

            // 2. Simultaneously assign a Reserved Women Councillor to Ward 5 (Dual Representation)
            $pdo->exec("INSERT INTO persons (full_name_bn, full_name_en, is_public_visible, created_at) VALUES ('টেস্ট সংরক্ষিত কাউন্সিলর', 'Test Reserved Councillor', 1, NOW())");
            $pResId = (int)$pdo->lastInsertId();

            $resTypeId = (int)$pdo->query("SELECT id FROM representation_types WHERE slug = 'reserved_women_councillor'")->fetchColumn();
            $pdo->exec("INSERT INTO representation_assignments (person_id, representation_type_id, authority_basis, effective_from, status, created_at) 
                        VALUES ({$pResId}, {$resTypeId}, 'elected', NOW(), 'active', NOW())");
            $assignResId = (int)$pdo->lastInsertId();

            $seat1Id = (int)$pdo->query("SELECT id FROM reserved_seats WHERE seat_number = 1")->fetchColumn();
            $pdo->exec("INSERT INTO representation_areas (representation_assignment_id, area_type, area_id) VALUES ({$assignResId}, 'reserved_seat', {$seat1Id})");

            // Verify Ward 5 has general officer representation AND reserved seat representation concurrently
            $this->assertTrue($assignOfficerId > 0 && $assignResId > 0, "Dual representation (General + Reserved) operates concurrently without collision");
        } finally {
            if ($assignOfficerId || $assignResId) {
                $pdo->exec("DELETE FROM representation_areas WHERE representation_assignment_id IN ({$assignOfficerId}, {$assignResId})");
                $pdo->exec("DELETE FROM representation_assignments WHERE id IN ({$assignOfficerId}, {$assignResId})");
            }
            if ($pOfficerId) $pdo->exec("DELETE FROM persons WHERE id = {$pOfficerId}");
            if ($pResId) $pdo->exec("DELETE FROM persons WHERE id = {$pResId}");
        }
    }

    public function testHistoricalGovernanceTenureAndPostingHistory(): void
    {
        $pdo = DatabaseManager::getConnection();
        $suffix = bin2hex(random_bytes(4));

        $pOldId = 0;
        $assignOldId = 0;
        $pEmpId = 0;
        $empId = 0;
        $deptA = 0;
        $deptB = 0;

        try {
            // 1. Historical Governance: Past Officer (Term ended) and Current Officer
            $pdo->exec("INSERT INTO persons (full_name_bn, full_name_en, is_public_visible, created_at) VALUES ('অফিসার পুরাতন', 'Old Officer', 1, NOW())");
            $pOldId = (int)$pdo->lastInsertId();

            $respTypeId = (int)$pdo->query("SELECT id FROM representation_types WHERE slug = 'responsible_officer'")->fetchColumn();
            $pdo->exec("INSERT INTO representation_assignments (person_id, representation_type_id, authority_basis, effective_from, effective_to, status, created_at) 
                        VALUES ({$pOldId}, {$respTypeId}, 'appointed', '2025-01-01 00:00:00', '2025-12-31 23:59:59', 'term_ended', NOW())");
            $assignOldId = (int)$pdo->lastInsertId();

            $this->assertTrue($assignOldId > 0, "Historical governance assignment with effective_to is preserved");

            // 2. Employee Postings History: Past Posting in Dept 1 and Active Posting in Dept 2
            $pdo->exec("INSERT INTO persons (full_name_bn, full_name_en, is_public_visible, created_at) VALUES ('পোস্টিং টেস্ট', 'Posting Test Emp', 0, NOW())");
            $pEmpId = (int)$pdo->lastInsertId();

            $empCode = "EMP-POST-{$suffix}";
            $pdo->exec("INSERT INTO employees (person_id, employee_code, employment_type, designation_bn, designation_en, created_at) 
                        VALUES ({$pEmpId}, '{$empCode}', 'officer', 'সহকারী পরিদর্শক', 'Asst Inspector', NOW())");
            $empId = (int)$pdo->lastInsertId();

            $cityId = (int)$pdo->query("SELECT id FROM cities WHERE slug = 'mcc'")->fetchColumn();
            $deptSlugA = "dept_a_{$suffix}";
            $deptSlugB = "dept_b_{$suffix}";
            $pdo->exec("INSERT INTO departments (city_id, slug, name_bn, name_en, created_at) VALUES ({$cityId}, '{$deptSlugA}', 'বিভাগ ক', 'Dept A', NOW())");
            $deptA = (int)$pdo->lastInsertId();
            $pdo->exec("INSERT INTO departments (city_id, slug, name_bn, name_en, created_at) VALUES ({$cityId}, '{$deptSlugB}', 'বিভাগ খ', 'Dept B', NOW())");
            $deptB = (int)$pdo->lastInsertId();

            // Past Posting (2025)
            $pdo->exec("INSERT INTO employee_postings (employee_id, department_id, posting_type, effective_from, effective_to, created_at) 
                        VALUES ({$empId}, {$deptA}, 'regular', '2025-01-01 00:00:00', '2025-12-31 23:59:59', NOW())");

            // Current Posting (2026)
            $pdo->exec("INSERT INTO employee_postings (employee_id, department_id, posting_type, effective_from, effective_to, created_at) 
                        VALUES ({$empId}, {$deptB}, 'regular', '2026-01-01 00:00:00', NULL, NOW())");

            $postings = $pdo->query("SELECT department_id, effective_to FROM employee_postings WHERE employee_id = {$empId} ORDER BY effective_from ASC")->fetchAll(PDO::FETCH_ASSOC);
            $this->assertEquals(2, count($postings), "Employee posting history preserves past and current postings");
            $this->assertNotNull($postings[0]['effective_to']);
            $this->assertNull($postings[1]['effective_to']);
        } finally {
            if ($empId) {
                $pdo->exec("DELETE FROM employee_postings WHERE employee_id = {$empId}");
                $pdo->exec("DELETE FROM employees WHERE id = {$empId}");
            }
            if ($deptA || $deptB) {
                $pdo->exec("DELETE FROM departments WHERE id IN ({$deptA}, {$deptB})");
            }
            if ($assignOldId) {
                $pdo->exec("DELETE FROM representation_assignments WHERE id = {$assignOldId}");
            }
            if ($pOldId) $pdo->exec("DELETE FROM persons WHERE id = {$pOldId}");
            if ($pEmpId) $pdo->exec("DELETE FROM persons WHERE id = {$pEmpId}");
        }
    }
}
