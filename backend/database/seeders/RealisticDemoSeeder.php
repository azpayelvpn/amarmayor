<?php

declare(strict_types=1);

namespace AmarMayor\Database\Seeders;

use AmarMayor\Database\DatabaseManager;
use AmarMayor\Support\Config;
use AmarMayor\Support\Security;
use PDO;
use RuntimeException;

/**
 * Rich Realistic Demo Dataset Seeder.
 *
 * Populates:
 * - All 22 canonical system roles for interactive testing
 * - 40+ realistic fictional Demo Citizens with profiles and preferences
 * - Realistic Demo Workforce (cleaners, supervisors, technicians, inspectors)
 * - 600 Demo Complaints across all 33 Wards and 3 Zones over 90 days
 * - Dedicated testable portfolio for Demo Citizen A (01711000001) covering all statuses
 * - Separate isolated portfolio for Demo Citizen B (01711000002)
 * - Calibrated SLA overdue rates (~5-8% realistic overdue instead of uncalibrated 80%)
 * - Realistic citizen feedback distribution (~80% satisfaction score with 1-5 star mix)
 * - Unique actionable executive attention queue cases
 * - Privacy-safe public locations, status histories, and coherent field tasks
 *
 * All records strictly tagged with is_demo = 1 and verification_status = 'demo_test'.
 * Strictly blocked in production.
 */
class RealisticDemoSeeder
{
    public const DEMO_PASSWORD = 'Demo@12345';

