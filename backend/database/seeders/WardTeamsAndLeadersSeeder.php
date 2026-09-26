<?php

declare(strict_types=1);

namespace AmarMayor\Database\Seeders;

use AmarMayor\Database\DatabaseManager;
use AmarMayor\Support\Security;
use PDO;

class WardTeamsAndLeadersSeeder
{
    private const DEFAULT_PASSWORD = 'password123';

    public static function run(): void
    {
        $pdo = DatabaseManager::getConnection();
        $passwordHash = Security::hashPassword(self::DEFAULT_PASSWORD);

        $teamLeaderRoleId = (int)$pdo->query("SELECT id FROM roles WHERE slug = 'team_leader' LIMIT 1")->fetchColumn();
        if (!$teamLeaderRoleId) {
            echo "Error: Team Leader role not found.\n";
            return;
        }

        $wasteDeptId = (int)$pdo->query("SELECT id FROM departments WHERE slug = 'waste_management' LIMIT 1")->fetchColumn() ?: 1;

        // Fetch all 33 wards
        $wards = $pdo->query("SELECT id, ward_number FROM wards WHERE status = 'active' ORDER BY ward_number ASC")->fetchAll(PDO::FETCH_ASSOC);
        $wardMap = [];
        foreach ($wards as $wRow) {
            $wardMap[(int)$wRow['ward_number']] = (int)$wRow['id'];
        }

        for ($w = 1; $w <= 33; $w++) {
            $wardId = $wardMap[$w] ?? $w;
            $email = "team_leader.ward{$w}@demo.local";
            $phone = sprintf('017110020%02d', $w);
            $lookupHash = Security::phoneLookupHash($phone);
            $nameBn = "ওয়ার্ড " . to_bn_number((string)$w) . " মাঠ দলনেতা";
            $nameEn = "Ward {$w} Field Team Leader";
            $desigBn = "মাঠ দলনেতা (ওয়ার্ড " . to_bn_number((string)$w) . ")";
            $desigEn = "Field Team Leader (Ward {$w})";
            $empCode = sprintf('MCC-LEAD-%03d', $w);

            // 1. Team Leader User
            $stmt = $pdo->prepare("SELECT id FROM users WHERE email = ? LIMIT 1");
            $stmt->execute([$email]);
            $userId = $stmt->fetchColumn();

            if (!$userId) {
                $uStmt = $pdo->prepare("
                    INSERT INTO users (uuid, phone, phone_lookup_hash, email, password_hash, user_type, status, is_demo, created_at)
                    VALUES (UUID(), ?, ?, ?, ?, 'staff', 'active', 1, NOW())
                ");
                $uStmt->execute([$phone, $lookupHash, $email, $passwordHash]);
                $userId = (int)$pdo->lastInsertId();
            } else {
                $userId = (int)$userId;
                $pdo->prepare("UPDATE users SET password_hash = ? WHERE id = ?")->execute([$passwordHash, $userId]);
            }

            // Also ensure demo.team_leader@demo.local points to Ward 1 team leader credentials
            if ($w === 1) {
                $demoLeadStmt = $pdo->prepare("SELECT id FROM users WHERE email = 'demo.team_leader@demo.local' LIMIT 1");
                $demoLeadStmt->execute();
                $demoLeadId = $demoLeadStmt->fetchColumn();
                if ($demoLeadId) {
                    $pdo->prepare("UPDATE users SET password_hash = ? WHERE id = ?")->execute([$passwordHash, (int)$demoLeadId]);
                }
            }

            // Role assignment
            $pdo->prepare("INSERT IGNORE INTO user_roles (user_id, role_id) VALUES (?, ?)")->execute([$userId, $teamLeaderRoleId]);

            // 2. Person Record
            $pStmt = $pdo->prepare("SELECT id FROM persons WHERE user_id = ? LIMIT 1");
            $pStmt->execute([$userId]);
            $personId = $pStmt->fetchColumn();

            if (!$personId) {
                $inPerson = $pdo->prepare("
                    INSERT INTO persons (user_id, full_name_bn, full_name_en, official_phone, official_email, is_demo, created_at)
                    VALUES (?, ?, ?, ?, ?, 1, NOW())
                ");
                $inPerson->execute([$userId, $nameBn, $nameEn, $phone, $email]);
                $personId = (int)$pdo->lastInsertId();
            } else {
                $personId = (int)$personId;
            }

            // 3. Employee Record
            $eStmt = $pdo->prepare("SELECT id FROM employees WHERE person_id = ? OR employee_code = ? LIMIT 1");
            $eStmt->execute([$personId, $empCode]);
            $teamLeaderEmpId = $eStmt->fetchColumn();

            if (!$teamLeaderEmpId) {
                $inEmp = $pdo->prepare("
                    INSERT INTO employees (person_id, employee_code, designation_bn, designation_en, employment_type, duty_status, created_at)
                    VALUES (?, ?, ?, ?, 'permanent', 'active', NOW())
                ");
                $inEmp->execute([$personId, $empCode, $desigBn, $desigEn]);
                $teamLeaderEmpId = (int)$pdo->lastInsertId();
            } else {
                $teamLeaderEmpId = (int)$teamLeaderEmpId;
                $pdo->prepare("UPDATE employees SET person_id = ?, designation_bn = ?, designation_en = ? WHERE id = ?")
                    ->execute([$personId, $desigBn, $desigEn, $teamLeaderEmpId]);
            }

            // 4. Responsibility & Posting
            $pdo->prepare("DELETE FROM employee_responsibilities WHERE employee_id = ?")->execute([$teamLeaderEmpId]);
            $respStmt = $pdo->prepare("
                INSERT INTO employee_responsibilities (employee_id, area_type, area_id, responsibility_type, effective_from, verification_status, is_demo, created_at)
                VALUES (?, 'ward', ?, 'primary', CURDATE(), 'demo_test', 1, NOW())
            ");
            $respStmt->execute([$teamLeaderEmpId, $w]);

            $pdo->prepare("DELETE FROM employee_postings WHERE employee_id = ?")->execute([$teamLeaderEmpId]);
            $postStmt = $pdo->prepare("
                INSERT INTO employee_postings (employee_id, department_id, posting_type, effective_from, created_at)
                VALUES (?, ?, 'primary', CURDATE(), NOW())
            ");
            $postStmt->execute([$teamLeaderEmpId, $wasteDeptId]);

            // 5. Find Ward Supervisor Employee ID
            $supEmpStmt = $pdo->prepare("
                SELECT e.id FROM employees e
                INNER JOIN employee_responsibilities er ON er.employee_id = e.id
                INNER JOIN persons p ON p.id = e.person_id
                INNER JOIN user_roles ur ON ur.user_id = p.user_id
                INNER JOIN roles r ON r.id = ur.role_id
                WHERE er.area_type = 'ward' AND er.area_id = ? AND r.slug = 'supervisor'
                LIMIT 1
            ");
            $supEmpStmt->execute([$w]);
            $supervisorEmpId = $supEmpStmt->fetchColumn();
            if (!$supervisorEmpId) {
                $supervisorEmpId = 1;
            } else {
                $supervisorEmpId = (int)$supervisorEmpId;
            }

            // 6. Ward Sanitation Squad Team
            $teamNameBn = "ওয়ার্ড " . to_bn_number((string)$w) . " পরিচ্ছন্নতা স্কোয়াড";
            $teamNameEn = "Ward {$w} Sanitation Squad";

            $tStmt = $pdo->prepare("SELECT id FROM teams WHERE ward_id = ? AND department_id = ? LIMIT 1");
            $tStmt->execute([$wardId, $wasteDeptId]);
            $teamId = $tStmt->fetchColumn();

            if (!$teamId) {
                $inTeam = $pdo->prepare("
                    INSERT INTO teams (department_id, ward_id, name_bn, name_en, supervisor_employee_id, team_leader_employee_id, status, is_demo, created_at)
                    VALUES (?, ?, ?, ?, ?, ?, 'active', 1, NOW())
                ");
                $inTeam->execute([$wasteDeptId, $wardId, $teamNameBn, $teamNameEn, $supervisorEmpId, $teamLeaderEmpId]);
                $teamId = (int)$pdo->lastInsertId();
            } else {
                $teamId = (int)$teamId;
                $pdo->prepare("UPDATE teams SET name_bn = ?, name_en = ?, supervisor_employee_id = ?, team_leader_employee_id = ?, status = 'active' WHERE id = ?")
                    ->execute([$teamNameBn, $teamNameEn, $supervisorEmpId, $teamLeaderEmpId, $teamId]);
            }

            // 7. Cleaners Manpower Roster (8 cleaners per ward - Pure workforce resources, NO user logins)
            for ($c = 1; $c <= 8; $c++) {
                $cleanerCode = sprintf('MCC-CLN-%02d-%02d', $w, $c);
                $cleanerNameBn = "পরিচ্ছন্নতাকর্মী-" . to_bn_number((string)$w) . "-" . to_bn_number((string)$c);
                $cleanerNameEn = "Sanitation Worker {$w}-{$c}";

                $cEmpStmt = $pdo->prepare("SELECT id FROM employees WHERE employee_code = ? LIMIT 1");
                $cEmpStmt->execute([$cleanerCode]);
                $cleanerEmpId = $cEmpStmt->fetchColumn();

                if (!$cleanerEmpId) {
                    // Create Person without user_id
                    $inCleanPerson = $pdo->prepare("
                        INSERT INTO persons (user_id, full_name_bn, full_name_en, is_demo, is_public_visible, created_at)
                        VALUES (NULL, ?, ?, 1, 0, NOW())
                    ");
                    $inCleanPerson->execute([$cleanerNameBn, $cleanerNameEn]);
                    $cleanPersonId = (int)$pdo->lastInsertId();

                    $inCleanEmp = $pdo->prepare("
                        INSERT INTO employees (person_id, employee_code, designation_bn, designation_en, employment_type, duty_status, created_at)
                        VALUES (?, ?, 'পরিচ্ছন্নতাকর্মী', 'Sanitation Worker', 'daily_wage', 'available', NOW())
                    ");
                    $inCleanEmp->execute([$cleanPersonId, $cleanerCode]);
                    $cleanerEmpId = (int)$pdo->lastInsertId();
                } else {
                    $cleanerEmpId = (int)$cleanerEmpId;
                }

                // Add to team_members
                $pdo->prepare("
                    INSERT IGNORE INTO team_members (team_id, employee_id, effective_from, created_at)
                    VALUES (?, ?, CURDATE(), NOW())
                ")->execute([$teamId, $cleanerEmpId]);
            }
        }
    }
}
