<?php

declare(strict_types=1);

namespace AmarMayor\Database\Seeders;

use AmarMayor\Database\DatabaseManager;
use AmarMayor\Support\Config;
use AmarMayor\Support\Security;
use PDO;
use RuntimeException;

/**
 * Complete Fictional Development Demo Seeder.
 * Generates test identities for ALL 22 canonical system roles.
 * Strictly blocked in production.
 */
class DemoSeeder
{
    public const DEMO_PASSWORD = 'Demo@12345';

    public static function run(): void
    {
        if (Config::get('app.env') === 'production') {
            throw new RuntimeException("DemoSeeder is strictly prohibited in production environments!");
        }

        DatabaseManager::transaction(function (PDO $pdo) {
            $cityId = (int)$pdo->query("SELECT id FROM cities WHERE slug = 'mcc'")->fetchColumn();
            if (!$cityId) {
                return;
            }

            $passwordHash = Security::hashPassword(self::DEMO_PASSWORD);

            // 1. All 22 Canonical Roles Definition
            $rolesList = [
                ['slug' => 'public_viewer', 'email' => 'demo.public_viewer@demo.local', 'phone' => '01711000022', 'name_bn' => 'ডেমো পাবলিক ভিউয়ার', 'name_en' => 'Demo Public Viewer', 'type' => 'staff', 'desig_bn' => 'পর্যবেক্ষক', 'desig_en' => 'Observer'],
                ['slug' => 'citizen', 'email' => 'demo.citizen@demo.local', 'phone' => '01711000001', 'name_bn' => 'ডেমো নাগরিক ক', 'name_en' => 'Demo Citizen A', 'type' => 'citizen', 'desig_bn' => 'নাগরিক', 'desig_en' => 'Citizen'],
                ['slug' => 'citizen', 'email' => 'demo.citizen_b@demo.local', 'phone' => '01711000002', 'name_bn' => 'ডেমো নাগরিক খ', 'name_en' => 'Demo Citizen B', 'type' => 'citizen', 'desig_bn' => 'নাগরিক', 'desig_en' => 'Citizen'],
                ['slug' => 'mayor', 'email' => 'demo.mayor@demo.local', 'phone' => '01711000003', 'name_bn' => 'ডেমো মেয়র', 'name_en' => 'Demo Mayor', 'type' => 'staff', 'desig_bn' => 'মেয়র', 'desig_en' => 'City Mayor'],
                ['slug' => 'administrator', 'email' => 'demo.administrator@demo.local', 'phone' => '01711000004', 'name_bn' => 'ডেমো প্রশাসক', 'name_en' => 'Demo Administrator', 'type' => 'staff', 'desig_bn' => 'প্রশাসক', 'desig_en' => 'City Administrator'],
                ['slug' => 'ceo', 'email' => 'demo.ceo@demo.local', 'phone' => '01711000005', 'name_bn' => 'ডেমো প্রধান নির্বাহী কর্মকর্তা', 'name_en' => 'Demo Chief Executive Officer', 'type' => 'staff', 'desig_bn' => 'প্রধান নির্বাহী কর্মকর্তা (সিইও)', 'desig_en' => 'Chief Executive Officer'],
                ['slug' => 'general_councillor', 'email' => 'demo.general_councillor@demo.local', 'phone' => '01711000006', 'name_bn' => 'ডেমো সাধারণ কাউন্সিলর', 'name_en' => 'Demo General Councillor', 'type' => 'representative', 'desig_bn' => 'কাউন্সিলর (ওয়ার্ড ১)', 'desig_en' => 'Ward Councillor (Ward 1)'],
                ['slug' => 'reserved_women_councillor', 'email' => 'demo.reserved_women_councillor@demo.local', 'phone' => '01711000007', 'name_bn' => 'ডেমো সংরক্ষিত নারী কাউন্সিলর', 'name_en' => 'Demo Reserved Women Councillor', 'type' => 'representative', 'desig_bn' => 'সংরক্ষিত নারী কাউন্সিলর (যাচাইকরণাধীন আসন)', 'desig_en' => 'Reserved Women Councillor (Unassigned/Pending Gazette)'],
                ['slug' => 'responsible_officer', 'email' => 'demo.responsible_officer@demo.local', 'phone' => '01711000008', 'name_bn' => 'ডেমো দায়িত্বপ্রাপ্ত কর্মকর্তা', 'name_en' => 'Demo Responsible Officer', 'type' => 'representative', 'desig_bn' => 'দায়িত্বপ্রাপ্ত কর্মকর্তা (ওয়ার্ড ২)', 'desig_en' => 'Responsible Officer (Ward 2)'],
                ['slug' => 'department_head', 'email' => 'demo.department_head@demo.local', 'phone' => '01711000009', 'name_bn' => 'ডেমো বিভাগীয় প্রধান', 'name_en' => 'Demo Department Head', 'type' => 'staff', 'desig_bn' => 'প্রধান বর্জ্য ব্যবস্থাপনা কর্মকর্তা', 'desig_en' => 'Chief Waste Management Officer'],
                ['slug' => 'department_officer', 'email' => 'demo.department_officer@demo.local', 'phone' => '01711000010', 'name_bn' => 'ডেমো বিভাগীয় কর্মকর্তা', 'name_en' => 'Demo Department Officer', 'type' => 'staff', 'desig_bn' => 'সহকারী বর্জ্য ব্যবস্থাপনা কর্মকর্তা', 'desig_en' => 'Assistant Waste Officer'],
                ['slug' => 'zone_officer', 'email' => 'demo.zone_officer@demo.local', 'phone' => '01711000011', 'name_bn' => 'ডেমো আঞ্চলিক নির্বাহী কর্মকর্তা', 'name_en' => 'Demo Zone Officer', 'type' => 'staff', 'desig_bn' => 'আঞ্চলিক নির্বাহী কর্মকর্তা (অঞ্চল ১)', 'desig_en' => 'Zonal Executive Officer (Zone 1)'],
                ['slug' => 'ward_officer', 'email' => 'demo.ward_officer@demo.local', 'phone' => '01711000012', 'name_bn' => 'ডেমো ওয়ার্ড সচিব / কর্মকর্তা', 'name_en' => 'Demo Ward Officer', 'type' => 'staff', 'desig_bn' => 'ওয়ার্ড সচিব (ওয়ার্ড ১)', 'desig_en' => 'Ward Secretary (Ward 1)'],
                ['slug' => 'supervisor', 'email' => 'demo.supervisor@demo.local', 'phone' => '01711000013', 'name_bn' => 'ডেমো সুপারভাইজার', 'name_en' => 'Demo Supervisor', 'type' => 'staff', 'desig_bn' => 'পরিচ্ছন্নতা পরিদর্শক', 'desig_en' => 'Sanitation Inspector'],
                ['slug' => 'team_leader', 'email' => 'demo.team_leader@demo.local', 'phone' => '01711000014', 'name_bn' => 'ডেমো দলনেতা', 'name_en' => 'Demo Team Leader', 'type' => 'staff', 'desig_bn' => 'মাঠ দলনেতা', 'desig_en' => 'Field Team Leader'],
                ['slug' => 'field_worker', 'email' => 'demo.field_worker@demo.local', 'phone' => '01711000015', 'name_bn' => 'ডেমো মাঠকর্মী', 'name_en' => 'Demo Field Worker', 'type' => 'staff', 'desig_bn' => 'সড়ক পরিচ্ছন্নতাকর্মী', 'desig_en' => 'Street Cleaner'],
                ['slug' => 'call_center_operator', 'email' => 'demo.call_center_operator@demo.local', 'phone' => '01711000016', 'name_bn' => 'ডেমো কল সেন্টার অপারেটর', 'name_en' => 'Demo Call Center Operator', 'type' => 'staff', 'desig_bn' => 'অভিযোগ গ্রহণকারী অপারেটর', 'desig_en' => 'Intake Operator'],
                ['slug' => 'control_room_officer', 'email' => 'demo.control_room_officer@demo.local', 'phone' => '01711000017', 'name_bn' => 'ডেমো কন্ট্রোল রুম কর্মকর্তা', 'name_en' => 'Demo Control Room Officer', 'type' => 'staff', 'desig_bn' => 'কন্ট্রোল রুম সমন্বয়ক', 'desig_en' => 'Control Room Coordinator'],
                ['slug' => 'public_info_officer', 'email' => 'demo.public_info_officer@demo.local', 'phone' => '01711000018', 'name_bn' => 'ডেমো জনসংযোগ কর্মকর্তা', 'name_en' => 'Demo Public Information Officer', 'type' => 'staff', 'desig_bn' => 'জনসংযোগ কর্মকর্তা', 'desig_en' => 'Public Relations Officer'],
                ['slug' => 'data_monitoring_officer', 'email' => 'demo.data_monitoring_officer@demo.local', 'phone' => '01711000019', 'name_bn' => 'ডেমো ডাটা ও মনিটরিং কর্মকর্তা', 'name_en' => 'Demo Data & Monitoring Officer', 'type' => 'staff', 'desig_bn' => 'আইটি ও পরিসংখ্যান কর্মকর্তা', 'desig_en' => 'IT & Statistics Officer'],
                ['slug' => 'auditor', 'email' => 'demo.auditor@demo.local', 'phone' => '01711000020', 'name_bn' => 'ডেমো অডিটর', 'name_en' => 'Demo Auditor', 'type' => 'staff', 'desig_bn' => 'অভ্যন্তরীণ নিরীক্ষক', 'desig_en' => 'Internal Auditor'],
                ['slug' => 'platform_super_admin', 'email' => 'demo.platform_super_admin@demo.local', 'phone' => '01711000021', 'name_bn' => 'ডেমো প্ল্যাটফর্ম সুপার অ্যাডমিন', 'name_en' => 'Demo Platform Super Admin', 'type' => 'staff', 'desig_bn' => 'প্ল্যাটফর্ম প্রশাসক', 'desig_en' => 'Platform Administrator'],
                ['slug' => 'technical_super_admin', 'email' => 'demo.technical_super_admin@demo.local', 'phone' => '01711000000', 'name_bn' => 'ডেমো টেকনিক্যাল সুপার অ্যাডমিন', 'name_en' => 'Demo Technical Super Admin', 'type' => 'staff', 'desig_bn' => 'সিস্টেম ইঞ্জিনিয়ার', 'desig_en' => 'Lead System Engineer'],
            ];

            $seededUserIds = [];
            $seededEmployeeIds = [];

            foreach ($rolesList as $r) {
                $roleId = (int)$pdo->query("SELECT id FROM roles WHERE slug = '{$r['slug']}' LIMIT 1")->fetchColumn();
                if (!$roleId) {
                    continue;
                }

                $lookupHash = Security::phoneLookupHash($r['phone']);

                // Find or insert User
                $uStmt = $pdo->prepare("SELECT id FROM users WHERE email = ? OR phone = ? LIMIT 1");
                $uStmt->execute([$r['email'], $r['phone']]);
                $userId = $uStmt->fetchColumn();

                if (!$userId) {
                    $uUuid = Security::uuid();
                    $insU = $pdo->prepare("
                        INSERT INTO users (uuid, email, phone, phone_lookup_hash, user_type, status, preferred_language, password_hash, created_at)
                        VALUES (?, ?, ?, ?, ?, 'active', 'bn', ?, NOW())
                    ");
                    $insU->execute([$uUuid, $r['email'], $r['phone'], $lookupHash, $r['type'], $passwordHash]);
                    $userId = (int)$pdo->lastInsertId();
                } else {
                    $userId = (int)$userId;
                    $pdo->prepare("UPDATE users SET password_hash = ?, user_type = ?, status = 'active' WHERE id = ?")
                        ->execute([$passwordHash, $r['type'], $userId]);
                }

                $seededUserIds[$r['slug']] = $userId;

                // Bind Role
                $urExists = (bool)$pdo->query("SELECT 1 FROM user_roles WHERE user_id = {$userId} AND role_id = {$roleId}")->fetchColumn();
                if (!$urExists) {
                    $pdo->prepare("INSERT INTO user_roles (user_id, role_id) VALUES (?, ?)")->execute([$userId, $roleId]);
                }

                // Person record
                $pStmt = $pdo->prepare("SELECT id FROM persons WHERE user_id = ? LIMIT 1");
                $pStmt->execute([$userId]);
                $personId = $pStmt->fetchColumn();

                if (!$personId) {
                    $insP = $pdo->prepare("INSERT INTO persons (user_id, full_name_bn, full_name_en, is_public_visible, created_at) VALUES (?, ?, ?, 1, NOW())");
                    $insP->execute([$userId, $r['name_bn'], $r['name_en']]);
                    $personId = (int)$pdo->lastInsertId();
                } else {
                    $personId = (int)$personId;
                    $pdo->prepare("UPDATE persons SET full_name_bn = ?, full_name_en = ? WHERE id = ?")
                        ->execute([$r['name_bn'], $r['name_en'], $personId]);
                }

                // Employee record for staff roles
                if ($r['type'] !== 'citizen') {
                    $eStmt = $pdo->prepare("SELECT id FROM employees WHERE person_id = ? LIMIT 1");
                    $eStmt->execute([$personId]);
                    $empId = $eStmt->fetchColumn();

                    $empCode = 'DEMO-' . strtoupper(substr($r['slug'], 0, 4)) . '-' . str_pad((string)$userId, 3, '0', STR_PAD_LEFT);
                    if (!$empId) {
                        $insE = $pdo->prepare("
                            INSERT INTO employees (person_id, employee_code, employment_type, designation_bn, designation_en, duty_status, created_at)
                            VALUES (?, ?, 'permanent', ?, ?, 'available', NOW())
                        ");
                        $insE->execute([$personId, $empCode, $r['desig_bn'], $r['desig_en']]);
                        $empId = (int)$pdo->lastInsertId();
                    } else {
                        $empId = (int)$empId;
                    }
                    $seededEmployeeIds[$r['slug']] = $empId;
                }
            }

            // 2. Seed Demo Team & Team Member
            $wasteDeptId = (int)$pdo->query("SELECT id FROM departments WHERE slug = 'waste_management' LIMIT 1")->fetchColumn();
            $ward1Id = (int)$pdo->query("SELECT id FROM wards WHERE ward_number = 1 LIMIT 1")->fetchColumn();

            if ($wasteDeptId && $ward1Id && isset($seededEmployeeIds['supervisor'], $seededEmployeeIds['field_worker'])) {
                $teamStmt = $pdo->prepare("SELECT id FROM teams WHERE name_en = 'Demo Sanitation Team 1' LIMIT 1");
                $teamStmt->execute();
                $teamId = $teamStmt->fetchColumn();

                if (!$teamId) {
                    $insT = $pdo->prepare("
                        INSERT INTO teams (department_id, ward_id, name_bn, name_en, supervisor_employee_id, team_leader_employee_id, status, created_at)
                        VALUES (?, ?, 'ডেমো পরিচ্ছন্নতা দল ১', 'Demo Sanitation Team 1', ?, ?, 'active', NOW())
                    ");
                    $insT->execute([$wasteDeptId, $ward1Id, $seededEmployeeIds['supervisor'], $seededEmployeeIds['field_worker']]);
                    $teamId = (int)$pdo->lastInsertId();

                    $insTM = $pdo->prepare("INSERT INTO team_members (team_id, employee_id, effective_from, created_at) VALUES (?, ?, NOW(), NOW())");
                    $insTM->execute([$teamId, $seededEmployeeIds['field_worker']]);
                } else {
                    $teamId = (int)$teamId;
                }

                // 3. Seed Sample Complaints and Tasks for immediate interactive testing
                $subcatId = (int)$pdo->query("SELECT id FROM complaint_subcategories LIMIT 1")->fetchColumn();
                $catId = (int)$pdo->query("SELECT category_id FROM complaint_subcategories WHERE id = {$subcatId}")->fetchColumn();
                $zone1Id = (int)$pdo->query("SELECT zone_id FROM wards WHERE id = {$ward1Id}")->fetchColumn();
                $citizenAId = $seededUserIds['citizen'] ?? 1;

                // Task 1: Active Task for Field Worker
                $t1Track = 'MCC-DEMO-001';
                $c1Exists = (bool)$pdo->query("SELECT 1 FROM complaints WHERE public_complaint_number = '{$t1Track}'")->fetchColumn();
                if (!$c1Exists) {
                    $insC1 = $pdo->prepare("
                        INSERT INTO complaints (
                            public_complaint_number, citizen_user_id, category_id, subcategory_id, zone_id, ward_id,
                            department_id, current_supervisor_employee_id, internal_status, citizen_status,
                            description, deadline_at, submitted_at, created_at
                        ) VALUES (
                            ?, ?, ?, ?, ?, ?, ?, ?, 'assigned', 'assigned',
                            'সড়কে ময়লার স্তূপ জমে আছে, দ্রুত পরিষ্কার প্রয়োজন।',
                            NOW() + INTERVAL 24 HOUR, NOW(), NOW()
                        )
                    ");
                    $insC1->execute([$t1Track, $citizenAId, $catId, $subcatId, $zone1Id, $ward1Id, $wasteDeptId, $seededEmployeeIds['supervisor']]);
                    $c1Id = (int)$pdo->lastInsertId();

                    $pdo->prepare("
                        INSERT INTO complaint_locations (complaint_id, latitude, longitude, landmark, approximate_address, public_safe_address)
                        VALUES (?, 24.7471, 90.4203, 'টাউন হল মোড়', 'বড় বাজার রোড, টাউন হল মোড়', 'টাউন হল মোড়, ওয়ার্ড ১')
                    ")->execute([$c1Id]);

                    $insTask1 = $pdo->prepare("
                        INSERT INTO field_tasks (complaint_id, task_code, assigned_team_id, assigned_worker_employee_id, supervisor_employee_id, task_status, instructions, created_at)
                        VALUES (?, 'TSK-DEMO-001', ?, ?, ?, 'pending', 'টাউন হল মোড়ের ময়লা অপসারণ করুন।', NOW())
                    ");
                    $insTask1->execute([$c1Id, $teamId, $seededEmployeeIds['field_worker'], $seededEmployeeIds['supervisor']]);
                }

                // Task 2: Work Completed awaiting Supervisor Verification
                $t2Track = 'MCC-DEMO-002';
                $c2Exists = (bool)$pdo->query("SELECT 1 FROM complaints WHERE public_complaint_number = '{$t2Track}'")->fetchColumn();
                if (!$c2Exists) {
                    $insC2 = $pdo->prepare("
                        INSERT INTO complaints (
                            public_complaint_number, citizen_user_id, category_id, subcategory_id, zone_id, ward_id,
                            department_id, current_supervisor_employee_id, internal_status, citizen_status,
                            description, completion_attempts, deadline_at, submitted_at, created_at
                        ) VALUES (
                            ?, ?, ?, ?, ?, ?, ?, ?, 'work_completed', 'work_completed',
                            'ড্রেন উপচে রাস্তায় পানি ও ময়লা নিষ্কাশন হচ্ছে।',
                            1, NOW() + INTERVAL 12 HOUR, NOW() - INTERVAL 5 HOUR, NOW() - INTERVAL 5 HOUR
                        )
                    ");
                    $insC2->execute([$t2Track, $citizenAId, $catId, $subcatId, $zone1Id, $ward1Id, $wasteDeptId, $seededEmployeeIds['supervisor']]);
                    $c2Id = (int)$pdo->lastInsertId();

                    $pdo->prepare("
                        INSERT INTO complaint_locations (complaint_id, latitude, longitude, landmark, approximate_address, public_safe_address)
                        VALUES (?, 24.7480, 90.4210, 'গাঙ্গিনার পাড়', 'রেলওয়ে ওভারব্রিজ সংলগ্ন', 'গাঙ্গিনার পাড়, ওয়ার্ড ১')
                    ")->execute([$c2Id]);

                    $insTask2 = $pdo->prepare("
                        INSERT INTO field_tasks (complaint_id, task_code, assigned_team_id, assigned_worker_employee_id, supervisor_employee_id, task_status, instructions, completed_at, created_at)
                        VALUES (?, 'TSK-DEMO-002', ?, ?, ?, 'completed', 'ড্রেন পরিষ্কার সম্পন্ন হয়েছে।', NOW(), NOW() - INTERVAL 4 HOUR)
                    ");
                    $insTask2->execute([$c2Id, $teamId, $seededEmployeeIds['field_worker'], $seededEmployeeIds['supervisor']]);
                }

                // Task 3: Overdue Complaint for Mayor / Administrator Attention Required
                $t3Track = 'MCC-DEMO-003';
                $c3Exists = (bool)$pdo->query("SELECT 1 FROM complaints WHERE public_complaint_number = '{$t3Track}'")->fetchColumn();
                if (!$c3Exists) {
                    $insC3 = $pdo->prepare("
                        INSERT INTO complaints (
                            public_complaint_number, citizen_user_id, category_id, subcategory_id, zone_id, ward_id,
                            department_id, current_supervisor_employee_id, internal_status, citizen_status,
                            description, deadline_at, deadline_missed_at, submitted_at, created_at
                        ) VALUES (
                            ?, ?, ?, ?, ?, ?, ?, ?, 'in_progress', 'in_progress',
                            'জরুরি ড্রেনেজ ব্লকেজ - পানি নামছে না।',
                            NOW() - INTERVAL 6 HOUR, NOW() - INTERVAL 6 HOUR, NOW() - INTERVAL 48 HOUR, NOW() - INTERVAL 48 HOUR
                        )
                    ");
                    $insC3->execute([$t3Track, $citizenAId, $catId, $subcatId, $zone1Id, $ward1Id, $wasteDeptId, $seededEmployeeIds['supervisor']]);
                    $c3Id = (int)$pdo->lastInsertId();

                    $pdo->prepare("
                        INSERT INTO complaint_locations (complaint_id, latitude, longitude, landmark, approximate_address, public_safe_address)
                        VALUES (?, 24.7430, 90.4150, 'চরপাড়া মোড়', 'মেডিকেল কলেজ রোড', 'চরপাড়া মোড়, ওয়ার্ড ১')
                    ")->execute([$c3Id]);

                    $insEA = $pdo->prepare("
                        INSERT INTO executive_attention (complaint_id, trigger_type, severity, is_active, created_at)
                        VALUES (?, 'deadline_breach', 'p2_high', 1, NOW())
                    ");
                    $insEA->execute([$c3Id]);
                }
            }
        });
    }
}
