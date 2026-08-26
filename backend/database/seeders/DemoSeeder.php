<?php

declare(strict_types=1);

namespace AmarMayor\Database\Seeders;

use AmarMayor\Database\DatabaseManager;
use AmarMayor\Support\Security;
use PDO;

/**
 * Demo Seeder: Fictional Data for Local Development & Testing.
 * Critical Rules:
 * 1. Clearly fictional names and markers (e.g. 'Demo ...').
 * 2. Never use real government identities or verified personal data.
 * 3. Strictly separated from official structural seeds.
 */
class DemoSeeder
{
    public static function run(): void
    {
        DatabaseManager::transaction(function (PDO $pdo) {
            $cityId = (int)$pdo->query("SELECT id FROM cities WHERE slug = 'mcc'")->fetchColumn();
            if (!$cityId) {
                return;
            }

            // 1. Seed Demo Departments
            $deptStmt = $pdo->prepare("SELECT id FROM departments WHERE city_id = ? AND slug = ?");
            $deptStmt->execute([$cityId, 'waste_management']);
            $wasteDeptId = $deptStmt->fetchColumn();

            if (!$wasteDeptId) {
                $insDept = $pdo->prepare("INSERT INTO departments (city_id, slug, name_bn, name_en, official_phone, status, created_at) VALUES (?, 'waste_management', 'বর্জ্য ব্যবস্থাপনা বিভাগ', 'Waste Management Department', '+8809166661', 'active', NOW())");
                $insDept->execute([$cityId]);
                $wasteDeptId = (int)$pdo->lastInsertId();
            }

            // 2. Seed Demo Fictional Persons & Users
            $demoProfiles = [
                ['user_type' => 'staff', 'role' => 'field_supervisor', 'name_bn' => 'ডেমো সুপারভাইজার ক', 'name_en' => 'Demo Supervisor A', 'code' => 'DEMO-EMP-001', 'desig_bn' => 'পরিচ্ছন্নতা পরিদর্শক', 'desig_en' => 'Cleanliness Inspector'],
                ['user_type' => 'staff', 'role' => 'field_worker', 'name_bn' => 'ডেমো মাঠকর্মী ক', 'name_en' => 'Demo Field Worker A', 'code' => 'DEMO-EMP-002', 'desig_bn' => 'সড়ক পরিচ্ছন্নতাকর্মী', 'desig_en' => 'Street Cleaner'],
                ['user_type' => 'representative', 'role' => 'ward_responsible_officer', 'name_bn' => 'ডেমো দায়িত্বপ্রাপ্ত কর্মকর্তা ক', 'name_en' => 'Demo Responsible Officer A', 'code' => 'DEMO-EMP-003', 'desig_bn' => 'সহকারী প্রকৌশলী', 'desig_en' => 'Assistant Engineer'],
                ['user_type' => 'representative', 'role' => 'general_councillor', 'name_bn' => 'ডেমো সাধারণ কাউন্সিলর ক', 'name_en' => 'Demo General Councillor A', 'code' => 'DEMO-REP-001', 'desig_bn' => 'কাউন্সিলর (ওয়ার্ড ১৯)', 'desig_en' => 'Councillor (Ward 19)'],
                ['user_type' => 'representative', 'role' => 'reserved_councillor', 'name_bn' => 'ডেমো সংরক্ষিত কাউন্সিলর ক', 'name_en' => 'Demo Reserved Councillor A', 'code' => 'DEMO-REP-002', 'desig_bn' => 'সংরক্ষিত নারী কাউন্সিলর', 'desig_en' => 'Reserved Women Councillor'],
                ['user_type' => 'citizen', 'role' => 'citizen', 'name_bn' => 'ডেমো নাগরিক ক', 'name_en' => 'Demo Citizen A', 'code' => null, 'desig_bn' => 'নাগরিক', 'desig_en' => 'Citizen'],
            ];

            $seededEmployees = [];

            foreach ($demoProfiles as $p) {
                // Check if user already exists
                $email = strtolower(str_replace(' ', '_', $p['name_en'])) . '@demo.amarmayor.local';
                $userStmt = $pdo->prepare("SELECT id FROM users WHERE email = ?");
                $userStmt->execute([$email]);
                $userId = $userStmt->fetchColumn();

                if (!$userId) {
                    $uUuid = Security::uuid();
                    $insUser = $pdo->prepare("INSERT INTO users (uuid, email, user_type, status, preferred_language, password_hash, created_at) VALUES (?, ?, ?, 'active', 'bn', ?, NOW())");
                    $insUser->execute([$uUuid, $email, $p['user_type'], Security::hashPassword('DemoSecret123!')]);
                    $userId = (int)$pdo->lastInsertId();

                    // Assign role
                    $roleId = (int)$pdo->query("SELECT id FROM roles WHERE slug = '{$p['role']}'")->fetchColumn();
                    if ($roleId) {
                        $insUR = $pdo->prepare("INSERT INTO user_roles (user_id, role_id) VALUES (?, ?)");
                        $insUR->execute([$userId, $roleId]);
                    }
                }

                // Create Person
                $personStmt = $pdo->prepare("SELECT id FROM persons WHERE user_id = ?");
                $personStmt->execute([$userId]);
                $personId = $personStmt->fetchColumn();

                if (!$personId) {
                    $insPerson = $pdo->prepare("INSERT INTO persons (user_id, full_name_bn, full_name_en, is_public_visible, created_at) VALUES (?, ?, ?, 1, NOW())");
                    $insPerson->execute([$userId, $p['name_bn'], $p['name_en']]);
                    $personId = (int)$pdo->lastInsertId();
                }

                // If employee code present, create employee
                if ($p['code']) {
                    $empStmt = $pdo->prepare("SELECT id FROM employees WHERE employee_code = ?");
                    $empStmt->execute([$p['code']]);
                    $empId = $empStmt->fetchColumn();

                    if (!$empId) {
                        $insEmp = $pdo->prepare("INSERT INTO employees (person_id, employee_code, employment_type, designation_bn, designation_en, duty_status, created_at) VALUES (?, ?, 'permanent', ?, ?, 'available', NOW())");
                        $insEmp->execute([$personId, $p['code'], $p['desig_bn'], $p['desig_en']]);
                        $empId = (int)$pdo->lastInsertId();
                    }
                    $seededEmployees[$p['role']] = (int)$empId;
                }
            }

            // 3. Seed Demo Team
            $teamStmt = $pdo->prepare("SELECT id FROM teams WHERE name_en = 'Demo Waste Team 19'");
            $teamStmt->execute();
            $teamId = $teamStmt->fetchColumn();

            if (!$teamId && isset($seededEmployees['field_supervisor'], $seededEmployees['field_worker'])) {
                $ward19Id = (int)$pdo->query("SELECT id FROM wards WHERE ward_number = 19")->fetchColumn();
                $insTeam = $pdo->prepare("INSERT INTO teams (department_id, ward_id, name_bn, name_en, supervisor_employee_id, team_leader_employee_id, status, created_at) VALUES (?, ?, 'ডেমো বর্জ্য দল ১৯', 'Demo Waste Team 19', ?, ?, 'active', NOW())");
                $insTeam->execute([(int)$wasteDeptId, $ward19Id, $seededEmployees['field_supervisor'], $seededEmployees['field_worker']]);
                $teamId = (int)$pdo->lastInsertId();

                // Add member
                $insTM = $pdo->prepare("INSERT INTO team_members (team_id, employee_id, effective_from, created_at) VALUES (?, ?, NOW(), NOW())");
                $insTM->execute([$teamId, $seededEmployees['field_worker']]);
            }
        });
    }
}