    public static function run(): void
    {
        if (Config::get('app.env') === 'production') {
            throw new RuntimeException("RealisticDemoSeeder is strictly prohibited in production environments!");
        }

        DatabaseManager::transaction(function (PDO $pdo) {
            $cityId = (int)$pdo->query("SELECT id FROM cities WHERE slug = 'mcc'")->fetchColumn();
            if (!$cityId) {
                return;
            }

            $passwordHash = Security::hashPassword(self::DEMO_PASSWORD);

            // 1. Seed Canonical 22 Roles Identities
            $canonicalRoles = [
                ['slug' => 'public_viewer', 'email' => 'demo.public_viewer@demo.local', 'phone' => '01711000022', 'name_bn' => 'ডেমো পাবলিক ভিউয়ার', 'name_en' => 'Demo Public Viewer', 'type' => 'staff', 'desig_bn' => 'পর্যবেক্ষক', 'desig_en' => 'Observer', 'ward' => 1],
                ['slug' => 'citizen', 'email' => 'demo.citizen@demo.local', 'phone' => '01711000001', 'name_bn' => 'রফিকুল ইসলাম', 'name_en' => 'Rafiqul Islam', 'type' => 'citizen', 'desig_bn' => 'নাগরিক', 'desig_en' => 'Citizen', 'ward' => 1, 'area' => 'গাঙ্গিনার পাড়'],
                ['slug' => 'citizen_b', 'email' => 'demo.citizen_b@demo.local', 'phone' => '01711000002', 'name_bn' => 'সাবিনা ইয়াসমিন', 'name_en' => 'Sabina Yasmin', 'type' => 'citizen', 'desig_bn' => 'নাগরিক', 'desig_en' => 'Citizen', 'ward' => 5, 'area' => 'সানকিপাড়া'],
                ['slug' => 'mayor', 'email' => 'demo.mayor@demo.local', 'phone' => '01711000003', 'name_bn' => 'ডেমো মেয়র', 'name_en' => 'Demo Mayor', 'type' => 'staff', 'desig_bn' => 'মেয়র', 'desig_en' => 'City Mayor', 'ward' => null],
                ['slug' => 'administrator', 'email' => 'demo.administrator@demo.local', 'phone' => '01711000004', 'name_bn' => 'ডেমো প্রশাসক', 'name_en' => 'Demo Administrator', 'type' => 'staff', 'desig_bn' => 'প্রশাসক', 'desig_en' => 'City Administrator', 'ward' => null],
                ['slug' => 'ceo', 'email' => 'demo.ceo@demo.local', 'phone' => '01711000005', 'name_bn' => 'ডেমো প্রধান নির্বাহী কর্মকর্তা', 'name_en' => 'Demo Chief Executive Officer', 'type' => 'staff', 'desig_bn' => 'প্রধান নির্বাহী কর্মকর্তা (সিইও)', 'desig_en' => 'Chief Executive Officer', 'ward' => null],
                ['slug' => 'general_councillor', 'email' => 'demo.general_councillor@demo.local', 'phone' => '01711000006', 'name_bn' => 'ডেমো সাধারণ কাউন্সিলর', 'name_en' => 'Demo General Councillor', 'type' => 'representative', 'desig_bn' => 'কাউন্সিলর (ওয়ার্ড ১)', 'desig_en' => 'Ward Councillor (Ward 1)', 'ward' => 1],
                ['slug' => 'reserved_women_councillor', 'email' => 'demo.reserved_women_councillor@demo.local', 'phone' => '01711000007', 'name_bn' => 'ডেমো সংরক্ষিত নারী কাউন্সিলর', 'name_en' => 'Demo Reserved Women Councillor', 'type' => 'representative', 'desig_bn' => 'সংরক্ষিত নারী কাউন্সিলর (যাচাইকরণাধীন)', 'desig_en' => 'Reserved Women Councillor (Unassigned)', 'ward' => null],
                ['slug' => 'responsible_officer', 'email' => 'demo.responsible_officer@demo.local', 'phone' => '01711000008', 'name_bn' => 'ডেমো দায়িত্বপ্রাপ্ত কর্মকর্তা', 'name_en' => 'Demo Responsible Officer', 'type' => 'representative', 'desig_bn' => 'দায়িত্বপ্রাপ্ত কর্মকর্তা (ওয়ার্ড ২)', 'desig_en' => 'Responsible Officer (Ward 2)', 'ward' => 2],
                ['slug' => 'department_head', 'email' => 'demo.department_head@demo.local', 'phone' => '01711000009', 'name_bn' => 'ডেমো বিভাগীয় প্রধান', 'name_en' => 'Demo Department Head', 'type' => 'staff', 'desig_bn' => 'প্রধান বর্জ্য ব্যবস্থাপনা কর্মকর্তা', 'desig_en' => 'Chief Waste Management Officer', 'ward' => null],
                ['slug' => 'department_officer', 'email' => 'demo.department_officer@demo.local', 'phone' => '01711000010', 'name_bn' => 'ডেমো বিভাগীয় কর্মকর্তা', 'name_en' => 'Demo Department Officer', 'type' => 'staff', 'desig_bn' => 'সহকারী বর্জ্য ব্যবস্থাপনা কর্মকর্তা', 'desig_en' => 'Assistant Waste Officer', 'ward' => null],
                ['slug' => 'zone_officer', 'email' => 'demo.zone_officer@demo.local', 'phone' => '01711000011', 'name_bn' => 'ডেমো আঞ্চলিক নির্বাহী কর্মকর্তা', 'name_en' => 'Demo Zone Officer', 'type' => 'staff', 'desig_bn' => 'আঞ্চলিক নির্বাহী কর্মকর্তা (অঞ্চল ১)', 'desig_en' => 'Zonal Executive Officer (Zone 1)', 'ward' => null],
                ['slug' => 'ward_officer', 'email' => 'demo.ward_officer@demo.local', 'phone' => '01711000012', 'name_bn' => 'ডেমো ওয়ার্ড সচিব / কর্মকর্তা', 'name_en' => 'Demo Ward Officer', 'type' => 'staff', 'desig_bn' => 'ওয়ার্ড সচিব (ওয়ার্ড ১)', 'desig_en' => 'Ward Secretary (Ward 1)', 'ward' => 1],
                ['slug' => 'supervisor', 'email' => 'demo.supervisor@demo.local', 'phone' => '01711000013', 'name_bn' => 'ডেমো সুপারভাইজার', 'name_en' => 'Demo Supervisor', 'type' => 'staff', 'desig_bn' => 'পরিচ্ছন্নতা পরিদর্শক', 'desig_en' => 'Sanitation Inspector', 'ward' => 1],
                ['slug' => 'team_leader', 'email' => 'demo.team_leader@demo.local', 'phone' => '01711000014', 'name_bn' => 'ডেমো দলনেতা', 'name_en' => 'Demo Team Leader', 'type' => 'staff', 'desig_bn' => 'মাঠ দলনেতা', 'desig_en' => 'Field Team Leader', 'ward' => 1],
                ['slug' => 'field_worker', 'email' => 'demo.field_worker@demo.local', 'phone' => '01711000015', 'name_bn' => 'ডেমো মাঠকর্মী', 'name_en' => 'Demo Field Worker', 'type' => 'staff', 'desig_bn' => 'সড়ক পরিচ্ছন্নতাকর্মী', 'desig_en' => 'Street Cleaner', 'ward' => 1],
                ['slug' => 'call_center_operator', 'email' => 'demo.call_center_operator@demo.local', 'phone' => '01711000016', 'name_bn' => 'ডেমো কল সেন্টার অপারেটর', 'name_en' => 'Demo Call Center Operator', 'type' => 'staff', 'desig_bn' => 'অভিযোগ গ্রহণকারী অপারেটর', 'desig_en' => 'Intake Operator', 'ward' => null],
                ['slug' => 'control_room_officer', 'email' => 'demo.control_room_officer@demo.local', 'phone' => '01711000017', 'name_bn' => 'ডেমো কন্ট্রোল রুম কর্মকর্তা', 'name_en' => 'Demo Control Room Officer', 'type' => 'staff', 'desig_bn' => 'কন্ট্রোল রুম সমন্বয়ক', 'desig_en' => 'Control Room Coordinator', 'ward' => null],
                ['slug' => 'public_info_officer', 'email' => 'demo.public_info_officer@demo.local', 'phone' => '01711000018', 'name_bn' => 'ডেমো জনসংযোগ কর্মকর্তা', 'name_en' => 'Demo Public Information Officer', 'type' => 'staff', 'desig_bn' => 'জনসংযোগ কর্মকর্তা', 'desig_en' => 'Public Relations Officer', 'ward' => null],
                ['slug' => 'data_monitoring_officer', 'email' => 'demo.data_monitoring_officer@demo.local', 'phone' => '01711000019', 'name_bn' => 'ডেমো ডাটা ও মনিটরিং কর্মকর্তা', 'name_en' => 'Demo Data & Monitoring Officer', 'type' => 'staff', 'desig_bn' => 'আইটি ও পরিসংখ্যান কর্মকর্তা', 'desig_en' => 'IT & Statistics Officer', 'ward' => null],
                ['slug' => 'auditor', 'email' => 'demo.auditor@demo.local', 'phone' => '01711000020', 'name_bn' => 'ডেমো অডিটর', 'name_en' => 'Demo Auditor', 'type' => 'staff', 'desig_bn' => 'অভ্যন্তরীণ নিরীক্ষক', 'desig_en' => 'Internal Auditor', 'ward' => null],
                ['slug' => 'platform_super_admin', 'email' => 'demo.platform_super_admin@demo.local', 'phone' => '01711000021', 'name_bn' => 'ডেমো প্ল্যাটফর্ম সুপার অ্যাডমিন', 'name_en' => 'Demo Platform Super Admin', 'type' => 'staff', 'desig_bn' => 'প্ল্যাটফর্ম প্রশাসক', 'desig_en' => 'Platform Administrator', 'ward' => null],
                ['slug' => 'technical_super_admin', 'email' => 'demo.technical_super_admin@demo.local', 'phone' => '01711000000', 'name_bn' => 'ডেমো টেকনিক্যাল সুপার অ্যাডমিন', 'name_en' => 'Demo Technical Super Admin', 'type' => 'staff', 'desig_bn' => 'সিস্টেম ইঞ্জিনিয়ার', 'desig_en' => 'Lead System Engineer', 'ward' => null],
            ];

            $seededUserIds = [];
            $seededEmployeeIds = [];

            foreach ($canonicalRoles as $r) {
                $roleSlug = ($r['slug'] === 'citizen_b') ? 'citizen' : $r['slug'];
                $roleId = (int)$pdo->query("SELECT id FROM roles WHERE slug = '{$roleSlug}' LIMIT 1")->fetchColumn();
                if (!$roleId) {
                    continue;
                }

                $lookupHash = Security::phoneLookupHash($r['phone']);
                $uStmt = $pdo->prepare("SELECT id FROM users WHERE email = ? OR phone = ? LIMIT 1");
                $uStmt->execute([$r['email'], $r['phone']]);
                $userId = $uStmt->fetchColumn();

                if (!$userId) {
                    $uUuid = Security::uuid();
                    $insU = $pdo->prepare("
                        INSERT INTO users (uuid, email, phone, phone_lookup_hash, user_type, status, preferred_language, password_hash, is_demo, created_at)
                        VALUES (?, ?, ?, ?, ?, 'active', 'bn', ?, 1, NOW())
                    ");
                    $insU->execute([$uUuid, $r['email'], $r['phone'], $lookupHash, $r['type'], $passwordHash]);
                    $userId = (int)$pdo->lastInsertId();
                } else {
                    $userId = (int)$userId;
                    $pdo->prepare("UPDATE users SET email = ?, phone = ?, phone_lookup_hash = ?, password_hash = ?, user_type = ?, is_demo = 1, status = 'active' WHERE id = ?")
                        ->execute([$r['email'], $r['phone'], $lookupHash, $passwordHash, $r['type'], $userId]);
                }

                // Deterministic mapping
                if ($r['email'] === 'demo.citizen@demo.local') {
                    $seededUserIds['citizen'] = $userId;
                    $seededUserIds['citizen_a'] = $userId;
                } elseif ($r['email'] === 'demo.citizen_b@demo.local') {
                    $seededUserIds['citizen_b'] = $userId;
                } else {
                    $seededUserIds[$r['slug']] = $userId;
                }

                // Bind Role
                $urExists = (bool)$pdo->query("SELECT 1 FROM user_roles WHERE user_id = {$userId} AND role_id = {$roleId}")->fetchColumn();
                if (!$urExists) {
                    $pdo->prepare("INSERT INTO user_roles (user_id, role_id) VALUES (?, ?)")->execute([$userId, $roleId]);
                }

                // Person record with Home Ward
                $homeWardId = !empty($r['ward']) ? (int)$pdo->query("SELECT id FROM wards WHERE ward_number = {$r['ward']} LIMIT 1")->fetchColumn() : null;
                $homeArea = $r['area'] ?? null;

                $pStmt = $pdo->prepare("SELECT id FROM persons WHERE user_id = ? LIMIT 1");
                $pStmt->execute([$userId]);
                $personId = $pStmt->fetchColumn();

                if (!$personId) {
                    $insP = $pdo->prepare("
                        INSERT INTO persons (user_id, full_name_bn, full_name_en, home_ward_id, home_area, notification_prefs, verification_status, is_demo, is_public_visible, created_at)
                        VALUES (?, ?, ?, ?, ?, '{\"sms\":true,\"app\":true}', 'demo_test', 1, 1, NOW())
                    ");
                    $insP->execute([$userId, $r['name_bn'], $r['name_en'], $homeWardId, $homeArea]);
                    $personId = (int)$pdo->lastInsertId();
                } else {
                    $personId = (int)$personId;
                    $pdo->prepare("UPDATE persons SET full_name_bn = ?, full_name_en = ?, home_ward_id = ?, home_area = ?, verification_status = 'demo_test', is_demo = 1 WHERE id = ?")
                        ->execute([$r['name_bn'], $r['name_en'], $homeWardId, $homeArea, $personId]);
                }

                // Employee record for staff roles
                if ($r['type'] !== 'citizen') {
                    $eStmt = $pdo->prepare("SELECT id FROM employees WHERE person_id = ? LIMIT 1");
                    $eStmt->execute([$personId]);
                    $empId = $eStmt->fetchColumn();

                    $empCode = 'DEMO-' . strtoupper(substr($r['slug'], 0, 4)) . '-' . str_pad((string)$userId, 3, '0', STR_PAD_LEFT);
                    if (!$empId) {
                        $insE = $pdo->prepare("
                            INSERT INTO employees (person_id, employee_code, employment_type, designation_bn, designation_en, duty_status, verification_status, is_demo, created_at)
                            VALUES (?, ?, 'permanent', ?, ?, 'available', 'demo_test', 1, NOW())
                        ");
                        $insE->execute([$personId, $empCode, $r['desig_bn'], $r['desig_en']]);
                        $empId = (int)$pdo->lastInsertId();
                    } else {
                        $empId = (int)$empId;
                        $pdo->prepare("UPDATE employees SET verification_status = 'demo_test', is_demo = 1 WHERE id = ?")->execute([$empId]);
                    }
                    $seededEmployeeIds[$r['slug']] = $empId;
                }
            }

            // 2. Seed 40+ Diverse Demo Citizens with Profiles & Wards
            $citizenNames = [
                ['bn' => 'মাহমুদ হাসান', 'en' => 'Mahmud Hasan', 'ward' => 1, 'area' => 'গাঙ্গিনার পাড়'],
                ['bn' => 'ফাতেমা জাহান', 'en' => 'Fatema Jahan', 'ward' => 2, 'area' => 'বড় বাজার রোড'],
                ['bn' => 'কামরুল হাসান', 'en' => 'Kamrul Hasan', 'ward' => 3, 'area' => 'চরপাড়া মেডিকেল মোড়'],
                ['bn' => 'নাসরিন আক্তার', 'en' => 'Nasreen Akhter', 'ward' => 4, 'area' => 'টাউন হল রোড'],
                ['bn' => 'আরিফুল হক', 'en' => 'Ariful Haque', 'ward' => 5, 'area' => 'সানকিপাড়া'],
                ['bn' => 'সুলতানা পারভীন', 'en' => 'Sultana Parveen', 'ward' => 6, 'area' => 'কাঁচিঝুলি'],
                ['bn' => 'তানভীর আহমেদ', 'en' => 'Tanvir Ahmed', 'ward' => 7, 'area' => 'আকুয়া মোড়'],
                ['bn' => 'শামসুন্নাহার', 'en' => 'Shamsun Nahar', 'ward' => 8, 'area' => 'নওমহল'],
                ['bn' => 'জাহিদুল ইসলাম', 'en' => 'Zahidul Islam', 'ward' => 9, 'area' => 'কৃষ্টপুর'],
                ['bn' => 'রোকেয়া খাতুন', 'en' => 'Rokeya Khatun', 'ward' => 10, 'area' => 'শম্ভুগঞ্জ'],
                ['bn' => 'শাহাদাত হোসেন', 'en' => 'Shahadat Hossain', 'ward' => 11, 'area' => 'ভাটিকাশর'],
                ['bn' => 'লায়লা আরজুমান', 'en' => 'Laila Arjuman', 'ward' => 12, 'area' => 'দাপুনিয়া'],
                ['bn' => 'মুস্তাফিজুর রহমান', 'en' => 'Mustafizur Rahman', 'ward' => 13, 'area' => 'খাগডহর'],
                ['bn' => 'ফেরদৌসী বেগম', 'en' => 'Ferdousi Begum', 'ward' => 14, 'area' => 'বয়রা বাজার'],
                ['bn' => 'মোজাম্মেল হক', 'en' => 'Mozammel Haque', 'ward' => 15, 'area' => 'পাটগুদাম ব্রিজ মোড়'],
                ['bn' => 'নাজমুন নাহার', 'en' => 'Najmun Nahar', 'ward' => 16, 'area' => 'কাশর'],
                ['bn' => 'আবুল কালাম আজাদ', 'en' => 'Abul Kalam Azad', 'ward' => 17, 'area' => 'মাসকান্দা বাসস্ট্যান্ড'],
                ['bn' => 'পারভীন সুলতানা', 'en' => 'Parveen Sultana', 'ward' => 18, 'area' => 'কেওয়াটখালী'],
                ['bn' => 'তারেক মাহমুদ', 'en' => 'Tarek Mahmud', 'ward' => 19, 'area' => 'গলগণ্ডা'],
                ['bn' => 'শিরীন আক্তার', 'en' => 'Shirin Akhter', 'ward' => 20, 'area' => 'বাঘমারা'],
                ['bn' => 'গোলাম মোস্তফা', 'en' => 'Golam Mostafa', 'ward' => 21, 'area' => 'পণ্ডিতপাড়া'],
                ['bn' => 'রুবিনা ইয়াসমিন', 'en' => 'Rubina Yasmin', 'ward' => 22, 'area' => 'মহারাজা রোড'],
                ['bn' => 'সাইফুল ইসলাম', 'en' => 'Saiful Islam', 'ward' => 23, 'area' => 'আমলাপাড়া'],
                ['bn' => 'সেলিনা হোসেন', 'en' => 'Selina Hossain', 'ward' => 24, 'area' => 'গুলকিবাড়ি'],
                ['bn' => 'হাবিবুর রহমান', 'en' => 'Habibur Rahman', 'ward' => 25, 'area' => 'আলোকিত নগর'],
                ['bn' => 'বিলকিস বেগম', 'en' => 'Bilkis Begum', 'ward' => 26, 'area' => 'শান্তিনগর'],
                ['bn' => 'মতিউর রহমান', 'en' => 'Motiur Rahman', 'ward' => 27, 'area' => 'রহমতপুর'],
                ['bn' => 'রাবেয়া বসরী', 'en' => 'Rabeya Basri', 'ward' => 28, 'area' => 'কালীবাড়ি'],
                ['bn' => 'আনোয়ার হোসেন', 'en' => 'Anwar Hossain', 'ward' => 29, 'area' => 'ধোপাখলা'],
                ['bn' => 'সাবিহা চৌধুরী', 'en' => 'Sabiha Chowdhury', 'ward' => 30, 'area' => 'সেহড়া'],
                ['bn' => 'ফারুক আহমেদ', 'en' => 'Faruk Ahmed', 'ward' => 31, 'area' => 'নয়াপাড়া'],
                ['bn' => 'মেহেরুন্নেসা', 'en' => 'Meherunnesa', 'ward' => 32, 'area' => 'কাশিগঞ্জ'],
                ['bn' => 'কবির হোসেন', 'en' => 'Kabir Hossain', 'ward' => 33, 'area' => 'বড়বিল'],
                ['bn' => 'আসাদুজ্জামান', 'en' => 'Asaduzzaman', 'ward' => 1, 'area' => 'দুর্গাবাড়ী রোড'],
                ['bn' => 'তাহমিনা আক্তার', 'en' => 'Tahmina Akhter', 'ward' => 2, 'area' => 'ছোট বাজার'],
                ['bn' => 'ইমরান খান', 'en' => 'Imran Khan', 'ward' => 3, 'area' => 'ভাটিকাশর রোড'],
                ['bn' => 'সোহেল রানা', 'en' => 'Sohel Rana', 'ward' => 4, 'area' => 'নাহিদ সড়ক'],
                ['bn' => 'মমতাজ বেগম', 'en' => 'Momtaz Begum', 'ward' => 5, 'area' => 'ধোপাখলা মোড়'],
                ['bn' => 'বোরহান উদ্দিন', 'en' => 'Borhan Uddin', 'ward' => 6, 'area' => 'কলেজ রোড'],
                ['bn' => 'আফসানা মিমি', 'en' => 'Afsana Mimi', 'ward' => 7, 'area' => 'রেলওয়ে কলোনি'],
            ];

            $citizenRole = (int)$pdo->query("SELECT id FROM roles WHERE slug = 'citizen' LIMIT 1")->fetchColumn();
            $demoCitizenUserIds = [$seededUserIds['citizen'], $seededUserIds['citizen_b']];

            $cIdx = 50;
            foreach ($citizenNames as $c) {
                $cPhone = '017110000' . str_pad((string)$cIdx, 2, '0', STR_PAD_LEFT);
                $cEmail = "demo.citizen_{$cIdx}@demo.local";
                $cLookup = Security::phoneLookupHash($cPhone);

                $uStmt = $pdo->prepare("SELECT id FROM users WHERE phone = ? LIMIT 1");
                $uStmt->execute([$cPhone]);
                $uId = $uStmt->fetchColumn();

                if (!$uId) {
                    $uUuid = Security::uuid();
                    $insU = $pdo->prepare("
                        INSERT INTO users (uuid, email, phone, phone_lookup_hash, user_type, status, preferred_language, password_hash, is_demo, created_at)
                        VALUES (?, ?, ?, ?, 'citizen', 'active', 'bn', ?, 1, NOW())
                    ");
                    $insU->execute([$uUuid, $cEmail, $cPhone, $cLookup, $passwordHash]);
                    $uId = (int)$pdo->lastInsertId();

                    $pdo->prepare("INSERT INTO user_roles (user_id, role_id) VALUES (?, ?)")->execute([$uId, $citizenRole]);
                } else {
                    $uId = (int)$uId;
                    $pdo->prepare("UPDATE users SET is_demo = 1, user_type = 'citizen', password_hash = ? WHERE id = ?")->execute([$passwordHash, $uId]);
                }

                $wId = (int)$pdo->query("SELECT id FROM wards WHERE ward_number = {$c['ward']} LIMIT 1")->fetchColumn();
                $pStmt = $pdo->prepare("SELECT id FROM persons WHERE user_id = ? LIMIT 1");
                $pStmt->execute([$uId]);
                $pId = $pStmt->fetchColumn();

                if (!$pId) {
                    $insP = $pdo->prepare("
                        INSERT INTO persons (user_id, full_name_bn, full_name_en, home_ward_id, home_area, notification_prefs, verification_status, is_demo, is_public_visible, created_at)
                        VALUES (?, ?, ?, ?, ?, '{\"sms\":true,\"app\":true}', 'demo_test', 1, 1, NOW())
                    ");
                    $insP->execute([$uId, $c['bn'], $c['en'], $wId ?: null, $c['area']]);
                } else {
                    $pdo->prepare("
                        UPDATE persons SET full_name_bn = ?, full_name_en = ?, home_ward_id = ?, home_area = ?, is_demo = 1, verification_status = 'demo_test'
                        WHERE id = ?
                    ")->execute([$c['bn'], $c['en'], $wId ?: null, $c['area'], $pId]);
                }

                $demoCitizenUserIds[] = $uId;
                $cIdx++;
            }

            // 3. Seed Demo Operational Workforce Teams across Departments
            $wasteDeptId = (int)$pdo->query("SELECT id FROM departments WHERE slug = 'waste_management' LIMIT 1")->fetchColumn();
            $healthDeptId = (int)$pdo->query("SELECT id FROM departments WHERE slug = 'public_health' LIMIT 1")->fetchColumn();
            $drainDeptId = (int)$pdo->query("SELECT id FROM departments WHERE slug = 'drainage_waterlogging' LIMIT 1")->fetchColumn();
            $elecDeptId = (int)$pdo->query("SELECT id FROM departments WHERE slug = 'electrical_lighting' LIMIT 1")->fetchColumn();
            $engDeptId = (int)$pdo->query("SELECT id FROM departments WHERE slug = 'engineering_civil' LIMIT 1")->fetchColumn();

            $ward1Id = (int)$pdo->query("SELECT id FROM wards WHERE ward_number = 1 LIMIT 1")->fetchColumn();

            $demoTeams = [
                ['name_bn' => 'ডেমো পরিচ্ছন্নতা দল ১ (অঞ্চল ১)', 'name_en' => 'Demo Sanitation Team 01 (Zone 1)', 'dept' => $wasteDeptId],
                ['name_bn' => 'ডেমো পরিচ্ছন্নতা দল ২ (অঞ্চল ২)', 'name_en' => 'Demo Sanitation Team 02 (Zone 2)', 'dept' => $wasteDeptId],
                ['name_bn' => 'ডেমো ড্রেন পলি অপসারণ দল', 'name_en' => 'Demo Drain Desilting Team', 'dept' => $drainDeptId],
                ['name_bn' => 'ডেমো মশক নিধন ও স্প্রে দল', 'name_en' => 'Demo Mosquito Fogging Team', 'dept' => $healthDeptId],
                ['name_bn' => 'ডেমো সড়কবাতি মেরামত দল', 'name_en' => 'Demo Streetlight Repair Team', 'dept' => $elecDeptId],
                ['name_bn' => 'ডেমো সড়ক সংস্কার কুইক টিম', 'name_en' => 'Demo Road Quick Action Team', 'dept' => $engDeptId],
            ];

            $teamIds = [];
            $supervisorEmpId = $seededEmployeeIds['supervisor'] ?? 1;
            $workerEmpId = $seededEmployeeIds['field_worker'] ?? 1;

            foreach ($demoTeams as $t) {
                if (!$t['dept']) {
                    continue;
                }
                $tStmt = $pdo->prepare("SELECT id FROM teams WHERE name_en = ? LIMIT 1");
                $tStmt->execute([$t['name_en']]);
                $tId = $tStmt->fetchColumn();

                if (!$tId) {
                    $insT = $pdo->prepare("
                        INSERT INTO teams (department_id, ward_id, name_bn, name_en, supervisor_employee_id, team_leader_employee_id, status, is_demo, created_at)
                        VALUES (?, ?, ?, ?, ?, ?, 'active', 1, NOW())
                    ");
                    $insT->execute([$t['dept'], $ward1Id, $t['name_bn'], $t['name_en'], $supervisorEmpId, $workerEmpId]);
                    $tId = (int)$pdo->lastInsertId();

                    $insTM = $pdo->prepare("INSERT INTO team_members (team_id, employee_id, effective_from, created_at) VALUES (?, ?, NOW(), NOW())");
                    $insTM->execute([$tId, $workerEmpId]);
                } else {
                    $tId = (int)$tId;
                    $pdo->prepare("UPDATE teams SET is_demo = 1 WHERE id = ?")->execute([$tId]);
                }
                $teamIds[] = $tId;
            }

            // 4. Load all Active Subcategories & Wards
            $subcategories = $pdo->query("
                SELECT cs.id, cs.category_id, cs.name_bn, cs.name_en, cs.default_priority, cs.default_classification,
                       cc.slug as cat_slug
                FROM complaint_subcategories cs
                INNER JOIN complaint_categories cc ON cc.id = cs.category_id
                WHERE cs.is_active = 1
            ")->fetchAll(PDO::FETCH_ASSOC);

            $wards = $pdo->query("SELECT id, ward_number, zone_id FROM wards WHERE status = 'active' ORDER BY ward_number ASC")->fetchAll(PDO::FETCH_ASSOC);

            $deptMap = [
                'waste_management' => $wasteDeptId,
                'drainage_waterlogging' => $drainDeptId,
                'electrical_lighting' => $elecDeptId,
                'public_health' => $healthDeptId,
                'engineering_civil' => $engDeptId,
            ];

            // 5. Setup Flagship Scenarios specifically for Citizen A (01711000001) & Citizen B (01711000002)
            $now = time();
            $citizenAId = $seededUserIds['citizen'];
            $citizenBId = $seededUserIds['citizen_b'];

            $flagshipScenarios = [
                [
                    'num' => 'MCC-DEMO-001', 'citizen' => $citizenAId, 'ward_idx' => 0,
                    'internal' => 'submitted', 'citizen_st' => 'received', 'hours_ago' => 3, 'sla_hours' => 48,
                    'desc' => 'টাউন হল মোড়ে সড়কে ময়লার স্তূপ জমে আছে, দ্রুত পরিষ্কার প্রয়োজন।',
                    'task_st' => 'pending', 'is_reopen' => 0, 'is_overdue' => 0, 'is_recurring' => 0
                ],
                [
                    'num' => 'MCC-DEMO-002', 'citizen' => $citizenAId, 'ward_idx' => 0,
                    'internal' => 'work_completed', 'citizen_st' => 'confirmation_needed', 'hours_ago' => 20, 'sla_hours' => 48,
                    'desc' => 'গাঙ্গিনার পাড়ে ড্রেন উপচে রাস্তায় পানি ও ময়লা নিষ্কাশন হচ্ছে। মাঠকর্মীরা কাজ শেষ করেছেন।',
                    'task_st' => 'completed', 'is_reopen' => 0, 'is_overdue' => 0, 'is_recurring' => 0
                ],
                [
                    'num' => 'MCC-DEMO-003', 'citizen' => $citizenAId, 'ward_idx' => 1,
                    'internal' => 'in_progress', 'citizen_st' => 'in_progress', 'hours_ago' => 96, 'sla_hours' => 48,
                    'desc' => 'চরপাড়া মেডিকেল মোড়ে জরুরি ড্রেনেজ ব্লকেজ - পানি নামছে না। সময়সীমা অতিক্রম করেছে।',
                    'task_st' => 'in_progress', 'is_reopen' => 0, 'is_overdue' => 1, 'is_recurring' => 0
                ],
                [
                    'num' => 'MCC-DEMO-004', 'citizen' => $citizenAId, 'ward_idx' => 2,
                    'internal' => 'needs_more_work', 'citizen_st' => 'needs_more_work', 'hours_ago' => 72, 'sla_hours' => 48,
                    'desc' => 'সানকিপাড়ায় ড্রেন পরিষ্কার করার পর আবর্জনা রাস্তার পাশে স্তূপ করে রেখে যাওয়া হয়েছে। নাগরিক পুনরায় চালু করেছেন।',
                    'task_st' => 'in_progress', 'is_reopen' => 1, 'is_overdue' => 0, 'is_recurring' => 0
                ],
                [
                    'num' => 'MCC-DEMO-005', 'citizen' => $citizenAId, 'ward_idx' => 3,
                    'internal' => 'resolved', 'citizen_st' => 'resolved', 'hours_ago' => 140, 'sla_hours' => 48,
                    'desc' => 'কাঁচিঝুলি মোড়ে ৫টি এলইডি সড়কবাতি মেরামত সম্পন্ন হয়েছে। নাগরিক সন্তুষ্টি নিশ্চিত করেছেন।',
                    'task_st' => 'completed', 'is_reopen' => 0, 'is_overdue' => 0, 'is_recurring' => 0, 'rating' => 5
                ],
                [
                    'num' => 'MCC-DEMO-006', 'citizen' => $citizenAId, 'ward_idx' => 0,
                    'internal' => 'in_progress', 'citizen_st' => 'in_progress', 'hours_ago' => 30, 'sla_hours' => 48,
                    'desc' => 'বড় বাজার রেলক্রসিং সংলগ্ন নিয়মিত আবর্জনা হটস্পট। প্রতিদিন উপচে পড়ে।',
                    'task_st' => 'in_progress', 'is_reopen' => 0, 'is_overdue' => 0, 'is_recurring' => 1
                ],
                [
                    'num' => 'MCC-DEMO-007', 'citizen' => $citizenBId, 'ward_idx' => 4,
                    'internal' => 'in_progress', 'citizen_st' => 'in_progress', 'hours_ago' => 15, 'sla_hours' => 48,
                    'desc' => 'আকুয়া মোড়ে ভাঙা রাস্তায় বৃষ্টির পানি জমে যান চলাচলে বিঘ্ন ঘটছে।',
                    'task_st' => 'in_progress', 'is_reopen' => 0, 'is_overdue' => 0, 'is_recurring' => 0
                ],
                [
                    'num' => 'MCC-DEMO-008', 'citizen' => $citizenBId, 'ward_idx' => 5,
                    'internal' => 'resolved', 'citizen_st' => 'resolved', 'hours_ago' => 160, 'sla_hours' => 48,
                    'desc' => 'পাটগুদাম ব্রিজ সংলগ্ন ড্রেন পরিষ্কারকরণ ও ময়লা অপসারণ সফলভাবে সম্পন্ন হয়েছে।',
                    'task_st' => 'completed', 'is_reopen' => 0, 'is_overdue' => 0, 'is_recurring' => 0, 'rating' => 4
                ],
            ];

            $insComp = $pdo->prepare("
                INSERT INTO complaints (
                    public_complaint_number, citizen_user_id, created_by_user_id,
                    category_id, subcategory_id, ward_id, zone_id, department_id,
                    current_supervisor_employee_id, current_team_id,
                    priority, operational_classification, internal_status, citizen_status,
                    description, is_sensitive, is_recurring,
                    submitted_at, deadline_at, deadline_missed_at,
                    completion_attempts, reopen_count, first_reopened_at, verified_at, citizen_confirmed_at, closed_at,
                    is_demo, data_origin, created_at
                ) VALUES (
                    ?, ?, ?,
                    ?, ?, ?, ?, ?,
                    ?, ?,
                    ?, ?, ?, ?,
                    ?, 0, ?,
                    ?, ?, ?,
                    ?, ?, ?, ?, ?, ?,
                    1, 'demo', ?
                )
            ");

            $insLoc = $pdo->prepare("
                INSERT INTO complaint_locations (
                    complaint_id, latitude, longitude, landmark, approximate_address, public_safe_address
                ) VALUES (?, ?, ?, ?, ?, ?)
            ");

            $insTask = $pdo->prepare("
                INSERT INTO field_tasks (
                    complaint_id, task_code, assigned_team_id, assigned_worker_employee_id, supervisor_employee_id,
                    task_status, instructions, started_at, completed_at, is_demo, created_at
                ) VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, 1, ?)
            ");

            $insHist = $pdo->prepare("
                INSERT INTO complaint_status_history (
                    complaint_id, from_internal_status, to_internal_status, from_citizen_status, to_citizen_status,
                    action_name, actor_user_id, is_demo, created_at
                ) VALUES (?, ?, ?, ?, ?, ?, ?, 1, ?)
            ");

            $insEA = $pdo->prepare("
                INSERT INTO executive_attention (
                    complaint_id, trigger_type, severity, is_active, is_demo, created_at
                ) VALUES (?, ?, ?, 1, 1, ?)
            ");

            $insFeedback = $pdo->prepare("
                INSERT INTO citizen_feedback (
                    complaint_id, citizen_user_id, resolution_confirmation, unresolved_reason_code,
                    rating_score, comment, confirmed_at
                ) VALUES (?, ?, 'confirmed', NULL, ?, ?, ?)
            ");

            $existingTracking = [];

            // Seed Flagship Scenarios
            foreach ($flagshipScenarios as $fs) {
                $sub = $subcategories[0];
                $catId = (int)$sub['category_id'];
                $subId = (int)$sub['id'];
                $ward = $wards[$fs['ward_idx'] % count($wards)];
                $wId = (int)$ward['id'];
                $zId = (int)$ward['zone_id'];
                $deptId = $wasteDeptId;
                $tId = $teamIds[0] ?? null;

                $submittedSec = $now - ($fs['hours_ago'] * 3600);
                $submittedTime = date('Y-m-d H:i:s', $submittedSec);

                $deadlineSec = $submittedSec + ($fs['sla_hours'] * 3600);
                $deadlineTime = date('Y-m-d H:i:s', $deadlineSec);

                $isOverdue = (bool)$fs['is_overdue'];
                $deadlineMissed = $isOverdue ? date('Y-m-d H:i:s', $deadlineSec) : null;

                $isResolved = ($fs['internal'] === 'resolved');
                $verifiedAt = in_array($fs['internal'], ['supervisor_verified', 'resolved'], true) ? date('Y-m-d H:i:s', $submittedSec + (20 * 3600)) : null;
                $confirmedAt = $isResolved ? date('Y-m-d H:i:s', $submittedSec + (24 * 3600)) : null;
                $closedAt = $isResolved ? date('Y-m-d H:i:s', $submittedSec + (24 * 3600)) : null;
                $reopenedAt = $fs['is_reopen'] ? date('Y-m-d H:i:s', $submittedSec + (36 * 3600)) : null;

                $cExists = $pdo->query("SELECT id FROM complaints WHERE public_complaint_number = '{$fs['num']}' LIMIT 1")->fetchColumn();
                if ($cExists) {
                    $cId = (int)$cExists;
                    $pdo->prepare("UPDATE complaints SET citizen_user_id = ?, created_by_user_id = ?, internal_status = ?, citizen_status = ?, is_demo = 1 WHERE id = ?")
                        ->execute([$fs['citizen'], $fs['citizen'], $fs['internal'], $fs['citizen_st'], $cId]);
                } else {
                    $insComp->execute([
                        $fs['num'], $fs['citizen'], $fs['citizen'],
                        $catId, $subId, $wId, $zId, $deptId,
                        $supervisorEmpId, $tId,
                        'p2_high', 'quick_action', $fs['internal'], $fs['citizen_st'],
                        $fs['desc'], $fs['is_recurring'],
                        $submittedTime, $deadlineTime, $deadlineMissed,
                        $isResolved ? 1 : 0, $fs['is_reopen'], $reopenedAt, $verifiedAt, $confirmedAt, $closedAt,
                        $submittedTime
                    ]);
                    $cId = (int)$pdo->lastInsertId();

                    $insLoc->execute([
                        $cId, 24.7471, 90.4203, 'টাউন হল মোড়', 'বড় বাজার রোড, টাউন হল মোড়', "টাউন হল মোড়, ওয়ার্ড {$ward['ward_number']}"
                    ]);

                    $tCode = 'TSK-DEMO-' . str_pad((string)$cId, 4, '0', STR_PAD_LEFT);
                    $insTask->execute([
                        $cId, $tCode, $tId, $workerEmpId, $supervisorEmpId,
                        $fs['task_st'], $fs['desc'], $submittedTime, $fs['task_st'] === 'completed' ? $verifiedAt : null, $submittedTime
                    ]);

                    $insHist->execute([
                        $cId, null, 'submitted', null, 'received', 'submitted', $fs['citizen'], $submittedTime
                    ]);

                    if ($isOverdue) {
                        $insEA->execute([$cId, 'deadline_breach', 'p2_high', $deadlineTime]);
                    }
                    if ($fs['is_reopen']) {
                        $insEA->execute([$cId, 'reopened_unresolved', 'p2_high', $reopenedAt ?: $submittedTime]);
                    }
                    if ($fs['is_recurring']) {
                        $insEA->execute([$cId, 'recurring_hotspot', 'p3_normal', $submittedTime]);
                    }

                    if ($isResolved && !empty($fs['rating'])) {
                        $insFeedback->execute([
                            $cId, $fs['citizen'], (int)$fs['rating'], 'দ্রুত সেবা দেওয়ার জন্য ধন্যবাদ।', $confirmedAt
                        ]);
                    }
                }
                $existingTracking[$fs['num']] = true;
            }

            // 6. Generate Remaining Complaints up to 600 total across all 33 Wards
            $complaintCount = 600;
            $sampleLandmarks = [
                'গাঙ্গিনের পাড় মোড়', 'বড় বাজার রেলক্রসিং', 'চরপাড়া মেডিকেল কলেজ গেইট', 'টাউন হল চত্বর',
                'সানকিপাড়া রেলগেট', 'কাঁচিঝুলি মোড়', 'আকুয়া বাইপাস মোড়', 'নওমহল মাদ্রাসা রোড',
                'কৃষ্টপুর প্রাইমারি স্কুল সংলগ্ন', 'শম্ভুগঞ্জ নতুন ব্রিজ রোড', 'ভাটিকাশর প্রধান সড়ক',
                'দাপুনিয়া বাজার মোড়', 'খাগডহর নদী তীর', 'বয়রা পলিটেকনিক সংলগ্ন', 'পাটগুদাম বাস টার্মিনাল',
                'মাসকান্দা কেন্দ্রীয় বাস টার্মিনাল', 'কেওয়াটখালী পাওয়ার হাউজ রোড', 'বাঘমারা মেডিকেল হোস্টেল',
                'পণ্ডিতপাড়া জামে মসজিদ মোড়', 'মহারাজা পার্কের বিপরীত', 'আমলাপাড়া পূজা মণ্ডপ গলি',
                'গুলকিবাড়ি সরকারি প্রাথমিক বিদ্যালয়', 'শান্তিনগর পানির পাম্প সংলগ্ন', 'রহমতপুর বাইপাস মোড়',
                'কালীবাড়ি মোড়', 'ধোপাখলা জিলা স্কুল রোড', 'সেহড়া ডিবি রোড', 'নয়াপাড়া খেলার মাঠ সংলগ্ন',
                'কাশিগঞ্জ বাজার রোড', 'বড়বিল মোড়', 'দুর্গাবাড়ী কালীমন্দির রোড', 'ছোট বাজার চালের আড়ত'
            ];

            $descriptions = [
                'সড়কে গৃহস্থালি বর্জ্যের স্তূপ জমে আছে, পথচারীদের চলাচলে চরম দুর্গন্ধ ও বিঘ্ন ঘটছে। দ্রুত অপসারণ প্রয়োজন।',
                'প্রধান ড্রেন উপচে রাস্তায় ময়লা পানি প্রবাহিত হচ্ছে। স্থানীয় দোকানপাটে পানি ঢুকে পড়ছে।',
                'রাস্তার ৫টি সড়কবাতি গত ৩ দিন ধরে অচল থাকায় রাতে সম্পূর্ণ অন্ধকার থাকে এবং ছিনতাইয়ের ঝুঁকি তৈরি হয়েছে।',
                'এলাকায় মশার প্রকোপ আশঙ্কাজনকভাবে বৃদ্ধি পেয়েছে। অবিলম্বে ফগিং ও লার্ভিসাইড স্প্রে করা দরকার।',
                'সড়কের পিচ উঠে বড় বড় গর্তের সৃষ্টি হয়েছে। অটোরিকশা ও রিকশা চলাচলে দুর্ঘটনা ঘটছে।',
                'ড্রেনের ওপরের কংক্রিট স্ল্যাব ভেঙে বিপজ্জনক গর্ত তৈরি হয়েছে। যেকোনো সময় পথচারী পড়ে যেতে পারে।',
                'বাজারের সামনে ডাস্টবিন ভেঙে গেছে এবং ময়লা রাস্তায় ছড়িয়ে পড়ছে। নতুন ডাস্টবিন স্থাপন প্রয়োজন।',
                'রাস্তায় হেলে পড়া গাছের শুকনো ডাল যে কোনো সময় বৈদ্যুতিক তারের ওপর পড়তে পারে। জরুরি অপসারণ চাই।',
                'অবৈধভাবে ফুটপাত দখল করে মালামাল রাখায় হেঁটে চলার কোনো সুযোগ নেই। উচ্ছেদ অভিযান প্রয়োজন।',
                'পৌর গণশৌচাগার অপরিচ্ছন্ন ও পানির সংযোগ বন্ধ থাকায় সাধারণ মানুষ ভোগান্তিতে পড়ছে।',
            ];

            $statusDist = [
                ['internal' => 'submitted', 'citizen' => 'received', 'weight' => 8],
                ['internal' => 'assigned', 'citizen' => 'assigned', 'weight' => 14],
                ['internal' => 'in_progress', 'citizen' => 'in_progress', 'weight' => 22],
                ['internal' => 'work_completed', 'citizen' => 'work_completed', 'weight' => 12],
                ['internal' => 'supervisor_verified', 'citizen' => 'confirmation_needed', 'weight' => 10],
                ['internal' => 'resolved', 'citizen' => 'resolved', 'weight' => 28],
                ['internal' => 'needs_more_work', 'citizen' => 'needs_more_work', 'weight' => 4],
                ['internal' => 'cancelled', 'citizen' => 'received', 'weight' => 2],
            ];

            $statusWeightedPool = [];
            foreach ($statusDist as $item) {
                for ($w = 0; $w < $item['weight']; $w++) {
                    $statusWeightedPool[] = $item;
                }
            }

            $subCount = count($subcategories);
            $wardCount = count($wards);
            $citCount = count($demoCitizenUserIds);
            $poolSize = count($statusWeightedPool);

            // Calibrated feedback ratings distribution (5: 55%, 4: 25%, 3: 12%, 2: 5%, 1: 3%)
            $feedbackRatings = [5, 5, 5, 5, 5, 5, 5, 5, 5, 5, 5, 4, 4, 4, 4, 4, 3, 3, 2, 1];
            $feedbackComments = [
                5 => 'চমৎকার ও দ্রুততম সমাধান। সিটি কর্পোরেশনকে ধন্যবাদ।',
                4 => 'কাজটি সঠিকভাবে সম্পন্ন হয়েছে। পরিচ্ছন্নতা বজায় থাকুক।',
                3 => 'সমাধান হয়েছে তবে আরও একটু দ্রুত হলে ভালো হতো।',
                2 => 'অনেক দেরিতে কাজ শেষ হলো।',
                1 => 'কাজের মান আশানুরূপ হয়নি।'
            ];

            for ($i = 9; $i <= $complaintCount; $i++) {
                $tracking = 'MCC-DEMO-' . str_pad((string)$i, 4, '0', STR_PAD_LEFT);
                if (isset($existingTracking[$tracking])) {
                    continue;
                }
                $cExistsInDb = $pdo->query("SELECT id FROM complaints WHERE public_complaint_number = '{$tracking}' LIMIT 1")->fetchColumn();
                if ($cExistsInDb) {
                    $existingTracking[$tracking] = true;
                    continue;
                }

                $sub = $subcategories[$i % $subCount];
                $catId = (int)$sub['category_id'];
                $subId = (int)$sub['id'];
                $ward = $wards[$i % $wardCount];
                $wId = (int)$ward['id'];
                $zId = (int)$ward['zone_id'];
                $st = $statusWeightedPool[$i % $poolSize];

                // Assign ~10 complaints throughout the range directly to Citizen A
                if ($i % 55 === 0) {
                    $citizenId = $citizenAId;
                } elseif ($i % 56 === 0) {
                    $citizenId = $citizenBId;
                } else {
                    $citizenId = $demoCitizenUserIds[$i % $citCount];
                }

                $catSlug = $sub['cat_slug'] ?? 'waste_management';
                $deptId = $deptMap[$catSlug] ?? $wasteDeptId;
                $tId = $teamIds[$i % count($teamIds)] ?? null;

                $isResolved = ($st['internal'] === 'resolved');
                $isCancelled = ($st['internal'] === 'cancelled');

                if ($isResolved || $isCancelled) {
                    // Historical completed complaints spread across 3 to 90 days ago
                    $daysAgo = ($i % 87) + 3;
                    $submittedSec = $now - ($daysAgo * 86400) + (($i % 12) * 3600);
                    $submittedTime = date('Y-m-d H:i:s', $submittedSec);

                    // SLA was 48h, resolved within 24-38 hours
                    $deadlineSec = $submittedSec + (48 * 3600);
                    $deadlineTime = date('Y-m-d H:i:s', $deadlineSec);

                    $durationHours = ($i % 14) + 20; // 20 to 33 hours
                    $closedSec = $submittedSec + ($durationHours * 3600);
                    $closedTime = date('Y-m-d H:i:s', $closedSec);
                    $verifiedTime = date('Y-m-d H:i:s', $closedSec - 7200);

                    $isOverdue = false;
                    $deadlineMissed = null;
                    $reopenCount = 0;
                    $firstReopenedAt = null;
                    $verifiedAt = $isResolved ? $verifiedTime : null;
                    $confirmedAt = $isResolved ? $closedTime : null;
                    $closedAt = $closedTime;
                } else {
                    // Open complaints
                    // 1. Overdue Breaches: strictly ~5-6% (approx 25-30 complaints total)
                    $isOverdue = ($i % 18 === 0);

                    // 2. Reopened active cases: ~4%
                    $isReopen = ($st['internal'] === 'needs_more_work' || ($i % 24 === 0));
                    $reopenCount = $isReopen ? 1 : 0;

                    if ($isOverdue) {
                        // Submitted 4-7 days ago with 48h SLA -> Overdue
                        $daysAgo = ($i % 4) + 4;
                        $submittedSec = $now - ($daysAgo * 86400);
                        $deadlineSec = $submittedSec + (48 * 3600);
                        $deadlineMissed = date('Y-m-d H:i:s', $deadlineSec);
                    } else {
                        // Healthy on-time active complaints submitted recently (2 to 36 hours ago)
                        $hoursAgo = ($i % 34) + 2;
                        $submittedSec = $now - ($hoursAgo * 3600);
                        $deadlineSec = $submittedSec + (48 * 3600); // deadline in future (> NOW())
                        $deadlineMissed = null;
                    }

                    $submittedTime = date('Y-m-d H:i:s', $submittedSec);
                    $deadlineTime = date('Y-m-d H:i:s', $deadlineSec);
                    $firstReopenedAt = $isReopen ? date('Y-m-d H:i:s', $submittedSec + (30 * 3600)) : null;

                    $verifiedAt = in_array($st['internal'], ['supervisor_verified', 'work_completed'], true) ? date('Y-m-d H:i:s', $submittedSec + (18 * 3600)) : null;
                    $confirmedAt = null;
                    $closedAt = null;
                }

                $isRecurring = ($i % 28 === 0);
                $landmark = $sampleLandmarks[$i % count($sampleLandmarks)];
                $desc = $descriptions[$i % count($descriptions)];

                $lat = 24.7100 + (($i % 60) * 0.0011);
                $lng = 90.3800 + (($i % 60) * 0.0011);

                $insComp->execute([
                    $tracking, $citizenId, $citizenId,
                    $catId, $subId, $wId, $zId, $deptId,
                    $supervisorEmpId, $tId,
                    $sub['default_priority'] ?? 'p3_normal', $sub['default_classification'] ?? 'quick_action',
                    $st['internal'], $st['citizen'],
                    $desc, $isRecurring ? 1 : 0,
                    $submittedTime, $deadlineTime, $deadlineMissed,
                    $isResolved ? 1 : 0, $reopenCount, $firstReopenedAt, $verifiedAt, $confirmedAt, $closedAt,
                    $submittedTime
                ]);
                $cId = (int)$pdo->lastInsertId();

                $publicSafeLoc = "{$landmark}, ওয়ার্ড " . $ward['ward_number'];
                $insLoc->execute([
                    $cId, $lat, $lng, $landmark, "{$landmark}, ময়মনসিংহ", $publicSafeLoc
                ]);

                // Create Task if past submitted stage
                if ($st['internal'] !== 'submitted' && $st['internal'] !== 'cancelled') {
                    $tCode = 'TSK-DEMO-' . str_pad((string)$cId, 4, '0', STR_PAD_LEFT);
                    $taskSt = in_array($st['internal'], ['work_completed', 'supervisor_verified', 'resolved'], true) ? 'completed' : 'in_progress';
                    $taskCompletedAt = ($taskSt === 'completed') ? ($verifiedAt ?: $submittedTime) : null;

                    $insTask->execute([
                        $cId, $tCode, $tId, $workerEmpId, $supervisorEmpId,
                        $taskSt, $desc, $submittedTime, $taskCompletedAt, $submittedTime
                    ]);
                }

                // Initial Status history
                $insHist->execute([
                    $cId, null, 'submitted', null, 'received', 'submitted', $citizenId, $submittedTime
                ]);

                $supervisorUserId = $seededUserIds['supervisor'] ?? 1;
                if ($st['internal'] !== 'submitted') {
                    $insHist->execute([
                        $cId, 'submitted', $st['internal'], 'received', $st['citizen'], 'status_update', $supervisorUserId, $submittedTime
                    ]);
                }

                // Seed Citizen Feedback for resolved complaints
                if ($isResolved) {
                    $rating = $feedbackRatings[$i % count($feedbackRatings)];
                    $comment = $feedbackComments[$rating] ?? 'ধন্যবাদ।';
                    $insFeedback->execute([
                        $cId, $citizenId, $rating, $comment, $closedAt
                    ]);
                }

                // Executive Attention triggers (Active only)
                if ($isOverdue && !$isResolved) {
                    $insEA->execute([$cId, 'deadline_breach', 'p2_high', $deadlineTime]);
                }
                if ($reopenCount >= 1 && !$isResolved) {
                    $insEA->execute([$cId, 'reopened_unresolved', 'p2_high', $firstReopenedAt ?: $submittedTime]);
                }
                if ($isRecurring && !$isResolved) {
                    $insEA->execute([$cId, 'recurring_hotspot', 'p3_normal', $submittedTime]);
                }
            }
        });
    }
}
